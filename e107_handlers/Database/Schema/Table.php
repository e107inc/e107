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
use e107\Database\Exception\QueryException;
use e107\Database\Exception\UnsupportedException;
use e107\Database\Platform\PlatformInterface;
use e107\Database\SqlFragment;
use InvalidArgumentException;
use RuntimeException;

require_once(__DIR__.'/SchemaBuilderTrait.php');
require_once(__DIR__.'/TableOperation.php');

/**
 * A batch of changes to one table. Obtained from {@see SchemaBuilder::table()};
 * every verb returns $this for chaining and the accumulated changes run together,
 * all or none, when {@see Table::execute()} is called.
 *
 * The same identifier/definition guards as {@see SchemaBuilder} apply (via the
 * shared {@see SchemaBuilderTrait} trait), so a bad identifier or a bare-string
 * definition throws before any SQL is built.
 */
class Table
{
	use SchemaBuilderTrait;

	/** @var string unquoted physical table name */
	private $physicalTable;

	/** @var TableOperation[] accumulated changes */
	private $operations = array();

	/**
	 * @param ConnectionInterface $db
	 * @param PlatformInterface $platform
	 * @param string $physicalTable Already-resolved, unquoted physical table name.
	 */
	public function __construct($db, $platform, $physicalTable)
	{
		$this->db = $db;
		$this->platform = $platform;
		$this->physicalTable = (string) $physicalTable;
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
		$sql = $this->resolveColumnDefinition($definition);
		$this->operations[] = TableOperation::addColumn('ADD COLUMN '.$this->quoteColumn($name).' '.$sql.$this->_position($after), $name, $sql, $after);

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
		$sql = $this->resolveColumnDefinition($definition);
		$this->operations[] = TableOperation::modifyColumn('MODIFY COLUMN '.$this->quoteColumn($name).' '.$sql.$this->_position($after), $name, $sql, $after);

		return $this;
	}

	/**
	 * Add a column from a definition that already carries its own name, e.g. a
	 * line of the server's own SHOW CREATE TABLE output.
	 *
	 * @param SqlFragment $definition Vouched `` `name` <type> ... `` fragment.
	 * @param string|null $after Existing column, the {@see SchemaBuilder::FIRST} sentinel, or null.
	 * @return $this
	 */
	public function addColumnRaw($definition, $after = null)
	{
		$sql = $this->_vouchedDefinition($definition, 'addColumnRaw');
		$this->operations[] = TableOperation::addNamedColumn('ADD '.$sql.$this->_position($after), $sql, $after);

		return $this;
	}

