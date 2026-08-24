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

use e107\Database\Schema\SchemaBuilder;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * An ordered, immutable list of {@see ChangeInterface} repairs. Every operation
 * preserves the relative order of the changes it keeps.
 *
 * <code>
 * $plan = new FixPlan(array($convertTable, $addColumn));
 * $plan->count();                          // 2
 * $plan->forTable('news')->isEmpty();      // changes for one table only
 * $plan->toSqlStatements($schemaBuilder);  // ['ALTER TABLE ...', 'ALTER TABLE ...']
 * </code>
 */
final class FixPlan
{
	/** @var ChangeInterface[] zero-indexed, in application order */
	private $changes;

	/**
	 * @param ChangeInterface[] $changes In application order; keys are dropped.
	 * @throws InvalidArgumentException when an element is not a ChangeInterface.
	 */
	public function __construct(array $changes = array())
	{
		$this->changes = array();

		foreach($changes as $change)
		{
			if(!$change instanceof ChangeInterface)
			{
				throw new InvalidArgumentException(
					'FixPlan accepts ChangeInterface instances only; got '.(is_object($change) ? get_class($change) : gettype($change)).'.'
				);
			}

			$this->changes[] = $change;
		}
	}

	/**
	 * The changes, in application order.
	 *
	 * @return ChangeInterface[]
	 */
	public function getChanges()
	{
		return $this->changes;
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return count($this->changes) === 0;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->changes);
	}

	/**
	 * The distinct tables this plan touches, in the order they are first changed.
	 *
	 * @return string[]
	 */
	public function getTables()
	{
		$tables = array();

		foreach($this->changes as $change)
		{
			$table = $change->getTable();

			if(!in_array($table, $tables, true))
			{
				$tables[] = $table;
			}
		}

		return $tables;
	}

	/**
	 * The changes for one table, as a plan of their own, keeping their relative
	 * order. Table names are matched exactly.
	 *
	 * @param string $name Unprefixed logical table name.
	 * @return FixPlan
	 */
	public function forTable($name)
	{
		$name = (string) $name;
		$matched = array();

		foreach($this->changes as $change)
		{
			if((string) $change->getTable() === $name)
			{
				$matched[] = $change;
			}
		}

		return new self($matched);
	}

	/**
	 * Everything except the named tables, keeping the relative order of what is
	 * left.
	 *
	 * @param string[] $names Unprefixed logical table names, matched exactly.
	 * @return FixPlan
	 */
	public function exceptTables(array $names)
	{
		$excluded = array();

		foreach($names as $name)
		{
			$excluded[(string) $name] = true;
		}

		$kept = array();

		foreach($this->changes as $change)
		{
			if(!isset($excluded[(string) $change->getTable()]))
			{
				$kept[] = $change;
			}
		}

		return new self($kept);
	}

	/**
	 * A clone with one change appended.
	 *
	 * @param ChangeInterface $change
	 * @return FixPlan
	 */
	public function withChange(ChangeInterface $change)
	{
		$clone = clone $this;
		$clone->changes[] = $change;

		return $clone;
	}

	/**
	 * A clone with another plan's changes appended, in that plan's order.
	 *
	 * @param FixPlan $other
	 * @return FixPlan
	 */
	public function merge(FixPlan $other)
	{
		$clone = clone $this;
		$clone->changes = array_merge($this->changes, $other->getChanges());

		return $clone;
	}

	/**
	 * Render every change, in order, to a flat list of executable statements. An
	 * empty plan renders to an empty array.
	 *
	 * @param SchemaBuilder $schema
	 * @return string[] Non-empty SQL statements, without trailing semicolons.
	 * @throws UnexpectedValueException when a change renders an empty statement, an empty list, or a non-string.
	 */
	public function toSqlStatements(SchemaBuilder $schema)
	{
		$statements = array();

		foreach($this->changes as $change)
		{
			$rendered = $change->toSql($schema);

			if(!is_array($rendered))
			{
				$rendered = array($rendered);
			}

			if(count($rendered) === 0)
			{
				throw new UnexpectedValueException($this->_renderFailure($change, 'rendered no statement at all'));
			}

			foreach($rendered as $sql)
			{
				if(!is_string($sql) || trim($sql) === '')
				{
					throw new UnexpectedValueException($this->_renderFailure($change, 'rendered an empty statement'));
				}

				$statements[] = $sql;
			}
		}

		return $statements;
	}

	/**
	 * @param ChangeInterface $change
	 * @param string $what
	 * @return string
	 */
	private function _renderFailure(ChangeInterface $change, $what)
	{
		return get_class($change).' for table "'.$change->getTable().'" ('.$change->describe().') '.$what
			.'; a change must throw rather than render nothing.';
	}
}
