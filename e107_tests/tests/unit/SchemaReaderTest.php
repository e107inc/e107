<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Exception\QueryException;
use e107\Database\Schema\Introspect\IndexPart;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SchemaReader;

/**
 * Reads real tables out of the test database and checks the answer against what
 * SHOW CREATE TABLE says about the same table.
 *
 * The fixture is a v2.3.0-era dump, deliberately drifted from what core_sql.php
 * declares today; shapes it lacks are exercised against a scratch table.
 */
class SchemaReaderTest extends \Test\Unit
{
	/** The comment the scratch table's commented column carries. */
	const SCRATCH_COMMENT = 'Kept verbatim: a comment that changes is drift';

	/** @var SchemaReader */
	private $reader;

	/** @var string prefixed name of the scratch table these tests own */
	private $scratch;

	protected function _before()
	{
		$this->reader = new SchemaReader(e107::getDb());
		$this->scratch = MPREFIX.'schemareader_scratch';

		$this->dropScratchTable();
	}

	protected function _after()
	{
		$this->dropScratchTable();
	}

	// --- reading a real table ---------------------------------------------

	public function testTableMetadataMatchesShowCreateTable()
	{
		$table = $this->reader->read(MPREFIX.'admin_log');

		$this->assertNotNull($table, MPREFIX.'admin_log must exist in the test database.');
		$this->assertSame(MPREFIX.'admin_log', $table->getName());
		$this->assertTrue($table->hasEngine('myisam'), 'The engine is compared case-insensitively.');
		$this->assertSame('MyISAM', $table->getEngine(), 'The engine is stored as the server canonicalises it.');
		$this->assertContains($table->getCollation(), array('utf8mb3_general_ci', 'utf8_general_ci'),
			'MySQL 5.7 spells the three-byte set utf8; MariaDB 10.11 and MySQL 8 spell it utf8mb3.');
		$this->assertContains($table->getCharset(), array('utf8mb3', 'utf8'),
			'The charset is TABLE_COLLATION up to its first underscore, in whichever spelling this server uses.');
	}

	public function testColumnsAreReadInOrdinalOrder()
	{
		$table = $this->reader->read(MPREFIX.'admin_log');

		$this->assertSame(
			array(
				'dblog_id',
				'dblog_datestamp',
				'dblog_microtime',
				'dblog_type',
				'dblog_eventcode',
				'dblog_user_id',
				'dblog_ip',
				'dblog_title',
				'dblog_remarks',
			),
			array_keys($table->getColumns())
		);

		$position = 0;

		foreach($table->getColumns() as $column)
		{
			$position++;
			$this->assertSame($position, $column->getPosition(), $column->getName().' is the column in slot '.$position.'.');
		}
	}

	public function testEveryColumnTypeAppearsInShowCreateTable()
	{
		$table = $this->reader->read(MPREFIX.'admin_log');
		$create = strtolower($this->showCreateTable(MPREFIX.'admin_log'));

		foreach($table->getColumns() as $name => $column)
		{
			$this->assertStringContainsString(
				'`'.$name.'` '.$column->getColumnType(),
				$create,
				'The reader must report '.$name.' with the type SHOW CREATE TABLE renders.'
			);
		}
	}

	public function testAnAutoIncrementColumnIsReadWholeAndVerbatim()
	{
		$column = $this->reader->read(MPREFIX.'admin_log')->getColumn('dblog_id');

		$this->assertContains(
			$column->getColumnType(),
			array('int(10) unsigned', 'int unsigned'),
			'The type token is one opaque field carrying the base type, whatever display width the server chooses '
			.'to state, and UNSIGNED, all together. MariaDB 10.11 states int(10) unsigned and MySQL 8 dropped the '
			.'width, so the two disagree - and neither side of a verify is spared that, which is why neither needs '
			.'a rule for it. testEveryColumnTypeAppearsInShowCreateTable pins the token against the server itself '
			.'on whichever vendor is running.'
		);
		$this->assertStringContainsString('unsigned', $column->getColumnType(), 'UNSIGNED is part of the token, not a field of its own.');
		$this->assertFalse($column->isNullable());
		$this->assertNull($column->getDefault(), 'An AUTO_INCREMENT column has no default.');
		$this->assertSame('auto_increment', $column->getExtra());
		$this->assertSame('', $column->getComment());
		$this->assertSame(1, $column->getPosition());
	}

