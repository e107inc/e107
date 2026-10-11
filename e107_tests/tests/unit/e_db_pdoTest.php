<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
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
		require_once(e_HANDLER . "e_db_interface.php");
		require_once(e_HANDLER . "e_db_legacy_trait.php");
		require_once(e_HANDLER . "e_db_pdo_class.php");
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

	/**
	 * @return e_db_pdo an instance built from the site's configuration that has not connected yet
	 */
	private function unconnectedDb()
	{
		$db = $this->makeDb();
		$db->__construct();

		return $db;
	}

	/**
	 * @return e_db_pdo a second instance on $this->db's connection
	 */
	private function sharingDb()
	{
		$other = e107::getDb('e_db_pdoTestSharing');
		$this->assertSame($this->sessionOf($this->db), $this->sessionOf($other), 'the two have to share a connection for this to prove anything');

		return $other;
	}

	public function testInstancesThatHaveNotConnectedShareTheSitesConnection()
	{
		$sessions = array_unique(array(
			$this->sessionOf(e107::getDb()),
			$this->sessionOf(e107::getDb('e_db_pdoTestNamed')),
			$this->sessionOf($this->unconnectedDb()),
		));

		$this->assertCount(1, $sessions);
		$this->assertGreaterThan(0, reset($sessions));
	}

	public function testAnInstanceOnTheSharedConnectionTakesItsCharacterSet()
	{
		$db = $this->unconnectedDb();
		$this->assertGreaterThan(0, $this->sessionOf($db));

		$this->assertNotNull(e107::getDb()->getCharset());
		$this->assertSame(e107::getDb()->getCharset(), $db->getCharset());
	}

	public function testAnInstanceConfiguredWithThePortInTheServerNameSharesTheSitesConnection()
	{
		$configured = $this->makeDbConfiguredWith(array('mySQLserver' => $this->dbConfig['mySQLserver'].':'.$this->dbConfig['mySQLport']));

		$this->assertSame($this->sessionOf(e107::getDb()), $this->sessionOf($configured));
	}

	public function testAnInstanceSelectingAnotherDatabaseMovesToAConnectionOfItsOwn()
	{
		$site = $this->sessionOf(e107::getDb());
		$this->assertSame($site, $this->sessionOf($this->db));

		$this->assertTrue($this->db->database('information_schema', ''));

		$this->assertNotSame($site, $this->sessionOf($this->db));
		$this->assertSame('information_schema', $this->db->retrieve('SELECT DATABASE()'));
		$this->assertSame($this->dbConfig['mySQLdefaultdb'], e107::getDb()->retrieve('SELECT DATABASE()'));
	}

	public function testAConnectionAskedForAsANewLinkIsNotShared()
	{
		$this->assertTrue($this->connectOwnSession($this->db, 'information_schema', ''));
		$other = $this->makeDbConfiguredWith(array('mySQLdefaultdb' => 'information_schema'));

		try
		{
			$this->assertNotSame($this->sessionOf($this->db), $this->sessionOf($other));
		}
		finally
		{
			$other->close();
		}
	}

	public function testAConnectionAskedForAsANewLinkStaysUnsharedAfterClose()
	{
		$this->assertTrue($this->connectOwnSession($this->db, $this->dbConfig['mySQLdefaultdb']));
		$this->db->close();

		$this->assertNotSame($this->sessionOf(e107::getDb()), $this->sessionOf($this->db));
	}

	public function testAnInstanceThatFailsToSelectAnotherDatabaseStaysOnTheSharedConnection()
	{
		$other = $this->sharingDb();

		$this->assertFalse($this->db->database('e_db_pdoTest_no_such_database'));

		$this->assertSame($this->dbConfig['mySQLdefaultdb'], $this->db->retrieve('SELECT DATABASE()'));
		$this->assertSame($this->sessionOf($other), $this->sessionOf($this->db));
	}

	public function testARefusedConnectLeavesTheInstanceAbleToSelectItsDatabase()
	{
		$this->sharingDb();

		$this->assertFalse($this->db->connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], 'wrong password'));

		$this->assertTrue($this->db->database($this->dbConfig['mySQLdefaultdb']));
	}

	public function testTheLastInstanceToCloseEndsTheConnection()
	{
		$first = $this->makeDbConfiguredWith(array('mySQLdefaultdb' => 'information_schema'));
		$second = $this->makeDbConfiguredWith(array('mySQLdefaultdb' => 'information_schema'));
		$session = $this->sessionOf($first);
		$this->assertSame($session, $this->sessionOf($second));
		$this->assertNotSame($session, $this->sessionOf(e107::getDb()));

		$first->close();
		$second->close();

		$this->assertTrue($this->sessionEnds($session), "server connection $session survived the last close()");
	}

	public function testARefusedConnectKeepsTheConnectionItLeavesOutOfSharing()
	{
		$this->assertTrue($this->db->connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], $this->dbConfig['mySQLpassword']));
		$this->assertFalse($this->db->connect($this->dbConfig['mySQLserver'], $this->dbConfig['mySQLuser'], 'wrong password'));
		$this->assertTrue($this->db->database('information_schema', ''));
		$other = $this->makeDbConfiguredWith(array('mySQLpassword' => 'wrong password', 'mySQLdefaultdb' => 'information_schema'));
		$refused = false;

		try
		{
			$other->retrieve('SELECT 1');
		}
		catch(PDOException $e)
		{
			$refused = true;
		}

		$this->assertTrue($refused, 'an instance with the wrong password was handed a connection');
	}

	public function testAnInstanceSwitchingBackAndForthStaysOnTheConnectionItMovedTo()
	{
		$this->sharingDb();
		$this->assertTrue($this->db->database('information_schema', ''));
		$moved = $this->sessionOf($this->db);

		$this->assertTrue($this->db->database($this->dbConfig['mySQLdefaultdb']));
		$this->assertTrue($this->db->database('information_schema', ''));

		$this->assertSame($moved, $this->sessionOf($this->db));
	}

	public function testClosingOneInstanceLeavesTheConnectionToTheOthers()
	{
		$other = $this->sharingDb();
		$session = $this->sessionOf($other);

		$this->db->close();

		$this->assertSame($session, $this->sessionOf($other));
	}

	public function testTheInsertIdIsTheInstancesOwnWhenAnotherInstanceQueriesInBetween()
	{
		$other = $this->sharingDb();
		$this->assertNotFalse($this->db->gen('CREATE TEMPORARY TABLE `#e_db_share_test` (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, v INT)'));

		try
		{
			$this->db->gen('INSERT INTO `#e_db_share_test` (v) VALUES (1), (2)');
			$this->db->gen('INSERT INTO `#e_db_share_test` (v) VALUES (3)');
			$other->retrieve('SELECT 1');

			$this->assertSame(3, $this->db->lastInsertId());
		}
		finally
		{
			$this->db->gen('DROP TEMPORARY TABLE IF EXISTS `#e_db_share_test`');
		}
	}

	public function testTheRowsFoundAreTheInstancesOwnWhenAnotherInstanceQueriesInBetween()
	{
		$other = $this->sharingDb();
		$this->assertNotFalse($this->db->gen('SELECT SQL_CALC_FOUND_ROWS e107_name FROM `#core` LIMIT 1'));
		$expected = (int) $other->count('core');
		$this->assertNotFalse($other->gen('SELECT SQL_CALC_FOUND_ROWS user_id FROM `#user` LIMIT 1'));

		$this->assertGreaterThan(1, $expected);
		$this->assertSame($expected, $this->db->foundRows());
	}

	public function testAResultInHandSurvivesAnotherInstanceReadingTheConnection()
	{
		$other = $this->sharingDb();
		$names = array();
		$this->assertGreaterThan(1, $this->db->gen('SELECT e107_name FROM `#core` ORDER BY e107_name'));
		$first = $this->db->fetch();

		$this->assertNotFalse($other->gen('SELECT e107_name FROM `#core` ORDER BY e107_name'));
		while($row = $other->fetch())
		{
			$names[] = $row['e107_name'];
		}

		$read = array($first['e107_name']);
		while($row = $this->db->fetch())
		{
			$read[] = $row['e107_name'];
		}

		$this->assertSame($names, $read);
	}

	public function testGetCharSet()
	{
		$this->db->setCharset();
		$result = $this->db->getCharset();

		$this->assertEquals('utf8', $result);
	}

	public function testBackup()
	{
		$opts = array(
			'gzip' => false,
			'nologs' => false,
			'droptable' => false,
		);

		$result = $this->db->backup('user,core_media_cat', null, $opts);
		$uncompressedSize = filesize($result);

		$tmp = file_get_contents($result);

		$this->assertStringNotContainsString("DROP TABLE IF EXISTS `e107_user`;", $tmp);
		$this->assertStringContainsString("CREATE TABLE `e107_user` (", $tmp);
		$this->assertStringContainsString("INSERT INTO `e107_user` VALUES (1", $tmp);
		$this->assertStringContainsString("CREATE TABLE `e107_core_media_cat`", $tmp);

		$result = $this->db->backup('*', null, $opts);
		$size = filesize($result);
		$this->assertGreaterThan(100000, $size);

		$opts = array(
			'gzip' => true,
			'nologs' => false,
			'droptable' => false,
		);

		$result = $this->db->backup('user,core_media_cat', null, $opts);
		$compressedSize = filesize($result);
		$this->assertLessThan($uncompressedSize, $compressedSize);

		$result = $this->db->backup('missing_table', null, $opts);
		$this->assertFalse($result);
		$this->assertNotSame(0, $this->db->getLastErrorNumber(),
			'a failed backup has to leave an error number behind');
		$this->assertNotSame('', $this->db->getLastErrorText(),
			'a failed backup has to say why');
	}

	/**
	 * PDO-exclusive feature: Select with argument bindings
	 * @see e_db_abstractTest::testSelect()
	 */
	public function testSelectBind()
	{
		$result = $this->db->select('user', 'user_id, user_name', 'user_id=:id OR user_name=:name ORDER BY user_name', array('id' => 999, 'name' => 'e107')); // bind support.
		$this->assertEquals(1, $result);
	}

	/**
	 * PDO-exclusive feature: Query with argument bindings
	 * @see e_db_abstractTest::testDb_Query()
	 */
	public function testDb_QueryBind()
	{
		$query = array(
			'PREPARE' => 'INSERT INTO ' . MPREFIX . 'tmp (`tmp_ip`,`tmp_time`,`tmp_info`) VALUES (:tmp_ip, :tmp_time, :tmp_info)',
			'BIND' =>
				array(
					'tmp_ip' =>
						array(
							'value' => '127.0.0.1',
							'type' => PDO::PARAM_STR,
						),
					'tmp_time' =>
						array(
							'value' => 12345435,
							'type' => PDO::PARAM_INT,
						),
					'tmp_info' =>
						array(
							'value' => 'Insert test',
							'type' => PDO::PARAM_STR,
						),
				),
		);


		$result = $this->db->db_Query($query, null, 'db_Insert');
		$this->assertGreaterThan(0, $result);


		$query = array(
			'PREPARE' => 'SELECT * FROM ' . MPREFIX . 'user WHERE user_id=:user_id AND user_name=:user_name',
			'EXECUTE' => array(
				'user_id' => 1,
				'user_name' => 'e107'
			)
		);


		$res = $this->db->db_Query($query, null, 'db_Select');
		$result = $res->fetch();
		$this->assertArrayHasKey('user_password', $result);
	}

	/**
	 * PDO-exclusive feature: Copy row and keep unique keys unique
	 * @see e_db_abstractTest::testDb_Query()
	 * @see https://github.com/e107inc/e107/issues/3678
	 */
	public function testDb_CopyRowUnique()
	{
		// test with table that has unique keys.
		$result = $this->db->db_CopyRow('core_media_cat', '*', "media_cat_id = 1");
		$qry = $this->db->getLastErrorText();
		$this->assertGreaterThan(1, $result, $qry);

		// test with table that has unique keys. (same row again) - make sure copyRow duplicates it regardless.
		$result = $this->db->db_CopyRow('core_media_cat', '*', "media_cat_id = 1");
		$qry = $this->db->getLastErrorText();
		$this->assertGreaterThan(1, $result, $qry);
	}

	public function test_Db_CopyRowRNGRetry()
	{
		$original_user_handler = e107::getRegistry('core/e107/singleton/UserHandler');
		$evil_user_handler = $this->make('UserHandler', [
			'generateRandomString' => function ($pattern = '', $seed = '')
			{
				static $index = 0;
				$mock_values = ['same0000000', 'same0000000', 'different00'];

				return $mock_values[$index++];
			}
		]);
		e107::setRegistry('core/e107/singleton/UserHandler', $evil_user_handler);

		// test with table that has unique keys.
		$result = $this->db->db_CopyRow('core_media_cat', '*', "media_cat_id = 1");
		$qry = $this->db->getLastErrorText();
		$this->assertGreaterThan(1, $result, $qry);

		// test with table that has unique keys. (same row again) - make sure copyRow duplicates it regardless.
		$result = $this->db->db_CopyRow('core_media_cat', '*', "media_cat_id = 1");
		$qry = $this->db->getLastErrorText();
		$this->assertGreaterThan(1, $result, $qry);

		e107::setRegistry('core/e107/singleton/UserHandler', $original_user_handler);
	}

	public function test_Db_CopyRowRNGGiveUp()
	{
		$original_user_handler = e107::getRegistry('core/e107/singleton/UserHandler');
		$evil_user_handler = $this->make('UserHandler', [
			'generateRandomString' => function ($pattern = '', $seed = '')
			{
				return 'neverchange';
			}
		]);
		e107::setRegistry('core/e107/singleton/UserHandler', $evil_user_handler);

		// test with table that has unique keys.
		$result = $this->db->db_CopyRow('core_media_cat', '*', "media_cat_id = 1");
		$result = $this->db->db_CopyRow('core_media_cat', '*', "media_cat_id = 1");
		$qry = $this->db->getLastErrorText();
		$this->assertFalse($result,
			"Intentionally broken random number generator should have prevented row copy with unique keys"
		);

		e107::setRegistry('core/e107/singleton/UserHandler', $original_user_handler);
	}

	/**
	 * A statement with nothing in it has to come back false, the way any refused
	 * query does. It must never escape as an error the caller cannot catch.
	 *
	 * PHP 8's PDO answers an empty statement with a ValueError and a non-string
	 * one with a TypeError. Neither descends from PDOException, so neither was
	 * caught by db_Query(), and callers that reach it directly rather than
	 * through gen() got an uncaught fatal: a blank HTTP 500 with no output at
	 * all. The database character set conversion tool in e107_admin/db.php is
	 * one such caller, and it feeds db_Query() strings a CONCAT() produced,
	 * which are NULL whenever any argument was.
	 *
	 * @see https://github.com/e107inc/e107/discussions/5904
	 */
	public function testAnEmptyStatementIsRefusedRatherThanFatal()
	{
		$this->assertFalse($this->db->db_Query(''),
			'db_Query() has to refuse an empty statement');

		$this->assertFalse($this->db->db_Query("  \n\t "),
			'db_Query() has to refuse a statement that is only whitespace');

		$this->assertFalse($this->db->db_Query(null),
			'db_Query() has to refuse a statement that is not a string');

		$this->assertFalse($this->db->gen(''),
			'gen() has to refuse an empty statement');

		// The guard rejects nothing that was working before.
		$this->assertNotFalse($this->db->select('user', 'user_id', '`user_id` = 1'),
			'a real query still has to run');
	}
}
