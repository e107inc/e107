<?php

/**
 * The db_verify admin screen, driven end to end over HTTP.
 *
 * Repairs are asserted against the server's own account of the table, never
 * against what the screen reported.
 *
 * @see db_verify::runFix()        the entry point under test
 * @see db_verify::requestedPlan() what one ticked checkbox is turned into
 * @see db_verify::renderResults() the form these tests submit
 */
class DbVerifyRepairCest
{
	/** Tools -> Database -> Check database validity. */
	const ROUTE = '/e107_admin/db.php?mode=verify_sql';

	/** Key of the core schema file in {@see db_verify::$sqlFileTables}. */
	const SQL_FILE = 'core';

	/** Unprefixed, as the schema file and the form both name it. */
	const TABLE = 'upload';

	const PREFIXED_TABLE = 'e107_upload';

	/** The column to drop: ordinary and unindexed, so dropping it takes nothing else with it. */
	const COLUMN = 'upload_website';

	/** COLUMN_TYPE core_sql.php declares for {@see COLUMN}. */
	const COLUMN_TYPE = 'varchar(100)';

	/** Where the declaration puts {@see COLUMN}; column order is never reported as drift. */
	const COLUMN_ORDINAL = 4;

	/** The secondary index to drop, and the column it covers. */
	const INDEX = 'upload_active';

	const INDEXED_COLUMN = 'upload_active';

	/** Where the declaration puts {@see INDEXED_COLUMN}. */
	const INDEXED_COLUMN_ORDINAL = 13;

	/**
	 * The fieldset {@see db_verify::renderResults()} writes for drifted tables.
	 * Scope screen assertions to it: the selection form names every table too.
	 */
	const RESULTS_SELECTOR = '#core-db-verify-results';

	/** DBVLAN_11, the status cell of a row reporting a missing column. */
	const STATUS_FIELD_MISSING = 'Field missing!';

	/** DBVLAN_25, the status cell of a row reporting a missing index. */
	const STATUS_INDEX_MISSING = 'Index missing!';

	/** DBVLAN_16, the legend of the results screen. */
	const RESULTS_LEGEND = 'SQL Verification';

	/** {@see db_verify::$modes} entry for a column the live table does not have. */
	const MODE_ADD_COLUMN = 'insert';

	/** {@see db_verify::$modes} entry for an index the live table does not have. */
	const MODE_ADD_INDEX = 'index';

	/** @var string|null DDL for {@see TABLE} as it stood before this Cest touched it */
	private static $pristineDdl = null;

