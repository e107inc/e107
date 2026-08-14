<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Plan;

use e107\Database\Schema\Diff\TableDiff;
use e107\Database\Schema\Introspect\ColumnSchema;
use e107\Database\Schema\Introspect\TableSchema;
use e107\Database\Schema\Plan\Change\AddColumn;
use e107\Database\Schema\Plan\Change\AddIndex;
use e107\Database\Schema\Plan\Change\ConvertTable;
use e107\Database\Schema\Plan\Change\CreateTable;
use e107\Database\Schema\Plan\Change\DropIndex;
use e107\Database\Schema\Plan\Change\ModifyColumn;
use e107\Database\Schema\SchemaBuilder;
use RuntimeException;

/**
 * Turns one {@see TableDiff} into the ordered {@see FixPlan} that repairs it.
 *
 * The order is the whole of what this class knows, and it is the only order in
 * which the statements apply cleanly:
 *
 * 1. {@see CreateTable}, and nothing else - a table that does not exist has no
 *    columns to alter.
 * 2. {@see ConvertTable}: the engine, then the character set, as one change,
 *    because the index key-byte limit depends on the engine.
 * 3. {@see DropIndex} for every modified index - a key must go before its
 *    replacement can be added, and a UNIQUE that has drifted would refuse the
 *    new one outright.
 * 4. {@see AddColumn} for every missing column, in declared ordinal order, each
 *    placed after the column that precedes it in the declaration. Going in
 *    declared order is what makes those AFTER clauses resolve: a run of
 *    consecutive missing columns is added front to back, so each one's anchor
 *    exists by the time it is named.
 * 5. {@see ModifyColumn} for every modified column - after the additions, so an
 *    index added in step 6 finds every column it names - and then, when step 2
 *    converted the character set, for the long string columns that conversion
 *    has just widened ({@see PlanBuilder::_restoredStringColumns()}).
 * 6. {@see AddIndex} for the missing indexes and then the modified ones, whose
 *    declared form replaces what step 3 dropped.
 *
 * Nothing is ever dropped except an index that is being put back. Extra columns
 * and extra indexes are not drift ({@see TableDiff::hasDrift()}) and no plan
 * removes them.
 *
 * A diff with no drift plans to nothing, and an empty plan renders to zero
 * statements ({@see FixPlan::toSqlStatements()}). Between that and every change
 * throwing rather than rendering '', there is no path from "something to fix"
 * to an empty statement reaching the database (#5905, #5797).
 */
final class PlanBuilder
{
	/**
	 * @param TableDiff $diff The difference to repair.
	 * @param string $engine Resolved storage engine to convert an existing table to.
	 * @param string $charset Resolved character set to convert an existing table to.
	 * @return FixPlan
	 * @throws RuntimeException on a missing table with no declared shape, or a missing column the declared shape omits.
	 */
	public function build(TableDiff $diff, $engine, $charset)
	{
		if($diff->isMissing())
		{
			return new FixPlan(array($this->_createTable($diff)));
		}

		$changes = array();

		$convert = $this->_convertTable($diff, $engine, $charset);

		if($convert !== null)
		{
			$changes[] = $convert;
		}

		foreach($diff->getModifiedIndexes() as $indexDiff)
		{
			$changes[] = new DropIndex($diff->getSqlFile(), $diff->getTableName(), $indexDiff->getActual());
		}

		foreach($this->_placedMissingColumns($diff) as $placed)
		{
			$changes[] = new AddColumn($diff->getSqlFile(), $diff->getTableName(), $placed['column'], $placed['after']);
		}

		foreach($diff->getModifiedColumns() as $columnDiff)
		{
			$changes[] = new ModifyColumn($diff->getSqlFile(), $diff->getTableName(), $columnDiff->getExpected());
		}

		if($convert !== null && $convert->getCharset() !== null)
		{
			foreach($this->_restoredStringColumns($diff) as $column)
			{
				$changes[] = new ModifyColumn($diff->getSqlFile(), $diff->getTableName(), $column);
			}
		}

		foreach($diff->getMissingIndexes() as $index)
		{
			$changes[] = new AddIndex($diff->getSqlFile(), $diff->getTableName(), $index);
		}

		foreach($diff->getModifiedIndexes() as $indexDiff)
		{
			$changes[] = new AddIndex($diff->getSqlFile(), $diff->getTableName(), $indexDiff->getExpected());
		}

		return new FixPlan($changes);
	}

