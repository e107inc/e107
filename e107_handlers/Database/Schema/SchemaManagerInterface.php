<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema;

use e107\Database\ConnectionInterface;
use e107\Database\Schema\Introspect\SchemaReaderInterface;

/**
 * The engine-specific schema work of one connection: what tables exist, how a table is described, and the
 * structural operations whose spelling depends on what is already there.
 *
 * Obtain it with {@see ConnectionInterface::getSchemaManager()}; the connection's driver decides the class. A
 * manager runs its statements on the connection it was made for, so a read replaces that connection's current
 * result set.
 *
 * Table arguments are physical names (prefix included), optionally qualified with a second database the way the
 * connection's own prefix qualifies it after {@see ConnectionInterface::database()} with $multiple set.
 *
 * {@see SchemaManagerInterface::getColumnRows()} and {@see SchemaManagerInterface::getIndexRows()} answer in the
 * shape of MySQL's SHOW COLUMNS and SHOW INDEX rows on every engine.
 */
interface SchemaManagerInterface
{
	/**
	 * @return SchemaReaderInterface reader for this connection's engine
	 */
	public function getReader();

	/**
	 * Tables whose name starts with a prefix.
	 *
	 * @param string $prefix literal name prefix (no wildcards), optionally database-qualified
	 * @return string[]|false matching physical table names, without any database qualifier; false on error
	 */
	public function listTableNames($prefix);

	/**
	 * A table's columns as SHOW COLUMNS rows: Field, Type, Null ('YES'/'NO'), Key ('PRI'/'UNI'/'MUL'/''),
	 * Default, Extra.
	 *
	 * @param string $table
	 * @return array[]|false one row per column in table order, or false on error
	 */
	public function getColumnRows($table);

	/**
	 * A table's indexes as SHOW INDEX rows, one per indexed column: at least Table, Non_unique, Key_name,
	 * Seq_in_index, Column_name, Sub_part, Index_type; the primary key is named 'PRIMARY'.
	 *
	 * @param string $table
	 * @return array[]|false or false on error
	 */
	public function getIndexRows($table);

	/**
	 * The statement, in the engine's own dialect, that creates the table as it stands.
	 *
	 * @param string $table
	 * @return string|null null when the table cannot be described
	 */
	public function getCreateStatement($table);

	/**
	 * Create a table with the structure (columns, keys, indexes) of another, without its rows.
	 *
	 * @param string $from existing table
	 * @param string $to table to create; must not exist
	 * @return bool
	 */
	public function createTableLike($from, $to);

	/**
	 * Remove every row of a table and start its auto-increment counter again.
	 *
	 * @param string $table
	 * @return int|bool what the connection returned for the emptying statement; false on error
	 */
	public function truncateTable($table);

	/**
	 * The statements that create a table from e107's schema DSL: column definitions with their names and key
	 * clauses as core_sql.php and plugin *_sql.php files write them, and MySQL table options.
	 *
	 * @param string $table physical name of the table to create
	 * @param string[] $definitions column and key definitions in the schema DSL
	 * @param string $options table options in the schema DSL with a leading space, e.g. ' ENGINE=InnoDB', or ''
	 * @return string[] the statements, in order
	 * @throws \e107\Database\Exception\UnsupportedException when a definition holds what the engine cannot build
	 */
	public function compileCreateTable($table, array $definitions, $options = '');

	/**
	 * The statements that rename a table, in order: on MySQL one RENAME TABLE; elsewhere also whatever keeps the
	 * table's indexes named after it.
	 *
	 * @param string $from physical name of the table to rename
	 * @param string $to physical name it takes
	 * @return string[]
	 * @throws \e107\Database\Exception\QueryException when the engine must read the table and there is no such table
	 */
	public function compileRenameTable($from, $to);

	/**
	 * The statements that rebuild tables to reclaim their unused space: on MySQL one OPTIMIZE TABLE of them all; on
	 * SQLite one VACUUM, which rebuilds the whole database whichever tables are named.
	 *
	 * @param string[] $tables physical table names
	 * @return string[]
	 */
	public function compileOptimizeTable(array $tables);

	/**
	 * The statements that make a batch of changes to a table, in order: on MySQL one ALTER TABLE; elsewhere one
	 * statement per change, a rebuild of the table, or none for a change the engine has no notion of.
	 *
	 * @param string $table physical name of the table to change
	 * @param TableOperation[] $operations
	 * @return string[]
	 * @throws \e107\Database\Exception\UnsupportedException when the engine cannot make a change
	 * @throws \e107\Database\Exception\QueryException when the table cannot be read
	 */
	public function compileAlterTable($table, array $operations);

	/**
	 * A table's definitions in the schema DSL, ready to splice into statements that recreate them through the
	 * schema builder: one per column and per index, the body they make together, and the table options. On MySQL
	 * they are the server's own SHOW CREATE TABLE lines; elsewhere the table as the engine describes it, written in
	 * the DSL.
	 *
	 * @param string $table physical table name
	 * @return array|null ['body' => string, 'options' => string, 'columns' => name => string, 'indexes' => name => string],
	 *                    indexes named as {@see SchemaReaderInterface} names them; null when the table cannot be described
	 */
	public function describeDefinitions($table);
}
