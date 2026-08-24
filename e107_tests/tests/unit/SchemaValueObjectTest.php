<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexPart;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\TableSchema;

/**
 * DB-less tests for the introspection value objects: {@see ColumnSchema}, {@see IndexPart}, {@see IndexSchema} and
 * {@see TableSchema}.
 */
class SchemaValueObjectTest extends \Test\Unit
{
	/**
	 * Nothing in this class is require_once'd, so every other test here checks the autoload path as well.
	 */
	public function testTheValueObjectsAutoloadFromTheirNamespacePath()
	{
		$classes = array(
			'e107\Database\Schema\Introspect\ColumnSchema',
			'e107\Database\Schema\Introspect\IndexPart',
			'e107\Database\Schema\Introspect\IndexSchema',
			'e107\Database\Schema\Introspect\TableSchema',
		);

		foreach($classes as $class)
		{
			$this->assertTrue(class_exists($class), $class.' must autoload from e107_handlers/'.str_replace('\\', '/', substr($class, 6)).'.php');
		}
	}

	// --- ColumnSchema -----------------------------------------------------

	public function testColumnEqualsAnIdenticalColumn()
	{
		$this->assertTrue($this->column()->equals($this->column()));
	}

	public function testColumnDoesNotEqualSomethingElse()
	{
		$column = $this->column();

		$this->assertFalse($column->equals(null));
		$this->assertFalse($column->equals('user_id'));
		$this->assertFalse($column->equals($this->indexPart('user_id')));
	}

	public function testPositionAloneDoesNotMakeColumnsUnequal()
	{
		$first = $this->column(array('position' => 1));
		$last = $this->column(array('position' => 42));

		$this->assertTrue($first->equals($last));
		$this->assertTrue($last->equals($first));
		$this->assertSame(1, $first->getPosition());
		$this->assertSame(42, $last->getPosition());
	}

	public function testEveryOtherColumnFieldMakesColumnsUnequal()
	{
		$changes = array(
			'name'                 => 'user_name',
			'columnType'           => 'int(11)',
			'nullable'             => true,
			'default'              => '0',
			'extra'                => '',
			'charset'              => 'utf8mb4',
			'collation'            => 'utf8mb4_general_ci',
			'comment'              => 'the id',
			'generationExpression' => '`a` + `b`',
		);

		foreach($changes as $field => $value)
		{
			$this->assertFalse(
				$this->column()->equals($this->column(array($field => $value))),
				'A difference in "'.$field.'" must make two columns unequal.'
			);
		}
	}

	public function testExtraIsLowercasedOnTheWayInAndTheColumnTypeIsNot()
	{
		$column = $this->column(array('columnType' => 'INT(10) UNSIGNED', 'extra' => 'AUTO_INCREMENT'));

		$this->assertSame('auto_increment', $column->getExtra());
		$this->assertSame('INT(10) UNSIGNED', $column->getColumnType(), 'The type token is stored exactly as the server stated it.');
		$this->assertFalse(
			$column->equals($this->column()),
			'and compared exactly. No server reports one column two ways, so a difference here is a real one.'
		);
	}

	public function testAnEnumKeepsItsMembersExactlyAsTheServerStatesThem()
	{
		$column = $this->column(array('columnType' => "enum('Yes','No')"));

		$this->assertSame("enum('Yes','No')", $column->getColumnType());
		$this->assertTrue($column->equals($this->column(array('columnType' => "enum('Yes','No')"))));
		$this->assertFalse(
			$column->equals($this->column(array('columnType' => "enum('yes','no')"))),
			"enum('Yes','No') and enum('yes','no') are different columns holding different values."
		);
	}

	public function testColumnKeepsItsDefaultVerbatim()
	{
		$this->assertNull($this->column()->getDefault());
		$this->assertSame("'0'", $this->column(array('default' => "'0'"))->getDefault());
		$this->assertSame('', $this->column(array('default' => ''))->getDefault());
	}

