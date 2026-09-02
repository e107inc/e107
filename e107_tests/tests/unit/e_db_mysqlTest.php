<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Reflection\ReflectionProperty;

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
	    if (!empty($db_impl->server_info))
	    {
	        $this->db->db_Close();
	        // Reading a property off a closed mysqli triggers a PHP 5.6
	        // warning ("Couldn't fetch mysqli") that Codeception 4.x
	        // promotes to a test error. Suppress with @ so the property
	        // access still returns the empty value we are asserting on.
	        self::assertTrue(@empty($db_impl->server_info));
	    }
	    else
	    {
	        self::assertTrue(true); // Connection is already closed, so the test passes
	    }
	}

	private function getDbImplementation()
	{
		$db_property = new ReflectionProperty($this->db, 'mySQLaccess');
		return $db_property->getValue($this->db);
	}

	/**
	 * e_db_mysql::select()/count()/delete()/fields() interpolate the table name
	 * unquoted into the FROM/DELETE clause, so a hostile $table must fail closed
	 * (return false) before any SQL is executed. Covers the _safeIdentifier()
	 * guards added to those four entry points in mysql_class.php.
	 */
	public function testCrudEntryPointsRejectHostileTableIdentifier()
	{
		$db = $this->db;

		// A payload that would break out of the unquoted FROM/DELETE clause.
		$hostile = "tmp' UNION SELECT user_password FROM `".MPREFIX."user`; -- ";

		$this->assertFalse($db->select($hostile, '*', 'tmp_id = 1'),
			'select() must reject a hostile table identifier');
		$this->assertFalse($db->count($hostile, '(*)', 'tmp_id = 1'),
			'count() must reject a hostile table identifier');
		$this->assertFalse($db->delete($hostile, 'tmp_id = 1'),
			'delete() must reject a hostile table identifier');
		$this->assertFalse($db->fields($hostile),
			'fields() must reject a hostile table identifier');

		// A plain table-name fragment with a trailing statement is equally rejected.
		$this->assertFalse($db->select('user; DROP TABLE x'),
			'select() must reject identifier-injection attempts');
		$this->assertFalse($db->count('user; DROP TABLE x'),
			'count() must reject identifier-injection attempts');
		$this->assertFalse($db->delete('user; DROP TABLE x'),
			'delete() must reject identifier-injection attempts');

		// Valid identifiers still work, confirming the guard is not over-strict.
		$this->assertNotFalse($db->select('user', 'user_id', 'user_id = 1'),
			'select() must still accept a valid table identifier');
		$this->assertSame(1, (int) $db->count('user', '(*)', 'user_id = 1'),
			'count() must still accept a valid table identifier');
		$this->assertNotFalse($db->fields('user'),
			'fields() must still accept a valid table identifier');

		// count()'s documented 'generic' raw-query escape hatch ($table holds the
		// full query) is intentionally exempt from the identifier guard.
		$this->assertSame(
			1,
			(int) $db->count("SELECT COUNT(*) FROM `".MPREFIX."user` WHERE user_id = 1", 'generic'),
			"count() 'generic' raw-query escape hatch must still work"
		);
	}

	/**
	 * @dataProvider entryPointsGuardingTheTableIdentifier
	 * @see https://github.com/e107inc/e107/issues/6040
	 */
	public function testARefusedTableIdentifierRecordsAnErrorNumber($method)
	{
		$this->db->resetLastError();

		$this->assertFalse($this->db->$method('user; DROP TABLE x'),
			$method.'() has to refuse a hostile table identifier');
		$this->assertSame(-1, $this->db->getLastErrorNumber(),
			$method.'() refuses before the server sees anything, which the contract spells -1');
		$this->assertStringContainsString($method, $this->db->getLastErrorText(),
			$method.'() has to say which operation refused, and name itself correctly');
	}

	/**
	 * One case per entry point, so a revert run reds each of them separately rather than stopping at the first.
	 *
	 * @return array
	 */
	public function entryPointsGuardingTheTableIdentifier()
	{
		return array(
			'select' => array('select'),
			'count'  => array('count'),
			'delete' => array('delete'),
			'fields' => array('fields'),
		);
	}

	/**
	 * e_db_mysql::db_Set_Charset() interpolates the charset into
	 * "SET NAMES `$charset`", so a backtick or quote would break out of the
	 * identifier context. The guard must reject any non-plain charset token.
	 */
	public function testSetCharsetRejectsHostileCharset()
	{
		$db = $this->db;

		$this->assertSame('Invalid charset', $db->db_Set_Charset('utf8mb4`; DROP TABLE x; -- '),
			'db_Set_Charset() must reject a charset containing a backtick');
		$this->assertSame('Invalid charset', $db->db_Set_Charset("utf8' OR '1'='1"),
			'db_Set_Charset() must reject a charset containing a quote');

		// A plain charset token is accepted (empty message, no error).
		$this->assertSame('', $db->db_Set_Charset('utf8mb4'),
			'db_Set_Charset() must still accept a plain charset token');
	}
}
