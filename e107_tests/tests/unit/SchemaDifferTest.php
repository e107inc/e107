<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Diff\ColumnDiff;
use e107\Database\Schema\Diff\IndexDiff;
use e107\Database\Schema\Diff\SchemaDiffer;
use e107\Database\Schema\Diff\TableDiff;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexPart;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\TableSchema;

/**
 * DB-less tests for {@see SchemaDiffer}, which sorts two {@see TableSchema} objects into a {@see TableDiff}.
 */
class SchemaDifferTest extends \Test\Unit
{
	/**
	 * Nothing in this class is require_once'd, so every other test here checks the autoload path as well.
	 */
	public function testTheDifferAutoloadsFromItsNamespacePath()
	{
		$this->assertTrue(
			class_exists('e107\Database\Schema\Diff\SchemaDiffer'),
			'SchemaDiffer must autoload from e107_handlers/Database/Schema/Diff/SchemaDiffer.php'
		);
	}

	// --- the clean case ---------------------------------------------------

	public function testIdenticalSchemasShowNoDrift()
	{
		$diff = $this->diff('core', $this->table(), $this->table(), 'news');

		$this->assertInstanceOf('e107\Database\Schema\Diff\TableDiff', $diff);
		$this->assertFalse($diff->isMissing());
		$this->assertFalse($diff->hasDrift());
		$this->assertNull($diff->getEngineChange());
		$this->assertNull($diff->getCharsetChange());
		$this->assertSame($this->counts(), $this->countsOf($diff));
	}

	public function testTheDeclaringFileAndTableNameTravelWithTheDiff()
	{
		$diff = $this->diff('forum', $this->table(), $this->table(), 'news');

		$this->assertSame('forum', $diff->getSqlFile());
		$this->assertSame('news', $diff->getTableName());
	}

	/**
	 * The declared side is materialised into a scratch table, so its own name is never the logical one.
	 */
	public function testAnExplicitTableNameOverridesEitherSchemaName()
	{
		$diff = $this->diff('core', $this->table(array('name' => 'e107_dbvscratch_ab12cd34')), $this->table(), 'news');

		$this->assertSame('news', $diff->getTableName());
	}

	public function testTheTableNameFallsBackToTheDeclaredSchema()
	{
		$diff = $this->diff('core', $this->table(), $this->table());

		$this->assertSame('e107_news', $diff->getTableName());
	}

	public function testEveryDiffCarriesTheDeclaredShapeNotOnlyAMissingOne()
	{
		$expected = $this->table();

		$clean = $this->diff('core', $expected, $this->table(), 'news');
		$this->assertSame($expected, $clean->getExpectedTable(), 'A clean diff still has to carry the declared shape.');

		$drifted = $this->diff('core', $expected, $this->table(array('engine' => 'MyISAM')), 'news');
		$this->assertSame($expected, $drifted->getExpectedTable());
	}

	// --- a missing table --------------------------------------------------

	public function testAnAbsentTableIsMissingAndCarriesTheDeclaredShape()
	{
		$expected = $this->table();
		$diff = $this->diff('core', $expected, null, 'news');

		$this->assertTrue($diff->isMissing());
		$this->assertTrue($diff->hasDrift());
		$this->assertSame($expected, $diff->getExpectedTable(), 'A missing table must carry its declared shape, or nothing can render the CREATE TABLE.');
		$this->assertSame($this->counts(), $this->countsOf($diff));
	}

	public function testAMissingDeclarationIsAProgrammingError()
	{
		$actual = $this->table();

		$this->assertThrowsInvalidArgument(function () use ($actual)
		{
			$differ = new SchemaDiffer();
			$differ->diff('core', null, $actual, 'news');
		});
	}

	public function testADeclaredShapeWithNoColumnsIsAProgrammingError()
	{
		$empty = new TableSchema('e107_news', 'InnoDB', 'utf8mb4', 'utf8mb4_general_ci', array(), array());
		$actual = $this->table();

		$this->assertThrowsInvalidArgument(function () use ($empty, $actual)
		{
			$differ = new SchemaDiffer();
			$differ->diff('core', $empty, $actual, 'news');
		});

		$this->assertThrowsInvalidArgument(function () use ($empty)
		{
			$differ = new SchemaDiffer();
			$differ->diff('core', $empty, null, 'news');
		});
	}

