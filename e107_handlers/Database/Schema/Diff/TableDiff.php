<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Diff;

use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\IndexSchema;
use e107\Database\Schema\Introspect\TableSchema;
use InvalidArgumentException;

/**
 * The whole difference between one declared table and its live counterpart.
 * Immutable.
 *
 * <code>
 * $diff = new TableDiff('core', 'news', array(
 *     'expectedTable'   => $expected,
 *     'engineChange'    => array('expected' => 'InnoDB', 'actual' => 'MyISAM'),
 *     'missingColumns'  => array('news_thumbnail' => $columnSchema),
 *     'modifiedColumns' => array('news_id' => new ColumnDiff($expectedCol, $actualCol)),
 * ));
 *
 * $withExtras = $diff->withParts(array('extraColumns' => $extras));
 * </code>
 *
 * List parts come back keyed as they were supplied; {@see SchemaDiffer} keys
 * columns by column name and indexes by index name.
 */
final class TableDiff
{
	/** @var array every recognised part key, mapped to its default */
	private static $defaultParts = array(
		'missing'         => false,
		'expectedTable'   => null,
		'engineChange'    => null,
		'charsetChange'   => null,
		'missingColumns'  => array(),
		'modifiedColumns' => array(),
		'extraColumns'    => array(),
		'missingIndexes'   => array(),
		'modifiedIndexes'  => array(),
		'extraIndexes'     => array(),
		'redundantIndexes' => array(),
	);

	/** @var string */
	private $sqlFile;

	/** @var string */
	private $tableName;

	/** @var array the validated parts, always carrying every key */
	private $parts;

	/**
	 * @param string $sqlFile 'core' or the plugin folder that declared the table.
	 * @param string $tableName Unprefixed table name.
	 * @param array $parts Any subset of the recognised part keys.
	 * @throws InvalidArgumentException on an unrecognised or malformed part, or an empty table name.
	 */
	public function __construct($sqlFile, $tableName, array $parts = array())
	{
		$this->sqlFile = (string) $sqlFile;
		$this->tableName = trim((string) $tableName);

		if($this->tableName === '')
		{
			throw new InvalidArgumentException('TableDiff requires a non-empty table name.');
		}

		$this->parts = $this->_validateParts(self::$defaultParts, $parts);
	}

	/**
	 * A table that is declared but absent from the database.
	 *
	 * @param string $sqlFile
	 * @param string $tableName
	 * @param TableSchema|null $expectedTable Declared shape to render a CREATE TABLE from.
	 * @return TableDiff
	 */
	public static function missingTable($sqlFile, $tableName, $expectedTable = null)
	{
		return new self($sqlFile, $tableName, array(
			'missing'       => true,
			'expectedTable' => $expectedTable,
		));
	}

	/**
	 * A clone carrying the given parts; parts not named keep their present value.
	 *
	 * @param array $parts
	 * @return TableDiff
	 * @throws InvalidArgumentException on an unrecognised or malformed part.
	 */
	public function withParts(array $parts)
	{
		$clone = clone $this;
		$clone->parts = $this->_validateParts($this->parts, $parts);

		return $clone;
	}

	/**
	 * The unprefixed table name.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * The schema file that declared the table: 'core' or a plugin folder.
	 *
	 * @return string
	 */
	public function getSqlFile()
	{
		return $this->sqlFile;
	}

	/**
	 * Whether the table is absent from the database altogether. When true, the
	 * column and index lists are empty.
	 *
	 * @return bool
	 */
	public function isMissing()
	{
		return $this->parts['missing'];
	}

	/**
	 * @return array|null ['expected'=>string, 'actual'=>string], or null when the engine matches.
	 */
	public function getEngineChange()
	{
		return $this->parts['engineChange'];
	}

	/**
	 * @return array|null ['expected'=>string, 'actual'=>string], or null when the character set matches.
	 */
	public function getCharsetChange()
	{
		return $this->parts['charsetChange'];
	}

	/**
	 * Declared columns the live table does not have, in declared ordinal order.
	 *
	 * @return ColumnSchema[]
	 */
	public function getMissingColumns()
	{
		return $this->parts['missingColumns'];
	}

	/**
	 * @return ColumnDiff[]
	 */
	public function getModifiedColumns()
	{
		return $this->parts['modifiedColumns'];
	}

	/**
	 * Live columns nothing declares. Not drift, and never dropped by a plan.
	 *
	 * @return ColumnSchema[]
	 */
	public function getExtraColumns()
	{
		return $this->parts['extraColumns'];
	}

	/**
	 * @return IndexSchema[]
	 */
	public function getMissingIndexes()
	{
		return $this->parts['missingIndexes'];
	}

	/**
	 * @return IndexDiff[]
	 */
	public function getModifiedIndexes()
	{
		return $this->parts['modifiedIndexes'];
	}

	/**
	 * Live indexes nothing declares. Not drift, and never dropped by a plan.
	 *
	 * @return IndexSchema[]
	 */
	public function getExtraIndexes()
	{
		return $this->parts['extraIndexes'];
	}

	/**
	 * Live indexes covering the same columns as a declared index under another
	 * name. Drift, and the one thing a plan ever drops outright.
	 *
	 * @return IndexSchema[]
	 */
	public function getRedundantIndexes()
	{
		return $this->parts['redundantIndexes'];
	}

	/**
	 * Whether this table needs fixing.
	 *
	 * Extra columns and extra indexes do not count; a redundant index does.
	 *
	 * @return bool
	 */
	public function hasDrift()
	{
		return $this->parts['missing']
			|| $this->parts['engineChange'] !== null
			|| $this->parts['charsetChange'] !== null
			|| count($this->parts['missingColumns']) > 0
			|| count($this->parts['modifiedColumns']) > 0
			|| count($this->parts['missingIndexes']) > 0
			|| count($this->parts['modifiedIndexes']) > 0
			|| count($this->parts['redundantIndexes']) > 0;
	}

	/**
	 * The declared shape of the table, when one was materialised.
	 *
	 * @return TableSchema|null
	 */
	public function getExpectedTable()
	{
		return $this->parts['expectedTable'];
	}

	/**
	 * Merge $parts over $base, validating each supplied part.
	 *
	 * @param array $base
	 * @param array $parts
	 * @return array
	 * @throws InvalidArgumentException
	 */
	private function _validateParts(array $base, array $parts)
	{
		foreach($parts as $key => $value)
		{
			if(!array_key_exists($key, self::$defaultParts))
			{
				throw new InvalidArgumentException(
					'Unknown TableDiff part "'.$key.'"; expected one of: '.implode(', ', array_keys(self::$defaultParts)).'.'
				);
			}

			$base[$key] = $this->_validatePart($key, $value);
		}

		return $base;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	private function _validatePart($key, $value)
	{
		if($key === 'missing')
		{
			return (bool) $value;
		}

		if($key === 'expectedTable')
		{
			if($value !== null && !is_object($value))
			{
				throw new InvalidArgumentException('TableDiff part "expectedTable" must be a TableSchema or null.');
			}

			return $value;
		}

		if($key === 'engineChange' || $key === 'charsetChange')
		{
			if($value === null)
			{
				return null;
			}

			if(!is_array($value) || !array_key_exists('expected', $value) || !array_key_exists('actual', $value))
			{
				throw new InvalidArgumentException('TableDiff part "'.$key.'" must be null or an array with "expected" and "actual" keys.');
			}

			return array('expected' => $value['expected'], 'actual' => $value['actual']);
		}

		if(!is_array($value))
		{
			throw new InvalidArgumentException('TableDiff part "'.$key.'" must be an array.');
		}

		return $value;
	}
}
