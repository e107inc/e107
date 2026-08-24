<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Platform\MysqlPlatform;
use e107\Database\Schema\Declared\DeclaredTable;
use e107\Database\Schema\Diff\ColumnDiff;
use e107\Database\Schema\Diff\IndexDiff;
use e107\Database\Schema\Diff\TableDiff;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexPart;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Plan\ChangeInterface;
use e107\Database\Schema\Plan\FixPlan;
use e107\Database\Schema\SchemaBuilder;

// The stub change below implements ChangeInterface at file scope, and this file is parsed before e107's autoloader is up.
require_once(__DIR__.'/../../../e107_handlers/Database/Schema/Plan/ChangeInterface.php');

/**
 * DB-less tests for the db_verify successor's value objects: {@see DeclaredTable}, {@see TableDiff} and {@see FixPlan}.
 */
class SchemaDiffValueObjectTest extends \Test\Unit
{
	// --- autoloading ----------------------------------------------------

	/**
	 * Beyond the ChangeInterface require above, nothing here is require_once'd, so every other test checks this too.
	 */
	public function testEveryValueObjectAutoloadsFromItsNamespacePath()
	{
		$classes = array(
			'e107\\Database\\Schema\\Declared\\DeclaredTable',
			'e107\\Database\\Schema\\Diff\\ColumnDiff',
			'e107\\Database\\Schema\\Diff\\IndexDiff',
			'e107\\Database\\Schema\\Diff\\TableDiff',
			'e107\\Database\\Schema\\Plan\\FixPlan',
		);

		foreach($classes as $class)
		{
			$this->assertTrue(class_exists($class), $class.' must autoload from e107_handlers/'.str_replace('\\', '/', substr($class, 5)).'.php');
		}

		$this->assertTrue(interface_exists('e107\\Database\\Schema\\Declared\\EngineCharsetResolverInterface'));
		$this->assertTrue(interface_exists('e107\\Database\\Schema\\Plan\\ChangeInterface'));
	}

	// --- DeclaredTable --------------------------------------------------

	public function testDeclaredTableCarriesTheDeclaringFile()
	{
		$table = new DeclaredTable('forum', 'forum_thread', 'thread_id int(10) NOT NULL', 'InnoDB', 'utf8mb4');

		$this->assertSame('forum', $table->getSqlFile());
		$this->assertSame('forum_thread', $table->getName());
		$this->assertSame('thread_id int(10) NOT NULL', $table->getBody());
		$this->assertSame('InnoDB', $table->getDeclaredEngine());
		$this->assertSame('utf8mb4', $table->getDeclaredCharset());
	}

	public function testDeclaredTableNormalisesAnUndeclaredEngineAndCharsetToNull()
	{
		$table = new DeclaredTable('core', 'news', 'news_id int(10) NOT NULL', '', '   ');

		$this->assertNull($table->getDeclaredEngine());
		$this->assertNull($table->getDeclaredCharset());
		$this->assertTrue($table->equals(new DeclaredTable('core', 'news', 'news_id int(10) NOT NULL')));
		$this->assertFalse($table->equals(new DeclaredTable('forum', 'news', 'news_id int(10) NOT NULL')));
	}

	public function testDeclaredTableRejectsAnEmptyName()
	{
		$this->assertThrows('InvalidArgumentException', function ()
		{
			new DeclaredTable('core', '', 'news_id int(10) NOT NULL');
		});
	}

	// --- ColumnDiff / IndexDiff -----------------------------------------

	public function testColumnDiffHoldsWholeColumns()
	{
		$expected = new ColumnSchema('news_title', 'varchar(255)', false, '', '', null, null, '', 3);
		$actual = new ColumnSchema('news_title', 'varchar(200)', true, null, '', null, null, '', 7);

		$diff = new ColumnDiff($expected, $actual);

		$this->assertSame('news_title', $diff->getName());
		$this->assertSame($expected, $diff->getExpected());
		$this->assertSame($actual, $diff->getActual());
		$this->assertSame(array('columnType', 'nullable', 'default'), $diff->getChangedFields());
		$this->assertTrue($diff->hasChanges());
	}

