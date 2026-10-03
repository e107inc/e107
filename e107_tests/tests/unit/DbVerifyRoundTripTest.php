<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Column;
use e107\Database\Schema\Diff\TableDiff;
use e107\Database\Schema\Index;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\Table;

/**
 * The round-trip property: break a live table, repair it through {@see \db_verify}, then read the database back.
 *
 * The unit database is a v2.3.0 dump in which most core tables are already drifted, so every expected shape is stated
 * here by hand from core_sql.php rather than read back from the code under test. The damage goes through the schema
 * builder and the reading through the connection's own reader, so the property is checked on whatever engine the
 * suite runs on; a type MySQL spells back is asserted where MySQL runs.
 */
class DbVerifyRoundTripTest extends \Test\Unit
{
	/** @var string[] column order of `news` as core_sql.php declares it */
	private static $declaredNewsColumns = array(
		'news_id', 'news_title', 'news_sef', 'news_body', 'news_extended',
		'news_meta_title', 'news_meta_keywords', 'news_meta_description',
		'news_meta_robots', 'news_datestamp', 'news_modified', 'news_author',
		'news_category', 'news_allow_comments', 'news_start', 'news_end',
		'news_class', 'news_render_type', 'news_comment_total', 'news_summary',
		'news_thumbnail', 'news_sticky', 'news_template',
	);

	/** @var string[] column order of `rate` as core_sql.php declares it */
	private static $declaredRateColumns = array(
		'rate_id', 'rate_table', 'rate_itemid', 'rate_rating', 'rate_votes',
		'rate_voters', 'rate_up', 'rate_down',
	);

	/** @var array logical table name => physical name of its backup copy */
	private $snapshots = array();

	protected function _before()
	{

		require_once(e_HANDLER . 'db_verify_class.php');
	}

	protected function _after()
	{

		foreach($this->snapshots as $table => $backup)
		{
			$this->restore($table, $backup);
		}

		$this->snapshots = array();
	}

	// --- columns ----------------------------------------------------------

	public function testADroppedColumnComesBackInItsDeclaredPosition()
	{

		$this->snapshot('news');

		$this->alter('news', $this->table('news')->dropColumn('news_class'));

		$broken = $this->driftOf('news');
		$this->assertArrayHasKey(
			'news_class',
			$broken->getMissingColumns(),
			'Dropping news_class must be reported as a missing column.'
		);

		$this->repair('news');

		$restored = $this->columnOf('news', 'news_class');
		$this->assertNotNull($restored, 'news_class must be back in the live table.');
		$this->assertMysqlType('varchar(255)', $restored);
		$this->assertEquals('NO', $restored['IS_NULLABLE']);
		$this->assertEquals('0', self::defaultOf($restored));

		$this->assertEquals(
			self::$declaredNewsColumns,
			$this->columnNames('news'),
			'A re-added column belongs where the schema file puts it, not at the end of the table: '
			. 'news_class is declared between news_end and news_render_type, so the AFTER clause has to be right.'
		);

		$this->assertFalse($this->driftOf('news')->hasDrift(), 'The repaired news table must verify clean.');
	}

	public function testAChangeOfSeveralStatementsRunsNoneOfThemWhenNoTransactionCanBeOpened()
	{

		if(!e107::getDb()->getPlatform()->supportsTransactionalDdl())
		{
			$this->markTestSkipped('Only an engine whose DDL is transactional makes a change of several statements as one.');
		}

		$this->snapshot('news');
		$this->alter('news', $this->table('news')->dropColumn('news_class'));

		$dbv = $this->verifierFor('news');
		$dbv->compare('core');
		$dbv->compileResults();

		$this->runStatement('BEGIN');

		try
		{
			$dbv->runFix();
			$columns = $this->columnNames('news');
		}
		finally
		{
			e107::getDb()->execute('ROLLBACK');
		}

		$this->assertNotContains('news_class', $columns, 'No statement of the rebuild may run outside a transaction of its own.');
		$this->assertArrayHasKey('news', $dbv->getErrors(), 'A change that did not run leaves its table reported.');
	}

