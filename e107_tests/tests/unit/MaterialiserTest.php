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
use e107\Database\Schema\Declared\DeclaredTable;
use e107\Database\Schema\Declared\Materialiser;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\SchemaReader;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Reflection\ReflectionMethod;

/**
 * Tests for the scratch-table materialiser, against the live test database.
 *
 * The unit database is loaded from a v2.3.0-era dump, so its `admin_log` is
 * genuinely drifted from what `core_sql.php` declares today.
 */
class MaterialiserTest extends \Test\Unit
{
	use \Helper\SchemaIntent;

	/** @var \e107\Database\ConnectionInterface */
	private $db;

	/** @var Materialiser */
	private $materialiser;

	protected function _before()
	{
		$this->db = e107::getDb();
		$this->materialiser = new Materialiser($this->db, new SchemaReader($this->db), MPREFIX);
	}

	protected function _after()
	{
		$this->materialiser->sweep();
	}

	// --- the scratch table ------------------------------------------------

	public function testEachInstanceOwnsOneUniquelyNamedScratchTable()
	{
		$pattern = '/^'.preg_quote(MPREFIX, '/').'dbvscratch_[0-9a-f]{8}$/';

		$this->assertMatchesRegularExpression($pattern, $this->materialiser->getScratchTableName());

		$other = new Materialiser($this->db, new SchemaReader($this->db), MPREFIX);

		$this->assertMatchesRegularExpression($pattern, $other->getScratchTableName());
		$this->assertNotEquals(
			$this->materialiser->getScratchTableName(),
			$other->getScratchTableName(),
			'Two materialisers must not share a scratch table, or one would drop the other\'s.'
		);
	}

	public function testTheConstructorRejectsAPrefixTheConnectionDoesNotUse()
	{
		$this->expectException('InvalidArgumentException');

		new Materialiser($this->db, new SchemaReader($this->db), 'not_the_prefix_');
	}

	// --- materialise() ----------------------------------------------------