	public function testAColumnThatOnlyMovedHasNotChanged()
	{
		$expected = new ColumnSchema('news_title', 'varchar(255)', false, '', '', null, null, '', 3);
		$actual = new ColumnSchema('news_title', 'varchar(255)', false, '', '', null, null, '', 9);

		$diff = new ColumnDiff($expected, $actual);

		$this->assertSame(array(), $diff->getChangedFields());
		$this->assertFalse($diff->hasChanges());
	}

	public function testIndexDiffHoldsWholeIndexes()
	{
		$expected = new IndexSchema('news_datestamp', IndexSchema::KIND_INDEX, array(new IndexPart('news_datestamp', null, 'A')));
		$actual = new IndexSchema('news_datestamp', IndexSchema::KIND_UNIQUE, array(new IndexPart('news_datestamp', 10, 'A')));

		$diff = new IndexDiff($expected, $actual);

		$this->assertSame('news_datestamp', $diff->getName());
		$this->assertSame($expected, $diff->getExpected());
		$this->assertSame($actual, $diff->getActual());
		$this->assertSame(array('kind', 'parts'), $diff->getChangedFields());
		$this->assertTrue($diff->hasChanges());
	}

	public function testAnIdenticalIndexHasNotChanged()
	{
		$parts = array(new IndexPart('news_datestamp', null, 'A'));
		$diff = new IndexDiff(
			new IndexSchema('news_datestamp', IndexSchema::KIND_INDEX, $parts),
			new IndexSchema('news_datestamp', IndexSchema::KIND_INDEX, $parts)
		);

		$this->assertSame(array(), $diff->getChangedFields());
		$this->assertFalse($diff->hasChanges());
	}

	// --- TableDiff accessors --------------------------------------------

	public function testTableDiffCarriesEveryPartWhole()
	{
		$expectedTable = $this->fake('table');
		$missingColumn = $this->fake('column');
		$modifiedColumn = $this->fake('columnDiff');
		$extraColumn = $this->fake('column');
		$missingIndex = $this->fake('index');
		$modifiedIndex = $this->fake('indexDiff');
		$extraIndex = $this->fake('index');
		$redundantIndex = $this->fake('index');

		$diff = new TableDiff('core', 'news', array(
			'expectedTable'    => $expectedTable,
			'engineChange'     => array('expected' => 'InnoDB', 'actual' => 'MyISAM'),
			'charsetChange'    => array('expected' => 'utf8mb4', 'actual' => 'utf8mb3'),
			'missingColumns'   => array('news_thumbnail' => $missingColumn),
			'modifiedColumns'  => array('news_id' => $modifiedColumn),
			'extraColumns'     => array('news_plugin_field' => $extraColumn),
			'missingIndexes'   => array('news_datestamp' => $missingIndex),
			'modifiedIndexes'  => array('PRIMARY' => $modifiedIndex),
			'extraIndexes'     => array('news_plugin_idx' => $extraIndex),
			'redundantIndexes' => array('ft_news_news_title' => $redundantIndex),
		));

		$this->assertSame('news', $diff->getTableName());
		$this->assertSame('core', $diff->getSqlFile());
		$this->assertFalse($diff->isMissing());
		$this->assertSame($expectedTable, $diff->getExpectedTable());
		$this->assertSame(array('expected' => 'InnoDB', 'actual' => 'MyISAM'), $diff->getEngineChange());
		$this->assertSame(array('expected' => 'utf8mb4', 'actual' => 'utf8mb3'), $diff->getCharsetChange());
		$this->assertSame(array('news_thumbnail' => $missingColumn), $diff->getMissingColumns());
		$this->assertSame(array('news_id' => $modifiedColumn), $diff->getModifiedColumns());
		$this->assertSame(array('news_plugin_field' => $extraColumn), $diff->getExtraColumns());
		$this->assertSame(array('news_datestamp' => $missingIndex), $diff->getMissingIndexes());
		$this->assertSame(array('PRIMARY' => $modifiedIndex), $diff->getModifiedIndexes());
		$this->assertSame(array('news_plugin_idx' => $extraIndex), $diff->getExtraIndexes());
		$this->assertSame(array('ft_news_news_title' => $redundantIndex), $diff->getRedundantIndexes());
	}