	public function testAStringColumnCarriesItsServerSpelledDefault()
	{
		$column = $this->reader->read(MPREFIX.'admin_log')->getColumn('dblog_eventcode');

		$this->assertSame('varchar(10)', $column->getColumnType());
		$this->assertFalse($column->isNullable());
		$this->assertSame('', $column->getExtra());
		$this->assertContains(
			$column->getDefault(),
			array('', "''"),
			"An empty-string default reads as '' on MySQL and as \"''\" on MariaDB. The reader keeps whichever "
			."the server said, because both sides of a verify are read from the same server and so agree."
		);
	}

	public function testANullableColumnIsDistinguishedFromANotNullOne()
	{
		$table = $this->reader->read(MPREFIX.'news');

		$this->assertTrue(
			$table->getColumn('news_template')->isNullable(),
			'news_template is varchar(50) NULL in the fixture, and NULL against NOT NULL is drift the '
			.'reader has to be able to see.'
		);
		$this->assertFalse($table->getColumn('news_id')->isNullable());
	}

	public function testADefaultIsKeptExactlyAsTheServerSpellsIt()
	{
		$table = $this->reader->read(MPREFIX.'news');

		$this->assertNull(
			$table->getColumn('news_id')->getDefault(),
			'A column with no default at all is the one case that is SQL NULL on both backends.'
		);
		$this->assertContains(
			$table->getColumn('news_template')->getDefault(),
			array(null, 'NULL'),
			'DEFAULT NULL reads as the literal "NULL" on MariaDB and as SQL NULL on MySQL.'
		);
	}

	public function testAColumnCommentIsReadVerbatim()
	{
		$table = $this->readScratchTable();

		$this->assertSame(self::SCRATCH_COMMENT, $table->getColumn('scratch_commented')->getComment());
		$this->assertSame('', $table->getColumn('scratch_title')->getComment(), 'An uncommented column reports the empty string.');
	}

	public function testIndexesMatchShowCreateTable()
	{
		$table = $this->reader->read(MPREFIX.'admin_log');
		$create = strtolower($this->showCreateTable(MPREFIX.'admin_log'));

		$this->assertSame(array('dblog_datestamp', 'PRIMARY'), array_keys($table->getIndexes()));

		$this->assertStringContainsString('primary key (`dblog_id`)', $create);
		$this->assertStringContainsString('key `dblog_datestamp` (`dblog_datestamp`)', $create);

		$primary = $table->getIndex('PRIMARY');
		$this->assertSame(IndexSchema::KIND_PRIMARY, $primary->getKind(), 'PRIMARY is reported by name, not by uniqueness.');
		$this->assertSame(array('dblog_id'), $primary->getColumnNames());

		$secondary = $table->getIndex('dblog_datestamp');
		$this->assertSame(IndexSchema::KIND_INDEX, $secondary->getKind());
		$this->assertSame(array('dblog_datestamp'), $secondary->getColumnNames());
		$this->assertNull($secondary->getParts()[0]->getSubPart(), 'A whole-column index part has no prefix length.');
		$this->assertSame(IndexPart::ASC, $secondary->getParts()[0]->getDirection());
	}

	public function testAUniqueIndexIsDistinguishedFromAnOrdinaryOne()
	{
		$table = $this->reader->read(MPREFIX.'user');

		$this->assertSame(IndexSchema::KIND_UNIQUE, $table->getIndex('user_loginname')->getKind());
		$this->assertSame(IndexSchema::KIND_INDEX, $table->getIndex('join_ban_index')->getKind());
	}