	public function testColumnToArrayRoundTrips()
	{
		$column = $this->column(array(
			'name'                 => 'user_email',
			'columnType'           => 'varchar(100)',
			'nullable'             => true,
			'default'              => 'NULL',
			'extra'                => '',
			'charset'              => 'utf8mb4',
			'collation'            => 'utf8mb4_unicode_ci',
			'comment'              => 'primary address',
			'generationExpression' => null,
			'position'             => 7,
		));

		$fields = $column->toArray();

		$this->assertSame(
			array('name', 'columnType', 'nullable', 'default', 'extra', 'charset', 'collation', 'comment',
				'generationExpression', 'position'),
			array_keys($fields)
		);
		$this->assertSame('user_email', $fields['name']);
		$this->assertSame('varchar(100)', $fields['columnType']);
		$this->assertTrue($fields['nullable']);
		$this->assertSame('NULL', $fields['default']);
		$this->assertSame('', $fields['extra']);
		$this->assertSame('utf8mb4', $fields['charset']);
		$this->assertSame('utf8mb4_unicode_ci', $fields['collation']);
		$this->assertSame('primary address', $fields['comment']);
		$this->assertNull($fields['generationExpression']);
		$this->assertSame(7, $fields['position']);

		$rebuilt = new ColumnSchema(
			$fields['name'],
			$fields['columnType'],
			$fields['nullable'],
			$fields['default'],
			$fields['extra'],
			$fields['charset'],
			$fields['collation'],
			$fields['comment'],
			$fields['position'],
			null,
			$fields['generationExpression']
		);

		$this->assertTrue($rebuilt->equals($column));
		$this->assertSame($fields, $rebuilt->toArray());
	}

	// --- a generated column's expression ----------------------------------

	public function testAGeneratedColumnCarriesItsExpression()
	{
		$column = $this->column(array('columnType' => 'int(11)', 'extra' => 'stored generated',
			'generationExpression' => '`a` + `b`'));

		$this->assertSame('`a` + `b`', $column->getGenerationExpression());
		$this->assertNull($this->column()->getGenerationExpression(), 'An ordinary column computes nothing.');
	}

	public function testTwoColumnsDifferingOnlyInTheirGenerationExpressionAreNotEqual()
	{
		$sum = $this->column(array('extra' => 'stored generated', 'generationExpression' => '`a` + `b`'));
		$difference = $this->column(array('extra' => 'stored generated', 'generationExpression' => '`a` - `b`'));

		$this->assertFalse($sum->equals($difference));
		$this->assertFalse($difference->equals($sum));
		$this->assertSame($sum->getExtra(), $difference->getExtra(), 'EXTRA cannot tell them apart.');
		$this->assertFalse($sum->equals($this->column(array('extra' => 'stored generated'))),
			'A generated column is not the same as one that computes nothing.');
	}

	public function testWithDdlKeepsTheGenerationExpression()
	{
		$column = $this->column(array('extra' => 'stored generated', 'generationExpression' => '`a` + `b`'));
		$materialised = $column->withDdl('`total` int(11) GENERATED ALWAYS AS (`a` + `b`) STORED');

		$this->assertSame('`a` + `b`', $materialised->getGenerationExpression());
		$this->assertTrue($materialised->equals($column), 'The DDL is the only thing withDdl() adds.');
	}

	public function testTheGenerationExpressionIsAnIdentifyingField()
	{
		$fields = $this->column(array('generationExpression' => '`a` + `b`'))->toArray();
		$keys = array_keys($fields);

		$this->assertSame('`a` + `b`', $fields['generationExpression']);
		$this->assertSame('generationExpression', $keys[8], 'It sits between the comment and the position.');
	}

	public function testAColumnBuiltWithoutAnExpressionHasNone()
	{
		$nine = new ColumnSchema('user_id', 'int(10) unsigned', false, null, 'auto_increment', null, null, '', 1);
		$ten = new ColumnSchema('user_id', 'int(10) unsigned', false, null, 'auto_increment', null, null, '', 1,
			'`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT');

		$this->assertNull($nine->getGenerationExpression());
		$this->assertNull($ten->getGenerationExpression());
		$this->assertTrue($nine->equals($ten));
		$this->assertTrue($nine->equals($this->column()));
	}

	// --- IndexPart --------------------------------------------------------

	public function testIndexPartEqualsAnIdenticalPart()
	{
		$this->assertTrue($this->indexPart('user_email')->equals($this->indexPart('user_email')));
	}

	public function testIndexPartInequality()
	{
		$part = new IndexPart('user_email', 32, 'ASC');

		$this->assertFalse($part->equals(new IndexPart('user_name', 32, 'ASC')));
		$this->assertFalse($part->equals(new IndexPart('user_email', 16, 'ASC')));
		$this->assertFalse($part->equals(new IndexPart('user_email', null, 'ASC')));
		$this->assertFalse($part->equals(new IndexPart('user_email', 32, 'DESC')));
		$this->assertFalse($part->equals(new IndexPart('user_email', 32, null)));
		$this->assertFalse($part->equals(null));
	}

