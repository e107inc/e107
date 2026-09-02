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

	public function _after()
	{
		$db_impl = $this->getDbImplementation();
		if (@empty($db_impl->server_info)) return;

		parent::_after();
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

	/**
	 * @see https://github.com/e107inc/e107/issues/6040
	 */
	public function testBackupWithoutPdoRecordsAnErrorNumber()
	{
		$this->assertFalse($this->db->backup(),
			'precondition: the mysqli backend cannot take a backup');
		$this->assertSame(-1, $this->db->getLastErrorNumber(),
			'a refusal that carries no driver number has to report -1');
		$this->assertNotSame('', $this->db->getLastErrorText(),
			'a refused backup has to say why');
	}

	public function testDb_Close()
	{
		$db_impl = $this->getDbImplementation();
		$this->assertFalse(@empty($db_impl->server_info));
		$this->db->db_Close();
		$this->assertTrue(@empty($db_impl->server_info));
	}

	private function getDbImplementation()
	{
		$db_property = new \e107\Reflection\ReflectionProperty($this->db, 'mySQLaccess');
		return $db_property->getValue($this->db);
	}
}
