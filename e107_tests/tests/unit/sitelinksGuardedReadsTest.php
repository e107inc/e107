<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * sitelinks reads things its callers are not obliged to have supplied, starting
 * with LINKDISPLAY, which only sitelinks.sc defines and which a theme calling
 * get() directly never does.
 *
 * The case runs in a subprocess. A constant cannot be undefined in-process, so
 * an in-process LINKDISPLAY test would only pass while nothing else in the run
 * had defined it, and below PHP 8 the read is a notice rather than a fatal, so
 * what separates the guarded source from the unguarded one is the diagnostic
 * text the child writes under E_ALL rather than its exit status.
 */
class sitelinksGuardedReadsTest extends \Test\Unit
{
	const DONE = '@@e107help-returned@@';

	/**
	 * Runs $php in a booted CLI process that reports everything, and returns what it wrote.
	 *
	 * The level is raised again inside the child because class2.php lowers CLI reporting
	 * to E_ALL & ~E_NOTICE, which is the class of diagnostic these cases measure.
	 *
	 * @param string $php statements to run once class2.php has booted
	 * @return string stdout and stderr interleaved
	 */
	private function probe($php)
	{
		list($output, $status) = $this->runInBootedCli('error_reporting(E_ALL); '.$php." echo '".self::DONE."';");

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the call never returned:\n".$printed);
		self::assertStringContainsString(self::DONE, $printed, "the call never returned:\n".$printed);

		return $printed;
	}

	/**
	 * LINKCLASS_HILITE takes get() off both sides of its cache, which otherwise returns
	 * before the read under test.
	 */
	public function testGetRendersWithoutLinkdisplayDefined()
	{
		$printed = $this->probe("define('LINKCLASS_HILITE', 'active'); e107::getSitelinks()->get(1);");

		self::assertDoesNotMatchRegularExpression('/undefined constant/i', $printed,
			"get() is public and a theme calling it defines no LINKDISPLAY:\n".$printed);
	}
}
