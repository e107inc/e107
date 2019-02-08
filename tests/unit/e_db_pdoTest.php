<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_db_pdoTest extends \Codeception\Test\Unit
	{

		/** @var e_db_pdo  */
		protected $db;

		protected function _before()
		{
			require_once(e_HANDLER."e_db_interface.php");
			require_once(e_HANDLER."e_db_legacy_trait.php");
			require_once(e_HANDLER."e_db_pdo_class.php");
			try
			{
				$this->db = $this->make('e_db_pdo');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_db_pdo object");
			}

			$this->db->__construct();

		}

		public function testGetPDO()
		{
			$result = $this->db->getPDO();
			$this->assertTrue($result);
		}
/*
		public function testGetMode()
		{

		}

		public function testDb_Connect()
		{

		}*/

		/**
		 * TODO
		 */
		public function testConnect()
		{

			// $this->db->connect();
		}

		/**
		 * Test primary methods against a secondary database (ensures mysqlPrefix is working correctly)
		 * TODO Split into separate methods? Order needs to be respected.
		 */
		 /*
		public function testSecondaryDatabase()
		{
			$xql = e107::getDb('newdb');
			$config =  e107::getMySQLConfig();

			$database = 'e107_tests_tmp';
			$table = 'test';

			// cleanup
			$xql->gen("DROP DATABASE `".$database."`");

			// create database
			if($xql->gen("CREATE DATABASE ".$database." CHARACTER SET `utf8`"))
			{
				$xql->gen("GRANT ALL ON `".$database."`.* TO ".$config['mySQLuser']."@'".$config['mySQLserver']."';");
				$xql->gen("FLUSH PRIVILEGES;");
			}
			else
			{
				$this->fail("Failed to create secondary database");
			}

			// use new database
			$use = $xql->database($database,MPREFIX,true);

			if($use === false)
			{
				$this->fail("Failed to select new database");
			}

			$create = "CREATE TABLE `".$database."`.".MPREFIX.$table." (
					 `test_id` int(4) NOT NULL AUTO_INCREMENT,
					 `test_var` varchar(255) NOT NULL,
					 PRIMARY KEY (`test_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";

			// create secondary database
			if(!$xql->gen($create))
			{
				$this->fail("Failed to create table in secondary database");
			}


			// Insert
			$arr = array('test_id'=>0, 'test_var'=>'Example insert');
			if(!$xql->insert($table, $arr))
			{
				$err = $xql->getLastErrorText();
				$this->fail("Failed to insert into secondary database: ".$err);
			}

			// Copy Row.
			if(!$copied = $xql->db_CopyRow($table, '*', "test_id = 1"))
			{
				$err = $xql->getLastErrorText();
				$this->fail("Failed to copy row into secondary database table: ".$err);
			}


			// Select
			if(!$xql->select($table,'*','test_id !=0'))
			{
				$err = $xql->getLastErrorText();
				$this->fail("Failed to select from secondary database: ".$err);
			}


			// fetch
			$row = $xql->fetch();
			$this->assertNotEmpty($row['test_var'], "Failed to fetch from secondary database");
			$this->assertEquals('Example insert',$row['test_var'], $xql->getLastErrorText());


			// update
			$upd = array('test_var' => "Updated insert",'WHERE' => "test_id = 1");
			if(!$xql->update($table,$upd))
			{
			    $err = $xql->getLastErrorText();
				$this->fail("Failed to update secondary database table: ".$err);
			}

			// update (legacy)
			$upd2 = $xql->update($table, "test_var = 'Updated legacy' WHERE test_id = 1");
			$this->assertNotEmpty($upd2, "UPDATE (legacy) failed on secondary database table: ".$xql->getLastErrorText());


			// primary database retrieve
			$username = e107::getDb()->retrieve('user','user_name', 'user_id  = 1');
			$this->assertNotEmpty($username, "Lost connection with primary database.");


			// count
			$count = $xql->count($table, "(*)", "test_id = 1");
			$this->assertNotEmpty($count, "COUNT failed on secondary database table: ".$xql->getLastErrorText());

			// delete
			if(!$xql->delete($table, "test_id = 1"))
			{
				$err = $xql->getLastErrorText();
				$this->fail("Failed to delete secondary database table row: ".$err);
			}

			// Truncate & isEmpty
			$xql->truncate($table);
			$empty = $xql->isEmpty($table);
			$this->assertTrue($empty,"isEmpty() or truncate() failed");



		}*/


/*
		public function testGetServerInfo()
		{

		}

		public function testDatabase()
		{

		}

		public function testGetConfig()
		{

		}

		public function testDb_Mark_Time()
		{

		}

		public function testDb_Show_Performance()
		{

		}

		public function testDb_Write_log()
		{

		}
*/
		public function testDb_Query()
		{


			$userp = "3, 'Display Name', 'Username', '', 'password-hash', '', 'email@address.com', '', '', 0, ".time().", 0, 0, 0, 0, 0, '127.0.0.1', 0, '', 0, 1, '', '', '0', '', ".time().", ''";
			$this->db->db_Query("REPLACE INTO ".MPREFIX."user VALUES ({$userp})" );

			$res = $this->db->db_Query("SELECT user_email FROM ".MPREFIX."user WHERE user_id = 3");
			$result = $res->fetch();
			$this->assertEquals('email@address.com', $result['user_email']);

		}

/*
		public function testRetrieve()
		{

		}

		public function testSelect()
		{

		}

		public function testDb_Select()
		{

		}
*/
		public function testInsert()
		{
			$actual = $this->db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test insert'));
			$this->assertEquals(1, $actual, 'Unable to add record to table #tmp');
		}
/*
		public function testLastInsertId()
		{

		}

		public function testFoundRows()
		{

		}

		public function testRowCount()
		{

		}
*/
		public function testDb_Insert()
		{
			$actual = $this->db->db_Insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2'));
			$this->assertTrue($actual);
		}
/*
		public function testReplace()
		{

		}

		public function testDb_Replace()
		{

		}
*/
		public function testUpdate()
		{
			$db = $this->db;

			$db->delete('tmp');

			// Test 1
			$expected = $db->update('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 1', 'WHERE' => 'tmp_ip="127.0.0.1"'));
			$this->assertEmpty($expected, "Test 1 update() failed (not empty {$expected})");

			// Test 2
			$actual = $db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2'));
			$expected = $db->update('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2a', 'WHERE' => 'tmp_ip="127.0.0.1"'));
			$this->assertEquals(1, $expected, "Test 2 update() failed ({$actual} != {$expected}");

			// Test 3
			$expected = $db->update('tmp', 'tmp_ip = "127.0.0.1", tmp_time = tmp_time + 1, tmp_info = "test 3" WHERE tmp_ip="127.0.0.1"');
			$this->assertEquals(1, $expected, "Test 3 update() failed ({$actual} != {$expected}");

		}

		public function testDb_Update()
		{
			$this->db->delete('tmp', "tmp_ip = '127.0.0.1'");
			$this->db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2'));
			$actual = $this->db->db_Update('tmp', 'tmp_ip = "127.0.0.1", tmp_time = tmp_time + 1, tmp_info = "test 3" WHERE tmp_ip="127.0.0.1"');
			$this->assertEquals(1,$actual);
		}
