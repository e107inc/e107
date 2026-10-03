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
use e107\Database\Schema\Index;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\Plan\FixPlan;

/**
 * The defect classes the {@see \db_verify} rewrite makes structurally impossible, asserted over the whole in-tree corpus.
 *
 * The suite's database is the v2.3.0 sample dump: MyISAM, utf8mb3, and missing everything added to core_sql.php since,
 * so it is drifted by design and these assertions are not vacuous.
 */
class DbVerifyFixListTest extends \Test\Unit
{
	/** @var string[] core_sql.php tables whose own name could be mistaken for a schema file key */
	private static $coreTablesWithMisleadingNames = array('core_media', 'core_media_cat', 'news', 'session');

	/** A core table with several string columns and no other drift on this dump, that nothing else in the suite alters. */
	const CHARSET_TABLE = 'upload';

	/** A core table with a clean declared shape on this dump, to hang an undeclared column on. */
	const EXTRA_COLUMN_TABLE = 'mail_content';

	const EXTRA_COLUMN = 'dbv_undeclared_column';

	/** The core table that both declares a FULLTEXT index and has one derived for the same column from e_search. */
	const REDUNDANT_INDEX_TABLE = 'user';

	const REDUNDANT_INDEX = 'ft_user_user_signature';

	const REDUNDANT_INDEX_COLUMN = 'user_signature';

	const DRIFTED_FIXTURE = 'The unit database is the v2.3.0 dump and is drifted by design, so a corpus-wide verify must find something to fix. '
		. 'Finding nothing means an earlier test in this shuffled run repaired the fixture without restoring it.';

	protected function _before()
	{

		require_once(e_HANDLER . 'db_verify_class.php');
	}

	protected function _after()
	{

		$this->dropUndeclaredColumn();
		$this->dropRedundantIndex();
	}

	// --- 1. the filing invariant (#5910) ----------------------------------

	public function testEveryFixListKeyNamesASchemaFileRunFixCanResolve()
	{

		$dbv = $this->verifiedCorpus();

		$this->assertNotEmpty($dbv->fixList, self::DRIFTED_FIXTURE);

		foreach($dbv->fixList as $file => $tables)
		{
			$this->assertArrayHasKey(
				$file,
				$dbv->sqlFileTables,
				'`' . $file . '` is filed as a schema file but names none, so runFix() would skip everything under it in silence (#5910).'
			);
			$this->assertArrayHasKey('tables', $dbv->sqlFileTables[$file]);

			foreach(array_keys($tables) as $table)
			{
				$this->assertNotNull(
					$dbv->getId($dbv->sqlFileTables[$file]['tables'], $table),
					'`' . $file . '` does not declare `' . $table . '`, so runFix() could not resolve the fix filed there.'
				);
			}
		}
	}

	public function testEveryPlannedChangeNamesTheFileThatDeclaredItsTable()
	{

		$dbv = $this->verifiedCorpus();
		$diffs = $dbv->getTableDiffs();
		$changes = $dbv->getFixPlan()->getChanges();

		$this->assertGreaterThan(0, count($changes));

		foreach($changes as $change)
		{
			$file = $change->getSqlFile();
			$table = $change->getTable();

			$this->assertArrayHasKey($file, $dbv->sqlFileTables, $change->describe() . ' names `' . $file . '`, which is not a schema file.');
			$this->assertNotNull($dbv->getId($dbv->sqlFileTables[$file]['tables'], $table), $change->describe() . ': `' . $file . '` does not declare `' . $table . '`.');
			$this->assertArrayHasKey($table, $diffs);
			$this->assertSame($diffs[$table]->getSqlFile(), $file, 'A change and the difference it was planned from must agree on the declaring file.');
		}
	}

	public function testATableIsFiledUnderItsDeclaringFileAndNotUnderItsOwnName()
	{

		$dbv = $this->verifiedCorpus();

		$this->assertArrayHasKey('core', $dbv->fixList);

		$diffs = $dbv->getTableDiffs();
		$drifted = 0;

		foreach(self::$coreTablesWithMisleadingNames as $table)
		{
			if(isset($diffs[$table]) && $diffs[$table]->hasDrift())
			{
				$drifted++;
				$this->assertArrayHasKey(
					$table,
					$dbv->fixList['core'],
					'`' . $table . '` is declared by core_sql.php and drifted on this dump, so it belongs under `core`.'
				);
			}

			$this->assertArrayNotHasKey(
				$table,
				$dbv->fixList,
				'`' . $table . '` is a table, not a schema file, and must never reach the top level of $fixList.'
			);
		}

		$this->assertGreaterThan(0, $drifted, 'None of ' . implode(', ', self::$coreTablesWithMisleadingNames) . ' drifted, so where a drifted one is filed went unchecked.');
	}

