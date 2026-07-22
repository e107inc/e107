<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Platform;

use InvalidArgumentException;

require_once(__DIR__.'/PlatformInterface.php');


/**
 * MySQL/MariaDB dialect: the only platform e107 ships today. Both database
 * backends ({@see e_db_pdo} and {@see e_db_mysql}) speak it.
 */
class MysqlPlatform implements PlatformInterface
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

	/**
	 * @return string
	 */
	public function compileGroupConcat($quotedExpression, array $quotedOrderBy, $separatorLiteral, $distinct = false)
	{
		$sql = 'GROUP_CONCAT('.($distinct ? 'DISTINCT ' : '').$quotedExpression;

		if(!empty($quotedOrderBy))
		{
			$sql .= ' ORDER BY '.implode(', ', $quotedOrderBy);
		}

		return $sql.' SEPARATOR '.$separatorLiteral.')';
	}

	/**
	 * @return string
	 */
	public function compileAlterTable($quotedTable, array $clauses)
	{
		return 'ALTER TABLE '.$quotedTable.' '.implode(', ', $clauses);
	}

	/**
	 * @return string
	 */
	public function compileCreateTable($quotedTable, array $definitions, $options = '')
	{
		return 'CREATE TABLE '.$quotedTable.' ('.implode(', ', $definitions).')'.$options;
	}

	/**
	 * @return string
	 */
	public function compileRenameTable($quotedFrom, $quotedTo)
	{
		return 'RENAME TABLE '.$quotedFrom.' TO '.$quotedTo;
	}

	/**
	 * @return string
	 */
	public function compileOptimizeTable(array $quotedTables)
	{
		return 'OPTIMIZE TABLE '.implode(', ', $quotedTables);
	}

	/**
	 * @return string
	 */
	public function compileCreateDatabase($quotedDatabase, $charset = null)
	{
		$sql = 'CREATE DATABASE '.$quotedDatabase;

		if($charset !== null)
		{
			$sql .= ' CHARACTER SET '.$charset;
		}

		return $sql;
	}

	/**
	 * @return string
	 */
	public function compileGrant($quotedDatabase, $quotedUser, $host)
	{
		return 'GRANT ALL ON '.$quotedDatabase.'.* TO '.$quotedUser."@'".$host."'";
	}

	/**
	 * @return string
	 */
	public function compileFlushPrivileges()
	{
		return 'FLUSH PRIVILEGES';
	}
}
