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
}
