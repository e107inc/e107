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
		protected $dbConfig = array();

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

			define('e_LEGACY_MODE', true);
			$this->db->__construct();
			$this->loadConfig();

		}

		private function loadConfig()
		{
			/** @var Helper\DelayedDb $db */
			try
			{
				$db = $this->getModule('\Helper\DelayedDb');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load eHelper\DelayedDb object");
			}

			$config = array();
			$config['mySQLserver']      = $db->_getDbHostname();
			$config['mySQLuser']        = $db->_getDbUsername();
			$config['mySQLpassword']    = $db->_getDbPassword();
			$config['mySQLdefaultdb']   = $db->_getDbName();

			$this->dbConfig = $config;
		}

		public function testGetPDO()
		{
			$result = $this->db->getPDO();
			$this->assertTrue($result);
		}




		public function testGetMode()
		{
			$actual = $this->db->getMode();
			$this->assertEquals('NO_ENGINE_SUBSTITUTION', $actual);
		}

		public function testDb_Connect()
		{
			$result = $this->db->db_Connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], $this->dbConfig['mySQLpassword'], $this->dbConfig['mySQLdefaultdb']);
			$this->assertTrue($result);
		}

		public function testGetServerInfo()
		{

			$result = $this->db->getServerInfo();
			$this->assertNotContains('?',$result);
		}

		/**
		 * connect() test.
		 */
		public function testConnect()
		{
			$result = $this->db->connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], "wrong Password");
			$this->assertFalse($result);

			$result = $this->db->connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], $this->dbConfig['mySQLpassword']);
			$this->assertTrue($result);

			$result = $this->db->connect($this->dbConfig['mySQLserver'].":3306", $this->dbConfig['mySQLuser'], $this->dbConfig['mySQLpassword']);
			$this->assertTrue($result);
		}








		public function testDatabase()
		{
			$this->db->connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], $this->dbConfig['mySQLpassword']);
			$result = $this->db->database($this->dbConfig['mySQLdefaultdb']);

			$this->assertTrue($result);

			$result = $this->db->database("missing_database");
			$this->assertFalse($result);

			$result = $this->db->database($this->dbConfig['mySQLdefaultdb'], MPREFIX,  true);
			$this->assertTrue($result);
			$this->assertEquals("`".$this->dbConfig["mySQLdefaultdb"]."`.".\Helper\Unit::E107_MYSQL_PREFIX,
				$this->db->mySQLPrefix);



		}

		public function testGetCharSet()
		{
			$this->db->setCharset();
			$result = $this->db->getCharset();

			$this->assertEquals('utf8', $result);
		}

