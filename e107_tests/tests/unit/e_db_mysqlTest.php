<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e_db_mysqlTest extends e_db_abstractTest
{
	protected function makeDb()
	{
		return $this->make('e_db_mysql');
	}

	protected function _before()
	{
		require_once(e_HANDLER."mysql_class.php");
		try
		{
			$this->db = $this->makeDb();
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't load e_db_mysql object");
		}


		// Simulate PHP 5.6
		defined('MYSQL_ASSOC') or define('MYSQL_ASSOC', 1);
		defined('MYSQL_NUM') or define('MYSQL_NUM', 2);
		defined('MYSQL_BOTH') or define('MYSQL_BOTH', 3);
		$this->db->__construct();
		$this->loadConfig();

		$this->db->db_Connect(
			$this->dbConfig['mySQLserver'],
			$this->dbConfig['mySQLuser'],
			$this->dbConfig['mySQLpassword'],
			$this->dbConfig['mySQLdefaultdb']
		);
	}

	public function testGetPDO()
	{
		$result = $this->db->getPDO();
		$this->assertFalse($result);
	}

	public function testGetServerInfo()
	{
		$result = $this->db->getServerInfo();
		$this->assertRegExp('/[0-9]+\./', $result);
	}

	public function testGetLastErrorNumber()
	{
		$this->db->select('doesnt_exists');
		$result = $this->db->getLastErrorNumber();
		$this->assertEquals("1146", $result);
	}

	public function testEscape()
	{
		$result = $this->db->escape(123);
		$this->assertEquals(123,$result);

		$result = $this->db->escape("Can't", true);
		$this->assertEquals("Can\'t", $result);
	}
}