	public function testMaterialisingADeclaredBodyGivesTheShapeTheServerWouldBuild()
	{
		$declared = new DeclaredTable('core', 'widget',
			"widget_id int(10) unsigned NOT NULL auto_increment,
			 widget_name varchar(100) NOT NULL default '',
			 widget_body text,
			 PRIMARY KEY (widget_id),
			 KEY widget_name (widget_name)");

		$schema = $this->materialiser->materialise($declared, 'InnoDB', 'utf8mb4');

		$this->assertInstanceOf(TableSchema::class, $schema);
		$this->assertEquals($this->materialiser->getScratchTableName(), $schema->getName());
		$this->assertTrue($schema->hasEngine('InnoDB'));
		$this->assertEquals('utf8mb4', $schema->getCharset());

		$this->assertEquals(
			array('widget_id', 'widget_name', 'widget_body'),
			array_keys($schema->getColumns()),
			'Columns come back in ordinal order, keyed by name.'
		);

		$id = $schema->getColumn('widget_id');
		$this->assertStringContainsString('int', $id->getColumnType());
		$this->assertStringContainsString('unsigned', $id->getColumnType());
		$this->assertFalse($id->isNullable());
		$this->assertEquals('auto_increment', $id->getExtra());

		$this->assertEquals('varchar(100)', $schema->getColumn('widget_name')->getColumnType());
		$this->assertTrue($schema->getColumn('widget_body')->isNullable(), 'A column declared without NOT NULL is nullable.');

		$this->assertEquals(array('PRIMARY', 'widget_name'), array_keys($schema->getIndexes()));
		$this->assertEquals('PRIMARY', $schema->getIndex('PRIMARY')->getKind());
		$this->assertEquals(array('widget_id'), $schema->getIndex('PRIMARY')->getColumnNames());
	}

	public function testTheScratchTableIsGoneOnceTheSchemaIsRead()
	{
		$declared = new DeclaredTable('core', 'widget', 'widget_id int(10) unsigned NOT NULL, PRIMARY KEY (widget_id)');

		$this->materialiser->materialise($declared, 'InnoDB', 'utf8mb4');

		$this->assertFalse(
			$this->tableExists($this->materialiser->getScratchTableName()),
			'The scratch table must not outlive the call that created it.'
		);
	}

	public function testTheScratchTableIsGoneWhenTheDeclaredBodyIsInvalid()
	{
		$declared = new DeclaredTable('core', 'widget', 'this is not a column definition');

		$thrown = null;

		try
		{
			$this->materialiser->materialise($declared, 'InnoDB', 'utf8mb4');
		}
		catch(QueryException $e)
		{
			$thrown = $e;
		}

		$this->assertInstanceOf(QueryException::class, $thrown, 'A body the server refuses must throw, never return a partial schema.');
		$this->assertStringContainsString('widget', $thrown->getMessage());
		$this->assertStringContainsString('CREATE TABLE', $thrown->getMessage(), 'The DDL is attached, because it is the only record of what was asked.');

		$this->assertFalse(
			$this->tableExists($this->materialiser->getScratchTableName()),
			'A refused CREATE leaves nothing behind. That the drop also runs after a successful one is '
			.'testTheScratchTableIsGoneOnceTheSchemaIsRead; between them the finally is covered both ways.'
		);
	}

	public function testAnInstanceIsReusableAfterAFailure()
	{
		try
		{
			$this->materialiser->materialise(new DeclaredTable('core', 'broken', 'not a column'), 'InnoDB', 'utf8mb4');
			$this->fail('The invalid body should have thrown.');
		}
		catch(QueryException $e)
		{
		}

		$schema = $this->materialiser->materialise(
			new DeclaredTable('core', 'widget', 'widget_id int(10) unsigned NOT NULL, PRIMARY KEY (widget_id)'),
			'InnoDB', 'utf8mb4'
		);

		$this->assertEquals(array('widget_id'), array_keys($schema->getColumns()));
	}

	public function testAnEmptyDeclaredBodyIsRejectedBeforeAnyDdlRuns()
	{
		$this->expectException('InvalidArgumentException');

		$this->materialiser->materialise(new DeclaredTable('core', 'widget', "  \n\t "), 'InnoDB', 'utf8mb4');
	}

	public function testAnEngineOutsideTheIdentifierGrammarIsRejected()
	{
		$this->expectException('InvalidArgumentException');

		$this->materialiser->materialise(
			new DeclaredTable('core', 'widget', 'widget_id int(10) NOT NULL'),
			'InnoDB; DROP TABLE x', 'utf8mb4'
		);
	}

	public function testWhateverEngineAndCharacterSetTheServerSupportsIsBuiltAsAsked()
	{
		$declared = new DeclaredTable('core', 'widget',
			"widget_id int(10) unsigned NOT NULL,
			 widget_name varchar(20) NOT NULL default ''");

		$supported = $this->supportedEngines();
		$built = 0;

		foreach(array(array('Aria', 'latin1'), array('MEMORY', 'latin1'), array('MyISAM', 'utf8mb3'), array('InnoDB', 'utf8mb4')) as $pair)
		{
			list($engine, $charset) = $pair;

			if(!isset($supported[strtolower($engine)]))
			{
				continue;
			}

			$schema = $this->materialiser->materialise($declared, $engine, $charset);
			$built++;

			$this->assertTrue($schema->hasEngine($engine), $engine.' is a real engine on this server; the table must be built with it.');
			$this->assertTrue(
				$this->sameCharset($charset, $schema->getCharset()),
				$charset.' is a real character set on this server; the table must be built with it, '
				.'but it came back as '.$schema->getCharset().'.'
			);
		}

		$this->assertGreaterThanOrEqual(3, $built, 'Both supported vendors offer MEMORY, MyISAM and InnoDB, so a run that exercised fewer than three pairs is filtering away the point of the test rather than an engine this server lacks.');
	}

	/**
	 * The storage engines this server will build a table with, keyed by lowercased name.
	 *
	 * @return array
	 */
	private function supportedEngines()
	{
		$this->assertNotFalse($this->db->execute("SELECT ENGINE FROM information_schema.ENGINES WHERE SUPPORT IN ('YES', 'DEFAULT')"));

		$engines = array();

		while($row = $this->db->fetch())
		{
			$engines[strtolower($row['ENGINE'])] = true;
		}

		return $engines;
	}

	/**
	 * @dataProvider absentOptionProvider
	 * @param string|null $engine
	 * @param string|null $charset
	 */
	public function testAnAbsentEngineOrCharacterSetIsNotALicenceToUseTheServerDefault($engine, $charset)
	{
		$this->expectException('InvalidArgumentException');

		$this->materialiser->materialise(
			new DeclaredTable('core', 'widget', 'widget_id int(10) NOT NULL'),
			$engine, $charset
		);
	}

	/**
	 * @return array
	 */
	public function absentOptionProvider()
	{
		return array(
			'no engine'  => array(null, 'utf8mb4'),
			'no charset' => array('InnoDB', null),
			'neither'    => array(null, null),
			'blank'      => array('InnoDB', '  '),
		);
	}

	public function testUnsignedACharsetACollationACommentAndAPrefixLengthAllSurvive()
	{
		$declared = new DeclaredTable('core', 'widget',
			"widget_id int(10) unsigned NOT NULL auto_increment,
			 widget_code varchar(20) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL default 'x' COMMENT 'a code',
			 widget_body text NOT NULL,
			 PRIMARY KEY (widget_id),
			 KEY widget_body (widget_body(32))");

		$schema = $this->materialiser->materialise($declared, 'InnoDB', 'utf8mb4');

		$this->assertContains(
			$schema->getColumn('widget_id')->getColumnType(),
			array('int(10) unsigned', 'int unsigned'),
			'UNSIGNED survives, inside the one opaque type token. MariaDB 10.11 states the display width and '
			.'MySQL 8 dropped it, which is the design thesis in miniature: the two vendors disagree, and the '
			.'disagreement never matters because both sides of a verify are read from one of them.'
		);

		$code = $schema->getColumn('widget_code');
		$this->assertEquals('latin1', $code->getCharset(), 'A column charset that differs from the table default is kept; the reader only nulls the ones that match.');
		$this->assertEquals('latin1_bin', $code->getCollation());
		$this->assertEquals('a code', $code->getComment());
		$this->assertFalse($code->isNullable());
		$this->assertContains(
			$code->getDefault(),
			array('x', "'x'"),
			'A string default reads back as x on MySQL and as \'x\' on MariaDB, which stores COLUMN_DEFAULT as an '
			.'expression. Neither side of a verify is spared that, so neither needs a rule for it.'
		);

		$parts = $schema->getIndex('widget_body')->getParts();

		$this->assertCount(1, $parts);
		$this->assertEquals('widget_body', $parts[0]->getColumnName());
		$this->assertEquals(32, $parts[0]->getSubPart(), 'A prefix index keeps its length.');
	}

	// --- the captured DDL -------------------------------------------------

	public function testEveryColumnAndIndexCarriesTheServersOwnDefinitionLine()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());

		foreach($schema->getColumns() as $name => $column)
		{
			$this->assertNotNull($column->getDdl(), $name.' should carry the definition line the server wrote for it.');
			$this->assertStringStartsWith('`'.$name.'` ', $column->getDdl(), 'A column definition opens with its own quoted name.');
			$this->assertStringNotContainsString("\n", $column->getDdl(), 'One definition, one line.');
			$this->assertNotEquals(',', substr($column->getDdl(), -1), 'The separating comma belongs to the list, not to the definition.');
		}

		foreach($schema->getIndexes() as $name => $index)
		{
			$this->assertNotNull($index->getDdl(), $name.' should carry the definition line the server wrote for it.');
			$this->assertStringNotContainsString("\n", $index->getDdl());
			$this->assertNotEquals(',', substr($index->getDdl(), -1));
		}

		$this->assertStringStartsWith('PRIMARY KEY ', $schema->getIndex('PRIMARY')->getDdl());
		$this->assertStringStartsWith('UNIQUE KEY `widget_name` ', $schema->getIndex('widget_name')->getDdl());
		$this->assertStringStartsWith('KEY `widget_flag` ', $schema->getIndex('widget_flag')->getDdl());
		$this->assertStringStartsWith('FULLTEXT KEY `widget_body` ', $schema->getIndex('widget_body')->getDdl());
	}

	public function testTheShapesAHandWrittenRendererWouldLoseAllSurviveTheCapture()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());

