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

use e107\Database\ConnectionInterface;
use e107\Database\Exception\QueryException;

/**
 * Reads a live table out of information_schema and returns it as a
 * {@see TableSchema}.
 *
 * This is the single entry point through which both sides of a verify are
 * read: the live table, and the scratch table materialised from a declared
 * CREATE TABLE body. Because the same server describes both, every spelling
 * equivalence the old db_verify patched by hand - int(11) against int, utf8
 * against utf8mb3, CURRENT_TIMESTAMP() against CURRENT_TIMESTAMP - has already
 * cancelled out before a comparison is ever made. Nothing downstream of this
 * class re-interprets the server's answer.
 *
 * A read costs exactly three queries - one against TABLES, one against
 * COLUMNS, one against STATISTICS - however many tables are asked for, so a
 * whole-site verify is three round trips rather than three per table. Every
 * table name is bound; none is ever spliced into the SQL.
 *
 * The reader applies two normalisations and no others:
 *
 * - N1: a column's character set and collation are nulled when they equal the
 *   table's own default, so a table-level charset drift is reported once
 *   rather than once per column.
 * - N2: EXTRA is lowercased and MySQL 8's DEFAULT_GENERATED marker is dropped
 *   (MariaDB never emits it, and both sides of a verify come from one server,
 *   so this is for readability rather than correctness).
 *
 * A third rule would mean the two sides are not coming from the same server,
 * and the bug would be upstream of here. COLUMN_TYPE used to be lowercased as
 * well; the server has already canonicalised its keywords, so all that rule
 * achieved was folding the ENUM and SET members, which are user data.
 *
 * The reader is pure: it reports what information_schema says and asks the
 * server for nothing else. In particular it never captures DDL, so a table read
 * from here carries none, and a read stays at three queries however many tables
 * are named. Capturing the server's own CREATE TABLE text belongs to
 * {@see \e107\Database\Schema\Declared\Materialiser}, on the expected side only.
 *
 * <code>
 * $reader = new SchemaReader(e107::getDb());
 * $live = $reader->read(MPREFIX.'news');            // TableSchema, or null
 * $all = $reader->readMany(array(MPREFIX.'news', MPREFIX.'user'));
 * </code>
 */
final class SchemaReader
{
	/** @var ConnectionInterface */
	private $db;

	/**
	 * @param ConnectionInterface $db An e107 database connection, typically e107::getDb().
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @param string $physicalTableName Prefixed table name, as {@see ConnectionInterface::resolveTableName()} returns it.
	 * @return TableSchema|null null when the database has no such table.
	 * @throws QueryException when a query against information_schema fails.
	 * @throws \InvalidArgumentException when the server describes a shape no value object can hold.
	 */
	public function read($physicalTableName)
	{
		$physicalTableName = (string) $physicalTableName;

		if($physicalTableName === '')
		{
			return null;
		}

		$tables = $this->readMany(array($physicalTableName));

		return isset($tables[$physicalTableName]) ? $tables[$physicalTableName] : null;
	}

	/**
	 * Several tables in three queries.
	 *
	 * @param string[] $physicalTableNames Prefixed table names, matched exactly; duplicates and empty names are ignored.
	 * @return TableSchema[] keyed by table name, in the server's order; tables the database does not have are absent.
	 * @throws QueryException when a query against information_schema fails.
	 * @throws \InvalidArgumentException when the server describes a shape no value object can hold.
	 */
	public function readMany(array $physicalTableNames)
	{
		$wanted = self::_distinctNames($physicalTableNames);

		if(empty($wanted))
		{
			return array();
		}

		$tables = $this->_readTables($wanted);

		if(empty($tables))
		{
			return array();
		}

		$columns = $this->_readColumns($tables);
		$indexes = $this->_readIndexes($tables);

		$schemas = array();

		foreach($tables as $serverName => $meta)
		{
			$schemas[$serverName] = new TableSchema(
				$serverName,
				$meta['engine'],
				$meta['charset'],
				$meta['collation'],
				isset($columns[$serverName]) ? $columns[$serverName] : array(),
				isset($indexes[$serverName]) ? $indexes[$serverName] : array()
			);
		}

		return $schemas;
	}

