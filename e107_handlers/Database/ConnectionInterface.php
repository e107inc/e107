<?php

namespace e107\Database;

use e107;
use e107\Database\Platform\PlatformInterface;
use e107\Database\Schema\Column;
use e107\Database\Schema\Index;
use e107\Database\Schema\SchemaBuilder;
use PDO;
use PDOStatement;

	/**
	 * e107 database abstraction layer.
	 *
	 * Obtain the active instance with {@see e107::getDb()}. There are three ways
	 * to reach the database, listed here in order of preference.
	 *
	 * 1. The fluent query builder (preferred). Call
	 *    {@see ConnectionInterface::createQueryBuilder()} to get an {@see QueryBuilder}. It binds
	 *    every value (no escaping, no injection surface) and compiles through the
	 *    {@see PlatformInterface} dialect layer, so the same code stays portable
	 *    toward other SQL backends. Use it for ordinary
	 *    SELECT/INSERT/UPDATE/DELETE work.
	 *    <code>
	 *    $rows = e107::getDb()->createQueryBuilder()
	 *        ->select('user_id', 'user_name')
	 *        ->from('user')
	 *        ->whereIn('user_class', array(1, 2))
	 *        ->orderBy('user_name', 'ASC')
	 *        ->fetchAll();
	 *    </code>
	 *
	 * 2. {@see ConnectionInterface::execute()} with bound :named parameters (fallback). Reach
	 *    for it only when the builder cannot express the query, such as
	 *    INSERT...SELECT, window functions, or other constructs the fluent
	 *    methods do not model. The SQL you pass is run verbatim in the
	 *    connection's own dialect, so unlike builder output it does not
	 *    automatically carry across backends.
	 *
	 * 3. The legacy CRUD methods (select, insert, update, delete, replace, gen,
	 *    retrieve, count, max, escape) are deprecated and strongly discouraged:
	 *    avoid them in new code, and migrate the call sites you touch when
	 *    refactoring. Each carries a deprecation note mapping it to its
	 *    replacement. They nevertheless remain part of the supported, tested
	 *    surface, with no removal planned or scheduled; in this API,
	 *    deprecation is a signpost to the replacement, never a removal
	 *    schedule or an obligation to rewrite working code.
	 *
	 * Schema and DDL work (CREATE/ALTER/DROP/TRUNCATE) has its own dedicated
	 * methods: {@see ConnectionInterface::dropTable()}, {@see ConnectionInterface::truncate()},
	 * {@see ConnectionInterface::copyTable()}, {@see ConnectionInterface::field()}, {@see ConnectionInterface::fields()} and
	 * {@see ConnectionInterface::index()}.
	 *
	 * The whole contract runs against both backends in
	 * {@see \e_db_abstractTest}, whose test methods double as working examples
	 * of every method here.
	 */


	interface ConnectionInterface
	{
		/**
		 * Bind-parameter types for the db_Query() ['PREPARE' => ..., 'BIND' => ...]
		 * contract. Values match the PDO::PARAM_* constants, so existing call sites
		 * passing PDO::PARAM_* keep working while backend-neutral code can use
		 * ConnectionInterface::PARAM_* without depending on the pdo extension.
		 */
		const PARAM_NULL = 0;
		const PARAM_INT  = 1;
		const PARAM_STR  = 2;
		const PARAM_LOB  = 3;
		const PARAM_BOOL = 5;

		/**
		 * Connect ONLY  - used in v2.x
		 *
		 * @param string $mySQLserver IP Or hostname of the MySQL server
		 * @param string $mySQLuser MySQL username
		 * @param string $mySQLpassword MySQL Password
		 * @param bool   $newLink force a new link connection if TRUE. Default FALSE
		 * @return boolean true on success, false on error.
		 */
		public function connect($mySQLserver, $mySQLuser, $mySQLpassword, $newLink = false);


		/**
		 * Select the database to use.
		 *
		 * @param string       $database name
		 * @param array|string $prefix
		 * @param boolean      $multiple set to maintain connection to a secondary database.
		 * @return boolean true when database selection was successful otherwise false.
		 */
		public function database($database, $prefix = MPREFIX, $multiple=false);


		/**
		 * Delete rows from a table.
		 *
		 * @param string $table
		 * @param string $arg WHERE clause, without the WHERE keyword
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int number of affected rows, or false on error
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $qb->delete('tmp')
		 *                 ->where($qb->expr()->eq('tmp_ip', $ip))
		 *                 ->execute();
		 *             </code>
		 *             See {@see QueryBuilder::delete()}, and {@see ConnectionInterface} for the
		 *             full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		function delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '');





		/**
		 * Fetch the next row from the current result set.
		 *
		 * <code>
		 * while($row = $sql->fetch())
		 * {
		 *     $text .= $row['user_name'];
		 * }
		 * </code>
		 *
		 * @param string|null $type 'assoc' (default), 'num' or 'both'
		 * @return array|false the row, or false when no rows remain
		 */
		function fetch($type = null);






		/**
		 *	Determines if a plugin field (and key) exist. OR if fieldid is numeric - return the field name in that position.
		 *
		 *	@param string $table - table name (no prefix)
		 *	@param string $fieldid - Numeric offset or field/key name
		 *	@param string $key - PRIMARY|INDEX|UNIQUE - type of key when searching for key name
		 *	@param boolean $retinfo = FALSE - just returns true|false. TRUE - returns all field info
		 *	@return array|boolean - FALSE on error, field information on success
		 */
	    function field($table,$fieldid="",$key="", $retinfo = false);



		/**
		 * Insert a row, replacing any existing row with the same primary or
		 * unique key.
		 *
		 * @param string $table
		 * @param array  $arg the same structured array as {@see ConnectionInterface::insert()};
		 *               a '_REPLACE' key is implied
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int Last insert ID or false on error
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $qb->replace('links')
		 *                 ->values(array('link_id' => 1, 'link_name' => 'News'))
		 *                 ->execute();
		 *             </code>
		 *             See {@see QueryBuilder::replace()}, and {@see ConnectionInterface} for the
		 *             full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		function replace($table, $arg, $debug = false, $log_type = '', $log_remark = '');







		/**
		 * Run a SELECT and fetch the result in one call. The mode is detected
		 * from the arguments:
		 * - Empty $table: fetch-only mode, reading from the connection's
		 *   current result set ($multi and $indexField apply as below).
		 * - Boolean $fields with no $where: $table is a complete SQL query;
		 *   true fetches all rows, false fetches one row.
		 * - A single named field plus $where: returns that field's value.
		 * - Otherwise: returns one row, or all rows when $multi is true;
		 *   $indexField keys the multi-row result by that column.
		 * A $where starting with an uppercase SQL keyword (ORDER, LIMIT, ...)
		 * is treated as a trailing clause rather than a WHERE body.
		 *
		 * <code>
		 * $name = $sql->retrieve('user', 'user_name', 'user_id = 1');            // field value
		 * $row  = $sql->retrieve('user', 'user_id, user_name', 'user_id = 1');   // one row
		 * $rows = $sql->retrieve('user', '*', 'user_class = 0', true);           // all rows
		 * $rows = $sql->retrieve('SELECT * FROM #user WHERE user_id > 5', true); // raw query
		 * </code>
		 *
		 * @param string $table logical table name, a complete SQL query (see
		 *        above), or empty for fetch-only mode
		 * @param string|bool $fields comma-separated field list, '*', a single
		 *        field name, or the all-rows flag when $table is a raw query
		 * @param string $where WHERE clause body, or a trailing clause when it
		 *        starts with an SQL keyword; empty to disable
		 * @param boolean $multi if true, fetch all rows
		 * @param string $indexField column to key the multi-row result by
		 * @param boolean $debug
		 * @return string|array
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value and
		 *             fetches in one call:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $email = $qb->select('user_email')
		 *                 ->from('user')
		 *                 ->where($qb->expr()->eq('user_id', 1))
		 *                 ->fetchOne();
		 *             </code>
		 *             See {@see QueryBuilder::fetchOne()},
		 *             {@see QueryBuilder::fetchRow()} and
		 *             {@see QueryBuilder::fetchAll()}, and {@see ConnectionInterface} for the full
		 *             guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		public function retrieve($table = null, $fields = null, $where=null, $multi = false, $indexField = null, $debug = false);


		/**
		 * Drain the current result set into a list of rows, keeping
		 * string-keyed columns only.
		 *
		 * @param string|array $fields 'ALL', or an array of column names to keep
		 * @param bool|int $amount stop after this many rows; false for no limit
		 * @param bool|int $maximum hard cap on rows read; false for no cap
		 * @param bool|string $ordermode column whose value keys the result
		 *        array; false for a 1-based numeric index
		 * @return array rows as associative arrays
		 */
		function rows($fields = 'ALL', $amount = false, $maximum = false, $ordermode=false);


		/**
		 * Run a hand-written SQL query.
		 *
		 * @param string $query the SQL query string, where '#' represents the database prefix in front of table names.
		 *        Strongly recommended to enclose all table names in backticks, to minimise the possibility of erroneous substitutions; it is
		 *            likely that this will become mandatory at some point
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return boolean | int
		 *        Returns FALSE if there is an error in the query
		 *        Returns TRUE if the query is successful, and it does not return a row count
		 *        Returns the number of rows added/updated/deleted for DELETE, INSERT, REPLACE, or UPDATE
		 * @deprecated v2.4.0 Use {@see ConnectionInterface::execute()} instead; it accepts the
		 *             same SQL (including '#table' markers) with values moved to
		 *             bound :named parameters. For ordinary CRUD prefer the query
		 *             builder ({@see ConnectionInterface::createQueryBuilder()}); see {@see ConnectionInterface}
		 *             for the full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		public function gen($query, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Execute an SQL statement with bound parameters. The canonical way to
		 * run raw SQL against an e107 database.
		 *
		 * For ordinary SELECT/INSERT/UPDATE/DELETE work, prefer the query builder
		 * ({@see ConnectionInterface::createQueryBuilder()}): it binds values for you and emits
		 * SQL through the {@see PlatformInterface} dialect layer, so builder-based code
		 * stays portable across backends. Reach for execute() when the builder
		 * cannot express the query, for example INSERT...SELECT, window functions,
		 * or other constructs the fluent methods do not model. SQL you pass here is
		 * run verbatim in the connection's own dialect; the full decision guide
		 * lives at {@see ConnectionInterface}.
		 *
		 * Table names may be written as `#table` (backticks optional): the e107
		 * database prefix is attached and multi-language routing is applied,
		 * while a '#' inside string literals or comments is left untouched.
		 * Values belong in $params as :named placeholders, never concatenated
		 * into the SQL string.
		 *
		 * <code>
		 * $sql->execute('SELECT user_name FROM `#user` WHERE user_id = :id', array('id' => 5));
		 * while($row = $sql->fetch()) { ... }
		 * </code>
		 *
		 * @param string $sql SQL with optional `#table` markers and :named placeholders
		 * @param array $params name => value, or name => array('value' => mixed, 'type' => ConnectionInterface::PARAM_*)
		 * @return int|bool row count for result sets (read rows with {@see ConnectionInterface::fetch()});
		 *                  affected rows for DELETE/INSERT/REPLACE/UPDATE;
		 *                  true for other successful statements; false on error
		 */
		public function execute($sql, $params = array());


		/**
		 * Run a statement against every language variant of the tables it
		 * references: once against the base tables, then once per language
		 * that has a lan_* copy of any referenced table. The modern
		 * replacement for {@see ConnectionInterface::db_Query_all()}.
		 *
		 * '#table' markers are resolved afresh for each leg, so a language
		 * without a copy of some referenced table falls back to the base
		 * table for that leg, and a statement with no markers runs exactly
		 * once. Every leg is attempted even when an earlier one fails, so
		 * maintenance reaches all copies; on failure the first failing
		 * leg's error is kept for {@see ConnectionInterface::getLastErrorText()}.
		 *
		 * <code>
		 * // Drop a plugin table and its language copies:
		 * $sql->executeAllLanguages('DROP TABLE `#myplugin_data`');
		 *
		 * // Parameters bind exactly as in execute():
		 * $sql->executeAllLanguages('UPDATE #news SET news_render_type = :type', array('type' => 0));
		 * </code>
		 *
		 * @param string $sql SQL with '#table' markers and optional :named placeholders
		 * @param array $parameters name => value, or name => array('value' => mixed, 'type' => ConnectionInterface::PARAM_*)
		 * @return int|false number of statements executed (>= 1), or false when any leg failed
		 */
		public function executeAllLanguages($sql, $parameters = array());


		/**
		 * Resolve a logical e107 table name to its physical name: the database
		 * prefix is attached and, on multi-language sites, the table is routed
		 * to a language's lan_* table when one exists.
		 *
		 * @param string $table table name with or without a leading '#'
		 * @param string|null $language null: route for the connection's current
		 *                    language, honouring the multilanguage preference
		 *                    (the default); a language name, e.g. 'Spanish':
		 *                    route to that language's lan_* table when it
		 *                    exists, regardless of the current language or the
		 *                    multilanguage preference
		 * @return string|false physical table name (unquoted), or false when
		 *                      the name is not a valid identifier
		 */
		public function resolveTableName($table, $language = null);


		/**
		 * Resolve a logical e107 table name to its physical name applying the
		 * database prefix only, never the multi-language lan_* routing that
		 * {@see ConnectionInterface::resolveTableName()} performs. For schema-maintenance tooling
		 * that addresses a literal table and handles language tables itself.
		 *
		 * @param string $table table name with or without a leading '#'
		 * @return string|false physical table name (unquoted, prefix only), or
		 *                      false when the name is not a valid identifier
		 */
		public function resolvePhysicalTableName($table);


		/**
		 * Validate and backtick-quote an SQL identifier (`column` or `table.column`).
		 * Fails closed: anything outside the [A-Za-z0-9_] grammar (with one
		 * optional dot) returns false.
		 *
		 * @param string $identifier
		 * @return string|false
		 */
		public function quoteIdentifier($identifier);


		/**
		 * Quote a string as a complete single-quoted SQL literal, including the
		 * surrounding quotes, using the driver's own connection-charset-aware
		 * quoting: the literal-quoting sibling of {@see ConnectionInterface::quoteIdentifier()}.
		 *
		 * For values, always prefer bound parameters ({@see ConnectionInterface::execute()},
		 * the query builder). This exists for the rare grammar positions where a
		 * bound parameter is a syntax error, e.g. MySQL's GROUP_CONCAT
		 * SEPARATOR clause, and is meant for developer-authored strings, never
		 * user input. Fails closed: throws when the driver cannot quote.
		 *
		 * @param string $value
		 * @return string quoted literal, e.g. "'it\'s'"
		 */
		public function quoteStringLiteral($value);


		/**
		 * Preferred entry point for database access. Create a fluent query builder
		 * bound to this connection. It compiles to SQL with bound :named
		 * placeholders and runs through {@see ConnectionInterface::execute()}; table names are
		 * logical (no '#' marker, no database prefix) and resolve through
		 * {@see ConnectionInterface::resolveTableName()}.
		 *
		 * <code>
		 * $rows = e107::getDb()->createQueryBuilder()
		 *     ->select('user_id', 'user_name')
		 *     ->from('user')
		 *     ->whereIn('user_class', array(1, 2))
		 *     ->orderBy('user_name', 'ASC')
		 *     ->fetchAll();
		 * </code>
		 *
		 * For the cases the builder cannot express, drop down to
		 * {@see ConnectionInterface::execute()}; see {@see ConnectionInterface} for the decision guide.
		 *
		 * @return QueryBuilder
		 */
		public function createQueryBuilder();


		/**
		 * Entry point for schema/DDL work (CREATE/ALTER/DROP/RENAME). Create a
		 * fluent schema builder bound to this connection. It is the DDL
		 * counterpart to {@see ConnectionInterface::createQueryBuilder()}: tables resolve through
		 * {@see ConnectionInterface::resolveTableName()} and every column/index identifier is
		 * validated fail-closed, while type and key definitions are structured
		 * value objects ({@see Column}/{@see Index}) or a vouched
		 * {@see SqlFragment} fragment.
		 *
		 * <code>
		 * e107::getDb()->schema()->addColumn('user_extended', 'user_twitter',
		 *     Column::define('VARCHAR', 255)->notNull()->default(''));
		 * </code>
		 *
		 * @return SchemaBuilder
		 */
		public function createSchemaBuilder();


		/**
		 * Shorthand for {@see ConnectionInterface::createSchemaBuilder()}.
		 *
		 * @return SchemaBuilder
		 */
		public function schema();


		/**
		 * SQL dialect of this connection, consulted by the query builder for
		 * dialect-specific SQL such as LIMIT clauses.
		 *
		 * @return PlatformInterface
		 */
		public function getPlatform();


		/**
		 * Apply the e107 field-type STORAGE transform to a value, returning what
		 * the deprecated array-form {@see ConnectionInterface::insert()}/{@see ConnectionInterface::update()}
		 * would bind for that token. Shared with {@see QueryBuilder::setTyped()} and
		 * {@see QueryBuilder::valuesTyped()} so builder writes are byte-identical
		 * to the legacy CRUD path.
		 *
		 * The tokens, their storage transforms, and the bind type each pairs
		 * with ({@see ConnectionInterface::fieldTypeBind()}):
		 * - 'int'/'integer': (int) cast; PARAM_INT.
		 * - 'str'/'string'/'escape'/'safestr': value passes through and is
		 *   bound as PARAM_STR (the legacy escaping distinctions between these
		 *   tokens are moot under parameter binding).
		 * - 'float': locale-safe number conversion; PARAM_STR.
		 * - 'todb': HTML-aware filtering via e107::getParser()->toDB();
		 *   PARAM_STR.
		 * - 'array': e107::serialize(); PARAM_STR.
		 * - 'null': empty values and the '_NULL_' sentinel become SQL NULL
		 *   (PARAM_NULL); non-empty strings pass through as PARAM_STR.
		 * - 'cmd': the value passes through unchanged here, but the legacy
		 *   update() path inlines such fields into the SQL unbound; never
		 *   place user input in one.
		 * - '_DEFAULT' (as a _FIELD_TYPES key): the fallback token for columns
		 *   not listed; 'string' when omitted.
		 *
		 * @param string $type Field-type token.
		 * @param mixed $fieldValue
		 * @return mixed transformed value ready for bindValue()
		 */
		public function applyFieldType($type, $fieldValue);


		/**
		 * The bind type ({@see ConnectionInterface}::PARAM_*) for a field-type token. Pass the
		 * already-transformed value (the result of {@see ConnectionInterface::applyFieldType()}),
		 * as the legacy bind tuple does.
		 *
		 * @param string $type Field-type token.
		 * @param mixed $value Transformed value; consulted only for 'null'.
		 * @return int ConnectionInterface::PARAM_* constant
		 */
		public function fieldTypeBind($type, $value = null);


		/**
		 * Field-type definitions for a table - the '_FIELD_TYPES', '_DEFAULT' and
		 * '_NOTNULL' maps the array-form CRUD consults - or false when none are
		 * available. Lets a caller source legacy field types explicitly, e.g. to
		 * feed {@see QueryBuilder::valuesTyped()}.
		 *
		 * @param string $tableName Logical table name.
		 * @return array|false
		 */
		public function getFieldDefs($tableName);


		/**
		 * Auto-increment id generated by the most recent INSERT on this
		 * connection. Used by {@see QueryBuilder::insertGetId()}.
		 *
		 * @return bool|int the id, or true when the table has no auto-increment column.
		 */
		public function lastInsertId();


		/**
		 * Return a list of the field names in a table.
		 *
		 * @param string $table - table name (no prefix)
		 * @param string $prefix - table prefix to apply. If empty, MPREFIX is used.
		 * @param boolean $retinfo = false - just returns array of field names. TRUE - returns all field info
		 * @return array|boolean - false on error, field list array on success
		 */
		public function fields($table, $prefix = '', $retinfo = false);


		/**
		 * Escape special characters in a string for use inside a quoted SQL
		 * literal.
		 *
		 * @deprecated v2.4.0 Bind values instead of escaping them: the query
		 *             builder ({@see ConnectionInterface::createQueryBuilder()}) binds every
		 *             value for you, and {@see ConnectionInterface::execute()} binds :named
		 *             parameters. Escaping is only safe when the result is placed
		 *             inside quotes in the SQL string, which parameter binding
		 *             makes unnecessary. Calls emit one E_USER_DEPRECATED notice
		 *             per call site per request. See {@see ConnectionInterface} for the full
		 *             guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $data
		 * @param bool $strip Unused; retained for backwards compatibility
		 * @return string
		 */
		public function escape($data, $strip = true);


		/**
		 * Update fields in one table.
		 *
		 * @param string       $tableName Name of table to access, without any language or general DB prefix
		 * @param array{
		 *            data: array<string, mixed>,
		 *            WHERE?: string,
		 *            _FIELD_TYPES?: array<string, string>
		 *        }|array<string, mixed>|string $arg Fields to set. A flat
		 *        column => value map is auto-wrapped; a top-level 'WHERE' key
		 *        survives the wrap and becomes the WHERE clause (without the
		 *        keyword). Field-type handling matches {@see ConnectionInterface::insert()}:
		 *        values are bound, except a 'cmd'-typed field, whose value is
		 *        inlined into the SQL unbound (for expressions such as
		 *        col=col+1; never place user input in one). A plain string is
		 *        used verbatim as the SET clause (legacy, unbound; avoid).
		 * @param bool         $debug
		 * @param string       $log_type
		 * @param string       $log_remark
		 * @return int|false number of affected rows, or false on error
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $qb->update('user')
		 *                 ->set('user_viewed', $u_new)
		 *                 ->where($qb->expr()->eq('user_id', USERID))
		 *                 ->execute();
		 *             </code>
		 *             See {@see QueryBuilder::update()} and {@see QueryBuilder::set()};
		 *             for SQL expressions such as user_viewed = user_viewed + 1
		 *             use {@see QueryBuilder::setExpression()}. See {@see ConnectionInterface} for
		 *             the full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		function update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');



		/**
		 * Close the database connection. Only needed for secondary
		 * connections; the native e107 connection is closed at the end of the
		 * request.
		 *
		 * @return void
		 */
		function close();


		/**
		 * Total number of results of the last query regardless of its LIMIT,
		 * when that query used SELECT SQL_CALC_FOUND_ROWS.
		 *
		 * @return int|false the total, or false when none was captured
		 */
		public function foundRows();


		/**
		 * Error text of the last operation; empty string when there was none.
		 *
		 * @return string
		 */
		function getLastErrorText();


		/**
		 * Driver error number of the last operation; 0 when there was none.
		 *
		 * @return int
		 */
		function getLastErrorNumber();


		/**
		 * Perform a SELECT query.
		 *
		 * @param string $table table name without the prefix
		 * @param string $fields comma-separated column list, or '*'
		 * @param string $arg WHERE clause body (no keyword), a full trailing
		 *        clause (see $noWhere), or SQL with :named placeholders when
		 *        $noWhere carries a bind array
		 * @param bool|string|array $noWhere three modes:
		 *        false or 'default': $arg is a WHERE clause, prepended with
		 *        the WHERE keyword;
		 *        any other truthy scalar: $arg is appended verbatim, for
		 *        clauses like ORDER BY or LIMIT with no WHERE;
		 *        array: bind mode; $arg must carry :named placeholders and
		 *        $noWhere supplies the name => value bindings, each bound as
		 *        a string (mirrors {@see \PDOStatement::execute()}).
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int|false Number of rows or false on error
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $rows = $qb->select('*')
		 *                 ->from('comments')
		 *                 ->where($qb->expr()->eq('comment_item_id', $id))
		 *                 ->orderBy('comment_datestamp', 'ASC')
		 *                 ->fetchAll();
		 *             </code>
		 *             See {@see QueryBuilder::select()} and
		 *             {@see QueryBuilder::fetchAll()}, and {@see ConnectionInterface} for the full
		 *             guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = false, $log_type = '', $log_remark = '');



		/**
		 * Determine whether a table index (key) exists.
		 *
		 * @param string $table table name (no prefix)
		 * @param string $keyname name of the key to look for
		 * @param array|string|null $fields optional list of field names the
		 *        index must contain
		 * @param boolean $retinfo false returns true|false; true returns the
		 *        key information
		 * @return array|boolean false on error or no match, key information on
		 *         success
		 */
		function index($table, $keyname, $fields=null, $retinfo = false);




		/**
		 * Insert one row into a table.
		 *
		 * @param string $tableName Name of table to access, without any language or general DB prefix
		 * @param string|array{
		 *            data: array<string, mixed>,
		 *            _FIELD_TYPES?: array<string, string>,
		 *            _NOTNULL?: array<string, mixed>,
		 *            _REPLACE?: true,
		 *            _DUPLICATE_KEY_UPDATE?: true,
		 *            _IGNORE?: true
		 *        }|array<string, mixed> $arg Row data. Three forms are accepted:
		 *        a flat column => value map (auto-wrapped as array('data' => $arg));
		 *        the structured array shown above; or a raw SQL VALUES list as a
		 *        string (legacy, unbound; avoid). Notes:
		 *        - A 'WHERE' key is silently removed, so one array can serve
		 *          both insert() and {@see ConnectionInterface::update()}.
		 *        - When '_FIELD_TYPES' is omitted, it is auto-loaded from the
		 *          table's field definitions ({@see ConnectionInterface::getFieldDefs()});
		 *          unlisted columns use the '_DEFAULT' token ('string'). Tokens
		 *          are documented at {@see ConnectionInterface::applyFieldType()}.
		 *        - The value '_NULL_' stores SQL NULL.
		 *        - '_NOTNULL' fills NOT NULL columns missing from 'data'.
		 *        - '_REPLACE' emits REPLACE INTO; '_IGNORE' emits INSERT IGNORE;
		 *          '_DUPLICATE_KEY_UPDATE' appends an ON DUPLICATE KEY UPDATE
		 *          clause built from the same data and changes the return
		 *          contract (see @return). Values are bound, but the generated
		 *          update clause follows update()'s rules, where a 'cmd'-typed
		 *          field is inlined into the SQL unbound.
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int|bool Last insert ID or false on error. When using '_DUPLICATE_KEY_UPDATE' return ID, true on update, 0 on no change and false on error.
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $qb->insert('links')
		 *                 ->values(array('link_name' => 'News', 'link_url' => 'news.php'))
		 *                 ->execute();
		 *             </code>
		 *             See {@see QueryBuilder::insert()} and {@see QueryBuilder::values()};
		 *             pass a list of rows to {@see QueryBuilder::values()} for a
		 *             multi-row insert, and use {@see QueryBuilder::upsert()} for the
		 *             legacy '_DUPLICATE_KEY_UPDATE' option. For inserts the builder
		 *             still cannot express (INSERT...SELECT), fall back to
		 *             {@see ConnectionInterface::execute()}. See {@see ConnectionInterface} for the full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		function insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');




		/**
		 * Check whether a database table is empty.
		 *
		 * @param string|null $table table name without the prefix; fails
		 *        closed outside the identifier grammar
		 * @return bool
		 */
		function isEmpty($table = null);



		/**
		 * Truncate a table, removing all of its rows.
		 *
		 * @param string|null $table table name without the prefix; fails
		 *        closed outside the identifier grammar
		 * @return bool|int|null query result; false on an invalid name, null
		 *         when no table was given
		 */
		function truncate($table=null);



		/**
		 * Count the number of rows matching a query.
		 *
		 * @param string $table table name without the prefix; when
		 *        $fields === 'generic', a complete SQL query instead, whose
		 *        result set must expose a COUNT(*) column (legacy escape
		 *        hatch: never place user input in it)
		 * @param string $fields '(*)' or '(field)' to shape the COUNT();
		 *        'generic' switches to the raw-query mode above
		 * @param string $arg optional WHERE clause, with the keyword
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int number of affected rows or false on error
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $topics = $qb->selectCount()
		 *                 ->from('forum_thread')
		 *                 ->where($qb->expr()->eq('thread_forum_id', $forum_id))
		 *                 ->andWhere($qb->expr()->eq('thread_parent', 0))
		 *                 ->fetchOne();
		 *             </code>
		 *             See {@see QueryBuilder::fetchOne()}, and {@see ConnectionInterface} for the
		 *             full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		function count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '');



		/**
		 * Return the maximum value of a field.
		 *
		 * @param $table (without the prefix)
		 * @param $field
		 * @param string $where (optional)
		 * @return bool|resource
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $max = $qb->selectAggregate('MAX', 'user_id')->from('user')->fetchOne();
		 *             </code>
		 *             See {@see QueryBuilder::fetchOne()}, and {@see ConnectionInterface} for the
		 *             full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 */
		public function max($table, $field, $where='');




		/**
		 * Dump MySQL Table(s) to a file in the Backup folder.
		 * @param $table string - name without the prefix or '*' for all
		 * @param $file string - optional file name. or leave blank to generate.
		 * @param $options - additional preferences.
		 * @return string|bool backup file path.
		 */
		function backup($table='*', $file='', $options=null);


		/**
		 * Discard the cached list of database tables, so the next lookup
		 * (e.g. {@see ConnectionInterface::tables()}) reads the current schema again.
		 */
		public function resetTableList();

		/**
		 * Return a filtered list of DB tables.
		 *
		 * @param string $mode [optional] all|lan|nolan|nologs
		 * @return array
		 */
		public function tables($mode='all');



		/**
		 * Number of columns in the current result set.
		 *
		 * @return int
		 */
		public function columnCount();


		/**
		 * Set the current database language
		 * @param string $lang English, French etc.
		 * @return null
		 */
		public function setLanguage($lang);



		/**
		 * Get the current database language
		 * @return string $lang English, French etc.
		 */
		public function getLanguage();



		/**
		 * Copy a table, optionally including its data.
		 *
		 * @param string $oldtable
		 * @param string $newtable
		 * @param bool $drop
		 * @param bool $data
		 * @return bool|int|PDOStatement|resource
		 */
		public function copyTable($oldtable, $newtable, $drop = false, $data = false);



		/**
		 * Drop a table and all its data.
		 *
		 * @param string $table name without the prefix
		 * @return bool|int
		 */
		public function dropTable($table);




		/**
		 * Returns the last database query used.
		 * @return string
		 */
		function getLastQuery();


		/* ---------------------------------------------------------------------
		 * Shared operational surface.
		 *
		 * Implemented identically by both backends (enforced by e_db_parityTest)
		 * and declared here so this interface states the complete public
		 * contract. Members marked @internal are plumbing the backends and
		 * shared trait need from each other; application code should not call
		 * them directly.
		 * ------------------------------------------------------------------ */


		/**
		 * Version string reported by the database server.
		 *
		 * @return string
		 */
		public function getServerInfo();


		/**
		 * The core preference object, consulted for multi-language settings.
		 *
		 * @return \e_core_pref
		 */
		public function getConfig();


		/**
		 * The connection's current sql_mode.
		 *
		 * @return string
		 */
		public function getMode();


		/**
		 * The intended charset of this connection, eg. 'utf8mb4'.
		 *
		 * @return string
		 */
		public function getCharset();


		/**
		 * Set the connection charset (SET NAMES).
		 *
		 * @param string $charset
		 * @return void
		 */
		public function setCharset($charset = 'utf8mb4');


		/**
		 * Toggle error-reporting mode for this connection.
		 *
		 * @param bool $mode
		 * @return void
		 */
		public function setErrorReporting($mode);


		/**
		 * Toggle debug mode for this connection.
		 *
		 * @param bool $bool
		 * @return void
		 */
		public function debugMode($bool);


		/**
		 * Record a named timing marker in the debug output when debug mode is on.
		 *
		 * @param string $sMarker
		 * @return null|true
		 */
		public function markTime($sMarker);


		/**
		 * Add a query entry to the system log (dblog table).
		 *
		 * @internal Called by the CRUD methods when a $log_type is supplied;
		 * application code should pass $log_type/$log_remark to those instead.
		 * @param string $log_type
		 * @param string $log_remark
		 * @param string $log_query
		 * @return void
		 */
		public function log($log_type = '', $log_remark = '', $log_query = '');


		/**
		 * Capture the driver's last error state after an operation.
		 *
		 * @internal Bookkeeping the backends and shared trait call after each
		 * query; read errors via {@see ConnectionInterface::getLastErrorText()} and
		 * {@see ConnectionInterface::getLastErrorNumber()} instead.
		 * @param string $from calling method name, used in the error text
		 * @return string|null error description, or null when there was no error
		 */
		public function dbError($from);


		/**
		 * Execute a raw query against this connection: the engine every legacy
		 * path routes through. Accepts a plain SQL string or the prepared form
		 * array('PREPARE' => $sqlWithNamedPlaceholders,
		 * 'BIND' => array(name => array('value' => $v, 'type' => ConnectionInterface::PARAM_*)))
		 * (an 'EXECUTE' key with a plain name => value map binds everything as
		 * PARAM_STR, mirroring {@see \PDOStatement::execute()}).
		 *
		 * @internal Plumbing for the legacy CRUD methods.
		 * @deprecated v2.4.0 Use {@see ConnectionInterface::execute()}: it takes the same SQL
		 *             with a friendlier name => value parameter map and
		 *             substitutes '#table' markers for you. See {@see ConnectionInterface}
		 *             for the full guide.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string|array $query
		 * @param null $rli unused; retained for backwards compatibility
		 * @param string $qry_from calling method name, for the debug log
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int|resource result handle or row count depending on the query; false on error
		 */
		public function db_Query($query, $rli = null, $qry_from = '', $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Run a query once for the main table and once per language variant of
		 * every prefixed table it references. Multi-language maintenance helper;
		 * no modern replacement exists.
		 *
		 * @param string $query SQL with '#' database-prefix markers
		 * @param bool $debug
		 * @return bool false when any leg of the query fails
		 */
		public function db_Query_all($query, $debug = false);


		/**
		 * Number of rows in the last result set, or rows affected by the last
		 * write; -1 when there is no result.
		 *
		 * @param null $result unused; retained for backwards compatibility
		 * @return int
		 */
		public function rowCount($result = null);


		/**
		 * Total number of queries executed during this request.
		 *
		 * @return int
		 */
		public function queryCount();


		/**
		 * Whether a table exists, without raising a database error.
		 *
		 * @param string $table table name without the prefix
		 * @param string $language empty for a regular table, or a language name
		 *                         to check for that language's lan_* table
		 * @return bool
		 */
		public function isTable($table, $language = '');


		/**
		 * Check for matching language table(s) when multi-language tables are
		 * active.
		 *
		 * @param string|array $table table name(s) without the prefix
		 * @param bool $multiple false: return the single routed table name;
		 *                       true: return every matching language table
		 * @return array|false|string
		 */
		public function hasLanguage($table, $multiple = false);


		/**
		 * Return a sorted parent/child tree with generated _treesort and _depth
		 * fields, using temporary SQL functions.
		 *
		 * @param string $table table name without the prefix; fails closed
		 *                      outside the identifier grammar
		 * @param string $parent parent-id field name
		 * @param string $pid primary-id field name
		 * @param string $order order field name
		 * @param string $where optional WHERE clause. Caller-supplied SQL: never
		 *                      place user input here
		 * @return bool|int
		 */
		public function selectTree($table, $parent, $pid, $order, $where = null);


		/**
		 * Duplicate a table row, randomising fields that carry a unique index.
		 *
		 * @param string $table table name without the prefix
		 * @param string $fields '*' or a comma-separated column list
		 * @param string $args WHERE clause selecting the source row.
		 *                     Caller-supplied SQL: never place user input here
		 * @return int|false the copied row's id, or false on failure
		 */
		public function copyRow($table, $fields = '*', $args = '');


		/**
		 * Clear the recorded last-error state.
		 *
		 * @return void
		 */
		public function resetLastError();


		/* ---------------------------------------------------------------------
		 * Legacy v1 API (deprecated shims).
		 *
		 * The db_* names below date from e107 v1 and delegate to the current
		 * API; they are implemented once, in the e_db_legacy trait. Avoid them
		 * in new code and migrate existing call sites when refactoring; they
		 * remain supported and tested, with no removal planned.
		 * ------------------------------------------------------------------ */


		/**
		 * Legacy v1 form of {@see ConnectionInterface::select()}; $mode other than 'default'
		 * maps to select()'s $noWhere flag.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param string $fields
		 * @param string $arg
		 * @param string $mode
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return false|int
		 */
		public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::insert()}.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $tableName
		 * @param array|string $arg
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int
		 */
		public function db_Insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::update()}.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $tableName
		 * @param array|string $arg
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int
		 */
		public function db_Update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::close()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::close()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @return void
		 */
		public function db_Close();


		/**
		 * Legacy v1 name for {@see ConnectionInterface::fetch()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::fetch()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string|null $type assoc|num|both
		 * @return array|bool
		 */
		public function db_Fetch($type = null);


		/**
		 * Legacy v1 name for {@see ConnectionInterface::delete()}.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param string $arg
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return false|int
		 */
		public function db_Delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::replace()}.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param array|string $arg
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int
		 */
		public function db_Replace($table, $arg, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::count()}.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param string $fields
		 * @param string $arg
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return false|int
		 */
		public function db_Count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::rowCount()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::rowCount()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @return int
		 */
		public function db_Rows();


		/**
		 * Legacy v1 name for {@see ConnectionInterface::gen()}.
		 *
		 * @deprecated v2.0.0 Use {@see ConnectionInterface::execute()} with bound :named
		 *             parameters, or the query builder
		 *             ({@see ConnectionInterface::createQueryBuilder()}) for ordinary CRUD.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $query
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int
		 */
		public function db_Select_gen($query, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::isTable()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::isTable()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param string $language
		 * @return bool
		 */
		public function db_Table_exists($table, $language = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::tables()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::tables()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $mode
		 * @return array
		 */
		public function db_TableList($mode = 'all');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::field()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::field()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param int|string $fieldid
		 * @param string $key
		 * @param bool $retinfo
		 * @return array|bool
		 */
		public function db_Field($table, $fieldid = "", $key = "", $retinfo = false);


		/**
		 * Legacy v1 name for {@see ConnectionInterface::rows()}.
		 *
		 * @deprecated v2.0.0 Renamed; use {@see ConnectionInterface::rows()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $fields
		 * @param bool|int $amount
		 * @param bool|int $maximum
		 * @param bool|string $ordermode
		 * @return array
		 */
		public function db_getList($fields = 'ALL', $amount = false, $maximum = false, $ordermode = false);


		/**
		 * Legacy v1 name for {@see ConnectionInterface::hasLanguage()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::hasLanguage()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param bool $multiple
		 * @return array|false|string
		 */
		public function db_IsLang($table, $multiple = false);


		/**
		 * Legacy v1 combined form of {@see ConnectionInterface::connect()} and
		 * {@see ConnectionInterface::database()}; returns 'e1' when the connection fails and
		 * 'e2' when database selection fails.
		 *
		 * @deprecated v2.0.0 Use {@see ConnectionInterface::connect()} and
		 *             {@see ConnectionInterface::database()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $mySQLserver
		 * @param string $mySQLuser
		 * @param string $mySQLpassword
		 * @param string $mySQLdefaultdb
		 * @param bool $newLink
		 * @param string $mySQLPrefix
		 * @return bool|string
		 */
		public function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $newLink = false, $mySQLPrefix = MPREFIX);


		/**
		 * Legacy v1 form of {@see ConnectionInterface::update()}; $arg is folded into the
		 * data array as its 'WHERE' key.
		 *
		 * @deprecated v2.0.0 Use the query builder, which binds every value; see
		 *             {@see ConnectionInterface::createQueryBuilder()} and the guide at
		 *             {@see ConnectionInterface}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param array $vars
		 * @param string $arg
		 * @param bool $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return bool|int
		 */
		public function db_UpdateArray($table, $vars = array(), $arg = '', $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::copyRow()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::copyRow()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param string $fields
		 * @param string $args
		 * @return int|false
		 */
		public function db_CopyRow($table, $fields = '*', $args = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::copyTable()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::copyTable()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $oldtable
		 * @param string $newtable
		 * @param bool $drop
		 * @param bool $data
		 * @return bool|int
		 */
		public function db_CopyTable($oldtable, $newtable, $drop = false, $data = false);


		/**
		 * Legacy v1 name for {@see ConnectionInterface::fields()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::fields()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $table
		 * @param string $prefix
		 * @param bool $retinfo
		 * @return array|bool
		 */
		public function db_FieldList($table, $prefix = '', $retinfo = false);


		/**
		 * Legacy v1 name for {@see ConnectionInterface::resetTableList()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::resetTableList()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @return void
		 */
		public function db_ResetTableList();


		/**
		 * Legacy v1 name for {@see ConnectionInterface::queryCount()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::queryCount()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @return int
		 */
		public function db_QueryCount();


		/**
		 * Legacy v1 name for {@see ConnectionInterface::log()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::log()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $log_type
		 * @param string $log_remark
		 * @param string $log_query
		 * @return void
		 */
		public function db_Write_log($log_type = '', $log_remark = '', $log_query = '');


		/**
		 * Legacy v1 name for {@see ConnectionInterface::setErrorReporting()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::setErrorReporting()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param bool $mode
		 * @return void
		 */
		public function db_SetErrorReporting($mode);


		/**
		 * Legacy v1 name for {@see ConnectionInterface::markTime()}.
		 *
		 * @deprecated v2.2.0 Renamed; use {@see ConnectionInterface::markTime()}.
		 *             Avoid in new code and migrate existing call sites when
		 *             refactoring; this method remains supported and tested, with no
		 *             removal planned.
		 * @param string $sMarker
		 * @return bool|true|null
		 */
		public function db_Mark_Time($sMarker);

}
