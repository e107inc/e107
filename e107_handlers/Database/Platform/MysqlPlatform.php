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

require_once(__DIR__.'/AbstractPlatform.php');


/**
 * MySQL/MariaDB dialect, spoken by both MySQL backends ({@see e_db_pdo} with the
 * MySQL driver, and {@see e_db_mysql}).
 *
 * An SPI implementation, not an application API; see {@see PlatformInterface}.
 */
class MysqlPlatform extends AbstractPlatform
{
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
	public function quoteRegexpLiteral($value)
	{
		return addcslashes((string) $value, '.\\+*?[^]$(){}=!<>|:-#/');
	}

	/**
	 * @return string
	 */
	public function getDefaultCharset()
	{
		return 'utf8mb4';
	}

	/**
	 * MySQL limits an UPDATE with a trailing LIMIT.
	 *
	 * @inheritDoc
	 */
	public function compileUpdate($quotedTable, array $assignments, $where, $limit = null)
	{
		return 'UPDATE '.$quotedTable
			.' SET '.$this->assignmentList($assignments)
			.$where
			.$this->getLimitClause($limit);
	}

	/**
	 * MySQL limits a DELETE with a trailing LIMIT.
	 *
	 * @inheritDoc
	 */
	public function compileDelete($quotedTable, $where, $limit = null)
	{
		return 'DELETE FROM '.$quotedTable
			.$where
			.$this->getLimitClause($limit);
	}

	/**
	 * @inheritDoc
	 */
	public function compileFindInSet($needle, $quotedColumn)
	{
		return 'FIND_IN_SET('.$needle.', '.$quotedColumn.')';
	}

	/**
	 * @inheritDoc
	 */
	public function compileAutoIncrementReset($quotedTable)
	{
		return "ALTER TABLE ".$quotedTable."  AUTO_INCREMENT=1";
	}

	/**
	 * MySQL's backslash already escapes LIKE metacharacters.
	 *
	 * @inheritDoc
	 */
	public function getLikeEscapeClause()
	{
		return '';
	}

	/**
	 * @return string
	 */
	public function compileUpsert($quotedTable, array $columns, array $tuples, array $updateAssignments, array $conflictColumns = array(), $modifier = '')
	{
		return $this->compileInsert($quotedTable, $columns, $tuples, $modifier)
			.' ON DUPLICATE KEY UPDATE '.$this->assignmentList($updateAssignments);
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
	public function getSharedLockClause()
	{
		return ' LOCK IN SHARE MODE';
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
	 * MySQL reads || as OR unless PIPES_AS_CONCAT is set; CONCAT() needs no mode.
	 *
	 * @inheritDoc
	 */
	public function compileConcat(array $expressions)
	{
		return 'CONCAT('.implode(', ', $expressions).')';
	}

	/**
	 * @inheritDoc
	 */
	public function compileSubstringBefore($expression, $delimiter)
	{
		return 'SUBSTRING_INDEX('.$expression.', '.$delimiter.', 1)';
	}

	/**
	 * @inheritDoc
	 */
	public function compileCaseSensitiveLike($quotedColumn, $placeholder)
	{
		return $quotedColumn.' LIKE BINARY '.$placeholder;
	}

	/**
	 * @inheritDoc
	 */
	public function compileRemoveFromSet($quotedColumn, $placeholder)
	{
		return "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', ".$quotedColumn.", ','), CONCAT(',', ".$placeholder.", ','), ','))";
	}

	/**
	 * MySQL has no UPDATE ... FROM and, before 8.0, no ROW_NUMBER(); a user
	 * variable counts the rows instead, in the order the server reads them.
	 *
	 * @inheritDoc
	 */
	public function compileRenumber($quotedTable, $quotedColumn, $quotedKey, $startPlaceholder, $stepPlaceholder, $thresholdPlaceholder)
	{
		return 'UPDATE '.$quotedTable.' e, (SELECT @n := '.$startPlaceholder.') m  SET e.'.$quotedColumn.' = @n := @n + '.$stepPlaceholder.' WHERE '.$quotedColumn.' > '.$thresholdPlaceholder;
	}

	/**
	 * @return string
	 */
	public function compileFullText(array $quotedColumns, $placeholder, $booleanMode = false)
	{
		return 'MATCH ('.implode(', ', $quotedColumns).') AGAINST ('.$placeholder.($booleanMode ? ' IN BOOLEAN MODE' : '').')';
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

	/**
	 * @inheritDoc
	 */
	public function supportsStorageEngines()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsCharsets()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsFoundRows()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsFullTextIndexes()
	{
		return true;
	}

	/**
	 * MySQL commits implicitly at every DDL statement.
	 *
	 * @inheritDoc
	 */
	public function supportsTransactionalDdl()
	{
		return false;
	}

	/**
	 * e107 never sets NO_AUTO_VALUE_ON_ZERO in its sessions.
	 *
	 * @inheritDoc
	 */
	public function assignsAutoIncrementOnZero()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function countsConflictingRows()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function resetsAutoIncrementOnAnyTable()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function insertVerb($modifier)
	{
		if($modifier === 'IGNORE')
		{
			return 'INSERT IGNORE INTO';
		}

		return parent::insertVerb($modifier);
	}
}