	public function testEveryTableWithAMisleadingNameIsFiledUnderCoreOnMysql()
	{

		$this->requireDatabaseDriver('mysql', 'the v2.3.0 dump leaves all of these tables drifted only as MySQL reads them back');

		$dbv = $this->verifiedCorpus();

		foreach(self::$coreTablesWithMisleadingNames as $table)
		{
			$this->assertArrayHasKey(
				$table,
				$dbv->fixList['core'],
				'`' . $table . '` is declared by core_sql.php and drifted on this dump, so it belongs under `core`.'
			);
		}
	}

	// --- 2. no empty statements (#5905) -----------------------------------

	public function testNoPlannedChangeEverRendersAnEmptyStatement()
	{

		$this->requireDatabaseDriver('mysql', 'it renders every change against the unrepaired tables, and SQLite compiles a change against the table as it stands, which an earlier change of the plan has not reached yet');

		$dbv = $this->verifiedCorpus();
		$plan = $dbv->getFixPlan();
		$schema = e107::getDb()->schema();

		$this->assertGreaterThan(0, $plan->count(), self::DRIFTED_FIXTURE);

		$rendered = 0;

		foreach($plan->getChanges() as $change)
		{
			$statements = (new FixPlan(array($change)))->toSqlStatements($schema);

			$this->assertNotEmpty($statements, $change->describe() . ' on `' . $change->getTable() . '` rendered no statement at all.');

			foreach($statements as $sql)
			{
				$rendered++;

				$this->assertIsString($sql);
				$this->assertNotSame('', trim($sql), $change->describe() . ' on `' . $change->getTable() . '` rendered an empty statement (#5905).');
				$this->assertMatchesRegularExpression(
					'/^\s*(ALTER|CREATE|DROP)\b/i',
					$sql,
					$change->describe() . ' on `' . $change->getTable() . '` rendered something that is not DDL: ' . $sql
				);
			}
		}

		$this->assertGreaterThanOrEqual($plan->count(), $rendered, 'Every change renders at least one statement.');
	}

	// --- 3. a column matching the table charset reports none of its own ------

	public function testATableWideCharacterSetDriftIsReportedOnceOnTheTable()
	{

		$this->requireDatabaseDriver('mysql', "character sets are MySQL's");

		$dbv = $this->verifiedCorpus();
		$table = self::CHARSET_TABLE;

		$errors = $dbv->getErrors();
		$results = $dbv->getResults('fields');
		$indices = $dbv->getResults('indices');

		$this->assertArrayHasKey($table, $errors);

		$bit = db_verify::STATUS_TABLE_MISMATCH_DEFAULT_CHARSET;

		$this->assertSame($bit, $errors[$table]['_status'] & $bit, '`' . $table . '` is utf8mb3 on this dump and must report the character set bit.');
		$this->assertSame('utf8mb4', $errors[$table]['_valid_' . $bit]);
		$this->assertNotSame('utf8mb4', $errors[$table]['_invalid_' . $bit], 'The dump is genuinely on the older character set, or this test asserts nothing.');

		$this->assertGreaterThanOrEqual(
			5,
			$this->stringColumnCount($table),
			'`' . $table . '` needs several string columns for the silence below to mean anything.'
		);

		foreach($results[$table] as $column => $entry)
		{
			$this->assertSame('ok', $entry['_status'], 'Column `' . $column . '` must not repeat its table\'s character set difference; the reader nulls a column charset that matches the table default.');
		}

		foreach($indices[$table] as $index => $entry)
		{
			$this->assertSame('ok', $entry['_status'], 'Index `' . $index . '` of `' . $table . '` is sound on this dump.');
		}
	}

