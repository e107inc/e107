<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema;

use e107\Database\Exception\UnsupportedException;
use e107\Database\Schema\Definition\ColumnDefinition;
use e107\Database\Schema\Definition\IndexDefinition;
use e107\Database\Schema\Definition\MysqlDdlParser;

/**
 * One change of a {@see Table} batch: the ALTER TABLE clause MySQL runs as written, and the change in the neutral
 * model, read from the clause's DSL only when first asked for.
 */
final class TableOperation
{
	const ADD_COLUMN = 'add_column';
	const MODIFY_COLUMN = 'modify_column';
	const CHANGE_COLUMN = 'change_column';
	const DROP_COLUMN = 'drop_column';
	const ADD_INDEX = 'add_index';
	const DROP_INDEX = 'drop_index';
	const DROP_PRIMARY_KEY = 'drop_primary_key';
	const SET_ENGINE = 'set_engine';
	const CONVERT_CHARSET = 'convert_charset';
	const RAW = 'raw';

	/** @var string one of the constants */
	private $type;

	/** @var string the ALTER TABLE clause in the schema DSL */
	private $clause;

	/** @var string|null the existing column the change is to, or the new column's name */
	private $column = null;

	/** @var string|null a renamed column's new name */
	private $newName = null;

	/** @var string|null DSL text of the column definition (without its name unless $named) or of the index */
	private $definition = null;

	/** @var bool whether $definition starts with the column name */
	private $named = false;

	/** @var string|null null, {@see SchemaBuilder::FIRST}, or the column to follow */
	private $after = null;

	/** @var string|null index name, engine or character set */
	private $value = null;

	/** @var ColumnDefinition|IndexDefinition|null */
	private $model = null;

	/**
	 * @param string $type
	 * @param string $clause
	 */
	private function __construct($type, $clause)
	{
		$this->type = $type;
		$this->clause = (string) $clause;
	}

	/**
	 * @param string $clause
	 * @param string $name
	 * @param string $definition column definition without the name
	 * @param string|null $after
	 * @return TableOperation
	 */
	public static function addColumn($clause, $name, $definition, $after = null)
	{
		return self::column(self::ADD_COLUMN, $clause, $name, null, $definition, false, $after);
	}

	/**
	 * @param string $clause
	 * @param string $definition column definition that starts with the column name
	 * @param string|null $after
	 * @return TableOperation
	 */
	public static function addNamedColumn($clause, $definition, $after = null)
	{
		return self::column(self::ADD_COLUMN, $clause, null, null, $definition, true, $after);
	}

	/**
	 * @param string $clause
	 * @param string $name
	 * @param string $definition column definition without the name
	 * @param string|null $after
	 * @return TableOperation
	 */
	public static function modifyColumn($clause, $name, $definition, $after = null)
	{
		return self::column(self::MODIFY_COLUMN, $clause, $name, null, $definition, false, $after);
	}

	/**
	 * @param string $clause
	 * @param string $definition column definition that starts with the column name
	 * @return TableOperation
	 */
	public static function modifyNamedColumn($clause, $definition)
	{
		return self::column(self::MODIFY_COLUMN, $clause, null, null, $definition, true, null);
	}

	/**
	 * @param string $clause
	 * @param string $oldName
	 * @param string $newName
	 * @param string $definition column definition without the name
	 * @param string|null $after
	 * @return TableOperation
	 */
	public static function changeColumn($clause, $oldName, $newName, $definition, $after = null)
	{
		return self::column(self::CHANGE_COLUMN, $clause, $oldName, $newName, $definition, false, $after);
	}

	/**
	 * @param string $clause
	 * @param string $name
	 * @return TableOperation
	 */
	public static function dropColumn($clause, $name)
	{
		$operation = new self(self::DROP_COLUMN, $clause);
		$operation->column = (string) $name;

		return $operation;
	}

	/**
	 * @param string $clause
	 * @param string $definition the index clause, e.g. "UNIQUE KEY `k` (`a`)" or "PRIMARY KEY (`id`)"
	 * @return TableOperation
	 */
	public static function addIndex($clause, $definition)
	{
		$operation = new self(self::ADD_INDEX, $clause);
		$operation->definition = (string) $definition;

		return $operation;
	}

