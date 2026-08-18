<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Introspect;

use InvalidArgumentException;

/**
 * One table's whole shape, as the server describes it: engine, character set,
 * collation, columns and indexes. Immutable.
 *
 * Compare the engine with {@see TableSchema::hasEngine()} rather than with the
 * getter's value, which is spelled as the server canonicalises it. A table
 * materialised by {@see \e107\Database\Schema\Declared\Materialiser} also
 * carries the server's own CREATE TABLE text, split into
 * {@see TableSchema::getCreateBody()} and {@see TableSchema::getCreateOptions()};
 * a table read from a live database leaves both null, and neither takes any part
 * in {@see TableSchema::equals()}.
 */
final class TableSchema
{
	/** @var string physical table name, prefix included */
	private $name;

	/** @var string ENGINE as the server canonicalises it; compared case-insensitively */
	private $engine;

	/** @var string|null character set, derived from TABLE_COLLATION */
	private $charset;

	/** @var string|null TABLE_COLLATION */
	private $collation;

	/** @var ColumnSchema[] keyed by column name, in ordinal order */
	private $columns;

	/** @var IndexSchema[] keyed by index name */
	private $indexes;

	/** @var string|null everything between the outer parentheses of SHOW CREATE TABLE */
	private $createBody;

	/** @var string|null everything after them, less any AUTO_INCREMENT counter */
	private $createOptions;

	/**
	 * @param string $name Physical table name, prefix included.
	 * @param string $engine ENGINE, stored verbatim.
	 * @param string|null $charset Character set, or null when unknown.
	 * @param string|null $collation TABLE_COLLATION, or null when unknown.
	 * @param ColumnSchema[] $columns In ordinal order; re-keyed by column name, order preserved.
	 * @param IndexSchema[] $indexes Re-keyed by index name.
	 * @param string|null $createBody The server's own column and key block, null unless this table was materialised.
	 * @param string|null $createOptions The trailing table options, null unless this table was materialised.
	 * @throws InvalidArgumentException on an empty table name, a member of the wrong type, or a duplicate name.
	 */
	public function __construct($name, $engine, $charset, $collation, array $columns, array $indexes, $createBody = null, $createOptions = null)
	{
		$name = (string) $name;

		if($name === '')
		{
			throw new InvalidArgumentException('A table schema must have a name.');
		}

		$this->name = $name;
		$this->engine = (string) $engine;
		$this->charset = ($charset === null) ? null : (string) $charset;
		$this->collation = ($collation === null) ? null : (string) $collation;
		$this->columns = self::_keyByName($columns, ColumnSchema::class, 'column', $name);
		$this->indexes = self::_keyByName($indexes, IndexSchema::class, 'index', $name);
		$this->createBody = ($createBody === null) ? null : (string) $createBody;
		$this->createOptions = ($createOptions === null) ? null : (string) $createOptions;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string as the server canonicalises it; compare with {@see TableSchema::hasEngine()}, not with ===.
	 */
	public function getEngine()
	{
		return $this->engine;
	}

	/**
	 * @return string|null
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * @return string|null
	 */
	public function getCollation()
	{
		return $this->collation;
	}

	/**
	 * @return ColumnSchema[] keyed by column name, in ordinal order.
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * @param string $name
	 * @return ColumnSchema|null null when the table has no such column.
	 */
	public function getColumn($name)
	{
		$name = (string) $name;

		return isset($this->columns[$name]) ? $this->columns[$name] : null;
	}

	/**
	 * @return IndexSchema[] keyed by index name.
	 */
	public function getIndexes()
	{
		return $this->indexes;
	}

	/**
	 * @param string $name
	 * @return IndexSchema|null null when the table has no such index.
	 */
	public function getIndex($name)
	{
		$name = (string) $name;

		return isset($this->indexes[$name]) ? $this->indexes[$name] : null;
	}

	/**
	 * The server's own column and key block: everything between the outer
	 * parentheses of SHOW CREATE TABLE, verbatim.
	 *
	 * @return string|null null when this table was read from a live database rather than materialised.
	 */
	public function getCreateBody()
	{
		return $this->createBody;
	}

	/**
	 * The trailing table options as one string, ready to follow the closing
	 * parenthesis, with any AUTO_INCREMENT counter already removed.
	 *
	 * @return string|null null for the same reason as {@see TableSchema::getCreateBody()}.
	 */
	public function getCreateOptions()
	{
		return $this->createOptions;
	}

	/**
	 * Whether this table runs on the named engine, compared case-insensitively.
	 *
	 * @param string|null $engine
	 * @return bool
	 */
	public function hasEngine($engine)
	{
		if($engine === null)
		{
			return false;
		}

		return strcasecmp($this->engine, (string) $engine) === 0;
	}

	/**
	 * Value equality over the name, the engine (case-insensitively), the character
	 * set, the collation, and every column and index by name rather than by slot.
	 * The captured CREATE TABLE text takes no part.
	 *
	 * @param mixed $other
	 * @return bool
	 */
	public function equals($other)
	{
		if(!$other instanceof self)
		{
			return false;
		}

		if($this->name !== $other->name || !$this->hasEngine($other->engine))
		{
			return false;
		}

		if($this->charset !== $other->charset || $this->collation !== $other->collation)
		{
			return false;
		}

		return self::_membersEqual($this->columns, $other->columns)
			&& self::_membersEqual($this->indexes, $other->indexes);
	}

	/**
	 * Every identifying field, with columns and indexes as nested arrays under the
	 * same keys they carry here. The captured CREATE TABLE text is omitted.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$columns = array();

		foreach($this->columns as $columnName => $column)
		{
			$columns[$columnName] = $column->toArray();
		}

		$indexes = array();

		foreach($this->indexes as $indexName => $index)
		{
			$indexes[$indexName] = $index->toArray();
		}

		return array(
			'name'      => $this->name,
			'engine'    => $this->engine,
			'charset'   => $this->charset,
			'collation' => $this->collation,
			'columns'   => $columns,
			'indexes'   => $indexes,
		);
	}

	/**
	 * Re-key a list of members by their own getName(), preserving order.
	 *
	 * @param array $members
	 * @param string $class fully qualified class every member must be.
	 * @param string $label 'column' or 'index', for the error message.
	 * @param string $table for the error message.
	 * @return array
	 * @throws InvalidArgumentException
	 */
	private static function _keyByName(array $members, $class, $label, $table)
	{
		$keyed = array();

		foreach($members as $member)
		{
			if(!$member instanceof $class)
			{
				throw new InvalidArgumentException('Table "'.$table.'" was given a '.$label.' that is not a '.$class.'.');
			}

			$memberName = $member->getName();

			if(isset($keyed[$memberName]))
			{
				throw new InvalidArgumentException('Table "'.$table.'" was given the '.$label.' "'.$memberName.'" twice.');
			}

			$keyed[$memberName] = $member;
		}

		return $keyed;
	}

	/**
	 * Whether two name-keyed member maps hold the same names with equal values, in any order.
	 *
	 * @param array $mine
	 * @param array $theirs
	 * @return bool
	 */
	private static function _membersEqual(array $mine, array $theirs)
	{
		if(count($mine) !== count($theirs))
		{
			return false;
		}

		foreach($mine as $memberName => $member)
		{
			if(!isset($theirs[$memberName]) || !$member->equals($theirs[$memberName]))
			{
				return false;
			}
		}

		return true;
	}
}
