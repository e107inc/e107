<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class e_file_inspectorTest extends \Codeception\Test\Unit
{
	/**
	 * @var e_file_inspector_json
	 */
	private $e_integrity;

	public function _before()
	{
		require_once(e_HANDLER."e_file_inspector_json.php");
		$this->e_integrity = new e_file_inspector_json();
	}

	public function testGetChecksums()
	{
		$checksums = $this->e_integrity->getChecksums("e107_admin/e107_update.php");
		$this->assertIsArray($checksums);
		$this->assertNotEmpty($checksums);

		$checksums = $this->e_integrity->getChecksums("e107_handlers/nonexistent.php");
		$this->assertIsArray($checksums);
		$this->assertEmpty($checksums);
	}
}
