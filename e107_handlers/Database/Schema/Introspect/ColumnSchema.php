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

/**
 * One column of a table, exactly as the server describes it in
 * information_schema.COLUMNS. Immutable.
 *
 * {@see ColumnSchema::getColumnType()} is one opaque token, carrying the base
 * type, the length or precision, UNSIGNED, ZEROFILL and the ENUM or SET members
 * together. Only a column materialised by
 * {@see \e107\Database\Schema\Declared\Materialiser} carries DDL; one read from
 * a live table leaves it null, and it takes no part in
 * {@see ColumnSchema::equals()}.
 *
 * <code>
 * new ColumnSchema('user_id', 'int(10) unsigned', false, null, 'auto_increment', null, null, '', 1);
 * </code>
 */
final class ColumnSchema
{
	/** @var string COLUMN_NAME, verbatim */
	private $name;

	/** @var string COLUMN_TYPE, verbatim, e.g. 'int(10) unsigned', "enum('Yes','No')" */
	private $columnType;

	/** @var bool IS_NULLABLE === 'YES' */
	private $nullable;

	/** @var string|null COLUMN_DEFAULT, verbatim; null when the column has no default */
	private $default;

	/** @var string EXTRA, lowercased, e.g. 'auto_increment', 'on update current_timestamp()' */
	private $extra;

	/** @var string|null CHARACTER_SET_NAME, null when it equals the table default */
	private $charset;

	/** @var string|null COLLATION_NAME, null when it equals the table default */
	private $collation;

	/** @var string COLUMN_COMMENT, '' when absent */
	private $comment;

	/** @var int ORDINAL_POSITION, 1-based; never part of {@see ColumnSchema::equals()} */
	private $position;

	/** @var string|null the server's own definition line; never part of {@see ColumnSchema::equals()} */
	private $ddl;

	/**
	 * @param string $name COLUMN_NAME.
	 * @param string $columnType COLUMN_TYPE as one opaque token, kept verbatim.
	 * @param bool $nullable IS_NULLABLE === 'YES'.
	 * @param string|null $default COLUMN_DEFAULT, kept verbatim, the server's own quoting included.
	 * @param string $extra EXTRA, lowercased on the way in.
	 * @param string|null $charset CHARACTER_SET_NAME, or null when it is the table default.
	 * @param string|null $collation COLLATION_NAME, or null when it is the table default.
	 * @param string $comment COLUMN_COMMENT, '' when absent.
	 * @param int $position ORDINAL_POSITION.
	 * @param string|null $ddl This column's line from SHOW CREATE TABLE, without
	 *                           the trailing comma. Null on the live side, where
	 *                           nothing renders from it.
	 */
	public function __construct($name, $columnType, $nullable, $default, $extra, $charset, $collation, $comment, $position, $ddl = null)
	{
		$this->name = (string) $name;
		$this->columnType = trim((string) $columnType);
		$this->nullable = (bool) $nullable;
		$this->default = ($default === null) ? null : (string) $default;
		$this->extra = strtolower(trim((string) $extra));
		$this->charset = ($charset === null) ? null : (string) $charset;
		$this->collation = ($collation === null) ? null : (string) $collation;
		$this->comment = (string) $comment;
		$this->position = (int) $position;
		$this->ddl = ($ddl === null) ? null : (string) $ddl;
	}

	/**
	 * This column with the server's own definition line attached.
	 *
	 * @param string|null $ddl
	 * @return ColumnSchema a copy; this object is immutable.
	 */
	public function withDdl($ddl)
	{
		return new self(
			$this->name,
			$this->columnType,
			$this->nullable,
			$this->default,
			$this->extra,
			$this->charset,
			$this->collation,
			$this->comment,
			$this->position,
			$ddl
		);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * The whole type token, e.g. 'int(10) unsigned' or "enum('a','b')".
	 *
	 * @return string
	 */
	public function getColumnType()
	{
		return $this->columnType;
	}

	/**
	 * @return bool
	 */
	public function isNullable()
	{
		return $this->nullable;
	}

	/**
	 * @return string|null
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * @return string
	 */
	public function getExtra()
	{
		return $this->extra;
	}

	/**
	 * @return string|null null means "the table default", not "none".
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * @return string|null null means "the table default", not "none".
	 */
	public function getCollation()
	{
		return $this->collation;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * This column's own line of the server's SHOW CREATE TABLE output, without
	 * the trailing comma, ready to be spliced into an ADD COLUMN or a MODIFY.
	 *
	 * @return string|null null when the column was read from a live table rather than materialised.
	 */
	public function getDdl()
	{
		return $this->ddl;
	}

	/**
	 * Value equality over every field except {@see ColumnSchema::$position} and
	 * {@see ColumnSchema::$ddl}.
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

		return $this->name === $other->name
			&& $this->columnType === $other->columnType
			&& $this->nullable === $other->nullable
			&& $this->default === $other->default
			&& $this->extra === $other->extra
			&& $this->charset === $other->charset
			&& $this->collation === $other->collation
			&& $this->comment === $other->comment;
	}

	/**
	 * Every identifying field, in constructor order, keyed by property name.
	 * Includes position, which {@see ColumnSchema::equals()} ignores, and omits
	 * the DDL, which is derived rather than identifying and is reached through
	 * {@see ColumnSchema::getDdl()}.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'name'       => $this->name,
			'columnType' => $this->columnType,
			'nullable'   => $this->nullable,
			'default'    => $this->default,
			'extra'      => $this->extra,
			'charset'    => $this->charset,
			'collation'  => $this->collation,
			'comment'    => $this->comment,
			'position'   => $this->position,
		);
	}
}