	/**
	 * @param string $clause
	 * @param string $name
	 * @return TableOperation
	 */
	public static function dropIndex($clause, $name)
	{
		$operation = new self(self::DROP_INDEX, $clause);
		$operation->value = (string) $name;

		return $operation;
	}

	/**
	 * @param string $clause
	 * @return TableOperation
	 */
	public static function dropPrimaryKey($clause)
	{
		return new self(self::DROP_PRIMARY_KEY, $clause);
	}

	/**
	 * @param string $clause
	 * @param string $engine
	 * @return TableOperation
	 */
	public static function engine($clause, $engine)
	{
		$operation = new self(self::SET_ENGINE, $clause);
		$operation->value = (string) $engine;

		return $operation;
	}

	/**
	 * @param string $clause
	 * @param string $charset
	 * @return TableOperation
	 */
	public static function charset($clause, $charset)
	{
		$operation = new self(self::CONVERT_CHARSET, $clause);
		$operation->value = (string) $charset;

		return $operation;
	}

	/**
	 * A clause no structured verb spells; only an engine that reads the schema DSL can run it.
	 *
	 * @param string $clause
	 * @return TableOperation
	 */
	public static function raw($clause)
	{
		return new self(self::RAW, $clause);
	}

	/** @return string one of the TableOperation constants */
	public function getType() { return $this->type; }

	/** @return string the ALTER TABLE clause in the schema DSL */
	public function getClause() { return $this->clause; }

	/** @return string|null {@see SchemaBuilder::FIRST}, the column to follow, or null to keep the engine's own placement */
	public function getAfter() { return $this->after; }

	/** @return string|null the index name for DROP_INDEX, the engine or character set for SET_ENGINE and CONVERT_CHARSET */
	public function getValue() { return $this->value; }

	/**
	 * The existing column a MODIFY, CHANGE or DROP is to; for ADD, the new column.
	 *
	 * @return string|null
	 */
	public function getColumnName()
	{
		if($this->column === null && $this->named)
		{
			return $this->getColumnDefinition()->getName();
		}

		return $this->column;
	}

	/**
	 * The column as it is to be, under its new name for a CHANGE.
	 *
	 * @return ColumnDefinition
	 * @throws UnsupportedException when the definition holds what the model cannot
	 * @throws \InvalidArgumentException when the definition cannot be read
	 */
	public function getColumnDefinition()
	{
		if($this->model === null)
		{
			$parser = self::parser();

			if($this->named)
			{
				$this->model = $parser->parseNamedColumn($this->definition);
			}
			else
			{
				$this->model = $parser->parseColumn(($this->newName !== null) ? $this->newName : $this->column, $this->definition);
			}
		}

		return $this->model;
	}

	/**
	 * @return IndexDefinition the index an ADD_INDEX makes
	 * @throws UnsupportedException when the definition holds what the model cannot
	 * @throws \InvalidArgumentException when the definition cannot be read
	 */
	public function getIndexDefinition()
	{
		if($this->model === null)
		{
			$this->model = self::parser()->parseIndex($this->definition);
		}

		return $this->model;
	}

	/**
	 * @param string $type
	 * @param string $clause
	 * @param string|null $name
	 * @param string|null $newName
	 * @param string $definition
	 * @param bool $named
	 * @param string|null $after
	 * @return TableOperation
	 */
	private static function column($type, $clause, $name, $newName, $definition, $named, $after)
	{
		$operation = new self($type, $clause);
		$operation->column = ($name === null) ? null : (string) $name;
		$operation->newName = ($newName === null) ? null : (string) $newName;
		$operation->definition = (string) $definition;
		$operation->named = (bool) $named;
		$operation->after = $after;

		return $operation;
	}

	/**
	 * @return MysqlDdlParser
	 */
	private static function parser()
	{
		if(!class_exists(MysqlDdlParser::class, false))
		{
			require_once(__DIR__.'/Definition/MysqlDdlParser.php');
		}

		return new MysqlDdlParser();
	}
}