	public function testANarrowedColumnIsWidenedBackToItsDeclaredLength()
	{

		$this->requireDatabaseDriver('mysql', 'SQLite stores VARCHAR(10), VARCHAR(255) and TEXT as one type, so the damage is no drift there');
		$this->snapshot('submitnews');

		$this->alter('submitnews', $this->table('submitnews')
			->modifyColumn('submitnews_keywords', Column::define('VARCHAR', 10)->notNull()->defaultValue('')));

		$broken = $this->driftOf('submitnews');
		$this->assertArrayHasKey(
			'submitnews_keywords',
			$broken->getModifiedColumns(),
			'Narrowing a column must be reported as a modified column.'
		);

		$this->repair('submitnews');

		$restored = $this->columnOf('submitnews', 'submitnews_keywords');
		$this->assertNotNull($restored);
		$this->assertMysqlType('varchar(255)', $restored);
		$this->assertEquals('NO', $restored['IS_NULLABLE']);

		$this->assertFalse($this->driftOf('submitnews')->hasDrift(), 'The repaired submitnews table must verify clean.');
	}

	public function testAChangedColumnTypeIsRestored()
	{

		$this->requireDatabaseDriver('mysql', 'SQLite stores VARCHAR(10), VARCHAR(255) and TEXT as one type, so the damage is no drift there');
		$this->snapshot('submitnews');

		$this->alter('submitnews', $this->table('submitnews')
			->modifyColumn('submitnews_item', Column::define('VARCHAR', 255)->notNull()->defaultValue('')));

		$broken = $this->driftOf('submitnews');
		$this->assertArrayHasKey('submitnews_item', $broken->getModifiedColumns());

		$this->repair('submitnews');

		$restored = $this->columnOf('submitnews', 'submitnews_item');
		$this->assertNotNull($restored);
		$this->assertMysqlType('text', $restored, 'submitnews_item is declared TEXT.');
		$this->assertEquals('NO', $restored['IS_NULLABLE']);

		if($this->onMysql())
		{
			$this->assertNull($restored['COLUMN_DEFAULT'], 'The declaration gives submitnews_item no default.');
		}

		$this->assertFalse($this->driftOf('submitnews')->hasDrift(), 'The repaired submitnews table must verify clean.');
	}

	// --- indexes ----------------------------------------------------------

	public function testADroppedIndexComesBack()
	{

		$this->snapshot('news');

		$this->alter('news', $this->table('news')->dropIndex('news_datestamp'));

		$broken = $this->driftOf('news');
		$this->assertArrayHasKey(
			'news_datestamp',
			$broken->getMissingIndexes(),
			'Dropping news_datestamp must be reported as a missing index.'
		);

		$this->repair('news');

		$this->assertEquals(
			array('news_datestamp'),
			$this->indexColumns('news', 'news_datestamp'),
			'The index must be back over the column it is declared on.'
		);

		$this->assertFalse($this->driftOf('news')->hasDrift(), 'The repaired news table must verify clean.');
	}

	public function testADroppedCompositeIndexComesBackWithItsColumnsInOrder()
	{

		$this->snapshot('news');

		$this->alter('news', $this->table('news')->dropIndex('news_start_end'));

		$broken = $this->driftOf('news');
		$this->assertArrayHasKey('news_start_end', $broken->getMissingIndexes());

		$this->repair('news');

		$this->assertEquals(
			array('news_start', 'news_end'),
			$this->indexColumns('news', 'news_start_end'),
			'A composite index is not a set of columns: news_start_end is declared over (news_start, news_end) '
			. 'and an index rebuilt the other way round answers a different range query.'
		);

		$this->assertFalse($this->driftOf('news')->hasDrift(), 'The repaired news table must verify clean.');
	}

	public function testACompositeIndexBuiltInTheWrongOrderIsRebuiltInTheDeclaredOrder()
	{

		$this->snapshot('user');

		$this->alter('user', $this->table('user')->dropIndex('join_ban_index'));
		$this->alter('user', $this->table('user')->addIndex(Index::index('join_ban_index', array('user_ban', 'user_join'))));

		$broken = $this->driftOf('user');
		$this->assertArrayHasKey(
			'join_ban_index',
			$broken->getModifiedIndexes(),
			'An index over the declared columns in the wrong order is a modified index, not a matching one.'
		);

		$this->repair('user');

		$this->assertEquals(
			array('user_join', 'user_ban'),
			$this->indexColumns('user', 'join_ban_index'),
			'The declared order has to win, which means the reversed index is dropped before the declared one is added.'
		);

		$this->assertFalse($this->driftOf('user')->hasDrift(), 'The repaired user table must verify clean.');
	}

