<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\IdentifierFilter;
use e107\Database\Platform\MysqlPlatform;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexPart;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Plan\Change\AddColumn;
use e107\Database\Schema\Plan\Change\AddIndex;
use e107\Database\Schema\Plan\Change\ConvertTable;
use e107\Database\Schema\Plan\Change\DropIndex;
use e107\Database\Schema\Plan\FixPlan;
use e107\Database\Schema\SchemaBuilder;

/**
 * {@see FixPlan}, the container the whole repair path passes through: it keeps its changes in the order they were
 * planned in, and refuses to hand on one that renders nothing.
 *
 * Nothing here touches the database; the changes are rendered against a stub connection.
 */
class FixPlanTest extends \Test\Unit
{
	// --- autoloading ------------------------------------------------------

	public function testTheClassAutoloadsFromItsNamespacePath()
	{
		$this->assertTrue(
			class_exists('e107\\Database\\Schema\\Plan\\FixPlan'),
			'FixPlan must autoload from e107_handlers/Database/Schema/Plan/FixPlan.php'
		);
	}

	// --- construction -----------------------------------------------------

	public function testAnEmptyPlanIsEmptyAndRendersNothing()
	{
		$plan = new FixPlan();

		$this->assertTrue($plan->isEmpty());
		$this->assertSame(0, $plan->count());
		$this->assertSame(array(), $plan->getChanges());
		$this->assertSame(array(), $plan->getTables());
		$this->assertSame(array(), $plan->toSqlStatements($this->schema()));
	}

	public function testCallerKeysAreDroppedSoAPlanStaysASequence()
	{
		$plan = new FixPlan(array(7 => $this->addIndex('news', 'news_sticky'), 3 => $this->addIndex('news', 'news_author')));

		$this->assertSame(array(0, 1), array_keys($plan->getChanges()));
		$this->assertSame('news_sticky', $plan->getChanges()[0]->getIndex()->getName());
	}

	public function testAnythingThatIsNotAChangeIsRefused()
	{
		$this->assertThrows('InvalidArgumentException', function ()
		{
			new FixPlan(array('ALTER TABLE `e107_news` DROP INDEX `news_sticky`'));
		});
	}

