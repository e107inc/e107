<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A page that took less time than the footer's statistics round to, in all or in the database, still gets the rest of its footer.
 */
class footerRenderTimeTest extends \Test\Unit
{
	public function testAFrontEndPageRenderedInUnderFiveMillisecondsIsFinished()
	{
		$this->assertFinished($this->renderInBootedCli('news.php', $this->oneMillisecondClock()));
	}

	public function testAnAdminPageRenderedInUnderFiveMillisecondsIsFinished()
	{
		$this->assertFinished($this->renderInBootedCli('e107_admin/db.php', $this->oneMillisecondClock()));
	}

	public function testTheTimeProfileOfAPageWithUnderFiveMillisecondsOfDatabaseTimeIsShown()
	{
		list($output) = $this->runInBootedCliDefining(array('E107_DEBUG_LEVEL' => 256),
			"error_reporting(E_ALL); \$GLOBALS['db_time'] = number_format(0.004, 2); echo e107::getDebug()->Show_Performance(), '@@SHOWN@@';");
		$page = implode("\n", $output);

		$this->assertStringNotContainsString('Division by zero', $page);
		$this->assertStringContainsString('@@SHOWN@@', $page, "The time profile stopped part way:\n".$page);
	}

	/**
	 * @return string PHP that makes every page report it took one millisecond to render
	 */
	private function oneMillisecondClock()
	{
		$seed = "error_reporting(E_ALL); ";
		$seed .= "class e107_traffic_one_millisecond extends e107_traffic { public function TimeDelta(\$tStart, \$tFinish) { return 0.001; } } ";
		$seed .= "e107::setRegistry('core/e107/singleton/e107_traffic', new e107_traffic_one_millisecond()); ";

		return $seed;
	}

	/**
	 * @param string $page
	 * @return void
	 */
	private function assertFinished($page)
	{
		$this->assertStringNotContainsString('Division by zero', $page);
		$this->assertStringContainsString('</html>', $page, "The page stopped before its footer finished:\n".$page);
	}
}