/*
		public function testGetConfig()
		{

		}
*/
		public function testDb_Mark_Time()
		{
			$this->db->debugMode(true);

			$this->db->db_Mark_Time("Testing");

			$actual = e107::getDebug()->getTimeMarkers();

			$this->assertIsArray($actual);
			$this->assertEquals('Testing', $actual[1]['What']);
			$this->assertArrayHasKey('Index', $actual[1]);
			$this->assertArrayHasKey('Time', $actual[1]);
			$this->assertArrayHasKey('Memory', $actual[1]);

			$this->db->debugMode(false);
			$result = $this->db->db_Mark_Time("Testing");
			$this->assertNull($result);


		}


		public function testDb_IsLang()
		{
			$result = $this->db->db_IsLang('news', false);
			$this->assertEquals('news', $result);

			$this->db->copyTable('news','lan_spanish_news',true, true);

			e107::getConfig()->set('multilanguage',true)->save();

			$this->db->setLanguage('Spanish');
			$this->db->resetTableList(); // reload the table list so it includes the copied table above.

			$result = $this->db->db_IsLang('news', false);
			$this->assertEquals('lan_spanish_news', $result);


			$result = $this->db->db_IsLang('news', true);
			$expected = array ('spanish' => array ('e107_news' => 'e107_lan_spanish_news', ),);
			$this->assertEquals($expected, $result);

			$this->db->setLanguage('English');

			$this->db->dropTable('lan_spanish_news');
		}



		public function testDb_Write_log()
		{
			$log_type = 127;
			$remark =  'e_db_pdoTest';
			$query = 'query goes here';

			$this->db->db_Write_log($log_type, $remark, $query);

			$data = $this->db->retrieve('dblog','dblog_title, dblog_user_id', "dblog_type = ".$log_type. " AND dblog_title = '".$remark ."' ");

			$expected = array (
			  'dblog_title' => 'e_db_pdoTest',
			  'dblog_user_id' => '1',
			);

			$this->assertEquals($expected, $data);
		}



		public function testDb_Query()
		{


			$userp = "3, 'Display Name', 'Username', '', 'password-hash', '', 'email@address.com', '', '', 0, ".time().", 0, 0, 0, 0, 0, '127.0.0.1', 0, '', 0, 1, '', '', '0', '', ".time().", ''";
			$this->db->db_Query("REPLACE INTO ".MPREFIX."user VALUES ({$userp})" );

			$res = $this->db->db_Query("SELECT user_email FROM ".MPREFIX."user WHERE user_id = 3");
			$result = $res->fetch();
			$this->assertEquals('email@address.com', $result['user_email']);

		}


		public function testRetrieve()
		{
			$expected = array ('user_id' => '1', 'user_name' => 'e107',	);
			$result = $this->db->retrieve('user', 'user_id, user_name', 'user_id = 1');
			$this->assertEquals($expected,$result);


			$this->db->select('user', 'user_id, user_name', 'user_id = 1');
			$result = $this->db->retrieve(null);
			$this->assertEquals($expected,$result);


			$expected = array ( 0 =>  array (   'user_id' => '1', 'user_name' => 'e107', ),);
			$result = $this->db->retrieve('user', 'user_id, user_name', 'user_id = 1', true);
			$this->assertEquals($expected,$result);


			$result = $this->db->retrieve("SELECT user_id, user_name FROM #user WHERE user_id = 1", true);
			$this->assertEquals($expected,$result);


			$expected = array();
			$result = $this->db->retrieve('missing_table', 'user_id, user_name', 'user_id = 1', true);
			$this->assertEquals($expected,$result);

			$this->db->select('plugin');
			$result = $this->db->retrieve(null, null, null, true);
			$this->assertArrayHasKey('plugin_name', $result[14]);

			$result = $this->db->retrieve('plugin', '*', null, true);

			$this->assertArrayHasKey('plugin_name', $result[14]);


		}

		public function testSelect()
		{

			$result = $this->db->select('user', 'user_id, user_name', 'user_id = 1');
			$this->assertEquals(1, $result);

			$result = $this->db->select('user', 'user_id, user_name', 'user_id = 999');
			$this->assertEquals(0, $result);

			$result = $this->db->select('user', 'user_id, user_name', 'WHERE user_id = 1', true);
			$this->assertEquals(1, $result);


		}

		public function testDb_Select()
		{
			$result = $this->db->db_Select('user', 'user_id, user_name', 'user_id = 1');
			$this->assertEquals(1, $result);

			$result = $this->db->db_Select('user', 'user_id, user_name', 'user_id = 999');
			$this->assertEquals(0, $result);

			$result = $this->db->db_Select('user', 'user_id, user_name', 'WHERE user_id = 1', true);
			$this->assertEquals(1, $result);
		}


		public function testDb_Select_gen()
		{
			$result = $this->db->db_Select_gen("UPDATE `#user` SET user_ip = '127.0.0.3' WHERE user_id = 1");
			$this->assertEquals(1,$result);

		}

		public function testInsert()
		{
			// Test 1
			$actual = $this->db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => '12345435', 'tmp_info' => 'Insert test'));
			$this->assertEquals(1, $actual, 'Unable to add record to table #tmp');

			// Test 2 Verify content
			$expected = array(
				'tmp_ip' => '127.0.0.1',
				'tmp_time' => '12345435',
				'tmp_info' => 'Insert test'
			);
			$actual = $this->db->retrieve('tmp', '*','tmp_ip = "127.0.0.1" AND tmp_time = 12345435');
			$this->assertEquals($expected, $actual, 'Inserted content doesn\'t match the retrieved content');
		}


		public function testIndex()
		{
			$result = $this->db->index('plugin', 'plugin_path');
			$this->assertTrue($result);

		}

		public function testLastInsertId()
		{
			$insert = array(
				'gen_id'    => 0,
				'gen_type'  => 'whatever',
				'gen_datestamp' => time(),
				'gen_user_id'   => 1,
				'gen_ip'        => '127.0.0.1',
				'gen_intdata'   => '',
				'gen_chardata'   => ''
				);
		
			$this->db->insert('generic', $insert);
			$actual = $this->db->lastInsertId();
			$this->assertGreaterThan(0,$actual);

		}

		public function testFoundRows()
		{
			$this->db->debugMode(false);
			$this->db->gen('SELECT SQL_CALC_FOUND_ROWS * FROM `#user` WHERE user_id = 1');
			$row = $this->db->fetch();
			$this->assertArrayHasKey('user_name', $row);
			$result = $this->db->foundRows();
			$this->assertEquals(1, $result);

		}

		public function testDb_Rows()
		{
			$this->db->retrieve('plugin', '*');
			$result = $this->db->db_Rows();

			$this->assertGreaterThan(10,$result);

		}

		public function testDb_Insert()
		{
			$actual = $this->db->db_Insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2'));
			$this->assertTrue($actual);
		}

		public function testReplace()
		{
			$insert = array(
				'gen_id'    => 1,
				'gen_type'  => 'whatever',
				'gen_datestamp' => time(),
				'gen_user_id'   => 1,
				'gen_ip'        => '127.0.0.1',
				'gen_intdata'   => '',
				'gen_chardata'   => ''
				);

			$result = $this->db->replace('generic', $insert);

			$this->assertNotEmpty($result);

		}

		public function testDb_Replace()
		{
			$insert = array(
				'gen_id'    => 1,
				'gen_type'  => 'whatever',
				'gen_datestamp' => time(),
				'gen_user_id'   => 1,
				'gen_ip'        => '127.0.0.1',
				'gen_intdata'   => '',
				'gen_chardata'   => ''
				);

			$result = $this->db->db_Replace('generic', $insert);

			$this->assertNotEmpty($result);
		}

		public function testUpdate()
		{
			$db = $this->db;

			$db->delete('tmp');

			// Test 1
			$expected = $db->update('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'Update test 1', 'WHERE' => 'tmp_ip="127.0.0.1"'));
			$this->assertEmpty($expected, "Test 1 update() failed (not empty {$expected})");

			$actual = 1;
			// Test 2
			$db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2'));
			$expected = $db->update('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => '1234567', 'tmp_info' => 'Update test 2a', 'WHERE' => 'tmp_ip="127.0.0.1"'));
			$this->assertEquals($expected, $actual, "Test 2 update() failed ({$actual} != {$expected}");

			// Test 3
			$expected = $db->update('tmp', 'tmp_ip = "127.0.0.1", tmp_time = tmp_time + 1, tmp_info = "Update test 3" WHERE tmp_ip="127.0.0.1"');
			$this->assertEquals($expected, $actual, "Test 3 update() failed ({$actual} != {$expected}");

			// Test 4: Verify content
			$expected = array(
				'tmp_ip' => '127.0.0.1',
				'tmp_time' => '1234568',
				'tmp_info' => 'Update test 3'
			);
			$actual = $this->db->retrieve('tmp', '*','tmp_ip = "127.0.0.1"');
			$this->assertEquals($expected, $actual, 'Test 4: Updated content doesn\'t match the retrieved content');

			// Test for Error response.
			$actual = $this->db->update('tmp', 'tmp_ip = "127.0.0.0 WHERE tmp_ip = "125.123.123.13"');
			$this->assertFalse($actual);

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
*/
		public function testDb_QueryCount()
		{
			$this->db->select('user', '*');
			$this->db->select('plugin','*');

			$result = $this->db->db_QueryCount();
			$this->assertEquals(2,$result);

		}


		public function testGetLastQuery()
		{
			$this->db->select('user');
			$result = $this->db->getLastQuery();
			$this->assertEquals("SELECT * FROM e107_user", $result);
		}


		public function testDb_UpdateArray()
		{

			$array = array(
				'user_comments' => 28,
			);

			$result = $this->db->db_UpdateArray('user', $array, ' WHERE user_id = 1');

			$this->assertEquals(1,$result);

			$actual = $this->db->retrieve('user', 'user_comments', 'user_id = 1');

			$expected = '28';

			$this->assertEquals($expected,$actual);

			$reset = array(
				'user_comments' => 0,
				'WHERE'=> "user_id = 1"
			);

			$this->db->update('user', $reset);


		}

		public function testTruncate()
		{
			$this->db->truncate('generic');

			$count = $this->db->count('generic');

			$this->assertEquals(0, $count);

		}

/*
		public function testFetch()
		{

		}
*/
		public function testDb_Fetch()
		{
			$this->db->select('user', '*', 'user_id = 1');
			$row = $this->db->db_Fetch();
			$this->assertArrayHasKey('user_ip', $row);

			$qry = 'SHOW CREATE TABLE `'.MPREFIX."user`";
			$this->db->gen($qry);

			$row = $this->db->db_Fetch('num');
			$this->assertEquals('e107_user', $row[0]);

			$check = (strpos($row[1], "CREATE TABLE `e107_user`") !== false);
			$this->assertTrue($check);

			$this->db->select('user', '*', 'user_id = 1');
			$row = $this->db->db_Fetch();
			$this->assertEquals("e107", $row['user_name']);
			$this->assertEquals("e107", $row[1]);

			// legacy tests
			$this->db->select('user', '*', 'user_id = 1');
			$row = $this->db->db_Fetch(MYSQL_ASSOC);
			$this->assertArrayHasKey('user_ip', $row);

			$qry = 'SHOW CREATE TABLE `'.MPREFIX."user`";
			$this->db->gen($qry);

			$row = $this->db->db_Fetch(MYSQL_NUM);
			$this->assertEquals('e107_user', $row[0]);

			$this->db->select('user', '*', 'user_id = 1');
			$row = $this->db->db_Fetch(MYSQL_BOTH);
			$this->assertEquals("e107", $row['user_name']);
			$this->assertEquals("e107", $row[1]);

		}


		public function testDb_Count()
		{
			$count = $this->db->count('user');
			$this->assertGreaterThan(0, $count);

			$result = $this->db->db_Count('user','(*)', 'user_id = 1');
			$this->assertEquals(1,$result);

			$result = $this->db->db_Count('SELECT COUNT(*) FROM '.MPREFIX.'plugin ','generic');
			$this->assertGreaterThan(20, $result);
		//var_dump($result);
			//$this->assertEquals(1,$result);
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
			// make sure the table is empty
			// table may contain data, so number of deleted records is unknown,
			// but should always be >= 0
			$actual = $this->db->delete('tmp');
			$this->assertGreaterThanOrEqual(0, $actual, 'Unable to empty the table.');

			// Check if the returned value is equal to the number of affected records
			$expected = $actual;
			$actual = $this->db->rowCount();
			$this->assertEquals($expected, $actual, "Number of deleted records is wrong ({$expected} != {$actual}");

			// Insert some records
			$this->db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'Delete test 1'));
			$this->db->insert('tmp', array('tmp_ip' => '127.0.0.2', 'tmp_time' => time(), 'tmp_info' => 'Delete test 2'));
			$this->db->insert('tmp', array('tmp_ip' => '127.0.0.3', 'tmp_time' => time(), 'tmp_info' => 'Delete test 3'));

			// Count records
			$expected = 3;
			$actual = $this->db->count('tmp');
			$this->assertEquals($expected, $actual, "Number of inserted records is wrong ({$expected} != {$actual}");

			// Delete 1 record
			$expected = 1;
			$actual = $this->db->delete('tmp', 'tmp_ip="127.0.0.1"');
			$this->assertEquals($expected, $actual, 'Unable to delete 1 records.');

			// Check if the returned value is equal to the number of affected records
			$expected = $actual;
			$actual = $this->db->rowCount();
			$this->assertEquals($expected, $actual, "Number of deleted records is wrong ({$expected} != {$actual}");

			// Delete all remaining (2) records
			$expected = 2;
			$actual = $this->db->delete('tmp');
			$this->assertEquals($expected, $actual, 'Unable to delete the remaining records.');

			// Check if the returned value is equal to the number of affected records
			$expected = $actual;
			$actual = $this->db->rowCount();
			$this->assertEquals($expected, $actual, "Number of deleted records is wrong ({$expected} != {$actual}");

			// Delete from an table that doesn't exist
			$actual = $this->db->delete('tmp_unknown_table');
			$this->assertFalse($actual, 'Trying to delete records from an invalid table should return FALSE!');
		}

		public function testDb_Delete()
		{
			$expected = $this->db->count('tmp');
			$actual = $this->db->db_Delete('tmp');
			$this->assertEquals($expected, $actual, 'Unable to delete all records.');
		}

/*
		public function testDb_SetErrorReporting()
		{
			$this->db->db_SetErrorReporting(false);
			// fixme - getErrorReporting.
		}


		public function testMl_check()
		{

		}


*/
		public function testDb_getList()
		{
			$this->db->select('plugin', '*');
			$rows = $this->db->db_getList();
			$this->assertArrayHasKey('plugin_name', $rows[2]);
		}
/*

		public function testMax()
		{

		}

		public function testSelectTree()
		{

		}

		public function testDb_Query_all()
		{

		}
*/
		public function testDb_FieldList()
		{
			$this->db->db_FieldList('user');

		}

		public function testDb_Field()
		{
			$result = $this->db->db_Field('plugin', 'plugin_path');
			$this->assertTrue($result);

			$result = $this->db->db_Field('plugin', 2);
			$this->assertEquals('plugin_version', $result);

		}

		public function testColumnCount()
		{
			$this->db->select('user');
			$result = $this->db->columnCount();
			$this->assertEquals(27, $result);

		}

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
*/
		public function testDb_Table_exists()
		{
			$result = $this->db->db_Table_exists('plugin');
			$this->assertTrue($result);

			$result = $this->db->db_Table_exists('plugin', 'French');
			$this->assertFalse($result);

			$result = $this->db->db_Table_exists('plugin', 'English');
			$this->assertFalse($result);
		}

		public function testIsEmpty()
		{
			$result = $this->db->isEmpty('plugin');
			$this->assertFalse($result);

			$result = $this->db->isEmpty('comments');
			$this->assertTrue($result);

			$result = $this->db->isEmpty();
			$this->assertFalse($result);

		}

		public function testDb_ResetTableList()
		{
			$this->db->Db_ResetTableList();
		}

		public function testDb_TableList()
		{
			$list = $this->db->db_TableList();

			$present = in_array('banlist', $list);
			$this->assertTrue($present);

			$list = $this->db->db_TableList('nologs');
			$present = in_array('admin_log', $list);
			$this->assertFalse($present);

			$list = $this->db->db_TableList('lan');
			$this->assertEmpty($list);
		}

		public function testTables()
		{
			$list = $this->db->tables();

			if(empty($list))
			{
				$error = $this->db->getLastQuery();
				$this->assertNotEmpty($list,"tables() didn't return a list of database tables.\n".$error);
			}

			$present = in_array('banlist', $list);
			$this->assertTrue($present);

			$list = $this->db->tables('nologs');
			$present = in_array('admin_log', $list);
			$this->assertFalse($present);

		}

		public function testDb_CopyRow()
		{
			$result = $this->db->db_CopyRow('news', '*', "news_id = 1");
			$this->assertGreaterThan(1,$result);

			$result = $this->db->db_CopyRow('bla');
			$this->assertFalse($result);

			$result = $this->db->db_CopyRow('bla', 'non_exist');
			$this->assertFalse($result);

			$result = $this->db->db_CopyRow(null);
			$this->assertFalse($result);

			$result = $this->db->db_CopyRow('news', null);
			$this->assertFalse($result);

		}

		public function testDb_CopyTable()
		{
			$this->db->db_CopyTable('news', 'news_bak', false, true);
			$result = $this->db->retrieve('news_bak', 'news_title', 'news_id = 1');

			$this->assertEquals('Welcome to e107', $result);


			$result = $this->db->db_CopyTable('non_exist', 'news_bak', false, true);
			$this->assertFalse($result);

		}


		public function testBackup()
		{
			$opts = array(
				'gzip'      => false,
				'nologs'    => false,
				'droptable' => false,
			);

			$result = $this->db->backup('user,core_media_cat', null, $opts);
			$uncompressedSize = filesize($result);

			$tmp = file_get_contents($result);

			$this->assertNotContains("DROP TABLE IF EXISTS `e107_user`;", $tmp);
			$this->assertContains("CREATE TABLE `e107_user` (", $tmp);
			$this->assertContains("INSERT INTO `e107_user` VALUES (1", $tmp);
			$this->assertContains("CREATE TABLE `e107_core_media_cat`", $tmp);

			$result = $this->db->backup('*', null, $opts);
			$size = filesize($result);
			$this->assertGreaterThan(100000,$size);

			$opts = array(
				'gzip'      => true,
				'nologs'    => false,
				'droptable' => false,
			);

			$result = $this->db->backup('user,core_media_cat', null, $opts);
			$compressedSize = filesize($result);
			$this->assertLessThan($uncompressedSize, $compressedSize);

			$result = $this->db->backup('missing_table', null, $opts);
			$this->assertFalse($result);

		}


		public function testGetLanguage()
		{
			$result = $this->db->getLanguage();
			$this->assertEquals('English', $result);

			$this->db->setLanguage('French');
			$result = $this->db->getLanguage();
			$this->assertEquals('French', $result);

		}
/*
		public function testDbError()
		{

		}
*/
		public function testGetLastErrorNumber()
		{
			$this->db->select('doesnt_exists');
			$result = $this->db->getLastErrorNumber();
			$this->assertEquals("42S02", $result);

		}

		public function testGetLastErrorText()
		{
			$this->db->select('doesnt_exists');
			$result = $this->db->getLastErrorText();

			$actual = (strpos($result,"doesn't exist")!== false );

			$this->assertTrue($actual);
		}

		public function testResetLastError()
		{
			$this->db->select('doesnt_exists');
			$this->db->resetLastError();

			$num = $this->db->getLastErrorNumber();
			$this->assertEquals(0, $num);

		}
/*
		public function testGetLastQuery()
		{

		}
*/


		public function testGetFieldDefs()
		{
			$actual = $this->db->getFieldDefs('plugin');

			$expected = array (
			  '_FIELD_TYPES' =>
			  array (
			    'plugin_id' => 'int',
			    'plugin_name' => 'escape',
			    'plugin_version' => 'escape',
			    'plugin_path' => 'escape',
			    'plugin_installflag' => 'int',
			    'plugin_addons' => 'escape',
			    'plugin_category' => 'escape',
			  ),
			  '_NOTNULL' =>
			  array (
			    'plugin_id' => '',
			    'plugin_addons' => '',
			  ),
			);

			$this->assertEquals($expected, $actual);

		}


			/**
		 * @desc Test primary methods against a secondary database (ensures mysqlPrefix is working correctly)
		 */
		public function testSecondaryDatabase()
		{

			try
			{
				$xql = $this->make('e_db_pdo');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_db_pdo object");
			}

			$xql->__construct();

			$config =  e107::getMySQLConfig();

		//	$xql = $this->db;

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

			if(!$res = $xql->db_FieldList($table))
			{
				$err = $xql->getLastErrorText();
				$this->fail("Failed to get field list from secondary database:\n".$err);

			}

			$this->assertEquals('test_id', $res[0]);

			if(!$tabs = $xql->tables())
			{
				$err = $xql->getLastQuery();
				$this->fail("Failed to get table list from secondary database:\n".$err);
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
			$username = $this->db->retrieve('user','user_name', 'user_id  = 1');
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



		}

	}
