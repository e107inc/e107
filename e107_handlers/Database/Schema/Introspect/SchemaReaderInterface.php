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

use e107\Database\Exception\QueryException;

/**
 * Reads live tables into {@see TableSchema} value objects, in whatever way the engine describes them.
 *
 * Two readers of the same engine must describe the same table identically, because the schema differ compares
 * what a reader returns with no equivalence rules of its own; see
 * {@see \e107\Database\Schema\Declared\Materialiser}, which reads a declared table back through the same reader
 * that reads the live one.
 */
interface SchemaReaderInterface
{
	/**
	 * @param string $physicalTableName Prefixed table name, as {@see \e107\Database\ConnectionInterface::resolveTableName()} returns it.
	 * @return TableSchema|null null when the database has no such table.
	 * @throws QueryException when the engine cannot be asked.
	 */
	public function read($physicalTableName);

	/**
	 * @param string[] $physicalTableNames Prefixed table names, matched exactly; duplicates and empty names are ignored.
	 * @return TableSchema[] keyed by table name; tables the database does not have are absent.
	 * @throws QueryException when the engine cannot be asked.
	 */
	public function readMany(array $physicalTableNames);
}
