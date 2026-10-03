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

use db_verify;
use e107;
use e107\Database\ConnectionInterface;
use e107\Database\Exception\QueryException;
use e107\Database\Exception\UnsupportedException;
use e107\Database\IdentifierFilter;
use e107\Database\Platform\PlatformInterface;
use e107\Database\QueryBuilder;
use e107\Database\SqlFragment;
use InvalidArgumentException;

require_once(__DIR__.'/SchemaBuilderTrait.php');
require_once(__DIR__.'/Table.php');

/**
 * Fluent schema/DDL builder bound to an e107 database connection.
 * Every compilation behaviour asserted here is covered by
 * {@see \e107\Database\Schema\SchemaBuilderTest}, a cookbook of working
 * examples.
 *
 * Create one with {@see ConnectionInterface::createSchemaBuilder()} (or the {@see ConnectionInterface::schema()}
 * shorthand). It is the DDL counterpart to {@see QueryBuilder}: where the query
 * builder owns SELECT/INSERT/UPDATE/DELETE and binds every value, this builder
 * owns CREATE/ALTER/DROP/RENAME and validates every identifier. DDL has no bind
 * slots, so safety here is identifier safety: tables resolve through
 * {@see ConnectionInterface::resolveTableName()} and columns/indexes through the strict
 * {@see IdentifierFilter::identifier()} grammar, both fail-closed. Type and key
 * definitions are structured value objects ({@see Column}/{@see Index})
 * or a vouched {@see SqlFragment} fragment; a bare string is rejected.
 *
 * Single operations apply immediately:
 * <code>
 * $schema = e107::getDb()->schema();
 * $schema->addColumn('user_extended', 'user_twitter',
 *     Column::define('VARCHAR', 255)->notNull()->defaultValue(''));
 * $schema->dropColumn('user_extended', 'user_twitter');
 * </code>
 *
 * Several changes to one table batch into a single ALTER TABLE via
 * {@see SchemaBuilder::table()}:
 * <code>
 * $schema->table('comments')
 *     ->addColumn('comment_author_id', Column::define('INT', 10)->unsigned()->notNull()->defaultValue('0'), 'comment_author')
 *     ->addColumn('comment_author_name', Column::define('VARCHAR', 100)->notNull()->defaultValue(''), 'comment_author_id')
 *     ->execute();
 * </code>
 *
 * {@see SchemaBuilder::createDatabase()}, {@see SchemaBuilder::grant()} and
 * {@see SchemaBuilder::flushPrivileges()} are MySQL/MariaDB-only administrative
 * verbs; a platform without them throws {@see UnsupportedException}.
 */
class SchemaBuilder
{
	use SchemaBuilderTrait;

	/** Position sentinel for {@see Table::addColumn()} meaning the
	 *  column is placed first in the table (distinct from any real column name). */
	const FIRST = "\0SchemaBuilder::FIRST\0";

	/**
	 * @param ConnectionInterface $db Connection the schema operations run on.
	 * @param PlatformInterface|null $platform SQL dialect; taken from
	 *                           {@see ConnectionInterface::getPlatform()} when omitted.
	 */
	public function __construct($db, $platform = null)
	{
		$this->db = $db;
		$this->platform = ($platform !== null) ? $platform : $db->getPlatform();
	}

	/**
	 * Begin a batch of changes to one table; every clause lands in one
	 * ALTER TABLE when {@see Table::execute()} runs.
	 *
	 * @param string $table Logical table name.
	 * @return Table
	 * @throws InvalidArgumentException on an invalid table name.
	 */
	public function table($table)
	{
		return new Table($this->db, $this->platform, $this->resolveTable($table));
	}

	/**
	 * Begin a batch of changes to a literal physical table, applying the prefix
	 * only and never the multi-language lan_* routing of {@see SchemaBuilder::table()}.
	 *
	 * For schema-maintenance tooling (db_verify, db_table_admin) that addresses
	 * the literal table it parsed from a schema file and performs its own
	 * language-table handling; routing such DDL would silently retarget it on a
	 * multi-language site.
	 *
	 * @param string $table Logical table name (prefix applied, no routing).
	 * @return Table
	 * @throws InvalidArgumentException on an invalid table name.
	 */
	public function tablePhysical($table)
	{
		return new Table($this->db, $this->platform, $this->resolvePhysicalTable($table));
	}