	public function testIndexPartAcceptsBothSpellingsOfADirection()
	{
		$this->assertSame(IndexPart::ASC, $this->indexPart('a', null, 'A')->getDirection());
		$this->assertSame(IndexPart::ASC, $this->indexPart('a', null, 'asc')->getDirection());
		$this->assertSame(IndexPart::DESC, $this->indexPart('a', null, 'D')->getDirection());
		$this->assertSame(IndexPart::DESC, $this->indexPart('a', null, 'desc')->getDirection());
		$this->assertNull($this->indexPart('a', null, null)->getDirection());
		$this->assertNull($this->indexPart('a', null, '')->getDirection());
	}

	public function testIndexPartRejectsWhatItCannotRead()
	{
		$this->assertThrowsInvalidArgument(function ()
		{
			new IndexPart('a', null, 'sideways');
		});

		$this->assertThrowsInvalidArgument(function ()
		{
			new IndexPart('', null, 'ASC');
		});
	}

	public function testIndexPartToArrayRoundTrips()
	{
		$part = new IndexPart('user_email', 32, 'DESC');
		$fields = $part->toArray();

		$this->assertSame(array('columnName', 'subPart', 'direction'), array_keys($fields));
		$this->assertSame('user_email', $fields['columnName']);
		$this->assertSame(32, $fields['subPart']);
		$this->assertSame('DESC', $fields['direction']);

		$rebuilt = new IndexPart($fields['columnName'], $fields['subPart'], $fields['direction']);

		$this->assertTrue($rebuilt->equals($part));
		$this->assertSame($fields, $rebuilt->toArray());
	}

	// --- IndexSchema ------------------------------------------------------

	public function testIndexEqualsAnIdenticalIndex()
	{
		$this->assertTrue($this->index()->equals($this->index()));
	}

	public function testIndexPartOrderIsSignificant()
	{
		$ab = $this->index('user_lookup', IndexSchema::KIND_INDEX, array('a', 'b'));
		$ba = $this->index('user_lookup', IndexSchema::KIND_INDEX, array('b', 'a'));

		$this->assertFalse($ab->equals($ba));
		$this->assertFalse($ba->equals($ab));
		$this->assertSame(array('a', 'b'), $ab->getColumnNames());
		$this->assertSame(array('b', 'a'), $ba->getColumnNames());
	}

	public function testIndexInequality()
	{
		$index = $this->index('user_lookup', IndexSchema::KIND_INDEX, array('a', 'b'));

		$this->assertFalse($index->equals($this->index('user_lookup2', IndexSchema::KIND_INDEX, array('a', 'b'))));
		$this->assertFalse($index->equals($this->index('user_lookup', IndexSchema::KIND_UNIQUE, array('a', 'b'))));
		$this->assertFalse($index->equals($this->index('user_lookup', IndexSchema::KIND_INDEX, array('a'))));
		$this->assertFalse($index->equals($this->index('user_lookup', IndexSchema::KIND_INDEX, array('a', 'b', 'c'))));
		$this->assertFalse($index->equals(null));

		$prefixed = new IndexSchema('user_lookup', IndexSchema::KIND_INDEX, array(
			new IndexPart('a', 8, 'ASC'),
			new IndexPart('b', null, 'ASC'),
		));

		$this->assertFalse($index->equals($prefixed));
	}

	public function testIndexKindIsNormalisedAndValidated()
	{
		$this->assertSame(
			IndexSchema::KIND_PRIMARY,
			$this->index('PRIMARY', 'primary', array('user_id'))->getKind()
		);

		$this->assertThrowsInvalidArgument(function ()
		{
			new IndexSchema('user_lookup', 'KEY', array(new IndexPart('a', null, 'ASC')));
		});

		$this->assertThrowsInvalidArgument(function ()
		{
			new IndexSchema('user_lookup', IndexSchema::KIND_INDEX, array());
		});

		$this->assertThrowsInvalidArgument(function ()
		{
			new IndexSchema('', IndexSchema::KIND_INDEX, array(new IndexPart('a', null, 'ASC')));
		});
	}

