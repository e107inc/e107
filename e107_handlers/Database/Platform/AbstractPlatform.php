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

use e107\Database\Exception\UnsupportedException;
use e107\Database\IdentifierFilter;

require_once(__DIR__.'/PlatformInterface.php');

/**
 * What more than one dialect spells alike; a verb only some engines have (CREATE DATABASE, GRANT) refuses here with
 * {@see UnsupportedException}.
 */
abstract class AbstractPlatform implements PlatformInterface
{
	/**
	 * @inheritDoc
	 */
	public function getIdentifierQuoteCharacter()
	{
		return '`';
	}

	/**
	 * Validates against the {@see IdentifierFilter} grammar, then quotes each part.
	 *
	 * @inheritDoc
	 */
	public function quoteIdentifier($identifier)
	{
		if(!class_exists(IdentifierFilter::class, false))
		{
			require_once(dirname(__DIR__).'/IdentifierFilter.php');
		}

		if(IdentifierFilter::identifier($identifier) === false)
		{
			return false;
		}

		return implode('.', array_map(array($this, 'quoteName'), explode('.', trim((string) $identifier))));
	}

	/**
	 * @param string $name one part of an identifier
	 * @return string the part in identifier quotes, any quote inside it doubled
	 */
	private function quoteName($name)
	{
		$quote = $this->getIdentifierQuoteCharacter();

		return $quote.str_replace($quote, $quote.$quote, $name).$quote;
	}

	/**
	 * @inheritDoc
	 */
	public function getRegexpOperator()
	{
		return 'REGEXP';
	}

	/**
	 * @inheritDoc
	 */
	public function compileReplace($quotedTable, array $columns, array $placeholders)
	{
		return 'REPLACE INTO '.$quotedTable
			.' ('.implode(', ', $columns).')'
			.' VALUES ('.implode(', ', $placeholders).')';
	}

	/**
	 * A plain INSERT; no standard modifier exists, so any is refused.
	 *
	 * @inheritDoc
	 */
	public function compileInsert($quotedTable, array $columns, array $tuples, $modifier = '')
	{
		return $this->insertVerb($modifier).' '.$quotedTable
			.' ('.implode(', ', $columns).')'
			.' VALUES '.implode(', ', $tuples);
	}

	/**
	 * @inheritDoc
	 */
	public function compileInsertSelect($quotedTable, array $columns, $selectSql, $modifier = '')
	{
		$cols = (count($columns) > 0) ? ' ('.implode(', ', $columns).')' : '';

		return $this->insertVerb($modifier).' '.$quotedTable.$cols.' '.$selectSql;
	}

	/**
	 * A plain UPDATE; a row limit has no standard spelling and is refused.
	 *
	 * @inheritDoc
	 */
	public function compileUpdate($quotedTable, array $assignments, $where, $limit = null)
	{
		$this->refuseLimit($limit, 'UPDATE');

		return 'UPDATE '.$quotedTable.' SET '.$this->assignmentList($assignments).$where;
	}

	/**
	 * A plain DELETE; a row limit has no standard spelling and is refused.
	 *
	 * @inheritDoc
	 */
	public function compileDelete($quotedTable, $where, $limit = null)
	{
		$this->refuseLimit($limit, 'DELETE');

		return 'DELETE FROM '.$quotedTable.$where;
	}

	/**
	 * @inheritDoc
	 */
	public function compileAutoIncrementReset($quotedTable)
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getForUpdateClause()
	{
		return ' FOR UPDATE';
	}

	/**
	 * Standard SQL escapes nothing in a LIKE pattern unless told which character does it.
	 *
	 * @inheritDoc
	 */
	public function getLikeEscapeClause()
	{
		return " ESCAPE '\\'";
	}

	/**
	 * @inheritDoc
	 */
	public function quoteLikeLiteral($value)
	{
		return addcslashes((string) $value, '%_\\');
	}

	/**
	 * One clause per statement, the form every engine accepts.
	 *
	 * @inheritDoc
	 */
	public function compileAlterTable($quotedTable, array $clauses)
	{
		return 'ALTER TABLE '.$quotedTable.' '.implode(', ', $clauses);
	}

	/**
	 * @inheritDoc
	 */
	public function compileCreateTable($quotedTable, array $definitions, $options = '')
	{
		return 'CREATE TABLE '.$quotedTable.' ('.implode(', ', $definitions).')'.$options;
	}

	/**
	 * @inheritDoc
	 */
	public function compileRenameTable($quotedFrom, $quotedTo)
	{
		return 'ALTER TABLE '.$quotedFrom.' RENAME TO '.$quotedTo;
	}

	/**
	 * @inheritDoc
	 */
	public function compileCreateDatabase($quotedDatabase, $charset = null)
	{
		throw new UnsupportedException(get_class($this).' has no CREATE DATABASE.');
	}

	/**
	 * @inheritDoc
	 */
	public function compileGrant($quotedDatabase, $quotedUser, $host)
	{
		throw new UnsupportedException(get_class($this).' has no GRANT.');
	}

	/**
	 * @inheritDoc
	 */
	public function compileFlushPrivileges()
	{
		throw new UnsupportedException(get_class($this).' has no FLUSH PRIVILEGES.');
	}

	/**
	 * @inheritDoc
	 */
	public function supportsStorageEngines()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsCharsets()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsFoundRows()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsFullTextIndexes()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsTransactionalDdl()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function assignsAutoIncrementOnZero()
	{
		return false;
	}

	/**
	 * @param array $assignments quoted column => value expression
	 * @return string "col = expr, ..."
	 */
	protected function assignmentList(array $assignments)
	{
		$list = array();

		foreach($assignments as $column => $expression)
		{
			$list[] = $column.' = '.$expression;
		}

		return implode(', ', $list);
	}

	/**
	 * @param string $modifier '' or a modifier this dialect does not know
	 * @return string the INSERT verb
	 * @throws UnsupportedException on any modifier
	 */
	protected function insertVerb($modifier)
	{
		if($modifier !== '')
		{
			throw new UnsupportedException(get_class($this).' has no INSERT '.$modifier.'.');
		}

		return 'INSERT INTO';
	}

	/**
	 * @param int|null $limit
	 * @param string $verb
	 * @return void
	 * @throws UnsupportedException when a limit is asked for
	 */
	private function refuseLimit($limit, $verb)
	{
		if($limit !== null)
		{
			throw new UnsupportedException(get_class($this).' cannot limit the rows of an '.$verb.'.');
		}
	}
}