	/**
	 * Add one column.
	 *
	 * @param string $table
	 * @param string $name New column name.
	 * @param Column|SqlFragment $definition
	 * @param string|null $after Existing column to place this after, the
	 *                    {@see SchemaBuilder::FIRST} sentinel, or null.
	 * @return int|bool the {@see ConnectionInterface::execute()} result.
	 */
	public function addColumn($table, $name, $definition, $after = null)
	{
		return $this->table($table)->addColumn($name, $definition, $after)->execute();
	}

	/**
	 * Change a column's definition, keeping its name.
	 *
	 * @param string $table
	 * @param string $name
	 * @param Column|SqlFragment $definition
	 * @param string|null $after
	 * @return int|bool
	 */
	public function modifyColumn($table, $name, $definition, $after = null)
	{
		return $this->table($table)->modifyColumn($name, $definition, $after)->execute();
	}

	/**
	 * Rename and/or redefine a column.
	 *
	 * @param string $table
	 * @param string $oldName
	 * @param string $newName
	 * @param Column|SqlFragment $definition
	 * @param string|null $after
	 * @return int|bool
	 */
	public function changeColumn($table, $oldName, $newName, $definition, $after = null)
	{
		return $this->table($table)->changeColumn($oldName, $newName, $definition, $after)->execute();
	}

	/**
	 * Drop one column.
	 *
	 * @param string $table
	 * @param string $name
	 * @return int|bool
	 */
	public function dropColumn($table, $name)
	{
		return $this->table($table)->dropColumn($name)->execute();
	}

	/**
	 * Add one index/key.
	 *
	 * @param string $table
	 * @param Index|SqlFragment $index
	 * @return int|bool
	 */
	public function addIndex($table, $index)
	{
		return $this->table($table)->addIndex($index)->execute();
	}

	/**
	 * Drop a named index/key.
	 *
	 * @param string $table
	 * @param string $name
	 * @return int|bool
	 */
	public function dropIndex($table, $name)
	{
		return $this->table($table)->dropIndex($name)->execute();
	}

	/**
	 * Add the PRIMARY KEY over one or more columns.
	 *
	 * @param string $table
	 * @param string[]|string $columns
	 * @return int|bool
	 */
	public function addPrimaryKey($table, $columns)
	{
		return $this->table($table)->addPrimaryKey($columns)->execute();
	}

	/**
	 * Drop the PRIMARY KEY.
	 *
	 * @param string $table
	 * @return int|bool
	 */
	public function dropPrimaryKey($table)
	{
		return $this->table($table)->dropPrimaryKey()->execute();
	}

	/**
	 * Change a table's storage engine.
	 *
	 * @param string $table
	 * @param string $engine
	 * @return int|bool
	 */
	public function setEngine($table, $engine)
	{
		return $this->table($table)->engine($engine)->execute();
	}

	/**
	 * Convert a table to a character set.
	 *
	 * @param string $table
	 * @param string $charset
	 * @return int|bool
	 */
	public function convertCharset($table, $charset)
	{
		return $this->table($table)->charset($charset)->execute();
	}

	/**
	 * Create a table from structured columns and indexes.
	 *
	 * @param string $table
	 * @param array $columns Map of column name => (Column|SqlFragment).
	 * @param Index[]|SqlFragment[] $indexes Optional key definitions.
	 * @param array|SqlFragment $options ['engine' => ..., 'charset' => ...] or a
	 *                       vouched trailing-options fragment.
	 * @return int|bool
	 * @throws InvalidArgumentException when no columns are given.
	 */
	public function createTable($table, array $columns, array $indexes = array(), $options = array())
	{
		$defs = array();

		foreach($columns as $name => $definition)
		{
			$defs[] = $this->quoteColumn($name).' '.$this->resolveColumnDefinition($definition);
		}

		foreach($indexes as $index)
		{
			$defs[] = $this->resolveIndexDefinition($index);
		}

		if(count($defs) === 0)
		{
			throw new InvalidArgumentException('createTable() needs at least one column.');
		}

		return $this->runStatements($this->db->getSchemaManager()->compileCreateTable($this->resolveTable($table), $defs, $this->_resolveOptions($options)));
	}

	/**
	 * Create a table from a vouched body fragment (e.g. the inner block parsed
	 * from a .sql schema file). The body is emitted verbatim between the
	 * parentheses; only the table identifier and the options are owned here.
	 *
	 * @param string $table
	 * @param SqlFragment $body
	 * @param array|SqlFragment $options
	 * @return int|bool
	 * @throws InvalidArgumentException when $body is not a vouched fragment.
	 */
	public function createTableRaw($table, $body, $options = array())
	{
		if(!$body instanceof SqlFragment)
		{
			throw new InvalidArgumentException('createTableRaw() expects a SqlFragment body fragment (e.g. $qb->raw(...)); a bare string is not accepted.');
		}

		return $this->runStatements($this->db->getSchemaManager()->compileCreateTable($this->resolveTable($table), array($body->getSql()), $this->_resolveOptions($options)));
	}

