<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Definition;

use InvalidArgumentException;

require_once(__DIR__.'/ColumnDefinition.php');
require_once(__DIR__.'/IndexDefinition.php');

/**
 * An immutable, engine-neutral table as {@see MysqlDdlParser} reads it from the schema DSL; column and index names
 * match case-insensitively, as in MySQL.
 */
final class TableDefinition
{
	/** @var string */
	private $name;

	/** @var ColumnDefinition[] keyed by name, in declaration order */
	private $columns = array();

	/** @var IndexDefinition[] keyed by name, in declaration order */
	private $indexes = array();

	/** @var string[] lowercased column name => declared name */
	private $columnNames = array();

	/** @var string[] lowercased index name => declared name */
	private $indexNames = array();

	/** @var array lowercased option name => value, e.g. 'engine' => 'InnoDB', 'charset' => 'utf8mb4' */
	private $options;

	/**
	 * @param string $name table name as declared (no prefix)
	 * @param ColumnDefinition[] $columns
	 * @param IndexDefinition[] $indexes
	 * @param array $options lowercased option name => value
	 * @throws InvalidArgumentException on a duplicate column or index name, or an index over an unknown column
	 */
	public function __construct($name, array $columns, array $indexes = array(), array $options = array())
	{
		$this->name = (string) $name;
		$this->options = array_change_key_case($options, CASE_LOWER);

		foreach($columns as $column)
		{
			if($this->getColumn($column->getName()) !== null)
			{
				throw new InvalidArgumentException('Table "'.$name.'" declares column "'.$column->getName().'" twice.');
			}

			$this->columns[$column->getName()] = $column;
			$this->columnNames[strtolower($column->getName())] = $column->getName();
		}

		foreach($indexes as $index)
		{
			if($this->getIndex($index->getName()) !== null)
			{
				throw new InvalidArgumentException('Table "'.$name.'" declares index "'.$index->getName().'" twice.');
			}

			$index = $this->withDeclaredColumnNames($index);
			$this->indexes[$index->getName()] = $index;
			$this->indexNames[strtolower($index->getName())] = $index->getName();
		}
	}

	/** @return string */
	public function getName() { return $this->name; }

	/** @return ColumnDefinition[] keyed by name, in declaration order */
	public function getColumns() { return $this->columns; }

	/**
	 * @param string $name
	 * @return ColumnDefinition|null
	 */
	public function getColumn($name)
	{
		$name = strtolower($name);

		return isset($this->columnNames[$name]) ? $this->columns[$this->columnNames[$name]] : null;
	}

	/** @return IndexDefinition[] keyed by name, in declaration order */
	public function getIndexes() { return $this->indexes; }

	/**
	 * @param string $name
	 * @return IndexDefinition|null
	 */
	public function getIndex($name)
	{
		$name = strtolower($name);

		return isset($this->indexNames[$name]) ? $this->indexes[$this->indexNames[$name]] : null;
	}

	/**
	 * @return IndexDefinition|null
	 */
	public function getPrimaryKey()
	{
		return $this->getIndex('PRIMARY');
	}

	/** @return array lowercased option name => value */
	public function getOptions() { return $this->options; }

	/**
	 * @param string $name e.g. 'engine', 'charset', 'collate', 'comment'
	 * @return string|null
	 */
	public function getOption($name)
	{
		$name = strtolower($name);

		return isset($this->options[$name]) ? $this->options[$name] : null;
	}

	/**
	 * The column that takes an auto-increment value, if any.
	 *
	 * @return ColumnDefinition|null
	 */
	public function getAutoIncrementColumn()
	{
		foreach($this->columns as $column)
		{
			if($column->isAutoIncrement())
			{
				return $column;
			}
		}

		return null;
	}

	/**
	 * A copy under another name.
	 *
	 * @param string $name
	 * @return TableDefinition
	 */
	public function withName($name)
	{
		$copy = clone $this;
		$copy->name = (string) $name;

		return $copy;
	}

	/**
	 * @return array every attribute, for comparison and debugging
	 */
	public function toArray()
	{
		$columns = array();
		foreach($this->columns as $name => $column)
		{
			$columns[$name] = $column->toArray();
		}

		$indexes = array();
		foreach($this->indexes as $name => $index)
		{
			$indexes[$name] = $index->toArray();
		}

		return array('name' => $this->name, 'columns' => $columns, 'indexes' => $indexes, 'options' => $this->options);
	}

	/**
	 * @param IndexDefinition $index
	 * @return IndexDefinition the index with each part naming its column as the column is declared
	 * @throws InvalidArgumentException when a part names no column of the table
	 */
	private function withDeclaredColumnNames(IndexDefinition $index)
	{
		$parts = array();

		foreach($index->getParts() as $part)
		{
			$column = $this->getColumn($part['column']);

			if($column === null)
			{
				throw new InvalidArgumentException('Index "'.$index->getName().'" of table "'.$this->name.'" names unknown column "'.$part['column'].'".');
			}

			$part['column'] = $column->getName();
			$parts[] = $part;
		}

		return ($parts === $index->getParts()) ? $index : new IndexDefinition($index->getName(), $index->getKind(), $parts);
	}
}