	/**
	 * Redefine a column from a definition that already carries its own name, the
	 * counterpart to {@see Table::addColumnRaw()}. No placement is offered.
	 *
	 * @param SqlFragment $definition Vouched `` `name` <type> ... `` fragment.
	 * @return $this
	 */
	public function modifyColumnRaw($definition)
	{
		$sql = $this->_vouchedDefinition($definition, 'modifyColumnRaw');
		$this->operations[] = TableOperation::modifyNamedColumn('MODIFY '.$sql, $sql);

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
		$sql = $this->resolveColumnDefinition($definition);
		$this->operations[] = TableOperation::changeColumn('CHANGE COLUMN '.$this->quoteColumn($oldName).' '.$this->quoteColumn($newName).' '.$sql.$this->_position($after), $oldName, $newName, $sql, $after);

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function dropColumn($name)
	{
		$this->operations[] = TableOperation::dropColumn('DROP COLUMN '.$this->quoteColumn($name), $name);

		return $this;
	}

	/**
	 * @param Index|SqlFragment $index
	 * @return $this
	 */
	public function addIndex($index)
	{
		$sql = $this->resolveIndexDefinition($index);
		$this->operations[] = TableOperation::addIndex('ADD '.$sql, $sql);

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function dropIndex($name)
	{
		$this->operations[] = TableOperation::dropIndex('DROP INDEX '.$this->quoteColumn($name), $name);

		return $this;
	}

	/**
	 * @param string[]|string $columns
	 * @return $this
	 */
	public function addPrimaryKey($columns)
	{
		$sql = Index::primary($columns)->getDefinition();
		$this->operations[] = TableOperation::addIndex('ADD '.$sql, $sql);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function dropPrimaryKey()
	{
		$this->operations[] = TableOperation::dropPrimaryKey('DROP PRIMARY KEY');

		return $this;
	}

	/**
	 * @param string $engine
	 * @return $this
	 */
	public function engine($engine)
	{
		$engine = $this->validateEngine($engine);
		$this->operations[] = TableOperation::engine('ENGINE = '.$engine, $engine);

		return $this;
	}

	/**
	 * @param string $charset
	 * @return $this
	 */
	public function charset($charset)
	{
		$charset = $this->validateCharset($charset);
		$this->operations[] = TableOperation::charset('CONVERT TO CHARACTER SET '.$charset, $charset);

		return $this;
	}

	/**
	 * Append a vouched, already-rendered ALTER clause (the escape hatch for
	 * clauses the structured verbs cannot spell, e.g. an inline PRIMARY KEY or a
	 * FIRST placement combined with other attributes). The table identifier is
	 * still owned here, and quoted by the schema manager; only the clause body
	 * is developer-vouched, so it must never carry user input. The clause is
	 * MySQL's, so only an engine that reads the schema DSL as its own dialect
	 * can run it; another throws {@see UnsupportedException} when the batch is
	 * compiled.
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

		$this->operations[] = TableOperation::raw($clause->getSql());

		return $this;
	}

	/**
	 * Queue a change already described as a {@see TableOperation}, for a caller
	 * that keeps its own spelling of the MySQL clause.
	 *
	 * @param TableOperation $operation
	 * @return $this
	 */
	public function addOperation(TableOperation $operation)
	{
		$this->operations[] = $operation;

		return $this;
	}

	/**
	 * The statements that make the batched changes on this connection's engine,
	 * in order: one ALTER TABLE on MySQL; elsewhere possibly several, or none for
	 * a change the engine has no notion of (a storage engine on SQLite).
	 *
	 * @return string[]
	 * @throws RuntimeException when no change has been added.
	 * @throws UnsupportedException when the engine cannot make a change.
	 * @throws QueryException when the table, or a column or index a change names, is not there.
	 * @throws InvalidArgumentException when a definition cannot be read, or a column or index is added twice.
	 */
	public function getStatements()
	{
		if(count($this->operations) === 0)
		{
			throw new RuntimeException('An ALTER TABLE needs at least one change; none was added.');
		}

		return $this->db->getSchemaManager()->compileAlterTable($this->physicalTable, $this->operations);
	}

	/**
	 * The compiled statement, for a batch that compiles to exactly one, as it
	 * always does on MySQL.
	 *
	 * @return string
	 * @throws RuntimeException when no change has been added.
	 * @throws UnsupportedException when the engine needs other than one statement; see {@see Table::getStatements()}.
	 */
	public function getSQL()
	{
		$statements = $this->getStatements();

		if(count($statements) !== 1)
		{
			throw new UnsupportedException('These changes take '.count($statements).' statements on this engine; read them with getStatements().');
		}

		return reset($statements);
	}

	/**
	 * Compile and run the batched changes, several statements in one transaction.
	 *
	 * @return int|bool the {@see ConnectionInterface::execute()} result of a single statement; for several, whether they
	 *                  all ran; false, as on MySQL, for a change the table cannot take.
	 * @throws RuntimeException when no change has been added.
	 * @throws UnsupportedException when the engine cannot make a change.
	 */
	public function execute()
	{
		try
		{
			$statements = $this->getStatements();
		}
		catch(QueryException $e)
		{
			return false;
		}
		catch(InvalidArgumentException $e)
		{
			return false;
		}

		return $this->runStatements($statements);
	}

	/**
	 * A whole-column definition for one of the raw seams, which splice it after
	 * a bare ADD or MODIFY and so need it to carry its own column name.
	 *
	 * @param SqlFragment $definition
	 * @param string $method for the error message.
	 * @return string
	 * @throws InvalidArgumentException when $definition is not a vouched fragment.
	 */
	private function _vouchedDefinition($definition, $method)
	{
		if(!$definition instanceof SqlFragment)
		{
			throw new InvalidArgumentException($method.'() expects a SqlFragment carrying the whole column definition, name included (e.g. $qb->raw(...)); a Column renders no name and a bare string is not accepted.');
		}

		return $definition->getSql();
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
