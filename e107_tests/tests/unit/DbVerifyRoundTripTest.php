<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Diff\TableDiff;

/**
 * The round-trip property: break a live table, repair it through {@see \db_verify}, then read the database back.
 *
 * The unit database is a v2.3.0 dump in which most core tables are already drifted, so every expected shape is stated
 * here by hand from core_sql.php rather than read back from the code under test.
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

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'news` DROP COLUMN `news_class`');

		$broken = $this->driftOf('news');
		$this->assertArrayHasKey(
			'news_class',
			$broken->getMissingColumns(),
			'Dropping news_class must be reported as a missing column.'
		);

		$this->repair('news');

		$restored = $this->columnOf('news', 'news_class');
		$this->assertNotNull($restored, 'news_class must be back in the live table.');
		$this->assertEquals('varchar(255)', strtolower($restored['COLUMN_TYPE']));
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

	public function testANarrowedColumnIsWidenedBackToItsDeclaredLength()
	{

		$this->snapshot('submitnews');

		$this->runStatement(
			'ALTER TABLE `' . MPREFIX . 'submitnews` '
			. "MODIFY COLUMN `submitnews_keywords` varchar(10) NOT NULL DEFAULT ''"
		);

		$broken = $this->driftOf('submitnews');
		$this->assertArrayHasKey(
			'submitnews_keywords',
			$broken->getModifiedColumns(),
			'Narrowing a column must be reported as a modified column.'
		);

		$this->repair('submitnews');

		$restored = $this->columnOf('submitnews', 'submitnews_keywords');
		$this->assertNotNull($restored);
		$this->assertEquals('varchar(255)', strtolower($restored['COLUMN_TYPE']));
		$this->assertEquals('NO', $restored['IS_NULLABLE']);

		$this->assertFalse($this->driftOf('submitnews')->hasDrift(), 'The repaired submitnews table must verify clean.');
	}

	public function testAChangedColumnTypeIsRestored()
	{

		$this->snapshot('submitnews');

		$this->runStatement(
			'ALTER TABLE `' . MPREFIX . 'submitnews` '
			. "MODIFY COLUMN `submitnews_item` varchar(255) NOT NULL DEFAULT ''"
		);

		$broken = $this->driftOf('submitnews');
		$this->assertArrayHasKey('submitnews_item', $broken->getModifiedColumns());

		$this->repair('submitnews');

		$restored = $this->columnOf('submitnews', 'submitnews_item');
		$this->assertNotNull($restored);
		$this->assertEquals('text', strtolower($restored['COLUMN_TYPE']), 'submitnews_item is declared TEXT.');
		$this->assertEquals('NO', $restored['IS_NULLABLE']);
		$this->assertNull($restored['COLUMN_DEFAULT'], 'The declaration gives submitnews_item no default.');

		$this->assertFalse($this->driftOf('submitnews')->hasDrift(), 'The repaired submitnews table must verify clean.');
	}

	// --- indexes ----------------------------------------------------------

	public function testADroppedIndexComesBack()
	{

		$this->snapshot('news');

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'news` DROP INDEX `news_datestamp`');

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

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'news` DROP INDEX `news_start_end`');

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

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'user` DROP INDEX `join_ban_index`');
		$this->runStatement('ALTER TABLE `' . MPREFIX . 'user` ADD INDEX `join_ban_index` (`user_ban`, `user_join`)');

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

		$this->runStatement('DROP TABLE `' . MPREFIX . 'rate`');
		$this->assertFalse($this->tableExists('rate'), 'The table must really be gone before the repair.');

		$broken = $this->driftOf('rate');
		$this->assertTrue($broken->isMissing(), 'A declared table the database does not have must be reported as missing.');

		$this->repair('rate');

		$this->assertTrue($this->tableExists('rate'), 'The table must have been recreated.');
		$this->assertEquals(self::$declaredRateColumns, $this->columnNames('rate'));
		$this->assertEquals(array('rate_id'), $this->indexColumns('rate', 'PRIMARY'));
		$this->assertEquals('InnoDB', $this->engineOf('rate'));

		$rateId = $this->columnOf('rate', 'rate_id');
		$this->assertEquals('auto_increment', strtolower($rateId['EXTRA']));

		$this->assertFalse($this->driftOf('rate')->hasDrift(), 'The recreated rate table must verify clean.');
	}

	// --- applying the same plan twice -------------------------------------

	public function testApplyingTheSamePlanTwiceChangesNothingTheSecondTime()
	{

		$this->snapshot('news');

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'news` DROP INDEX `news_datestamp`');

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

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'news` DROP INDEX `news_sticky`');
		$this->runStatement('ALTER TABLE `' . MPREFIX . 'news` DROP INDEX `news_render_type`');

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

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'user` ADD FULLTEXT `ft_user_user_signature` (`user_signature`)');

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

		$this->runStatement('ALTER TABLE `' . MPREFIX . 'user` ADD FULLTEXT `ft_user_user_signature` (`user_signature`)');

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

		$physical = MPREFIX . $table;
		$backup = MPREFIX . 'dbvroundtrip_' . $table;

		$this->runStatement('DROP TABLE IF EXISTS `' . $backup . '`');
		$this->runStatement('CREATE TABLE `' . $backup . '` LIKE `' . $physical . '`');
		$this->runStatement('INSERT INTO `' . $backup . '` SELECT * FROM `' . $physical . '`');

		$this->snapshots[$table] = $backup;
	}

	/**
	 * @param string $table unprefixed table name.
	 * @param string $backup physical name of its copy.
	 * @return void
	 */
	private function restore($table, $backup)
	{

		$physical = MPREFIX . $table;
		$autoIncrement = $this->autoIncrementOf($backup);

		$this->runStatement('DROP TABLE IF EXISTS `' . $physical . '`');
		$this->runStatement('CREATE TABLE `' . $physical . '` LIKE `' . $backup . '`');
		$this->runStatement('INSERT INTO `' . $physical . '` SELECT * FROM `' . $backup . '`');

		if($autoIncrement !== null)
		{
			$this->runStatement('ALTER TABLE `' . $physical . '` AUTO_INCREMENT = ' . (int) $autoIncrement);
		}

		$this->runStatement('DROP TABLE `' . $backup . '`');
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
	 * @param string $sql
	 * @param array $params bound values, keyed without the leading colon.
	 * @return array[] associative rows.
	 */
	private function rows($sql, array $params = array())
	{

		$db = e107::getDb();

		$this->assertNotFalse($db->execute($sql, $params), $sql . ' :: ' . $db->getLastErrorText());

		$rows = array();

		while($row = $db->fetch())
		{
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return string[] live column names, in ordinal order.
	 */
	private function columnNames($table)
	{

		$rows = $this->rows(
			'SELECT COLUMN_NAME FROM information_schema.COLUMNS'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name'
			. ' ORDER BY ORDINAL_POSITION',
			array('name' => MPREFIX . $table)
		);

		$names = array();

		foreach($rows as $row)
		{
			$names[] = $row['COLUMN_NAME'];
		}

		return $names;
	}

	/**
	 * @param string $table unprefixed table name.
	 * @param string $column
	 * @return array|null the information_schema row, or null when there is no such column.
	 */
	private function columnOf($table, $column)
	{

		$rows = $this->rows(
			'SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, ORDINAL_POSITION'
			. ' FROM information_schema.COLUMNS'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name AND COLUMN_NAME = :column',
			array('name' => MPREFIX . $table, 'column' => $column)
		);

		return empty($rows) ? null : $rows[0];
	}

	/**
	 * A column's default with the quotes MariaDB wraps a string default in stripped; MySQL states it without them.
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
	 * @return string[] the index's columns in SEQ_IN_INDEX order; empty when there is no such index.
	 */
	private function indexColumns($table, $index)
	{

		$rows = $this->rows(
			'SELECT COLUMN_NAME FROM information_schema.STATISTICS'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name AND INDEX_NAME = :index'
			. ' ORDER BY SEQ_IN_INDEX',
			array('name' => MPREFIX . $table, 'index' => $index)
		);

		$columns = array();

		foreach($rows as $row)
		{
			$columns[] = $row['COLUMN_NAME'];
		}

		return $columns;
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return string|null
	 */
	private function engineOf($table)
	{

		$rows = $this->rows(
			'SELECT ENGINE FROM information_schema.TABLES'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name',
			array('name' => MPREFIX . $table)
		);

		return empty($rows) ? null : $rows[0]['ENGINE'];
	}

	/**
	 * @param string $physicalTableName
	 * @return int|null null when the table has no AUTO_INCREMENT column.
	 */
	private function autoIncrementOf($physicalTableName)
	{

		$rows = $this->rows(
			'SELECT AUTO_INCREMENT FROM information_schema.TABLES'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name',
			array('name' => $physicalTableName)
		);

		if(empty($rows) || $rows[0]['AUTO_INCREMENT'] === null)
		{
			return null;
		}

		return (int) $rows[0]['AUTO_INCREMENT'];
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return bool
	 */
	private function tableExists($table)
	{

		return $this->engineOf($table) !== null;
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

		$rows = $this->rows(
			'SELECT INDEX_NAME FROM information_schema.STATISTICS'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name'
			. ' AND COLUMN_NAME = :column AND INDEX_TYPE = :type',
			array('name' => MPREFIX . $table, 'column' => $column, 'type' => 'FULLTEXT')
		);

		$names = array();

		foreach($rows as $row)
		{
			$names[] = $row['INDEX_NAME'];
		}

		sort($names);

		return $names;
	}

	/**
	 * Skips the test on the mysql:5.5 and mariadb:10.0 that CI also runs, whose InnoDB has no FULLTEXT.
	 *
	 * @return void
	 */
	private function skipWithoutFulltext()
	{

		$dbv = $this->verifierFor('user');
		$engine = $dbv->getIntendedStorageEngine('InnoDB', array('needsFulltext' => true));

		if($engine === false || !$dbv->engineSupportsFulltext($engine))
		{
			$this->markTestSkipped('No storage engine on this server can carry the FULLTEXT index `user` declares.');
		}
	}
}