	/**
	 * @param TableDiff $diff
	 * @return CreateTable
	 * @throws RuntimeException when the diff carries no declared shape.
	 */
	private function _createTable(TableDiff $diff)
	{
		$expected = $diff->getExpectedTable();

		if(!$expected instanceof TableSchema)
		{
			throw new RuntimeException('Table `'.$diff->getTableName().'` is missing but carries no declared shape; a CREATE TABLE cannot be planned for it.');
		}

		return new CreateTable($diff->getSqlFile(), $diff->getTableName(), $expected);
	}

	/**
	 * The declared long string columns to restore after a character set
	 * conversion, in declared order.
	 *
	 * These narrow a live column, so emit them only alongside the
	 * {@see ConvertTable} that widened it in the same plan.
	 *
	 * @param TableDiff $diff
	 * @return ColumnSchema[] declared columns, in declared ordinal order.
	 */
	private function _restoredStringColumns(TableDiff $diff)
	{
		$expected = $diff->getExpectedTable();

		if(!$expected instanceof TableSchema)
		{
			return array();
		}

		$planned = array();

		foreach($diff->getMissingColumns() as $column)
		{
			$planned[$column->getName()] = true;
		}

		foreach($diff->getModifiedColumns() as $columnDiff)
		{
			$planned[$columnDiff->getExpected()->getName()] = true;
		}

		$restored = array();

		foreach($expected->getColumns() as $name => $column)
		{
			if(!isset($planned[$name]) && self::_isLongStringType($column->getColumnType()))
			{
				$restored[] = $column;
			}
		}

		return $restored;
	}

	/**
	 * Whether a COLUMN_TYPE names a text or blob family whose width a character
	 * set conversion may change.
	 *
	 * @param string $columnType
	 * @return bool
	 */
	private static function _isLongStringType($columnType)
	{
		return (bool) preg_match('/^(?:tiny|medium|long)?(?:text|blob)\b/i', trim((string) $columnType));
	}

	/**
	 * @param TableDiff $diff
	 * @param string $engine
	 * @param string $charset
	 * @return ConvertTable|null null when neither the engine nor the character set differs.
	 */
	private function _convertTable(TableDiff $diff, $engine, $charset)
	{
		$engineChange = $diff->getEngineChange();
		$charsetChange = $diff->getCharsetChange();

		if($engineChange === null && $charsetChange === null)
		{
			return null;
		}

		return new ConvertTable(
			$diff->getSqlFile(),
			$diff->getTableName(),
			($engineChange === null) ? null : self::_target($engine, $engineChange),
			($charsetChange === null) ? null : self::_target($charset, $charsetChange)
		);
	}

	/**
	 * Every missing column paired with where it belongs, in declared ordinal
	 * order.
	 *
	 * @param TableDiff $diff
	 * @return array[] each ['column' => ColumnSchema, 'after' => string|null].
	 * @throws RuntimeException when a missing column is not in the declared shape.
	 */
	private function _placedMissingColumns(TableDiff $diff)
	{
		$missing = array();

		foreach($diff->getMissingColumns() as $column)
		{
			$missing[$column->getName()] = $column;
		}

		if(count($missing) === 0)
		{
			return array();
		}

		$expected = $diff->getExpectedTable();

		if(!$expected instanceof TableSchema)
		{
			return $this->_appended($missing);
		}

		$placed = array();
		$after = SchemaBuilder::FIRST;

		foreach($expected->getColumns() as $name => $column)
		{
			if(isset($missing[$name]))
			{
				$placed[] = array('column' => $missing[$name], 'after' => $after);
				unset($missing[$name]);
			}

			$after = $name;
		}

		if(count($missing) > 0)
		{
			throw new RuntimeException(
				'Table `'.$diff->getTableName().'` reports the column(s) '.implode(', ', array_keys($missing))
				.' as missing, but its declared shape does not declare them; the diff is inconsistent and cannot be planned.'
			);
		}

		return $placed;
	}

	/**
	 * Fallback for a diff with no declared shape to order by: every column is
	 * appended, in the order the diff listed it.
	 *
	 * @param array $missing
	 * @return array[]
	 */
	private function _appended(array $missing)
	{
		$placed = array();

		foreach($missing as $column)
		{
			$placed[] = array('column' => $column, 'after' => null);
		}

		return $placed;
	}

	/**
	 * The value to convert to: the resolved intent, or the diff's expected value.
	 *
	 * @param string|null $resolved
	 * @param array $change ['expected' => string, 'actual' => string]
	 * @return string|null
	 */
	private static function _target($resolved, array $change)
	{
		if($resolved !== null && trim((string) $resolved) !== '')
		{
			return $resolved;
		}

		return $change['expected'];
	}
}
