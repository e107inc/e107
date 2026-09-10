<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * sitelinks reads two things its callers are not obliged to have supplied:
 * LINKDISPLAY, which only sitelinks.sc defines and which a theme calling get()
 * directly never does, and the frontpage pref, which a hand-edited or
 * half-migrated pref row can leave absent or holding the pre-v2 bare string.
 *
 * Both cases run in a subprocess. A constant cannot be undefined in-process, so
 * an in-process LINKDISPLAY test would only pass while nothing else in the run
 * had defined it, and below PHP 8 neither read is fatal, so what separates the
 * guarded source from the unguarded one is the diagnostic text the child writes
 * under E_ALL rather than its exit status.
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

	/**
	 * The two shapes core cannot produce but a pref row can still hold, answered against
	 * the empty array the guard makes them equivalent to and against a front page the
	 * current request satisfies, which is built from e_SELF so that the match does not
	 * depend on what a CLI boot makes of the site URL.
	 */
	public function testHiliteAnswersOnAFrontpagePrefThatIsNotAnArray()
	{
		$php = "\$sl = e107::getSitelinks(); \$cfg = e107::getConfig('core'); \$link = e_HTTP.'index.php'; "
			."\$uc = current(explode(',', USERCLASS_LIST)); "
			."\$cfg->setPref('frontpage', array()); \$answers = array(var_export(\$sl->hilite(\$link, true), true)); "
			."\$cfg->setPref('frontpage', 'news.php'); \$answers[] = var_export(\$sl->hilite(\$link, true), true); "
			."\$cfg->removePref('frontpage'); \$answers[] = var_export(\$sl->hilite(\$link, true), true); "
			."\$cfg->setPref('frontpage', array(\$uc => e_SELF)); \$answers[] = var_export(\$sl->hilite(\$link, true), true); "
			."echo '<<'.implode('|', \$answers).'>>'; ";

		$printed = $this->probe($php);
		$matches = array();

		self::assertDoesNotMatchRegularExpression('/count\(\)|foreach|TypeError|frontpage/i', $printed,
			"the frontpage pref is counted and walked without being an array:\n".$printed);

		self::assertSame(1, preg_match('/<<(.*)>>/s', $printed, $matches), "the probe printed no answers:\n".$printed);

		$answers = explode('|', $matches[1]);

		self::assertSame('true', $answers[3], 'the guard must not switch the home highlight off');
		self::assertNotSame($answers[3], $answers[0], 'an empty frontpage highlights nothing');
		self::assertSame($answers[0], $answers[1], 'the pre-v2 string has to answer as the empty array does');
		self::assertSame($answers[0], $answers[2], 'an absent pref has to answer as the empty array does');
	}
}
