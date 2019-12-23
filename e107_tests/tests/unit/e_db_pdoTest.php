<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e_db_pdoTest extends e_db_abstractTest
{
	protected function makeDb()
	{
		return $this->make('e_db_pdo');
	}

	protected function _before()
	{
		require_once(e_HANDLER."e_db_interface.php");
		require_once(e_HANDLER."e_db_legacy_trait.php");
		require_once(e_HANDLER."e_db_pdo_class.php");
		try
		{
			$this->db = $this->makeDb();
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't load e_db_pdo object");
		}

		$this->db->__construct();
		$this->loadConfig();

	}

	public function testGetCharSet()
	{
		$this->db->setCharset();
		$result = $this->db->getCharset();

		$this->assertEquals('utf8', $result);
	}
}
