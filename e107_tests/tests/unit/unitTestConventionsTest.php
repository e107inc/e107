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
