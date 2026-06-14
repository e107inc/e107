<?php
	/**
	 * e107 database abstraction layer.
	 *
	 * Obtain the active instance with {@see e107::getDb()}. There are three ways
	 * to reach the database, listed here in order of preference.
	 *
	 * 1. The fluent query builder (preferred). Call
	 *    {@see e_db::createQueryBuilder()} to get an {@see e_db_query}. It binds
	 *    every value (no escaping, no injection surface) and compiles through the
	 *    {@see e_db_platform} dialect layer, so the same code stays portable
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
	 * 2. {@see e_db::execute()} with bound :named parameters (fallback). Reach
	 *    for it only when the builder cannot express the query, such as
	 *    INSERT...SELECT, window functions, or other constructs the fluent
	 *    methods do not model. The SQL you pass is run verbatim in the
	 *    connection's own dialect, so unlike builder output it does not
	 *    automatically carry across backends.
	 *
	 * 3. The legacy CRUD methods (select, insert, update, delete, replace, gen,
	 *    retrieve, count, max, escape) are deprecated; do not use them in new
	 *    code. Each carries an @deprecated note mapping it to its replacement.
	 *
	 * Schema and DDL work (CREATE/ALTER/DROP/TRUNCATE) has its own dedicated
	 * methods: {@see e_db::dropTable()}, {@see e_db::truncate()},
	 * {@see e_db::copyTable()}, {@see e_db::field()}, {@see e_db::fields()} and
	 * {@see e_db::index()}.
	 */


	interface e_db
	{
		/**
		 * Bind-parameter types for the db_Query() ['PREPARE' => ..., 'BIND' => ...]
		 * contract. Values match the PDO::PARAM_* constants, so existing call sites
		 * passing PDO::PARAM_* keep working while backend-neutral code can use
		 * e_db::PARAM_* without depending on the pdo extension.
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
		 *             See {@see e_db_query::delete()}, and {@see e_db} for the
		 *             full guide.
		 */
		function delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '');





		/**
		 * @param string $type assoc|num|both
		* @return array|bool MySQL row
		* @desc Fetch an array containing row data (see PHP's mysql_fetch_array() docs)<br />
		* @example
		* Example :<br />
		* <code>while($row = $sql->fetch()){
		*  $text .= $row['username'];
		* }</code>
		*
		* @access public
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
		 * @param array  $arg column => value map
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
		 *             See {@see e_db_query::replace()}, and {@see e_db} for the
		 *             full guide.
		 */
		function replace($table, $arg, $debug = false, $log_type = '', $log_remark = '');







		/**
		 * Run a SELECT and fetch the result in one call.
		 *
		 * @param string $table if empty, enter fetch only mode
		 * @param string $fields comma separated list of fields or * or single field name (get one); if $fields is of type boolean and $where is not found, $fields overrides $multi
		 * @param string $where WHERE/ORDER/LIMIT etc clause, empty to disable
		 * @param boolean $multi if true, fetch all (multi mode)
		 * @param string $indexField field name to be used for indexing when in multi mode
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
		 *             See {@see e_db_query::fetchOne()},
		 *             {@see e_db_query::fetchRow()} and
		 *             {@see e_db_query::fetchAll()}, and {@see e_db} for the full
		 *             guide.
		 */
		public function retrieve($table, $fields = null, $where=null, $multi = false, $indexField = null, $debug = false);


		/**
		 * @param string fields to retrieve
		 * @param bool $amount
		 * @param bool $maximum
		 * @param bool $ordermode
		 * @return array
		 * @desc returns fields as structured array
		 * @access public
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
		 * @deprecated v2.4.0 Use {@see e_db::execute()} instead; it accepts the
		 *             same SQL (including '#table' markers) with values moved to
		 *             bound :named parameters. For ordinary CRUD prefer the query
		 *             builder ({@see e_db::createQueryBuilder()}); see {@see e_db}
		 *             for the full guide.
		 */
		public function gen($query, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * Execute an SQL statement with bound parameters. The canonical way to
		 * run raw SQL against an e107 database.
		 *
		 * For ordinary SELECT/INSERT/UPDATE/DELETE work, prefer the query builder
		 * ({@see e_db::createQueryBuilder()}): it binds values for you and emits
		 * SQL through the {@see e_db_platform} dialect layer, so builder-based code
		 * stays portable across backends. Reach for execute() when the builder
		 * cannot express the query, for example INSERT...SELECT, window functions,
		 * or other constructs the fluent methods do not model. SQL you pass here is
		 * run verbatim in the connection's own dialect; the full decision guide
		 * lives at {@see e_db}.
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
		 * @param array $params name => value, or name => array('value' => mixed, 'type' => e_db::PARAM_*)
		 * @return int|bool row count for result sets (read rows with {@see e_db::fetch()});
		 *                  affected rows for DELETE/INSERT/REPLACE/UPDATE;
		 *                  true for other successful statements; false on error
		 */
		public function execute($sql, $params = array());


		/**
		 * Resolve a logical e107 table name to its physical name: the database
		 * prefix is attached and, on multi-language sites, the table is routed
		 * to the current language's lan_* table when one exists.
		 *
		 * @param string $table table name with or without a leading '#'
		 * @return string|false physical table name (unquoted), or false when
		 *                      the name is not a valid identifier
		 */
		public function resolveTableName($table);


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
		 * Preferred entry point for database access. Create a fluent query builder
		 * bound to this connection. It compiles to SQL with bound :named
		 * placeholders and runs through {@see e_db::execute()}; table names are
		 * logical (no '#' marker, no database prefix) and resolve through
		 * {@see e_db::resolveTableName()}.
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
		 * {@see e_db::execute()}; see {@see e_db} for the decision guide.
		 *
		 * @return e_db_query
		 */
		public function createQueryBuilder();


		/**
		 * SQL dialect of this connection, consulted by the query builder for
		 * dialect-specific SQL such as LIMIT clauses.
		 *
		 * @return e_db_platform
		 */
		public function getPlatform();


		/**
		 * Auto-increment id generated by the most recent INSERT on this
		 * connection. Used by {@see e_db_query::insertGetId()}.
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
		 *             builder ({@see e_db::createQueryBuilder()}) binds every
		 *             value for you, and {@see e_db::execute()} binds :named
		 *             parameters. Escaping is only safe when the result is placed
		 *             inside quotes in the SQL string, which parameter binding
		 *             makes unnecessary. Calls emit one E_USER_DEPRECATED notice
		 *             per call site per request. See {@see e_db} for the full
		 *             guide.
		 * @param string $data
		 * @param bool $strip Unused; retained for backwards compatibility
		 * @return string
		 */
		public function escape($data, $strip = true);


		/**
		 * Update fields in one table.
		 *
		 * @param string       $tableName Name of table to access, without any language or general DB prefix
		 * @param array|string $arg (array preferred)
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
		 *             See {@see e_db_query::update()} and {@see e_db_query::set()};
		 *             for SQL expressions such as user_viewed = user_viewed + 1
		 *             use {@see e_db_query::setExpression()}. See {@see e_db} for
		 *             the full guide.
		 */
		function update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');



		/**
		 * @desc Closes the mySQL server connection.<br />
		 * <br />
		 * Only required if you open a second connection.<br />
		 * Native e107 connection is closed in the footer.php file<br />
		 * <br />
		 * Example :<br />
		 *
		 * @access public
		 * @return void
		 */
		function close();


		/**
		 * @desc Return the total number of results on the last query regardless of the LIMIT value when SELECT SQL_CALC_FOUND_ROWS is used.
		 * @return bool
		 */
		public function foundRows();


		/**
		 * @desc Return error text for last operation
		 */
		function getLastErrorText();


		// Return error number for last operation

		/**
		 * @return mixed
		 */
		function getLastErrorNumber();


		/**
		 * Perform a SELECT query.
		 *
		 * @param        $table
		 * @param string $fields
		 * @param string $arg
		 * @param bool   $noWhere
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
		 *             See {@see e_db_query::select()} and
		 *             {@see e_db_query::fetchAll()}, and {@see e_db} for the full
		 *             guide.
		 */
		public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = false, $log_type = '', $log_remark = '');



		/**
		 *	@desc Determines if a table index (key) exist.
		 *	@param string $table - table name (no prefix)
		 *	@param string $keyname - Name of the key to
		 *  @param array $fields - OPTIONAL list of fieldnames, the index (key) must contain
		 *	@param boolean $retinfo = FALSE - just returns true|false. TRUE - returns all key info
		 *	@return array|boolean - FALSE on error, key information on success
		 */
		function index($table, $keyname, $fields=null, $retinfo = false);




		/**
		 * Insert one row into a table.
		 *
		 * @param string $tableName Name of table to access, without any language or general DB prefix
		 * @param        $arg
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
		 *             See {@see e_db_query::insert()} and {@see e_db_query::values()};
		 *             pass a list of rows to {@see e_db_query::values()} for a
		 *             multi-row insert, and use {@see e_db_query::upsert()} for the
		 *             legacy '_DUPLICATE_KEY_UPDATE' option. For inserts the builder
		 *             still cannot express (INSERT...SELECT), fall back to
		 *             {@see e_db::execute()}. See {@see e_db} for the full guide.
		 */
		function insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');




		/**
		 * Check if a database table is empty or not.
		 * @param $table
		 * @return bool
		 */
		function isEmpty($table);



		/**
		 * Truncate a table, removing all of its rows.
		 *
		 * @param string $table - table name without e107 prefix
		 */
		function truncate($table=null);



		/**
		 * Count the number of rows matching a query.
		 *
		 * @param string $table
		 * @param string $fields
		 * @param string $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int number of affected rows or false on error
		 * @deprecated v2.4.0 Prefer the query builder, which binds every value:
		 *             <code>
		 *             $qb = e107::getDb()->createQueryBuilder();
		 *             $topics = $qb->select('COUNT(*)')
		 *                 ->from('forum_thread')
		 *                 ->where($qb->expr()->eq('thread_forum_id', $forum_id))
		 *                 ->andWhere($qb->expr()->eq('thread_parent', 0))
		 *                 ->fetchOne();
		 *             </code>
		 *             See {@see e_db_query::fetchOne()}, and {@see e_db} for the
		 *             full guide.
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
		 *             $max = $qb->select('MAX(user_id)')->from('user')->fetchOne();
		 *             </code>
		 *             See {@see e_db_query::fetchOne()}, and {@see e_db} for the
		 *             full guide.
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
		 * (e.g. {@see e_db::tables()}) reads the current schema again.
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
		 * @desc Returns the number of columns in the result set
		 * @return mixed
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

}