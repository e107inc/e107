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
use e107\Database\Schema\Declared\DeclaredTable;
use e107\Database\Schema\Declared\Materialiser;
use e107\Database\Schema\Diff\ColumnDiff;
use e107\Database\Schema\Diff\IndexDiff;
use e107\Database\Schema\Diff\SchemaDiffer;
use e107\Database\Schema\Diff\TableDiff;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexPart;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\Column;
use e107\Database\Schema\Plan\Change\AddColumn;
use e107\Database\Schema\Plan\Change\AddIndex;
use e107\Database\Schema\Plan\Change\ConvertTable;
use e107\Database\Schema\Plan\Change\CreateTable;
use e107\Database\Schema\Plan\Change\DropIndex;
use e107\Database\Schema\Plan\Change\ModifyColumn;
use e107\Database\Schema\Plan\PlanBuilder;
use e107\Database\Schema\SchemaBuilder;

/**
 * Tests for the Plan layer: the six {@see e107\Database\Schema\Plan\ChangeInterface}
 * changes and {@see PlanBuilder}, which puts them in the only order that applies
 * cleanly.
 */
class PlanBuilderTest extends \Test\Unit
{
	// --- autoloading ----------------------------------------------------

	public function testEveryPlanClassAutoloadsFromItsNamespacePath()
	{
		$classes = array(
			'e107\\Database\\Schema\\Plan\\PlanBuilder',
			'e107\\Database\\Schema\\Plan\\Change\\AbstractChange',
			'e107\\Database\\Schema\\Plan\\Change\\AddColumn',
			'e107\\Database\\Schema\\Plan\\Change\\AddIndex',
			'e107\\Database\\Schema\\Plan\\Change\\ConvertTable',
			'e107\\Database\\Schema\\Plan\\Change\\CreateTable',
			'e107\\Database\\Schema\\Plan\\Change\\DropIndex',
			'e107\\Database\\Schema\\Plan\\Change\\ModifyColumn',
		);

		foreach($classes as $class)
		{
			$this->assertTrue(class_exists($class), $class.' must autoload from e107_handlers/'.str_replace('\\', '/', substr($class, 5)).'.php');
		}
	}

	public function testThePlannerCarriesNoDdlRenderer()
	{
		$this->assertFalse(class_exists('e107\\Database\\Schema\\Plan\\ColumnDdl'), 'The planner must render no column DDL of its own.');
		$this->assertFalse(class_exists('e107\\Database\\Schema\\Plan\\IndexDdl'), 'The planner must render no key DDL of its own.');
	}

	// --- splicing a captured column -------------------------------------

	public function testAnAddedColumnSplicesTheServersOwnDefinition()
	{
		$captured = array(
			"`news_title` varchar(255) NOT NULL DEFAULT ''",
			'`news_id` int(10) unsigned NOT NULL AUTO_INCREMENT',
			'`news_id` int unsigned NOT NULL AUTO_INCREMENT',
			'`news_body` text DEFAULT NULL',
			'`news_datestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()',
			'`news_datestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
			"`news_flag` enum('Yes','No') NOT NULL DEFAULT 'Yes'",
			"`news_note` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'a, comma'",
		);

		foreach($captured as $ddl)
		{
			$this->assertSame(
				'ALTER TABLE `e107_news` ADD '.$ddl,
				$this->render(new AddColumn('core', 'news', $this->col('news_x', 'varchar(255)', $ddl)))
			);
		}
	}

	public function testAnAddedColumnCarriesItsPlacement()
	{
		$column = $this->col('news_thumbnail', 'varchar(255)', "`news_thumbnail` varchar(255) NOT NULL DEFAULT ''");

		$this->assertSame(
			'ALTER TABLE `e107_news` ADD `news_thumbnail` varchar(255) NOT NULL DEFAULT \'\' AFTER `news_id`',
			$this->render(new AddColumn('core', 'news', $column, 'news_id'))
		);

		$this->assertSame(
			'ALTER TABLE `e107_news` ADD `news_thumbnail` varchar(255) NOT NULL DEFAULT \'\' FIRST',
			$this->render(new AddColumn('core', 'news', $column, SchemaBuilder::FIRST))
		);
	}