/*
		public function test_getTypes()
		{

		}

		public function test_getFieldValue()
		{

		}

		public function testDb_UpdateArray()
		{

		}

		public function testTruncate()
		{

		}

		public function testFetch()
		{

		}

		public function testDb_Fetch()
		{

		}

		public function testCount()
		{

		}
*/
		public function testDb_Count()
		{
			$result = $this->db->db_Count('user','(*)', 'user_id = 1');
			$this->assertEquals(1,$result);
		}
/*
		public function testClose()
		{

		}

		public function testDb_Close()
		{

		}
*/
		public function testDelete()
		{
			$expected = $this->db->count('tmp');
			$actual = $this->db->delete('tmp');
			$this->assertEquals($expected, $actual, 'Unable to delete all records.');

		}

		public function testDb_Delete()
		{
			$expected = $this->db->count('tmp');
			$actual = $this->db->db_Delete('tmp');
			$this->assertEquals($expected, $actual, 'Unable to delete all records.');
		}
/*
		public function testDb_Rows()
		{

		}

		public function testDb_SetErrorReporting()
		{

		}

		public function testGen()
		{

		}

		public function testDb_Select_gen()
		{

		}

		public function testMl_check()
		{

		}

		public function testDb_IsLang()
		{

		}

		public function testDb_getList()
		{

		}

		public function testRows()
		{

		}

		public function testMax()
		{

		}

		public function testSelectTree()
		{

		}

		public function testDb_QueryCount()
		{

		}

		public function testDb_Query_all()
		{

		}

		public function testDb_FieldList()
		{

		}

		public function testDb_Field()
		{

		}

		public function testColumnCount()
		{

		}
*/
		public function testField()
		{
			$result = $this->db->field('plugin', 'plugin_path');
			$this->assertTrue($result);
		}
/*
		public function testEscape()
		{

		}

		public function testDb_Table_exists()
		{

		}

		public function testIsTable()
		{

		}

		public function testIsEmpty()
		{

		}

		public function testDb_ResetTableList()
		{

		}

		public function testDb_TableList()
		{

		}

		public function testTables()
		{

		}

		public function testDb_CopyRow()
		{

		}

		public function testDb_CopyTable()
		{

		}
*//*
		public function testBackup()
		{
			$opts = array(
				'gzip'      => false,
				'nologs'    => false,
				'droptable' => false,
			);

			$result = $this->db->backup('user,core_media_cat', null, $opts);

			$tmp = file_get_contents($result);

			$this->assertNotContains("DROP TABLE IF EXISTS `e107_user`;", $tmp);
			$this->assertContains("CREATE TABLE `e107_user` (", $tmp);
			$this->assertContains("INSERT INTO `e107_user` VALUES (1", $tmp);
			$this->assertContains("CREATE TABLE `e107_core_media_cat`", $tmp);


		}*/
/*
		public function testDbError()
		{

		}

		public function testGetLastErrorNumber()
		{

		}

		public function testGetLastErrorText()
		{

		}

		public function testResetLastError()
		{

		}

		public function testGetLastQuery()
		{

		}

		public function testDb_Set_Charset()
		{

		}

		public function testGetFieldDefs()
		{

		}*/
	}
