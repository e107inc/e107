<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * upload.php fills the fields of its form from $_POST, and a visitor who has
 * only just opened the page has posted nothing at all.
 *
 * The page is an entry script, so it is measured in a subprocess of its own,
 * which boots class2.php and renders through the theme. $_E107 stays empty
 * rather than saying cli, because the CLI flag takes the page off the theme and
 * HEADERF with it. An absent key is a warning on PHP 8 and a notice below it,
 * and neither ends the request, so what separates the guarded source from the
 * unguarded one is the diagnostic text the child writes under E_ALL rather than
 * its exit status.
 *
 * Every such child is e107's CLI superuser: class2.php defines USER and ADMIN
 * on the CLI SAPI whatever $_E107 holds, so the name and email fields that only
 * a guest is shown are beyond what this case can reach, and driving those needs
 * a browser. The six fields it does reach are the six the report listed.
 */
class uploadFormTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const DONE = '@@e107help-returned@@';

	/**
	 * The last field of the form is asserted as well as the first, because the
	 * form leaves at an empty file type list one read into the six.
	 */
	public function testTheFormRendersWithoutReadingUnsetPostKeys()
	{
		$system = str_replace(realpath(APP_PATH).'/', '', realpath(e_SYSTEM).'/');

		$this->getModule('\Helper\Unit')->writeAppFile($system.'filetypes.xml', '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="public" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="2M" />
</e107Filetypes>');

		$php = "restore_error_handler(); error_reporting(E_ALL); ini_set('display_errors', 1); "
			."\$_POST = array(); "
			."\$pref['upload_enabled'] = 1; \$pref['upload_class'] = e_UC_PUBLIC; "
			."e107::getConfig('core')->setPref('upload_class', e_UC_PUBLIC); "
			."require '".addslashes(APP_PATH)."/upload.php'; "
			."echo '".self::DONE."';";

		list($output, $status) = $this->runInBootedCli($php, '', array());

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the page never returned:\n".$printed);
		self::assertStringContainsString(self::DONE, $printed, "the page never returned:\n".$printed);

		self::assertStringContainsString("name='category'", $printed,
			"the form never reached its category selector:\n".$printed);
		self::assertStringContainsString("name='file_demo'", $printed,
			"the form stopped short of its last field, leaving the reads after the first untested:\n".$printed);

		self::assertSame(0, preg_match('/Undefined (array key|index|variable)[^\n]*upload\.php/i', $printed),
			"an unsubmitted form has no posted value to read back:\n".$printed);
	}
}