	/**
	 * The engine, character set and collation of every table that exists, keyed by name.
	 *
	 * @param string[] $wanted
	 * @return array name => ['engine' => string, 'charset' => string|null, 'collation' => string|null]
	 * @throws QueryException
	 */
	private function _readTables(array $wanted)
	{
		list($placeholders, $params) = self::_bindList($wanted);

		$rows = $this->_fetchAll(
			'SELECT TABLE_NAME, ENGINE, TABLE_COLLATION'
			.' FROM information_schema.TABLES'
			.' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('.$placeholders.')'
			.' ORDER BY TABLE_NAME',
			$params,
			'TABLES'
		);

		$requested = array_flip($wanted);
		$tables = array();

		foreach($rows as $row)
		{
			$serverName = (string) $row['TABLE_NAME'];

			if(!isset($requested[$serverName]))
			{
				continue;
			}

			$collation = self::_orNull($row['TABLE_COLLATION']);

			$tables[$serverName] = array(
				'engine'    => (string) $row['ENGINE'],
				'charset'   => self::_charsetOf($collation),
				'collation' => $collation,
			);
		}

		return $tables;
	}

	/**
	 * Every column of every named table, in ordinal order.
	 *
	 * @param array $tables as {@see SchemaReader::_readTables()} returns.
	 * @return array tableName => ColumnSchema[]
	 * @throws QueryException
	 */
	private function _readColumns(array $tables)
	{
		list($placeholders, $params) = self::_bindList(array_keys($tables));

		$rows = $this->_fetchAll(
			'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA,'
			.' CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_COMMENT, ORDINAL_POSITION'
			.' FROM information_schema.COLUMNS'
			.' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('.$placeholders.')'
			.' ORDER BY TABLE_NAME, ORDINAL_POSITION',
			$params,
			'COLUMNS'
		);

		$columns = array();

		foreach($rows as $row)
		{
			$serverName = (string) $row['TABLE_NAME'];

			if(!isset($tables[$serverName]))
			{
				continue;
			}

			$columns[$serverName][] = new ColumnSchema(
				$row['COLUMN_NAME'],
				$row['COLUMN_TYPE'],
				strcasecmp((string) $row['IS_NULLABLE'], 'YES') === 0,
				$row['COLUMN_DEFAULT'],
				self::_normaliseExtra($row['EXTRA']),
				self::_unlessTableDefault($row['CHARACTER_SET_NAME'], $tables[$serverName]['charset']),
				self::_unlessTableDefault($row['COLLATION_NAME'], $tables[$serverName]['collation']),
				$row['COLUMN_COMMENT'],
				$row['ORDINAL_POSITION']
			);
		}

		return $columns;
	}

	/**
	 * Every index of every named table, its parts in SEQ_IN_INDEX order.
	 *
	 * A part the server reports without a column name is recorded as indexing an
	 * expression ({@see IndexPart::isOverExpression()}) rather than skipped.
	 *
	 * @param array $tables as {@see SchemaReader::_readTables()} returns.
	 * @return array tableName => IndexSchema[]
	 * @throws QueryException
	 */
	private function _readIndexes(array $tables)
	{
		list($placeholders, $params) = self::_bindList(array_keys($tables));

		$rows = $this->_fetchAll(
			'SELECT TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX, COLUMN_NAME, COLLATION, SUB_PART,'
			.' NON_UNIQUE, INDEX_TYPE'
			.' FROM information_schema.STATISTICS'
			.' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('.$placeholders.')'
			.' ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX',
			$params,
			'STATISTICS'
		);

		$grouped = array();

		foreach($rows as $row)
		{
			$serverName = (string) $row['TABLE_NAME'];

			if(!isset($tables[$serverName]))
			{
				continue;
			}

			$indexName = (string) $row['INDEX_NAME'];

			if(!isset($grouped[$serverName][$indexName]))
			{
				$grouped[$serverName][$indexName] = array(
					'kind'  => self::_indexKind($indexName, $row['INDEX_TYPE'], $row['NON_UNIQUE']),
					'parts' => array(),
				);
			}

			$overExpression = ($row['COLUMN_NAME'] === null || $row['COLUMN_NAME'] === '');

			$grouped[$serverName][$indexName]['parts'][] = new IndexPart(
				$overExpression ? null : $row['COLUMN_NAME'],
				$row['SUB_PART'],
				$row['COLLATION'],
				$overExpression
			);
		}

		$indexes = array();

		foreach($grouped as $serverName => $tableIndexes)
		{
			foreach($tableIndexes as $indexName => $index)
			{
				$indexes[$serverName][] = new IndexSchema($indexName, $index['kind'], $index['parts']);
			}
		}

		return $indexes;
	}

