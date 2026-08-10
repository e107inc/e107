<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Regression test for issue #5606: lan_print.php must not define PAGE_NAME
 * as a side effect. Doing so would leak "Printer Friendly" into the <title>
 * of any unrelated page that loads emailprint_class.php transitively
 * (e.g. via {PRINTICON} on a news widget) when the page itself has not
 * defined PAGE_NAME first.
 */


class lanPrintTest extends \Codeception\Test\Unit
{

	public function testLanPrintDoesNotCarryPAGE_NAME()
	{
		$path = e_LANGUAGEDIR . 'English/lan_print.php';
		$this->assertFileExists($path);

		// Let e107 be the first to reach the file. lan_print.php defines
		// nothing itself, it returns its terms and e107::includeLan() turns
		// them into constants; but includeLan() reads the file with
		// include_once, so a plain include here first would hand it true
		// rather than the array and no LAN_PRINT_* constant could be defined
		// for the rest of the process. Once the constants exist, reading the
		// file again for the array below costs nothing.
		e107::includeLan($path);

		$terms = include $path;

		$this->assertIsArray(
			$terms,
			'lan_print.php must return an array (v2 language pack form)'
		);
		$this->assertArrayNotHasKey(
			'PAGE_NAME',
			$terms,
			'lan_print.php must not define PAGE_NAME as a side effect; '
			. 'see issue #5606. The print page should set its own title via '
			. 'e107::title() instead.'
		);
	}

}
