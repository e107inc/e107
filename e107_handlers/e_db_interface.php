<?php
	/**
	 * Created by PhpStorm.
	 * Date: 2/3/2019
	 * Time: 6:22 PM
	 */


	interface e_db
	{

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
		 * @param string $table
		 * @param string $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int number of affected rows, or false on error
		 * @desc Delete rows from a table<br />
		 * <br />
		 * Example:
		 * <code>$sql->delete("tmp", "tmp_ip='$ip'");</code><br />
		 * <br />
		 * @access public
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
		 * @param string $table
		 * @param array  $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int Last insert ID or false on error
		 * @desc Insert/REplace a row into the table<br />
		 * <br />
		 * Example:<br />
		 * <code>e107::getDb()->replace("links", $array);</code>
		 *
		 * @access public
		 */
		function replace($table, $arg, $debug = false, $log_type = '', $log_remark = '');







		/**
		 * Query and fetch at once
		 *
		 * Examples:
		 * <code>
		 * <?php
		 *
		 * // Get single value, $multi and indexField are ignored
		 * $string = e107::getDb()->retrieve('user', 'user_email', 'user_id=1');
		 *
		 * // Get single row set, $multi and indexField are ignored
		 * $array = e107::getDb()->retrieve('user', 'user_email, user_name', 'user_id=1');
		 *
		 * // Fetch all, don't append WHERE to the query, index by user_id, noWhere auto detected (string starts with upper case ORDER)
		 * $array = e107::getDb()->retrieve('user', 'user_id, user_email, user_name', 'ORDER BY user_email LIMIT 0,20', true, 'user_id');
		 *
		 * // Same as above but retrieve() is only used to fetch, not useable for single return value
		 * if(e107::getDb()->select('user', 'user_id, user_email, user_name', 'ORDER BY user_email LIMIT 0,20', true))
		 * {
		 *        $array = e107::getDb()->retrieve(null, null, null,  true, 'user_id');
		 * }
		 *
		 * // Using whole query example, in this case default mode is 'single'
		 * $array = e107::getDb()->retrieve('SELECT
		 *    p.*, u.user_email, u.user_name FROM `#user` AS u
		 *    LEFT JOIN `#myplug_table` AS p ON p.myplug_table=u.user_id
		 *    ORDER BY u.user_email LIMIT 0,20'
		 * );
		 *
		 * // Using whole query example, multi mode - $fields argument mapped to $multi
		 * $array = e107::getDb()->retrieve('SELECT u.user_email, u.user_name FROM `#user` AS U ORDER BY user_email LIMIT 0,20', true);
		 *
		 * // Using whole query example, multi mode with index field
		 * $array = e107::getDb()->retrieve('SELECT u.user_email, u.user_name FROM `#user` AS U ORDER BY user_email LIMIT 0,20', null, null, true, 'user_id');
		 * </code>
		 *
		 * @param string $table if empty, enter fetch only mode
		 * @param string $fields comma separated list of fields or * or single field name (get one); if $fields is of type boolean and $where is not found, $fields overrides $multi
		 * @param string $where WHERE/ORDER/LIMIT etc clause, empty to disable
		 * @param boolean $multi if true, fetch all (multi mode)
		 * @param string $indexField field name to be used for indexing when in multi mode
		 * @param boolean $debug
		 * @return string|array
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
		 * Function to handle any MySQL query
		 *
		 * @param string $query - the MySQL query string, where '#' represents the database prefix in front of table names.
		 *        Strongly recommended to enclose all table names in backticks, to minimise the possibility of erroneous substitutions - its
		 *            likely that this will become mandatory at some point
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return boolean | integer
		 *        Returns FALSE if there is an error in the query
		 *        Returns TRUE if the query is successful, and it does not return a row count
		 *        Returns the number of rows added/updated/deleted for DELETE, INSERT, REPLACE, or UPDATE
		 */
		public function gen($query, $debug = false, $log_type = '', $log_remark = '');


		/**
		 * @param string       $tableName - Name of table to access, without any language or general DB prefix
		 * @param array|string $arg (array preferred)
		 * @param bool         $debug
		 * @param string       $log_type
		 * @param string       $log_remark
		 * @return int number of affected rows, or false on error
		 * @desc Update fields in ONE table of the database corresponding to your $arg variable<br />
		 * <br />
		 * Think to call it if you need to do an update while retrieving data.<br />
		 * <br />
		 * Example using a unique connection to database:<br />
		 * <code>e107::getDb()->update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");</code>
		 * <br />
		 * OR as second connection<br />
		 * <code>
		 * e107::getDb('sql2')->update("user", "user_viewed = '$u_new' WHERE user_id = '".USERID."' ");</code><br />
		 *
		 * @access public
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
		function getLastErrorNumber();


		/**
		 * @desc Perform a select query()
		 * <br />
		 * If you need more requests think to call the class.<br />
		 * <br />
		 * Example using a unique connection to database:<br />
		 * <code>e107::getDb()->select("comments", "*", "comment_item_id = '$id' AND comment_type = '1' ORDER BY comment_datestamp");</code><br />
		 * <br />
		 * OR as second connection:<br />
		 * <code>
		 * e107::getDb('sql2')->select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT $from, ".$view, true);</code>
		 *
		 * @param        $table
		 * @param string $fields
		 * @param string $arg
		 * @param bool   $noWhere
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return integer Number of rows or false on error
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
		 * @param string $tableName - Name of table to access, without any language or general DB prefix
		 * @param        $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int Last insert ID or false on error. When using '_DUPLICATE_KEY_UPDATE' return ID, true on update, 0 on no change and false on error.
		 * @desc Insert a row into the table<br />
		 * <br />
		 * Example:<br />
		 * <code>e107::getDb()->insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0");</code>
		 *
		 * @access public
		 */
		function insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '');




		/**
		 * Check if a database table is empty or not.
		 * @param $table
		 * @return bool
		 */
		function isEmpty($table);



		/**
		 * Truncate a table
		 * @param string $table - table name without e107 prefix
		 */
		function truncate($table=null);



		/**
		 * @param string $table
		 * @param string $fields
		 * @param string $arg
		 * @param bool   $debug
		 * @param string $log_type
		 * @param string $log_remark
		 * @return int number of affected rows or false on error
		 * @desc Count the number of rows in a select<br />
		 * <br />
		 * Example:<br />
		 * <code>$topics = e107::getDb()->count("forum_thread", "(*)", "thread_forum_id='".$forum_id."' AND thread_parent='0'");</code>
		 *
		 * @access public
		 */
		function count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '');



		/**
		 * Return the maximum value for a given table/field
		 * @param $table (without the prefix)
		 * @param $field
		 * @param string $where (optional)
		 * @return bool|resource
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
		 * Drop/delete table and all it's data
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