	public function testNoColumnAnywhereReportsACharacterSetOrCollationDifference()
	{

		$this->requireDatabaseDriver('mysql', "character sets are MySQL's");

		$dbv = $this->verifiedCorpus();
		$converted = 0;
		$examined = 0;

		foreach($dbv->getTableDiffs() as $table => $diff)
		{
			if($diff->getCharsetChange() === null)
			{
				continue;
			}

			$converted++;

			foreach($diff->getModifiedColumns() as $column => $columnDiff)
			{
				$examined++;
				$changed = $columnDiff->getChangedFields();

				$this->assertNotContains('charset', $changed, '`' . $table . '`.`' . $column . '` restates its table\'s character set difference.');
				$this->assertNotContains('collation', $changed, '`' . $table . '`.`' . $column . '` restates its table\'s collation difference.');
			}
		}

		$this->assertGreaterThan(20, $converted, 'Nearly every table of the v2.3.0 dump has a drifted character set, so a run that found few is exercising nothing. ' . self::DRIFTED_FIXTURE);
		$this->assertGreaterThan(0, $examined, 'Some of those tables do have genuinely drifted columns, so the rule is asserted over real column differences and not over empty lists.');
	}

	// --- 4. extra columns are not drift -----------------------------------

	public function testAColumnNothingDeclaresIsNotDriftAndNoFixDropsIt()
	{

		$table = self::EXTRA_COLUMN_TABLE;
		$column = self::EXTRA_COLUMN;

		$before = $this->verifiedFile('core');
		$schema = e107::getDb()->schema();
		$beforeStatements = $before->getFixPlan()->forTable($table)->toSqlStatements($schema);

		$this->addUndeclaredColumn();

		try
		{
			$live = e107::getDb()->getSchemaManager()->getReader()->read(MPREFIX . $table);

			$this->assertInstanceOf(TableSchema::class, $live);
			$this->assertNotNull($live->getColumn($column), 'The undeclared column must actually be on the table, or this test asserts nothing.');

			$after = $this->verifiedFile('core');
			$diffs = $after->getTableDiffs();

			$this->assertArrayHasKey($table, $diffs);
			$this->assertArrayHasKey(
				$column,
				$diffs[$table]->getExtraColumns(),
				'An undeclared column is recorded, so an admin screen can show it; it is being ignored, not missed.'
			);
			$this->assertSame(
				$before->getTableDiffs()[$table]->hasDrift(),
				$diffs[$table]->hasDrift(),
				'An undeclared column must not change whether the table counts as drifted.'
			);

			$beforeErrors = $before->getErrors();
			$afterErrors = $after->getErrors();

			$this->assertSame($beforeErrors[$table], $afterErrors[$table], '`' . $table . '` must report exactly what it reported before the column was added.');
			$this->assertSame($before->errors(), $after->errors(), 'The number of tables reported as drifted must not move.');

			$beforeResults = $before->getResults('fields');
			$afterResults = $after->getResults('fields');

			$this->assertArrayNotHasKey($column, $afterResults[$table], 'A column nothing declares has no declared shape to be compared against.');
			$this->assertSame($beforeResults[$table], $afterResults[$table]);

			$beforeIndices = $before->getResults('indices');
			$afterIndices = $after->getResults('indices');

			$this->assertSame($beforeIndices[$table], $afterIndices[$table]);
			$this->assertSame(
				isset($before->fixList['core'][$table]) ? $before->fixList['core'][$table] : null,
				isset($after->fixList['core'][$table]) ? $after->fixList['core'][$table] : null
			);

			$this->assertSame(
				$beforeStatements,
				$after->getFixPlan()->forTable($table)->toSqlStatements($schema),
				'The repair for `' . $table . '` must be the statement it was before, unchanged by a column it does not declare.'
			);

			foreach($after->getFixPlan()->forTable($table)->toSqlStatements($schema) as $sql)
			{
				$this->assertStringNotContainsString($column, $sql, 'No fix anywhere may name a column nothing declares: ' . $sql);
				$this->assertDoesNotMatchRegularExpression('/\bDROP\s+COLUMN\b/i', $sql, 'A fix plan never drops a column: ' . $sql);
			}
		}
		finally
		{
			$this->dropUndeclaredColumn();
		}
	}

	public function testNoFixInTheWholePlanNamesAColumnNothingDeclares()
	{

		$this->requireDatabaseDriver('mysql', 'only MySQL renders a whole plan before any of it runs; see FixPlan::toSqlStatements()');

		$this->addUndeclaredColumn();

		try
		{
			foreach($this->verifiedFile('core')->getFixPlan()->toSqlStatements(e107::getDb()->schema()) as $sql)
			{
				$this->assertStringNotContainsString(self::EXTRA_COLUMN, $sql, 'No fix anywhere may name a column nothing declares: ' . $sql);
				$this->assertDoesNotMatchRegularExpression('/\bDROP\s+COLUMN\b/i', $sql, 'A fix plan never drops a column: ' . $sql);
			}
		}
		finally
		{
			$this->dropUndeclaredColumn();
		}
	}

