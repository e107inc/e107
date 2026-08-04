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

use e107\Database\ConnectionInterface;
use e107\Database\Platform\PlatformInterface;
use e107\Database\SqlFragment;
use InvalidArgumentException;
use RuntimeException;

require_once(__DIR__.'/SchemaBuilderTrait.php');

/**
 * A batch of ALTER TABLE clauses targeting one table, compiled into a single
 * statement. Obtained from {@see SchemaBuilder::table()}; every verb returns $this
 * for chaining and the accumulated clauses run together when
 * {@see Table::execute()} is called.
 *
 * The same identifier/definition guards as {@see SchemaBuilder} apply (via the
 * shared {@see SchemaBuilderTrait} trait), so a bad identifier or a bare-string
 * definition throws before any SQL is built.
 */
class Table
{
	use SchemaBuilderTrait;

	/** @var string backtick-quoted physical table name */
	private $quotedTable;

	/** @var string[] accumulated ALTER clauses */
	private $clauses = array();

	/**
	 * @param ConnectionInterface $db
	 * @param PlatformInterface $platform
	 * @param string $quotedTable Already-validated, backtick-quoted table name.
	 */
	public function __construct($db, $platform, $quotedTable)
	{
		$this->db = $db;
		$this->platform = $platform;
		$this->quotedTable = $quotedTable;
	}

	/**
	 * @param string $name
	 * @param Column|SqlFragment $definition
	 * @param string|null $after Existing column, the {@see SchemaBuilder::FIRST}
	 *                    sentinel, or null.
	 * @return $this
	 */
	public function addColumn($name, $definition, $after = null)
	{
		$this->clauses[] = 'ADD COLUMN '.$this->quoteColumn($name).' '.$this->resolveColumnDefinition($definition).$this->_position($after);

		return $this;
	}

	/**
	 * @param string $name
	 * @param Column|SqlFragment $definition
	 * @param string|null $after
	 * @return $this
	 */
	public function modifyColumn($name, $definition, $after = null)
	{
		$this->clauses[] = 'MODIFY COLUMN '.$this->quoteColumn($name).' '.$this->resolveColumnDefinition($definition).$this->_position($after);

		return $this;
	}

	/**
	 * @param string $oldName
	 * @param string $newName
	 * @param Column|SqlFragment $definition
	 * @param string|null $after
	 * @return $this
	 */
	public function changeColumn($oldName, $newName, $definition, $after = null)
	{
		$this->clauses[] = 'CHANGE COLUMN '.$this->quoteColumn($oldName).' '.$this->quoteColumn($newName).' '.$this->resolveColumnDefinition($definition).$this->_position($after);

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function dropColumn($name)
	{
		$this->clauses[] = 'DROP COLUMN '.$this->quoteColumn($name);

		return $this;
	}

	/**
	 * @param Index|SqlFragment $index
	 * @return $this
	 */
	public function addIndex($index)
	{
		$this->clauses[] = 'ADD '.$this->resolveIndexDefinition($index);

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function dropIndex($name)
	{
		$this->clauses[] = 'DROP INDEX '.$this->quoteColumn($name);

		return $this;
	}

	/**
	 * @param string[]|string $columns
	 * @return $this
	 */
	public function addPrimaryKey($columns)
	{
		$this->clauses[] = 'ADD '.Index::primary($columns)->getDefinition();

		return $this;
	}

	/**
	 * @return $this
	 */
	public function dropPrimaryKey()
	{
		$this->clauses[] = 'DROP PRIMARY KEY';

		return $this;
	}

	/**
	 * @param string $engine
	 * @return $this
	 */
	public function engine($engine)
	{
		$this->clauses[] = 'ENGINE = '.$this->validateEngine($engine);

		return $this;
	}

	/**
	 * @param string $charset
	 * @return $this
	 */
	public function charset($charset)
	{
		$this->clauses[] = 'CONVERT TO CHARACTER SET '.$this->validateCharset($charset);

		return $this;
	}

	/**
	 * Append a vouched, already-rendered ALTER clause (the escape hatch for
	 * clauses the structured verbs cannot spell, e.g. an inline PRIMARY KEY or a
	 * FIRST placement combined with other attributes). The table identifier is
	 * still owned and quoted here; only the clause body is developer-vouched, so
	 * it must never carry user input.
	 *
	 * @param SqlFragment $clause
	 * @return $this
	 * @throws InvalidArgumentException when $clause is not a vouched fragment.
	 */
	public function addRaw($clause)
	{
		if(!$clause instanceof SqlFragment)
		{
			throw new InvalidArgumentException('addRaw() expects a SqlFragment (e.g. $qb->raw(...)); a bare string is not accepted.');
		}

		$this->clauses[] = $clause->getSql();

		return $this;
	}

	/**
	 * The compiled ALTER TABLE statement.
	 *
	 * @return string
	 * @throws RuntimeException when no clause has been added.
	 */
	public function getSQL()
	{
		if(count($this->clauses) === 0)
		{
			throw new RuntimeException('An ALTER TABLE needs at least one change; none was added.');
		}

		return $this->platform->compileAlterTable($this->quotedTable, $this->clauses);
	}

	/**
	 * Compile and run the batched ALTER TABLE.
	 *
	 * @return int|bool the {@see ConnectionInterface::execute()} result.
	 */
	public function execute()
	{
		return $this->db->execute($this->getSQL());
	}

	/**
	 * Render an optional column-position suffix.
	 *
	 * @param string|null $after
	 * @return string '' , ' FIRST', or ' AFTER `col`'
	 */
	private function _position($after)
	{
		if($after === null)
		{
			return '';
		}

		if($after === SchemaBuilder::FIRST)
		{
			return ' FIRST';
		}

		return ' AFTER '.$this->quoteColumn($after);
	}
}