	// --- whole tables -----------------------------------------------------

	public function testATableOnTheWrongStorageEngineIsConvertedBack()
	{

		$this->skipWithoutStorageEngines();
		$this->snapshot('tmp');

		$this->repair('tmp');
		$this->assertEquals('InnoDB', $this->engineOf('tmp'));
		$this->assertFalse($this->driftOf('tmp')->hasDrift(), 'tmp must verify clean before its engine is broken.');

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'tmp` ENGINE=MyISAM');

		$broken = $this->driftOf('tmp');
		$engineChange = $broken->getEngineChange();

		$this->assertNotNull($engineChange, 'A table on the wrong storage engine must be reported as such.');
		$this->assertEquals('InnoDB', $engineChange['expected']);
		$this->assertEquals('MyISAM', $engineChange['actual']);

		$this->repair('tmp');

		$this->assertEquals('InnoDB', $this->engineOf('tmp'), 'The table must be back on its declared engine.');
		$this->assertFalse($this->driftOf('tmp')->hasDrift(), 'The reconverted tmp table must verify clean.');
	}

	public function testADroppedTableIsRecreated()
	{

		$this->snapshot('rate');

		$this->assertNotFalse(e107::getDb()->dropTable('rate'));
		$this->assertFalse($this->tableExists('rate'), 'The table must really be gone before the repair.');

		$broken = $this->driftOf('rate');
		$this->assertTrue($broken->isMissing(), 'A declared table the database does not have must be reported as missing.');

		$this->repair('rate');

		$this->assertTrue($this->tableExists('rate'), 'The table must have been recreated.');
		$this->assertEquals(self::$declaredRateColumns, $this->columnNames('rate'));
		$this->assertEquals(array('rate_id'), $this->indexColumns('rate', 'PRIMARY'));

		if(e107::getDb()->getPlatform()->supportsStorageEngines())
		{
			$this->assertEquals('InnoDB', $this->engineOf('rate'));
		}

		$rateId = $this->columnOf('rate', 'rate_id');
		$this->assertEquals('auto_increment', strtolower($rateId['EXTRA']));

		$this->assertFalse($this->driftOf('rate')->hasDrift(), 'The recreated rate table must verify clean.');
	}

	// --- applying the same plan twice -------------------------------------

	public function testApplyingTheSamePlanTwiceChangesNothingTheSecondTime()
	{

		$this->snapshot('news');

		$this->alter('news', $this->table('news')->dropIndex('news_datestamp'));

		$dbv = $this->verifierFor('news');
		$dbv->compare('core');
		$dbv->compileResults();

		$this->assertGreaterThan(0, $dbv->getFixPlan()->count(), 'precondition: there is something to repair.');

		$dbv->runFix();

		$this->assertEquals(
			array('news_datestamp'),
			$this->indexColumns('news', 'news_datestamp'),
			'precondition: the first run repairs the index.'
		);

		$this->assertSame(0, $dbv->getFixPlan()->count(), 'A repaired table leaves nothing behind in the plan.');
		$this->assertSame(array(), $dbv->fixList['core'], 'nor anything under its schema file in the legacy fix list.');
		$this->assertSame(array(), $dbv->getTableDiffs(), 'nor a diff to report from.');

		$dbv->runFix();

		$this->assertEquals(
			array('news_datestamp'),
			$this->indexColumns('news', 'news_datestamp'),
			'The second run is a no-op and leaves the repaired index alone.'
		);
		$this->assertFalse($this->driftOf('news')->hasDrift(), 'news is still clean after the second run.');
	}

	// --- the admin form's entry point -------------------------------------

	public function testTheFormPathRepairsOnlyWhatTheFormAsksFor()
	{

		$this->snapshot('news');

		$this->alter('news', $this->table('news')->dropIndex('news_sticky'));
		$this->alter('news', $this->table('news')->dropIndex('news_render_type'));

		$dbv = $this->verifierFor('news');
		$dbv->runFix(array('core' => array('news' => array('news_sticky' => array('index')))));

		$this->assertEquals(
			array('news_sticky'),
			$this->indexColumns('news', 'news_sticky'),
			'The requested index must be rebuilt without a prior compare().'
		);
		$this->assertEquals(
			array(),
			$this->indexColumns('news', 'news_render_type'),
			'An index the form did not ask about must be left alone.'
		);
	}

	// --- derived FULLTEXT indexes -----------------------------------------

	public function testADerivedFulltextIndexTheDeclarationCoversIsDroppedRatherThanBuilt()
	{

		$this->skipWithoutFulltext();
		$this->snapshot('user');

		$pristine = $this->indicesOf('user');

		$this->assertArrayNotHasKey(
			'ft_user_user_signature',
			$pristine,
			'core_sql.php declares FULLTEXT (user_signature), so nothing may derive a second index over the same column.'
		);
		$this->assertSame(
			'missing_index',
			$pristine['ft_user_user_name']['_status'],
			'The derived index over a column no FULLTEXT declaration covers is still wanted.'
		);
		$this->assertSame(
			'missing_index',
			$pristine['user_signature']['_status'],
			'precondition: the declared FULLTEXT index is absent from the v2.3.0 dump.'
		);

		$this->alter('user', $this->table('user')->addIndex(Index::fulltext('ft_user_user_signature', 'user_signature')));

		$reported = $this->indicesOf('user');

		$this->assertSame(
			'redundant_index',
			$reported['ft_user_user_signature']['_status'],
			'A derived index already on the table, whose columns the declaration covers, is reported as redundant.'
		);
		$this->assertSame('user_signature', $reported['ft_user_user_signature']['_duplicates']);
		$this->assertSame('missing_index', $reported['ft_user_user_name']['_status']);

		$this->repair('user');

		$this->assertSame(
			array('user_signature'),
			$this->fulltextIndexNamesOver('user', 'user_signature'),
			'Exactly one FULLTEXT index over user_signature must be left, and it is the declared one.'
		);
		$this->assertSame(
			array('user_name'),
			$this->indexColumns('user', 'ft_user_user_name'),
			'The genuinely derived index is built as it always was.'
		);

		$this->assertFalse($this->driftOf('user')->hasDrift(), 'The repaired user table must verify clean.');

		$settled = $this->indicesOf('user');

		$this->assertArrayNotHasKey('ft_user_user_signature', $settled, 'The dropped duplicate is not wanted back on the next run.');
		$this->assertSame('ok', $settled['user_signature']['_status']);
		$this->assertSame('ok', $settled['ft_user_user_name']['_status']);
	}

	/**
	 * `indexdrop` is what the screen's checkbox for a redundant index posts, per {@see \db_verify::$modes}.
	 */
	public function testTheFormPathDropsARedundantIndexAndNothingElse()
	{

		$this->skipWithoutFulltext();
		$this->snapshot('user');

		$this->alter('user', $this->table('user')->addIndex(Index::fulltext('ft_user_user_signature', 'user_signature')));

		$dbv = $this->verifierFor('user');
		$dbv->runFix(array('core' => array('user' => array('ft_user_user_signature' => array('indexdrop')))));

		$this->assertSame(
			array(),
			$this->fulltextIndexNamesOver('user', 'user_signature'),
			'The duplicate must be gone, and the declared index the request did not ask for must not have been built.'
		);
		$this->assertSame(array(), $this->indexColumns('user', 'ft_user_user_name'), 'An index the form did not ask about is left alone.');
		$this->assertEquals('MyISAM', $this->engineOf('user'), 'nor is the table converted behind the request.');
	}

	// --- helpers ----------------------------------------------------------

	/**
	 * A db_verify whose declared corpus is narrowed to one core table, so a repair cannot reach past it.
	 *
	 * @param string $table unprefixed table name declared in core_sql.php.
	 * @return db_verify
	 */
	private function verifierFor($table)
	{

		$dbv = new db_verify();

		$this->assertArrayHasKey('core', $dbv->sqlFileTables, 'core_sql.php must have been parsed.');

		$file = $dbv->sqlFileTables['core'];
		$key = array_search($table, $file['tables'], true);

		$this->assertNotFalse($key, 'core_sql.php must declare `' . $table . '`.');

		$narrowed = array(
			'tables'  => array($key => $file['tables'][$key]),
			'data'    => array($key => $file['data'][$key]),
			'engine'  => array(),
			'charset' => array(),
		);

		if(isset($file['engine'][$key]))
		{
			$narrowed['engine'][$key] = $file['engine'][$key];
		}

		if(isset($file['charset'][$key]))
		{
			$narrowed['charset'][$key] = $file['charset'][$key];
		}

		$dbv->sqlFileTables = array('core' => $narrowed);

		return $dbv;
	}

	/**
	 * compare -> compileResults -> runFix, on an object that has seen nothing else.
	 *
	 * @param string $table
	 * @return void
	 */
	private function repair($table)
	{

		$dbv = $this->verifierFor($table);
		$dbv->compare('core');
		$dbv->compileResults();
		$dbv->runFix();
	}

	/**
	 * What a brand new db_verify makes of one table right now; a reused object would answer from before the damage.
	 *
	 * @param string $table
	 * @return TableDiff
	 */
	private function driftOf($table)
	{

		$dbv = $this->verifierFor($table);
		$dbv->compare('core');

		$diffs = $dbv->getTableDiffs();

		$this->assertArrayHasKey($table, $diffs, 'compare() must have reached `' . $table . '`.');

		return $diffs[$table];
	}

	/**
	 * Copy a table, structure and rows, so the test can put it back.
	 *
	 * @param string $table unprefixed table name.
	 * @return void
	 */
	private function snapshot($table)
	{

		$db = e107::getDb();
		$backup = 'dbvroundtrip_' . $table;

		$db->dropTable($backup);
		$this->assertTrue($db->getSchemaManager()->createTableLike(MPREFIX . $table, MPREFIX . $backup), 'the backup of ' . $table . ' has to be made');
		$this->runStatement('INSERT INTO `' . MPREFIX . $backup . '` SELECT * FROM `' . MPREFIX . $table . '`');

		$this->snapshots[$table] = $backup;
	}

	/**
	 * @param string $table unprefixed table name.
	 * @param string $backup unprefixed name of its copy.
	 * @return void
	 */
	private function restore($table, $backup)
	{

		$db = e107::getDb();

		$db->dropTable($table);
		$this->assertTrue($db->getSchemaManager()->createTableLike(MPREFIX . $backup, MPREFIX . $table), $table . ' has to be put back');
		$this->runStatement('INSERT INTO `' . MPREFIX . $table . '` SELECT * FROM `' . MPREFIX . $backup . '`');
		$db->dropTable($backup);
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return Table a batch of changes to it, compiled for the suite's engine.
	 */
	private function table($table)
	{

		return e107::getDb()->schema()->tablePhysical($table);
	}

	/**
	 * Run a batch of changes the test breaks a table with.
	 *
	 * @param string $table unprefixed table name, for the message.
	 * @param Table $changes
	 * @return void
	 */
	private function alter($table, Table $changes)
	{

		$this->assertNotFalse($changes->execute(), 'the damage to ' . $table . ' has to be done: ' . e107::getDb()->getLastErrorText());
	}

	/**
	 * @param string $sql a whole statement, built here and never from a fixture.
	 * @return void
	 */
	private function runStatement($sql)
	{

		$db = e107::getDb();

		$this->assertNotFalse($db->execute($sql), $sql . ' :: ' . $db->getLastErrorText());
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return TableSchema|null the live table, as the connection's own reader reads it.
	 */
	private function live($table)
	{

		return e107::getDb()->getSchemaManager()->getReader()->read(MPREFIX . $table);
	}

	/**
	 * @return bool whether the suite runs on MySQL, which spells a declared type back as declared.
	 */
	private function onMysql()
	{

		return e107::getDb()->getDriver()->getName() === 'mysql';
	}

	/**
	 * @param string $type the type as MySQL reports it, e.g. 'varchar(255)'
	 * @param array $column as {@see DbVerifyRoundTripTest::columnOf()} returns it.
	 * @param string $message
	 * @return void
	 */
	private function assertMysqlType($type, array $column, $message = '')
	{

		if($this->onMysql())
		{
			$this->assertEquals($type, strtolower($column['COLUMN_TYPE']), $message);
		}
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return string[] live column names, in ordinal order.
	 */
	private function columnNames($table)
	{

		$live = $this->live($table);

		return ($live === null) ? array() : array_map('strval', array_keys($live->getColumns()));
	}

	/**
	 * @param string $table unprefixed table name.
	 * @param string $column
	 * @return array|null the column in the shape of an information_schema row, or null when there is no such column.
	 */
	private function columnOf($table, $column)
	{

		$live = $this->live($table);
		$found = ($live === null) ? null : $live->getColumn($column);

		if($found === null)
		{
			return null;
		}

		return array(
			'COLUMN_TYPE'    => $found->getColumnType(),
			'IS_NULLABLE'    => $found->isNullable() ? 'YES' : 'NO',
			'COLUMN_DEFAULT' => $found->getDefault(),
			'EXTRA'          => $found->getExtra(),
		);
	}

	/**
	 * A column's default with the quotes MariaDB and SQLite wrap a string default in stripped; MySQL states it without them.
	 *
	 * @param array $column as {@see DbVerifyRoundTripTest::columnOf()} returns it.
	 * @return string|null
	 */
	private static function defaultOf(array $column)
	{

		if($column['COLUMN_DEFAULT'] === null)
		{
			return null;
		}

		return trim((string) $column['COLUMN_DEFAULT'], "'");
	}

	/**
	 * @param string $table unprefixed table name.
	 * @param string $index
	 * @return string[] the index's columns in order; empty when there is no such index.
	 */
	private function indexColumns($table, $index)
	{

		$live = $this->live($table);
		$found = ($live === null) ? null : $live->getIndex($index);

		return ($found === null) ? array() : $found->getColumnNames();
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return string|null
	 */
	private function engineOf($table)
	{

		$live = $this->live($table);

		return ($live === null) ? null : $live->getEngine();
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return bool
	 */
	private function tableExists($table)
	{

		return $this->live($table) !== null;
	}

	/**
	 * One table's indexes in the legacy $indices shape the admin screen renders from.
	 *
	 * @param string $table unprefixed table name.
	 * @return array index name => entry.
	 */
	private function indicesOf($table)
	{

		$dbv = $this->verifierFor($table);
		$dbv->compare('core');

		$indices = $dbv->getResults('indices');

		$this->assertArrayHasKey($table, $indices, 'compare() must have reached `' . $table . '`.');

		return $indices[$table];
	}

	/**
	 * @param string $table unprefixed table name.
	 * @param string $column
	 * @return string[] sorted names of the live FULLTEXT indexes over exactly that one column.
	 */
	private function fulltextIndexNamesOver($table, $column)
	{

		$live = $this->live($table);
		$names = array();

		foreach(($live === null) ? array() : $live->getIndexes() as $name => $index)
		{
			if($index->getKind() === 'FULLTEXT' && $index->getColumnNames() === array($column))
			{
				$names[] = (string) $name;
			}
		}

		sort($names);

		return $names;
	}

	/**
	 * Skips the test where no storage engine can carry the FULLTEXT index `user` declares: an engine without FULLTEXT
	 * indexes, or the mysql:5.5 and mariadb:10.0 that CI also runs, whose InnoDB has none.
	 *
	 * @return void
	 */
	private function skipWithoutFulltext()
	{

		if(!e107::getDb()->getPlatform()->supportsFullTextIndexes())
		{
			$this->markTestSkipped('This engine builds no FULLTEXT index.');
		}

		$dbv = $this->verifierFor('user');
		$engine = $dbv->getIntendedStorageEngine('InnoDB', array('needsFulltext' => true));

		if($engine === false || !$dbv->engineSupportsFulltext($engine))
		{
			$this->markTestSkipped('No storage engine on this server can carry the FULLTEXT index `user` declares.');
		}
	}

	/**
	 * @return void
	 */
	private function skipWithoutStorageEngines()
	{

		if(!e107::getDb()->getPlatform()->supportsStorageEngines())
		{
			$this->markTestSkipped('This engine has no storage engines to be on the wrong one of.');
		}
	}
}