	// --- 5. a derived index the declaration covers ------------------------

	public function testARedundantDerivedIndexIsFiledForDroppingAndNothingElse()
	{

		if(!e107::getDb()->getPlatform()->supportsFullTextIndexes())
		{
			$this->markTestSkipped('This engine builds no FULLTEXT index, declared or derived.');
		}

		$table = self::REDUNDANT_INDEX_TABLE;
		$index = self::REDUNDANT_INDEX;

		$before = $this->verifiedCorpus();

		$this->assertArrayNotHasKey(
			$index,
			isset($before->fixList['core'][$table]) ? $before->fixList['core'][$table] : array(),
			'`' . $index . '` duplicates a declared FULLTEXT index, so nothing may plan to create it.'
		);

		$this->addRedundantIndex();

		try
		{
			$after = $this->verifiedCorpus();

			$this->assertArrayHasKey($table, $after->fixList['core']);
			$this->assertSame(
				array('indexdrop'),
				$after->fixList['core'][$table][$index],
				'A redundant index is offered for dropping, and dropping is the only thing offered for it.'
			);

			$diffs = $after->getTableDiffs();

			$this->assertArrayHasKey($index, $diffs[$table]->getRedundantIndexes());
			$this->assertArrayNotHasKey($index, $diffs[$table]->getExtraIndexes());
		}
		finally
		{
			$this->dropRedundantIndex();
		}
	}

	// --- helpers ----------------------------------------------------------

	/**
	 * @return db_verify fresh, with every schema file in the tree compared and compiled.
	 */
	private function verifiedCorpus()
	{

		$dbv = new db_verify();
		$dbv->compareAll();
		$dbv->compileResults();

		return $dbv;
	}

	/**
	 * @param string $file key of {@see \db_verify::$sqlFileTables}.
	 * @return db_verify fresh, with that one schema file compared and compiled.
	 */
	private function verifiedFile($file)
	{

		$dbv = new db_verify();
		$dbv->compare($file);
		$dbv->compileResults();

		return $dbv;
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return int columns of the live table that hold text.
	 */
	private function stringColumnCount($table)
	{

		$sql = e107::getDb();

		$this->assertNotFalse($sql->execute(
			'SELECT COUNT(*) AS hits FROM information_schema.COLUMNS'
			. ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name AND CHARACTER_SET_NAME IS NOT NULL',
			array('name' => MPREFIX . $table)
		));

		$row = $sql->fetch();

		return (int) $row['hits'];
	}

	/**
	 * @return void
	 */
	private function addUndeclaredColumn()
	{

		$this->assertNotFalse(e107::getDb()->schema()->tablePhysical(self::EXTRA_COLUMN_TABLE)
			->addColumn(self::EXTRA_COLUMN, Column::define('VARCHAR', 32)->notNull()->defaultValue(''))
			->execute());
	}

	/**
	 * Neither engine drops a column only if it exists, so the column is looked up before it is dropped.
	 *
	 * @return void
	 */
	private function dropUndeclaredColumn()
	{

		$live = $this->live(self::EXTRA_COLUMN_TABLE);

		if($live !== null && $live->getColumn(self::EXTRA_COLUMN) !== null)
		{
			e107::getDb()->schema()->tablePhysical(self::EXTRA_COLUMN_TABLE)->dropColumn(self::EXTRA_COLUMN)->execute();
		}
	}

	/**
	 * @return void
	 */
	private function addRedundantIndex()
	{

		$this->assertNotFalse(e107::getDb()->schema()->tablePhysical(self::REDUNDANT_INDEX_TABLE)
			->addIndex(Index::fulltext(self::REDUNDANT_INDEX, self::REDUNDANT_INDEX_COLUMN))
			->execute());
	}

	/**
	 * @return void
	 */
	private function dropRedundantIndex()
	{

		$live = $this->live(self::REDUNDANT_INDEX_TABLE);

		if($live !== null && $live->getIndex(self::REDUNDANT_INDEX) !== null)
		{
			e107::getDb()->schema()->tablePhysical(self::REDUNDANT_INDEX_TABLE)->dropIndex(self::REDUNDANT_INDEX)->execute();
		}
	}

	/**
	 * @param string $table unprefixed table name.
	 * @return TableSchema|null the live table, as the connection's own reader reads it.
	 */
	private function live($table)
	{

		return e107::getDb()->getSchemaManager()->getReader()->read(MPREFIX . $table);
	}
}