	/**
	 * Compile (without executing) a CREATE TABLE for a literal physical table
	 * from a vouched body fragment, returning the statement text.
	 *
	 * The companion to {@see SchemaBuilder::createTableRaw()} for schema-maintenance
	 * tooling that needs the statement string rather than immediate execution
	 * (e.g. db_verify, which previews the fix query before running it) and that
	 * addresses the literal table without multi-language lan_* routing. Only the
	 * table identifier and the trailing options are owned here; the body is
	 * emitted verbatim between the parentheses.
	 *
	 * @param string $table Logical table name (prefix applied, no routing).
	 * @param SqlFragment $body Vouched inner column/key block.
	 * @param array|SqlFragment $options ['engine' => ..., 'charset' => ...] or a
	 *                       vouched trailing-options fragment.
	 * @return string
	 * @throws InvalidArgumentException when $body is not a vouched fragment.
	 * @throws UnsupportedException when the engine needs several statements; see
	 *                              {@see SchemaBuilder::buildCreateTablePhysicalStatements()}.
	 */
	public function buildCreateTablePhysicalRaw($table, $body, $options = array())
	{
		$statements = $this->buildCreateTablePhysicalStatements($table, $body, $options);

		if(count($statements) !== 1)
		{
			throw new UnsupportedException('Creating this table takes '.count($statements).' statements on this engine; read them with buildCreateTablePhysicalStatements().');
		}

		return reset($statements);
	}

	/**
	 * The statements, in order, that create a literal physical table from a
	 * vouched body fragment in the schema DSL: one CREATE TABLE on MySQL, which
	 * reads the body as written; on another engine the table rendered in its own
	 * dialect, followed by its CREATE INDEX statements.
	 * @param string $table Logical table name (prefix applied, no routing).
	 * @param SqlFragment $body Vouched inner column/key block.
	 * @param array|SqlFragment $options ['engine' => ..., 'charset' => ...] or a
	 *                       vouched trailing-options fragment.
	 * @return string[]
	 * @throws InvalidArgumentException when $body is not a vouched fragment.
	 */
	public function buildCreateTablePhysicalStatements($table, $body, $options = array())
	{
		if(!$body instanceof SqlFragment)
		{
			throw new InvalidArgumentException('buildCreateTablePhysicalRaw() expects a SqlFragment body fragment (e.g. $qb->raw(...)); a bare string is not accepted.');
		}

		return $this->db->getSchemaManager()->compileCreateTable($this->resolvePhysicalTable($table), array($body->getSql()), $this->_resolveOptions($options));
	}

	/**
	 * Rename a table, its indexes going with it.
	 *
	 * @param string $from
	 * @param string $to
	 * @return int|bool false, as on MySQL, when there is no such table
	 * @throws InvalidArgumentException on an invalid table name.
	 */
	public function renameTable($from, $to)
	{
		try
		{
			$statements = $this->db->getSchemaManager()->compileRenameTable($this->resolveTable($from), $this->resolveTable($to));
		}
		catch(QueryException $e)
		{
			return false;
		}

		return $this->runStatements($statements);
	}

	/**
	 * Reclaim unused space / rebuild one or more tables by prefixed name, never routed to a lan_* table, so the names {@see ConnectionInterface::tables()} lists pass as they are.
	 *
	 * @param string[]|string $tables Logical table names (prefix applied, no routing).
	 * @return int|bool
	 * @throws InvalidArgumentException when no table is given or a name is invalid.
	 */
	public function optimizeTable($tables)
	{
		$physical = array();

		foreach((is_array($tables) ? $tables : array($tables)) as $table)
		{
			$physical[] = $this->resolvePhysicalTable($table);
		}

		if(count($physical) === 0)
		{
			throw new InvalidArgumentException('optimizeTable() needs at least one table.');
		}

		return $this->runStatements($this->db->getSchemaManager()->compileOptimizeTable($physical));
	}

	/**
	 * Drop a table if it exists. Delegates to {@see ConnectionInterface::dropTable()}.
	 *
	 * @param string $table Logical table name (no prefix).
	 * @return bool|int
	 */
	public function dropTable($table)
	{
		return $this->db->dropTable($table);
	}