	public function testATableDiffWithNoPartsIsClean()
	{
		$diff = new TableDiff('core', 'news');

		$this->assertFalse($diff->isMissing());
		$this->assertNull($diff->getExpectedTable());
		$this->assertNull($diff->getEngineChange());
		$this->assertNull($diff->getCharsetChange());
		$this->assertSame(array(), $diff->getMissingColumns());
		$this->assertSame(array(), $diff->getModifiedColumns());
		$this->assertSame(array(), $diff->getExtraColumns());
		$this->assertSame(array(), $diff->getMissingIndexes());
		$this->assertSame(array(), $diff->getModifiedIndexes());
		$this->assertSame(array(), $diff->getExtraIndexes());
		$this->assertSame(array(), $diff->getRedundantIndexes());
		$this->assertFalse($diff->hasDrift());
	}

	public function testMissingTableCarriesTheExpectedShapeForACreate()
	{
		$expectedTable = $this->fake('table');
		$diff = TableDiff::missingTable('core', 'news', $expectedTable);

		$this->assertTrue($diff->isMissing());
		$this->assertTrue($diff->hasDrift());
		$this->assertSame($expectedTable, $diff->getExpectedTable());
		$this->assertSame('news', $diff->getTableName());
	}

	public function testWithPartsReturnsACloneAndLeavesTheOriginalAlone()
	{
		$diff = new TableDiff('core', 'news');
		$grown = $diff->withParts(array('missingColumns' => array('news_thumbnail' => $this->fake('column'))));

		$this->assertNotSame($diff, $grown);
		$this->assertSame(array(), $diff->getMissingColumns());
		$this->assertFalse($diff->hasDrift());
		$this->assertCount(1, $grown->getMissingColumns());
		$this->assertTrue($grown->hasDrift());
		$this->assertSame('news', $grown->getTableName());
	}

	public function testAnUnknownPartThrowsRatherThanBeingIgnored()
	{
		$this->assertThrows('InvalidArgumentException', function ()
		{
			new TableDiff('core', 'news', array('missingFields' => array()));
		});
	}

	public function testAMalformedEngineChangeThrows()
	{
		$this->assertThrows('InvalidArgumentException', function ()
		{
			new TableDiff('core', 'news', array('engineChange' => 'InnoDB'));
		});

		$this->assertThrows('InvalidArgumentException', function ()
		{
			new TableDiff('core', 'news', array('charsetChange' => array('expected' => 'utf8mb4')));
		});

		$this->assertThrows('InvalidArgumentException', function ()
		{
			new TableDiff('core', 'news', array('missingColumns' => 'news_thumbnail'));
		});

		$this->assertThrows('InvalidArgumentException', function ()
		{
			new TableDiff('core', '');
		});
	}

	// --- TableDiff::hasDrift() ------------------------------------------

	public function testExtraColumnsAndIndexesAreNotDrift()
	{
		$diff = new TableDiff('core', 'news', array(
			'extraColumns' => array('news_plugin_field' => $this->fake('column')),
			'extraIndexes' => array('news_plugin_idx' => $this->fake('index')),
		));

		$this->assertCount(1, $diff->getExtraColumns());
		$this->assertCount(1, $diff->getExtraIndexes());
		$this->assertFalse($diff->hasDrift());
	}

	/**
	 * A redundant index is one e107 derived that the declaration now covers, so dropping it is the fix.
	 */
	public function testARedundantIndexIsDriftWhereTheSameExtraOneIsNot()
	{
		$index = $this->fake('index');

		$redundant = new TableDiff('core', 'news', array('redundantIndexes' => array('ft_news_news_title' => $index)));
		$extra = new TableDiff('core', 'news', array('extraIndexes' => array('ft_news_news_title' => $index)));

		$this->assertTrue($redundant->hasDrift());
		$this->assertFalse($extra->hasDrift());
	}

