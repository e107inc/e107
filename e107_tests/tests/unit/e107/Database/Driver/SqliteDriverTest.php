<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Driver;

use e107\Database\ConnectionInterface;
use e107\Database\Platform\SqlitePlatform;
use e107\Database\Result\BufferedResult;
use e107\Database\Schema\Column;
use e107\Database\Schema\Definition\MysqlDdlParser;
use e107\Database\Schema\Index;
use e107\Database\Schema\SchemaBuilder;
use e107\Database\Schema\SqliteSchemaManager;

/**
 * An e_db_pdo connection on the SQLite driver, against a database file of its own, so every lane runs it whatever
 * engine the suite itself uses.
 */
class SqliteDriverTest extends \Test\Unit
{
	/** @var string */
	private $file;

	/** @var \e_db_pdo */
	private $db;

	protected function _before()
	{
		require_once(e_HANDLER.'e_db_pdo_class.php');
		$this->requireSqliteLibrary();

		$this->file = codecept_output_dir().'SqliteDriverTest_'.getmypid().'.sqlite';
		$this->removeFiles();
		touch($this->file);

		$this->db = $this->connect();
		$this->createTable('CREATE TABLE item (
			item_id int(10) unsigned NOT NULL auto_increment,
			item_name varchar(100) NOT NULL default \'\',
			item_class varchar(255) NOT NULL default \'0\',
			item_hits int(10) unsigned NOT NULL default \'0\',
			item_body text NOT NULL,
			PRIMARY KEY (item_id),
			UNIQUE KEY item_name (item_name),
			KEY item_hits (item_hits)
		) ENGINE=InnoDB;');
	}

	protected function _after()
	{
		if($this->db !== null)
		{
			$this->db->close();
		}

		$this->removeFiles();
	}

	public function testTheConnectionSpeaksSqlite()
	{
		$this->assertSame('sqlite', $this->db->getDriver()->getName());
		$this->assertInstanceOf(SqlitePlatform::class, $this->db->getPlatform());
		$this->assertInstanceOf(SqliteSchemaManager::class, $this->db->getSchemaManager());
		$this->assertTrue(version_compare($this->db->getServerInfo(), SqliteDriver::MINIMUM_VERSION, '>='));
		$this->assertSame('', $this->db->getMode());
	}

	public function testAMissingFileIsReportedNotCreated()
	{
		$db = new \e_db_pdo();
		$db->useDriver('sqlite');

		$this->assertTrue($db->connect('', '', ''));
		$this->assertFalse($db->database($this->file.'.missing', 'e107_'));
		$this->assertSame(ConnectionInterface::ERROR_UNKNOWN_DATABASE, $db->getLastErrorNumber());
		$this->assertFileDoesNotExist($this->file.'.missing');
	}

	public function testRowsAreCountedUpFrontAndFetchedAsStrings()
	{
		$this->insertItems();

		$this->assertSame(3, $this->db->execute('SELECT * FROM `#item` ORDER BY item_id'));
		$this->assertInstanceOf(BufferedResult::class, $this->db->mySQLresult);
		$this->assertSame(array('item_id' => '1', 'item_name' => 'one'), array_intersect_key($this->db->fetch(), array_flip(array('item_id', 'item_name'))));
		$this->assertSame(0, $this->db->execute('SELECT * FROM `#item` WHERE item_id > 99'));
		$this->assertSame(2, $this->db->createQueryBuilder()->select('item_id')->from('item')->where('item_hits', '>', 5)->execute());
	}

	public function testTheBuilderCountsTheRowsAQueryFindsWithoutItsLimit()
	{
		$this->insertItems();
		$this->db->createQueryBuilder()->insert('item')->values(array('item_name' => 'four', 'item_class' => '2', 'item_hits' => 12, 'item_body' => ''))->execute();

		$qb = $this->db->createQueryBuilder();
		$qb->calcFoundRows()->select('item_class')->from('item')->where('item_hits', '>', 5)
			->groupBy('item_class')->orderBy('item_class', 'DESC')->setMaxResults(1);

		$this->assertStringNotContainsString('SQL_CALC_FOUND_ROWS', $qb->getSQL());
		$this->assertSame(1, $qb->execute());
		$this->assertSame(2, $qb->foundRows(), 'two groups match; the LIMIT keeps one');
		$this->assertSame(array('item_class' => '2,3'), $this->db->fetch(), 'counting left the page itself to be read');

		$this->db->execute('SELECT * FROM `#item`');
		$this->assertSame(2, $qb->foundRows(), 'the count is kept past later queries');

		$page = $this->db->createQueryBuilder()->calcFoundRows()->select('item_id')->from('item')->setFirstResult(3)->setMaxResults(2);
		$this->assertCount(1, $page->fetchAll());
		$this->assertSame(4, $page->foundRows());
	}

