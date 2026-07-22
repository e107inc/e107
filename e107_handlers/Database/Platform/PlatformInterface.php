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

use e107\Database\ConnectionInterface;
use e107\Database\Exception\UnsupportedException;
use e107\Database\QueryBuilder;
use InvalidArgumentException;

/**
 * Minimal SQL dialect description, consulted by {@see QueryBuilder}.
 *
 * Deliberately small: it answers the few dialect questions the query builder
 * needs (identifier quoting, LIMIT syntax, regular-expression operator,
 * default character set) without attempting schema abstraction or a driver
 * registry. Obtain the connection's platform via {@see ConnectionInterface::getPlatform()}.
 */
interface PlatformInterface
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
	 *                 (see {@see PlatformInterface::getUpsertValueReference()}).
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

	/**
	 * Build a string-aggregation expression (e.g. MySQL's GROUP_CONCAT,
	 * PostgreSQL's string_agg). The aggregated expression and any ORDER BY
	 * terms arrive quoted; the separator arrives as a complete quoted string
	 * literal (see {@see ConnectionInterface::quoteStringLiteral()}), because some
	 * dialects (MySQL's SEPARATOR clause) reject a bound parameter in that
	 * position. Dialects that cannot spell a requested combination (e.g. no
	 * in-aggregate ORDER BY) throw {@see UnsupportedException}.
	 *
	 * @param string $quotedExpression Quoted aggregated expression.
	 * @param string[] $quotedOrderBy Quoted "identifier ASC|DESC" terms; may be empty.
	 * @param string $separatorLiteral Quoted string literal for the separator.
	 * @param bool $distinct Aggregate only distinct values.
	 * @return string
	 * @throws UnsupportedException when the dialect cannot spell the combination.
	 */
	public function compileGroupConcat($quotedExpression, array $quotedOrderBy, $separatorLiteral, $distinct = false);

	/**
	 * Build an ALTER TABLE statement from one or more already-rendered clauses
	 * (e.g. "ADD COLUMN `c` INT", "DROP INDEX `i`"). The table identifier
	 * arrives quoted; the dialect only joins the clauses onto the statement.
	 *
	 * @param string $quotedTable Quoted physical table name.
	 * @param string[] $clauses Rendered ALTER clauses, at least one.
	 * @return string SQL statement.
	 */
	public function compileAlterTable($quotedTable, array $clauses);

	/**
	 * Build a CREATE TABLE statement from already-rendered column/key definition
	 * lines and an optional trailing options string (e.g. " ENGINE=InnoDB").
	 *
	 * @param string $quotedTable Quoted physical table name.
	 * @param string[] $definitions Rendered definition lines.
	 * @param string $options Trailing options, or '' for none.
	 * @return string SQL statement.
	 */
	public function compileCreateTable($quotedTable, array $definitions, $options = '');

	/**
	 * Build a "rename this table" statement. Both identifiers arrive quoted.
	 *
	 * @param string $quotedFrom Quoted current table name.
	 * @param string $quotedTo Quoted new table name.
	 * @return string SQL statement.
	 */
	public function compileRenameTable($quotedFrom, $quotedTo);

	/**
	 * Build a statement that reclaims unused space / rebuilds one or more
	 * tables (e.g. MySQL's OPTIMIZE TABLE). Identifiers arrive quoted.
	 *
	 * @param string[] $quotedTables Quoted physical table names, at least one.
	 * @return string SQL statement.
	 */
	public function compileOptimizeTable(array $quotedTables);

	/**
	 * Build a CREATE DATABASE statement. The database identifier arrives quoted
	 * and the character set already validated. Engines without the concept throw
	 * {@see UnsupportedException}.
	 *
	 * @param string $quotedDatabase Quoted database name.
	 * @param string|null $charset Validated character set, or null for the
	 *                             server default.
	 * @return string SQL statement.
	 * @throws UnsupportedException when the platform has no databases.
	 */
	public function compileCreateDatabase($quotedDatabase, $charset = null);

	/**
	 * Build a GRANT ALL statement scoping a user to a database. Identifiers
	 * arrive quoted and the host already validated. Engines without a grant
	 * system throw {@see UnsupportedException}.
	 *
	 * @param string $quotedDatabase Quoted database name.
	 * @param string $quotedUser Quoted user name.
	 * @param string $host Validated host (placed in a single-quoted literal).
	 * @return string SQL statement.
	 * @throws UnsupportedException when the platform has no grant system.
	 */
	public function compileGrant($quotedDatabase, $quotedUser, $host);

	/**
	 * Build a statement that reloads the privilege tables (e.g. MySQL's FLUSH
	 * PRIVILEGES). Engines without a grant system throw
	 * {@see UnsupportedException}.
	 *
	 * @return string SQL statement.
	 * @throws UnsupportedException when the platform has no grant system.
	 */
	public function compileFlushPrivileges();
}
