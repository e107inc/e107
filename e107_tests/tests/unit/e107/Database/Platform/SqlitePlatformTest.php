<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Platform;

use e107\Database\Exception\UnsupportedException;
use e107\Database\Schema\Definition\MysqlDdlParser;
use e107\Database\SqlLexer;
use PDO;

/**
 * The SQLite dialect: what it writes, and that SQLite runs it with the meaning MySQL gives the same request. Each
 * test executes against an in-memory database, so every lane runs this.
 */
class SqlitePlatformTest extends \Test\Unit
{
	/** @var SqlitePlatform */
	private $platform;

	/** @var PDO */
	private $pdo;

	protected function _before()
	{
		require_once(e_HANDLER.'e_db_interface.php');
		require_once(e_HANDLER.'Database/Platform/SqlitePlatform.php');
		require_once(e_HANDLER.'Database/Schema/Definition/MysqlDdlParser.php');

		if(!extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('pdo_sqlite is not loaded');
		}

		$this->pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true); // as the SQLite driver sets it, on every PHP version
		$version = $this->pdo->query('SELECT sqlite_version()')->fetchColumn();
		$this->platform = new SqlitePlatform($version);

		if(version_compare($version, '3.35.0', '<'))
		{
			$this->markTestSkipped('SQLite '.$version.' is older than e107 supports');
		}
	}

	public function testIdentifiersAreBacktickQuotedSoAMissingColumnIsAnErrorNotAString()
	{
		$this->assertSame('`t`.`col`', $this->platform->quoteIdentifier('t.col'));
		$this->assertFalse($this->platform->quoteIdentifier('bad name'));

		$this->pdo->exec('CREATE TABLE q (a INTEGER)');
		$this->expectException('PDOException');
		$this->pdo->query('SELECT `nosuch` FROM q');
	}

	public function testAnOffsetWithoutALimitStillGetsALimit()
	{
		$this->assertSame(' LIMIT 10', $this->platform->getLimitClause(10));
		$this->assertSame(' LIMIT 10 OFFSET 20', $this->platform->getLimitClause(10, 20));
		$this->assertSame(' LIMIT -1 OFFSET 20', $this->platform->getLimitClause(null, 20));
		$this->assertSame('', $this->platform->getLimitClause(null, 0));

		$this->pdo->exec('CREATE TABLE n (v INTEGER)');
		$this->pdo->exec('INSERT INTO n VALUES (1), (2), (3)');
		$this->assertSame(array('3'), $this->column('SELECT v FROM n ORDER BY v'.$this->platform->getLimitClause(null, 2)));
	}

	public function testInsertIgnoreAndReplaceKeepTheirMysqlMeaning()
	{
		$this->pdo->exec('CREATE TABLE k (id INTEGER PRIMARY KEY, v TEXT)');
		$this->pdo->exec("INSERT INTO k VALUES (1, 'a')");

		$ignore = $this->platform->compileInsert('`k`', array('`id`', '`v`'), array("(1, 'b')"), 'IGNORE');
		$this->assertSame("INSERT OR IGNORE INTO `k` (`id`, `v`) VALUES (1, 'b')", $ignore);
		$this->assertSame(0, $this->pdo->exec($ignore));

		$replace = $this->platform->compileReplace('`k`', array('`id`', '`v`'), array('1', "'c'"));
		$this->pdo->exec($replace);
		$this->assertSame(array('c'), $this->column('SELECT v FROM k'));
	}

	public function testAnUpsertReportsAnUnchangedRowAsNoChange()
	{
		$this->pdo->exec('CREATE TABLE u (id INTEGER PRIMARY KEY, name TEXT COLLATE NOCASE, hits INTEGER)');
		$sql = $this->platform->compileUpsert(
			'`u`', array('`id`', '`name`', '`hits`'), array('(:id, :name, :hits)'),
			array('`name`' => $this->platform->getUpsertValueReference('`name`'), '`hits`' => $this->platform->getUpsertValueReference('`hits`')),
			array('`id`')
		);

		$this->assertSame(1, $this->upsert($sql, 1, 'abc', 5), 'insert');
		$this->assertSame(0, $this->upsert($sql, 1, 'abc', 5), 'same values: no change');
		$this->assertSame(1, $this->upsert($sql, 1, 'abc', 6), 'changed');
		$this->assertSame(1, $this->upsert($sql, 1, 'ABC', 6), 'a change of letter case is a change even in a NOCASE column');
		$this->assertSame(array('1|ABC|6'), $this->column("SELECT id || '|' || name || '|' || hits FROM u"));
	}

	public function testALimitedUpdateOrDeleteTouchesOnlyThatManyRows()
	{
		$this->pdo->exec('CREATE TABLE l (v INTEGER)');
		$this->pdo->exec('INSERT INTO l VALUES (1), (1), (1), (2)');

		$update = $this->platform->compileUpdate('`l`', array('`v`' => '9'), ' WHERE (`v` = 1)', 2);
		$this->assertSame('UPDATE `l` SET `v` = 9 WHERE rowid IN (SELECT rowid FROM `l` WHERE (`v` = 1) LIMIT 2)', $update);
		$this->assertSame(2, $this->pdo->exec($update));

		$this->assertSame(1, $this->pdo->exec($this->platform->compileDelete('`l`', '', 1)));
		$this->assertSame('DELETE FROM `l` WHERE (`v` = 2)', $this->platform->compileDelete('`l`', ' WHERE (`v` = 2)'));
	}

	public function testTheLikeEscapeClauseMakesABackslashEscapeWildcards()
	{
		$this->pdo->exec('CREATE TABLE e (v TEXT)');
		$this->pdo->exec("INSERT INTO e VALUES ('50%'), ('500')");

		$this->assertSame(array('50%'), $this->column('SELECT v FROM e WHERE v LIKE :p'.$this->platform->getLikeEscapeClause(), array('p' => '50\\%')));
	}

	public function testDatePartsReadTheTextDatesSqliteStores()
	{
		$this->assertSame(array('2024|7|9|2024-07-09|13:45:00'), $this->column(
			"SELECT ".$this->platform->compileDatePart('year', 'd')." || '|' || ".$this->platform->compileDatePart('month', 'd')
			." || '|' || ".$this->platform->compileDatePart('day', 'd')." || '|' || ".$this->platform->compileDatePart('date', 'd')
			." || '|' || ".$this->platform->compileDatePart('time', 'd')." FROM (SELECT '2024-07-09 13:45:00' AS d)"
		));
	}

	public function testGroupConcatSpellsWhatThisSqliteCanRun()
	{
		$this->assertSame("group_concat(`a`, ';')", $this->platform->compileGroupConcat('`a`', array(), "';'"));
		$this->assertSame('group_concat(DISTINCT `a`)', $this->platform->compileGroupConcat('`a`', array(), "','", true));
		$this->assertSame("e107_group_concat_distinct(`a`, ';')", $this->platform->compileGroupConcat('`a`', array(), "';'", true));

		$old = new SqlitePlatform('3.40.1');
		$this->expectException(UnsupportedException::class);
		$old->compileGroupConcat('`a`', array('`a` ASC'), "','");
	}

	public function testEveryCoreTableIsCreatedWithSqliteTypesAndQualifiedIndexes()
	{
		$parser = new MysqlDdlParser();
		$text = preg_replace('#^<\?php.*?\?>#s', '', file_get_contents(e_CORE.'sql/core_sql.php'));
		$tables = 0;

		foreach(SqlLexer::mysql()->splitStatements($text) as $statement)
		{
			$table = $parser->parseCreateTable($statement);

			foreach($this->platform->compileTableDefinition('e107_'.$table->getName(), $table) as $sql)
			{
				$this->pdo->exec($sql);
			}

			$tables++;
		}

		$this->assertSame(30, $tables);
		$this->assertSame(array('e107_user__user_name'), $this->column("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'e107_user' AND name LIKE '%user_name'"));
		$this->assertSame(
			array('CREATE TABLE `e107_core` (', '  `e107_name` TEXT COLLATE NOCASE NOT NULL DEFAULT \'\',', '  `e107_value` TEXT COLLATE NOCASE NOT NULL DEFAULT \'\',', '  PRIMARY KEY (`e107_name`)', ')'),
			explode("\n", $this->column("SELECT sql FROM sqlite_master WHERE name = 'e107_core'")[0])
		);
	}

	public function testAColumnLeftOutOfAnInsertGetsMysqlsImplicitDefault()
	{
		$parser = new MysqlDdlParser();
		$table = $parser->parseTableBody('d', "id int(10) unsigned NOT NULL auto_increment, i int NOT NULL, s varchar(10) NOT NULL, t text NOT NULL, dt datetime NOT NULL, e enum('x','y') NOT NULL, n int, PRIMARY KEY (id)");

		foreach($this->platform->compileTableDefinition('d', $table) as $sql)
		{
			$this->pdo->exec($sql);
		}

		$this->pdo->exec('INSERT INTO d (id) VALUES (NULL)');
		$this->assertSame(array('id' => '1', 'i' => '0', 's' => '', 't' => '', 'dt' => '0000-00-00 00:00:00', 'e' => 'x', 'n' => null), $this->pdo->query('SELECT * FROM d')->fetch(PDO::FETCH_ASSOC));
	}

	public function testEveryPrimaryKeyColumnIsNotNullAsMysqlMakesIt()
	{
		$parser = new MysqlDdlParser();

		foreach(array('l' => 'id int(10) unsigned, v varchar(10), PRIMARY KEY (id)', 'c' => 'a int, b varchar(5), v varchar(10), PRIMARY KEY (a, b)') as $name => $body)
		{
			foreach($this->platform->compileTableDefinition($name, $parser->parseTableBody($name, $body)) as $sql)
			{
				$this->pdo->exec($sql);
			}

			$this->pdo->exec("INSERT INTO $name (v) VALUES ('first')");

			try
			{
				$this->pdo->exec("INSERT INTO $name (v) VALUES ('second')");
				$this->fail('Both inserts left out the key, which took the same value both times, so the second must be refused.');
			}
			catch(\PDOException $e)
			{
				$this->assertStringContainsString('UNIQUE', $e->getMessage());
			}
		}
	}

	public function testFullTextIndexesAreNotBuiltAndAutoIncrementNeedsTheLonePrimaryKey()
	{
		$parser = new MysqlDdlParser();
		$statements = $this->platform->compileTableDefinition('f', $parser->parseTableBody('f', 'id int NOT NULL auto_increment, body text, PRIMARY KEY (id), FULLTEXT (body)'));
		$this->assertCount(1, $statements, 'no CREATE INDEX for a FULLTEXT key');

		$this->expectException(UnsupportedException::class);
		$this->platform->compileTableDefinition('g', $parser->parseTableBody('g', 'a int NOT NULL, id int NOT NULL auto_increment, PRIMARY KEY (a, id)'));
	}

	public function testAByteDefaultIsStoredAsTheTextABoundValueIsAndRefusedWhereTextCannotHoldIt()
	{
		$parser = new MysqlDdlParser();

		foreach($this->platform->compileTableDefinition('b', $parser->parseTableBody('b', "id int NOT NULL, a blob DEFAULT 0x41, t varbinary(8) NOT NULL DEFAULT 'A', e varbinary(4) NOT NULL, PRIMARY KEY (id)")) as $sql)
		{
			$this->pdo->exec($sql);
		}

		$this->pdo->exec('INSERT INTO b (id) VALUES (1)');
		$this->assertSame(array('a' => 'A', 'ta' => 'text', 't' => 'A', 'tt' => 'text', 'e' => '', 'te' => 'text'), $this->pdo->query('SELECT a, typeof(a) AS ta, t, typeof(t) AS tt, e, typeof(e) AS te FROM b')->fetch(PDO::FETCH_ASSOC));

		$bound = $this->pdo->prepare('SELECT COUNT(*) FROM b WHERE a = :v AND t = :v AND e = :e');
		$bound->execute(array('v' => 'A', 'e' => ''));
		$this->assertSame('1', $bound->fetchColumn(), 'the defaults equal the same bytes written through a bound parameter');

		foreach(array('z varbinary(4) NOT NULL DEFAULT 0x00', "flag char(1) NOT NULL DEFAULT b'0'", 'h varbinary(4) NOT NULL DEFAULT 0xff') as $column)
		{
			try
			{
				$this->platform->compileTableDefinition('c', $parser->parseTableBody('c', 'id int NOT NULL, '.$column.', PRIMARY KEY (id)'));
				$this->fail('A default text cannot hold was written: '.$column);
			}
			catch(UnsupportedException $e)
			{
				$this->assertStringContainsString('NUL byte or bytes that are not UTF-8', $e->getMessage());
			}
		}
	}

	public function testAnOnUpdateColumnIsRefusedRatherThanCreatedWithoutIt()
	{
		$table = (new MysqlDdlParser())->parseTableBody('u', 'id int NOT NULL, a timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (id)');
		$column = null;

		foreach($table->getColumns() as $candidate)
		{
			$column = ($candidate->getName() === 'a') ? $candidate : $column;
		}

		try
		{
			$this->platform->compileTableDefinition('u', $table);
			$this->fail('CREATE TABLE dropped ON UPDATE');
		}
		catch(UnsupportedException $e)
		{
			$this->assertStringContainsString('ON UPDATE', $e->getMessage());
		}

		$this->expectException(UnsupportedException::class);
		$this->platform->compileColumnDefinition($column);
	}

	public function testANameOutsideTheIdentifierGrammarIsRefused()
	{
		$parser = new MysqlDdlParser();

		foreach(array('bad name', 'a.b', "x`y") as $name)
		{
			try
			{
				$this->platform->compileTableDefinition($name, $parser->parseTableBody('n', 'id int NOT NULL, PRIMARY KEY (id)'));
				$this->fail('a table named '.$name.' was written');
			}
			catch(\InvalidArgumentException $e)
			{
				$this->assertStringContainsString($name, $e->getMessage());
			}
		}
	}

	public function testTheCapabilitiesAreSqlites()
	{
		$this->assertFalse($this->platform->supportsStorageEngines());
		$this->assertFalse($this->platform->supportsCharsets());
		$this->assertFalse($this->platform->supportsFoundRows());
		$this->assertFalse($this->platform->supportsFullTextIndexes());
		$this->assertTrue($this->platform->supportsTransactionalDdl());
		$this->assertFalse($this->platform->assignsAutoIncrementOnZero());
		$this->assertNull($this->platform->compileAutoIncrementReset('`t`'));
		$this->assertSame('ALTER TABLE `a` RENAME TO `b`', $this->platform->compileRenameTable('`a`', '`b`'));
		$this->assertSame('', $this->platform->getForUpdateClause());
		$this->assertSame('RANDOM()', $this->platform->getRandomFunction());

		$this->expectException(UnsupportedException::class);
		$this->platform->compileCreateDatabase('`x`');
	}

	/**
	 * @param string $sql
	 * @param array $params
	 * @return array the first column of every row
	 */
	private function column($sql, array $params = array())
	{
		$statement = $this->pdo->prepare($sql);
		$statement->execute($params);

		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * @param string $sql
	 * @param int $id
	 * @param string $name
	 * @param int $hits
	 * @return int rows changed
	 */
	private function upsert($sql, $id, $name, $hits)
	{
		$statement = $this->pdo->prepare($sql);
		$statement->execute(array('id' => $id, 'name' => $name, 'hits' => $hits));

		return $statement->rowCount();
	}
}