	public function testAnUnspellablePlacementAnchorIsRefused()
	{
		$schema = $this->schema();
		$column = $this->col('news_thumbnail', 'varchar(255)', '`news_thumbnail` varchar(255) NOT NULL');

		$this->assertThrows('InvalidArgumentException', function () use ($schema, $column)
		{
			$change = new AddColumn('core', 'news', $column, 'news_id`; DROP TABLE x');
			$change->toSql($schema);
		});
	}

	public function testAModifiedColumnSplicesTheServersOwnDefinitionAndNoPlacement()
	{
		$this->assertSame(
			'ALTER TABLE `e107_news` MODIFY `news_title` varchar(255) NOT NULL DEFAULT \'\'',
			$this->render(new ModifyColumn('core', 'news', $this->col('news_title', 'varchar(255)', "`news_title` varchar(255) NOT NULL DEFAULT ''", 4)))
		);
	}

	// --- splicing a captured key ----------------------------------------

	public function testAnAddedIndexSplicesTheServersOwnKeyClause()
	{
		$captured = array(
			'PRIMARY KEY (`news_id`)',
			'UNIQUE KEY `u_bc` (`b`(10),`c`)',
			'KEY `k_c` (`c` DESC)',
			'FULLTEXT KEY `ft_d` (`d`)',
			'SPATIAL KEY `sp_g` (`g`)',
		);

		foreach($captured as $ddl)
		{
			$this->assertSame(
				'ALTER TABLE `e107_news` ADD '.$ddl,
				$this->render(new AddIndex('core', 'news', $this->idx('k_x', IndexSchema::KIND_INDEX, array('c'), $ddl)))
			);
		}
	}