	public function testEveryOtherDifferenceIsDrift()
	{
		$drifting = array(
			'missing'          => true,
			'engineChange'     => array('expected' => 'InnoDB', 'actual' => 'MyISAM'),
			'charsetChange'    => array('expected' => 'utf8mb4', 'actual' => 'utf8mb3'),
			'missingColumns'   => array($this->fake('column')),
			'modifiedColumns'  => array($this->fake('columnDiff')),
			'missingIndexes'   => array($this->fake('index')),
			'modifiedIndexes'  => array($this->fake('indexDiff')),
			'redundantIndexes' => array($this->fake('index')),
		);

		foreach($drifting as $part => $value)
		{
			$diff = new TableDiff('core', 'news', array($part => $value));
			$this->assertTrue($diff->hasDrift(), 'A "'.$part.'" difference must count as drift.');
		}
	}

	public function testExtrasDoNotMaskRealDrift()
	{
		$diff = new TableDiff('core', 'news', array(
			'missingColumns' => array($this->fake('column')),
			'extraColumns'   => array($this->fake('column')),
			'extraIndexes'   => array($this->fake('index')),
		));

		$this->assertTrue($diff->hasDrift());
	}

	// --- FixPlan ---------------------------------------------------------

	public function testAnEmptyPlanRendersToZeroStatements()
	{
		$plan = new FixPlan();

		$this->assertTrue($plan->isEmpty());
		$this->assertSame(0, $plan->count());
		$this->assertSame(array(), $plan->getChanges());
		$this->assertSame(array(), $plan->getTables());
		$this->assertSame(array(), $plan->toSqlStatements($this->makeSchema()));
	}

	public function testPlanKeepsTheOrderItWasBuiltIn()
	{
		$first = $this->change('news', 'ALTER TABLE `e107_news` ENGINE = InnoDB');
		$second = $this->change('news', 'ALTER TABLE `e107_news` ADD COLUMN `news_thumbnail` TEXT NOT NULL');
		$third = $this->change('news', 'ALTER TABLE `e107_news` ADD INDEX `news_datestamp` (`news_datestamp`)');

		$plan = new FixPlan(array($first, $second, $third));

		$this->assertFalse($plan->isEmpty());
		$this->assertSame(3, $plan->count());
		$this->assertSame(array($first, $second, $third), $plan->getChanges());
		$this->assertSame(
			array(
				'ALTER TABLE `e107_news` ENGINE = InnoDB',
				'ALTER TABLE `e107_news` ADD COLUMN `news_thumbnail` TEXT NOT NULL',
				'ALTER TABLE `e107_news` ADD INDEX `news_datestamp` (`news_datestamp`)',
			),
			$plan->toSqlStatements($this->makeSchema())
		);
	}

	public function testPlanIsASequenceNotAMap()
	{
		$first = $this->change('news', 'ALTER TABLE `e107_news` ENGINE = InnoDB');
		$second = $this->change('page', 'ALTER TABLE `e107_page` ENGINE = InnoDB');

		$plan = new FixPlan(array('news' => $first, 'page' => $second));

		$this->assertSame(array(0, 1), array_keys($plan->getChanges()));
	}

	public function testForTableFiltersAndKeepsOrder()
	{
		$newsFirst = $this->change('news', 'ALTER TABLE `e107_news` ENGINE = InnoDB');
		$page = $this->change('page', 'ALTER TABLE `e107_page` ENGINE = InnoDB');
		$newsSecond = $this->change('news', 'ALTER TABLE `e107_news` ADD INDEX `news_datestamp` (`news_datestamp`)');

		$plan = new FixPlan(array($newsFirst, $page, $newsSecond));
		$forNews = $plan->forTable('news');

		$this->assertInstanceOf('e107\\Database\\Schema\\Plan\\FixPlan', $forNews);
		$this->assertSame(array($newsFirst, $newsSecond), $forNews->getChanges());
		$this->assertSame(array($page), $plan->forTable('page')->getChanges());
		$this->assertTrue($plan->forTable('forum_thread')->isEmpty());
		$this->assertSame(3, $plan->count());
		$this->assertSame(array('news', 'page'), $plan->getTables());
	}

