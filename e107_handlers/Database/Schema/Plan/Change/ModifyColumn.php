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
 * Bring a live column back to its declared definition in full, splicing the
 * definition line the server wrote for it ({@see ColumnSchema::getDdl()}).
 *
 * Renders no placement, so the column stays where it is.
 */
final class ModifyColumn extends AbstractChange
{
	/** @var ColumnSchema the declared column, whole */
	private $column;

	/** @var bool */
	private $mayLoseData;

	/**
	 * @param string $sqlFile
	 * @param string $table Unprefixed logical table name.
	 * @param ColumnSchema $column The declared column; it must carry the server's own definition line.
	 */
	public function __construct($sqlFile, $table, ColumnSchema $column, $mayLoseData = false)
	{
		parent::__construct($sqlFile, $table);

		$this->column = $column;
		$this->mayLoseData = (bool) $mayLoseData;
	}

	/**
	 * @return bool
	 */
	public function mayLoseData()
	{
		return $this->mayLoseData;
	}

	/**
	 * @return ColumnSchema
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @return string
	 */
	public function describe()
	{
		return 'Modify column `'.$this->column->getName().'`';
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
			->modifyColumnRaw($definition)
			->getSQL();
	}
}
