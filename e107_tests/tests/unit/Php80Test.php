<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Floor coverage for the vendored, downgraded symfony/polyfill-php80.
 *
 * The polyfill is registered in autoload_files.php, so it loads on every
 * request, and its preg_last_error_msg() switch names its constants in fully
 * qualified form. A fully qualified undefined constant is a fatal below PHP
 * 7.0, not the notice an unqualified one gets, so one case arm naming a
 * constant that postdates the floor kills the request on every call, a
 * successful match included. Parsing the tree under PHP 5.6 does not run it,
 * which is why only an executing test catches this shape.
 */
class Php80Test extends \Codeception\Test\Unit
{
	protected function _before()
	{
		if (!function_exists('preg_last_error_msg'))
		{
			$this->markTestSkipped('symfony/polyfill-php80 is not autoloadable');
		}
	}

	public function testPregLastErrorMsgSurvivesASuccessfulMatch()
	{
		$this->assertSame(1, preg_match('/^e107$/', 'e107'));
		$this->assertSame('No error', preg_last_error_msg());
	}
}
