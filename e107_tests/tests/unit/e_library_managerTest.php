<?php


	class e_library_managerTest extends \Codeception\Test\Unit
	{

		/** @var e_library_manager */
		protected $lib;

		/** @var e_library_manager */
		protected $lib2;

		/** @var core_library */
		protected $corelib;

		protected $libraries = array();

		protected function _before()
		{

			try
			{
				$this->lib = $this->make('e_library_manager');
			}
			catch(Exception $e)
			{
				$this->fail($e->getMessage());
			}


			try
			{
				$this->corelib = $this->make('core_library');
			}
			catch(Exception $e)
			{
				$this->fail($e->getMessage());
			}

			$coreLibraries = $this->corelib->config();

			$this->assertNotEmpty($coreLibraries);

			$this->libraries = array_keys($coreLibraries);

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

		function testDetectionVersionConsistency()
		{

			foreach($this->libraries as $name)
			{
				$coded = $this->lib->detect($name);
				$detected = $this->lib->detect($name, true);

				if(empty($coded['version']))
				{
					$this->fail("No coded version returned in core_library:config() -- " . $name);
				}

				if(empty($detected['version']))
				{
					$this->fail("No looked-up version in core_library:config() -- " . $name);
				}

				$this->assertSame($coded['version'],$detected['version'], 'Version mismatch in core_library:config() -- '.$name);

			}


		}

		public function testCoreLibraryPresence()
		{
			$coreLibraries = $this->corelib->config();

			foreach($coreLibraries as $id => $item)
			{
				$path = $this->lib->getPath($id);

				if(strpos($path, 'http') === 0) // Remote
				{
					if(!empty($item['files']))
					{
						foreach($item['files'] as $k=>$v)
						{
							foreach($v as $file => $info)
							{
								$url = $path.$file;
								$valid = $this->isValidURL($url);
								$this->assertTrue($valid, $url.' is not valid. (404)');
							}
						}
					}
				}
				else // Local
				{

					if(!empty($item['files']))
					{
						foreach($item['files'] as $k=>$v)
						{
							foreach($v as $file => $info)
							{
								$this->assertStringNotContainsString('//',$path);
								$this->assertFileExists($path.$file);
							}


						}




					}

				}

			}



		}

		private function isValidURL($url)
		{
			if(empty($url))
			{
				return false;
			}

			if(!$headers = get_headers($url))
			{
				return false;
			}

			if(!empty($headers[0]) && strpos((string) $headers[0], 'OK') !== false)
			{
				return true;
			}

			return false;


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
