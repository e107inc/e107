<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Plan\Change;

use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\SchemaBuilder;
use RuntimeException;

/**
 * Add a declared column the live table does not have, splicing the definition
 * the server wrote for it ({@see ColumnSchema::getDdl()}) verbatim.
 *
 * The placement is a preference, not a comparison: column order is never drift,
 * so a column that lands elsewhere is not reported again.
 */
final class AddColumn extends AbstractChange
{
	/** @var ColumnSchema the declared column, whole */
	private $column;

	/**
	 * @var string|null existing column to place this after, the
	 *      {@see SchemaBuilder::FIRST} sentinel, or null to append.
	 */
	private $after;

	/**
	 * @param string $sqlFile
	 * @param string $table Unprefixed logical table name.
	 * @param ColumnSchema $column The declared column, carrying the server's own definition line.
	 * @param string|null $after Column to place this after, the {@see SchemaBuilder::FIRST} sentinel, or null.
	 */
	public function __construct($sqlFile, $table, ColumnSchema $column, $after = null)
	{
		parent::__construct($sqlFile, $table);

		$this->column = $column;
		$this->after = $after;
	}

	/**
	 * @return ColumnSchema
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @return string|null
	 */
	public function getAfter()
	{
		return $this->after;
	}

	/**
	 * @return string
	 */
	public function describe()
	{
		return 'Add column `'.$this->column->getName().'`';
	}

	/**
	 * @param SchemaBuilder $schema
	 * @return string
	 * @throws RuntimeException when the column carries no captured definition.
	 */
	public function toSql(SchemaBuilder $schema)
	{
		$definition = $this->captured($this->column->getDdl(), 'column `'.$this->column->getName().'`');

		return $schema->tablePhysical($this->getTable())
			->addColumnRaw($definition, $this->after)
			->getSQL();
	}
}