	public function testIndexToArrayRoundTrips()
	{
		$index = new IndexSchema('user_lookup', IndexSchema::KIND_UNIQUE, array(
			new IndexPart('a', 8, 'ASC'),
			new IndexPart('b', null, 'DESC'),
		));

		$fields = $index->toArray();

		$this->assertSame(array('name', 'kind', 'parts'), array_keys($fields));
		$this->assertSame('user_lookup', $fields['name']);
		$this->assertSame('UNIQUE', $fields['kind']);
		$this->assertSame(
			array(
				array('columnName' => 'a', 'subPart' => 8, 'direction' => 'ASC'),
				array('columnName' => 'b', 'subPart' => null, 'direction' => 'DESC'),
			),
			$fields['parts']
		);

		$parts = array();

		foreach($fields['parts'] as $part)
		{
			$parts[] = new IndexPart($part['columnName'], $part['subPart'], $part['direction']);
		}

		$rebuilt = new IndexSchema($fields['name'], $fields['kind'], $parts);

		$this->assertTrue($rebuilt->equals($index));
		$this->assertSame($fields, $rebuilt->toArray());
	}

	// --- TableSchema ------------------------------------------------------

	public function testTableEqualsAnIdenticalTable()
	{
		$this->assertTrue($this->table()->equals($this->table()));
	}

	public function testTableEngineIsComparedCaseInsensitively()
	{
		$asDeclared = $this->table(array('engine' => 'INNODB'));
		$asRead = $this->table(array('engine' => 'InnoDB'));

		$this->assertTrue($asDeclared->equals($asRead));
		$this->assertTrue($asRead->hasEngine('innodb'));
		$this->assertFalse($asRead->hasEngine('MyISAM'));
		$this->assertFalse($asRead->hasEngine(null));
		$this->assertSame('InnoDB', $asRead->getEngine());
	}

	public function testTableInequality()
	{
		$table = $this->table();

		$this->assertFalse($table->equals($this->table(array('name' => 'e107_other'))));
		$this->assertFalse($table->equals($this->table(array('engine' => 'MyISAM'))));
		$this->assertFalse($table->equals($this->table(array('charset' => 'latin1'))));
		$this->assertFalse($table->equals($this->table(array('collation' => 'utf8mb4_unicode_ci'))));
		$this->assertFalse($table->equals(null));

		$widened = $this->table(array('columns' => array(
			$this->column(array('name' => 'user_id')),
			$this->column(array('name' => 'user_name', 'columnType' => 'varchar(255)', 'extra' => '', 'position' => 2)),
		)));
		$this->assertFalse($table->equals($widened));

		$shortened = $this->table(array('columns' => array($this->column(array('name' => 'user_id')))));
		$this->assertFalse($table->equals($shortened));

		$reIndexed = $this->table(array('indexes' => array(
			$this->index('user_name', IndexSchema::KIND_UNIQUE, array('user_name')),
		)));
		$this->assertFalse($table->equals($reIndexed));

		$unIndexed = $this->table(array('indexes' => array()));
		$this->assertFalse($table->equals($unIndexed));
	}

	public function testTableColumnOrderIsNotDrift()
	{
		$ordered = $this->table();
		$shuffled = $this->table(array('columns' => array(
			$this->column(array('name' => 'user_name', 'columnType' => 'varchar(100)', 'extra' => '', 'position' => 1)),
			$this->column(array('name' => 'user_id', 'position' => 2)),
		)));

		$this->assertTrue($ordered->equals($shuffled));
		$this->assertSame(array('user_id', 'user_name'), array_keys($ordered->getColumns()));
		$this->assertSame(array('user_name', 'user_id'), array_keys($shuffled->getColumns()));
	}

	public function testTableLooksUpItsMembersByName()
	{
		$table = $this->table();

		$this->assertSame('user_id', $table->getColumn('user_id')->getName());
		$this->assertNull($table->getColumn('user_nonesuch'));
		$this->assertSame('PRIMARY', $table->getIndex('PRIMARY')->getName());
		$this->assertNull($table->getIndex('nonesuch'));
		$this->assertSame(array('PRIMARY'), array_keys($table->getIndexes()));
	}

	public function testTableRejectsWhatItCannotKey()
	{
		$this->assertThrowsInvalidArgument(function ()
		{
			$this->table(array('name' => ''));
		});

		$this->assertThrowsInvalidArgument(function ()
		{
			$this->table(array('columns' => array($this->column(), $this->column())));
		});

		$this->assertThrowsInvalidArgument(function ()
		{
			$this->table(array('columns' => array($this->indexPart('user_id'))));
		});

		$this->assertThrowsInvalidArgument(function ()
		{
			$this->table(array('indexes' => array($this->column())));
		});
	}

