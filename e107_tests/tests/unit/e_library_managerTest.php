<?php


	class e_library_managerTest extends \Codeception\Test\Unit
	{

		/**
		 * Library manager whose file handler refuses every network read, so any
		 * accidental remote fetch during a unit run fails loudly instead of
		 * silently reaching the Internet.
		 *
		 * @var e_library_manager
		 */
		protected $lib;

		/** @var core_library */
		protected $corelib;

		/** @var string[] all core library machine names */
		protected $libraries = array();

		/** @var string[] the CDN-hosted subset (machine names starting "cdn.") */
		protected $cdnLibraries = array();

		protected function _before()
		{
			$blockingFile = $this->make('e_file', array(
				'getRemoteContent' => function($address)
				{
					throw new RuntimeException(
						"Unit tests must not fetch from the Internet. Blocked remote read of: $address. " .
						"CDN version detection is exercised offline against vendored header fixtures; " .
						"see testCdnFileReportsHardcodedVersion and testRefreshCdnFixtures."
					);
				},
			));

			try
			{
				$this->lib = $this->make('e_library_manager', array('fileHandler' => $blockingFile));
				$this->corelib = $this->make('core_library');
			}
			catch(Exception $e)
			{
				$this->fail($e->getMessage());
			}

			$coreLibraries = $this->corelib->config();

			$this->assertNotEmpty($coreLibraries);

			$this->libraries = array_keys($coreLibraries);
			$this->cdnLibraries = array_values(array_filter($this->libraries, function($name)
			{
				return strpos($name, 'cdn.') === 0;
			}));
		}

		/**
		 * Make sure the default lookup contains no callbacks.
		 */
		public function testDetectionCallbacks()
		{

			foreach($this->libraries as $name)
			{
				$this->lib->detect($name);
			}

			$lookups = $this->lib->getCallbackLog();

			foreach($lookups as $name)
			{
				$this->fail("'version' key is missing in core_library:config() -- " . $name);
			}


		}

		/**
		 * The hard-coded version of every library must match the version detected
		 * from the actual bundled file. CDN entries carry no local file of their
		 * own, so they are checked against their bundled local sibling (the same
		 * library at the "cdn."-stripped machine name), which ships in the repo.
		 * Reading local files keeps this offline while still catching drift
		 * between a CDN pin and the copy e107 actually ships.
		 */
		function testDetectionVersionConsistency()
		{

			foreach($this->libraries as $name)
			{
				$coded = $this->lib->detect($name);

				$localName = strpos($name, 'cdn.') === 0 ? substr($name, strlen('cdn.')) : $name;
				$detected = $this->lib->detect($localName, true);

				$this->assertNotEmpty($coded['version'], "No coded version in core_library:config() -- $name");
				$this->assertNotEmpty($detected['version'], "No version detectable from the bundled file -- $localName");

				$this->assertSame($coded['version'], $detected['version'],
					"Version mismatch between '$name' (hard-coded {$coded['version']}) and the bundled " .
					"library '$localName' (file reports {$detected['version']}). Reconcile the pin in " .
					"core_library::config() with the copy under e107_web/lib/."
				);
			}


		}

		/**
		 * Every CDN library's declared version must appear in its resolved URL, so
		 * a version bump that forgets to move the pinned path (or vice versa) is
		 * caught. Pure string check, no I/O.
		 */
		public function testCdnUrlContainsVersion()
		{
			$this->assertNotEmpty($this->cdnLibraries, 'No cdn.* libraries found in core_library::config().');

			$config = $this->corelib->config();

			foreach($this->cdnLibraries as $name)
			{
				$version = $config[$name]['version'];
				$url = $this->lib->getPath($name);

				$this->assertNotFalse(strpos($url, $version),
					"CDN library '$name' pins version $version but that version does not appear in its " .
					"resolved URL ($url). The library_path/path and the version have drifted apart."
				);
			}
		}

		/**
		 * The hard-coded version of every CDN library must match what that CDN
		 * file reports about itself, exercising each entry's own version_arguments
		 * pattern. This is the check the test was originally written for, run
		 * offline: the CDN file's header is vendored as a fixture under
		 * tests/_data/library_headers/ and served through a stub file handler, so
		 * no network read happens. Regenerate the fixtures with
		 * testRefreshCdnFixtures whenever a CDN pin changes.
		 */
		public function testCdnFileReportsHardcodedVersion()
		{
			$config = $this->corelib->config();
			$fixtures = array();

			foreach($this->cdnLibraries as $name)
			{
				$fixturePath = $this->fixtureDir() . $name;
				$this->assertFileExists($fixturePath,
					"Missing CDN header fixture for '$name'. Run testRefreshCdnFixtures to snapshot it " .
					"(see the method's skip message for the command)."
				);
				$url = $this->versionFileUrl($config[$name]);
				$fixtures[$url] = file_get_contents($fixturePath);
			}

			$servingFile = $this->make('e_file', array(
				'getRemoteContent' => function($address) use ($fixtures)
				{
					if(!array_key_exists($address, $fixtures))
					{
						throw new RuntimeException(
							"No header fixture for $address. A CDN library was added or its pin moved; " .
							"run testRefreshCdnFixtures to snapshot it."
						);
					}
					return $fixtures[$address];
				},
			));

			$cdnLib = $this->make('e_library_manager', array('fileHandler' => $servingFile));

			foreach($this->cdnLibraries as $name)
			{
				$coded = $config[$name]['version'];
				$detected = $cdnLib->detect($name, true);

				$this->assertNotEmpty($detected['version'],
					"The CDN file for '$name' reported no version. Its version_arguments pattern may no " .
					"longer match the file, or the fixture is stale (run testRefreshCdnFixtures)."
				);
				$this->assertSame($coded, $detected['version'],
					"'$name' is pinned to $coded but its CDN file reports {$detected['version']}. Either " .
					"the pin is stale or the header fixture needs refreshing (run testRefreshCdnFixtures)."
				);
			}
		}

		public function testCoreLibraryPresence()
		{
			$coreLibraries = $this->corelib->config();

			foreach($coreLibraries as $id => $item)
			{
				$path = $this->lib->getPath($id);

				if(strpos($path, 'http') === 0)
				{
					// CDN libraries: presence is verified offline against the
					// vendored header fixtures (testCdnFileReportsHardcodedVersion)
					// and refreshed by testRefreshCdnFixtures, never by hitting the
					// network from a unit test.
					continue;
				}

				if(empty($item['files']))
				{
					continue;
				}

				foreach($item['files'] as $v)
				{
					foreach($v as $file => $info)
					{
						$this->assertStringNotContainsString('//', $path);
						$this->assertFileExists($path . $file);
					}
				}
			}
		}

		/**
		 * Regenerates the vendored CDN header fixtures from the live CDNs. This is
		 * the one place allowed to touch the Internet, and it is skipped unless
		 * explicitly requested, so an ordinary unit run stays fully offline. Run
		 * it after changing any CDN pin:
		 *
		 *   E107_REFRESH_LIBRARY_FIXTURES=1 vendor/bin/codecept run unit \
		 *       e_library_managerTest:testRefreshCdnFixtures
		 *
		 * A dead pin (fetch failure) fails the refresh loudly, which is how CDN
		 * presence is policed now that the unit suite no longer probes URLs.
		 */
		public function testRefreshCdnFixtures()
		{
			if(!getenv('E107_REFRESH_LIBRARY_FIXTURES'))
			{
				$this->markTestSkipped(
					'Set E107_REFRESH_LIBRARY_FIXTURES=1 to regenerate the CDN header fixtures from the live CDNs.'
				);
			}

			$realFile = e107::getFile();
			$config = $this->corelib->config();

			foreach($this->cdnLibraries as $name)
			{
				$url = $this->versionFileUrl($config[$name]);
				$content = $realFile->getRemoteContent($url);

				$this->assertNotEmpty($content, "Failed to fetch $url for '$name' (dead CDN pin?).");

				file_put_contents($this->fixtureDir() . $name, substr($content, 0, 4096));
				codecept_debug("Refreshed CDN header fixture: $name <- $url");
			}
		}

		/**
		 * The URL that {@see e_library_manager::detect()} reads to sniff a
		 * library's version, resolved exactly as getVersion() builds it.
		 *
		 * @param array $library one core_library::config() entry
		 * @return string
		 */
		private function versionFileUrl($library)
		{
			$libraryPath = e107::getParser()->replaceConstants($library['library_path']);
			$libraryPath = ($library['path'] !== '' ? rtrim($libraryPath, '/') . '/' . $library['path'] : $libraryPath);
			$libraryPath = rtrim($libraryPath, '/');

			return $libraryPath . '/' . $library['version_arguments']['file'];
		}

		/**
		 * @return string trailing-slash directory holding the CDN header fixtures
		 */
		private function fixtureDir()
		{
			return codecept_data_dir('library_headers/');
		}

/*
		public function testInfo()
		{

		}

		public function testGetProperty()
		{

		}

		public function testLoad()
		{

		}

		public function testGetExcludedLibraries()
		{

		}

		public function testGetPath()
		{

		}
*/



	}
