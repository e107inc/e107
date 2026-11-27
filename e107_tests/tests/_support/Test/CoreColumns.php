<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * Core table columns a unit test adds where the suite's database lacks them, and drops again.
 */
trait CoreColumns
{
	/** @var string[][] the columns this test added, by table */
	private $addedCoreColumns = array();

	/**
	 * Adds whichever of these columns the table lacks, as {@see CoreTable::addColumns()} does; call {@see CoreColumns::dropCoreColumns()} from _after().
	 *
	 * @param string $table without the table prefix
	 * @param string[] $columns
	 * @return void
	 */
	protected function haveCoreColumns($table, array $columns)
	{
		$added = CoreTable::addColumns($table, $columns);

		if($added)
		{
			$this->addedCoreColumns[$table] = array_merge(isset($this->addedCoreColumns[$table]) ? $this->addedCoreColumns[$table] : array(), $added);
		}
	}

	/**
	 * @return void
	 */
	protected function dropCoreColumns()
	{
		foreach($this->addedCoreColumns as $table => $columns)
		{
			CoreTable::dropColumns($table, $columns);
		}

		$this->addedCoreColumns = array();
	}
}