	public function testTableToArrayRoundTrips()
	{
		$table = $this->table();
		$fields = $table->toArray();

		$this->assertSame(array('name', 'engine', 'charset', 'collation', 'columns', 'indexes'), array_keys($fields));
		$this->assertSame('e107_user', $fields['name']);
		$this->assertSame('InnoDB', $fields['engine']);
		$this->assertSame('utf8mb4', $fields['charset']);
		$this->assertSame('utf8mb4_general_ci', $fields['collation']);
		$this->assertSame(array('user_id', 'user_name'), array_keys($fields['columns']));
		$this->assertSame(array('PRIMARY'), array_keys($fields['indexes']));
		$this->assertSame($table->getColumn('user_id')->toArray(), $fields['columns']['user_id']);
		$this->assertSame($table->getIndex('PRIMARY')->toArray(), $fields['indexes']['PRIMARY']);

		$columns = array();

		foreach($fields['columns'] as $column)
		{
			$columns[] = new ColumnSchema(
				$column['name'],
				$column['columnType'],
				$column['nullable'],
				$column['default'],
				$column['extra'],
				$column['charset'],
				$column['collation'],
				$column['comment'],
				$column['position']
			);
		}

		$indexes = array();

		foreach($fields['indexes'] as $index)
		{
			$parts = array();

			foreach($index['parts'] as $part)
			{
				$parts[] = new IndexPart($part['columnName'], $part['subPart'], $part['direction']);
			}

			$indexes[] = new IndexSchema($index['name'], $index['kind'], $parts);
		}

		$rebuilt = new TableSchema(
			$fields['name'],
			$fields['engine'],
			$fields['charset'],
			$fields['collation'],
			$columns,
			$indexes
		);

		$this->assertTrue($rebuilt->equals($table));
		$this->assertSame($fields, $rebuilt->toArray());
	}

	// --- the captured DDL -------------------------------------------------

	public function testTwoColumnsDifferingOnlyInTheirCapturedDdlAreEqual()
	{
		$materialised = $this->column()->withDdl('`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT');
		$live = $this->column();

		$this->assertTrue($materialised->equals($live));
		$this->assertTrue($live->equals($materialised));
		$this->assertTrue($materialised->equals($materialised->withDdl('`user_id` int unsigned NOT NULL AUTO_INCREMENT')));
	}

	public function testTwoIndexesDifferingOnlyInTheirCapturedDdlAreEqual()
	{
		$materialised = $this->index()->withDdl('PRIMARY KEY (`user_id`)');
		$live = $this->index();

		$this->assertTrue($materialised->equals($live));
		$this->assertTrue($live->equals($materialised));
	}

	public function testTwoTablesDifferingOnlyInTheirCapturedCreateTextAreEqual()
	{
		$materialised = $this->table(array(
			'createBody'    => '  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,',
			'createOptions' => 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
		));

		$this->assertTrue($materialised->equals($this->table()));
		$this->assertTrue($this->table()->equals($materialised));
	}

	public function testWhatIsReadFromALiveTableCarriesNoDdlAtAll()
	{
		$this->assertNull($this->column()->getDdl());
		$this->assertNull($this->index()->getDdl());
		$this->assertNull($this->table()->getCreateBody());
		$this->assertNull($this->table()->getCreateOptions());
	}

	public function testTheCapturedDdlIsReachableThroughItsGetter()
	{
		$column = $this->column(array('ddl' => '`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT'));
		$index = new IndexSchema('PRIMARY', IndexSchema::KIND_PRIMARY, array($this->indexPart('user_id')), 'PRIMARY KEY (`user_id`)');
		$table = $this->table(array('createBody' => '  `user_id` int(10) unsigned NOT NULL,', 'createOptions' => 'ENGINE=InnoDB'));

		$this->assertSame('`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT', $column->getDdl());
		$this->assertSame('PRIMARY KEY (`user_id`)', $index->getDdl());
		$this->assertSame('  `user_id` int(10) unsigned NOT NULL,', $table->getCreateBody());
		$this->assertSame('ENGINE=InnoDB', $table->getCreateOptions());
	}