	// --- columns ----------------------------------------------------------

	public function testADeclaredColumnAbsentFromTheDatabaseIsMissing()
	{
		$expected = $this->table();
		$actual = $this->table(array('columns' => array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(200)', 2, array('default' => '')),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame($this->counts(array('missingColumns' => 1)), $this->countsOf($diff));
		$this->assertSame(array('news_datestamp'), array_keys($diff->getMissingColumns()));

		$missing = $diff->getMissingColumns();
		$this->assertInstanceOf('e107\Database\Schema\Introspect\ColumnSchema', $missing['news_datestamp']);
		$this->assertSame('int(10) unsigned', $missing['news_datestamp']->getColumnType());
	}

	/**
	 * The three missing names sort differently both ascending and descending, so no re-sort passes by coincidence.
	 */
	public function testMissingColumnsKeepTheirDeclaredOrder()
	{
		$declared = array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(200)', 2, array('default' => '')),
			$this->column('news_author', 'int(10) unsigned', 3, array('default' => '0')),
			$this->column('news_datestamp', 'int(10) unsigned', 4, array('default' => '0')),
			$this->column('news_updated', 'int(10) unsigned', 5, array('default' => '0')),
		);

		$expected = $this->table(array('columns' => $declared));
		$actual = $this->table(array('columns' => array($declared[0], $declared[3])));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertSame($this->counts(array('missingColumns' => 3)), $this->countsOf($diff));
		$this->assertSame(
			array('news_title', 'news_author', 'news_updated'),
			array_keys($diff->getMissingColumns()),
			'Missing columns come back in declared ordinal order, not sorted.'
		);
	}

	public function testAColumnWhoseTypeChangedIsModifiedAndCarriesBothColumns()
	{
		$expected = $this->table();
		$actual = $this->table(array('columns' => array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(100)', 2, array('default' => '')),
			$this->column('news_datestamp', 'int(10) unsigned', 3, array('default' => '0')),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame($this->counts(array('modifiedColumns' => 1)), $this->countsOf($diff));

		$modified = $diff->getModifiedColumns();
		$this->assertSame(array('news_title'), array_keys($modified));

		$columnDiff = $modified['news_title'];
		$this->assertInstanceOf('e107\Database\Schema\Diff\ColumnDiff', $columnDiff);
		$this->assertSame('varchar(200)', $columnDiff->getExpected()->getColumnType());
		$this->assertSame('varchar(100)', $columnDiff->getActual()->getColumnType());
		$this->assertSame(array('columnType'), $columnDiff->getChangedFields());
	}

	public function testAColumnThatDiffersOnlyInItsDefaultIsModified()
	{
		$expected = $this->table();
		$actual = $this->table(array('columns' => array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(200)', 2, array('default' => '')),
			$this->column('news_datestamp', 'int(10) unsigned', 3, array('default' => null)),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$modified = $diff->getModifiedColumns();
		$this->assertSame($this->counts(array('modifiedColumns' => 1)), $this->countsOf($diff));
		$this->assertSame(array('default'), $modified['news_datestamp']->getChangedFields());
		$this->assertSame('0', $modified['news_datestamp']->getExpected()->getDefault());
		$this->assertNull($modified['news_datestamp']->getActual()->getDefault());
	}

	public function testAColumnThatDiffersOnlyInItsCharacterSetOrCommentIsModified()
	{
		$expected = $this->table(array('columns' => array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(200)', 2, array(
				'default'   => '',
				'charset'   => 'utf8mb4',
				'collation' => 'utf8mb4_bin',
				'comment'   => 'headline',
			)),
			$this->column('news_datestamp', 'int(10) unsigned', 3, array('default' => '0')),
		)));

		$diff = $this->diff('core', $expected, $this->table(), 'news');

		$modified = $diff->getModifiedColumns();
		$this->assertSame($this->counts(array('modifiedColumns' => 1)), $this->countsOf($diff));
		$this->assertSame(array('charset', 'collation', 'comment'), $modified['news_title']->getChangedFields());
		$this->assertSame('utf8mb4_bin', $modified['news_title']->getExpected()->getCollation());
		$this->assertNull($modified['news_title']->getActual()->getCollation());
	}

	public function testAnUndeclaredColumnIsExtraAndIsNotDrift()
	{
		$expected = $this->table();
		$actual = $this->table(array('columns' => array_merge($this->newsColumns(), array(
			$this->column('news_thumbnail', 'varchar(255)', 4, array('nullable' => true)),
		))));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertFalse($diff->hasDrift(), 'An extra column is recorded but is not drift.');
		$this->assertSame($this->counts(array('extraColumns' => 1)), $this->countsOf($diff));
		$this->assertSame(array('news_thumbnail'), array_keys($diff->getExtraColumns()));
	}

	public function testAnExtraColumnDoesNotMaskRealDrift()
	{
		$expected = $this->table();
		$actual = $this->table(array('columns' => array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(200)', 2, array('default' => '')),
			$this->column('news_thumbnail', 'varchar(255)', 3, array('nullable' => true)),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame($this->counts(array('missingColumns' => 1, 'extraColumns' => 1)), $this->countsOf($diff));
	}

	public function testAColumnThatOnlyMovedIsNotADifference()
	{
		$expected = $this->table();
		$actual = $this->table(array('columns' => array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_datestamp', 'int(10) unsigned', 2, array('default' => '0')),
			$this->column('news_title', 'varchar(200)', 3, array('default' => '')),
		)));

		$this->assertSame(2, $expected->getColumn('news_title')->getPosition());
		$this->assertSame(3, $actual->getColumn('news_title')->getPosition());

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertFalse($diff->hasDrift());
		$this->assertSame($this->counts(), $this->countsOf($diff));
	}

	// --- indexes ----------------------------------------------------------

	public function testADeclaredIndexAbsentFromTheDatabaseIsMissing()
	{
		$expected = $this->table();
		$actual = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame($this->counts(array('missingIndexes' => 1)), $this->countsOf($diff));

		$missing = $diff->getMissingIndexes();
		$this->assertSame(array('news_datestamp'), array_keys($missing));
		$this->assertInstanceOf('e107\Database\Schema\Introspect\IndexSchema', $missing['news_datestamp']);
		$this->assertSame(array('news_datestamp'), $missing['news_datestamp']->getColumnNames());
	}

	public function testAnIndexWhoseColumnOrderChangedIsModified()
	{
		$expected = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_pair', IndexSchema::KIND_INDEX, array('news_title', 'news_datestamp')),
		)));
		$actual = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_pair', IndexSchema::KIND_INDEX, array('news_datestamp', 'news_title')),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift(), 'A reordered index is drift.');
		$this->assertSame($this->counts(array('modifiedIndexes' => 1)), $this->countsOf($diff));

		$modified = $diff->getModifiedIndexes();
		$this->assertSame(array('news_pair'), array_keys($modified));

		$indexDiff = $modified['news_pair'];
		$this->assertInstanceOf('e107\Database\Schema\Diff\IndexDiff', $indexDiff);
		$this->assertSame(array('news_title', 'news_datestamp'), $indexDiff->getExpected()->getColumnNames());
		$this->assertSame(array('news_datestamp', 'news_title'), $indexDiff->getActual()->getColumnNames());
		$this->assertSame(array('parts'), $indexDiff->getChangedFields());
	}