	// --- composite indexes are ordered ------------------------------------

	public function testACompositeIndexKeepsItsPartOrder()
	{
		$this->assertSame(
			array('news_start', 'news_end'),
			$this->reader->read(MPREFIX.'news')->getIndex('news_start_end')->getColumnNames(),
			'KEY (a, b) and KEY (b, a) are different indexes, so SEQ_IN_INDEX order is load-bearing.'
		);

		$this->assertSame(
			array('user_join', 'user_ban'),
			$this->reader->read(MPREFIX.'user')->getIndex('join_ban_index')->getColumnNames()
		);
	}

	// --- absent tables ----------------------------------------------------

	public function testReadReturnsNullForATableThatDoesNotExist()
	{
		$this->assertNull(
			$this->reader->read(MPREFIX.'no_such_table_at_all'),
			'A table that was never created is the ordinary missing-table case, not a failure.'
		);
	}

	public function testReadReturnsNullForAnEmptyName()
	{
		$this->assertNull($this->reader->read(''));
	}

	public function testReadManyOmitsTablesThatDoNotExist()
	{
		$tables = $this->reader->readMany(array(
			MPREFIX.'news',
			MPREFIX.'no_such_table_at_all',
			MPREFIX.'user',
		));

		$this->assertSame(array(MPREFIX.'news', MPREFIX.'user'), array_keys($tables));
		$this->assertSame(MPREFIX.'news', $tables[MPREFIX.'news']->getName());
		$this->assertSame(MPREFIX.'user', $tables[MPREFIX.'user']->getName());
	}

	public function testATableIsMatchedOnItsExactName()
	{
		$this->readScratchTable();

		$asked = strtoupper($this->scratch);

		$this->assertNull($this->reader->read($asked), 'Alone, the name matches nothing.');
		$this->assertSame(
			array(MPREFIX.'news'),
			array_keys($this->reader->readMany(array($asked, MPREFIX.'news'))),
			'In company, the same name still matches nothing.'
		);
	}

	public function testReadManyOfNothingAsksTheDatabaseNothing()
	{
		$before = e107::getDb()->queryCount();

		$this->assertSame(array(), $this->reader->readMany(array()));
		$this->assertSame($before, e107::getDb()->queryCount());
	}

	/**
	 * More than ten tables on purpose. The placeholders are named :t0, :t1, ...
	 * and a driver that substituted them by plain string replacement would
	 * corrupt :t1 while filling :t10, so the boundary is crossed here rather
	 * than left to a caller to discover.
	 */
	public function testReadManyCostsThreeQueriesHoweverManyTablesAreAskedFor()
	{
		$db = e107::getDb();
		$names = array();

		foreach(self::twelveTableNames() as $table)
		{
			$names[] = MPREFIX.$table;
		}

		$before = $db->queryCount();
		$one = $this->reader->readMany(array(MPREFIX.'news'));
		$forOne = $db->queryCount() - $before;

		$before = $db->queryCount();
		$many = $this->reader->readMany($names);
		$forMany = $db->queryCount() - $before;

		$read = array_keys($many);
		sort($names);
		sort($read);

		$this->assertCount(1, $one);
		$this->assertSame(3, $forOne, 'One TABLES query, one COLUMNS query, one STATISTICS query.');
		$this->assertSame(3, $forMany, 'Still three: a whole-site verify is three round trips, not three per table.');
		$this->assertSame($names, $read, 'Every one of the twelve is read, and keyed by the name it was asked for.');
	}

