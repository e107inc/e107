<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

abstract class e_db_abstractTest extends \Codeception\Test\Unit
{
	/** @var e_db */
	protected $db;
	protected $dbConfig = array();

	/**
	 * @throws Exception
	 * @return e_db
	 */
	abstract protected function makeDb();

	/**
	 * Prevent creating too many connections to database server
	 */
	public function _after()
	{
		$this->db->close();
	}

	protected function loadConfig()
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

		$result = $this->db->db_Connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], "wrong password", $this->dbConfig['mySQLdefaultdb']);
		$this->assertEquals('e1', $result);

		$result = $this->db->db_Connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], $this->dbConfig['mySQLpassword'], "wrong database");
		$this->assertEquals('e2', $result);
	}

	public function testGetServerInfo()
	{

		$result = $this->db->getServerInfo();
		$this->assertStringNotContainsString('?',$result);
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

	public function testDb_Mark_Time()
	{
		$this->db->debugMode(true);
		e107::getDebug()->aTimeMarks = [];
		e107::getDebug()->nTimeMarks = 0;
		e107::getDebug()->e107_db_debug();

		$this->db->db_Mark_Time("Testing");

		$actual = e107::getDebug()->getTimeMarkers();

		$this->assertTrue(is_array($actual));
		$this->assertEquals('Testing', $actual[1]['What']);
		$this->assertArrayHasKey('Index', $actual[1]);
		$this->assertArrayHasKey('Time', $actual[1]);
		$this->assertArrayHasKey('Memory', $actual[1]);

		$this->db->debugMode(false);
		e107::getDebug()->aTimeMarks = [];
		e107::getDebug()->nTimeMarks = 0;
		e107::getDebug()->e107_db_debug();
		$result = $this->db->db_Mark_Time("Testing");
		$this->assertNull($result);
		$this->assertEquals(1, count(e107::getDebug()->getTimeMarkers()));


	}

	/*	public function testMakeTableDef()
		{

			$result = $this->db->makeTableDef('userclass_classes');

			var_export($result);
		}*/


	public function testDb_IsLang()
	{
		// XXX: This test leads to e_pref, which depends on lan_admin.php
		e107::coreLan('', true);

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
		$remark =  'e_db_abstractTest';
		$query = 'query goes here';

		$this->db->db_Write_log($log_type, $remark, $query);

		$data = $this->db->retrieve('dblog','dblog_title, dblog_user_id', "dblog_type = ".$log_type. " AND dblog_title = '".$remark ."' ");

		$expected = array (
			'dblog_title' => 'e_db_abstractTest',
			'dblog_user_id' => '1',
		);

		$this->assertEquals($expected, $data);
	}



	public function testDb_Query()
	{


		$userp = "3, 'Display Name', 'Username', '', 'password-hash', '', 'email@address.com', '', '', 0, ".time().", 0, 0, 0, 0, 0, '127.0.0.1', 0, '', 0, 1, '', '', '0', '', ".time().", ''";
		$this->db->db_Query("REPLACE INTO ".MPREFIX."user VALUES ({$userp})" );

		$this->db->db_Query("SELECT user_email FROM ".MPREFIX."user WHERE user_id = 3");
		$result = $this->db->fetch();
		$this->assertEquals('email@address.com', $result['user_email']);

		// duplicate unique field 'media_cat_category', should return false/error.
		$result = $this->db->db_Query("INSERT INTO ".MPREFIX."core_media_cat(media_cat_owner,media_cat_title,media_cat_category,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order) SELECT media_cat_owner,media_cat_title,media_cat_category,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order FROM ".MPREFIX."core_media_cat WHERE media_cat_id = 1");
		$err = $this->db->getLastErrorText();
		$this->assertFalse($result, $err);
	}

	public function testSelectBind()
	{
		$result = $this->db->select('user', 'user_id, user_name', 'user_id=:id OR user_name=:name ORDER BY user_name', array('id' => 999, 'name' => \Helper\AdminLogin::ADMIN_USER)); // bind support.
		$this->assertEquals(1, $result);
	}

	public function testDb_QueryBind()
	{
		$query = array(
			'PREPARE' => 'INSERT INTO ' . MPREFIX . 'tmp (`tmp_ip`,`tmp_time`,`tmp_info`) VALUES (:tmp_ip, :tmp_time, :tmp_info)',
			'BIND' =>
				array(
					'tmp_ip' =>
						array(
							'value' => '127.0.0.1',
							'type' => e_db::PARAM_STR,
						),
					'tmp_time' =>
						array(
							'value' => 12345435,
							'type' => e_db::PARAM_INT,
						),
					'tmp_info' =>
						array(
							'value' => 'Insert test',
							'type' => e_db::PARAM_STR,
						),
				),
		);

		$result = $this->db->db_Query($query, null, 'db_Insert');
		$this->assertGreaterThan(0, $result);

		$query = array(
			'PREPARE' => 'SELECT * FROM ' . MPREFIX . 'user WHERE user_id=:user_id AND user_name=:user_name',
			'EXECUTE' => array(
				'user_id' => 1,
				'user_name' => \Helper\AdminLogin::ADMIN_USER
			)
		);

		$res = $this->db->db_Query($query, null, 'db_Select');
		$this->assertNotFalse($res);
		$result = $this->db->fetch();
		$this->assertArrayHasKey('user_password', $result);
	}

	public function testDb_QueryBindLiteralColon()
	{
		// a colon inside a string literal must not be treated as a placeholder
		$query = array(
			'PREPARE' => "SELECT user_name FROM " . MPREFIX . "user WHERE user_name != 'not:a:param' AND user_id=:id",
			'EXECUTE' => array('id' => 1),
		);

		$res = $this->db->db_Query($query, null, 'db_Select');
		$this->assertNotFalse($res);
		$row = $this->db->fetch();
		$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $row['user_name']);
	}

	public function testDb_QueryBindRepeatedPlaceholder()
	{
		$query = array(
			'PREPARE' => 'SELECT user_id FROM ' . MPREFIX . 'user WHERE user_id=:id OR user_id=:id',
			'EXECUTE' => array('id' => 1),
		);

		$res = $this->db->db_Query($query, null, 'db_Select');
		$this->assertNotFalse($res);
		$row = $this->db->fetch();
		$this->assertSame('1', $row['user_id']); // result values are stringified on both backends
	}

	public function testDb_QueryBindTypedNull()
	{
		$query = array(
			'PREPARE' => 'SELECT :v IS NULL AS n',
			'BIND' => array(
				'v' => array('value' => 'ignored', 'type' => e_db::PARAM_NULL),
			),
		);

		$res = $this->db->db_Query($query, null, 'db_Select');
		$this->assertNotFalse($res);
		$row = $this->db->fetch();
		$this->assertSame('1', $row['n']);
	}

	public function testExecute()
	{
		// no params; `#table` marker resolved; '#' inside a string literal untouched
		$count = $this->db->execute("SELECT user_name FROM `#user` WHERE user_name != 'no#user' AND user_id = 1");
		$this->assertEquals(1, $count);
		$row = $this->db->fetch();
		$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $row['user_name']);

		// bound params
		$count = $this->db->execute('SELECT user_name FROM `#user` WHERE user_id = :id', array('id' => 1));
		$this->assertEquals(1, $count);
		$row = $this->db->fetch();
		$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $row['user_name']);

		// bare #table marker
		$count = $this->db->execute('SELECT user_name FROM #user WHERE user_id = :id', array('id' => 1));
		$this->assertEquals(1, $count);

		// write path returns affected rows
		$affected = $this->db->execute('INSERT INTO `#tmp` (tmp_ip, tmp_time, tmp_info) VALUES (:ip, :time, :info)',
			array('ip' => '127.0.0.1', 'time' => 12345, 'info' => 'execute() test'));
		$this->assertEquals(1, $affected);

		// explicitly typed parameter
		$affected = $this->db->execute('DELETE FROM `#tmp` WHERE tmp_info = :info',
			array('info' => array('value' => 'execute() test', 'type' => e_db::PARAM_STR)));
		$this->assertGreaterThan(0, $affected);

		// an UPDATE matching no rows returns 0, not false
		$result = $this->db->execute('UPDATE `#tmp` SET tmp_info = :v WHERE tmp_ip = :ip',
			array('v' => 'x', 'ip' => 'no.such.ip'));
		$this->assertSame(0, $result);

		// errors return false
		$result = $this->db->execute('SELECT * FROM `#doesnt_exist_table`');
		$this->assertFalse($result);
	}

	public function testResolveTableName()
	{
		$this->assertEquals(MPREFIX.'user', $this->db->resolveTableName('user'));
		$this->assertEquals(MPREFIX.'user', $this->db->resolveTableName('#user'));
		$this->assertFalse($this->db->resolveTableName('bad-name'));
		$this->assertFalse($this->db->resolveTableName('user; DROP TABLE x'));
	}

	public function testQuoteIdentifier()
	{
		$this->assertEquals('`user_name`', $this->db->quoteIdentifier('user_name'));
		$this->assertEquals('`u`.`user_name`', $this->db->quoteIdentifier('u.user_name'));
		$this->assertFalse($this->db->quoteIdentifier('user_name; --'));
		$this->assertFalse($this->db->quoteIdentifier('a`b'));
	}

	public function testCreateQueryBuilder()
	{
		$qb = $this->db->createQueryBuilder();

		$this->assertInstanceOf('e_db_query', $qb);
		$this->assertInstanceOf('e_db_expr', $qb->expr());
		$this->assertInstanceOf('e_db_platform_mysql', $this->db->getPlatform());
		$this->assertSame($this->db->getPlatform(), $qb->getPlatform());
	}

	public function testQueryBuilderRoundTrip()
	{
		$db = $this->db;

		// INSERT through the builder; every value is bound
		$rows = array(
			array('tmp_ip' => 'qb.test.1', 'tmp_time' => 1001, 'tmp_info' => 'alpha 50% off'),
			array('tmp_ip' => 'qb.test.2', 'tmp_time' => 1002, 'tmp_info' => 'beta'),
			array('tmp_ip' => 'qb.test.3', 'tmp_time' => 1003, 'tmp_info' => 'gamma'),
		);

		foreach($rows as $row)
		{
			$affected = $db->createQueryBuilder()->insert('tmp')->values($row)->execute();
			$this->assertSame(1, $affected);
		}

		// fetchAll() with expression, ORDER BY and LIMIT
		$qb = $db->createQueryBuilder();
		$found = $qb->select('tmp_ip', 'tmp_time')
			->from('tmp')
			->where($qb->expr()->startsWith('tmp_ip', 'qb.test.'))
			->orderBy('tmp_time', 'DESC')
			->setMaxResults(2)
			->fetchAll();

		$this->assertCount(2, $found);
		$this->assertSame('qb.test.3', $found[0]['tmp_ip']);
		$this->assertSame('qb.test.2', $found[1]['tmp_ip']);

		// fetchAll($indexBy)
		$qb = $db->createQueryBuilder();
		$indexed = $qb->select()
			->from('tmp')
			->where($qb->expr()->startsWith('tmp_ip', 'qb.test.'))
			->fetchAll('tmp_ip');

		$this->assertArrayHasKey('qb.test.2', $indexed);
		$this->assertSame('beta', $indexed['qb.test.2']['tmp_info']);

		// whereIn() + fetchColumn()
		$qb = $db->createQueryBuilder();
		$ips = $qb->select('tmp_ip')
			->from('tmp')
			->whereIn('tmp_time', array(1001, 1003))
			->orderBy('tmp_time', 'ASC')
			->fetchColumn();

		$this->assertSame(array('qb.test.1', 'qb.test.3'), $ips);

		// fetchPairs(); both backends stringify row values identically
		$qb = $db->createQueryBuilder();
		$pairs = $qb->select('tmp_ip', 'tmp_time')
			->from('tmp')
			->where($qb->expr()->startsWith('tmp_ip', 'qb.test.'))
			->orderBy('tmp_time', 'ASC')
			->fetchPairs();

		$this->assertSame(array('qb.test.1' => '1001', 'qb.test.2' => '1002', 'qb.test.3' => '1003'), $pairs);

		// contains() escapes LIKE wildcards: a literal '%' in the needle
		$qb = $db->createQueryBuilder();
		$found = $qb->select('tmp_ip')
			->from('tmp')
			->where($qb->expr()->contains('tmp_info', '50%'))
			->fetchAll();

		$this->assertCount(1, $found);
		$this->assertSame('qb.test.1', $found[0]['tmp_ip']);

		// ... and '_' matches only a literal underscore, not "any character"
		$qb = $db->createQueryBuilder();
		$found = $qb->select('tmp_ip')
			->from('tmp')
			->where($qb->expr()->contains('tmp_info', '5_'))
			->fetchAll();

		$this->assertCount(0, $found);

		// fetchOne() / fetchRow()
		$qb = $db->createQueryBuilder();
		$value = $qb->select('tmp_info')
			->from('tmp')
			->where($qb->expr()->eq('tmp_ip', 'qb.test.2'))
			->fetchOne();

		$this->assertSame('beta', $value);

		$qb = $db->createQueryBuilder();
		$row = $qb->select()
			->from('tmp')
			->where($qb->expr()->eq('tmp_ip', 'qb.no.such.row'))
			->fetchRow();

		$this->assertSame(array(), $row);

		$qb = $db->createQueryBuilder();
		$none = $qb->select('tmp_info')
			->from('tmp')
			->where($qb->expr()->eq('tmp_ip', 'qb.no.such.row'))
			->fetchOne();

		$this->assertNull($none);

		// UPDATE through the builder
		$qb = $db->createQueryBuilder();
		$affected = $qb->update('tmp')
			->set('tmp_info', 'updated')
			->where($qb->expr()->eq('tmp_ip', 'qb.test.2'))
			->execute();

		$this->assertSame(1, $affected);

		$qb = $db->createQueryBuilder();
		$this->assertSame('updated', $qb->select('tmp_info')
			->from('tmp')
			->where($qb->expr()->eq('tmp_ip', 'qb.test.2'))
			->fetchOne());

		// DELETE through the builder cleans up
		$qb = $db->createQueryBuilder();
		$affected = $qb->delete('tmp')
			->where($qb->expr()->startsWith('tmp_ip', 'qb.test.'))
			->execute();

		$this->assertSame(3, $affected);
	}

	public function testQueryBuilderRejectsHostileInput()
	{
		try
		{
			$this->db->createQueryBuilder()->select()->from('tmp')
				->orderBy('tmp_time; DROP TABLE `'.MPREFIX.'tmp`');
			$this->fail('Expected InvalidArgumentException was not thrown');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertStringContainsString('ORDER BY', $e->getMessage());
		}

		try
		{
			$this->db->createQueryBuilder()->select()->from('tmp; DROP TABLE x')->getSQL();
			$this->fail('Expected InvalidArgumentException was not thrown');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertStringContainsString('table name', $e->getMessage());
		}
	}

	public function testCommonTraitRejectsHostileIdentifiers()
	{
		$db = $this->db;
		$hostile = 'tmp; DROP TABLE `'.MPREFIX.'tmp`';

		// identifier positions fail closed, before any SQL is executed
		$this->assertFalse($db->truncate($hostile));
		$this->assertFalse($db->isEmpty($hostile));
		$this->assertFalse($db->dropTable($hostile));
		$this->assertFalse($db->copyTable($hostile, 'tmp2'));
		$this->assertFalse($db->copyTable('tmp', $hostile));
		$this->assertFalse($db->copyRow($hostile, '*', "tmp_ip='x'"));
		$this->assertFalse($db->copyRow('tmp', 'tmp_ip) SELECT tmp_ip FROM x; -- ', "tmp_ip='x'"));
		$this->assertFalse($db->field($hostile));
		$this->assertFalse($db->index($hostile, 'PRIMARY'));
		$this->assertNull($db->max($hostile, 'tmp_time'));
		$this->assertNull($db->max('tmp', 'tmp_time) FROM dual; -- '));
		$this->assertFalse($db->selectTree($hostile, 'p', 'i', 'o'));
		$this->assertFalse($db->selectTree('tmp', 'p)`; DROP FUNCTION x', 'i', 'o'));

		// valid identifiers behave exactly as before
		$this->assertFalse($db->isEmpty('user'));
		$this->assertTrue($db->field('user', 'user_name'));
		$this->assertTrue($db->index('user', 'PRIMARY'));
		$this->assertGreaterThanOrEqual(1, (int) $db->max('user', 'user_id'));
	}

	public function testEscapeDeprecationNoticeOncePerCallSite()
	{
		$caught = array();
		set_error_handler(function ($errno, $errstr) use (&$caught)
		{
			$caught[] = $errstr;
			return true;
		}, E_USER_DEPRECATED);

		for($i = 0; $i < 2; $i++)
		{
			$this->db->escape("x"); // one call site, called twice: one notice
		}
		$this->db->escape("y"); // a second call site: one more notice

		restore_error_handler();

		$this->assertCount(2, $caught);
		$this->assertStringContainsString('escape() is deprecated', $caught[0]);
	}


	public function testRetrieve()
	{
		// 'single' field value mode.
		$expected = \Helper\AdminLogin::ADMIN_USER;
		$result = $this->db->retrieve('user', 'user_name', 'user_id = 1');
		$this->assertEquals($expected,$result);

		$result = $this->db->retrieve("SELECT user_name FROM #user WHERE user_id = 1");
		$this->assertEquals($expected,$result);

		// 'one' row mode.
		$expected = array ('user_id' => '1', 'user_name' => \Helper\AdminLogin::ADMIN_USER,	);
		$result = $this->db->retrieve('user', 'user_id, user_name', 'user_id = 1');
		$this->assertEquals($expected,$result);

		$result = $this->db->retrieve("SELECT user_id, user_name FROM #user WHERE user_id = 1");
		$this->assertEquals($expected,$result);

		$result = $this->db->retrieve("SELECT user_id, user_name FROM #user WHERE user_id = 1");
		$this->assertEquals($expected,$result);



		$result = $this->db->retrieve('user', 'missing_field, user_name', 'user_id = 1');
		$this->assertEquals(array(),$result);

		$result = $this->db->retrieve('user', 'missing_field', 'user_id = 1');
		$this->assertEmpty($result);

		$this->db->select('user', 'user_id, user_name', 'user_id = 1');
		$result = $this->db->retrieve(null);
		$this->assertEquals($expected,$result);

		// 'multi' row mode.
		$expected = array ( 0 =>  array (   'user_id' => '1', 'user_name' => \Helper\AdminLogin::ADMIN_USER, ),);
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


		$result = $this->db->retrieve('plugin', 'plugin_id, plugin_name, plugin_path', '', true, 'plugin_path');
		$this->assertArrayHasKey('banner', $result);
		$this->assertArrayHasKey('plugin_name', $result['banner']);

		// Fetch only mode
		$this->db->select('plugin');
		$result = $this->db->retrieve(null, 'plugin_id, plugin_name, plugin_path', '', true, 'plugin_path');
		$this->assertArrayHasKey('banner', $result);
		$this->assertArrayHasKey('plugin_name', $result['banner']);

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

		$result = $this->db->db_Select('user', 'missing_field, user_name', 'WHERE user_id = 1', true);
		$this->assertFalse($result);


		$result = $this->db->db_Select('user', 'user_id', 'WHERE user_id = 1', true, true); // debug enabled
		$log = e107::getDebug()->getLog();
		$this->assertEquals(1, $result);

		$found = false;
		foreach($log as $val)
		{
			if($val['Message'] === "SELECT user_id FROM e107_user WHERE user_id = 1")
			{
				$found = true;
			}
		}

		$this->assertTrue($found, "Couldn't find debug log message for db_Select() item.");

	}


	public function testDb_Select_gen()
	{
		$this->db->db_Select_gen(
			"UPDATE `#user` SET user_signature = 'something else' WHERE user_id = 1"
		);
		$result = $this->db->db_Select_gen(
			"UPDATE `#user` SET user_signature = 'e_db' WHERE user_id = 1"
		);
		$this->assertEquals(1,$result);
		$result = $this->db->db_Select_gen(
			"UPDATE `#user` SET user_signature = 'e_db' WHERE user_id = 1"
		);
		$this->assertEquals(0,$result);


		$qry = "INSERT INTO #core_media_cat(media_cat_owner,media_cat_title,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order) SELECT media_cat_owner,media_cat_title,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order FROM #core_media_cat WHERE media_cat_id = 1";
		$this->db->db_Select_gen($qry);


		$qry = "INSERT INTO #core_media_cat(media_cat_owner,media_cat_title,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order) SELECT media_cat_owner,media_cat_title,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order FROM #core_media_cat WHERE media_cat_id = 1";
		$result = $this->db->db_Select_gen($qry);
		$this->assertFalse($result);
		//	$error = $this->db->getLastErrorText();

		$result = $this->db->db_Query("INSERT INTO ".MPREFIX."core_media_cat(media_cat_owner,media_cat_title,media_cat_category,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order) SELECT media_cat_owner,media_cat_title,media_cat_category,media_cat_sef,media_cat_diz,media_cat_class,media_cat_image,media_cat_order FROM ".MPREFIX."core_media_cat WHERE media_cat_id = 1");
		$err = $this->db->getLastErrorText();
		$this->assertFalse($result, $err);


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

		// Test with auto-update on duplicate key found.
		$insert = array(
			'media_cat_category'    => '_common_image', // unique key.
			'media_cat_diz'         => "modified by e_db_abstractTest->insert test",
			'_DUPLICATE_KEY_UPDATE' => true
		);

		$this->db->insert('core_media_cat', $insert);
		$actual = $this->db->retrieve('core_media_cat', 'media_cat_diz','media_cat_category = "_common_image" ');
		$this->assertEquals("modified by e_db_abstractTest->insert test", $actual);

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
//
		$this->db->debugMode(true);// todo causes a hang while testing. (see db_Query() )
		$actual = $this->db->db_Insert('missing_table', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'test 2'));
		$this->assertFalse($actual);
		$this->db->debugMode(false);

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

	public function testWritePathUsesBinds()
	{
		$this->db->delete('tmp');

		$result = $this->db->insert('tmp', array('tmp_ip' => '127.0.0.9', 'tmp_time' => 4, 'tmp_info' => 'bind check'));
		$this->assertNotEmpty($result, 'insert() failed');

		$last = $this->db->getLastQuery();
		$this->assertTrue(is_array($last), 'Array-form insert() should run through the prepared-statement contract');
		$this->assertArrayHasKey('PREPARE', $last);
		$this->assertSame('bind check', $last['BIND']['tmp_info']['value']);

		$result = $this->db->update('tmp', array('tmp_info' => 'bind check 2', 'WHERE' => 'tmp_ip = "127.0.0.9"'));
		$this->assertEquals(1, $result);

		$last = $this->db->getLastQuery();
		$this->assertTrue(is_array($last), 'Array-form update() should run through the prepared-statement contract');
		$this->assertArrayHasKey('PREPARE', $last);
		$this->assertSame('bind check 2', $last['BIND']['tmp_info']['value']);

		$result = $this->db->update('tmp', 'tmp_time = tmp_time + 1 WHERE tmp_ip = "127.0.0.9"');
		$this->assertEquals(1, $result);
		$this->assertTrue(is_string($this->db->getLastQuery()), 'Raw-string update() should remain a plain query');
	}

	public function testWritePathBindsHostileValues()
	{
		$hostile = "Rob'); DROP TABLE `".MPREFIX."tmp`; -- \\ \"%_";

		$this->db->delete('tmp');

		$result = $this->db->insert('tmp', array('tmp_ip' => '127.0.0.8', 'tmp_time' => 1, 'tmp_info' => $hostile));
		$this->assertNotEmpty($result, 'insert() failed on special characters');

		$actual = $this->db->retrieve('tmp', 'tmp_info', 'tmp_ip = "127.0.0.8"');
		$this->assertSame($hostile, $actual, 'insert() altered the stored value');

		$hostile = "O'Connor \\' OR '1'='1";
		$result = $this->db->update('tmp', array('tmp_info' => $hostile, 'WHERE' => 'tmp_ip = "127.0.0.8"'));
		$this->assertEquals(1, $result, 'update() failed on special characters');

		$actual = $this->db->retrieve('tmp', 'tmp_info', 'tmp_ip = "127.0.0.8"');
		$this->assertSame($hostile, $actual, 'update() altered the stored value');

		$this->assertEquals(1, $this->db->count('tmp'));
	}

	public function testWritePathFieldTypes()
	{
		$chardata = array('k' => "v'1", 'n' => 2);

		$data = array(
			'data'          => array(
				'gen_id'        => 0,
				'gen_type'      => 'write-path-types',
				'gen_datestamp' => '12abc',
				'gen_user_id'   => 1,
				'gen_ip'        => '127.0.0.1',
				'gen_intdata'   => 0,
				'gen_chardata'  => $chardata,
			),
			'_FIELD_TYPES'  => array(
				'gen_id'        => 'int',
				'gen_type'      => 'str',
				'gen_datestamp' => 'int',
				'gen_user_id'   => 'int',
				'gen_ip'        => 'str',
				'gen_intdata'   => 'int',
				'gen_chardata'  => 'array',
			),
		);

		$id = $this->db->insert('generic', $data);
		$this->assertGreaterThan(0, $id);

		$row = $this->db->retrieve('generic', 'gen_datestamp, gen_chardata', 'gen_id = '.(int) $id);
		$this->assertSame('12', $row['gen_datestamp'], "'int' field type should cast the value");
		$this->assertSame($chardata, e107::unserialize($row['gen_chardata']), "'array' field type should serialize round-trip");

		$result = $this->db->update('generic', array(
			'data'          => array('gen_datestamp' => 'gen_datestamp+5'),
			'_FIELD_TYPES'  => array('gen_datestamp' => 'cmd'),
			'WHERE'         => 'gen_id = '.(int) $id,
		));
		$this->assertEquals(1, $result, "'cmd' field type update failed");

		$actual = $this->db->retrieve('generic', 'gen_datestamp', 'gen_id = '.(int) $id);
		$this->assertSame('17', $actual, "'cmd' field type should evaluate as SQL");
	}

	public function testWritePathReturnContracts()
	{
		$this->db->delete('generic', 'gen_type = "write-path-contract"');

		$base = array(
			'gen_type'      => 'write-path-contract',
			'gen_datestamp' => 100,
			'gen_user_id'   => 1,
			'gen_ip'        => '127.0.0.1',
			'gen_intdata'   => 1,
			'gen_chardata'  => 'a',
		);

		// REPLACE: 1 = added, 2 = replaced (delete + insert)
		$first = $this->db->replace('generic', $base);
		$this->assertEquals(1, $first, 'REPLACE of a new row should report 1');
		$id = $this->db->lastInsertId();
		$this->assertGreaterThan(0, $id);

		$row = $base;
		$row['gen_id'] = $id;
		$row['gen_intdata'] = 2;
		$second = $this->db->replace('generic', $row);
		$this->assertEquals(2, $second, 'REPLACE of an existing row should report 2');

		// _DUPLICATE_KEY_UPDATE: new ID on insert, true on update, 0 on no change
		$update = $row;
		$update['gen_intdata'] = 3;
		$update['_DUPLICATE_KEY_UPDATE'] = true;
		$result = $this->db->insert('generic', $update);
		$this->assertTrue($result, 'Duplicate-key update with changed data should return true');

		$result = $this->db->insert('generic', $update);
		$this->assertSame(0, $result, 'Duplicate-key update with no change should return 0');

		$fresh = $base;
		$fresh['gen_intdata'] = 4;
		$fresh['_DUPLICATE_KEY_UPDATE'] = true;
		$result = $this->db->insert('generic', $fresh);
		$this->assertGreaterThan(0, $result, 'Fresh duplicate-key insert should return the new ID');
	}
	public function testDb_QueryCount()
	{
		$this->db->select('user', '*');
		$this->db->select('plugin','*');

		$result = $this->db->db_QueryCount();
		$this->assertGreaterThan(1,$result);

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
		$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $row['user_name']);
		$this->assertFalse(isset($row[0]), "MYSQL_NUM keys not expected");
		$this->assertFalse(isset($row[1]), "MYSQL_NUM keys not expected");

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
		$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $row['user_name']);
		$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $row[1]);

	}


	public function testDb_Count()
	{
		$count = $this->db->count('user');
		$this->assertGreaterThan(0, $count);

		$result = $this->db->db_Count('user','(*)', 'user_id = 1');
		$this->assertEquals(1,$result);

		$result = $this->db->db_Count('SELECT COUNT(*) FROM '.MPREFIX.'plugin ','generic');
		$this->assertGreaterThan(20, $result);

		$result = $this->db->db_Count('user','(*)', 'user_missing = 1');
		$this->assertFalse($result);

		$result = $this->db->db_Count('SELECT COUNT(*) FROM '.MPREFIX.'missing ','generic');
		$this->assertFalse($result);
	}
	/*
			public function testClose()
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
		$this->assertEquals($expected, $actual, "Number of deleted records is wrong ({$expected} != {$actual})");

		// Insert some records
		$this->db->insert('tmp', array('tmp_ip' => '127.0.0.1', 'tmp_time' => time(), 'tmp_info' => 'Delete test 1'));
		$this->db->insert('tmp', array('tmp_ip' => '127.0.0.2', 'tmp_time' => time(), 'tmp_info' => 'Delete test 2'));
		$this->db->insert('tmp', array('tmp_ip' => '127.0.0.3', 'tmp_time' => time(), 'tmp_info' => 'Delete test 3'));

		// Count records
		$expected = 3;
		$actual = $this->db->count('tmp');
		$this->assertEquals($expected, $actual, "Number of inserted records is wrong ({$expected} != {$actual})");

		// Delete 1 record
		$expected = 1;
		$actual = $this->db->delete('tmp', 'tmp_ip="127.0.0.1"');
		$this->assertEquals($expected, $actual, 'Unable to delete 1 records.');

		// Check if the returned value is equal to the number of affected records
		$expected = $actual;
		$actual = $this->db->rowCount();
		$this->assertEquals($expected, $actual, "Number of deleted records is wrong ({$expected} != {$actual})");

		// Delete all remaining (2) records
		$expected = 2;
		$actual = $this->db->delete('tmp');
		$this->assertEquals($expected, $actual, 'Unable to delete the remaining records.');

		// Check if the returned value is equal to the number of affected records
		$expected = $actual;
		$actual = $this->db->rowCount();
		$this->assertEquals($expected, $actual, "Number of deleted records is wrong ({$expected} != {$actual})");

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


	public function testDb_SetErrorReporting()
	{
		$this->db->db_SetErrorReporting(false);
		// fixme - getErrorReporting.
	}

	/*
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


	public function testMax()
	{
		$insert = array(
			'gen_id'    => 0,
			'gen_type'  => 'testMax',
			'gen_datestamp' => time(),
			'gen_user_id'   => 333,
			'gen_ip'        => '127.0.0.1',
			'gen_intdata'   => '',
			'gen_chardata'   => ''
		);

		$this->db->insert('generic', $insert);


		$insert = array(
			'gen_id'    => 0,
			'gen_type'  => 'testMax',
			'gen_datestamp' => time(),
			'gen_user_id'   => 555,
			'gen_ip'        => '127.0.0.1',
			'gen_intdata'   => '',
			'gen_chardata'   => ''
		);

		$this->db->insert('generic', $insert);


		$result = $this->db->max('generic', 'gen_user_id');
		$this->assertEquals('555', $result);

		$result = $this->db->max('generic', 'gen_user_id');
		$this->assertEquals('555', $result, "gen_ip = '127.0.0.1'");

		$result = $this->db->max('generic', 'gen_user_id', "gen_ip = '127.0.0.1'");
		$this->assertEquals('555', $result);


	}


	/*
			public function testSelectTree()
			{

			}

			public function testDb_Query_all()
			{

			}
	*/
	public function testDb_FieldList()
	{
		$result = $this->db->db_FieldList('user');
		$this->assertEquals('user_id', $result[0]);

		$result = $this->db->db_FieldList('user', null, true);
		$this->assertEquals('user_id', $result['user_id']);

		$result = $this->db->db_FieldList('missing_table');
		$this->assertFalse($result);

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

	public function testEscape()
	{
		$result = $this->db->escape(123);
		$this->assertEquals(123,$result);

		$result = $this->db->escape("Can't", true);
		$this->assertEquals("Can\'t", $result);
	}

	public function testQuoteStringLiteral()
	{
		$value = "Can't; -- \\ \"mixed\"";
		$literal = $this->db->quoteStringLiteral($value);

		$this->assertSame("'", substr($literal, 0, 1));
		$this->assertSame("'", substr($literal, -1));

		$this->db->gen('SELECT '.$literal.' AS roundtrip');
		$row = $this->db->fetch();
		$this->assertSame($value, $row['roundtrip']);
	}

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
		$this->db->copyTable('user', 'test_is_empty', true, true);

		$result = $this->db->isEmpty('test_is_empty');
		$this->assertFalse($result);

		$this->db->truncate('test_is_empty');

		$result = $this->db->isEmpty('test_is_empty');
		$this->assertTrue($result);

		$this->db->dropTable('test_is_empty');

		$result = $this->db->isEmpty();
		$this->assertFalse($result);

	}

	public function testDb_ResetTableList()
	{
		$this->db->db_ResetTableList();
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

		$list = $this->db->db_TableList('invalid');
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

		$result = $this->db->db_CopyRow('bla', 'non_exist',  "news_id = 1");
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
	 * @desc Test primary methods against a secondary database instance (ensures mysqlPrefix is working correctly)
	 */
	public function testSecondaryDatabaseInstance()
	{
		try
		{
			$xql = $this->makeDb();
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't load e_db object");
		}

		$xql->__construct();

		$config =  e107::getMySQLConfig();

		$database = $config['mySQLdefaultdb'];
		$table = 'test';
		$MPREFIX = 'another_prefix_';
		$xql->connect($config['mySQLserver'], $config['mySQLuser'], $config['mySQLpassword']);

		// use new database
		$use = $xql->database($database,$MPREFIX,true);

		if($use === false)
		{
			$this->fail("Failed to select new database");
		}

		$create = "CREATE TABLE `".$database."`.".$MPREFIX.$table." (
					 `test_id` int(4) NOT NULL AUTO_INCREMENT,
					 `test_var` varchar(255) NOT NULL,
					 PRIMARY KEY (`test_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";

		// cleanup
		$xql->gen("DROP TABLE IF EXISTS `$database`.{$MPREFIX}{$table}");

		// create table
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
