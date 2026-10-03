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

/**
 * One immutable, engine-neutral column of a {@see TableDefinition}, typed in the DSL's lowercased MySQL vocabulary
 * ('int', 'varchar').
 */
final class ColumnDefinition
{
	/** no DEFAULT clause */
	const DEFAULT_NONE = 'none';

	/** DEFAULT NULL */
	const DEFAULT_NULL = 'null';

	/** DEFAULT with a literal: {@see ColumnDefinition::getDefault()} holds the value as a string */
	const DEFAULT_LITERAL = 'literal';

	/** DEFAULT with an expression, e.g. CURRENT_TIMESTAMP: {@see ColumnDefinition::getDefault()} holds its SQL */
	const DEFAULT_EXPRESSION = 'expression';

	/** @var string */
	private $name;

	/** @var string lowercased base type, e.g. 'int' */
	private $type;

	/** @var string|null length or precision, e.g. '255' or '10,2' */
	private $length;

	/** @var string[] ENUM/SET members */
	private $members;

	/** @var bool */
	private $unsigned;

	/** @var bool */
	private $zerofill;

	/** @var bool */
	private $nullable;

	/** @var string one of the DEFAULT_* constants */
	private $defaultKind;

	/** @var string|null */
	private $default;

	/** @var bool */
	private $autoIncrement;

	/** @var string|null */
	private $charset;

	/** @var string|null */
	private $collation;

	/** @var string|null */
	private $comment;

	/** @var string|null ON UPDATE expression */
	private $onUpdate;

	/**
	 * @param string $name
	 * @param string $type base type; lowercased here
	 * @param array $attributes any of: length, members, unsigned, zerofill, nullable (default true), defaultKind,
	 *                          default, autoIncrement, charset, collation, comment, onUpdate
	 * @throws InvalidArgumentException on an empty name or type, or an unknown attribute
	 */
	public function __construct($name, $type, array $attributes = array())
	{
		if((string) $name === '' || (string) $type === '')
		{
			throw new InvalidArgumentException('A column definition needs a name and a type.');
		}

		$known = array('length', 'members', 'unsigned', 'zerofill', 'nullable', 'defaultKind', 'default', 'autoIncrement', 'charset', 'collation', 'comment', 'onUpdate');

		foreach(array_keys($attributes) as $key)
		{
			if(!in_array($key, $known, true))
			{
				throw new InvalidArgumentException('Unknown column attribute "'.$key.'".');
			}
		}

		$this->name = (string) $name;
		$this->type = strtolower((string) $type);
		$this->length = isset($attributes['length']) ? (string) $attributes['length'] : null;
		$this->members = isset($attributes['members']) ? array_values(array_map('strval', $attributes['members'])) : array();
		$this->unsigned = !empty($attributes['unsigned']);
		$this->zerofill = !empty($attributes['zerofill']);
		$this->nullable = array_key_exists('nullable', $attributes) ? (bool) $attributes['nullable'] : true;
		$this->defaultKind = isset($attributes['defaultKind']) ? $attributes['defaultKind'] : self::DEFAULT_NONE;
		$this->default = isset($attributes['default']) ? (string) $attributes['default'] : null;
		$this->autoIncrement = !empty($attributes['autoIncrement']);
		$this->charset = isset($attributes['charset']) ? (string) $attributes['charset'] : null;
		$this->collation = isset($attributes['collation']) ? (string) $attributes['collation'] : null;
		$this->comment = isset($attributes['comment']) ? (string) $attributes['comment'] : null;
		$this->onUpdate = isset($attributes['onUpdate']) ? (string) $attributes['onUpdate'] : null;

		if(!in_array($this->defaultKind, array(self::DEFAULT_NONE, self::DEFAULT_NULL, self::DEFAULT_LITERAL, self::DEFAULT_EXPRESSION), true))
		{
			throw new InvalidArgumentException('Unknown default kind "'.$this->defaultKind.'".');
		}
	}

	/** @return string */
	public function getName() { return $this->name; }

	/** @return string lowercased base type */
	public function getType() { return $this->type; }

	/** @return string|null */
	public function getLength() { return $this->length; }

	/** @return string[] ENUM/SET members */
	public function getMembers() { return $this->members; }

	/** @return bool */
	public function isUnsigned() { return $this->unsigned; }

	/** @return bool */
	public function isZerofill() { return $this->zerofill; }

	/** @return bool */
	public function isNullable() { return $this->nullable; }

	/** @return string one of the DEFAULT_* constants */
	public function getDefaultKind() { return $this->defaultKind; }

	/** @return string|null literal value or expression SQL, per {@see ColumnDefinition::getDefaultKind()} */
	public function getDefault() { return $this->default; }

	/** @return bool */
	public function isAutoIncrement() { return $this->autoIncrement; }

	/** @return string|null */
	public function getCharset() { return $this->charset; }

	/** @return string|null */
	public function getCollation() { return $this->collation; }

	/** @return string|null */
	public function getComment() { return $this->comment; }

	/** @return string|null */
	public function getOnUpdate() { return $this->onUpdate; }

	/**
	 * Whether the type holds whole numbers.
	 *
	 * @return bool
	 */
	public function isInteger()
	{
		return in_array($this->type, array('tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'bit', 'bool', 'boolean', 'year'), true);
	}

	/**
	 * Whether the type holds numbers: whole, fixed-point or floating-point.
	 *
	 * @return bool
	 */
	public function isNumeric()
	{
		return $this->isInteger() || in_array($this->type, array('decimal', 'dec', 'numeric', 'fixed', 'float', 'double', 'real'), true);
	}

	/**
	 * Whether the type holds text that a collation applies to.
	 *
	 * @return bool
	 */
	public function isText()
	{
		return in_array($this->type, array('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set', 'json'), true);
	}

	/**
	 * Whether the type holds raw bytes.
	 *
	 * @return bool
	 */
	public function isBinary()
	{
		return in_array($this->type, array('binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob'), true);
	}

	/**
	 * A copy under another name.
	 *
	 * @param string $name
	 * @return ColumnDefinition
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
		return array(
			'name'          => $this->name,
			'type'          => $this->type,
			'length'        => $this->length,
			'members'       => $this->members,
			'unsigned'      => $this->unsigned,
			'zerofill'      => $this->zerofill,
			'nullable'      => $this->nullable,
			'defaultKind'   => $this->defaultKind,
			'default'       => $this->default,
			'autoIncrement' => $this->autoIncrement,
			'charset'       => $this->charset,
			'collation'     => $this->collation,
			'comment'       => $this->comment,
			'onUpdate'      => $this->onUpdate,
		);
	}
}
