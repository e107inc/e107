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

use e107\Database\SqlFragment;
use InvalidArgumentException;

/**
 * A structured column definition for the schema builder ({@see SchemaBuilder}).
 *
 * Renders the part of a column declaration that follows the (separately quoted)
 * column name, e.g. "VARCHAR(255) NOT NULL DEFAULT ''". Every piece is built
 * from a validated grammar and a default VALUE is rendered as a safely-quoted
 * literal, so a structured column never carries un-vouched SQL text. Anything
 * the structured form cannot spell - ENUM/SET members, expression defaults,
 * exotic attributes - is authored through {@see Column::raw()} (a vouched
 * {@see SqlFragment} fragment) or {@see Column::defaultRaw()} instead, which
 * is the one explicit escape hatch and consistent with the query builder's
 * "vouched fragment or it throws" rule.
 *
 * Wherever the schema builder takes a column definition it accepts an
 * Column or a SqlFragment; a bare string is rejected.
 *
 * <code>
 * Column::define('VARCHAR', 255)->notNull()->default('');
 * // => VARCHAR(255) NOT NULL DEFAULT ''
 *
 * Column::define('INT', 10)->unsigned()->notNull()->autoIncrement();
 * // => INT(10) UNSIGNED NOT NULL AUTO_INCREMENT
 *
 * Column::raw($qb->raw("ENUM('a','b') NOT NULL DEFAULT 'a'"));
 * // => ENUM('a','b') NOT NULL DEFAULT 'a'
 * </code>
 */
final class Column
{
	/** @var string|null validated bare type token, e.g. 'VARCHAR' */
	private $type = null;

	/** @var string|null validated length/precision, e.g. '255' or '10,2' */
	private $length = null;

	/** @var bool render the UNSIGNED attribute */
	private $unsigned = false;

	/** @var bool render the ZEROFILL attribute */
	private $zerofill = false;

	/**
	 * @var bool|null true => " NULL", false => " NOT NULL", null => unspecified
	 *      (the column inherits the engine's implicit nullability).
	 */
	private $null = null;

	/** @var bool whether a DEFAULT clause is rendered at all */
	private $hasDefault = false;

	/** @var string the rendered DEFAULT token, e.g. "'0'", 'NULL', 'CURRENT_TIMESTAMP' */
	private $defaultToken = '';

	/** @var bool render AUTO_INCREMENT */
	private $autoIncrement = false;

	/** @var string|null COMMENT body (un-escaped; quoted at render time) */
	private $comment = null;

	/**
	 * @var SqlFragment|null a whole-definition vouched fragment; when set it is
	 *      emitted verbatim and every structured piece is ignored.
	 */
	private $rawDefinition = null;

	/**
	 * @param string|null $type Bare type token (validated), or null when the
	 *                          definition is supplied through {@see Column::raw()}.
	 */
	private function __construct($type = null)
	{
		if($type !== null)
		{
			$this->type = $this->_validateType($type);
		}
	}

	/**
	 * Begin a structured column definition.
	 *
	 * @param string $type Bare type token, e.g. 'INT', 'VARCHAR', 'DATETIME'.
	 *                     Validated against /^[A-Za-z0-9_]+$/; anything the bare
	 *                     grammar cannot spell (ENUM, SET, DOUBLE PRECISION)
	 *                     belongs in {@see Column::raw()}.
	 * @param int|string|null $length Optional length/precision: an integer, or a
	 *                     "digits" / "digits,digits" string. Other shapes throw.
	 * @return Column
	 * @throws InvalidArgumentException on an invalid type or length.
	 */
	public static function define($type, $length = null)
	{
		$col = new self($type);

		if($length !== null)
		{
			$col->length($length);
		}

		return $col;
	}

	/**
	 * Wrap a whole column definition authored as a vouched {@see SqlFragment}
	 * fragment (the escape hatch for definitions the structured grammar cannot
	 * spell). The fragment is emitted verbatim, so it must never carry user
	 * input.
	 *
	 * @param SqlFragment $definition
	 * @return Column
	 * @throws InvalidArgumentException when $definition is not a SqlFragment.
	 */
	public static function raw($definition)
	{
		if(!$definition instanceof SqlFragment)
		{
			throw new InvalidArgumentException('Column::raw() expects a SqlFragment (e.g. $qb->raw(...)); a bare string is not accepted.');
		}

		$col = new self();
		$col->rawDefinition = $definition;

		return $col;
	}

	/**
	 * Set the length/precision rendered as "(...)" after the type.
	 *
	 * @param int|string $length Integer, or "digits" / "digits,digits".
	 * @return $this
	 * @throws InvalidArgumentException on any other shape.
	 */
	public function length($length)
	{
		$length = (string) $length;

		if(!preg_match('/^[0-9]+(,[0-9]+)?$/D', $length))
		{
			throw new InvalidArgumentException('Invalid column length "'.$length.'": expected digits, optionally "digits,digits". Use Column::raw() for ENUM/SET and other forms.');
		}

		$this->length = $length;

		return $this;
	}

