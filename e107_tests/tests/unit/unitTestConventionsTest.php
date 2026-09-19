<?php
	/**
	 * Guards the guarantees \Test\Unit makes to every unit test.
	 *
	 * The unit suite runs against two PHPUnit generations (PHPUnit 10+ via
	 * Codeception 5.x on PHP 8.1+, PHPUnit 5.7 / 6.x via Codeception 4.x on the
	 * PHP 5.6 and 7.0 cells), which do not share a complete assertion
	 * vocabulary. \Test\Unit carries \Helper\PhpUnitCompat to bridge that gap,
	 * so a test that extends a PHPUnit or Codeception base directly loses the
	 * bridge and fails on the legacy cells only -- typically in CI, long after
	 * it was written, with an error that says nothing about the cause.
	 *
	 * This test turns that delayed, remote failure into an immediate local one.
	 */

	class unitTestConventionsTest extends \Test\Unit
	{
		/**
		 * Base classes that a unit test must not extend directly, in every
		 * spelling the suite could reach them by. \Test\Unit extends the first
		 * of them on the suite's behalf.
		 *
		 * @var array
		 */
		private static $forbiddenParents = array(
			'\\Codeception\\Test\\Unit',
			'Codeception\\Test\\Unit',
			'\\PHPUnit\\Framework\\TestCase',
			'PHPUnit\\Framework\\TestCase',
			'TestCase',
		);

		public function testTheSharedBaseClassCarriesTheCompatibilityBridge()
		{
			$this->assertTrue(
				class_exists('\\Test\\Unit'),
				'\\Test\\Unit must be autoloadable from tests/_support/Test/Unit.php.'
			);

			$this->assertTrue(
				in_array('Helper\\PhpUnitCompat', class_uses('\\Test\\Unit'), true),
				'\\Test\\Unit must use \\Helper\\PhpUnitCompat: it is the only reason '
				. 'the suite has a shared base class at all.'
			);
		}

		public function testEveryUnitTestExtendsTheSharedBaseClass()
		{
			$offenders = array();

			foreach (\Test\Tree::phpFiles('tests/unit') as $file)
			{
				$parent = $this->declaredParent(file_get_contents($file));
				if ($parent === null)
				{
					continue;
				}

				$offenders[] = \Test\Tree::relativePath($file) . ' extends ' . $parent;
			}

			sort($offenders);

			$this->assertSame(
				array(),
				$offenders,
				"These unit tests extend a PHPUnit or Codeception base class directly.\n"
				. "Extend \\Test\\Unit instead -- it is that base class plus\n"
				. "\\Helper\\PhpUnitCompat, without which the PHPUnit 8/9-era assertion\n"
				. "names (assertMatchesRegularExpression, assertFileDoesNotExist,\n"
				. "assertDirectoryDoesNotExist) fatal on the PHP 5.6 and 7.0 cells."
			);
		}

		/**
		 * A child process that outlives its timeout fails the test that started it instead of stalling every test behind it.
		 */
		public function testASubprocessThatNeverReturnsFailsItsOwnTest()
		{
			try
			{
				$this->runInCli('sleep(30);', '', array(), 0.1);
			}
			catch (\Exception $wedged)
			{
				$this->assertStringContainsString('wedged', $wedged->getMessage());

				return;
			}

			self::fail(
				"A subprocess that never returns has to fail the test that started it.\n"
				. "\\Test\\Unit::runInCli() runs every child it starts under the timeout\n"
				. "utility for exactly that reason, and asserts on the status it reports."
			);
		}

		/**
		 * A timeout too small for the wrapper to express fails the test that asked for it instead of running the child unbounded.
		 */
		public function testATimeoutTooSmallToExpressFailsItsOwnTest()
		{
			$sentinel = sys_get_temp_dir().'/'.uniqid('runInCli-', true);

			try
			{
				$this->runInCli('touch('.var_export($sentinel, true).');', '', array(), 0.0004);
			}
			catch (\Exception $unbounded)
			{
				$this->assertStringContainsString('only a duration above zero', $unbounded->getMessage());
				$this->assertFileDoesNotExist($sentinel, 'The child ran before its budget was refused, so the refusal came too late to bound it.');

				return;
			}

			self::fail(
				"A budget the wrapper renders as zero leaves the child unbounded,\n"
				. "which is the one thing \\Test\\Unit::runInCli() exists to prevent, so it\n"
				. "refuses the budget rather than running the child without a timeout."
			);
		}

		/**
		 * Every booted child visits as itself, because e107 counts page hits per address and warns the ninetieth visit within five minutes off the site.
		 */
		public function testEachBootedChildVisitsAsItsOwnAddress()
		{
			$first  = $this->visit();
			$second = $this->visit($first['key']);

			self::assertTrue($first['counting'],
				'The child booted with tracking or flood control off, so nothing counts hits and this case would pass whatever the helper did.');
			self::assertNotSame($first['visitor'], $second['visitor'],
				'Two children of one run presented e107 with the same address, so the run is one visitor and its later boots meet the flood warning instead of the page under test.');
			self::assertLessThanOrEqual($first['hits'], $second['hits'],
				'Booting the second child added a hit to the first child\'s address ('.$first['visitor'].'), which is what warns a run off the site part way through.');
		}

		/**
		 * Boots a child and reports the address it visited as, encoded and for display, whether e107 counted the visit at all, and the hits standing against $key (its own address when omitted).
		 *
		 * @param string $key an encoded address, as e107 stores it
		 * @return array visitor, key, counting and hits
		 */
		private function visit($key = '')
		{
			$counted = $key === '' ? 'e107::getIPHandler()->getIP(false)' : "'".$key."'";

			list($output) = $this->runInBootedCli(
				"\$hits = e107::getDb()->createQueryBuilder()->select('online_pagecount')->from('online')"
				."->where('online_ip', ".$counted.")->where('online_user_id', '0')->fetchOne(); "
				."echo 'VISITOR=', e107::getIPHandler()->getIP(true), ' KEY=', e107::getIPHandler()->getIP(false), "
				."' COUNTING=', (int) !deftrue('e_TRACKING_DISABLED'), ' HITS=', (int) \$hits, PHP_EOL;");

			$visit = array();
			foreach ($output as $line)
			{
				if (preg_match('/^VISITOR=(\S+) KEY=([0-9a-fA-F:.]+) COUNTING=([01]) HITS=(\d+)$/', $line, $visit))
				{
					return array('visitor' => $visit[1], 'key' => $visit[2], 'counting' => $visit[3] === '1', 'hits' => (int) $visit[4]);
				}
			}

			self::fail("The child never reported the visit it made. It printed:\n".implode("\n", $output));
		}

		/**
		 * A test that writes the process-wide parser gets every setting back as it was found, in the type it was found in.
		 */
		public function testTheParserStateHelperPutsBackWhatItFound()
		{
			$parser = e107::getParser();
			$outer = $this->parserState();

			self::assertSame(
				array('staticUrl', 'modRewriteMedia', 'fontawesome', 'bootstrap', 'multibyte', 'thumbWidth',
					'thumbHeight', 'thumbCrop'),
				array_keys($outer),
				"\\Test\\Unit captures a parser setting this test does not dirty below, or has stopped capturing\n"
				. "one it does. Every setting the base class promises to put back is written here first, because\n"
				. "an assertion over the captured array alone passes whatever that array happens to contain.");

			try
			{
				$modRewriteMedia = new \e107\Reflection\ReflectionProperty('e_parse', 'modRewriteMedia');
				$modRewriteMedia->setValue($parser, '');

				$found = $this->parserState();

				$parser->setmodRewriteMedia(true);
				$parser->setFontAwesome(6);
				$parser->setBootstrap(5);
				$parser->setMultibyte(!$found['multibyte']);
				$parser->thumbWidth(320);
				$parser->thumbHeight(240);
				$parser->thumbCrop(1);
				$parser->setStaticUrl('https://static.example.com/');
				$parser->staticUrl('{e_WEB}script.js');

				self::assertNotSame(array(), $parser->getStaticUrlMap(),
					"Nothing below measures the discarding of a URL map unless serving a path under a static\n"
					. "URL builds one. e_parse::staticUrl() returns before it builds anything when e_ADMIN_AREA\n"
					. "is true, so a unit process booted inside an admin directory would land here.");

				$this->restoreParserState($found);

				self::assertSame($found, $this->parserState(),
					"A parser setting came back as something other than what \\Test\\Unit::parserState() found.\n"
					. "e_parse::setmodRewriteMedia() casts to bool over a parser born holding '', and\n"
					. "setFontAwesome() and setBootstrap() cast to int over properties born holding null, so\n"
					. "the setters cannot put back every value they accept and\n"
					. "\\Test\\Unit::restoreParserState() writes the properties instead.");

				self::assertSame(array(), $parser->getStaticUrlMap(),
					'The map of the domain already issued per path describes the static URL that has just been '
					. 'replaced, so leaving it behind serves the next test a domain nothing configures any more.');
			}
			finally
			{
				$this->restoreParserState($outer);
			}
		}

		/**
		 * The parent class a file declares, if it is one a test must not
		 * extend directly.
		 *
		 * @param string $source
		 * @return string|null
		 */
		private function declaredParent($source)
		{
			$matches = array();
			if (!preg_match_all('/\bclass\s+\w+\s+extends\s+(\\\\?[A-Za-z_\\\\][\w\\\\]*)/', $source, $matches))
			{
				return null;
			}

			foreach ($matches[1] as $parent)
			{
				if (in_array($parent, self::$forbiddenParents, true))
				{
					return $parent;
				}
			}

			return null;
		}
	}