	public function testAnIndexThatGainedASecondColumnIsModified()
	{
		$expected = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_datestamp', IndexSchema::KIND_INDEX, array('news_datestamp', 'news_title')),
		)));
		$actual = $this->table();

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame($this->counts(array('modifiedIndexes' => 1)), $this->countsOf($diff));

		$modified = $diff->getModifiedIndexes();
		$indexDiff = $modified['news_datestamp'];
		$this->assertCount(2, $indexDiff->getExpected()->getParts());
		$this->assertCount(1, $indexDiff->getActual()->getParts());
		$this->assertSame(array('parts'), $indexDiff->getChangedFields());
	}

	public function testTwoIndexesOverTheSameLeadingColumnAreComparedSeparately()
	{
		$columns = array_merge($this->newsColumns(), array(
			$this->column('news_author', 'int(10) unsigned', 4, array('default' => '0')),
		));

		$expected = $this->table(array('columns' => $columns, 'indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_author', IndexSchema::KIND_INDEX, array('news_author')),
			$this->index('news_author_datestamp', IndexSchema::KIND_INDEX, array('news_author', 'news_datestamp')),
		)));
		$actual = $this->table(array('columns' => $columns, 'indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_author', IndexSchema::KIND_INDEX, array('news_author')),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame($this->counts(array('missingIndexes' => 1)), $this->countsOf($diff));
		$this->assertSame(array('news_author_datestamp'), array_keys($diff->getMissingIndexes()));
	}

	public function testAnIndexWhoseKindChangedIsModified()
	{
		$expected = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_datestamp', IndexSchema::KIND_UNIQUE, array('news_datestamp')),
		)));
		$actual = $this->table();

		$diff = $this->diff('core', $expected, $actual, 'news');

		$modified = $diff->getModifiedIndexes();
		$this->assertSame($this->counts(array('modifiedIndexes' => 1)), $this->countsOf($diff));
		$this->assertSame(array('kind'), $modified['news_datestamp']->getChangedFields());
		$this->assertSame(IndexSchema::KIND_UNIQUE, $modified['news_datestamp']->getExpected()->getKind());
		$this->assertSame(IndexSchema::KIND_INDEX, $modified['news_datestamp']->getActual()->getKind());
	}

	public function testAnIndexWhosePrefixLengthChangedIsModified()
	{
		$expected = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			new IndexSchema('news_title', IndexSchema::KIND_INDEX, array(new IndexPart('news_title', 10, IndexPart::ASC))),
		)));
		$actual = $this->table(array('indexes' => array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			new IndexSchema('news_title', IndexSchema::KIND_INDEX, array(new IndexPart('news_title', 20, IndexPart::ASC))),
		)));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$modified = $diff->getModifiedIndexes();
		$this->assertSame($this->counts(array('modifiedIndexes' => 1)), $this->countsOf($diff));
		$this->assertSame(array('parts'), $modified['news_title']->getChangedFields());

		$expectedParts = $modified['news_title']->getExpected()->getParts();
		$actualParts = $modified['news_title']->getActual()->getParts();
		$this->assertSame(10, $expectedParts[0]->getSubPart());
		$this->assertSame(20, $actualParts[0]->getSubPart());
	}

	public function testAnUndeclaredIndexIsExtraAndIsNotDrift()
	{
		$expected = $this->table();
		$actual = $this->table(array('indexes' => array_merge($this->newsIndexes(), array(
			$this->index('news_title_local', IndexSchema::KIND_INDEX, array('news_title')),
		))));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertFalse($diff->hasDrift(), 'An extra index is recorded but is not drift.');
		$this->assertSame($this->counts(array('extraIndexes' => 1)), $this->countsOf($diff));
		$this->assertSame(array('news_title_local'), array_keys($diff->getExtraIndexes()));
	}

	// --- disowned indexes --------------------------------------------------

	/**
	 * A disowned index is one e107 derived from an e_search configuration that the schema file has since covered.
	 */
	public function testADisownedIndexTheDeclarationDoesNotCarryIsRedundant()
	{
		$diff = $this->diff('core', $this->table(), $this->withDerivedFulltext(), 'news', array('ft_news_news_title'));

		$this->assertTrue($diff->hasDrift(), 'A redundant index is drift: dropping it is the fix.');
		$this->assertSame($this->counts(array('redundantIndexes' => 1)), $this->countsOf($diff));
		$this->assertSame(array('ft_news_news_title'), array_keys($diff->getRedundantIndexes()));
		$this->assertSame(array(), $diff->getExtraIndexes(), 'A redundant index is not also an extra.');
	}

	public function testTheSameIndexNotDisownedIsStillMerelyExtra()
	{
		$diff = $this->diff('core', $this->table(), $this->withDerivedFulltext(), 'news');

		$this->assertFalse($diff->hasDrift());
		$this->assertSame($this->counts(array('extraIndexes' => 1)), $this->countsOf($diff));
		$this->assertSame(array('ft_news_news_title'), array_keys($diff->getExtraIndexes()));
	}

	public function testADisownedNameTheDeclarationCarriesIsComparedNormally()
	{
		$expected = $this->withDerivedFulltext();
		$disowned = array('ft_news_news_title');

		$matching = $this->diff('core', $expected, $this->withDerivedFulltext(), 'news', $disowned);

		$this->assertFalse($matching->hasDrift());
		$this->assertSame($this->counts(), $this->countsOf($matching));

		$absent = $this->diff('core', $expected, $this->table(), 'news', $disowned);

		$this->assertSame($this->counts(array('missingIndexes' => 1)), $this->countsOf($absent));
		$this->assertSame(array('ft_news_news_title'), array_keys($absent->getMissingIndexes()));

		$drifted = $this->diff('core', $expected, $this->withDerivedFulltext('news_datestamp'), 'news', $disowned);

		$this->assertSame($this->counts(array('modifiedIndexes' => 1)), $this->countsOf($drifted));
		$this->assertSame(array('ft_news_news_title'), array_keys($drifted->getModifiedIndexes()));
	}

	// --- engine and character set ------------------------------------------

	public function testEngineDriftAloneIsDrift()
	{
		$diff = $this->diff('core', $this->table(), $this->table(array('engine' => 'MyISAM')), 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame(array('expected' => 'InnoDB', 'actual' => 'MyISAM'), $diff->getEngineChange());
		$this->assertNull($diff->getCharsetChange());
		$this->assertSame($this->counts(), $this->countsOf($diff));
	}

	public function testEngineCaseAloneIsNotDrift()
	{
		$diff = $this->diff('core', $this->table(), $this->table(array('engine' => 'INNODB')), 'news');

		$this->assertFalse($diff->hasDrift());
		$this->assertNull($diff->getEngineChange());
	}

	public function testCharsetDriftAloneIsDrift()
	{
		$actual = $this->table(array('charset' => 'latin1', 'collation' => 'latin1_swedish_ci'));

		$diff = $this->diff('core', $this->table(), $actual, 'news');

		$this->assertTrue($diff->hasDrift());
		$this->assertSame(array('expected' => 'utf8mb4', 'actual' => 'latin1'), $diff->getCharsetChange());
		$this->assertNull($diff->getEngineChange());
		$this->assertSame($this->counts(), $this->countsOf($diff));
	}

	public function testADeclarationThatStatesNoEngineIsNotEngineDrift()
	{
		$diff = $this->diff('core', $this->table(array('engine' => '')), $this->table(array('engine' => 'MyISAM')), 'news');

		$this->assertNull($diff->getEngineChange());
		$this->assertFalse($diff->hasDrift());
	}

	public function testADeclarationThatStatesNoCharacterSetIsNotCharsetDrift()
	{
		$expected = $this->table(array('charset' => null, 'collation' => null));
		$actual = $this->table(array('charset' => 'latin1', 'collation' => 'latin1_swedish_ci'));

		$diff = $this->diff('core', $expected, $actual, 'news');

		$this->assertNull($diff->getCharsetChange());
		$this->assertFalse($diff->hasDrift());
	}

	// --- fixtures -----------------------------------------------------------

	/**
	 * @param string $sqlFile
	 * @param TableSchema|null $expected
	 * @param TableSchema|null $actual
	 * @param string|null $tableName
	 * @param string[] $disownedIndexNames derived indexes the declaration covers.
	 * @return TableDiff
	 */
	private function diff($sqlFile, $expected, $actual, $tableName = null, array $disownedIndexNames = array())
	{
		$differ = new SchemaDiffer();

		return $differ->diff($sqlFile, $expected, $actual, $tableName, $disownedIndexNames);
	}

	/**
	 * @param string $name
	 * @param string $columnType
	 * @param int $position
	 * @param array $overrides nullable, default, extra, charset, collation, comment.
	 * @return ColumnSchema
	 */
	private function column($name, $columnType, $position, array $overrides = array())
	{
		$fields = array_merge(array(
			'nullable'  => false,
			'default'   => null,
			'extra'     => '',
			'charset'   => null,
			'collation' => null,
			'comment'   => '',
		), $overrides);

		return new ColumnSchema(
			$name,
			$columnType,
			$fields['nullable'],
			$fields['default'],
			$fields['extra'],
			$fields['charset'],
			$fields['collation'],
			$fields['comment'],
			$position
		);
	}

	/**
	 * @param string $name
	 * @param string $kind
	 * @param string[] $columnNames in index order.
	 * @return IndexSchema
	 */
	private function index($name, $kind, array $columnNames)
	{
		$parts = array();

		foreach($columnNames as $columnName)
		{
			$parts[] = new IndexPart($columnName, null, IndexPart::ASC);
		}

		return new IndexSchema($name, $kind, $parts);
	}

	/**
	 * @return ColumnSchema[]
	 */
	private function newsColumns()
	{
		return array(
			$this->column('news_id', 'int(10) unsigned', 1, array('extra' => 'auto_increment')),
			$this->column('news_title', 'varchar(200)', 2, array('default' => '')),
			$this->column('news_datestamp', 'int(10) unsigned', 3, array('default' => '0')),
		);
	}

	/**
	 * @return IndexSchema[]
	 */
	private function newsIndexes()
	{
		return array(
			$this->index('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id')),
			$this->index('news_datestamp', IndexSchema::KIND_INDEX, array('news_datestamp')),
		);
	}

	/**
	 * The news table with one more index, named as {@see \e_search_fulltext_indexer::generateIndexDefinition()} does.
	 *
	 * @param string $column the column it indexes.
	 * @return TableSchema
	 */
	private function withDerivedFulltext($column = 'news_title')
	{
		return $this->table(array('indexes' => array_merge($this->newsIndexes(), array(
			$this->index('ft_news_news_title', IndexSchema::KIND_FULLTEXT, array($column)),
		))));
	}

	/**
	 * A three-column InnoDB news table with a PRIMARY KEY and one ordinary index.
	 *
	 * @param array $overrides name, engine, charset, collation, columns, indexes.
	 * @return TableSchema
	 */
	private function table(array $overrides = array())
	{
		$fields = array_merge(array(
			'name'      => 'e107_news',
			'engine'    => 'InnoDB',
			'charset'   => 'utf8mb4',
			'collation' => 'utf8mb4_general_ci',
			'columns'   => $this->newsColumns(),
			'indexes'   => $this->newsIndexes(),
		), $overrides);

		return new TableSchema(
			$fields['name'],
			$fields['engine'],
			$fields['charset'],
			$fields['collation'],
			$fields['columns'],
			$fields['indexes']
		);
	}

	/**
	 * @param array $overrides
	 * @return array the list parts of a diff, all empty unless named.
	 */
	private function counts(array $overrides = array())
	{
		return array_merge(array(
			'missingColumns'   => 0,
			'modifiedColumns'  => 0,
			'extraColumns'     => 0,
			'missingIndexes'   => 0,
			'modifiedIndexes'  => 0,
			'extraIndexes'     => 0,
			'redundantIndexes' => 0,
		), $overrides);
	}

	/**
	 * @param TableDiff $diff
	 * @return array in the same shape as {@see SchemaDifferTest::counts()}.
	 */
	private function countsOf(TableDiff $diff)
	{
		return array(
			'missingColumns'   => count($diff->getMissingColumns()),
			'modifiedColumns'  => count($diff->getModifiedColumns()),
			'extraColumns'     => count($diff->getExtraColumns()),
			'missingIndexes'   => count($diff->getMissingIndexes()),
			'modifiedIndexes'  => count($diff->getModifiedIndexes()),
			'extraIndexes'     => count($diff->getExtraIndexes()),
			'redundantIndexes' => count($diff->getRedundantIndexes()),
		);
	}

	/**
	 * @param callable $callback
	 */
	private function assertThrowsInvalidArgument($callback)
	{
		try
		{
			$callback();
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertInstanceOf('InvalidArgumentException', $e);

			return;
		}

		$this->fail('Expected InvalidArgumentException was not thrown');
	}
}