	public function testEveryTableOfALargeBatchIsReadWhole()
	{
		$names = array();

		foreach(self::twelveTableNames() as $table)
		{
			$names[] = MPREFIX.$table;
		}

		$batch = $this->reader->readMany($names);

		foreach($names as $name)
		{
			$alone = $this->reader->read($name);

			$this->assertNotNull($alone, $name.' must exist in the fixture.');
			$this->assertTrue(
				$batch[$name]->equals($alone),
				$name.' must read the same in a batch of twelve as it does on its own.'
			);
			$this->assertNotSame(array(), $batch[$name]->getColumns(), $name.' must carry its columns.');
		}
	}

	// --- failing to read is never "no such table" -------------------------

	public function testAFailureOnAnyOfTheThreeQueriesThrowsRatherThanReportingNoSuchTable()
	{
		foreach(array('TABLES', 'COLUMNS', 'STATISTICS') as $view)
		{
			$reader = new SchemaReader(new SchemaReaderScriptedDb(self::scriptedRows(), $view, 'server went away'));
			$thrown = null;

			try
			{
				$reader->read('scripted_table');
			}
			catch(QueryException $e)
			{
				$thrown = $e;
			}

			$this->assertInstanceOf(
				'e107\Database\Exception\QueryException',
				$thrown,
				'A failed query against information_schema.'.$view.' must throw, not return null.'
			);
			$this->assertStringContainsString($view, $thrown->getMessage(), 'The failing view is named.');
			$this->assertStringContainsString('server went away', $thrown->getMessage(), "The driver's own error is carried.");
		}
	}

	// --- EXTRA is normalised ----------------------------------------------

	/**
	 * Scripted because MariaDB never writes DEFAULT_GENERATED, so no live fixture holds it.
	 */
	public function testMysqlEightsDefaultGeneratedMarkerIsStrippedFromExtra()
	{
		$table = (new SchemaReader(new SchemaReaderScriptedDb(self::scriptedRows())))->read('scripted_table');

		$this->assertNotNull($table);
		$this->assertSame(
			'on update current_timestamp',
			$table->getColumn('scripted_updated')->getExtra(),
			'DEFAULT_GENERATED goes, the rest survives lowercased and single-spaced.'
		);
		$this->assertSame(
			'',
			$table->getColumn('scripted_ref')->getExtra(),
			'A column whose whole EXTRA was the marker is left with nothing.'
		);
		$this->assertSame('auto_increment', $table->getColumn('scripted_id')->getExtra());
		$this->assertSame(
			'VARCHAR(32)',
			$table->getColumn('scripted_ref')->getColumnType(),
			'COLUMN_TYPE is passed through verbatim: lowercasing it would fold ENUM and SET members, which are user data.'
		);
	}

	public function testALiveTableIsReadWithoutAnyCapturedDdl()
	{
		$table = $this->reader->read(MPREFIX.'admin_log');

		$this->assertNull($table->getCreateBody());
		$this->assertNull($table->getCreateOptions());

		foreach($table->getColumns() as $name => $column)
		{
			$this->assertNull($column->getDdl(), $name.' is read, not rendered.');
		}

		foreach($table->getIndexes() as $name => $index)
		{
			$this->assertNull($index->getDdl(), $name.' is read, not rendered.');
		}
	}

	// --- a functional index -------------------------------------------------

	/**
	 * Scripted because MariaDB 10.11, which this suite runs on, has neither functional
	 * indexes nor the EXPRESSION column that carries their text.
	 */
	public function testAFunctionalIndexIsReadRatherThanRefused()
	{
		$rows = self::scriptedRows();
		$rows['STATISTICS'][] = array(
			'TABLE_NAME' => 'scripted_table', 'INDEX_NAME' => 'scripted_functional', 'SEQ_IN_INDEX' => '1',
			'COLUMN_NAME' => null, 'COLLATION' => 'A', 'SUB_PART' => null,
			'NON_UNIQUE' => '1', 'INDEX_TYPE' => 'BTREE',
		);

		$table = (new SchemaReader(new SchemaReaderScriptedDb($rows)))->read('scripted_table');

		$this->assertNotNull($table, 'A functional index must not abort the read.');

		$index = $table->getIndex('scripted_functional');

		$this->assertInstanceOf(IndexSchema::class, $index);
		$this->assertSame(IndexSchema::KIND_INDEX, $index->getKind());

		$parts = $index->getParts();

		$this->assertCount(1, $parts);
		$this->assertTrue($parts[0]->isOverExpression(), 'A part with no COLUMN_NAME indexes an expression.');
		$this->assertNull($parts[0]->getColumnName());
		$this->assertSame(IndexPart::ASC, $parts[0]->getDirection(), 'The rest of the row is still read.');
		$this->assertSame(array(null), $index->getColumnNames());
	}