	public function testDroppingAnIndexSpellsThePrimaryKeyDifferently()
	{
		$this->assertSame(
			'ALTER TABLE `e107_news` DROP INDEX `k_c`',
			$this->render(new DropIndex('core', 'news', $this->idx('k_c', IndexSchema::KIND_INDEX, array('c'))))
		);

		$this->assertSame(
			'ALTER TABLE `e107_news` DROP PRIMARY KEY',
			$this->render(new DropIndex('core', 'news', $this->idx('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id'))))
		);
	}

	// --- whole-table changes --------------------------------------------

	public function testConvertTableRendersTheEngineBeforeTheCharacterSet()
	{
		$change = new ConvertTable('core', 'news', 'InnoDB', 'utf8mb4');

		$this->assertSame(
			array(
				'ALTER TABLE `e107_news` ENGINE = InnoDB',
				'ALTER TABLE `e107_news` CONVERT TO CHARACTER SET utf8mb4',
			),
			$change->toSql($this->schema())
		);

		$this->assertSame('Convert table `news` to engine InnoDB and character set utf8mb4', $change->describe());
	}

	public function testConvertTableRendersOnlyTheHalfThatDrifted()
	{
		$this->assertSame(
			array('ALTER TABLE `e107_news` ENGINE = InnoDB'),
			(new ConvertTable('core', 'news', 'InnoDB'))->toSql($this->schema())
		);

		$this->assertSame(
			array('ALTER TABLE `e107_news` CONVERT TO CHARACTER SET utf8mb4'),
			(new ConvertTable('core', 'news', null, 'utf8mb4'))->toSql($this->schema())
		);
	}

	public function testCreateTableReEmitsTheStatementTheServerAlreadyAccepted()
	{
		$change = new CreateTable('core', 'news', $this->newsTable(array('news_id', 'news_title')));

		$this->assertSame(
			"CREATE TABLE `e107_news` (\n"
			."  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n"
			."  `news_title` varchar(255) NOT NULL DEFAULT '',\n"
			."  PRIMARY KEY (`news_id`)\n"
			.') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
			$change->toSql($this->schema())
		);
	}

	// --- ordering --------------------------------------------------------

	public function testThePlanIsOrderedConvertDropIndexAddColumnModifyColumnAddIndex()
	{
		$plan = $this->planner()->build($this->driftedDiff(), 'InnoDB', 'utf8mb4');

		$described = array();

		foreach($plan->getChanges() as $change)
		{
			$described[] = get_class($change).': '.$change->describe();
		}

		$this->assertSame(
			array(
				'e107\\Database\\Schema\\Plan\\Change\\ConvertTable: Convert table `news` to engine InnoDB and character set utf8mb4',
				'e107\\Database\\Schema\\Plan\\Change\\DropIndex: Drop index `k_title`',
				'e107\\Database\\Schema\\Plan\\Change\\AddColumn: Add column `news_thumbnail`',
				'e107\\Database\\Schema\\Plan\\Change\\AddColumn: Add column `news_summary`',
				'e107\\Database\\Schema\\Plan\\Change\\ModifyColumn: Modify column `news_title`',
				'e107\\Database\\Schema\\Plan\\Change\\AddIndex: Add index `u_summary`',
				'e107\\Database\\Schema\\Plan\\Change\\AddIndex: Add index `k_title`',
			),
			$described
		);

		$this->assertSame(7, $plan->count());
		$this->assertSame(array('news'), $plan->getTables());
		$this->assertSame(7 + 1, count($plan->toSqlStatements($this->schema())), 'ConvertTable renders two statements, every other change one.');
	}

	/**
	 * Missing columns are added in declared ordinal order so that each one's
	 * AFTER anchor exists by the time it is named, even in a run of
	 * consecutive missing columns.
	 */
	public function testMissingColumnsAreAddedInDeclaredOrderEachAfterItsPredecessor()
	{
		$plan = $this->planner()->build($this->driftedDiff(), 'InnoDB', 'utf8mb4');
		$changes = $plan->getChanges();

		$this->assertSame('news_id', $changes[2]->getAfter());
		$this->assertSame('news_thumbnail', $changes[3]->getAfter());
	}

	public function testAMissingFirstColumnIsPlacedFirst()
	{
		$expected = $this->newsTable(array('news_id', 'news_title'));

		$diff = new TableDiff('core', 'news', array(
			'expectedTable'  => $expected,
			'missingColumns' => array('news_id' => $expected->getColumn('news_id')),
		));

		$changes = $this->planner()->build($diff, 'InnoDB', 'utf8mb4')->getChanges();

		$this->assertSame(SchemaBuilder::FIRST, $changes[0]->getAfter());
		$this->assertSame(
			'ALTER TABLE `e107_news` ADD `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST',
			$changes[0]->toSql($this->schema())
		);
	}

	public function testAMissingTablePlansACreateTableAndNothingElse()
	{
		$expected = $this->newsTable(array('news_id', 'news_title'));
		$plan = $this->planner()->build(TableDiff::missingTable('core', 'news', $expected), 'InnoDB', 'utf8mb4');

		$this->assertSame(1, $plan->count());
		$this->assertInstanceOf('e107\\Database\\Schema\\Plan\\Change\\CreateTable', $plan->getChanges()[0]);
		$this->assertSame('core', $plan->getChanges()[0]->getSqlFile());
	}

	// --- the widening a character set conversion causes (#4501) -----------

	public function testAConvertedCharacterSetAlsoRestoresTheTypesItWidens()
	{
		$expected = $this->newsTable();

		$diff = new TableDiff('core', 'news', array(
			'expectedTable' => $expected,
			'charsetChange' => array('expected' => 'utf8mb4', 'actual' => 'utf8mb3'),
		));

		$changes = $this->planner()->build($diff, 'InnoDB', 'utf8mb4')->getChanges();

		$this->assertSame(2, count($changes));
		$this->assertInstanceOf('e107\\Database\\Schema\\Plan\\Change\\ConvertTable', $changes[0]);
		$this->assertInstanceOf('e107\\Database\\Schema\\Plan\\Change\\ModifyColumn', $changes[1], 'The restore must follow the conversion that widened the column, never precede it.');
		$this->assertSame('news_summary', $changes[1]->getColumn()->getName());
		$this->assertSame(
			'ALTER TABLE `e107_news` MODIFY `news_summary` text DEFAULT NULL',
			$changes[1]->toSql($this->schema())
		);
	}

	public function testTextColumnsAreRestoredOnlyAlongsideTheConversionThatWidenedThem()
	{
		$expected = $this->newsTable();

		$noConvert = new TableDiff('core', 'news', array('expectedTable' => $expected));
		$engineOnly = new TableDiff('core', 'news', array(
			'expectedTable' => $expected,
			'engineChange'  => array('expected' => 'InnoDB', 'actual' => 'MyISAM'),
		));

		$this->assertTrue($this->planner()->build($noConvert, 'InnoDB', 'utf8mb4')->isEmpty(), 'A table already on the target character set is never narrowed retroactively.');

		$changes = $this->planner()->build($engineOnly, 'InnoDB', 'utf8mb4')->getChanges();

		$this->assertSame(1, count($changes), 'An engine change alone widens nothing, so it restores nothing.');
		$this->assertInstanceOf('e107\\Database\\Schema\\Plan\\Change\\ConvertTable', $changes[0]);
	}

	public function testATextColumnThePlanAlreadyCoversIsNotRestoredAsWell()
	{
		$expected = $this->newsTable();

		$diff = new TableDiff('core', 'news', array(
			'expectedTable'   => $expected,
			'charsetChange'   => array('expected' => 'utf8mb4', 'actual' => 'utf8mb3'),
			'modifiedColumns' => array(
				'news_summary' => new ColumnDiff($expected->getColumn('news_summary'), $this->col('news_summary', 'mediumtext', null, 3)),
			),
		));

		$modified = array();

		foreach($this->planner()->build($diff, 'InnoDB', 'utf8mb4')->getChanges() as $change)
		{
			if($change instanceof ModifyColumn)
			{
				$modified[] = $change->getColumn()->getName();
			}
		}

		$this->assertSame(array('news_summary'), $modified);
	}

	// --- nothing to do ---------------------------------------------------

	public function testACleanDiffPlansNothingAndRendersNoStatements()
	{
		$diff = new TableDiff('core', 'news', array('expectedTable' => $this->newsTable(array('news_id', 'news_title'))));

		$plan = $this->planner()->build($diff, 'InnoDB', 'utf8mb4');

		$this->assertFalse($diff->hasDrift());
		$this->assertTrue($plan->isEmpty());
		$this->assertSame(0, $plan->count());
		$this->assertSame(array(), $plan->toSqlStatements($this->schema()));
	}

	public function testExtraColumnsAndIndexesAreNeverPlanned()
	{
		$diff = new TableDiff('core', 'news', array(
			'expectedTable' => $this->newsTable(array('news_id', 'news_title')),
			'extraColumns'  => array('news_custom' => $this->col('news_custom', 'varchar(10)')),
			'extraIndexes'  => array('k_custom' => $this->idx('k_custom', IndexSchema::KIND_INDEX, array('news_custom'))),
		));

		$this->assertTrue($this->planner()->build($diff, 'InnoDB', 'utf8mb4')->isEmpty());
	}

	// --- fail closed -----------------------------------------------------

	public function testAChangeWithNoCapturedDefinitionThrowsRatherThanRenderingAnything()
	{
		$schema = $this->schema();

		$this->assertThrows('RuntimeException', function () use ($schema)
		{
			$change = new AddColumn('core', 'news', $this->col('news_x', 'varchar(10)'));
			$change->toSql($schema);
		});

		$this->assertThrows('RuntimeException', function () use ($schema)
		{
			$change = new ModifyColumn('core', 'news', $this->col('news_x', 'varchar(10)'));
			$change->toSql($schema);
		});

		$this->assertThrows('RuntimeException', function () use ($schema)
		{
			$change = new AddIndex('core', 'news', $this->idx('k_x', IndexSchema::KIND_INDEX, array('news_x')));
			$change->toSql($schema);
		});

		$this->assertThrows('RuntimeException', function () use ($schema)
		{
			$live = new TableSchema('e107_news', 'InnoDB', 'utf8mb4', 'utf8mb4_general_ci', array($this->col('news_id', 'int(10) unsigned')), array());
			$change = new CreateTable('core', 'news', $live);
			$change->toSql($schema);
		});

		$this->assertThrows('RuntimeException', function () use ($schema)
		{
			$noOptions = new TableSchema(
				'e107_news', 'InnoDB', 'utf8mb4', 'utf8mb4_general_ci',
				array($this->col('news_id', 'int(10) unsigned', '`news_id` int(10) unsigned NOT NULL')),
				array(),
				'  `news_id` int(10) unsigned NOT NULL'
			);

			$change = new CreateTable('core', 'news', $noOptions);
			$change->toSql($schema);
		});
	}

	public function testTheRawColumnSeamsTakeAVouchedFragmentAndNothingElse()
	{
		$schema = $this->schema();

		$this->assertThrows('InvalidArgumentException', function () use ($schema)
		{
			$schema->tablePhysical('news')->addColumnRaw('`news_x` varchar(10) NOT NULL');
		});

		$this->assertThrows('InvalidArgumentException', function () use ($schema)
		{
			$schema->tablePhysical('news')->modifyColumnRaw(Column::define('varchar', 10));
		});
	}

	public function testAConvertWithNothingToConvertIsRefusedAtConstruction()
	{
		$this->assertThrows('InvalidArgumentException', function ()
		{
			new ConvertTable('core', 'news');
		});
	}

	public function testAnUnplannableDiffThrows()
	{
		$planner = $this->planner();

		$this->assertThrows('RuntimeException', function () use ($planner)
		{
			$planner->build(TableDiff::missingTable('core', 'news'), 'InnoDB', 'utf8mb4');
		});

		$this->assertThrows('RuntimeException', function () use ($planner)
		{
			$diff = new TableDiff('core', 'news', array(
				'expectedTable'  => $this->newsTable(array('news_id', 'news_title')),
				'missingColumns' => array('news_nowhere' => $this->col('news_nowhere', 'varchar(10)')),
			));

			$planner->build($diff, 'InnoDB', 'utf8mb4');
		});
	}

	// --- against the real server -----------------------------------------

	public function testABrokenTableIsRepairedByItsOwnPlanAndDiffsCleanAfterwards()
	{
		$db = e107::getDb();
		$table = 'dbvplanprobe';
		$physical = MPREFIX.$table;

		$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');
		$db->execute(
			'CREATE TABLE `'.$physical.'` ('
			.' probe_id int(10) unsigned NOT NULL auto_increment,'
			." probe_title varchar(100) NOT NULL default '',"
			.' probe_body text NOT NULL,'
			.' probe_tiny tinytext,'
			.' PRIMARY KEY (probe_id)'
			.') ENGINE=MyISAM DEFAULT CHARSET=utf8mb3'
		);

		try
		{
			$expected = $this->materialise($table, $this->probeBody(), 'InnoDB', 'utf8mb4');
			$actual = $this->live($db, $physical);

			$diff = (new SchemaDiffer())->diff('core', $expected, $actual, $table);

			$this->assertTrue($diff->hasDrift());

			$plan = $this->planner()->build($diff, 'InnoDB', 'utf8mb4');
			$schema = $db->schema();

			foreach($plan->getChanges() as $change)
			{
				foreach((array) $change->toSql($schema) as $sql)
				{
					$this->assertTrue($db->execute($sql) !== false, 'The server must accept every planned statement. '.$sql.' -> '.$db->getLastErrorText());
				}

				if($change instanceof ConvertTable)
				{
					$this->assertSame('mediumtext', $this->liveType($db, $physical, 'probe_body'), 'CONVERT TO CHARACTER SET widens a text column: this is #4501, and the reason the restore exists.');
					$this->assertSame('text', $this->liveType($db, $physical, 'probe_tiny'));
				}
			}

			$this->assertSame('text', $this->liveType($db, $physical, 'probe_body'), 'The declared type must be restored by the same plan that widened it.');
			$this->assertSame('tinytext', $this->liveType($db, $physical, 'probe_tiny'));

			$second = (new SchemaDiffer())->diff('core', $this->materialise($table, $this->probeBody(), 'InnoDB', 'utf8mb4'), $this->live($db, $physical), $table);

			$this->assertFalse($second->hasDrift(), 'A repaired table must diff clean: '.$this->summarise($second));
			$this->assertTrue($this->planner()->build($second, 'InnoDB', 'utf8mb4')->isEmpty());
		}
		catch(Exception $e)
		{
			$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');

			throw $e;
		}

		$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');
	}

	public function testACreateTableFromACapturedBodyBuildsATableThatDiffsClean()
	{
		$db = e107::getDb();
		$table = 'dbvplanprobe';
		$physical = MPREFIX.$table;

		$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');

		try
		{
			$expected = $this->materialise($table, $this->probeBody(), 'InnoDB', 'utf8mb4');
			$statement = (new CreateTable('core', $table, $expected))->toSql($db->schema());

			$this->assertStringContainsString("enum('Yes','No')", $statement, 'ENUM members keep their case: nothing lowercased the captured line.');
			$this->assertNotRegExp('/AUTO_INCREMENT\s*=\s*\d+/i', $statement, 'A sequence counter is a fact about rows, not a schema, and re-emitting it would set the new table\'s counter.');

			$this->assertTrue($db->execute($statement) !== false, 'The server must accept the statement it wrote itself. '.$statement.' -> '.$db->getLastErrorText());

			$diff = (new SchemaDiffer())->diff('core', $this->materialise($table, $this->probeBody(), 'InnoDB', 'utf8mb4'), $this->live($db, $physical), $table);

			$this->assertFalse($diff->hasDrift(), 'A table created from the declaration must diff clean against it: '.$this->summarise($diff));
		}
		catch(Exception $e)
		{
			$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');

			throw $e;
		}

		$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');
	}

	public function testApplyingEveryCapturedDefinitionToAMatchingTableChangesNothing()
	{
		$db = e107::getDb();
		$table = 'dbvplanprobe';
		$physical = MPREFIX.$table;

		$expected = $this->materialise($table, $this->probeBody(), 'InnoDB', 'utf8mb4');

		$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');

		try
		{
			$db->execute((new CreateTable('core', $table, $expected))->toSql($db->schema()));

			$before = $db->schema()->getCreateTablePhysical($table);

			foreach($expected->getColumns() as $column)
			{
				$sql = (new ModifyColumn('core', $table, $column))->toSql($db->schema());

				$this->assertTrue($db->execute($sql) !== false, 'A captured definition must apply unchanged. '.$sql.' -> '.$db->getLastErrorText());
			}

			$this->assertSame($before, $db->schema()->getCreateTablePhysical($table), 'Re-applying every captured definition must be a no-op, byte for byte.');
		}
		catch(Exception $e)
		{
			$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');

			throw $e;
		}

		$db->execute('DROP TABLE IF EXISTS `'.$physical.'`');
	}

	// --- helpers ---------------------------------------------------------

	/**
	 * @param string $name
	 * @param string $type COLUMN_TYPE.
	 * @param string|null $ddl The server's own definition line, or null for a live column.
	 * @param int $position
	 * @return ColumnSchema
	 */
	private function col($name, $type, $ddl = null, $position = 1)
	{
		return new ColumnSchema($name, $type, false, null, '', null, null, '', $position, $ddl);
	}

	/**
	 * @param string $name
	 * @param string $kind an IndexSchema::KIND_* constant.
	 * @param string[] $columns Indexed column names, in index order.
	 * @param string|null $ddl The server's own key clause, or null.
	 * @return IndexSchema
	 */
	private function idx($name, $kind, array $columns, $ddl = null)
	{
		$parts = array();

		foreach($columns as $column)
		{
			$parts[] = new IndexPart($column, null, 'A');
		}

		return new IndexSchema($name, $kind, $parts, $ddl);
	}

	/**
	 * Four columns in ordinal order, three keys, each carrying the server's own DDL.
	 *
	 * @param string[] $only Column names to keep, or an empty list for all four.
	 * @return TableSchema
	 */
	private function newsTable(array $only = array())
	{
		$captured = array(
			'news_id'        => array('int(10) unsigned', '`news_id` int(10) unsigned NOT NULL AUTO_INCREMENT'),
			'news_thumbnail' => array('varchar(255)', "`news_thumbnail` varchar(255) NOT NULL DEFAULT ''"),
			'news_summary'   => array('text', '`news_summary` text DEFAULT NULL'),
			'news_title'     => array('varchar(255)', "`news_title` varchar(255) NOT NULL DEFAULT ''"),
		);

		$columns = array();
		$lines = array();
		$position = 0;

		foreach($captured as $name => $pair)
		{
			if(count($only) > 0 && !in_array($name, $only, true))
			{
				continue;
			}

			$position++;
			$columns[] = $this->col($name, $pair[0], $pair[1], $position);
			$lines[] = '  '.$pair[1];
		}

		$indexes = array($this->idx('PRIMARY', IndexSchema::KIND_PRIMARY, array('news_id'), 'PRIMARY KEY (`news_id`)'));

		if(count($only) === 0)
		{
			$indexes[] = $this->idx('u_summary', IndexSchema::KIND_UNIQUE, array('news_summary'), 'UNIQUE KEY `u_summary` (`news_summary`(32))');
			$indexes[] = $this->idx('k_title', IndexSchema::KIND_INDEX, array('news_title'), 'KEY `k_title` (`news_title`)');
		}

		foreach($indexes as $index)
		{
			$lines[] = '  '.$index->getDdl();
		}

		return new TableSchema(
			'e107_news',
			'InnoDB',
			'utf8mb4',
			'utf8mb4_general_ci',
			$columns,
			$indexes,
			implode(",\n", $lines),
			'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
		);
	}

	/**
	 * A table that has drifted in every way a plan reacts to.
	 *
	 * @return TableDiff
	 */
	private function driftedDiff()
	{
		$expected = $this->newsTable();

		$liveTitle = $this->col('news_title', 'varchar(100)', null, 2);
		$liveKey = $this->idx('k_title', IndexSchema::KIND_INDEX, array('news_title'));

		return new TableDiff('core', 'news', array(
			'expectedTable'   => $expected,
			'engineChange'    => array('expected' => 'InnoDB', 'actual' => 'MyISAM'),
			'charsetChange'   => array('expected' => 'utf8mb4', 'actual' => 'utf8'),
			'missingColumns'  => array(
				'news_thumbnail' => $expected->getColumn('news_thumbnail'),
				'news_summary'   => $expected->getColumn('news_summary'),
			),
			'modifiedColumns' => array('news_title' => new ColumnDiff($expected->getColumn('news_title'), $liveTitle)),
			'missingIndexes'  => array('u_summary' => $expected->getIndex('u_summary')),
			'modifiedIndexes' => array('k_title' => new IndexDiff($expected->getIndex('k_title'), $liveKey)),
		));
	}

	/**
	 * A declared body covering a widening text pair, an expression default and a capitalised ENUM.
	 *
	 * @return string
	 */
	private function probeBody()
	{
		return "probe_id int(10) unsigned NOT NULL auto_increment,\n"
			."probe_title varchar(255) NOT NULL default '',\n"
			."probe_body text NOT NULL,\n"
			."probe_tiny tinytext,\n"
			."probe_stamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,\n"
			."probe_flag enum('Yes','No') NOT NULL default 'Yes',\n"
			."PRIMARY KEY (probe_id),\n"
			.'KEY probe_title (probe_title)';
	}

	/**
	 * @param string $table Unprefixed table name.
	 * @param string $body Declared column/key block.
	 * @param string $engine
	 * @param string $charset
	 * @return TableSchema carrying the server's own DDL.
	 */
	private function materialise($table, $body, $engine, $charset)
	{
		$db = e107::getDb();
		$materialiser = new Materialiser($db, new SchemaReader($db), MPREFIX);

		try
		{
			return $materialiser->materialise(new DeclaredTable('core', $table, $body), $engine, $charset);
		}
		finally
		{
			$materialiser->sweep();
		}
	}

	/**
	 * @param object $db e_db
	 * @param string $physical
	 * @return TableSchema|null the live table, carrying no DDL at all.
	 */
	private function live($db, $physical)
	{
		$reader = new SchemaReader($db);

		return $reader->read($physical);
	}

	/**
	 * @param object $db e_db
	 * @param string $physical
	 * @param string $column
	 * @return string|null COLUMN_TYPE as the server reports it now.
	 */
	private function liveType($db, $physical, $column)
	{
		$db->execute(
			'SELECT COLUMN_TYPE FROM information_schema.COLUMNS'
			.' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column',
			array('table' => $physical, 'column' => $column)
		);

		$row = $db->fetch();

		return is_array($row) ? strtolower($row['COLUMN_TYPE']) : null;
	}

	/**
	 * @param TableDiff $diff
	 * @return string what a failing diff reports, for the assertion message.
	 */
	private function summarise(TableDiff $diff)
	{
		$parts = array();

		if($diff->getEngineChange() !== null)
		{
			$parts[] = 'engine '.$diff->getEngineChange()['actual'].' != '.$diff->getEngineChange()['expected'];
		}

		if($diff->getCharsetChange() !== null)
		{
			$parts[] = 'charset '.$diff->getCharsetChange()['actual'].' != '.$diff->getCharsetChange()['expected'];
		}

		foreach($diff->getMissingColumns() as $name => $column)
		{
			$parts[] = 'missing column '.$name;
		}

		foreach($diff->getModifiedColumns() as $name => $columnDiff)
		{
			$parts[] = 'modified column '.$name.' ('.$columnDiff->getActual()->getColumnType().' != '.$columnDiff->getExpected()->getColumnType().')';
		}

		foreach($diff->getMissingIndexes() as $name => $index)
		{
			$parts[] = 'missing index '.$name;
		}

		foreach($diff->getModifiedIndexes() as $name => $indexDiff)
		{
			$parts[] = 'modified index '.$name;
		}

		return implode('; ', $parts);
	}

	/**
	 * @return PlanBuilder
	 */
	private function planner()
	{
		return new PlanBuilder();
	}

	/**
	 * @return SchemaBuilder bound to a stub connection: nothing here executes.
	 */
	private function schema()
	{
		return new SchemaBuilder(new PlanBuilderTest_dbStub(), new MysqlPlatform());
	}

	/**
	 * @param object $change a ChangeInterface rendering exactly one statement.
	 * @return string
	 */
	private function render($change)
	{
		return $change->toSql($this->schema());
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
 * The least connection {@see SchemaBuilder} will accept: it quotes identifiers
 * through the same grammar as the real one, and executes nothing.
 */
class PlanBuilderTest_dbStub
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