	public function testTheQueryBuilderWritesAndReadsBack()
	{
		$qb = $this->db->createQueryBuilder();
		$id = $qb->insert('item')->values(array('item_name' => 'x', 'item_body' => 'b'))->execute();
		$this->assertSame(1, $id, 'one row inserted');

		$this->assertSame('x', $this->db->createQueryBuilder()->select('item_name')->from('item')->where('item_id', 1)->fetchOne());
		$this->assertSame(1, $this->db->createQueryBuilder()->update('item')->set('item_hits', 7)->where('item_id', 1)->limit(1)->execute());
		$this->assertSame('7', $this->db->createQueryBuilder()->select('item_hits')->from('item')->fetchOne());
		$this->assertSame(1, $this->db->createQueryBuilder()->delete('item')->where('item_name', 'x')->limit(1)->execute());
	}

	public function testAnUpsertInsertsUpdatesAndReportsAnUnchangedRowAsNoChange()
	{
		$upsert = function($db, $hits) {
			return $db->createQueryBuilder()->insert('item')
				->upsert(array('item_name' => 'u', 'item_hits' => $hits, 'item_body' => ''), 'item_name')
				->execute();
		};

		$this->assertSame(1, $upsert($this->db, 1));
		$this->assertSame(0, $upsert($this->db, 1));
		$this->assertSame(1, $upsert($this->db, 2));
		$this->assertSame('2', $this->db->createQueryBuilder()->select('item_hits')->from('item')->where('item_name', 'u')->fetchOne());
	}

	public function testABackslashInALikePatternEscapesAWildcardAsOnMysql()
	{
		foreach(array('a_b', 'axb', '100%', '1000') as $name)
		{
			$this->db->createQueryBuilder()->insert('item')->values(array('item_name' => $name, 'item_body' => ''))->execute();
		}

		$names = function($pattern)
		{
			return $this->db->createQueryBuilder()->select('item_name')->from('item')->whereLike('item_name', $pattern)->orderBy('item_name', 'ASC')->fetchColumn();
		};

		$this->assertSame(array('a_b'), $names('a\\_b'));
		$this->assertSame(array('a_b', 'axb'), $names('a_b'));
		$this->assertSame(array('100%'), $names('100\\%'));
		$this->assertSame(array('1000'), $this->db->createQueryBuilder()->select('item_name')->from('item')->whereNotLike('item_name', '%\\%')->whereLike('item_name', '1%')->fetchColumn());
	}

	public function testTheStringAndCounterExpressionsRunOnSqlite()
	{
		$this->insertItems(); // one '1' 3, two '2' 6, three '2,3' 9

		$qb = $this->db->createQueryBuilder();
		$this->assertSame(3, $qb->update('item')->setExpression('item_body', $qb->expr()->concat($qb->expr()->value('pre-'), 'item_name'))->decrementNotBelowZero('item_hits', 5)->execute());

		$qb = $this->db->createQueryBuilder();
		$rows = $qb->select('item_body', 'item_hits')->selectAs($qb->expr()->substringBefore('item_class', ','), 'first_class')
			->from('item')->orderBy('item_id', 'ASC')->fetchAll();
		$this->assertSame(array('pre-one', 'pre-two', 'pre-three'), array_column($rows, 'item_body'));
		$this->assertSame(array('0', '1', '4'), array_column($rows, 'item_hits'), 'a counter stops at zero');
		$this->assertSame(array('1', '2', '2'), array_column($rows, 'first_class'), 'all of it where there is no delimiter');

		$qb = $this->db->createQueryBuilder();
		$pairs = $qb->select('a.item_name')->selectAs('b.item_id', 'class_item')->from('item', 'a')
			->innerJoin('item', 'b', $qb->expr()->findColumnInSet('a.item_class', 'b.item_id'))
			->orderBy('a.item_id', 'ASC')->addOrderBy('b.item_id', 'ASC')->fetchAll();
		$this->assertSame(array('one', 'two', 'three', 'three'), array_column($pairs, 'item_name'));
		$this->assertSame(array('1', '2', '2', '3'), array_column($pairs, 'class_item'));

		$qb = $this->db->createQueryBuilder();
		$this->assertSame(array('one'), $qb->select('a.item_name')->from('item', 'a')
			->innerJoin('item', 'b', $qb->expr()->compareColumns($qb->expr()->substringBefore('a.item_class', ','), 'b.item_id'))
			->where('b.item_name', 'one')->fetchColumn());
	}

	public function testAnUpsertCanUpdateFromTheStoredValue()
	{
		$bump = function()
		{
			$qb = $this->db->createQueryBuilder();

			return $qb->insert('item')
				->upsert(array('item_name' => 'counted', 'item_hits' => 1, 'item_body' => ''), 'item_name',
					array('item_hits' => $qb->raw('COALESCE(item_hits, 0) + 1')))
				->execute();
		};

		$this->assertSame(1, $bump());
		$this->assertSame(1, $bump());
		$this->assertSame(1, $bump());
		$this->assertSame('3', $this->db->createQueryBuilder()->select('item_hits')->from('item')->where('item_name', 'counted')->fetchOne());
	}