	public function testWithDdlReturnsACopyAndLeavesTheOriginalAlone()
	{
		$column = $this->column();
		$index = $this->index();

		$withColumnDdl = $column->withDdl('`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT');
		$withIndexDdl = $index->withDdl('PRIMARY KEY (`user_id`)');

		$this->assertNotSame($column, $withColumnDdl);
		$this->assertNotSame($index, $withIndexDdl);
		$this->assertNull($column->getDdl(), 'The value objects are immutable; withDdl() copies.');
		$this->assertNull($index->getDdl());
		$this->assertSame('`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT', $withColumnDdl->getDdl());
		$this->assertSame('PRIMARY KEY (`user_id`)', $withIndexDdl->getDdl());
		$this->assertSame($column->toArray(), $withColumnDdl->toArray());
		$this->assertSame($index->toArray(), $withIndexDdl->toArray());
	}

	public function testTheCapturedDdlIsNotAnIdentifyingField()
	{
		$column = $this->column(array('ddl' => '`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT'));
		$index = $this->index()->withDdl('PRIMARY KEY (`user_id`)');
		$table = $this->table(array('createBody' => '  `user_id` int(10) unsigned NOT NULL,', 'createOptions' => 'ENGINE=InnoDB'));

		$this->assertArrayNotHasKey('ddl', $column->toArray());
		$this->assertArrayNotHasKey('ddl', $index->toArray());
		$this->assertArrayNotHasKey('createBody', $table->toArray());
		$this->assertArrayNotHasKey('createOptions', $table->toArray());
		$this->assertSame($this->column()->toArray(), $column->toArray());
		$this->assertSame($this->index()->toArray(), $index->toArray());
		$this->assertSame($this->table()->toArray(), $table->toArray());
	}

	// --- fixtures ---------------------------------------------------------

	/**
	 * A `user_id int(10) unsigned NOT NULL auto_increment` column, with any field replaced.
	 *
	 * @param array $overrides keyed as {@see ColumnSchema::toArray()}, plus 'ddl'.
	 * @return ColumnSchema
	 */
	private function column(array $overrides = array())
	{
		$fields = array_merge(array(
			'name'                 => 'user_id',
			'columnType'           => 'int(10) unsigned',
			'nullable'             => false,
			'default'              => null,
			'extra'                => 'auto_increment',
			'charset'              => null,
			'collation'            => null,
			'comment'              => '',
			'generationExpression' => null,
			'position'             => 1,
			'ddl'                  => null,
		), $overrides);

		return new ColumnSchema(
			$fields['name'],
			$fields['columnType'],
			$fields['nullable'],
			$fields['default'],
			$fields['extra'],
			$fields['charset'],
			$fields['collation'],
			$fields['comment'],
			$fields['position'],
			$fields['ddl'],
			$fields['generationExpression']
		);
	}

	/**
	 * @param string $columnName
	 * @param int|null $subPart
	 * @param string|null $direction
	 * @return IndexPart
	 */
	private function indexPart($columnName, $subPart = null, $direction = 'ASC')
	{
		return new IndexPart($columnName, $subPart, $direction);
	}

	/**
	 * @param string $name
	 * @param string $kind
	 * @param string[] $columnNames whole-column ascending parts, in order.
	 * @return IndexSchema
	 */
	private function index($name = 'PRIMARY', $kind = IndexSchema::KIND_PRIMARY, array $columnNames = array('user_id'))
	{
		$parts = array();

		foreach($columnNames as $columnName)
		{
			$parts[] = $this->indexPart($columnName);
		}

		return new IndexSchema($name, $kind, $parts);
	}

	/**
	 * A two-column InnoDB table with a PRIMARY KEY, with any field replaced.
	 *
	 * @param array $overrides keyed as {@see TableSchema::toArray()} plus 'createBody' and 'createOptions', with columns and indexes as plain lists.
	 * @return TableSchema
	 */
	private function table(array $overrides = array())
	{
		$fields = array_merge(array(
			'name'          => 'e107_user',
			'engine'        => 'InnoDB',
			'charset'       => 'utf8mb4',
			'collation'     => 'utf8mb4_general_ci',
			'columns'       => array(
				$this->column(array('name' => 'user_id')),
				$this->column(array('name' => 'user_name', 'columnType' => 'varchar(100)', 'extra' => '', 'position' => 2)),
			),
			'indexes'       => array($this->index()),
			'createBody'    => null,
			'createOptions' => null,
		), $overrides);

		return new TableSchema(
			$fields['name'],
			$fields['engine'],
			$fields['charset'],
			$fields['collation'],
			$fields['columns'],
			$fields['indexes'],
			$fields['createBody'],
			$fields['createOptions']
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