	public function testWithChangeAndMergeDoNotMutateTheOriginal()
	{
		$first = $this->change('news', 'ALTER TABLE `e107_news` ENGINE = InnoDB');
		$second = $this->change('page', 'ALTER TABLE `e107_page` ENGINE = InnoDB');

		$plan = new FixPlan(array($first));
		$grown = $plan->withChange($second);
		$merged = $plan->merge(new FixPlan(array($second, $first)));

		$this->assertSame(1, $plan->count());
		$this->assertSame(array($first, $second), $grown->getChanges());
		$this->assertSame(array($first, $second, $first), $merged->getChanges());
	}

	public function testAChangeMayRenderMoreThanOneStatement()
	{
		$convert = $this->change('news', array(
			'ALTER TABLE `e107_news` ENGINE = InnoDB',
			'ALTER TABLE `e107_news` CONVERT TO CHARACTER SET utf8mb4',
		));

		$plan = new FixPlan(array($convert));

		$this->assertSame(
			array(
				'ALTER TABLE `e107_news` ENGINE = InnoDB',
				'ALTER TABLE `e107_news` CONVERT TO CHARACTER SET utf8mb4',
			),
			$plan->toSqlStatements($this->makeSchema())
		);
	}

	public function testAnEmptyRenderIsReportedRatherThanExecuted()
	{
		$schema = $this->makeSchema();

		$emptyString = new FixPlan(array($this->change('news', '')));
		$this->assertThrows('UnexpectedValueException', function () use ($emptyString, $schema)
		{
			$emptyString->toSqlStatements($schema);
		});

		$whitespace = new FixPlan(array($this->change('news', "  \n ")));
		$this->assertThrows('UnexpectedValueException', function () use ($whitespace, $schema)
		{
			$whitespace->toSqlStatements($schema);
		});

		$nothing = new FixPlan(array($this->change('news', array())));
		$this->assertThrows('UnexpectedValueException', function () use ($nothing, $schema)
		{
			$nothing->toSqlStatements($schema);
		});
	}

	public function testAPlanHoldsChangesOnly()
	{
		$this->assertThrows('InvalidArgumentException', function ()
		{
			new FixPlan(array('ALTER TABLE `e107_news` ENGINE = InnoDB'));
		});
	}

	// --- helpers ---------------------------------------------------------

	/**
	 * @param string $table
	 * @param string|array $sql
	 * @return SchemaDiffValueObjectTest_change
	 */
	private function change($table, $sql)
	{
		return new SchemaDiffValueObjectTest_change($table, $sql);
	}

	/**
	 * A stand-in for an Introspect value object; these tests only ever assert identity.
	 *
	 * @param string $what
	 * @return stdClass
	 */
	private function fake($what)
	{
		$fake = new stdClass();
		$fake->what = $what;

		return $fake;
	}

	/**
	 * @return SchemaBuilder
	 */
	private function makeSchema()
	{
		return new SchemaBuilder(new SchemaDiffValueObjectTest_dbStub(), new MysqlPlatform());
	}

	/**
	 * @param string $class
	 * @param callable $callback
	 */
	private function assertThrows($class, $callback)
	{
		try
		{
			call_user_func($callback);
		}
		catch(Exception $e)
		{
			$this->assertInstanceOf($class, $e);

			return;
		}

		$this->fail('Expected '.$class.' was not thrown.');
	}
}

/**
 * A change that renders whatever it was handed.
 */
class SchemaDiffValueObjectTest_change implements ChangeInterface
{
	private $table;
	private $sql;

	/**
	 * @param string $table
	 * @param string|array $sql
	 */
	public function __construct($table, $sql)
	{
		$this->table = $table;
		$this->sql = $sql;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getSqlFile()
	{
		return 'core';
	}

	public function describe()
	{
		return 'Test change on '.$this->table;
	}

	public function toSql(SchemaBuilder $schema)
	{
		return $this->sql;
	}
}

/**
 * The least connection {@see SchemaBuilder} will accept; no test renders DDL through it.
 */
class SchemaDiffValueObjectTest_dbStub
{
	public function getPlatform()
	{
		return new MysqlPlatform();
	}

	public function execute($sql, $params = array())
	{
		return 1;
	}
}
