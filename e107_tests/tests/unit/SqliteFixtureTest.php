<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * The sqlite lane's database builder (e107_tests/lib/SqliteFixture.php), against a file of its own, on every lane
 * whose PHP links an SQLite e107 supports.
 */
class SqliteFixtureTest extends \Test\Unit
{
	/** @var string */
	private $file;

	protected function _before()
	{
		$this->requireSqliteLibrary();
		require_once(codecept_root_dir().'lib/SqliteFixture.php');

		$this->file = codecept_output_dir().'SqliteFixtureTest_'.getmypid().'.sqlite';
		@unlink($this->file);
	}

	protected function _after()
	{
		if($this->file !== null)
		{
			@unlink($this->file);
		}
	}

	public function testTheSampleDumpBecomesTheSameTablesAndRows()
	{
		$dump = file_get_contents(codecept_data_dir().'e107_v2.3.0.sample.sql');
		SqliteFixture::build(codecept_data_dir().'e107_v2.3.0.sample.sql', $this->file);
		$pdo = $this->open();

		preg_match_all('/^CREATE TABLE `(\w+)`/m', $dump, $declared);
		$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name <> 'sqlite_sequence' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
		sort($declared[1]);
		$this->assertSame($declared[1], $tables);

		preg_match_all('/^INSERT INTO `e107_admin_log` VALUES (.*);$/m', $dump, $inserts);
		$this->assertSame(substr_count($inserts[1][0], '),(') + 1, (int) $pdo->query('SELECT COUNT(*) FROM e107_admin_log')->fetchColumn());

		$prefs = $pdo->query("SELECT e107_value FROM e107_core WHERE e107_name = 'SitePrefs'")->fetchColumn();
		$this->assertNotFalse(e107::unserialize($prefs), 'the site preferences, escaped for MySQL in the dump, should read back intact');
	}

	public function testDeclaredTypesIndexesAndNextIdsCarryOver()
	{
		SqliteFixture::build(codecept_data_dir().'e107_v2.3.0.sample.sql', $this->file);
		$pdo = $this->open();

		$create = $pdo->query("SELECT sql FROM sqlite_master WHERE name = 'e107_admin_log'")->fetchColumn();
		$this->assertStringContainsString('`dblog_id` INTEGER PRIMARY KEY AUTOINCREMENT', $create);
		$this->assertStringContainsString('`dblog_eventcode` TEXT COLLATE NOCASE NOT NULL DEFAULT \'\'', $create);

		$this->assertSame('e107_admin_log', $pdo->query("SELECT tbl_name FROM sqlite_master WHERE name = 'e107_admin_log__dblog_datestamp'")->fetchColumn());
		$this->assertSame(57, (int) $pdo->query("SELECT seq FROM sqlite_sequence WHERE name = 'e107_admin_log'")->fetchColumn(), 'AUTO_INCREMENT=58 means the next id is 58');
	}

	public function testABuildReplacesWhatTheFileHeld()
	{
		$pdo = $this->open();
		$pdo->exec('CREATE TABLE leftover (id INTEGER)');
		$pdo->exec('CREATE TABLE e107_user (stale INTEGER)');
		$pdo = null;

		SqliteFixture::build(codecept_data_dir().'e107_v2.3.0.sample.sql', $this->file);
		$pdo = $this->open();

		$this->assertFalse($pdo->query("SELECT 1 FROM sqlite_master WHERE name = 'leftover'")->fetchColumn());
		$this->assertSame('1', $pdo->query('SELECT user_id FROM e107_user WHERE user_id = 1')->fetchColumn());
	}

	public function testRowsAreReadAsMysqlLiteralsAndBound()
	{
		$dump = codecept_output_dir().'SqliteFixtureTest_'.getmypid().'.sql';
		file_put_contents($dump, "-- a dump\n"
			."DROP TABLE IF EXISTS `t`;\n"
			."CREATE TABLE `t` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL DEFAULT '', `n` int(10) NULL DEFAULT NULL, `b` blob, PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=10;\n"
			."LOCK TABLES `t` WRITE;\n"
			."/*!40000 ALTER TABLE `t` DISABLE KEYS */;\n"
			."INSERT INTO `t` VALUES (1,'it\\'s; a \\\\ \"test\"',-5,0x6869),(2,'',NULL,_binary 'x');\n"
			."INSERT INTO `t` (`name`, `id`) VALUES ('third', 3);\n"
			."UNLOCK TABLES;\n");

		try
		{
			SqliteFixture::build($dump, $this->file);
		}
		finally
		{
			unlink($dump);
		}

		$rows = $this->open()->query('SELECT id, name, n, b FROM t ORDER BY id')->fetchAll(PDO::FETCH_NUM);

		$this->assertSame(array(
			array('1', 'it\'s; a \\ "test"', '-5', 'hi'),
			array('2', '', null, 'x'),
			array('3', 'third', null, null),
		), $rows);
		$this->assertSame(9, (int) $this->open()->query("SELECT seq FROM sqlite_sequence WHERE name = 't'")->fetchColumn());
	}

	public function testAFileThatIsNotADatabaseIsBuiltAnew()
	{
		file_put_contents($this->file, str_repeat('not a database ', 100));

		SqliteFixture::build(codecept_data_dir().'e107_v2.3.0.sample.sql', $this->file);

		$this->assertSame('1', $this->open()->query('SELECT COUNT(*) FROM e107_user WHERE user_id = 1')->fetchColumn());
	}

	public function testAStatementTheBuilderCannotLoadStopsTheBuildAndKeepsTheFile()
	{
		$pdo = $this->open();
		$pdo->exec('CREATE TABLE kept (id INTEGER)');
		$pdo = null;

		$dump = codecept_output_dir().'SqliteFixtureTest_'.getmypid().'.sql';
		file_put_contents($dump, "CREATE TABLE `t` (`id` int NOT NULL);\nUPDATE `t` SET `id` = 1;\n");

		try
		{
			SqliteFixture::build($dump, $this->file);
			$this->fail('an UPDATE in a dump should stop the build');
		}
		catch(RuntimeException $e)
		{
			$this->assertStringContainsString('UPDATE', $e->getMessage());
		}
		finally
		{
			unlink($dump);
		}

		$this->assertSame('kept', $this->open()->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchColumn());
	}

	/**
	 * @return PDO reading every value as a string, as PHP before 8.1 does anyway
	 */
	private function open()
	{
		return new PDO('sqlite:'.$this->file, null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES => true));
	}
}
