<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Minimal SQL dialect description, consulted by {@see e_db_query}.
 *
 * Deliberately small: it answers the few dialect questions the query builder
 * needs (identifier quoting, LIMIT syntax, regular-expression operator,
 * default character set) without attempting schema abstraction or a driver
 * registry. Obtain the connection's platform via {@see e_db::getPlatform()}.
 */
interface e_db_platform
{
	/**
	 * Character used to quote identifiers in this dialect.
	 *
	 * @return string
	 */
	public function getIdentifierQuoteCharacter();

	/**
	 * Build a LIMIT/OFFSET clause for this dialect, including a leading space.
	 *
	 * @param int|null $limit Maximum number of rows, or null for no limit.
	 * @param int|null $offset Number of rows to skip, or null/0 for none.
	 * @return string SQL fragment such as ' LIMIT 10 OFFSET 20', or '' when
	 *                neither a limit nor an offset is set.
	 */
	public function getLimitClause($limit, $offset = null);

	/**
	 * Operator for regular-expression matching in this dialect.
	 *
	 * @return string
	 */
	public function getRegexpOperator();

	/**
	 * Default connection character set for this dialect.
	 *
	 * @return string
	 */
	public function getDefaultCharset();

	/**
	 * Build an "insert this row, replacing any existing row that has the same
	 * primary or unique key" statement for this dialect. The table and column
	 * identifiers arrive already quoted and the values as bound placeholders,
	 * so the dialect only decides how to spell the statement (e.g. MySQL's
	 * REPLACE INTO).
	 *
	 * @param string $quotedTable Quoted physical table name.
	 * @param string[] $columns Quoted column identifiers.
	 * @param string[] $placeholders Bound-parameter placeholders, one per column.
	 * @return string SQL statement.
	 */
	public function compileReplace($quotedTable, array $columns, array $placeholders);

	/**
	 * Build an INSERT statement for one or more pre-built VALUES tuples,
	 * optionally with a modifier such as IGNORE (skip rows that collide on a
	 * key). Identifiers arrive quoted and values as bound placeholders.
	 *
	 * @param string $quotedTable Quoted physical table name.
	 * @param string[] $columns Quoted column identifiers.
	 * @param string[] $tuples Each a "(:p, :p, ...)" VALUES group, one per row.
	 * @param string $modifier '' or 'IGNORE'.
	 * @return string SQL statement.
	 */
	public function compileInsert($quotedTable, array $columns, array $tuples, $modifier = '');

	/**
	 * Build an "insert, or update the given columns on a key collision"
	 * statement (e.g. MySQL's INSERT ... ON DUPLICATE KEY UPDATE).
	 *
	 * @param string $quotedTable Quoted physical table name.
	 * @param string[] $columns Quoted column identifiers for the inserted row.
	 * @param string[] $tuples VALUES groups, one per row.
	 * @param string[] $updateAssignments "quoted column = value-reference" strings
	 *                 (see {@see e_db_platform::getUpsertValueReference()}).
	 * @return string SQL statement.
	 */
	public function compileUpsert($quotedTable, array $columns, array $tuples, array $updateAssignments);

	/**
	 * How this dialect refers, in the upsert UPDATE list, to the value that was
	 * being inserted for a column (e.g. MySQL's VALUES(`col`)).
	 *
	 * @param string $quotedColumn Quoted column identifier.
	 * @return string
	 */
	public function getUpsertValueReference($quotedColumn);

	/**
	 * Trailing clause that takes an exclusive write lock on the selected rows
	 * (e.g. ' FOR UPDATE'), including a leading space, or '' if unsupported.
	 *
	 * @return string
	 */
	public function getForUpdateClause();

	/**
	 * Trailing clause that takes a shared read lock on the selected rows,
	 * including a leading space, or '' if unsupported.
	 *
	 * @return string
	 */
	public function getSharedLockClause();

	/**
	 * Build an "INSERT ... SELECT" statement (optionally with a modifier such as
	 * IGNORE). The SELECT arrives compiled, without surrounding parentheses.
	 *
	 * @param string $quotedTable Quoted physical table name.
	 * @param string[] $columns Quoted target columns; empty to insert every column.
	 * @param string $selectSql The compiled SELECT.
	 * @param string $modifier '' or 'IGNORE'.
	 * @return string SQL statement.
	 */
	public function compileInsertSelect($quotedTable, array $columns, $selectSql, $modifier = '');

	/**
	 * Expression that selects rows at random for ORDER BY (e.g. MySQL's RAND()).
	 *
	 * @return string
	 */
	public function getRandomFunction();

	/**
	 * Wrap a column in the function that extracts a date part for comparison.
	 *
	 * @param string $part One of 'date', 'year', 'month', 'day', 'time'.
	 * @param string $quotedColumn Quoted column identifier.
	 * @return string e.g. "YEAR(`col`)".
	 * @throws InvalidArgumentException on an unknown part.
	 */
	public function compileDatePart($part, $quotedColumn);

	/**
	 * Test whether a JSON document contains a value (e.g. MySQL's JSON_CONTAINS).
	 *
	 * @param string $quotedColumn Quoted column identifier.
	 * @param string $placeholder Bound parameter holding the JSON-encoded value.
	 * @return string
	 */
	public function compileJsonContains($quotedColumn, $placeholder);