	/**
	 * Run one bound statement and drain its rows.
	 *
	 * @param string $sql
	 * @param array $params
	 * @param string $source information_schema view name, for the error message.
	 * @return array[] associative rows.
	 * @throws QueryException when the connection reports an error.
	 */
	private function _fetchAll($sql, array $params, $source)
	{
		if($this->db->execute($sql, $params) === false)
		{
			throw new QueryException(
				'Could not read information_schema.'.$source.': '.$this->db->getLastErrorText()
			);
		}

		$rows = array();

		while($row = $this->db->fetch())
		{
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * A comma-separated list of named placeholders and the parameters that fill it.
	 *
	 * @param string[] $names
	 * @return array [placeholder list, name => value]
	 */
	private static function _bindList(array $names)
	{
		$placeholders = array();
		$params = array();
		$slot = 0;

		foreach($names as $name)
		{
			$placeholders[] = ':t'.$slot;
			$params['t'.$slot] = (string) $name;
			$slot++;
		}

		return array(implode(', ', $placeholders), $params);
	}

	/**
	 * The given names with empties dropped and duplicates collapsed, in the order given.
	 *
	 * @param array $names
	 * @return string[]
	 */
	private static function _distinctNames(array $names)
	{
		$distinct = array();

		foreach($names as $name)
		{
			if(is_array($name) || is_object($name))
			{
				continue;
			}

			$name = (string) $name;

			if($name !== '')
			{
				$distinct[$name] = true;
			}
		}

		return array_keys($distinct);
	}

	/**
	 * The character set a collation belongs to: everything before its first underscore.
	 *
	 * @param string|null $collation
	 * @return string|null
	 */
	private static function _charsetOf($collation)
	{
		if($collation === null)
		{
			return null;
		}

		$underscore = strpos($collation, '_');

		return ($underscore === false) ? $collation : (string) substr($collation, 0, $underscore);
	}

	/**
	 * A column's character set or collation, or null when it equals the table's own default.
	 *
	 * @param string|null $value
	 * @param string|null $tableDefault
	 * @return string|null
	 */
	private static function _unlessTableDefault($value, $tableDefault)
	{
		$value = self::_orNull($value);

		if($value === null || ($tableDefault !== null && strcasecmp($value, $tableDefault) === 0))
		{
			return null;
		}

		return $value;
	}

	/**
	 * EXTRA lowercased, with the DEFAULT_GENERATED marker removed and the words single-spaced.
	 *
	 * @param string|null $extra
	 * @return string
	 */
	private static function _normaliseExtra($extra)
	{
		$extra = strtolower((string) $extra);
		$extra = preg_replace('/\bdefault_generated\b/', ' ', $extra);

		return trim(preg_replace('/\s+/', ' ', $extra));
	}

	/**
	 * The kind of an index, from the row group information_schema.STATISTICS gives it.
	 *
	 * @param string $indexName
	 * @param string $indexType INDEX_TYPE, e.g. 'BTREE', 'FULLTEXT', 'SPATIAL'.
	 * @param mixed $nonUnique NON_UNIQUE.
	 * @return string one of the {@see IndexSchema} KIND_* constants.
	 */
	private static function _indexKind($indexName, $indexType, $nonUnique)
	{
		if($indexName === IndexSchema::KIND_PRIMARY)
		{
			return IndexSchema::KIND_PRIMARY;
		}

		$indexType = strtoupper(trim((string) $indexType));

		if($indexType === IndexSchema::KIND_FULLTEXT || $indexType === IndexSchema::KIND_SPATIAL)
		{
			return $indexType;
		}

		return ((int) $nonUnique === 0) ? IndexSchema::KIND_UNIQUE : IndexSchema::KIND_INDEX;
	}

	/**
	 * @param mixed $value
	 * @return string|null null for both SQL NULL and the empty string.
	 */
	private static function _orNull($value)
	{
		if($value === null || $value === '')
		{
			return null;
		}

		return (string) $value;
	}
}