	public function _before(AcceptanceTester $I)
	{
		$this->restoreTable($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$this->restoreTable($I);
	}

	// -----------------------------------------------------------------
	// tests
	// -----------------------------------------------------------------

	public function theScreenRepairsAColumnAndAnIndexItReported(AcceptanceTester $I)
	{
		$I->wantTo('repair a missing column and a missing index through the db_verify admin screen');

		$this->dropColumn($I);
		$this->dropIndex($I);

		$I->assertNull($this->column($I, self::COLUMN),
			'Fixture failed: `'.self::COLUMN.'` is still there, so the test would prove nothing.');
		$I->assertSame(array(), $this->index($I, self::INDEX),
			'Fixture failed: index `'.self::INDEX.'` is still there, so the test would prove nothing.');

		$this->openResultsFor($I, self::SQL_FILE);

		$I->see(self::TABLE, self::RESULTS_SELECTOR);
		$I->see(self::COLUMN, self::RESULTS_SELECTOR);
		$I->see(self::STATUS_FIELD_MISSING, self::RESULTS_SELECTOR);
		$I->see(self::STATUS_INDEX_MISSING, self::RESULTS_SELECTOR);

		$I->checkOption($this->fixCheckbox(self::COLUMN, self::MODE_ADD_COLUMN));
		$I->checkOption($this->fixCheckbox(self::INDEX, self::MODE_ADD_INDEX));

		$I->click('runfix');
		$I->seeResponseCodeIs(200);

		$column = $this->column($I, self::COLUMN);

		$I->assertNotNull($column,
			'The repair did not land: `'.self::PREFIXED_TABLE.'` still has no `'.self::COLUMN.'` column.');
		$I->assertSame(self::COLUMN_TYPE, $column['COLUMN_TYPE'],
			'`'.self::COLUMN.'` came back with a type core_sql.php does not declare.');
		$I->assertSame('NO', $column['IS_NULLABLE'],
			'`'.self::COLUMN.'` came back nullable, which core_sql.php does not declare.');
		$I->assertSame('', $this->unquote($column['COLUMN_DEFAULT']),
			'`'.self::COLUMN.'` came back without the empty-string default core_sql.php declares.');
		$I->assertSame(self::COLUMN_ORDINAL, (int) $column['ORDINAL_POSITION'],
			'`'.self::COLUMN.'` was put back in the wrong place: the repair appended it rather than '
			.'splicing it in after the column that precedes it in the declaration.');

		$parts = $this->index($I, self::INDEX);

		$I->assertCount(1, $parts,
			'The repair did not land: `'.self::PREFIXED_TABLE.'` still has no `'.self::INDEX.'` index.');
		$I->assertSame(self::INDEXED_COLUMN, $parts[0]['COLUMN_NAME'],
			'Index `'.self::INDEX.'` came back covering a column core_sql.php does not declare it over.');
		$I->assertSame(1, (int) $parts[0]['NON_UNIQUE'],
			'Index `'.self::INDEX.'` came back unique, which core_sql.php does not declare.');

		$ddl = $this->showCreateTable($I);

		$I->assertStringContainsString('`'.self::COLUMN.'` '.self::COLUMN_TYPE, $ddl);
		$I->assertStringContainsString('KEY `'.self::INDEX.'` (`'.self::INDEXED_COLUMN.'`)', $ddl);
	}

	public function theScreenRepairsOnlyWhatWasTicked(AcceptanceTester $I)
	{
		$I->wantTo('repair only the item whose checkbox I ticked, and leave the other damage alone');

		$this->dropColumn($I);
		$this->dropIndex($I);

		$this->openResultsFor($I, self::SQL_FILE);

		$I->see(self::STATUS_FIELD_MISSING, self::RESULTS_SELECTOR);
		$I->see(self::STATUS_INDEX_MISSING, self::RESULTS_SELECTOR);

		$I->checkOption($this->fixCheckbox(self::COLUMN, self::MODE_ADD_COLUMN));

		$I->click('runfix');
		$I->seeResponseCodeIs(200);

		$I->assertNotNull($this->column($I, self::COLUMN),
			'The ticked item was not repaired: `'.self::COLUMN.'` is still missing.');

		$I->assertSame(array(), $this->index($I, self::INDEX),
			'An item nobody ticked was repaired: index `'.self::INDEX.'` came back although its '
			.'checkbox was left clear, so runFix() is applying the whole plan rather than the '
			.'changes the form asked for.');
	}

	public function theScreenTellsAColumnFromAnIndexOfTheSameName(AcceptanceTester $I)
	{
		$I->wantTo('repair the column and not the index when the two are named alike');

		$this->dropColumn($I, self::INDEXED_COLUMN);

		$I->assertNull($this->column($I, self::INDEXED_COLUMN),
			'Fixture failed: `'.self::INDEXED_COLUMN.'` is still there, so the test would prove nothing.');
		$I->assertSame(array(), $this->index($I, self::INDEX),
			'Fixture failed: dropping `'.self::INDEXED_COLUMN.'` was expected to take index `'
			.self::INDEX.'` with it, and did not, so the two rows this test needs are not both there.');

		$this->openResultsFor($I, self::SQL_FILE);

		$I->see(self::STATUS_FIELD_MISSING, self::RESULTS_SELECTOR);
		$I->see(self::STATUS_INDEX_MISSING, self::RESULTS_SELECTOR);

		$I->checkOption($this->fixCheckbox(self::INDEXED_COLUMN, self::MODE_ADD_COLUMN));

		$I->click('runfix');
		$I->seeResponseCodeIs(200);

		$column = $this->column($I, self::INDEXED_COLUMN);

		$I->assertNotNull($column,
			'The ticked item was not repaired: `'.self::INDEXED_COLUMN.'` is still missing.');
		$I->assertSame(self::INDEXED_COLUMN_ORDINAL, (int) $column['ORDINAL_POSITION'],
			'`'.self::INDEXED_COLUMN.'` was put back in the wrong place.');

		$I->assertSame(array(), $this->index($I, self::INDEX),
			'Index `'.self::INDEX.'` was repaired although the box ticked was the one for the column '
			.'of the same name, so requestedPlan() is matching a request on the field name and '
			.'ignoring the repair mode the checkbox carries.');
	}

	// -----------------------------------------------------------------
	// driving the screen
	// -----------------------------------------------------------------

	/**
	 * Log in, open the screen, tick one schema file and submit.
	 *
	 * @param string $sqlFile value of the table-selection checkbox, eg. 'core'
	 * @return void
	 */
	private function openResultsFor(AcceptanceTester $I, $sqlFile)
	{
		$I->loginAsAdmin();

		$I->amOnPage(self::ROUTE);
		$I->seeResponseCodeIs(200);

		$I->checkOption('input[name^="verify_table"][value="'.$sqlFile.'"]');
		$I->click('db_verify');

		$I->seeResponseCodeIs(200);
		$I->see(self::RESULTS_LEGEND);
	}

	/**
	 * CSS for one repair checkbox, matched on value too because an index can share a column's name.
	 *
	 * @param string $field column or index name, as the row names it
	 * @param string $mode  db_verify::$modes value
	 * @return string
	 */
	private function fixCheckbox($field, $mode)
	{
		$name = 'fix['.self::SQL_FILE.']['.self::TABLE.']['.$field.'][]';

		return 'input[name="'.$name.'"][value="'.$mode.'"]';
	}

	// -----------------------------------------------------------------
	// the fixture, and the server's account of it
	// -----------------------------------------------------------------

	/**
	 * @return \PDO the suite's own connection
	 */
	private function dbh(AcceptanceTester $I)
	{
		return $I->getDbModule()->_getDbh();
	}

	/**
	 * @param string $column defaults to {@see COLUMN}
	 * @return void
	 */
	private function dropColumn(AcceptanceTester $I, $column = self::COLUMN)
	{
		$this->dbh($I)->exec('ALTER TABLE `'.self::PREFIXED_TABLE.'` DROP COLUMN `'.$column.'`');
	}

	/**
	 * @return void
	 */
	private function dropIndex(AcceptanceTester $I)
	{
		$this->dbh($I)->exec('ALTER TABLE `'.self::PREFIXED_TABLE.'` DROP INDEX `'.self::INDEX.'`');
	}

	/**
	 * One column as information_schema has it.
	 *
	 * @param string $column
	 * @return array|null null when the table does not have it
	 */
	private function column(AcceptanceTester $I, $column)
	{
		$statement = $this->dbh($I)->prepare('
			SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, ORDINAL_POSITION
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
		');
		$statement->execute(array(self::PREFIXED_TABLE, $column));

		$row = $statement->fetch(\PDO::FETCH_ASSOC);

		return $row === false ? null : $row;
	}

	/**
	 * Strip one layer of SQL string quoting from a COLUMN_DEFAULT.
	 *
	 * MariaDB 10.2 and later quote a string default; MySQL reports the value itself.
	 *
	 * @param string|null $default as information_schema gives it
	 * @return string|null
	 */
	private function unquote($default)
	{
		if(!is_string($default) || strlen($default) < 2)
		{
			return $default;
		}

		if($default[0] === "'" && substr($default, -1) === "'")
		{
			return str_replace("''", "'", (string) substr($default, 1, -1));
		}

		return $default;
	}

	/**
	 * One index's parts, in order.
	 *
	 * @param string $index
	 * @return array empty when the table does not have it
	 */
	private function index(AcceptanceTester $I, $index)
	{
		$statement = $this->dbh($I)->prepare('
			SELECT COLUMN_NAME, NON_UNIQUE, SEQ_IN_INDEX
			FROM information_schema.STATISTICS
			WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
			ORDER BY SEQ_IN_INDEX
		');
		$statement->execute(array(self::PREFIXED_TABLE, $index));

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * @return string the server's own DDL for the table under test
	 */
	private function showCreateTable(AcceptanceTester $I)
	{
		$row = $this->dbh($I)
			->query('SHOW CREATE TABLE `'.self::PREFIXED_TABLE.'`')
			->fetch(\PDO::FETCH_NUM);

		return $row[1];
	}

	/**
	 * Put {@see TABLE} back as it stood before this Cest touched it.
	 *
	 * @return void
	 */
	private function restoreTable(AcceptanceTester $I)
	{
		if(self::$pristineDdl === null)
		{
			$this->reinstateDroppedParts($I);

			self::$pristineDdl = $this->showCreateTable($I);

			return;
		}

		if($this->showCreateTable($I) === self::$pristineDdl)
		{
			return;
		}

		$dbh = $this->dbh($I);

		$dbh->exec('DROP TABLE `'.self::PREFIXED_TABLE.'`');
		$dbh->exec(self::$pristineDdl);
	}

	/**
	 * Put back, by declaration, whatever of the parts these tests drop is missing.
	 *
	 * Only the first call of a run needs it, when a killed predecessor left damage.
	 *
	 * @return void
	 */
	private function reinstateDroppedParts(AcceptanceTester $I)
	{
		$dbh = $this->dbh($I);

		if($this->column($I, self::COLUMN) === null)
		{
			$dbh->exec('ALTER TABLE `'.self::PREFIXED_TABLE.'` ADD COLUMN `'.self::COLUMN.'` '
				."varchar(100) NOT NULL DEFAULT '' AFTER `upload_email`");
		}

		if($this->column($I, self::INDEXED_COLUMN) === null)
		{
			$dbh->exec('ALTER TABLE `'.self::PREFIXED_TABLE.'` ADD COLUMN `'.self::INDEXED_COLUMN.'` '
				."tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER `upload_filesize`");
		}

		if($this->index($I, self::INDEX) === array())
		{
			$dbh->exec('ALTER TABLE `'.self::PREFIXED_TABLE.'` '
				.'ADD INDEX `'.self::INDEX.'` (`'.self::INDEXED_COLUMN.'`)');
		}
	}
}
