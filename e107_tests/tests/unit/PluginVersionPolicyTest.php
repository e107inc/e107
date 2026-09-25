<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	use E107\PluginMetadata\ChangedPlugin;
	use E107\PluginMetadata\PluginVersionPolicy;

	/**
	 * Unit tests for the bundled-plugin metadata convention (e107_tests/_tools):
	 * a change to a plugin's files raises that plugin's plugin.xml version once
	 * per release cycle, and its date is no older than the newest change to the
	 * plugin. Pure logic - the facts come from git, the verdict comes from here.
	 *
	 * _before() loads e107_tests/_tools/plugin-metadata/src (PHP 8.1-only), so
	 * never a legacy cell.
	 *
	 * @group requires-modern-php
	 */
	class PluginVersionPolicyTest extends \Test\Unit
	{

		protected function _before()
		{
			require_once(e_BASE.'e107_tests/_tools/plugin-metadata/src/ChangedPlugin.php');
			require_once(e_BASE.'e107_tests/_tools/plugin-metadata/src/PolicyFailure.php');
			require_once(e_BASE.'e107_tests/_tools/plugin-metadata/src/PluginVersionPolicy.php');
		}

		/**
		 * @param string|null $baselineVersion version at the last tagged release
		 * @param string|null $headVersion version the branch declares
		 * @param string|null $headDate date the branch declares
		 * @param string $lastTouchedDate newest author date among the touching commits
		 * @return \E107\PluginMetadata\ChangedPlugin
		 */
		private function plugin($baselineVersion, $headVersion, $headDate = '2026-09-14',
			$lastTouchedDate = '2026-09-14')
		{
			return new ChangedPlugin('news', $baselineVersion, $headVersion, $headDate, $lastTouchedDate);
		}

		/**
		 * @return \E107\PluginMetadata\PluginVersionPolicy
		 */
		private function policy()
		{
			return new PluginVersionPolicy('v2.3.12');
		}

		public function testVersionUntouchedSinceTheTagFails()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_NOT_BUMPED, $failures[0]->reason);
			$this->assertSame('news', $failures[0]->folder);
			$this->assertStringContainsString('write version="1.0.1"', $failures[0]->message);
			$this->assertStringContainsString('v2.3.12', $failures[0]->message);
		}

		public function testVersionBumpedInThisChangePasses()
		{
			$this->assertSame(array(), $this->policy()->failures(array($this->plugin('1.0', '1.0.1'))));
		}

		public function testZeroPaddingIsNotARise()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0.0')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_NOT_BUMPED, $failures[0]->reason);
			$this->assertStringContainsString('write version="1.0.1"', $failures[0]->message);
		}

		public function testLevelsAreComparedAsNumbersNotAsText()
		{
			$this->assertSame(array(), $this->policy()->failures(array($this->plugin('1.9', '1.10'))));
		}

		public function testVersionAlreadyBumpedEarlierInTheCyclePasses()
		{
			$this->assertSame(array(), $this->policy()->failures(array($this->plugin('2.1', '2.2'))));
		}

		public function testPluginAbsentAtTheTagPasses()
		{
			$this->assertSame(array(), $this->policy()->failures(array($this->plugin(null, '1.0'))));
		}

		public function testVersionBelowTheTagFails()
		{
			$failures = $this->policy()->failures(array($this->plugin('2.0', '1.9')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_NOT_BUMPED, $failures[0]->reason);
			$this->assertStringContainsString('write version="2.0.1"', $failures[0]->message);
		}

		public function testNonNumericVersionFailsWithoutComparing()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0.1-beta')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_MALFORMED, $failures[0]->reason);
			$this->assertStringContainsString('1.0.1-beta', $failures[0]->message);
		}

		public function testMissingVersionAttributeFails()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', null)));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_MALFORMED, $failures[0]->reason);
			$this->assertStringContainsString('missing', $failures[0]->message);
		}

		public function testNonNumericBaselineFailsClosed()
		{
			$failures = $this->policy()->failures(array($this->plugin('e107 2.0', '1.0.1')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_BASELINE_MALFORMED, $failures[0]->reason);
		}

		public function testStaleDateFailsAndNamesTheDateToWrite()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0.1', '2012-08-01', '2026-09-14')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_DATE_STALE, $failures[0]->reason);
			$this->assertStringContainsString('date="2026-09-14"', $failures[0]->message);
		}

		public function testADateAheadOfTheNewestChangePasses()
		{
			$this->assertSame(array(), $this->policy()->failures(
				array($this->plugin('1.0', '1.0.1', '2026-09-20', '2026-09-14'))));
		}

		public function testADateThatIsNotADateFails()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0.1', 'soon')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_DATE_STALE, $failures[0]->reason);
			$this->assertStringContainsString('date="2026-09-14"', $failures[0]->message);
		}

		public function testADateShapedButImpossibleFails()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0.1', '2026-13-45')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_DATE_STALE, $failures[0]->reason);
		}

		public function testABaselineThatDeclaresNoVersionFailsClosed()
		{
			$failures = $this->policy()->failures(array($this->plugin('', '1.0')));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_BASELINE_MALFORMED, $failures[0]->reason);
			$this->assertStringContainsString('no version', $failures[0]->message);
		}

		public function testMissingDateAttributeFails()
		{
			$failures = $this->policy()->failures(array($this->plugin('1.0', '1.0.1', null)));

			$this->assertCount(1, $failures);
			$this->assertSame(PluginVersionPolicy::REASON_DATE_STALE, $failures[0]->reason);
			$this->assertStringContainsString('date="2026-09-14"', $failures[0]->message);
		}

		public function testEveryPluginIsJudgedAndBothRulesCanFireAtOnce()
		{
			$failures = $this->policy()->failures(array(
				new ChangedPlugin('news', '1.0', '1.0', '2012-08-01', '2026-09-14'),
				new ChangedPlugin('forum', '2.1', '2.2', '2026-09-14', '2026-09-14'),
				new ChangedPlugin('pm', '3.0', '3.0', '2026-09-14', '2026-09-14'),
			));

			$this->assertCount(3, $failures);
			$this->assertSame('news', $failures[0]->folder);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_NOT_BUMPED, $failures[0]->reason);
			$this->assertSame('news', $failures[1]->folder);
			$this->assertSame(PluginVersionPolicy::REASON_DATE_STALE, $failures[1]->reason);
			$this->assertSame('pm', $failures[2]->folder);
			$this->assertSame(PluginVersionPolicy::REASON_VERSION_NOT_BUMPED, $failures[2]->reason);
		}
	}
