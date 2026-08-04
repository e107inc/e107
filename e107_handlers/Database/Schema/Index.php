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

use e107\Database\IdentifierFilter;
use e107\Database\SqlFragment;
use InvalidArgumentException;

/**
 * A structured index/key definition for the schema builder ({@see SchemaBuilder}).
 *
 * Renders a key clause such as "INDEX `idx` (`a`, `b`)", "UNIQUE KEY `u` (`a`)"
 * or "PRIMARY KEY (`id`)". The index name and every column are validated against
 * the central identifier grammar ({@see IdentifierFilter::identifier()}) and quoted,
 * so an index definition never carries an un-validated identifier. Forms the
 * structured grammar cannot spell (prefix lengths, expression keys) are authored
 * through {@see Index::raw()} with a vouched {@see SqlFragment} fragment.
 *
 * Wherever the schema builder takes an index definition it accepts an
 * Index or a SqlFragment; a bare string is rejected.
 *
 * <code>
 * Index::index('user_email', array('user_email')); // INDEX `user_email` (`user_email`)
 * Index::unique('u_path', array('plugin_path'));    // UNIQUE KEY `u_path` (`plugin_path`)
 * Index::primary(array('banlist_id'));              // PRIMARY KEY (`banlist_id`)
 * </code>
 */
final class Index
{
	/** @var string one of INDEX, UNIQUE KEY, FULLTEXT KEY, PRIMARY KEY */
	private $keyword;

	/** @var string|null backtick-quoted index name, or null for PRIMARY KEY */
	private $quotedName = null;

	/** @var string[] backtick-quoted column identifiers */
	private $quotedColumns = array();

	/**
	 * @var SqlFragment|null a whole-clause vouched fragment; when set it is emitted
	 *      verbatim and the structured pieces are ignored.
	 */
	private $rawDefinition = null;

	/**
	 * @param string $keyword
	 */
	private function __construct($keyword)
	{
		$this->keyword = $keyword;
	}

	/**
	 * A non-unique index.
	 *
	 * @param string $name Index name (validated identifier).
	 * @param string[]|string $columns Column name(s).
	 * @return Index
	 * @throws InvalidArgumentException on an invalid identifier.
	 */
	public static function index($name, $columns)
	{
		return self::_named('INDEX', $name, $columns);
	}

	/**
	 * A UNIQUE index.
	 *
	 * @param string $name
	 * @param string[]|string $columns
	 * @return Index
	 * @throws InvalidArgumentException on an invalid identifier.
	 */
	public static function unique($name, $columns)
	{
		return self::_named('UNIQUE KEY', $name, $columns);
	}

	/**
	 * A FULLTEXT index.
	 *
	 * @param string $name
	 * @param string[]|string $columns
	 * @return Index
	 * @throws InvalidArgumentException on an invalid identifier.
	 */
	public static function fulltext($name, $columns)
	{
		return self::_named('FULLTEXT KEY', $name, $columns);
	}

	/**
	 * The (unnamed) PRIMARY KEY.
	 *
	 * @param string[]|string $columns
	 * @return Index
	 * @throws InvalidArgumentException on an invalid identifier.
	 */
	public static function primary($columns)
	{
		$index = new self('PRIMARY KEY');
		$index->quotedColumns = $index->_quoteColumns($columns);

		return $index;
	}

	/**
	 * Wrap a whole key clause authored as a vouched {@see SqlFragment} fragment
	 * (the escape hatch for forms the structured grammar cannot spell). Emitted
	 * verbatim, so it must never carry user input.
	 *
	 * @param SqlFragment $definition
	 * @return Index
	 * @throws InvalidArgumentException when $definition is not a SqlFragment.
	 */
	public static function raw($definition)
	{
		if(!$definition instanceof SqlFragment)
		{
			throw new InvalidArgumentException('Index::raw() expects a SqlFragment (e.g. $qb->raw(...)); a bare string is not accepted.');
		}

		$index = new self('');
		$index->rawDefinition = $definition;

		return $index;
	}

	/**
	 * The quoted index name, or null for the PRIMARY KEY (which has none).
	 * Consumed by {@see SchemaBuilder} to spell a DROP INDEX clause.
	 *
	 * @return string|null
	 */
	public function getQuotedName()
	{
		return $this->quotedName;
	}

	/**
	 * Render the key clause.
	 *
	 * @return string
	 */
	public function getDefinition()
	{
		if($this->rawDefinition !== null)
		{
			return $this->rawDefinition->getSql();
		}

		$name = ($this->quotedName !== null) ? ' '.$this->quotedName : '';

		return $this->keyword.$name.' ('.implode(', ', $this->quotedColumns).')';
	}

	/**
	 * String coercion yields the rendered clause.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getDefinition();
	}

	/**
	 * Build a named index, validating the name and columns.
	 *
	 * @param string $keyword
	 * @param string $name
	 * @param string[]|string $columns
	 * @return Index
	 * @throws InvalidArgumentException
	 */
	private static function _named($keyword, $name, $columns)
	{
		$index = new self($keyword);
		$index->quotedName = $index->_quoteIdentifier($name);
		$index->quotedColumns = $index->_quoteColumns($columns);

		return $index;
	}

	/**
	 * @param string[]|string $columns
	 * @return string[] quoted identifiers
	 * @throws InvalidArgumentException on an empty list or invalid identifier.
	 */
	private function _quoteColumns($columns)
	{
		if(!is_array($columns))
		{
			$columns = array($columns);
		}

		if(count($columns) === 0)
		{
			throw new InvalidArgumentException('An index must name at least one column.');
		}

		$quoted = array();

		foreach($columns as $column)
		{
			$quoted[] = $this->_quoteIdentifier($column);
		}

		return $quoted;
	}

	/**
	 * Validate and quote an identifier, fail-closed, via the central grammar.
	 *
	 * @param string $identifier
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _quoteIdentifier($identifier)
	{
		if(!class_exists(IdentifierFilter::class))
		{
			require_once(__DIR__.'/../IdentifierFilter.php');
		}

		$quoted = IdentifierFilter::identifier($identifier);

		if($quoted === false)
		{
			throw new InvalidArgumentException('Invalid identifier "'.$identifier.'" in index definition.');
		}

		return $quoted;
	}
}