	public function testAFunctionalIndexNeverMatchesADeclaredColumnIndex()
	{
		$rows = self::scriptedRows();
		$rows['STATISTICS'][] = array(
			'TABLE_NAME' => 'scripted_table', 'INDEX_NAME' => 'scripted_ref_key', 'SEQ_IN_INDEX' => '1',
			'COLUMN_NAME' => null, 'COLLATION' => 'A', 'SUB_PART' => null,
			'NON_UNIQUE' => '1', 'INDEX_TYPE' => 'BTREE',
		);

		$live = (new SchemaReader(new SchemaReaderScriptedDb($rows)))->read('scripted_table');

		$declared = new IndexSchema(
			'scripted_ref_key',
			IndexSchema::KIND_INDEX,
			array(new IndexPart('scripted_ref', null, 'ASC'))
		);

		$this->assertFalse(
			$declared->equals($live->getIndex('scripted_ref_key')),
			'An index over an expression is not the declared index over a column.'
		);
	}

	public function testAnIndexPartThatLosesItsColumnNameIsStillRefused()
	{
		$this->expectException('InvalidArgumentException');

		new IndexPart('', null, 'A');
	}

	public function testAnIndexPartCannotBeBothAColumnAndAnExpression()
	{
		$this->expectException('InvalidArgumentException');

		new IndexPart('scripted_ref', null, 'A', true);
	}

	public function testAModernCollationStillYieldsItsCharset()
	{
		$table = (new SchemaReader(new SchemaReaderScriptedDb(self::scriptedRows())))->read('scripted_table');

		$this->assertSame('utf8mb4', $table->getCharset(), 'utf8mb4_0900_ai_ci is a utf8mb4 collation.');
		$this->assertSame('utf8mb4_0900_ai_ci', $table->getCollation());
		$this->assertNull($table->getColumn('scripted_ref')->getCharset(), 'Rule N1 holds against a MySQL 8 collation too.');
		$this->assertNull($table->getColumn('scripted_ref')->getCollation());
	}

	// --- a column charset matching the table default reads as none --------

	public function testAColumnUsingTheTableDefaultCharsetReportsNoneOfItsOwn()
	{
		$table = $this->reader->read(MPREFIX.'admin_log');

		foreach($table->getColumns() as $name => $column)
		{
			$this->assertNull($column->getCharset(), $name.' uses the table default charset, so it reports none of its own.');
			$this->assertNull($column->getCollation(), $name.' uses the table default collation, so it reports none of its own.');
		}

		$this->assertContains($table->getCharset(), array('utf8mb3', 'utf8'),
			'The table default itself is still reported, once, in this server\'s spelling.');
	}

	public function testAColumnThatOverridesTheTableCharsetKeepsIt()
	{
		$table = $this->readScratchTable();

		$this->assertSame('utf8mb4', $table->getCharset());
		$this->assertNull($table->getColumn('scratch_title')->getCharset(), 'scratch_title takes the table default.');
		$this->assertSame('latin1', $table->getColumn('scratch_latin')->getCharset());
		$this->assertSame('latin1_swedish_ci', $table->getColumn('scratch_latin')->getCollation());
	}

	// --- shapes the dump does not contain ---------------------------------