	/**
	 * Test whether a JSON document contains a path/key (e.g. MySQL's
	 * JSON_CONTAINS_PATH).
	 *
	 * @param string $quotedColumn Quoted column identifier.
	 * @param string $placeholder Bound parameter holding the JSON path.
	 * @return string
	 */
	public function compileJsonContainsKey($quotedColumn, $placeholder);

	/**
	 * Expression for the length of a JSON array/object (e.g. MySQL's
	 * JSON_LENGTH), for comparison against a bound value.
	 *
	 * @param string $quotedColumn Quoted column identifier.
	 * @return string e.g. "JSON_LENGTH(`col`)".
	 */
	public function compileJsonLength($quotedColumn);

	/**
	 * Build a full-text search predicate over one or more columns (e.g. MySQL's
	 * MATCH (...) AGAINST (...)).
	 *
	 * @param string[] $quotedColumns Quoted column identifiers.
	 * @param string $placeholder Bound parameter holding the search terms.
	 * @return string
	 */
	public function compileFullText(array $quotedColumns, $placeholder);
}


/**
 * MySQL/MariaDB dialect: the only platform e107 ships today. Both database
 * backends ({@see e_db_pdo} and {@see e_db_mysql}) speak it.
 */
class e_db_platform_mysql implements e_db_platform
{
	/**
	 * @return string
	 */
	public function getIdentifierQuoteCharacter()
	{
		return '`';
	}

	/**
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return string
	 */
	public function getLimitClause($limit, $offset = null)
	{
		if($limit === null)
		{
			if($offset === null || (int) $offset <= 0)
			{
				return '';
			}

			// MySQL has no standalone OFFSET; the manual's idiom for
			// "skip $offset rows, no upper bound" is a huge row count.
			return ' LIMIT '.(int) $offset.', 18446744073709551615';
		}

		$clause = ' LIMIT '.(int) $limit;

		if($offset !== null && (int) $offset > 0)
		{
			$clause .= ' OFFSET '.(int) $offset;
		}

		return $clause;
	}

	/**
	 * @return string
	 */
	public function getRegexpOperator()
	{
		return 'REGEXP';
	}

	/**
	 * @return string
	 */
	public function getDefaultCharset()
	{
		return 'utf8mb4';
	}

	/**
	 * @return string
	 */
	public function compileReplace($quotedTable, array $columns, array $placeholders)
	{
		return 'REPLACE INTO '.$quotedTable
			.' ('.implode(', ', $columns).')'
			.' VALUES ('.implode(', ', $placeholders).')';
	}

	/**
	 * @return string
	 */
	public function compileInsert($quotedTable, array $columns, array $tuples, $modifier = '')
	{
		$verb = ($modifier === 'IGNORE') ? 'INSERT IGNORE INTO' : 'INSERT INTO';

		return $verb.' '.$quotedTable
			.' ('.implode(', ', $columns).')'
			.' VALUES '.implode(', ', $tuples);
	}

	/**
	 * @return string
	 */
	public function compileUpsert($quotedTable, array $columns, array $tuples, array $updateAssignments)
	{
		return 'INSERT INTO '.$quotedTable
			.' ('.implode(', ', $columns).')'
			.' VALUES '.implode(', ', $tuples)
			.' ON DUPLICATE KEY UPDATE '.implode(', ', $updateAssignments);
	}

	/**
	 * @return string
	 */
	public function getUpsertValueReference($quotedColumn)
	{
		return 'VALUES('.$quotedColumn.')';
	}

	/**
	 * @return string
	 */
	public function getForUpdateClause()
	{
		return ' FOR UPDATE';
	}

	/**
	 * @return string
	 */
	public function getSharedLockClause()
	{
		return ' LOCK IN SHARE MODE';
	}

	/**
	 * @return string
	 */
	public function compileInsertSelect($quotedTable, array $columns, $selectSql, $modifier = '')
	{
		$verb = ($modifier === 'IGNORE') ? 'INSERT IGNORE INTO' : 'INSERT INTO';
		$cols = (count($columns) > 0) ? ' ('.implode(', ', $columns).')' : '';

		return $verb.' '.$quotedTable.$cols.' '.$selectSql;
	}

	/**
	 * @return string
	 */
	public function getRandomFunction()
	{
		return 'RAND()';
	}

	/**
	 * @return string
	 */
	public function compileDatePart($part, $quotedColumn)
	{
		$functions = array(
			'date'  => 'DATE',
			'year'  => 'YEAR',
			'month' => 'MONTH',
			'day'   => 'DAY',
			'time'  => 'TIME',
		);

		if(!isset($functions[$part]))
		{
			throw new InvalidArgumentException('Unknown date part: '.$part);
		}

		return $functions[$part].'('.$quotedColumn.')';
	}

	/**
	 * @return string
	 */
	public function compileJsonContains($quotedColumn, $placeholder)
	{
		return 'JSON_CONTAINS('.$quotedColumn.', '.$placeholder.')';
	}

	/**
	 * @return string
	 */
	public function compileJsonContainsKey($quotedColumn, $placeholder)
	{
		return 'JSON_CONTAINS_PATH('.$quotedColumn.", 'one', ".$placeholder.')';
	}

	/**
	 * @return string
	 */
	public function compileJsonLength($quotedColumn)
	{
		return 'JSON_LENGTH('.$quotedColumn.')';
	}

	/**
	 * @return string
	 */
	public function compileFullText(array $quotedColumns, $placeholder)
	{
		return 'MATCH ('.implode(', ', $quotedColumns).') AGAINST ('.$placeholder.')';
	}
}