	/**
	 * Empty a table. Delegates to {@see ConnectionInterface::truncate()}.
	 *
	 * @param string $table Logical table name (no prefix).
	 * @return bool|int|null
	 */
	public function truncate($table)
	{
		return $this->db->truncate($table);
	}

	/**
	 * Create a database. MySQL/MariaDB-only administrative verb.
	 *
	 * @param string $database Bare database name (no prefix, no routing).
	 * @param string|null $charset Optional character set.
	 * @return int|bool
	 * @throws UnsupportedException on platforms without databases.
	 */
	public function createDatabase($database, $charset = null)
	{
		$quoted = $this->quoteBareIdentifier($database, 'database name');
		$charset = ($charset !== null) ? $this->validateCharset($charset) : null;

		$sql = $this->platform->compileCreateDatabase($quoted, $charset);

		return $this->db->execute($sql);
	}

	/**
	 * Grant a user all privileges on a database. MySQL/MariaDB-only.
	 *
	 * @param string $database Bare database name.
	 * @param string $user Bare user name.
	 * @param string $host Host the grant applies from.
	 * @return int|bool
	 * @throws UnsupportedException on platforms without a grant system.
	 */
	public function grant($database, $user, $host)
	{
		$sql = $this->platform->compileGrant(
			$this->quoteBareIdentifier($database, 'database name'),
			$this->quoteBareIdentifier($user, 'user name'),
			$this->validateHost($host)
		);

		return $this->db->execute($sql);
	}

	/**
	 * Reload the privilege tables. MySQL/MariaDB-only.
	 *
	 * @return int|bool
	 * @throws UnsupportedException on platforms without a grant system.
	 */
	public function flushPrivileges()
	{
		return $this->db->execute($this->platform->compileFlushPrivileges());
	}

	/**
	 * Introspect a table's columns, as SHOW COLUMNS rows whatever the engine
	 * ({@see SchemaManagerInterface::getColumnRows()}).
	 *
	 * @param string $table
	 * @return array[] one associative row per column; empty on error.
	 */
	public function getColumns($table)
	{
		$rows = $this->db->getSchemaManager()->getColumnRows($this->resolveTable($table));

		return ($rows === false) ? array() : $rows;
	}

	/**
	 * Introspect a table's indexes, as SHOW INDEX rows whatever the engine
	 * ({@see SchemaManagerInterface::getIndexRows()}).
	 *
	 * @param string $table
	 * @return array[] one associative row per index part; empty on error.
	 */
	public function getIndexes($table)
	{
		$rows = $this->db->getSchemaManager()->getIndexRows($this->resolveTable($table));

		return ($rows === false) ? array() : $rows;
	}

	/**
	 * The CREATE TABLE statement, in the engine's own dialect, that reproduces a table.
	 *
	 * @param string $table
	 * @return string|null the statement, or null on error.
	 */
	public function getCreateTable($table)
	{
		return $this->db->getSchemaManager()->getCreateStatement($this->resolveTable($table));
	}

	/**
	 * The CREATE TABLE statement that reproduces a literal physical table,
	 * applying the prefix only and never the multi-language lan_* routing of
	 * {@see SchemaBuilder::getCreateTable()}.
	 *
	 * @param string $table Logical table name (prefix applied, no routing).
	 * @return string|null the statement, or null on error.
	 * @throws InvalidArgumentException on an invalid table name.
	 */
	public function getCreateTablePhysical($table)
	{
		return $this->db->getSchemaManager()->getCreateStatement($this->resolvePhysicalTable($table));
	}

	/**
	 * Resolve a CREATE TABLE options argument to a trailing string.
	 *
	 * @param array|SqlFragment $options
	 * @return string
	 * @throws InvalidArgumentException on an unexpected type.
	 */
	private function _resolveOptions($options)
	{
		if($options instanceof SqlFragment)
		{
			return $options->getSql();
		}

		if(!is_array($options))
		{
			throw new InvalidArgumentException('Table options must be an array or a vouched SqlFragment.');
		}

		$parts = array();

		if(isset($options['engine']))
		{
			$parts[] = 'ENGINE = '.$this->validateEngine($options['engine']);
		}

		if(isset($options['charset']))
		{
			$parts[] = 'DEFAULT CHARSET = '.$this->validateCharset($options['charset']);
		}

		return (count($parts) > 0) ? ' '.implode(' ', $parts) : '';
	}
}
