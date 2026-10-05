<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Result;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use e107\Shims\PdoSqlite;
use PDO;
use PDOStatement;

if(!class_exists(PdoSqlite::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/Shims/PdoSqlite.php');
}

/**
 * A statement's whole result, read at once and served from memory the way a {@see PDOStatement} serves it.
 */
final class BufferedResult implements IteratorAggregate, Countable
{
	/** @var string[] column names in select-list order */
	private $columns;

	/** @var array[] rows as positional value lists */
	private $rows;

	/** @var int index of the next row fetch() returns */
	private $cursor = 0;

	/** @var int affected rows, for a statement that returned no result set */
	private $affected;

	/**
	 * @param string[] $columns column names in select-list order; empty for a statement without a result set
	 * @param array[] $rows positional value lists
	 * @param int $affected affected rows, for a statement without a result set
	 */
	public function __construct(array $columns, array $rows, $affected = 0)
	{
		$this->columns = array_values($columns);
		$this->rows = array_values($rows);
		$this->affected = (int) $affected;
	}

	/**
	 * Read a statement's whole result and release the statement.
	 *
	 * @param PDOStatement $statement an executed statement
	 * @return BufferedResult
	 */
	public static function fromStatement(PDOStatement $statement)
	{
		$count = $statement->columnCount();

		if($count === 0)
		{
			$affected = $statement->rowCount();
			$statement->closeCursor();

			return new self(array(), array(), $affected);
		}

		$columns = array();

		for($i = 0; $i < $count; $i++)
		{
			$meta = PdoSqlite::columnMeta($statement, $i);
			$columns[] = (is_array($meta) && isset($meta['name'])) ? (string) $meta['name'] : (string) $i;
		}

		$rows = $statement->fetchAll(PDO::FETCH_NUM);
		$statement->closeCursor();

		return new self($columns, $rows);
	}

	/**
	 * Rows in the result set, or the rows a statement without one affected.
	 *
	 * @return int
	 */
	public function rowCount()
	{
		return empty($this->columns) ? $this->affected : count($this->rows);
	}

	/**
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function count()
	{
		return $this->rowCount();
	}

	/**
	 * @return int columns in the result set, 0 for a statement without one
	 */
	public function columnCount()
	{
		return count($this->columns);
	}

	/**
	 * The next row, in the shape PDOStatement::fetch() gives for the mode.
	 *
	 * @param int $mode PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH or PDO::FETCH_COLUMN
	 * @return array|string|null|false the row, or false past the last one
	 * @throws InvalidArgumentException on any other mode
	 */
	public function fetch($mode = PDO::FETCH_BOTH)
	{
		if(!isset($this->rows[$this->cursor]))
		{
			return false;
		}

		return $this->shape($this->rows[$this->cursor++], $mode);
	}

	/**
	 * One column of the next row.
	 *
	 * @param int $column zero-based column position
	 * @return string|null|false the value, or false past the last row
	 */
	public function fetchColumn($column = 0)
	{
		if(!isset($this->rows[$this->cursor]))
		{
			return false;
		}

		$row = $this->rows[$this->cursor++];

		return array_key_exists($column, $row) ? $row[$column] : false;
	}

	/**
	 * Every remaining row.
	 *
	 * @param int $mode as for {@see BufferedResult::fetch()}
	 * @return array
	 */
	public function fetchAll($mode = PDO::FETCH_BOTH)
	{
		$all = array();

		while(($row = $this->fetch($mode)) !== false)
		{
			$all[] = $row;
		}

		return $all;
	}

	/**
	 * Nothing is held open; present so callers may release a result the way they release a statement.
	 *
	 * @return bool
	 */
	public function closeCursor()
	{
		return true;
	}

	/**
	 * Iterates the remaining rows as FETCH_BOTH arrays, as iterating a PDOStatement does.
	 *
	 * @return ArrayIterator
	 */
	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		return new ArrayIterator($this->fetchAll(PDO::FETCH_BOTH));
	}

	/**
	 * @param array $row positional values
	 * @param int $mode
	 * @return array|string|null
	 */
	private function shape(array $row, $mode)
	{
		switch($mode)
		{
			case PDO::FETCH_NUM:
				return $row;

			case PDO::FETCH_COLUMN:
				return isset($row[0]) || array_key_exists(0, $row) ? $row[0] : null;

			case PDO::FETCH_ASSOC:
				$assoc = array();

				foreach($this->columns as $i => $name)
				{
					$assoc[$name] = $row[$i];
				}

				return $assoc;

			case PDO::FETCH_BOTH:
				$both = array();

				foreach($this->columns as $i => $name)
				{
					$both[$name] = $row[$i];
					$both[$i] = $row[$i];
				}

				return $both;
		}

		throw new InvalidArgumentException('BufferedResult cannot fetch in PDO mode '.$mode.'.');
	}
}