	public function testAnUpdateThroughTheBuilderCountsTheRowsItChanges()
	{
		$this->db->insert('item', array('item_name' => 'a', 'item_hits' => 1, 'item_body' => ''));
		$this->db->insert('item', array('item_name' => 'b', 'item_hits' => 2, 'item_body' => ''));
		$update = function($hits)
		{
			return $this->db->createQueryBuilder()->update('item')->set('item_hits', $hits)->execute();
		};

		$this->assertSame(1, $update(2), 'the row already holding 2 is not changed');
		$this->assertSame(0, $update(2));
	}

	public function testALegacyReplaceAndDuplicateKeyInsertReportWhatTheyDidAsMysqlDoes()
	{
		$this->assertSame(1, $this->db->replace('item', array('item_name' => 'r', 'item_body' => '')), 'added');
		$id = $this->db->retrieve('item', 'item_id', "item_name = 'r'");
		$this->assertSame(2, $this->db->replace('item', array('item_id' => $id, 'item_name' => 'r', 'item_hits' => 3, 'item_body' => '')), 'replaced');

		$upsert = array('item_name' => 'r', 'item_hits' => 4, 'item_body' => '', '_DUPLICATE_KEY_UPDATE' => true);
		$this->assertTrue($this->db->insert('item', $upsert), 'updated through the unique name');
		$this->assertSame(0, $this->db->insert('item', $upsert), 'nothing to change');

		$fresh = array('item_name' => 'f', 'item_body' => '', '_DUPLICATE_KEY_UPDATE' => true);
		$this->assertSame((int) $id + 2, $this->db->insert('item', $fresh),
			'inserted: the new id, one past the value the unchanged upsert spent; the update gave its own back');
	}

	public function testAnUpsertThatUpdatesATableWithoutAutoIncrementReportsNoError()
	{
		$this->db->close();
		$this->removeFiles();
		touch($this->file);
		$this->db = $this->connect();
		$this->createTable("CREATE TABLE plain (plain_key varchar(10) NOT NULL default '', plain_value varchar(10) NOT NULL default '', UNIQUE KEY plain_key (plain_key))");

		$this->assertTrue($this->db->insert('plain', array('plain_key' => 'k', 'plain_value' => 'a')));
		$this->assertTrue($this->db->insert('plain', array('plain_key' => 'k', 'plain_value' => 'b', '_DUPLICATE_KEY_UPDATE' => true)));
		$this->assertSame(0, $this->db->getLastErrorNumber(), $this->db->getLastErrorText());
	}

	public function testAnInsertIntoATableWithoutAutoIncrementReportsNoId()
	{
		$this->createTable("CREATE TABLE plain (plain_key varchar(10) NOT NULL default '', UNIQUE KEY plain_key (plain_key))");

		$this->assertTrue($this->db->insert('plain', array('plain_key' => 'k')));
		$this->assertTrue($this->db->createQueryBuilder()->insert('plain')->insertGetId(array('plain_key' => 'l')));
	}

	public function testTheLegacyArrayInsertAndItsFieldTypesWork()
	{
		$id = $this->db->insert('item', array('item_name' => 'legacy', 'item_hits' => '12', 'item_body' => 'b'));
		$this->assertSame(1, $id);

		$this->assertSame('12', $this->db->retrieve('item', 'item_hits', 'item_id = 1'));
		$this->assertSame(1, $this->db->update('item', array('item_hits' => 13, 'WHERE' => 'item_id = 1')));
		$this->assertSame(1, $this->db->count('item'));
		$this->assertSame(1, $this->db->delete('item', 'item_id = 1'));
	}

	public function testFailuresCarryE107sErrorCodes()
	{
		$this->insertItems();

		$this->assertFalse($this->db->execute("INSERT INTO `#item` (item_name, item_body) VALUES ('one', '')"));
		$this->assertSame(ConnectionInterface::ERROR_DUPLICATE_KEY, $this->db->getLastErrorNumber());

		$this->assertFalse($this->db->execute('SELECT * FROM `#nosuch`'));
		$this->assertSame(ConnectionInterface::ERROR_NO_SUCH_TABLE, $this->db->getLastErrorNumber());

		$this->assertFalse($this->db->execute('SELECT nosuch FROM `#item`'));
		$this->assertSame(ConnectionInterface::ERROR_NO_SUCH_COLUMN, $this->db->getLastErrorNumber());

		$this->assertFalse($this->db->execute('CREATE TABLE `#item` (a INTEGER)'));
		$this->assertSame(ConnectionInterface::ERROR_TABLE_EXISTS, $this->db->getLastErrorNumber());
	}

	public function testRegexpAndFindInSetAndFullTextWork()
	{
		$this->insertItems();
		$qb = $this->db->createQueryBuilder();

		$this->assertSame(array('one', 'three'), $qb->select('item_name')->from('item')->where($qb->expr()->regexp('item_class', '(^|,)(1|3)(,|$)'))->orderBy('item_id')->fetchColumn());

		$qb = $this->db->createQueryBuilder();
		$this->assertSame(array('two', 'three'), $qb->select('item_name')->from('item')->where($qb->expr()->findInSet('item_class', '2'))->orderBy('item_id')->fetchColumn());

		$qb = $this->db->createQueryBuilder();
		$this->assertSame(array('three'), $qb->select('item_name')->from('item')->whereFullText('item_body', '+quick -lazy', true)->fetchColumn());
	}