	public function testAFulltextIndexIsReportedAsFulltext()
	{
		$index = $this->readScratchTable()->getIndex('scratch_fulltext');

		$this->assertSame(IndexSchema::KIND_FULLTEXT, $index->getKind());
		$this->assertSame(array('scratch_title', 'scratch_body'), $index->getColumnNames());

		foreach($index->getParts() as $part)
		{
			$this->assertNull($part->getDirection(), 'A FULLTEXT index has no sort order, so COLLATION is NULL.');
		}
	}

	public function testAnIndexPrefixLengthIsReported()
	{
		$parts = $this->readScratchTable()->getIndex('scratch_title_prefix')->getParts();

		$this->assertCount(1, $parts);
		$this->assertSame(10, $parts[0]->getSubPart(), 'SUB_PART is an int, not the string the driver hands over.');
	}

	// --- helpers ----------------------------------------------------------

	/**
	 * Twelve unprefixed fixture table names: enough to take the placeholder list past :t9.
	 *
	 * @return string[]
	 */
	private static function twelveTableNames()
	{
		return array(
			'news', 'user', 'admin_log', 'core', 'plugin', 'menus',
			'links', 'online', 'page', 'comments', 'generic', 'rss',
		);
	}

	/**
	 * One scripted table as MySQL 8 describes it, for the rules MariaDB cannot exercise.
	 *
	 * @return array view name => rows, as information_schema would return them
	 */
	private static function scriptedRows()
	{
		return array(
			'TABLES' => array(
				array('TABLE_NAME' => 'scripted_table', 'ENGINE' => 'InnoDB', 'TABLE_COLLATION' => 'utf8mb4_0900_ai_ci'),
			),
			'COLUMNS' => array(
				array(
					'TABLE_NAME' => 'scripted_table', 'COLUMN_NAME' => 'scripted_id',
					'COLUMN_TYPE' => 'int unsigned', 'IS_NULLABLE' => 'NO', 'COLUMN_DEFAULT' => null,
					'EXTRA' => 'auto_increment', 'CHARACTER_SET_NAME' => null, 'COLLATION_NAME' => null,
					'COLUMN_COMMENT' => '', 'ORDINAL_POSITION' => '1',
				),
				array(
					'TABLE_NAME' => 'scripted_table', 'COLUMN_NAME' => 'scripted_ref',
					'COLUMN_TYPE' => 'VARCHAR(32)', 'IS_NULLABLE' => 'NO', 'COLUMN_DEFAULT' => '',
					'EXTRA' => 'DEFAULT_GENERATED', 'CHARACTER_SET_NAME' => 'utf8mb4',
					'COLLATION_NAME' => 'utf8mb4_0900_ai_ci', 'COLUMN_COMMENT' => '', 'ORDINAL_POSITION' => '2',
				),
				array(
					'TABLE_NAME' => 'scripted_table', 'COLUMN_NAME' => 'scripted_updated',
					'COLUMN_TYPE' => 'timestamp', 'IS_NULLABLE' => 'NO', 'COLUMN_DEFAULT' => 'CURRENT_TIMESTAMP',
					'EXTRA' => 'DEFAULT_GENERATED on update CURRENT_TIMESTAMP', 'CHARACTER_SET_NAME' => null,
					'COLLATION_NAME' => null, 'COLUMN_COMMENT' => '', 'ORDINAL_POSITION' => '3',
				),
			),
			'STATISTICS' => array(
				array(
					'TABLE_NAME' => 'scripted_table', 'INDEX_NAME' => 'PRIMARY', 'SEQ_IN_INDEX' => '1',
					'COLUMN_NAME' => 'scripted_id', 'COLLATION' => 'A', 'SUB_PART' => null,
					'NON_UNIQUE' => '0', 'INDEX_TYPE' => 'BTREE',
				),
			),
		);
	}