		$code = $schema->getColumn('widget_code')->getDdl();
		$this->assertStringContainsString('CHARACTER SET latin1', $code);
		$this->assertStringContainsString('COLLATE latin1_general_ci', $code);
		$this->assertStringContainsString("COMMENT 'a, comma'", $code, 'A comma inside a comment stays inside its quotes, on one line.');

		$flag = $schema->getColumn('widget_flag');
		$this->assertStringContainsString("enum('Yes','No')", $flag->getDdl(), 'ENUM members are user data and keep their case.');
		$this->assertSame("enum('Yes','No')", $flag->getColumnType());

		$changed = $schema->getColumn('widget_changed')->getDdl();
		$this->assertMatchesRegularExpression('/DEFAULT current_timestamp(\(\))?/i', $changed, 'An expression default is not quoted.');
		$this->assertMatchesRegularExpression('/ON UPDATE current_timestamp(\(\))?/i', $changed);
		$this->assertStringNotContainsString("DEFAULT '", $changed, "MariaDB spells it current_timestamp() and MySQL CURRENT_TIMESTAMP; neither is a string.");

		$this->assertStringContainsString('unsigned', $schema->getColumn('widget_id')->getDdl());
		$this->assertStringContainsString('AUTO_INCREMENT', $schema->getColumn('widget_id')->getDdl());
		$this->assertStringContainsString('(20)', $schema->getIndex('widget_name')->getDdl(), 'A prefix length is part of the index definition.');
	}

	public function testTheWholeCreateBodyAndOptionsAreKeptForCreateTable()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());

		$body = $schema->getCreateBody();

		$this->assertNotNull($body);

		foreach($schema->getColumns() as $column)
		{
			$this->assertStringContainsString($column->getDdl(), $body, 'The body is the lines, so it contains every one of them.');
		}

		foreach($schema->getIndexes() as $index)
		{
			$this->assertStringContainsString($index->getDdl(), $body);
		}

		$intent = $this->schemaIntentFor($this->richDeclaration());
		$options = $schema->getCreateOptions();

		$this->assertStringContainsString('ENGINE='.$intent['engine'], $options);
		$this->assertStringContainsString('CHARSET='.$intent['charset'], $options);
		$this->assertStringNotContainsString('(', $options, 'The options begin after the closing parenthesis.');
	}

	/**
	 * A guard against the server changing, not the code: no live fixture on this
	 * matrix produces a counter, so this passes with the strip removed altogether.
	 */
	public function testNoAutoIncrementCounterSurvivesIntoTheCapturedOptions()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());

		$this->assertStringContainsString('AUTO_INCREMENT', $schema->getColumn('widget_id')->getDdl(), 'The column keyword is a schema fact and stays.');
		$this->assertStringNotContainsStringIgnoringCase('AUTO_INCREMENT=', $schema->getCreateOptions(), 'The table counter is a data fact and goes.');
		$this->assertStringNotContainsStringIgnoringCase('AUTO_INCREMENT=', $schema->getCreateBody());
	}

	/**
	 * The strip itself, over the option strings the two vendors write.
	 *
	 * Reached by reflection because no live fixture on this matrix produces a counter.
	 *
	 * @dataProvider counterStripCases
	 * @param string $options a SHOW CREATE TABLE options tail
	 * @param string $expected
	 * @param string $why
	 */
	public function testTheCounterStripRemovesTheCounterAndNothingElse($options, $expected, $why)
	{
		$strip = new ReflectionMethod('e107\\Database\\Schema\\Declared\\Materialiser', '_withoutAutoIncrement');

		$this->assertSame($expected, $strip->invoke(null, $options), $why);
	}

	/**
	 * @return array[] [options, expected, why]
	 */
	public function counterStripCases()
	{
		return array(
			'MariaDB, counter between the engine and the charset' => array(
				'ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
				'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
				'The counter goes and the options either side of it stay.',
			),
			'MySQL 8, spaces around the equals sign' => array(
				'ENGINE=InnoDB AUTO_INCREMENT = 1024 DEFAULT CHARSET=utf8mb4',
				'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
				'The server is entitled to space its own options out.',
			),
			'lowercase' => array(
				'engine=innodb auto_increment=7 default charset=utf8mb4',
				'engine=innodb default charset=utf8mb4',
				'The match is case-insensitive.',
			),
			'trailing counter' => array(
				'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=3',
				'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
				'A counter at the end leaves no trailing whitespace behind.',
			),
			'no counter at all' => array(
				'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
				'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
				'The ordinary case is left alone.',
			),
			'a table comment that says AUTO_INCREMENT=1' => array(
				"ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COMMENT='AUTO_INCREMENT=1 here'",
				"ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AUTO_INCREMENT=1 here'",
				'Only the options before the first quote are rewritten, so user text survives verbatim.',
			),
			'a table comment alone' => array(
				"ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='rows: AUTO_INCREMENT=900'",
				"ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='rows: AUTO_INCREMENT=900'",
				'Nothing outside a quote to strip means nothing is touched.',
			),
			'the column keyword is not the table option' => array(
				'ENGINE=MyISAM DEFAULT CHARSET=latin1',
				'ENGINE=MyISAM DEFAULT CHARSET=latin1',
				'AUTO_INCREMENT without a value is a column keyword and never appears here.',
			),
		);
	}

	public function testApplyingEveryCapturedColumnDefinitionToAMatchingTableIsANoOp()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());
		$live = MPREFIX.'dbvcapture';

		$this->buildFromCapture($live, $schema);

		try
		{
			$before = $this->showCreateTable($live);

			foreach($schema->getColumns() as $name => $column)
			{
				$this->assertNotFalse(
					$this->db->execute('ALTER TABLE `'.$live.'` MODIFY '.$column->getDdl()),
					'The server should accept its own definition of '.$name.' back: '.$column->getDdl()
				);
			}

			$this->assertSame($before, $this->showCreateTable($live), 'Restating a column as the server states it changes nothing.');
		}
		finally
		{
			$this->dropRawTable($live);
		}
	}

	public function testApplyingEveryCapturedIndexDefinitionRebuildsTheSameIndex()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());
		$live = MPREFIX.'dbvcapture';

		$this->buildFromCapture($live, $schema);

		try
		{
			foreach($schema->getIndexes() as $name => $index)
			{
				if($index->getKind() === IndexSchema::KIND_PRIMARY)
				{
					continue;
				}

				$this->assertNotFalse($this->db->execute('ALTER TABLE `'.$live.'` DROP INDEX `'.$name.'`'));
				$this->assertNotFalse(
					$this->db->execute('ALTER TABLE `'.$live.'` ADD '.$index->getDdl()),
					'The server should accept its own definition of '.$name.' back: '.$index->getDdl()
				);
			}

			$rebuilt = (new SchemaReader($this->db))->read($live);

			foreach($schema->getIndexes() as $name => $index)
			{
				$this->assertNotNull($rebuilt->getIndex($name), $name.' should be back.');
				$this->assertTrue($index->equals($rebuilt->getIndex($name)), $name.' should come back as the same index, kind and parts and all.');
			}
		}
		finally
		{
			$this->dropRawTable($live);
		}
	}

	public function testTheCapturedCreateTextRebuildsTheSameTableUnderAnotherName()
	{
		$schema = $this->materialiseAsIntended($this->richDeclaration());
		$live = MPREFIX.'dbvcapture';

		$this->buildFromCapture($live, $schema);

		try
		{
			$rebuilt = (new SchemaReader($this->db))->read($live);

			$this->assertInstanceOf(TableSchema::class, $rebuilt);
			$this->assertSame(array_keys($schema->getColumns()), array_keys($rebuilt->getColumns()));

			foreach($schema->getColumns() as $name => $column)
			{
				$this->assertTrue($column->equals($rebuilt->getColumn($name)), $name.' should come back identical.');
			}

			foreach($schema->getIndexes() as $name => $index)
			{
				$this->assertTrue($index->equals($rebuilt->getIndex($name)), $name.' should come back identical.');
			}

			$this->assertNull($rebuilt->getColumn('widget_id')->getDdl(), 'A table read from the database carries no DDL, whoever built it.');
		}
		finally
		{
			$this->dropRawTable($live);
		}
	}

	public function testAMaterialisedColumnStillEqualsTheLiveColumnItDescribes()
	{
		$declared = $this->declaredCoreTable('admin_log');
		$live = $this->readLive('admin_log');
		$expected = $this->materialiser->materialise($declared, $live->getEngine(), $live->getCharset());

		foreach($live->getColumns() as $name => $liveColumn)
		{
			$this->assertNotNull($expected->getColumn($name), 'The declared body should still declare '.$name.'.');
			$this->assertNotNull($expected->getColumn($name)->getDdl(), $name.' is materialised, so it carries DDL,');
			$this->assertNull($liveColumn->getDdl(), 'and read live, so it does not,');
			$this->assertTrue($expected->getColumn($name)->equals($liveColumn), 'and the two are still equal.');
		}

		foreach($live->getIndexes() as $name => $liveIndex)
		{
			if($expected->getIndex($name) === null)
			{
				continue;
			}

			$this->assertNotNull($expected->getIndex($name)->getDdl());
			$this->assertNull($liveIndex->getDdl());
			$this->assertTrue($expected->getIndex($name)->equals($liveIndex));
		}
	}

	// --- the payoff -------------------------------------------------------

	public function testTheDeclaredAdminLogAgreesWithTheLiveOneColumnByColumn()
	{
		$declared = $this->declaredCoreTable('admin_log');

		$this->assertStringContainsString(
			"dblog_datestamp int(10) unsigned NOT NULL default '0'",
			$declared->getBody(),
			'This test is about the file spelling an integer default as a quoted string; if core_sql.php stops doing that, it is testing nothing.'
		);
		$this->assertStringContainsString('dblog_remarks text NOT NULL', $declared->getBody());

		$live = $this->readLive('admin_log');
		$expected = $this->materialiser->materialise($declared, $live->getEngine(), $live->getCharset());

		$this->assertTrue($expected->hasEngine($live->getEngine()), 'The scratch table is built at the engine it was handed, not at the server default.');
		$this->assertEquals($live->getCharset(), $expected->getCharset(), 'and at the character set it was handed, which here is not the server default either.');
		$this->assertCount(9, $live->getColumns(), 'The agreement below is over the whole table, not over an empty list.');

		foreach($live->getColumns() as $name => $liveColumn)
		{
			$this->assertNotNull($expected->getColumn($name), 'The declared body should still declare '.$name.'.');
			$this->assertEquals(
				$this->comparedFields($expected->getColumn($name)),
				$this->comparedFields($liveColumn),
				'Column '.$name.' should need no equivalence rule at all.'
			);
			$this->assertTrue($expected->getColumn($name)->equals($liveColumn));
		}

		$this->assertEquals(
			'0',
			$live->getColumn('dblog_datestamp')->getDefault(),
			"The server stores the integer default unquoted, which is exactly the difference diffStructurePermissive() had a rule for."
		);
		$this->assertEquals('text', $live->getColumn('dblog_remarks')->getColumnType());

		$this->assertNotNull($expected->getIndex('dblog_eventcode_title'));
		$this->assertNull(
			$live->getIndex('dblog_eventcode_title'),
			'The v2.3.0 dump is genuinely drifted, so the column-by-column agreement above is not vacuous.'
		);
	}

	public function testATableWideCharsetDifferenceDoesNotShoutOnEveryColumn()
	{
		$declared = $this->declaredCoreTable('admin_log');
		$live = $this->readLive('admin_log');

		$expected = $this->materialiseAsIntended($declared);

		$this->assertFalse($live->hasEngine($expected->getEngine()), 'The dump has this table on MyISAM.');
		$this->assertNotEquals($expected->getCharset(), $live->getCharset(), 'The wider character set against the dump\'s utf8mb3.');

		foreach($live->getColumns() as $name => $liveColumn)
		{
			$this->assertTrue(
				$expected->getColumn($name)->equals($liveColumn),
				'Column '.$name.' should not repeat a table-wide charset difference; the reader nulls a column charset that matches the table default.'
			);
		}
	}

	public function testAnEngineAndCharsetTheDeclaredIndexesDoNotFitThrows()
	{
		$thrown = null;

		try
		{
			$this->materialiser->materialise($this->declaredCoreTable('admin_log'), 'MyISAM', 'utf8mb4');
		}
		catch(QueryException $e)
		{
			$thrown = $e;
		}

		$this->assertInstanceOf(QueryException::class, $thrown);
		$this->assertStringContainsString('admin_log', $thrown->getMessage());
		$this->assertFalse($this->tableExists($this->materialiser->getScratchTableName()));
	}

	public function testEveryTableCoreDeclaresTodayCanBeMaterialised()
	{
		$declared = $this->declaredCoreTables();

		$this->assertGreaterThan(25, count($declared), 'core_sql.php should declare the whole core schema.');

		foreach($declared as $name => $table)
		{
			$schema = $this->materialiseAsIntended($table);

			$this->assertNotEmpty($schema->getColumns(), $name.' should materialise with at least one column.');
		}
	}

	// --- sweep() ----------------------------------------------------------

	public function testSweepDropsALeakedScratchTable()
	{
		$leaked = MPREFIX.'dbvscratch_deadbeef';

		$this->createRawTable($leaked);
		$this->assertTrue($this->tableExists($leaked));

		$this->assertGreaterThanOrEqual(1, $this->materialiser->sweep());
		$this->assertFalse($this->tableExists($leaked), 'A scratch table left by a killed run is swept.');
	}

	public function testSweepLeavesTablesThatOnlyLookLikeScratchTables()
	{
		$decoys = array(MPREFIX.'dbvscratchXdeadbeef');

		if(strpos(MPREFIX, '_') !== false)
		{
			$decoys[] = str_replace('_', 'X', MPREFIX).'dbvscratch_deadbeef';
		}

		foreach($decoys as $decoy)
		{
			$this->createRawTable($decoy);
		}

		try
		{
			$this->assertEquals(0, $this->materialiser->sweep(), 'Every underscore in the pattern is a literal, not a wildcard.');

			foreach($decoys as $decoy)
			{
				$this->assertTrue($this->tableExists($decoy), $decoy.' is not a scratch table and must survive.');
			}
		}
		finally
		{
			foreach($decoys as $decoy)
			{
				$this->dropRawTable($decoy);
			}
		}
	}

	public function testSweepReportsNothingWhenThereIsNothingToSweep()
	{
		$this->materialiser->sweep();

		$this->assertEquals(0, $this->materialiser->sweep());
	}

	// --- helpers ----------------------------------------------------------

	/**
	 * Materialise a declared table at the engine and character set this server would build it with.
	 *
	 * @param DeclaredTable $table
	 * @return TableSchema
	 */
	private function materialiseAsIntended(DeclaredTable $table)
	{
		$intent = $this->schemaIntentFor($table);

		return $this->materialiser->materialise($table, $intent['engine'], $intent['charset']);
	}

	/**
	 * A declaration carrying every shape the rendering correction exists for.
	 *
	 * @return DeclaredTable
	 */
	private function richDeclaration()
	{
		return new DeclaredTable('core', 'widget',
			"widget_id int(10) unsigned NOT NULL auto_increment,
			 widget_name varchar(255) NOT NULL default '',
			 widget_flag enum('Yes','No') NOT NULL default 'Yes',
			 widget_changed timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			 widget_code varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'a, comma',
			 widget_body text NOT NULL,
			 PRIMARY KEY (widget_id),
			 UNIQUE KEY widget_name (widget_name(20)),
			 KEY widget_flag (widget_name(10),widget_flag),
			 FULLTEXT KEY widget_body (widget_body)");
	}

	/**
	 * Build a real table under an exact physical name from a schema's captured CREATE TABLE text.
	 *
	 * @param string $physicalName
	 * @param TableSchema $schema
	 * @return void
	 */
	private function buildFromCapture($physicalName, TableSchema $schema)
	{
		$this->assertMatchesRegularExpression('/^[A-Za-z0-9_]+$/', $physicalName);
		$this->dropRawTable($physicalName);

		$create = 'CREATE TABLE `'.$physicalName.'` ('."\n".$schema->getCreateBody()."\n".') '.$schema->getCreateOptions();

		$this->assertNotFalse($this->db->execute($create), 'The server should accept the statement it wrote itself: '.$create);
	}

	/**
	 * @param string $physicalName
	 * @return string the server's own CREATE TABLE statement.
	 */
	private function showCreateTable($physicalName)
	{
		$this->assertNotFalse($this->db->execute('SHOW CREATE TABLE `'.$physicalName.'`'));

		$row = $this->db->fetch();

		$this->assertArrayHasKey('Create Table', $row);

		return $row['Create Table'];
	}

	/**
	 * A table as `core_sql.php` declares it, parsed by db_verify's own parser.
	 *
	 * @param string $name unprefixed table name.
	 * @return DeclaredTable
	 */
	private function declaredCoreTable($name)
	{
		$declared = $this->declaredCoreTables();

		$this->assertArrayHasKey($name, $declared, 'core_sql.php should declare '.$name.'.');

		return $declared[$name];
	}

	/**
	 * Every table `core_sql.php` declares, keyed by name.
	 *
	 * @return DeclaredTable[]
	 */
	private function declaredCoreTables()
	{
		require_once(e_HANDLER.'db_verify_class.php');

		$parsed = (new db_verify(false))->getSqlFileTables(file_get_contents(e_CORE.'sql/core_sql.php'));
		$declared = array();

		foreach($parsed['tables'] as $at => $name)
		{
			$declared[$name] = new DeclaredTable('core', $name, $parsed['data'][$at],
				isset($parsed['engine'][$at]) ? $parsed['engine'][$at] : null,
				isset($parsed['charset'][$at]) ? $parsed['charset'][$at] : null);
		}

		return $declared;
	}

	/**
	 * @param string $name unprefixed table name.
	 * @return TableSchema
	 */
	private function readLive($name)
	{
		$live = (new SchemaReader($this->db))->read(MPREFIX.$name);

		$this->assertInstanceOf(TableSchema::class, $live, MPREFIX.$name.' should exist in the test database.');

		return $live;
	}

	/**
	 * Everything {@see ColumnSchema::equals()} looks at, laid out for a readable diff.
	 *
	 * @param ColumnSchema $column
	 * @return array
	 */
	private function comparedFields(ColumnSchema $column)
	{
		$fields = $column->toArray();

		unset($fields['position']);

		return $fields;
	}

	/**
	 * @param string $physicalName
	 * @return bool
	 */
	private function tableExists($physicalName)
	{
		$this->db->execute(
			'SELECT COUNT(*) AS hits FROM information_schema.TABLES'
			.' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name',
			array('name' => $physicalName)
		);

		$row = $this->db->fetch();

		return !empty($row['hits']);
	}

	/**
	 * Create a table under an exact physical name, bypassing the schema builder's prefixing.
	 *
	 * @param string $physicalName
	 * @return void
	 */
	private function createRawTable($physicalName)
	{
		$this->assertMatchesRegularExpression('/^[A-Za-z0-9_]+$/', $physicalName);
		$this->dropRawTable($physicalName);
		$this->assertNotFalse($this->db->execute('CREATE TABLE `'.$physicalName.'` (decoy_id INT NOT NULL)'));
	}

	/**
	 * @param string $physicalName
	 * @return void
	 */
	private function dropRawTable($physicalName)
	{
		$this->db->execute('DROP TABLE IF EXISTS `'.$physicalName.'`');
	}

	/**
	 * MySQL 5.7 calls the three-byte UTF-8 set `utf8`; MariaDB 10.11 and MySQL 8
	 * call it `utf8mb3`, and the reader reports whichever this server uses.
	 *
	 * @param string $wanted
	 * @param string $got
	 * @return bool
	 */
	private function sameCharset($wanted, $got)
	{
		$aliases = array('utf8' => 'utf8mb3', 'utf8mb3' => 'utf8mb3');
		$w = strtolower((string) $wanted);
		$g = strtolower((string) $got);
		$w = isset($aliases[$w]) ? $aliases[$w] : $w;
		$g = isset($aliases[$g]) ? $aliases[$g] : $g;

		return $w === $g;
	}

}
