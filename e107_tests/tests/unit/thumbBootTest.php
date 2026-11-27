<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * thumb.php booting against the test site's own e107_config.php, which sets only the folders the installer writes.
 *
 * @see https://github.com/e107inc/e107/issues/6815
 */
class thumbBootTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	public function testAFolderTheConfigLeavesOutIsNotReportedUndefined()
	{
		$php = "error_reporting(E_ALL); require('".addslashes(APP_PATH.'/thumb.php')."');";

		list($output) = $this->runInCli($php, '-d error_log=/dev/stderr');
		$printed = implode("\n", $output);

		self::assertStringContainsString('<svg', $printed,
			"thumb.php never answered with its placeholder, so its boot was not measured:\n".$printed);

		self::assertReadNoUndefinedVariable('[A-Z]+_DIRECTORY', $printed,
			"thumb.php read a folder variable the config does not set:\n".$printed);
	}
}