	public function testTheRefusalNamesWhatItWasGiven()
	{
		try
		{
			new FixPlan(array(new stdClass()));
			$this->fail('A plan must refuse an object that is not a ChangeInterface.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertStringContainsString('stdClass', $e->getMessage());
		}
	}

	// --- order ------------------------------------------------------------

	public function testEveryOperationPreservesTheOrderOfTheChanges()
	{
		$drop = $this->dropIndex('news', 'news_sticky');
		$add = $this->addColumn('news', 'news_class');
		$key = $this->addIndex('news', 'news_sticky');

		$plan = new FixPlan(array($drop, $add, $key));

		$this->assertSame(array($drop, $add, $key), $plan->getChanges());
		$this->assertSame(array($drop, $add, $key), $plan->forTable('news')->getChanges());
		$this->assertSame(array($drop, $add, $key), $plan->exceptTables(array('user'))->getChanges());
		$this->assertSame(array($drop, $add, $key), (new FixPlan())->merge($plan)->getChanges());
	}

	public function testMergeAppendsTheOtherPlanAfterThisOne()
	{
		$first = $this->convert('news');
		$second = $this->convert('user');

		$merged = (new FixPlan(array($first)))->merge(new FixPlan(array($second)));

		$this->assertSame(array($first, $second), $merged->getChanges());
	}

	public function testMergeLeavesBothOperandsAlone()
	{
		$mine = new FixPlan(array($this->convert('news')));
		$theirs = new FixPlan(array($this->convert('user')));

		$mine->merge($theirs);

		$this->assertSame(1, $mine->count(), 'A plan is immutable; merge() returns a new one.');
		$this->assertSame(1, $theirs->count());
	}

	public function testWithChangeAppendsWithoutTouchingTheOriginal()
	{
		$plan = new FixPlan(array($this->convert('news')));
		$grown = $plan->withChange($this->convert('user'));

		$this->assertSame(1, $plan->count());
		$this->assertSame(2, $grown->count());
		$this->assertSame(array('news', 'user'), $grown->getTables());
	}

	// --- selecting by table -----------------------------------------------

	public function testForTableKeepsOnlyThatTablesChanges()
	{
		$plan = new FixPlan(array($this->convert('news'), $this->convert('user'), $this->addColumn('news', 'news_class')));

		$this->assertSame(2, $plan->forTable('news')->count());
		$this->assertSame(array('news'), $plan->forTable('news')->getTables());
		$this->assertTrue($plan->forTable('links')->isEmpty());
	}

	public function testTableNamesAreMatchedExactly()
	{
		$plan = new FixPlan(array($this->convert('news'), $this->convert('news_category')));

		$this->assertSame(array('news'), $plan->forTable('news')->getTables());
		$this->assertSame(array('news_category'), $plan->exceptTables(array('news'))->getTables());
	}

	public function testGetTablesListsEachTableOnceInFirstChangeOrder()
	{
		$plan = new FixPlan(array(
			$this->convert('user'),
			$this->convert('news'),
			$this->addColumn('user', 'user_sess'),
		));

		$this->assertSame(array('user', 'news'), $plan->getTables());
	}

	public function testExceptTablesRemovesEveryChangeForTheNamedTables()
	{
		$plan = new FixPlan(array(
			$this->convert('news'),
			$this->addColumn('news', 'news_class'),
			$this->convert('user'),
		));

		$remaining = $plan->exceptTables(array('news'));

		$this->assertSame(1, $remaining->count());
		$this->assertSame(array('user'), $remaining->getTables());
		$this->assertSame(3, $plan->count(), 'The original plan is untouched.');
	}

	public function testExceptTablesWithNoNamesKeepsEverything()
	{
		$plan = new FixPlan(array($this->convert('news'), $this->convert('user')));

		$this->assertSame(2, $plan->exceptTables(array())->count());
	}

	public function testExceptingEveryTableLeavesAnEmptyPlan()
	{
		$plan = new FixPlan(array($this->convert('news'), $this->convert('user')));

		$this->assertTrue($plan->exceptTables(array('news', 'user'))->isEmpty());
	}

	// --- rendering: the #5905 guarantee -----------------------------------

	public function testEveryChangeRendersAtLeastOneNonEmptyStatement()
	{
		$plan = new FixPlan(array(
			$this->convert('news'),
			$this->addColumn('news', 'news_class'),
			$this->dropIndex('news', 'news_sticky'),
			$this->addIndex('news', 'news_sticky'),
		));

		$statements = $plan->toSqlStatements($this->schema());

		$this->assertGreaterThanOrEqual(4, count($statements), 'A ConvertTable renders two statements; the rest one each.');

		foreach($statements as $sql)
		{
			$this->assertIsString($sql);
			$this->assertNotSame('', trim($sql));
			$this->assertRegExp('/^\s*(ALTER|CREATE|DROP)\b/i', $sql, 'Rendered: '.$sql);
		}
	}

	public function testOneChangeMayRenderSeveralStatementsAndAllOfThemSurvive()
	{
		$statements = (new FixPlan(array($this->convert('news'))))->toSqlStatements($this->schema());

		$this->assertCount(2, $statements, 'ConvertTable states the engine and the character set separately.');
		$this->assertStringContainsString('ENGINE', $statements[0]);
		$this->assertStringContainsString('InnoDB', $statements[0]);
		$this->assertStringContainsString('CONVERT TO CHARACTER SET', $statements[1]);
		$this->assertStringContainsString('utf8mb4', $statements[1]);
	}

	public function testAChangeThatRendersAnEmptyStringIsReportedRatherThanPassedOn()
	{
		$this->assertThrows('UnexpectedValueException', function ()
		{
			(new FixPlan(array($this->brokenChange(''))))->toSqlStatements($this->schema());
		});
	}

	public function testAChangeThatRendersNoStatementAtAllIsReportedToo()
	{
		$this->assertThrows('UnexpectedValueException', function ()
		{
			(new FixPlan(array($this->brokenChange(array()))))->toSqlStatements($this->schema());
		});
	}

	public function testAChangeThatRendersWhitespaceIsReportedToo()
	{
		$this->assertThrows('UnexpectedValueException', function ()
		{
			(new FixPlan(array($this->brokenChange("  \n\t"))))->toSqlStatements($this->schema());
		});
	}

	public function testAChangeThatRendersSomethingThatIsNotAStringIsReportedToo()
	{
		$this->assertThrows('UnexpectedValueException', function ()
		{
			(new FixPlan(array($this->brokenChange(null))))->toSqlStatements($this->schema());
		});
	}

	public function testTheRefusalNamesTheChangeAndItsTable()
	{
		try
		{
			(new FixPlan(array($this->brokenChange(''))))->toSqlStatements($this->schema());
			$this->fail('An empty statement must be refused.');
		}
		catch(UnexpectedValueException $e)
		{
			$this->assertStringContainsString('news', $e->getMessage());
			$this->assertStringContainsString('a broken change', $e->getMessage(), "The change's own describe() is quoted.");
		}
	}

	public function testARefusalDiscardsTheStatementsRenderedBeforeIt()
	{
		$plan = new FixPlan(array($this->convert('news'), $this->brokenChange('')));

		try
		{
			$plan->toSqlStatements($this->schema());
			$this->fail('An empty statement must be refused.');
		}
		catch(UnexpectedValueException $e)
		{
			$this->assertSame(2, count((new FixPlan(array($this->convert('news'))))->toSqlStatements($this->schema())));
		}
	}

	// --- helpers ----------------------------------------------------------

	/**
	 * @param string $table
	 * @return ConvertTable
	 */
	private function convert($table)
	{
		return new ConvertTable('core', $table, 'InnoDB', 'utf8mb4');
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @return AddColumn
	 */
	private function addColumn($table, $column)
	{
		$schema = new ColumnSchema(
			$column, 'varchar(255)', false, '', '', null, null, '', 1,
			'`'.$column.'` varchar(255) NOT NULL DEFAULT \'\''
		);

		return new AddColumn('core', $table, $schema, null);
	}

	/**
	 * @param string $table
	 * @param string $index
	 * @return AddIndex
	 */
	private function addIndex($table, $index)
	{
		return new AddIndex('core', $table, $this->index($index, 'KEY `'.$index.'` (`'.$index.'`)'));
	}

	/**
	 * @param string $table
	 * @param string $index
	 * @return DropIndex
	 */
	private function dropIndex($table, $index)
	{
		return new DropIndex('core', $table, $this->index($index, null));
	}

	/**
	 * @param string $name
	 * @param string|null $ddl
	 * @return IndexSchema
	 */
	private function index($name, $ddl)
	{
		return new IndexSchema($name, IndexSchema::KIND_INDEX, array(new IndexPart($name, null, 'A')), $ddl);
	}

	/**
	 * A change that renders whatever it is handed. It is a mock rather than a class declared here because Codeception
	 * parses a test file before e107's namespaced autoloader is registered, so implementing ChangeInterface here is fatal.
	 *
	 * @param mixed $rendered what toSql() is to hand back.
	 * @return \PHPUnit\Framework\MockObject\MockObject|\e107\Database\Schema\Plan\ChangeInterface
	 */
	private function brokenChange($rendered)
	{
		$change = $this->createMock('e107\\Database\\Schema\\Plan\\ChangeInterface');

		$change->expects($this->any())->method('getTable')->willReturn('news');
		$change->expects($this->any())->method('getSqlFile')->willReturn('core');
		$change->expects($this->any())->method('describe')->willReturn('a broken change');
		$change->expects($this->any())->method('toSql')->willReturn($rendered);

		return $change;
	}

	/**
	 * @return SchemaBuilder bound to a stub connection: nothing here executes.
	 */
	private function schema()
	{
		return new SchemaBuilder(new FixPlanTest_dbStub(), new MysqlPlatform());
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
 * The least connection {@see SchemaBuilder} will accept: it resolves and quotes identifiers as the real one does,
 * through the same central grammar, and executes nothing.
 */
class FixPlanTest_dbStub
{
	public function getPlatform()
	{
		return new MysqlPlatform();
	}

	public function resolvePhysicalTableName($table)
	{
		if(!preg_match('/^[A-Za-z0-9_]+$/D', (string) $table))
		{
			return false;
		}

		return 'e107_'.$table;
	}

	public function quoteIdentifier($identifier)
	{
		return IdentifierFilter::identifier($identifier);
	}

	public function execute($sql, $params = array())
	{
		return 1;
	}
}