	/**
	 * Render the UNSIGNED attribute.
	 *
	 * @param bool $unsigned
	 * @return $this
	 */
	public function unsigned($unsigned = true)
	{
		$this->unsigned = (bool) $unsigned;

		return $this;
	}

	/**
	 * Render the ZEROFILL attribute.
	 *
	 * @param bool $zerofill
	 * @return $this
	 */
	public function zerofill($zerofill = true)
	{
		$this->zerofill = (bool) $zerofill;

		return $this;
	}

	/**
	 * Mark the column NOT NULL.
	 *
	 * @return $this
	 */
	public function notNull()
	{
		$this->null = false;

		return $this;
	}

	/**
	 * Mark the column explicitly NULL-able.
	 *
	 * @return $this
	 */
	public function nullable()
	{
		$this->null = true;

		return $this;
	}

	/**
	 * Set a DEFAULT from a PHP VALUE, rendered as a safely-quoted literal:
	 * null => DEFAULT NULL, int/float/bool => the bare number, string => a
	 * single-quoted literal with quotes and backslashes escaped. For an
	 * expression default (CURRENT_TIMESTAMP, a function call) use
	 * {@see Column::defaultRaw()}.
	 *
	 * @param mixed $value
	 * @return $this
	 */
	public function default($value)
	{
		$this->hasDefault = true;
		$this->defaultToken = $this->_renderDefaultValue($value);

		return $this;
	}

	/**
	 * Set a DEFAULT from a verbatim token (e.g. 'CURRENT_TIMESTAMP', a
	 * pre-quoted literal). Developer-vouched: never pass user input here.
	 *
	 * @param string $token
	 * @return $this
	 */
	public function defaultRaw($token)
	{
		$this->hasDefault = true;
		$this->defaultToken = (string) $token;

		return $this;
	}

	/**
	 * Render AUTO_INCREMENT.
	 *
	 * @param bool $autoIncrement
	 * @return $this
	 */
	public function autoIncrement($autoIncrement = true)
	{
		$this->autoIncrement = (bool) $autoIncrement;

		return $this;
	}

	/**
	 * Attach a COMMENT (quoted and escaped at render time).
	 *
	 * @param string $comment
	 * @return $this
	 */
	public function comment($comment)
	{
		$this->comment = (string) $comment;

		return $this;
	}

	/**
	 * Render the definition fragment (everything after the column name).
	 *
	 * @return string
	 * @throws InvalidArgumentException when neither a type nor a raw definition
	 *                                  was supplied.
	 */
	public function getDefinition()
	{
		if($this->rawDefinition !== null)
		{
			return $this->rawDefinition->getSql();
		}

		if($this->type === null)
		{
			throw new InvalidArgumentException('Column has no type; build it with Column::define() or Column::raw().');
		}

		$sql = $this->type;

		if($this->length !== null)
		{
			$sql .= '('.$this->length.')';
		}

		if($this->unsigned)
		{
			$sql .= ' UNSIGNED';
		}

		if($this->zerofill)
		{
			$sql .= ' ZEROFILL';
		}

		if($this->null === false)
		{
			$sql .= ' NOT NULL';
		}
		elseif($this->null === true)
		{
			$sql .= ' NULL';
		}

		if($this->hasDefault)
		{
			$sql .= ' DEFAULT '.$this->defaultToken;
		}

		if($this->autoIncrement)
		{
			$sql .= ' AUTO_INCREMENT';
		}

		if($this->comment !== null)
		{
			$sql .= " COMMENT '".$this->_escapeLiteral($this->comment)."'";
		}

		return $sql;
	}

	/**
	 * String coercion yields the rendered definition, so a column may be
	 * concatenated directly while assembling DDL.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getDefinition();
	}

	/**
	 * Validate a bare type token, fail-closed.
	 *
	 * @param string $type
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _validateType($type)
	{
		$type = trim((string) $type);

		if(!preg_match('/^[A-Za-z0-9_]+$/D', $type))
		{
			throw new InvalidArgumentException('Invalid column type "'.$type.'": expected a single type token (e.g. INT, VARCHAR). Use Column::raw() for ENUM/SET and compound types.');
		}

		return $type;
	}

	/**
	 * Render a PHP value as a DEFAULT literal token.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function _renderDefaultValue($value)
	{
		if($value === null)
		{
			return 'NULL';
		}

		if(is_bool($value))
		{
			return $value ? '1' : '0';
		}

		if(is_int($value) || is_float($value))
		{
			// (string) on a float uses '.' regardless of locale, unlike printf.
			return (string) $value;
		}

		return "'".$this->_escapeLiteral((string) $value)."'";
	}

	/**
	 * Escape a string for a single-quoted MySQL literal (DDL has no bind slot
	 * for DEFAULT/COMMENT, so the literal is built directly). Doubles the quote
	 * and escapes the backslash, which is all a DDL string literal can contain.
	 *
	 * @param string $value
	 * @return string
	 */
	private function _escapeLiteral($value)
	{
		return str_replace(array('\\', "'"), array('\\\\', "''"), $value);
	}
}