	/**
	 * Create the scratch table and read it back.
	 *
	 * Plain rather than TEMPORARY: a temporary table is invisible to information_schema.
	 *
	 * @return \e107\Database\Schema\Introspect\TableSchema
	 */
	private function readScratchTable()
	{
		$created = e107::getDb()->execute(
			'CREATE TABLE `'.$this->scratch.'` ('
			.' `scratch_id` int(10) unsigned NOT NULL AUTO_INCREMENT,'
			.' `scratch_title` varchar(100) NOT NULL DEFAULT \'\','
			.' `scratch_latin` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT \'\','
			.' `scratch_commented` varchar(20) NOT NULL DEFAULT \'\' COMMENT \''.self::SCRATCH_COMMENT.'\','
			.' `scratch_body` text NOT NULL,'
			.' PRIMARY KEY (`scratch_id`),'
			.' KEY `scratch_title_prefix` (`scratch_title`(10)),'
			.' FULLTEXT KEY `scratch_fulltext` (`scratch_title`, `scratch_body`)'
			.') ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
		);

		$this->assertNotFalse($created, 'The scratch table must be creatable: '.e107::getDb()->getLastErrorText());

		$table = $this->reader->read($this->scratch);

		$this->assertNotNull($table, 'The scratch table must be readable once created.');

		return $table;
	}

	private function dropScratchTable()
	{
		e107::getDb()->execute('DROP TABLE IF EXISTS `'.$this->scratch.'`');
	}

	/**
	 * @param string $physicalTableName
	 * @return string the CREATE TABLE statement the server renders.
	 */
	private function showCreateTable($physicalTableName)
	{
		$db = e107::getDb();

		$this->assertNotFalse($db->execute('SHOW CREATE TABLE `'.$physicalTableName.'`'));

		$row = $db->fetch();

		$this->assertIsArray($row, 'SHOW CREATE TABLE must return a row for '.$physicalTableName.'.');

		return $row['Create Table'];
	}
}

/**
 * A database connection that answers each of the reader's three queries from a
 * canned row set, and can be told to fail one of them.
 *
 * It carries only the three methods {@see SchemaReader} calls.
 */
class SchemaReaderScriptedDb
{
	/** @var array view name => rows */
	private $rows;

	/** @var string|null view name whose query must fail */
	private $failOn;

	/** @var string */
	private $error;

	/** @var array rows the current statement has left to hand over */
	private $pending = array();

	/**
	 * @param array $rows view name => rows, as {@see SchemaReaderTest::scriptedRows()} builds them.
	 * @param string|null $failOn view name whose query reports an error.
	 * @param string $error the text getLastErrorText() then returns.
	 */
	public function __construct(array $rows, $failOn = null, $error = 'scripted failure')
	{
		$this->rows = $rows;
		$this->failOn = $failOn;
		$this->error = $error;
	}

	/**
	 * @param string $sql
	 * @param array $params
	 * @return int|bool row count, or false when this view is the one told to fail.
	 */
	public function execute($sql, $params = array())
	{
		$view = self::viewOf($sql);
		$this->pending = array();

		if($view === $this->failOn)
		{
			return false;
		}

		$this->pending = isset($this->rows[$view]) ? $this->rows[$view] : array();

		return count($this->pending);
	}

	/**
	 * @param string|null $type
	 * @return array|bool
	 */
	public function fetch($type = null)
	{
		if(empty($this->pending))
		{
			return false;
		}

		return array_shift($this->pending);
	}

	/**
	 * @return string
	 */
	public function getLastErrorText()
	{
		return $this->error;
	}

	/**
	 * @param string $sql
	 * @return string the information_schema view the statement reads, or ''.
	 */
	private static function viewOf($sql)
	{
		foreach(array('TABLES', 'COLUMNS', 'STATISTICS') as $view)
		{
			if(strpos($sql, 'information_schema.'.$view) !== false)
			{
				return $view;
			}
		}

		return '';
	}
}