	public function testTheSchemaManagerDescribesTablesInTheShapesCallersRelyOn()
	{
		$this->assertSame(array('e107_item'), $this->db->getSchemaManager()->listTableNames('e107_'));
		$this->assertSame(array('item'), $this->db->tables());
		$this->assertTrue($this->db->isTable('item'));

		$fields = $this->db->field('item', 'item_id', '', true);
		$this->assertSame(array('Field' => 'item_id', 'Type' => 'integer', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'), $fields);
		$this->assertSame('UNI', $this->db->field('item', 'item_name', '', true)['Key']);
		$this->assertSame('', $this->db->field('item', 'item_name', '', true)['Default']);
		$this->assertTrue($this->db->index('item', 'item_hits'));
		$this->assertSame(array('item_id', 'item_name', 'item_class', 'item_hits', 'item_body'), $this->db->fields('item'));

		$defs = $this->db->getFieldDefs('item');
		$this->assertSame('int', $defs['_FIELD_TYPES']['item_hits']);
		$this->assertSame('escape', $defs['_FIELD_TYPES']['item_name']);
	}

	public function testCopyTruncateAndTransactionsWork()
	{
		$this->insertItems();

		$this->assertTrue($this->db->copyTable('item', 'item_copy', false, true) !== false);
		$this->assertSame(3, $this->db->count('item_copy'));
		$this->assertTrue($this->db->index('item_copy', 'item_name'), 'the copy has its own indexes');

		$this->assertTrue($this->db->truncate('item_copy'));
		$this->assertSame(0, $this->db->count('item_copy'));
		$this->db->insert('item_copy', array('item_name' => 'fresh', 'item_body' => ''));
		$this->assertSame(1, (int) $this->db->lastInsertId(), 'the counter starts again');

		$this->db->beginTransaction();
		$this->db->insert('item_copy', array('item_name' => 'gone', 'item_body' => ''));
		$this->db->rollBack();
		$this->assertSame(1, $this->db->count('item_copy'));
	}

	public function testTableChangesSqliteCanMakeInPlaceAreOneStatementEach()
	{
		$this->insertItems();
		$table = $this->db->schema()->table('item')
			->addColumn('item_note', Column::define('VARCHAR', 50)->notNull()->defaultValue('n/a'), 'item_body')
			->addIndex(Index::index('item_class', 'item_class'))
			->dropIndex('item_hits')
			->engine('InnoDB');

		$this->assertSame(array(
			"ALTER TABLE `e107_item` ADD COLUMN `item_note` TEXT COLLATE NOCASE NOT NULL DEFAULT 'n/a'",
			'CREATE INDEX `e107_item__item_class` ON `e107_item` (`item_class`)',
			'DROP INDEX `e107_item__item_hits`',
		), $table->getStatements(), 'no statement for the storage engine');
		$this->assertTrue($table->execute());

		$this->assertSame('n/a', $this->db->retrieve('item', 'item_note', 'item_id = 1'));
		$this->assertTrue($this->db->index('item', 'item_class'));
		$this->assertFalse($this->db->index('item', 'item_hits'));

		$rename = $this->db->schema()->table('item')->changeColumn('item_note', 'item_remark', Column::define('VARCHAR', 50)->notNull()->defaultValue('n/a'));
		$this->assertSame(array('ALTER TABLE `e107_item` RENAME COLUMN `item_note` TO `item_remark`'), $rename->getStatements());
		$this->assertSame('ALTER TABLE `e107_item` RENAME COLUMN `item_note` TO `item_remark`', $rename->getSQL());
		$this->assertNotFalse($rename->execute());
		$this->assertNotFalse($this->db->schema()->dropColumn('item', 'item_remark'));
		$this->assertSame(array('item_id', 'item_name', 'item_class', 'item_hits', 'item_body'), $this->db->fields('item'));
	}

	public function testOtherTableChangesRebuildTheTableKeepingItsRowsAndItsCounter()
	{
		$this->insertItems();
		$this->db->delete('item', 'item_id = 3');

		$table = $this->db->schema()->table('item')
			->modifyColumn('item_hits', Column::define('VARCHAR', 10)->notNull()->defaultValue(''))
			->changeColumn('item_class', 'item_classes', Column::define('TEXT')->notNull(), 'item_id')
			->addColumn('item_first', Column::define('INT', 10)->notNull()->defaultValue(7), SchemaBuilder::FIRST)
			->dropColumn('item_body');

		$statements = $table->getStatements();
		$this->assertSame('DROP TABLE IF EXISTS `e107_item__rebuild`', $statements[0]);
		$this->assertContains('DROP TABLE `e107_item`', $statements);

		try
		{
			$table->getSQL();
			$this->fail('a rebuild is no single statement');
		}
		catch(\e107\Database\Exception\UnsupportedException $e)
		{
			$this->assertStringContainsString('getStatements()', $e->getMessage());
		}

		$this->assertTrue($table->execute());
		$this->assertSame(array('item_first', 'item_id', 'item_classes', 'item_name', 'item_hits'), $this->db->fields('item'));
		$rows = $this->db->createQueryBuilder()->select('*')->from('item')->where('item_id', 1)->fetchAll();
		$this->assertSame(array('item_first' => '7', 'item_id' => '1', 'item_classes' => '1', 'item_name' => 'one', 'item_hits' => '3'), $rows[0]);
		$this->assertSame('text', $this->db->field('item', 'item_hits', '', true)['Type']);
		$this->assertTrue($this->db->index('item', 'item_name'), 'the unique index came back');

		$this->db->insert('item', array('item_name' => 'four', 'item_classes' => ''));
		$this->assertSame(4, (int) $this->db->lastInsertId(), 'the counter went on from 3, not from the highest id left');
	}

	public function testATableChangeSqliteCannotMakeIsRefused()
	{
		$this->expectException(\e107\Database\Exception\UnsupportedException::class);

		$this->db->schema()->table('item')->addRaw(\e107\Database\SqlFragment::raw('ALGORITHM=INPLACE'))->getStatements();
	}

	public function testATableIsCreatedFromTheSchemaDsl()
	{
		$schema = $this->db->schema();
		$this->assertTrue($schema->createTable('made', array(
			'made_id' => Column::define('INT', 10)->unsigned()->notNull()->autoIncrement(),
			'made_name' => Column::define('VARCHAR', 20)->notNull()->defaultValue(''),
		), array(Index::primary('made_id'), Index::unique('made_name', 'made_name')), array('engine' => 'InnoDB')));

		$this->assertSame(1, $this->db->insert('made', array('made_name' => 'x')));
		$this->assertTrue($this->db->index('made', 'made_name'));

		$this->assertSame(array(
			"CREATE TABLE `e107_raw` (\n  `raw_id` INTEGER NOT NULL DEFAULT 0\n)",
		), $schema->buildCreateTablePhysicalStatements('raw', \e107\Database\SqlFragment::raw('raw_id int NOT NULL')));
	}

	public function testADeclaredTableIsMaterialisedAndNothingIsLeftBehind()
	{
		$materialiser = new \e107\Database\Schema\Declared\Materialiser($this->db, $this->db->getSchemaManager()->getReader(), 'e107_');
		$declared = new \e107\Database\Schema\Declared\DeclaredTable('core', 'widget',
			"widget_id int(10) unsigned NOT NULL auto_increment,
			 widget_name varchar(255) NOT NULL default '',
			 widget_body text NOT NULL,
			 PRIMARY KEY (widget_id),
			 UNIQUE KEY widget_name (widget_name),
			 FULLTEXT KEY widget_body (widget_body)");

		$schema = $materialiser->materialise($declared, null, null);

		$this->assertSame(array('widget_id', 'widget_name', 'widget_body'), array_keys($schema->getColumns()));
		$this->assertSame(array('PRIMARY', 'widget_name'), array_keys($schema->getIndexes()), 'no FULLTEXT index is built on SQLite');
		$this->assertSame('', $schema->getEngine());
		$this->assertSame("`widget_name` text NOT NULL DEFAULT ''", $schema->getColumn('widget_name')->getDdl(), 'each definition comes back in the schema DSL');
		$this->assertSame('UNIQUE KEY `widget_name` (`widget_name`)', $schema->getIndex('widget_name')->getDdl());
		$this->assertSame('', $schema->getCreateOptions());

		$this->assertSame(array('e107_item'), $this->db->getSchemaManager()->listTableNames('e107_'), 'the scratch table was rolled back');
		$this->assertSame(0, $materialiser->sweep());
	}

	public function testADeclaredTableIsCreatedForSqliteAndAnExistingOneIsReported()
	{
		$declared = new \e107\Database\Schema\Declared\DeclaredTable('core', 'made',
			"made_id int(10) unsigned NOT NULL auto_increment, made_name varchar(20) NOT NULL default '', PRIMARY KEY (made_id), KEY made_name (made_name)", 'InnoDB', 'utf8mb4');

		$this->assertTrue($this->db->schema()->createDeclaredTable($declared));
		$this->assertTrue($this->db->index('made', 'made_name'));
		$this->assertSame(1, $this->db->insert('made', array('made_name' => 'x')));

		$this->assertFalse($this->db->schema()->createDeclaredTable($declared));
		$this->assertSame(ConnectionInterface::ERROR_TABLE_EXISTS, $this->db->getLastErrorNumber(), 'the failure the rollback followed is still the one reported');
	}

	public function testATransactionThatReadsBeforeItWritesIsNotOvertakenByAnotherWriter()
	{
		$this->db->insert('item', array('item_name' => 'counter', 'item_hits' => 0, 'item_body' => ''));
		$other = $this->connect();
		$other->execute('PRAGMA busy_timeout = 100');
		$counter = function($db)
		{
			return $db->createQueryBuilder()->update('item')->where('item_name', 'counter');
		};

		$this->assertTrue($this->db->beginTransaction());
		$read = (int) $this->db->createQueryBuilder()->select('item_hits')->from('item')->where('item_name', 'counter')->fetchOne();
		$this->assertFalse($counter($other)->set('item_hits', 10)->execute(), 'the other writer waits its turn');
		$this->assertSame(1, $counter($this->db)->set('item_hits', $read + 1)->execute(), $this->db->getLastErrorText());
		$this->assertTrue($this->db->commit());

		$this->assertSame('1', $other->createQueryBuilder()->select('item_hits')->from('item')->where('item_name', 'counter')->fetchOne());
	}

	public function testACommitTheEngineRefusesRollsTheTransactionBack()
	{
		$this->assertNotFalse($this->db->execute('PRAGMA foreign_keys = ON'));
		$this->assertNotFalse($this->db->execute('CREATE TABLE e107_parent (id INTEGER PRIMARY KEY)'));
		$this->assertNotFalse($this->db->execute('CREATE TABLE e107_child (id INTEGER PRIMARY KEY, parent_id INTEGER REFERENCES e107_parent (id) DEFERRABLE INITIALLY DEFERRED)'));

		try
		{
			$this->db->transactional(function($db)
			{
				$db->execute('INSERT INTO e107_child (id, parent_id) VALUES (1, 99)');
			});
			$this->fail('A commit the engine refused was reported as done.');
		}
		catch(\e107\Database\Exception\QueryException $e)
		{
			$this->assertStringContainsString('FOREIGN KEY', $e->getMessage());
		}

		$this->assertFalse($this->db->inTransaction());
		$this->assertSame(1, $this->db->execute('INSERT INTO e107_parent (id) VALUES (7)'));
		$this->db->close();
		$this->db = $this->connect();

		$this->assertSame(array('7'), $this->db->createQueryBuilder()->select('id')->from('parent')->fetchColumn(), 'a write after the refused commit was kept');
		$this->assertSame(array(), $this->db->createQueryBuilder()->select('id')->from('child')->fetchColumn());
	}

	public function testABeginInsideATransactionTheEngineRolledBackIsRefused()
	{
		$this->insertItems();

		$this->assertTrue($this->db->beginTransaction());
		$this->assertNotFalse($this->db->createQueryBuilder()->insert('item')->values(array('item_name' => 'outer', 'item_body' => ''))->execute());
		$this->assertFalse($this->db->execute("INSERT OR ROLLBACK INTO `#item` (item_name, item_body) VALUES ('one', '')"));
		$this->assertFalse($this->db->inTransaction());

		$this->assertFalse($this->db->beginTransaction(), 'a savepoint set now would open a transaction of its own');
		$this->assertSame(-1, $this->db->getLastErrorNumber());
		$this->db->rollBack();
		$this->assertSame(array(), $this->db->createQueryBuilder()->select('item_name')->from('item')->where('item_name', 'outer')->fetchColumn());
	}

	public function testAdvisoryLocksShutOutAnotherConnection()
	{
		$other = $this->connect();
		$name = 'e107_sqlite_test_'.md5($this->file);

		$this->assertTrue($this->db->acquireLock($name));
		$this->assertFalse($other->acquireLock($name));
		$this->assertTrue($this->db->releaseLock($name));
		$this->assertTrue($other->acquireLock($name));
		$this->assertTrue($other->releaseLock($name));
		$this->assertFalse($other->releaseLock($name), 'a lock no longer held is not released twice');
	}

	public function testAnAdvisoryLockBelongsToTheSessionAsOnMysql()
	{
		$other = $this->connect();
		$name = 'e107_sqlite_session_'.md5($this->file);

		$this->assertTrue($this->db->acquireLock($name));
		$clone = clone $this->db;
		$this->assertTrue($clone->acquireLock($name), 'a clone shares the session and its locks');
		$this->assertFalse($other->acquireLock($name));

		unset($clone);
		$this->db->close();
		$this->db = null;

		$this->assertTrue($other->acquireLock($name), 'closing the connection let the lock go');
		$this->assertTrue($other->releaseLock($name));
	}

	public function testALockFileLivesBesideTheDatabaseOnlyWhileTheLockIsHeld()
	{
		$name = 'e107_sqlite_file_'.md5($this->file);

		$this->assertTrue($this->db->acquireLock($name));
		$this->assertSame(array($this->file.'-lock-'.md5($name)), glob($this->file.'-lock-*'));

		$this->assertTrue($this->db->releaseLock($name));
		$this->assertSame(array(), glob($this->file.'-lock-*'));
	}

	public function testABackupRecreatesTheTablesInSqlite()
	{
		$this->insertItems();
		$backup = $this->file.'.sql';

		$this->assertSame($backup, $this->db->getDriver()->backup(array('database' => $this->file), array('e107_item'), $backup, array('droptable' => true)));

		$restored = new \PDO('sqlite::memory:');
		$restored->exec(file_get_contents($backup));
		$this->assertSame('3', (string) $restored->query('SELECT COUNT(*) FROM e107_item')->fetchColumn());
		unlink($backup);
	}

	public function testABackupRestoresEveryValueAsStoredAndFiresNoTrigger()
	{
		$this->insertItems();
		$body = serialize(new SqliteDriverTestProbe());
		$this->assertNotFalse(strpos($body, "\0"), 'a private property is serialised with NUL bytes');
		$this->db->createQueryBuilder()->update('item')->set('item_body', $body)->where('item_name', 'one')->execute();
		$this->assertNotFalse($this->db->execute("CREATE TRIGGER e107_item_mark AFTER INSERT ON e107_item BEGIN UPDATE e107_item SET item_body = item_body || '!' WHERE item_id = NEW.item_id; END"), $this->db->getLastErrorText());

		$read = 'SELECT item_name, item_body, typeof(item_body) AS type FROM e107_item ORDER BY item_id';
		$stored = (new \PDO('sqlite:'.$this->file))->query($read)->fetchAll(\PDO::FETCH_ASSOC);
		$backup = $this->file.'.sql';
		$this->db->getDriver()->backup(array('database' => $this->file), array('e107_item'), $backup, array('droptable' => true));

		$restored = new \PDO('sqlite::memory:', null, null, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		$restored->exec(file_get_contents($backup));
		unlink($backup);

		$this->assertSame($stored, $restored->query($read)->fetchAll(\PDO::FETCH_ASSOC));
		$this->assertSame('1', (string) $restored->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'trigger'")->fetchColumn());
	}

	public function testABackupKeepsEachTablesCounterSoARestoredSiteDoesNotHandOutADeletedId()
	{
		$this->insertItems();
		$this->db->createQueryBuilder()->insert('item')->values(array('item_name' => 'spam', 'item_body' => ''))->execute();
		$this->db->createQueryBuilder()->delete('item')->where('item_name', 'spam')->execute();
		$backup = $this->file.'.sql';
		$this->db->getDriver()->backup(array('database' => $this->file), array('e107_item'), $backup, array('droptable' => true));

		$restored = new \PDO('sqlite::memory:', null, null, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		$restored->exec(file_get_contents($backup));
		unlink($backup);
		$restored->exec("INSERT INTO e107_item (item_name, item_body) VALUES ('next', '')");

		$this->assertSame('5', (string) $restored->query("SELECT item_id FROM e107_item WHERE item_name = 'next'")->fetchColumn());
	}

	public function testABackupRefusedForATableNameLeavesNoFileBehind()
	{
		$this->insertItems();
		(new \PDO('sqlite:'.$this->file))->exec('CREATE TABLE "e107_my-plugin" (a INTEGER)');
		$backup = $this->file.'.sql';

		try
		{
			$this->db->getDriver()->backup(array('database' => $this->file), array('e107_item', 'e107_my-plugin'), $backup, array());
			$this->fail('A table name outside the identifier grammar was backed up.');
		}
		catch(\RuntimeException $e)
		{
			$this->assertStringContainsString('e107_my-plugin', $e->getMessage());
		}

		$this->assertFalse(file_exists($backup));
	}

	public function testALockTakenTwiceByOneSessionNeedsTwoReleasesAsOnMariaDb()
	{
		$other = $this->connect();
		$name = 'e107_sqlite_twice_'.md5($this->file);

		$this->assertTrue($this->db->acquireLock($name));
		$this->assertTrue($this->db->acquireLock($name));
		$this->assertTrue($this->db->releaseLock($name));
		$this->assertFalse($other->acquireLock($name), 'one release of two leaves the lock held');
		$this->assertTrue($this->db->releaseLock($name));
		$this->assertTrue($other->acquireLock($name));
		$this->assertTrue($other->releaseLock($name));
	}

	public function testALockStaysExclusiveWhileHoldersLetGoAndTakeItInOtherProcesses()
	{
		$counter = $this->file.'.counter';
		file_put_contents($counter, '0');
		$handlers = e_HANDLER;
		$child = '<?php
			require '.var_export($handlers.'Shims/PdoSqlite.php', true).';
			require '.var_export($handlers.'Reflection/ReflectionMethod.php', true).';
			require '.var_export($handlers.'Database/Driver/SqliteFunctions.php', true).';
			$open = function()
			{
				$pdo = \e107\Shims\PdoSqlite::connect('.var_export('sqlite:'.$this->file, true).', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				\e107\Database\Driver\SqliteFunctions::register($pdo, $pdo->query("SELECT sqlite_version()")->fetchColumn(), false);
				return $pdo;
			};
			$pdo = $open();
			for($i = 1; $i <= 150; $i++)
			{
				if((string) $pdo->query("SELECT get_lock(\'counted\', 20)")->fetchColumn() !== "1") { exit(1); }
				$value = (int) file_get_contents('.var_export($counter, true).');
				file_put_contents('.var_export($counter, true).', (string) ($value + 1));
				if($i % 7 === 0) { $pdo = null; $pdo = $open(); continue; }
				$pdo->query("SELECT release_lock(\'counted\')");
			}';
		$script = $this->file.'.child.php';
		file_put_contents($script, $child);
		$children = array();

		for($n = 0; $n < 16; $n++)
		{
			$children[] = proc_open(escapeshellarg(PHP_BINARY).' '.escapeshellarg($script), array(), $pipes);
		}

		foreach($children as $process)
		{
			$this->assertSame(0, proc_close($process));
		}

		$this->assertSame('2400', file_get_contents($counter), 'every increment ran under the lock');
		unlink($script);
		unlink($counter);
	}

	public function testARelativeDatabasePathLiesUnderTheRootTheDriverWasGiven()
	{
		$driver = new SqliteDriver(array('root' => dirname($this->file).'/'));
		$this->assertInstanceOf(\PDO::class, $driver->selectDatabase(null, basename($this->file), array()));

		try
		{
			(new SqliteDriver())->selectDatabase(null, basename($this->file), array());
			$this->fail('A relative path was opened with no root to put it under.');
		}
		catch(\PDOException $e)
		{
			$this->assertStringContainsString('e107 root', $e->getMessage());
		}
	}

	public function testTheDriverRegistersTheCompatibilityPackItIsToldTo()
	{
		foreach(array(true, false) as $compat)
		{
			$driver = new SqliteDriver(array('mysql_compat' => $compat));
			$pdo = $driver->selectDatabase(null, $this->file, array());
			$driver->configure($pdo);

			try
			{
				$answer = $pdo->query("SELECT left('abc', 1)")->fetchColumn();
			}
			catch(\PDOException $e)
			{
				$answer = null;
			}

			$this->assertSame($compat ? 'a' : null, $answer, 'mysql_compat '.var_export($compat, true));
		}
	}

	public function testABackupReadsEveryTableFromOneSnapshot()
	{
		$this->createTable('CREATE TABLE later (later_id int(10) unsigned NOT NULL auto_increment, PRIMARY KEY (later_id)) ENGINE=InnoDB;');
		$this->insertItems();
		$writer = $this->connect();
		SqliteDriverTestOutput::$onWrite = function($text) use ($writer)
		{
			if(strpos($text, 'INSERT INTO `e107_item`') !== false)
			{
				$writer->createQueryBuilder()->insert('later')->values(array('later_id' => 7))->execute();
			}
		};
		stream_wrapper_register('e107sqlitebackup', SqliteDriverTestOutput::class);

		try
		{
			$this->db->getDriver()->backup(array('database' => $this->file), array('e107_item', 'e107_later'), 'e107sqlitebackup://dump', array());
		}
		finally
		{
			stream_wrapper_unregister('e107sqlitebackup');
		}

		$this->assertSame(array('7'), $this->db->createQueryBuilder()->select('later_id')->from('later')->fetchColumn(), 'the row was written while the backup ran');
		$this->assertSame(3, substr_count(SqliteDriverTestOutput::$written, 'INSERT INTO `e107_item`'));
		$this->assertStringNotContainsString('INSERT INTO `e107_later`', SqliteDriverTestOutput::$written);
	}

	/**
	 * @return \e_db_pdo
	 */
	private function connect()
	{
		$db = new \e_db_pdo();
		$db->useDriver('sqlite');
		$db->connect('', '', '');
		$this->assertTrue($db->database($this->file, 'e107_'), $db->getLastErrorText());

		return $db;
	}

	/**
	 * @param string $ddl e107 schema DSL
	 * @return void
	 */
	private function createTable($ddl)
	{
		$table = (new MysqlDdlParser())->parseCreateTable($ddl);

		foreach($this->db->getPlatform()->compileTableDefinition('e107_'.$table->getName(), $table) as $sql)
		{
			$this->assertNotFalse($this->db->execute($sql), $this->db->getLastErrorText());
		}
	}

	/**
	 * @return void
	 */
	private function insertItems()
	{
		foreach(array(
			array('one', '1', 3, 'nothing to see'),
			array('two', '2', 6, 'the lazy dog'),
			array('three', '2,3', 9, 'the quick brown fox'),
		) as $row)
		{
			$this->db->createQueryBuilder()->insert('item')->values(array('item_name' => $row[0], 'item_class' => $row[1], 'item_hits' => $row[2], 'item_body' => $row[3]))->execute();
		}
	}

	/**
	 * @return void
	 */
	private function removeFiles()
	{
		foreach(array('', '-wal', '-shm', '.missing', '.sql', '.counter', '.child.php') as $suffix)
		{
			if(is_file($this->file.$suffix))
			{
				unlink($this->file.$suffix);
			}
		}

		// getFieldDefs() caches a table's field types on disk by its name, whatever connection asked.
		foreach(array('item', 'item_copy', 'plain', 'made') as $table)
		{
			if(is_file(e_CACHE_DB.$table.'.php'))
			{
				unlink(e_CACHE_DB.$table.'.php');
			}
		}
	}
}

class SqliteDriverTestProbe
{
	private $secret = 'kept';
}

/**
 * A backup destination that hands each write to a callback before keeping it.
 */
class SqliteDriverTestOutput
{
	/** @var resource|null set by PHP */
	public $context;

	/** @var callable */
	public static $onWrite;

	/** @var string */
	public static $written = '';

	public function stream_open($path, $mode, $options, &$openedPath)
	{
		self::$written = '';

		return true;
	}

	public function stream_write($data)
	{
		call_user_func(self::$onWrite, $data);
		self::$written .= $data;

		return strlen($data);
	}

	public function stream_close()
	{
	}
}
