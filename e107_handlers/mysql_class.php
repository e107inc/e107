<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * MySQL Handler
 *
*/


/**
 *	MySQL Abstraction class
 *
 *	@package    e107
 *	@subpackage	e107_handlers
 *	@todo separate cache for db type tables
 *
 * WARNING!!! System config  should be DIRECTLY called inside db handler like this:
 * e107::getConfig('core', false);
 * FALSE (don't load) is critical important - if missed, expect dead loop (preference handler is calling db handler as well
 * when data is initally loaded)
 * Always use $this->getConfig() method to avoid issues pointed above
 */

if(defined('MYSQL_LIGHT'))
{
	define('E107_DEBUG_LEVEL', 0);
	define('e_QUERY', '');
	$path = (MYSQL_LIGHT !== true ? MYSQL_LIGHT : '');
	require_once($path.'e107_config.php');
	define('MPREFIX', $mySQLprefix);
	$sql = new db;
	$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}
elseif(defined('E107_INSTALL'))
{
	define('E107_DEBUG_LEVEL', 0);
	require('e107_config.php');
	$sql_info = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');
	e107::getInstance()->initInstallSql($sql_info);
	$sql = new db;
	$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}
else
{
	if (!defined('e107_INIT')) { exit; }
}

$db_time = 0.0;				// Global total time spent in all db object queries
$db_mySQLQueryCount = 0;	// Global total number of db object queries (all db's)

$db_ConnectionID = NULL;	// Stores ID for the first DB connection used - which should be the main E107 DB - then used as default




class e_db_mysql implements e_db
{
	// TODO switch to protected vars where needed
	public      $mySQLserver;
	public      $mySQLuser;
	protected   $mySQLpassword;
	protected   $mySQLdefaultdb;
	protected   $mySQLport = 3306;
	public      $mySQLPrefix;

	/** @var mysqli */
	protected   $mySQLaccess;
	public      $mySQLresult;
	public      $mySQLrows;
	public      $mySQLerror = '';			// Error reporting mode - TRUE shows messages

	protected   $mySQLlastErrNum = 0;		// Number of last error - now protected, use getLastErrorNumber()
	protected   $mySQLlastErrText = '';		// Text of last error - now protected, use getLastErrorText()
	protected   $mySQLlastQuery = '';

	public      $mySQLcurTable;
	public      $mySQLlanguage;
	public      $mySQLinfo;
	public      $tabset;
	public      $mySQLtableList = array(); // list of all Db tables.

	public      $mySQLtableListLanguage = array(); // Db table list for the currently selected language
	public      $mySQLtablelist = array();

	protected	$dbFieldDefs = array();		// Local cache - Field type definitions for _FIELD_DEFS and _NOTNULL arrays
	public      $mySQLcharset;
	public	    $mySqlServerInfo = '?';			// Server info - needed for various things

	public      $total_results = false;			// Total number of results

	/** @var e107_db_debug */
	private     $dbg;

	private     $debugMode      = false;

	/**
	* Constructor - gets language options from the cookie or session
	* @access public
	*/
	public function __construct()
	{
		e107::getSingleton('e107_traffic')->BumpWho('Create db object', 1);

		$this->mySQLPrefix = MPREFIX;				// Set the default prefix - may be overridden

		if($port = e107::getMySQLConfig('port'))
		{
			$this->mySQLport = intval($port);
		}

		/*$langid = (isset($pref['cookie_name'])) ? 'e107language_'.$pref['cookie_name'] : 'e107language_temp';
		if (isset($pref['user_tracking']) && ($pref['user_tracking'] == 'session'))
		{
			if (!isset($_SESSION[$langid])) { return; }
			$this->mySQLlanguage = $_SESSION[$langid];
		}
		else
		{
			if (!isset($_COOKIE[$langid])) { return; }
			$this->mySQLlanguage = $_COOKIE[$langid];
		}*/
		// Detect is already done in language handler, use it if not too early
		if(defined('e_LANGUAGE')) $this->mySQLlanguage = e107::getLanguage()->e_language;

		if (E107_DEBUG_LEVEL > 0)
		{
			$this->debugMode = true;
		}

		$this->dbg = e107::getDebug();

	}

	function getPDO()
	{
		return false;
	}

	function debugMode($bool)
	{
		$this->debugMode = (bool) $bool;
	}

	function getMode()
	{
		 $this->gen('SELECT @@sql_mode');
		 $row = $this->fetch();
		 return $row['@@sql_mode'];
	}

	/**
	 * Connects to mySQL server and selects database - generally not required if your table is in the main DB.<br />
	 * <br />
	 * Example using e107 database with variables defined in e107_config.php:<br />
	 * <code>$sql = new db;
	 * $sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);</code>
	 * <br />
	 * OR to connect an other database:<br />
	 * <code>$sql = new db;
	 * $sql->db_Connect('url_server_database', 'user_database', 'password_database', 'name_of_database');</code>
	 *
	 * @param string $mySQLserver IP Or hostname of the MySQL server
	 * @param string $mySQLuser MySQL username
	 * @param string $mySQLpassword MySQL Password
	 * @param string $mySQLdefaultdb The database schema to connect to
	 * @param string $newLink force a new link connection if TRUE. Default FALSE
	 * @param string $mySQLPrefix Tables prefix. Default to $mySQLPrefix from e107_config.php
	 * @return null|string error code
	 */
	public function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $newLink = FALSE, $mySQLPrefix = MPREFIX)
	{
		global $db_ConnectionID, $db_defaultPrefix;
		e107::getSingleton('e107_traffic')->BumpWho('db Connect', 1);

		$this->mySQLserver      = $mySQLserver;
		$this->mySQLuser        = $mySQLuser;
		$this->mySQLpassword    = $mySQLpassword;
		$this->mySQLdefaultdb   = $mySQLdefaultdb;
		$this->mySQLPrefix      = $mySQLPrefix;
		$this->mySQLerror       = false;

		if (!$this->mySQLaccess = @mysqli_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword, $newLink))
		{
			$this->mySQLlastErrText = mysqli_connect_error();
			return 'e1';
		}

		$this->mySqlServerInfo = mysqli_get_server_info($this->mySQLaccess); // We always need this for db_Set_Charset() - so make generally available

		// Set utf8 connection?
		//@TODO: simplify when yet undiscovered side-effects will be fixed
		$this->db_Set_Charset();
		$this->setSQLMode();

		if (!$this->database($this->mySQLdefaultdb))
		{
			return 'e2';
		}

		$this->dbError('dbConnect/SelectDB');

		// Save the connection resource
		if ($db_ConnectionID == null)
		{
			$db_ConnectionID = $this->mySQLaccess;
		}

		return true;
	}





	/**
	 * Connect ONLY  - used in v2.x
	 * @param string $mySQLserver IP Or hostname of the MySQL server
	 * @param string $mySQLuser MySQL username
	 * @param string $mySQLpassword MySQL Password
	 * @param string $mySQLdefaultdb The database schema to connect to
	 * @param string $newLink force a new link connection if TRUE. Default FALSE
	 * @param string $mySQLPrefix Tables prefix. Default to $mySQLPrefix from e107_config.php
	 * @return boolean true on success, false on error.
	 */
	public function connect($mySQLserver, $mySQLuser, $mySQLpassword, $newLink = false)
	{
		global $db_ConnectionID, $db_defaultPrefix;

		e107::getSingleton('e107_traffic')->BumpWho('db Connect', 1);

		$this->mySQLserver 		= $mySQLserver;
		$this->mySQLuser 		= $mySQLuser;
		$this->mySQLpassword 	= $mySQLpassword;
		$this->mySQLerror 		= false;

		if(strpos($mySQLserver,':')!==false && substr_count($mySQLserver, ':')===1)
		{
			list($this->mySQLserver,$this->mySQLport) = explode(':',$mySQLserver,2);
		}

		if (!$this->mySQLaccess = @mysqli_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword, $newLink))
		{
			$this->mySQLlastErrText = mysqli_connect_error();
			return false;
		}

		$this->mySqlServerInfo = mysqli_get_server_info($this->mySQLaccess);

		$this->db_Set_Charset();
		$this->setSQLMode();

		$db_ConnectionID = $this->mySQLaccess;

		return true;
	}


	/**
	 * Get Server Info
	 * @return mixed
	 */
	public function getServerInfo()
	{
		$this->provide_mySQLaccess();
		return $this->mySqlServerInfo;
	}



	/**
	 * Select the database to use.
	 * @param string $database name
	 * @param string $table prefix . eg. e107_
	 * @param boolean $multiple set to maintain connection to a secondary database.
	 * @return boolean true when database selection was successful otherwise false.
	 */
	public function database($database, $prefix = MPREFIX, $multiple=false)
	{
		$this->mySQLdefaultdb 	= $database;
		$this->mySQLPrefix 		= $prefix;

		if($multiple === true)
		{
			$this->mySQLPrefix 		= "`".$database."`.".$prefix;
			return true;
		}

		if (!@mysqli_select_db($this->mySQLaccess, $database))
		{
			return false;
		}

		return true;
	}


	/**
	 * Get system config
	 * @return e_core_pref
	 */
	public function getConfig()
	{
		return e107::getConfig('core', false);
	}

	/**
	* @return void
	* @param string $sMarker
    * @deprecated Use e107::getDebug()->logTime();
	* @desc Enter description here...
	* @access private
	*/
	function db_Mark_Time($sMarker)
	{
		if($this->debugMode !== true)
		{
			return null;
		}

		$this->dbg->Mark_Time($sMarker);

	}


	/**
	* @deprecated
	* @return void
	* @desc Enter description here...
	* @access private
	*/
	function db_Show_Performance()
	{
	//	e107::getDebug()-Show_P
	//	return $db_debug->Show_Performance();
	}


	/**
	* @return void
	* @desc add query to dblog table
	* @access private
	*/
	function db_Write_log($log_type = '', $log_remark = '', $log_query = '')
	{

		$tp = e107::getParser();
		list($time_usec, $time_sec) = explode(" ", microtime());
		$uid = (USER) ? USERID : '0';
		$userstring = ( USER === true ? USERNAME : "LAN_ANONYMOUS");
		$ip = e107::getIPHandler()->getIP(FALSE);
		$qry = $tp->toDB($log_query);
		$this->insert('dblog', "0, {$time_sec}, {$time_usec}, '{$log_type}', 'DBDEBUG', {$uid}, '{$userstring}', '{$ip}', '', '{$log_remark}', '{$qry}'");
	}


	/**
	* This is the 'core' routine which handles much of the interface between other functions and the DB
	*
	* If a SELECT query includes SQL_CALC_FOUND_ROWS, the value of FOUND_ROWS() is retrieved and stored in $this->total_results
	* @param string|array $query
	* @param mysqli $rli Your own mysqli connection instead of the one in this object
	* @return boolean|mysqli_result - as mysqli_query() function.
	*			FALSE indicates an error
	*			For SELECT, SHOW, DESCRIBE, EXPLAIN and others returning a result set, returns a resource
	*			TRUE indicates success in other cases
	*/
	public function db_Query($query, $rli = NULL, $qry_from = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		global $db_time,$db_mySQLQueryCount,$queryinfo;
		$db_mySQLQueryCount++;

		$this->mySQLlastQuery = $query;

		if ($debug == 'now')
		{
			$this->dbg->log($query);
		}
		if ($debug !== FALSE || strstr($_SERVER['QUERY_STRING'], 'showsql'))
		{
			$debugQry = is_array($query) ? print_a($query,true) : $query;
			$queryinfo[] = "<b>{$qry_from}</b>: ".$debugQry;
		}
		if ($log_type != '')
		{
			$this->db_Write_log($log_type, $log_remark, $query);
		}

		$this->provide_mySQLaccess();

		$b = microtime();

		$sQryRes = is_null($rli) ? @mysqli_query($this->mySQLaccess, $query) : @mysqli_query($rli, $query);
		$this->mySQLlastErrNum = mysqli_errno($this->mySQLaccess);
		$this->mySQLlastErrText = mysqli_error($this->mySQLaccess);

		$e = microtime();

		e107::getSingleton('e107_traffic')->Bump('db_Query', $b, $e);
		$mytime = e107::getSingleton('e107_traffic')->TimeDelta($b,$e);
		$db_time += $mytime;
		$this->mySQLresult = $sQryRes;


		if (!E107_DEBUG_LEVEL)
		{
			$this->total_results = false;
		}
		// Need to get the total record count as well. Return code is a resource identifier
		// Have to do this before any debug action, otherwise this bit gets messed up



		if (!is_array($query) && (strpos($query,'EXPLAIN') !==0) && (strpos($query,'SQL_CALC_FOUND_ROWS') !== false) && (strpos($query,'SELECT') !== false))
		{

			$fr = mysqli_query($this->mySQLaccess, 'SELECT FOUND_ROWS()');
			$rc = mysqli_fetch_array($fr);
			$this->total_results = (int)$rc['FOUND_ROWS()'];

		}

		if ($this->debugMode === true)
		{
			/** @var $db_debug e107_db_debug */
			global $db_debug;
			$aTrace = debug_backtrace();
			$pTable = $this->mySQLcurTable;
			if (!strlen($pTable)) {
				$pTable = '(complex query)';
			} else {
				$this->mySQLcurTable = ''; // clear before next query
			}
			if(is_object($db_debug))
			{
				$buglink = is_null($rli) ? $this->mySQLaccess : $rli;
				$db_debug->Mark_Query($query, $buglink, $sQryRes, $aTrace, $mytime, $pTable);
			}
		}

		return $sQryRes;
	}

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
	 * // Using whole query example, in this case default mode is 'one'
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
	public function retrieve($table, $fields = null, $where=null, $multi = false, $indexField = null, $debug = false)
	{
		// fetch mode
		if(empty($table))
		{
			$ret = array();
			if(!$multi) return $this->fetch();

			while($row = $this->fetch())
			{
				if(null !== $indexField) $ret[$row[$indexField]] = $row;
				else $ret[] = $row;
			}
			return $ret;
		}

		// detect mode
		$mode = 'one';
		if($table && !$where && is_bool($fields))
		{
			// table is the query, fields used for multi
			if($fields) $mode = 'multi';
			else $mode = 'single';
			$fields = null;
		}
		elseif($fields && '*' !== $fields && strpos($fields, ',') === false && $where)
		{
			$mode = 'single';
		}
		if($multi)
		{
			$mode = 'multi';
		}

		// detect query type
		$select = true;
		$noWhere = false;
		if(!$fields && !$where)
		{
			// gen()
			$select = false;
			if($mode == 'one' && !preg_match('/[,*]+[\s\S]*FROM/im',$table)) // if a comma or astericks is found before "FROM" then leave it in 'one' row mode.
			{
			    $mode = 'single';
			}
		}
		// auto detect noWhere - if where string starts with upper case LATIN word
		elseif(!$where || preg_match('/^[A-Z]+\S.*$/', trim($where)))
		{
			// FIXME - move auto detect to select()?
			$noWhere = true;
		}

		// execute & fetch
		switch ($mode)
		{
			case 'single':
				if($select && !$this->select($table, $fields, $where, $noWhere, $debug))
				{
					return null;
				}
				elseif(!$select && !$this->gen($table, $debug))
				{
					return null;
				}
				$rows = $this->fetch();
				return array_shift($rows);
			break;

			case 'one':
				if($select && !$this->select($table, $fields, $where, $noWhere, $debug))
				{
					return array();
				}
				elseif(!$select && !$this->gen($table, $debug))
				{
					return array();
				}
				return $this->fetch();
			break;

			case 'multi':
				if($select && !$this->select($table, $fields, $where, $noWhere, $debug))
				{
					return array();
				}
				elseif(!$select && !$this->gen($table, $debug))
				{
					return array();
				}
				$ret = array();
				while($row = $this->fetch())
				{
					if(null !== $indexField) $ret[$row[$indexField]] = $row;
					else $ret[] = $row;
				}
				return $ret;
			break;

		}
	}

	/**
	* Perform a mysqli_query() using the arguments suplied by calling db::db_Query()<br />
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
	* @return integer Number of rows or false on error
	*/
	public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		global $db_mySQLQueryCount;

		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;

		if ($arg != '' && ($noWhere === false || $noWhere === 'default'))  // 'default' for BC. 
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table.' WHERE '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->rowCount();
			}
			else
			{
				$this->dbError("db_Select (SELECT $fields FROM ".$this->mySQLPrefix."{$table} WHERE {$arg})");
				return FALSE;
			}
		}
		elseif ($arg != '' && ($noWhere !== false) && ($noWhere !== 'default')) // 'default' for BC. 
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table.' '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->rowCount();
			}
			else
			{
				$this->dbError("db_Select (SELECT {$fields} FROM ".$this->mySQLPrefix."{$table} {$arg})");
				return FALSE;
			}
		}
		else
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table, NULL, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->rowCount();
			}
			else
			{
				$this->dbError("db_Select (SELECT {$fields} FROM ".$this->mySQLPrefix."{$table})");
				return FALSE;
			}
		}
	}

	/**
	 * select() alias
	 *
	 * @deprecated
	 */
	public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->select($table, $fields, $arg, $mode !== 'default', $debug, $log_type, $log_remark);
	}

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
	function insert($tableName, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($tableName);
		$this->mySQLcurTable = $table;
		$REPLACE = false; // kill any PHP notices
		$DUPEKEY_UPDATE = false;
		$IGNORE = '';

		if(is_array($arg))
		{
			if(isset($arg['WHERE'])) // use same array for update and insert.
			{
				unset($arg['WHERE']);
			}

			if(isset($arg['_REPLACE']))
			{
				$REPLACE = TRUE;
				unset($arg['_REPLACE']);
			}

			if(isset($arg['_DUPLICATE_KEY_UPDATE']))
			{
				$DUPEKEY_UPDATE = true;
				unset($arg['_DUPLICATE_KEY_UPDATE']);
			}

			if(isset($arg['_IGNORE']))
			{
				$IGNORE = ' IGNORE';
				unset($arg['_IGNORE']);
			}

			if(!isset($arg['_FIELD_TYPES']) && !isset($arg['data']))
			{
		   	//Convert data if not using 'new' format
				$_tmp = array();
				$_tmp['data'] = $arg;
				$arg = $_tmp;
				unset($_tmp);
			}

			if(!isset($arg['data'])) { return false; }


			// See if we need to auto-add field types array
			if(!isset($arg['_FIELD_TYPES']))
			{
				$fieldDefs = $this->getFieldDefs($tableName);
				if (is_array($fieldDefs)) $arg = array_merge($arg, $fieldDefs);
			}

			$argUpdate = $arg;  // used when DUPLICATE_KEY_UPDATE is active;


			// Handle 'NOT NULL' fields without a default value
			if (isset($arg['_NOTNULL']))
			{
				foreach ($arg['_NOTNULL'] as $f => $v)
				{
					if (!isset($arg['data'][$f]))
					{
						$arg['data'][$f] = $v;
					}
				}
			}


			$fieldTypes = $this->_getTypes($arg);
			$keyList= '`'.implode('`,`', array_keys($arg['data'])).'`';
			$tmp = array();

			foreach($arg['data'] as $fk => $fv)
			{
				$tmp[] = $this->_getFieldValue($fk, $fv, $fieldTypes);
			}

			$valList= implode(', ', $tmp);


			unset($tmp);



			if($REPLACE === false)
			{
				$query = "INSERT".$IGNORE." INTO ".$this->mySQLPrefix."{$table} ({$keyList}) VALUES ({$valList})";

				if($DUPEKEY_UPDATE === true)
				{
					$query .= " ON DUPLICATE KEY UPDATE ";
					$query .= $this->_prepareUpdateArg($tableName, $argUpdate);
				}

			}
			else
			{
				$query = "REPLACE INTO ".$this->mySQLPrefix."{$table} ({$keyList}) VALUES ({$valList})";
			}
		}
		else
		{
			$query = 'INSERT INTO '.$this->mySQLPrefix."{$table} VALUES ({$arg})";
		}

		$this->provide_mySQLaccess();

		$this->mySQLresult = $this->db_Query($query, NULL, 'db_Insert', $debug, $log_type, $log_remark);

		if($DUPEKEY_UPDATE === true)
		{
			$result = false; // ie. there was an error.

			$this->mySQLresult = mysqli_affected_rows($this->mySQLaccess);

			if($this->mySQLresult === 1 ) // insert.
			{
				$result = $this->lastInsertId();
			}
			elseif($this->mySQLresult === 2 || $this->mySQLresult === true) // updated
			{
				$result = true;
				// reset auto-increment to prevent gaps.
				$this->db_Query("ALTER TABLE ".$this->mySQLPrefix.$table."  AUTO_INCREMENT=1", NULL, 'db_Insert', $debug, $log_type, $log_remark);

			}
			elseif($this->mySQLresult === 0) // updated (no change)
			{
				$result = 0;
			}

			$this->dbError('db_Insert');
			return $result;
		}


		if ($this->mySQLresult)
		{
			if(true === $REPLACE)
			{
				$tmp = mysqli_affected_rows($this->mySQLaccess);
				$this->dbError('db_Replace');
				// $tmp == -1 (error), $tmp == 0 (not modified), $tmp == 1 (added), greater (replaced)
				if ($tmp == -1) { return false; } // mysqli_affected_rows error
				return $tmp;
			}

			$tmp = $this->lastInsertId();

			$this->dbError('db_Insert');
			return ($tmp) ? $tmp : TRUE; // return true even if table doesn't have auto-increment.
		}
		else
		{
		//	$this->dbError("db_Insert ({$query})");
			return FALSE;
		}
	}


	public function lastInsertId()
	{
		$tmp = mysqli_insert_id($this->mySQLaccess);
		return ($tmp) ? $tmp : true; // return true even if table doesn't have auto-increment.
	}


	/**
	 * Return the total number of results on the last query regardless of the LIMIT value when SELECT SQL_CALC_FOUND_ROWS is used.
	 * @return bool
	 */
	public function foundRows()
	{
		return $this->total_results;
	}

	/**
	 * @param mysqli_result $result
	 * @return false|int
	 */
	public function rowCount($result=null)
	{
		if (!($result instanceof mysqli_result))
		{
			$result = $this->mySQLresult;
		}
		if ($result instanceof mysqli_result)
		{
			$this->mySQLrows = mysqli_num_rows($result);
		}
		$this->dbError('db_Rows');
		return $this->mySQLrows;
	}

	/**
	 * hasLanguage() alias
	 * @deprecated
	 */
	function db_IsLang($table, $multiple=false)
	{
		return $this->hasLanguage($table, $multiple);
	}


	/**
	 * insert() alias
	 * @deprecated
	 */
	function db_Insert($tableName, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->insert($tableName, $arg, $debug, $log_type, $log_remark);
	}

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
	function replace($table, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$arg['_REPLACE'] = TRUE;
		return $this->insert($table, $arg, $debug, $log_type, $log_remark);
	}

	/**
	 * replace() alias
	 * @deprecated
	 */
	function db_Replace($table, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->replace($table, $arg, $debug, $log_type, $log_remark);
	}


	private function _prepareUpdateArg($tableName, $arg)
	{
		if (is_array($arg))  // Remove the need for a separate db_UpdateArray() function.
	  	{
			if(!isset($arg['_FIELD_TYPES']) && !isset($arg['data']))
		   	{
			   	//Convert data if not using 'new' format
		   		$_tmp = array();
		   		if(isset($arg['WHERE']))
		   		{
		   			$_tmp['WHERE'] = $arg['WHERE'];
		   			unset($arg['WHERE']);
		   		}
		   		$_tmp['data'] = $arg;
		   		$arg = $_tmp;
		   		unset($_tmp);
		   	}

	   		if(!isset($arg['data'])) { return false; }

			// See if we need to auto-add field types array
			if(!isset($arg['_FIELD_TYPES']))
			{
				$fieldDefs = $this->getFieldDefs($tableName);
				if (is_array($fieldDefs)) $arg = array_merge($arg, $fieldDefs);
			}

			$fieldTypes = $this->_getTypes($arg);


			$new_data = '';
			foreach ($arg['data'] as $fn => $fv)
			{
				$new_data .= ($new_data ? ', ' : '');

				$new_data .= "`{$fn}`=".$this->_getFieldValue($fn, $fv, $fieldTypes);
			}

			$arg = $new_data .(isset($arg['WHERE']) ? ' WHERE '. $arg['WHERE'] : '');

		}

		return $arg;

	}


	/**
	* @return int number of affected rows, or false on error
	* @param string $tableName - Name of table to access, without any language or general DB prefix
	* @param array|string $arg  (array preferred)
	* @param bool $debug
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
	function update($tableName, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($tableName);
		$this->mySQLcurTable = $table;

		$this->provide_mySQLaccess();

		$arg = $this->_prepareUpdateArg($tableName, $arg);

		$query = 'UPDATE '.$this->mySQLPrefix.$table.' SET '.$arg;

		$result = $this->mySQLresult = $this->db_Query($query, NULL, 'db_Update', $debug, $log_type, $log_remark);

		if ($result !==false)
		{
			$result = mysqli_affected_rows($this->mySQLaccess);

			$this->dbError('db_Update');
			if ($result === -1) { return false; }	// Error return from mysqli_affected_rows
			return $result;
		}
		else
		{
			$this->dbError("db_Update ({$query})");
			return FALSE;
		}
	}

	/**
	 * update() alias
	 * @deprecated
	 */
	function db_Update($tableName, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->update($tableName, $arg, $debug, $log_type, $log_remark);
	}

	function _getTypes(&$arg)
	{
		if(isset($arg['_FIELD_TYPES']))
		{
			if(!isset($arg['_FIELD_TYPES']['_DEFAULT']))
			{
				$arg['_FIELD_TYPES']['_DEFAULT'] = 'string';
			}
			$fieldTypes = $arg['_FIELD_TYPES'];
			unset($arg['_FIELD_TYPES']);
		}
		else
		{
			$fieldTypes = array();
			$fieldTypes['_DEFAULT'] = 'string';
		}
		return $fieldTypes;
	}

	/**
	* @return mixed
	* @param string|array $fieldValue
	* @desc Return new field value in proper format<br />
	*
	* @access private
	*/
	function _getFieldValue($fieldKey, $fieldValue, &$fieldTypes)
	{
		if($fieldValue === '_NULL_') { return 'NULL';}
		$type = (isset($fieldTypes[$fieldKey]) ? $fieldTypes[$fieldKey] : $fieldTypes['_DEFAULT']);

		switch ($type)
		{
			case 'int':
			case 'integer':
				return (int) $fieldValue;
			break;

			case 'cmd':
				return $fieldValue;
			break;

			case 'safestr':
				return "'{$fieldValue}'";
			break;

			case 'str':
			case 'string':
				//return "'{$fieldValue}'";
				return "'".$this->escape($fieldValue, false)."'";
			break;

			case 'float':
				// fix - convert localized float numbers
				// $larr = localeconv();
				// $search = array($larr['decimal_point'], $larr['mon_decimal_point'], $larr['thousands_sep'], $larr['mon_thousands_sep'], $larr['currency_symbol'], $larr['int_curr_symbol']);
				// $replace = array('.', '.', '', '', '', '');

				// return str_replace($search, $replace, floatval($fieldValue));

				return e107::getParser()->toNumber($fieldValue);
			break;

			case 'null':
				//return ($fieldValue && $fieldValue !== 'NULL' ? "'{$fieldValue}'" : 'NULL');
				return ($fieldValue && $fieldValue !== 'NULL' ? "'".$this->escape($fieldValue, false)."'" : 'NULL');
				break;

			case 'array':
				if(is_array($fieldValue))
				{
					return "'".e107::getArrayStorage()->writeArray($fieldValue, true)."'";
				}
				return "'". (string) $fieldValue."'";
			break;

			case 'todb': // using as default causes serious BC issues. 
				if($fieldValue == '') { return "''"; }
				return "'".e107::getParser()->toDB($fieldValue)."'";
			break;

			case 'escape':
			default:
				return "'".$this->escape($fieldValue, false)."'";
			break;
	  	}
	}

	/**
	 *  @DEPRECATED
	 	Similar to db_Update(), but splits the variables and the 'WHERE' clause.
		$vars may be an array (fieldname=>newvalue) of fields to be updated, or a simple list.
		$arg is usually a 'WHERE' clause
		The way the code is written at the moment, a call to db_Update() with just the first two parameters specified can be
			converted simply by changing the function name - it will still work.
		Deprecated routine - use db_Update() with array parameters
	*/
	function db_UpdateArray($table, $vars, $arg='', $debug = FALSE, $log_type = '', $log_remark = '')
	{
	  $table = $this->db_IsLang($table);
	  $this->mySQLcurTable = $table;

		$this->provide_mySQLaccess();

	  $new_data = '';
	  if (is_array($vars))
	  {
		$spacer = '';
		foreach ($vars as $fn => $fv)
		{
		  $new_data .= $spacer."`{$fn}`='{$fv}'";
		  $spacer = ', ';
		}
		$vars = '';
	  }
	  if ($result = $this->mySQLresult = $this->db_Query('UPDATE '.$this->mySQLPrefix.$table.' SET '.$new_data.$vars.' '.$arg, NULL, 'db_UpdateArray', $debug, $log_type, $log_remark))
	  {
		$result = mysqli_affected_rows($this->mySQLaccess);
		if ($result == -1) return FALSE;	// Error return from mysqli_affected_rows
		return $result;
	  }
	  else
	  {
	    $query = 'UPDATE '.$this->mySQLPrefix.$table.' SET '.$new_data.$vars.' '.$arg;
		$this->dbError("Error in deprecated db_UpdateArray method query:($query)");
		return FALSE;
	  }
	}

	/**
	 * Truncate a table
	 * @param string $table - table name without e107 prefix
	 */
	function truncate($table=null)
	{
		if($table == null){ return null; }
		return $this->gen("TRUNCATE TABLE ".$this->mySQLPrefix.$table);
	}


	/**
	 * @param string $type assoc|num|both
	* @return array|bool MySQL row
	* @desc Fetch an array containing row data (see PHP's mysqli_fetch_array() docs)<br />
	* @example
	* Example :<br />
	* <code>while($row = $sql->fetch()){
	*  $text .= $row['username'];
	* }</code>
	*
	* @access public
	*/
	function fetch($type = null)
	{
		if(defined('MYSQL_ASSOC'))
		{
			switch ($type)
			{
					case 'both':
					case 3: // MYSQL_BOTH:
						$type = MYSQL_BOTH; // 3
					break;

					case 'num':
					case 2; // MYSQL_NUM: // 2
						$type = MYSQL_NUM;
					break;

					default:
					case 'assoc':
					case 1; //: // 1
						$type = MYSQL_ASSOC;
					break;
				}
		}

		$b = microtime();

		if($this->mySQLresult)
		{
			$row = @mysqli_fetch_array($this->mySQLresult, $type);
			e107::getSingleton('e107_traffic')->Bump('db_Fetch', $b);
			if ($row)
			{
				$this->dbError('db_Fetch');
				return $row;		// Success - return data
			}
		}
		$this->dbError('db_Fetch');
		return FALSE;				// Failure
	}

	/**
	 * fetch() alias
	 * @deprecated
	 */
	function db_Fetch($type = null)
	{
		return $this->fetch($type);
	}

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
	function count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($table);

		if ($fields == 'generic')
		{
			$query=$table;
			if ($this->mySQLresult = $this->db_Query($query, NULL, 'db_Count', $debug, $log_type, $log_remark))
			{
				$rows = $this->mySQLrows = @mysqli_fetch_array($this->mySQLresult);
				$this->dbError('db_Count');
				return (int) $rows['COUNT(*)'];
			}
			else
			{
				$this->dbError("db_Count ({$query})");
				return FALSE;
			}
		}

		$this->mySQLcurTable = $table;
		// normalize query arguments - only COUNT expected 'WHERE', not anymore
		if($arg && stripos(trim($arg), 'WHERE') !== 0)
		{
			$arg = 'WHERE '.$arg;
		}
		$query='SELECT COUNT'.$fields.' FROM '.$this->mySQLPrefix.$table.' '.$arg;
		if ($this->mySQLresult = $this->db_Query($query, NULL, 'db_Count', $debug, $log_type, $log_remark))
		{
			$rows = $this->mySQLrows = @mysqli_fetch_array($this->mySQLresult);
			$this->dbError('db_Count');
			return (int) $rows[0];
		}
		else
		{
			$this->dbError("db_Count({$query})");
			return FALSE;
		}
	}

	/**
	 * @deprecated use count()
	 */
	function db_Count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->count($table, $fields, $arg, $debug, $log_type, $log_remark);
	}


	/**

	 * @desc Closes the mySQL server connection.<br />
	 * <br />
	 * Only required if you open a second connection.<br />
	 * Native e107 connection is closed in the footer.php file<br />
	 * <br />
	 * Example :<br />
	 * <code>$sql->db_Close();</code>
	 *
	 * @access public
	 * @return void
	 */
	function close()
	{
		$this->provide_mySQLaccess();
		e107::getSingleton('e107_traffic')->BumpWho('db Close', 1);
		@mysqli_close($this->mySQLaccess);
	}


	/**
	 * BC Alias of close()
	 */
	function db_Close()
	{
		$this->close();
	}


	/**
	* @return int number of affected rows, or false on error
	* @param string $table
	* @param string $arg
	* @desc Delete rows from a table<br />
	* <br />
	* Example:
	* <code>$sql->delete("tmp", "tmp_ip='$ip'");</code><br />
	* <br />
	* @access public
	*/
	function delete($table, $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;

		$this->provide_mySQLaccess();


		if (!$arg)
		{
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.$this->mySQLPrefix.$table, NULL, 'db_Delete', $debug, $log_type, $log_remark))
			{
				// return the number of records deleted instead of an object
				$this->mySQLrows = mysqli_affected_rows($this->mySQLaccess);
				$this->dbError('db_Delete');
				return $this->mySQLrows;
			}
			else
			{
				$this->dbError("db_Delete({$arg})");
				return FALSE;
			}
		}
		else
		{
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.$this->mySQLPrefix.$table.' WHERE '.$arg, NULL, 'db_Delete', $debug, $log_type, $log_remark))
			{
				$this->mySQLrows = mysqli_affected_rows($this->mySQLaccess);
				$this->dbError('db_Delete');
				return $this->mySQLrows;
			}
			else
			{
				$this->dbError('db_Delete ('.$arg.')');
				return FALSE;
			}
		}
	}

	/**
	 * @deprecated use $sql->delete();
	 */
	function db_Delete($table, $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->delete($table, $arg, $debug, $log_type, $log_remark);
	}


	/**
	* @deprecated
	* @desc Enter description here...
	* @access private
	*/
	function db_Rows()
	{
		return $this->rowCount();

	}


	/**
	* @return void
	* @param bool $mode
	* @desc Enter description here...
	* @access private
	*/
	public function db_SetErrorReporting($mode)
	{
		$this->mySQLerror = $mode;
	}


	/**
	* Function to handle any MySQL query
	* @param string $query - the MySQL query string, where '#' represents the database prefix in front of table names.
	*		Strongly recommended to enclose all table names in backticks, to minimise the possibility of erroneous substitutions - its
	*			likely that this will become mandatory at some point
	* @return boolean | integer
	*		Returns FALSE if there is an error in the query
	*		Returns TRUE if the query is successful, and it does not return a row count
	*		Returns the number of rows added/updated/deleted for DELETE, INSERT, REPLACE, or UPDATE
	*/
	public function gen($query, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		global $db_mySQLQueryCount;

		$this->tabset = FALSE;

		$query .= " "; // temp fix for failing regex below, when there is no space after the table name;

		if(strpos($query,'`#') !== FALSE)
		{
			//$query = str_replace('`#','`'.$this->mySQLPrefix,$query);	// This simple substitution should be OK when backticks used
			// SecretR - reverted back - breaks multi-language
			$query = preg_replace_callback("/\s`#([\w]*?)`\W/", array($this, 'ml_check'), $query);
		}
		elseif(strpos($query,'#') !== FALSE)
		{	// Deprecated scenario - caused problems when '#' appeared in data - hence use of backticks
			$query = preg_replace_callback("/\s#([\w]*?)\W/", array($this, 'ml_check'), $query);
		}

		//$query = str_replace("#",$this->mySQLPrefix,$query); //FIXME - quick fix for those that slip-thru - but destroys
																// the point of requiring backticks round table names - wrecks &#039;, for example
		if (($this->mySQLresult = $this->db_Query($query, NULL, 'db_Select_gen', $debug, $log_type, $log_remark)) === FALSE)
		{	// Failed query
			$this->dbError('db_Select_gen('.$query.')');
			return FALSE;
		}
		elseif ($this->mySQLresult === TRUE)
		{	// Successful query which may return a row count (because it operated on a number of rows without returning a result set)
			if(preg_match('#^(DELETE|INSERT|REPLACE|UPDATE)#',$query, $matches))
			{	// Need to check mysqli_affected_rows() - to return number of rows actually updated
				$tmp = mysqli_affected_rows($this->mySQLaccess);
				$this->dbError('db_Select_gen');
				return $tmp;
			}
			$this->dbError('db_Select_gen');		// No row count here
			return TRUE;
		}
		else
		{	// Successful query which does return a row count - get the count and return it
			$this->dbError('db_Select_gen');
			return $this->rowCount();
		}
	}

	/**
	 * gen() alias
	 * @deprecated
	 */
	public function db_Select_gen($query, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		return $this->gen($query, $debug, $log_type, $log_remark);
	}

	function ml_check($matches)
	{
		$table = $this->db_IsLang($matches[1]);
		if($this->tabset == false)
		{
			$this->mySQLcurTable = $table;
			$this->tabset = true;
		}

		return " ".$this->mySQLPrefix.$table.substr($matches[0],-1);
	}

	/**
	* Check for the existence of a matching language table when multi-language tables are active.
	* @param string|array $table Name of table, without the prefix. or an array of table names.
	* @access private
	* @return mixed the name of the language table (eg. lan_french_news) or an array of all matching language tables. (with mprefix)
	*/
	function hasLanguage($table, $multiple=false)
	{
		//When running a multi-language site with english included. English must be the main site language.
		// WARNING!!! FALSE is critical important - if missed, expect dead loop (prefs are calling db handler as well when loading)
		// Temporary solution, better one is needed
		$core_pref = $this->getConfig();
		//if ((!$this->mySQLlanguage || !$pref['multilanguage'] || $this->mySQLlanguage=='English') && $multiple==FALSE)
		if ((!$this->mySQLlanguage || !$core_pref->get('multilanguage') || !$core_pref->get('sitelanguage') /*|| $this->mySQLlanguage==$core_pref->get('sitelanguage')*/) && $multiple==FALSE)
		{
		  	return $table;
		}

		$this->provide_mySQLaccess();

		if($multiple == FALSE)
		{
			$mltable = "lan_".strtolower($this->mySQLlanguage.'_'.$table);
			return ($this->isTable($table,$this->mySQLlanguage) ? $mltable : $table);
		}
		else // return an array of all matching language tables. eg [french]->e107_lan_news
		{
			if(!is_array($table))
			{
				$table = array($table);
			}

			if(!$this->mySQLtableList)
			{
				$this->mySQLtableList = $this->db_mySQLtableList();
			}

			$lanlist = array();

			foreach($this->mySQLtableList as $tab)
			{

 				if(substr($tab,0,4) == "lan_")
				{
					list($tmp,$lng,$tableName) = explode("_",$tab,3);

                    foreach($table as $t)
					{
						if($tableName == $t)
						{
							$lanlist[$lng][$this->mySQLPrefix.$t] = $this->mySQLPrefix.$tab; // prefix needed.
						}

					}
			  	}
			}

			if(empty($lanlist))
			{
				return false;
			}
			else
			{
				return $lanlist;
			}


		}
	// -------------------------


	}


	/**
	 * Deprecated alias of the rows() function below.
	 */
	function db_getList($fields = 'ALL', $amount = FALSE, $maximum = FALSE, $ordermode=FALSE)
	{
		return $this->rows($fields, $amount, $maximum, $ordermode);
	}



	/**
	* @return array
	* @param string fields to retrieve
	* @desc returns fields as structured array
	* @access public
	* @return array rows of the database as an array.
	*/
	function rows($fields = 'ALL', $amount = FALSE, $maximum = FALSE, $ordermode=FALSE)
	{
		$list = array();
		$counter = 1;
		while ($row = $this->fetch())
		{
			foreach($row as $key => $value)
			{
				if (is_string($key))
				{
					if (strtoupper($fields) == 'ALL' || in_array ($key, $fields))
					{
						if(!$ordermode)
						{
							$list[$counter][$key] = $value;
						}
						else
						{
							$list[$row[$ordermode]][$key] = $value;
						}
					}
				}
			}
			if ($amount && $amount == $counter || ($maximum && $counter > $maximum))
			{
				break;
			}
			$counter++;
		}
		return $list;
	}




	/**
	 * Return the maximum value for a given table/field
	 * @param $table (without the prefix)
	 * @param $field
	 * @param string $where (optional)
	 * @return bool|resource
	 */
	public function max($table, $field, $where='')
	{
		$qry = "SELECT MAX(".$field.") FROM `".$this->mySQLPrefix.$table."` ";

		if(!empty($where))
		{
			$qry .= "WHERE ".$where;
		}

		return $this->retrieve($qry);

	}


	/**
	 * Return a sorted list of parent/child tree with an optional where clause.
	 * @param string $table Name of table (without the prefix)
	 * @param string $parent Name of the parent field
	 * @param string $pid  Name of the primary id
	 * @param string $where (Optional ) where condition.
	 * @param string $order Name of the order field.
	 * @todo Add extra params to each procedure so we only need 2 of them site-wide.
	 * @return boolean | integer with the addition of  _treesort and _depth fields in the results.
	 */
	public function selectTree($table, $parent, $pid, $order, $where=null)
	{

		if(empty($table) || empty($parent) || empty($pid))
		{
			$this->mySQLlastErrText = "missing variables in sql->categories()";
			return false;
		}

		$sql = "DROP FUNCTION IF EXISTS `getDepth` ;";

		$this->gen($sql);

		$sql = "
		CREATE FUNCTION `getDepth` (project_id INT) RETURNS int
		BEGIN
		    DECLARE depth INT;
		    SET depth=1;

		    WHILE project_id > 0 DO

		        SELECT IFNULL(".$parent.",-1)
		        INTO project_id
		        FROM ( SELECT ".$parent." FROM `#".$table."` WHERE ".$pid." = project_id) AS t;

		        IF project_id > 0 THEN
		            SET depth = depth + 1;
		        END IF;

		    END WHILE;

		    RETURN depth;

		END
		;
		";


		$this->gen($sql);

		$sql = "DROP FUNCTION IF EXISTS `getTreeSort`;";

		$this->gen($sql);

        $sql = "
        CREATE FUNCTION getTreeSort(incid INT)
        RETURNS CHAR(255)
        BEGIN
                SET @parentstr = CONVERT(incid, CHAR);
                SET @parent = -1;
                label1: WHILE @parent != 0 DO
                        SET @parent = (SELECT ".$parent." FROM `#".$table."` WHERE ".$pid." =incid);
                        SET @order = (SELECT ".$order." FROM `#".$table."` WHERE ".$pid." =incid);
                        SET @parentstr = CONCAT(if(@parent = 0,'',@parent), LPAD(@order,4,0), @parentstr);
                        SET incid = @parent;
                END WHILE label1;

                RETURN @parentstr;
        END
   ;

        ";


        $this->gen($sql);

        $qry =  "SELECT SQL_CALC_FOUND_ROWS *, getTreeSort(".$pid.") as _treesort, getDepth(".$pid.") as _depth FROM `#".$table."` ";

		if($where !== null)
		{
			$qry .= " WHERE ".$where;
		}


		$qry .= " ORDER BY _treesort";


		return $this->gen($qry);


	}

























	/**
	* @return integer
	* @desc returns total number of queries made so far
	* @access public
	*/
	function db_QueryCount()
	{
		global $db_mySQLQueryCount;
		return $db_mySQLQueryCount;
	}


	/**
	 * Multi-language Query Function. Run a query on the same table across all languages.
	 * @param $query
	 * @param bool $debug
	 * @return bool
	 */
	function db_Query_all($query, $debug=false)
	{
        $error = "";

		$query = str_replace("#", $this->mySQLPrefix, $query);

        if(!$this->db_Query($query))
		{  // run query on the default language first.
        	$error .= $query. " failed";
		}

		$table = array();
		$search = array();

        $tmp = explode(" ",$query); // split the query

      	foreach($tmp as $val)
		{
   			if(strpos($val,$this->mySQLPrefix) !== false) // search for table names references using the mprefix
			{
    			$table[] = str_replace(array($this->mySQLPrefix,"`"),"", $val);
				$search[] = str_replace("`","",$val);
			}
		}

		if(empty($table) || empty($search))
		{
			return false;
		}

     // Loop thru relevant language tables and replace each tablename within the query.

        if($tablist = $this->db_IsLang($table, true))
		{
			foreach($tablist as $key=>$tab)
			{
				$querylan = $query;

                foreach($search as $find)
				{
					$replace = ($tab[$find] !="") ? $tab[$find] : $find;
               	  	$querylan = str_replace($find,$replace,$querylan);
				}

				if(!$this->db_Query($querylan)) // run query on other language tables.
				{
					$error .= $querylan." failed for language";
				}

			 	if($debug){ echo "<br />** lang= ".$querylan; }
			}
		}


		return ($error) ? false : true;
	}



	/**
	 *	Return a list of the field names in a table.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $prefix - table prefix to apply. If empty, MPREFIX is used.
	 *	@param boolean $retinfo = FALSE - just returns array of field names. TRUE - returns all field info
	 *	@return array|boolean - FALSE on error, field list array on success
	 */
	function db_FieldList($table, $prefix = '', $retinfo = FALSE)
	{
		if(!$this->mySQLdefaultdb)
		{
			global $mySQLdefaultdb;
			$this->mySQLdefaultdb = $mySQLdefaultdb;
		}

		$this->provide_mySQLaccess();

		if ($prefix == '') $prefix = $this->mySQLPrefix;

		if (false ===  $this->gen('SHOW COLUMNS FROM '.$prefix.$table))
		{
			return false;		// Error return
		}

		$ret = array();

        if ($this->rowCount() > 0)
		{
			while ($row = $this->fetch())
			{
				if ($retinfo)
				{
					$ret[$row['Field']] = $row['Field'];
				}
				else
				{
					$ret[] = $row['Field'];
				}
			}
		}

		return $ret;
	}


	function db_Field($table,$fieldid="",$key="", $retinfo = FALSE)
	{
		return $this->field($table,$fieldid,$key, $retinfo);
	}

	function columnCount()
	{
		return mysqli_num_fields($this->mySQLresult);
	}

	/**
	 *	Determines if a plugin field (and key) exist. OR if fieldid is numeric - return the field name in that position.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $fieldid - Numeric offset or field/key name
	 *	@param string $key - PRIMARY|INDEX|UNIQUE - type of key when searching for key name
	 *	@param boolean $retinfo = FALSE - just returns true|false. TRUE - returns all field info
	 *	@return array|boolean - FALSE on error, field information on success
	 */
    function field($table,$fieldid="",$key="", $retinfo = FALSE)
	{
		if(!$this->mySQLdefaultdb)
		{
			global $mySQLdefaultdb;
			$this->mySQLdefaultdb = $mySQLdefaultdb;
		}
		$convert = array("PRIMARY"=>"PRI","INDEX"=>"MUL","UNIQUE"=>"UNI");
		$key = (isset($convert[$key])) ? $convert[$key] : "OFF";

		$this->provide_mySQLaccess();

        $result = $this->gen("SHOW COLUMNS FROM ".$this->mySQLPrefix.$table);
        if ($result && ($this->rowCount() > 0))
		{
			$c=0;
			while ($row = $this->fetch())
			{
				if(is_numeric($fieldid))
				{
					if($c == $fieldid)
					{
						if ($retinfo) return $row;
						return $row['Field']; // field number matches.
					}
				}
				else
				{	// Check for match of key name - and allow that key might not be used
					if(($fieldid == $row['Field']) && (($key == "OFF") || ($key == $row['Key'])))
					{
						if ($retinfo) return $row;
						return true;
					}
				}
				$c++;
			}
		}
		return FALSE;
	}


	/**
	 *	Determines if a table index (key) exist.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $keyname - Name of the key to
	 *  @param array $fields - OPTIONAL list of fieldnames, the index (key) must contain
	 *	@param boolean $retinfo = FALSE - just returns true|false. TRUE - returns all key info
	 *	@return array|boolean - FALSE on error, key information on success
	 */
	function index($table, $keyname, $fields=null, $retinfo = FALSE)
	{
		if(!$this->mySQLdefaultdb)
		{
			global $mySQLdefaultdb;
			$this->mySQLdefaultdb = $mySQLdefaultdb;
		}

		$this->provide_mySQLaccess();

		if (!empty($fields) && !is_array($fields))
		{
			$fields = explode(',', str_replace(' ', '', $fields));
		}
		elseif(empty($fields))
		{
			$fields = array();
		}

		$check_field = count($fields) > 0;

		$info = array();
		$result = $this->gen("SHOW INDEX FROM ".$this->mySQLPrefix.$table);
		if ($result && ($this->rowCount() > 0))
		{
			$c=0;
			while ($row = $this->fetch())
			{
				// Check for match of key name - and allow that key might not be used
				if($keyname == $row['Key_name'])
				{
					// a key can contain severeal fields which are returned as 1 row per field
					if (!$check_field)
					{   // Check only for keyname
						$info[] = $row;
					}
					elseif ($check_field && in_array($row['Column_name'], $fields))
					{   // Check also for fieldnames
						$info[] = $row;
					}
					$c++;
				}
			}

			if (count($info) > 0)
			{
				// Kex does not consist of all keys
				if ($check_field && $c != count($fields)) return false;
				// Return full information
				if ($retinfo) return $info;
				// Return only if index was found
				return true;
			}
		}
		return FALSE;
	}


	/**
	 * A pointer to mysqli_real_escape_string() - see https://www.php.net/manual/en/mysqli.real-escape-string.php
	 *
	 * @param string $data
	 * @return string
	 */
	function escape($data, $strip = true)
	{
		if ($strip)
		{
			$data = strip_if_magic($data);
		}

		$this->provide_mySQLaccess();

		return mysqli_real_escape_string($this->mySQLaccess, $data);
	}


	/**
	 * Legacy Alias of isTable();
	 * @deprecated
	 * @param $table
	 * @param string $language
	 * @return bool
	 */
	public function db_Table_exists($table,$language='')
	{
		return $this->isTable($table, $language);
	}



	/**
	 * Verify whether a table exists, without causing an error
	 *
	 * @param string $table Table name without the prefix
	 * @param string $language (optional) leave blank to search for a regular table, or language-name to search for a language table.
	 * @example $sql->isTable('news','Spanish');
	 * @return boolean TRUE if exists
	 *
	 * NOTES: Slower (28ms) than "SELECT 1 FROM" (4-5ms), but doesn't produce MySQL errors.
	 * Multiple checks on a single page will only use 1 query. ie. faster on multiple calls.
	 */
	public function isTable($table, $language='')
	{
		// global $pref;
		$table = strtolower($table); // precaution for multilanguage

		if(!empty($language)) //ie. is it a language table?
		{
			$sitelanguage = $this->getConfig()->get('sitelanguage');

			if($language == $sitelanguage)
			{
				 return false;
			}

			if(!isset($this->mySQLtableListLanguage[$language]))
			{
				$this->mySQLtableListLanguage = $this->db_mySQLtableList($language);
			}

			return in_array('lan_'.strtolower($language)."_".$table,$this->mySQLtableListLanguage[$language]);
		}
		else // regular search
		{
			if(!$this->mySQLtableList)
			{
				$this->mySQLtableList = $this->db_mySQLtableList();
			}

			return in_array($table,$this->mySQLtableList);
		}

	}


	/**
	 * Check if a database table is empty or not.
	 * @param $table
	 * @return bool
	 */
	function isEmpty($table=null)
	{
		if(empty($table))
		{
			return false;
		}

		$result = $this->gen("SELECT NULL FROM ".$this->mySQLPrefix.$table." LIMIT 1");

		if($result === 0)
		{
			return true;
		}

		return false;
	}




	/**
	 * Populate mySQLtableList and mySQLtableListLanguage
	 * TODO - better runtime cache - use e107::getRegistry() && e107::setRegistry()
	 * @return array
	 */
	private function db_mySQLtableList($language='')
	{

		$database = !empty($this->mySQLdefaultdb) ? "FROM  `".$this->mySQLdefaultdb."`" : "";
		$prefix = $this->mySQLPrefix;

		if(strpos($prefix, ".") !== false) // eg. `my_database`.$prefix
		{
			$tmp = explode(".",$prefix);
			$prefix = $tmp[1];
		}

		if($language)
		{
			if(!isset($this->mySQLtableListLanguage[$language]))
			{
				$table = array();
				if($res = $this->db_Query("SHOW TABLES ".$database." LIKE '".$prefix."lan_".strtolower($language)."%' "))
				{
					while($rows = $this->fetch('num'))
					{
						$table[] = str_replace($prefix,"",$rows[0]);
					}
				}
				$ret = array($language=>$table);
				return $ret;
			}
			else
			{
				return $this->mySQLtableListLanguage[$language];
			}
		}

		if(!$this->mySQLtableList)
		{
			$table = array();

			if($res = $this->db_Query("SHOW TABLES ".$database." LIKE '".$prefix."%' "))
			{
				$length = strlen($prefix);
				while($rows = $this->fetch('num'))
				{
					$table[] = substr($rows[0],$length);
				}
			}
			return $table;
		}
		else
		{
			return $this->mySQLtableList;
		}
	}

	public function db_ResetTableList()
	{
		$this->mySQLtableList = array();
		$this->mySQLtableListLanguage = array();
	}
	/**
	 * @inheritDoc
	 */
	public function resetTableList()
	{
		return $this->db_ResetTableList();
	}

	/**
	 * Legacy Alias of tables
	 * @deprecated
	 * @param string $mode
	 * @return array
	 */
	public function db_TableList($mode='all')
	{
		return $this->tables($mode);
	}


	/**
	 * Return a filtered list of DB tables.
	 * @param object $mode [optional] all|lan|nolan|nologs
	 * @return array
	 */
	public function tables($mode='all')
	{

		if(!$this->mySQLtableList)
		{
			$this->mySQLtableList = $this->db_mySQLtableList();
		}

		if($mode == 'nologs')
		{
			$ret = array();
			foreach($this->mySQLtableList as $table)
			{
				if(substr($table,-4) != '_log' && $table != 'download_requests')
				{
					$ret[] = $table;
				}

			}

			return $ret;
		}


		if($mode == 'all')
		{
			return $this->mySQLtableList;
		}

		if($mode == 'lan' || $mode=='nolan')
		{
			$nolan = array();
			$lan = array();

			foreach($this->mySQLtableList as $tab)
			{
				if(substr($tab,0,4)!='lan_')
				{
					$nolan[] = $tab;
				}
				else
				{
					$lan[] = $tab;
				}
			}

			return ($mode == 'lan') ? $lan : $nolan;
		}

	}


	/**
	 * Duplicate a Table Row in a table.
	 */
	function db_CopyRow($table,$fields = '*', $args='')
	{
		if(!$table || !$args )
		{
			return false;
		}

		if($fields === '*')
		{
			$fields = $this->db_FieldList($table);
			unset($fields[0]); // Remove primary_id. 
			$fieldList = implode(",",$fields);
		}
		else
		{
			$fieldList = $fields;
		}

		if(empty($fields))
		{
			$this->mysqlLastErrText = "copyRow \$fields list was empty";
			return false;
		}

		$id = $this->gen("INSERT INTO ".$this->mySQLPrefix.$table."(".$fieldList.") SELECT ".$fieldList." FROM ".$this->mySQLPrefix.$table." WHERE ".$args);
		$lastInsertId = $this->lastInsertId();
		return ($id && $lastInsertId) ? $lastInsertId : false;

	}



	function db_CopyTable($oldtable, $newtable, $drop = FALSE, $data = FALSE)
	{
		$old = $this->mySQLPrefix.strtolower($oldtable);
		$new = $this->mySQLPrefix.strtolower($newtable);

		if ($drop)
		{
			$this->gen("DROP TABLE IF EXISTS {$new}");
		}

		//Get $old table structure
		$this->gen('SET SQL_QUOTE_SHOW_CREATE = 1');

		$qry = "SHOW CREATE TABLE {$old}";
		if ($this->gen($qry))
		{
			$row = $this->fetch('num');
			$qry = $row[1];
			//        $qry = str_replace($old, $new, $qry);
			$qry = preg_replace("#CREATE\sTABLE\s`{0,1}".$old."`{0,1}\s#", "CREATE TABLE {$new} ", $qry, 1); // More selective search
		}
		else
		{
			return FALSE;
		}

		if(!$this->isTable($newtable))
		{
			$result = $this->db_Query($qry);
		}

		if ($data) //We need to copy the data too
		{
			$qry = "INSERT INTO {$new} SELECT * FROM {$old}";
			$result = $this->gen($qry);
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function copyTable($oldtable, $newtable, $drop = false, $data = false) {
		return $this->db_CopyTable($oldtable, $newtable, $drop, $data);
	}




	/**
	 * Dump MySQL Table(s) to a file in the Backup folder.
	 * @param $table string - name without the prefix or '*' for all
	 * @param $file string - optional file name. or leave blank to generate.
	 * @param $options - additional preferences.
	 * @return string|bool backup file path.
	 */
	function backup($table='*', $file='', $options=null)
	{
		$this->mysqlLastErrText = "PDO is required to use the mysql backup() method";
		return false;
	}

	/**
	* @return string relating to error (empty string if no error)
	* @param string $from
	* @desc Calling method from within this class
	* @access private
	*/
	function dbError($from)
	{
		$this->mySQLlastErrNum = mysqli_errno($this->mySQLaccess);
		$this->mySQLlastErrText = '';
		if ($this->mySQLlastErrNum == 0)
		{
			return '';
		}
		$this->mySQLlastErrText = mysqli_error($this->mySQLaccess);		// Get the error text.
		if ($this->mySQLerror == TRUE)
		{
			message_handler('ADMIN_MESSAGE', '<b>mySQL Error!</b> Function: '.$from.'. ['.$this->mySQLlastErrNum.' - '.$this->mySQLlastErrText.']', __LINE__, __FILE__);
		}
		return $this->mySQLlastErrText;
	}


	// Return error number for last operation
	function getLastErrorNumber()
	{
		return $this->mySQLlastErrNum;		// Number of last error
	}

	// Return error text for last operation
	function getLastErrorText()
	{
		return $this->mySQLlastErrText;		// Text of last error (empty string if no error)
	}

	function resetLastError()
	{
		$this->mySQLlastErrNum = 0;
		$this->mySQLlastErrText = '';
	}

	function getLastQuery()
	{
		return $this->mySQLlastQuery;
	}

	private function setSQLMode()
	{

		$this->db_Query("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");

	}



	/**
	 * Check if MySQL version is utf8 compatible and may be used as it accordingly to the user choice
	 *
	 * @TODO Simplify when the conversion script will be available
	 * @access public
	 * @param string    MySQL charset may be forced in special circumstances
	 *                  UTF-8 encoding and decoding is left to the progammer
	 * @param bool      TRUE enter debug mode. default FALSE
	 * @return string   hardcoded error message
	 */
	function db_Set_Charset($charset = '', $debug = FALSE)
	{
		// Get the default user choice
		global $mySQLcharset;
		if (isset($mySQLcharset) && $mySQLcharset != 'utf8')
		{
			// Only utf8 is accepted
			$mySQLcharset = '';
		}
		$charset = ($charset ? $charset : $mySQLcharset);
		$message = (( ! $charset && $debug) ? 'Empty charset!' : '');
		if($charset)
		{
			if ( ! $debug)
			{
			   @mysqli_query($this->mySQLaccess, "SET NAMES `$charset`");
			}
			else
			{
				// Check if MySQL version is utf8 compatible
				preg_match('/^(.*?)($|-)/', $this->mySqlServerInfo, $mysql_version);
				if (version_compare($mysql_version[1], '4.1.2', '<'))
				{
					// reset utf8
					//@TODO reset globally? $mySQLcharset = '';
					$charset      = '';
					$message      = 'MySQL version is not utf8 compatible!';
				}
				else
				{
					// Use db_Query() debug handler
					$this->db_Query("SET NAMES `$charset`", NULL, '', $debug);
				}
			}
		}

		// Save mySQLcharset for further uses within this connection
		$this->mySQLcharset = $charset;
		return $message;
	}



	/**
	 *	Get the _FIELD_DEFS and _NOTNULL definitions for a table
	 *<code>
	 *	The information is sought in a specific order:
	 *		a) In our internal cache
	 *		b) in the directory e_CACHE_DBDIR - file name $tableName.php
	 *		c) An override file for a core or plugin-related table. If found, the information is copied to the cache directory
	 *			For core overrides, e_ADMIN.'core_sql/db_field_defs.php' is searched
	 *			For plugins, $pref['e_sql_list'] is used as a search list - any file 'db_field_defs.php' in the plugin directory is earched
	 *		d) The table structure is read from the DB, and a definition created:
	 *			AUTOINCREMENT fields - ignored (or integer)
	 *			integer type fields - 'int' processing
	 *			character/string type fields - todb processing
	 *			fields which are 'NOT NULL' but have no default are added to the '_NOTNULL' list
	 *</code>
	 *	@param string $tableName - table name, without any prefixes (language or general)
	 *	@return boolean|array - FALSE if not found/not to be used. Array of field names and processing types and null overrides if found
	 */
	public function getFieldDefs($tableName)
	{
		if (!isset($this->dbFieldDefs[$tableName]))
		{
			if (is_readable(e_CACHE_DB.$tableName.'.php'))
			{
				$temp = file_get_contents(e_CACHE_DB.$tableName.'.php', FILE_TEXT);
				if ($temp !== FALSE)
				{
					$typeDefs = e107::unserialize($temp);
					unset($temp);
					$this->dbFieldDefs[$tableName] = $typeDefs;
				}
			}
			else
			{		// Need to try and find a table definition
				$searchArray = array(e_CORE.'sql/db_field_defs.php');
				// e107::getPref() shouldn't be used inside db handler! See db_IsLang() comments
				$sqlFiles = (array) $this->getConfig()->get('e_sql_list', array()); // kill any PHP notices
				foreach ($sqlFiles as $p => $f)
				{
					$searchArray[] = e_PLUGIN.$p.'/db_field_defs.php';
				}
				unset($sqlFiles);
				$found = FALSE;
				foreach ($searchArray as $defFile)
				{
					//echo "Check: {$defFile}, {$tableName}<br />";
					if ($this->loadTableDef($defFile, $tableName))
					{
						$found = TRUE;
						break;
					}
				}
				if (!$found)
				{	// Need to read table structure from DB and create the file
					$this->makeTableDef($tableName);
				}
			}
		}
		return $this->dbFieldDefs[$tableName];
	}


	/**
	 *	Search the specified file for a field type definition of the specified table.
	 *	If found, generate and save a cache file in the e_CACHE_DB directory,
	 *	Always also update $this->dbFieldDefs[$tableName] - FALSE if not found, data if found
	 *	@param	string $defFile - file name, including path
	 *	@param	string $tableName - name of table sought
	 *	@return boolean TRUE on success, FALSE on not found (some errors intentionally ignored)
	 */
	protected function loadTableDef($defFile, $tableName)
	{
		$result =false;

		if (is_readable($defFile))
		{
			// Read the file using the array handler routines
			// File structure is a nested array - first level is table name, second level is either FALSE (for do nothing) or array(_FIELD_DEFS => array(), _NOTNULL => array())
			$temp = file_get_contents($defFile);
			// Strip any comments  (only /*...*/ supported)
			$temp = preg_replace("#\/\*.*?\*\/#mis", '', $temp);
			//echo "Check: {$defFile}, {$tableName}<br />";
			if ($temp !== false)
			{
			//	$array = e107::getArrayStorage();
				$typeDefs = e107::unserialize($temp);

				unset($temp);
				if (isset($typeDefs[$tableName]))
				{
					$this->dbFieldDefs[$tableName] = $typeDefs[$tableName];

					$fileData = e107::serialize($typeDefs[$tableName], false);

					if (false === file_put_contents(e_CACHE_DB.$tableName.'.php', $fileData))
					{	// Could do something with error - but mustn't return FALSE - would trigger auto-generated structure

					}

					$result = true;
				}
			}
		}

		if (!$result)
		{
			$this->dbFieldDefs[$tableName] = false;
		}
		return $result;
	}


	/**
	 *	Creates a field type definition from the structure of the table in the DB
	 *	Generate and save a cache file in the e_CACHE_DB directory,
	 *	Also update $this->dbFieldDefs[$tableName] - FALSE if error, data if found
	 *	@param	string $tableName - name of table sought
	 *	@return boolean TRUE on success, FALSE on not found (some errors intentionally ignored)
	 */
	protected function makeTableDef($tableName)
	{
		require_once(e_HANDLER.'db_table_admin_class.php');
		$dbAdm = new db_table_admin();

		$baseStruct = $dbAdm->get_current_table($tableName);
		$baseStruct = isset($baseStruct[0][2]) ? $baseStruct[0][2] : null;
		$fieldDefs = $dbAdm->parse_field_defs($baseStruct);					// Required definitions
		if (!$fieldDefs) return false;

		$outDefs = array();


		foreach ($fieldDefs as $k => $v)
		{
			switch ($v['type'])
			{
				case 'field' :
					if (vartrue($v['autoinc']))
					{
						//break;		Probably include autoinc fields in array
					}

					$baseType = preg_replace('#\(\d+?\)#', '', $v['fieldtype']);		// Should strip any length

					switch ($baseType)
					{
						case 'int' :
						case 'integer':
						case 'shortint' :
						case 'tinyint' :
						case 'mediumint':
							$outDefs['_FIELD_TYPES'][$v['name']] = 'int';
							break;

						case 'char' :
						case 'text' :
						case 'varchar' :
						case 'tinytext' :
						case 'mediumtext' :
						case 'longtext' :
							$outDefs['_FIELD_TYPES'][$v['name']] = 'escape'; //XXX toDB() causes serious BC issues. 
							break;
					}

				//	if($v['name'])


					if (isset($v['nulltype']) && !isset($v['default']))
					{
						$outDefs['_NOTNULL'][$v['name']] = '';
					}
					break;
				case 'pkey' :
				case 'ukey' :
				case 'key' :
				case 'ftkey' :
					break;			// Do nothing with keys for now
				default :
					echo "Unexpected field type: {$k} => {$v['type']}<br />";
			}
		}
	//	$array = e107::getArrayStorage();
		$this->dbFieldDefs[$tableName] = $outDefs;
		$toSave = e107::serialize($outDefs, false);	// 2nd parameter to TRUE if needs to be written to DB

		if (FALSE === file_put_contents(e_CACHE_DB.$tableName.'.php', $toSave))
		{	// Could do something with error - but mustn't return FALSE - would trigger auto-generated structure
			$mes = e107::getMessage();
			$mes->addDebug("Error writing file: ".e_CACHE_DB.$tableName.'.php'); //Fix for during v1.x -> 2.x upgrade.
			// echo "Error writing file: ".e_CACHE_DB.$tableName.'.php'.'<br />';
		}

	}

	/**
	 * In case e_db_mysql::$mySQLaccess is not set, set it.
	 *
	 * Uses the global variable $db_ConnectionID if available.
	 *
	 * When the global variable has been unset like in https://github.com/e107inc/e107-test/issues/6 ,
	 * use the "mySQLaccess" from the default e_db_mysql instance singleton.
	 */
	private function provide_mySQLaccess()
	{
		if (!$this->mySQLaccess) {
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
		}
		if (!$this->mySQLaccess) {
			$this->mySQLaccess = e107::getDb()->get_mySQLaccess();
		}
	}

	/**
	 * @deprecated 2.1.9 Used only to provide $mySQLaccess to other instances of e_db_mysql scattered around
	 * @return resource
	 */
	public function get_mySQLaccess()
	{
		return $this->mySQLaccess;
	}

	/**
	 * @inheritDoc
	 */
	public function setLanguage($lang)
	{
		$this->mySQLlanguage = $lang;
	}

	/**
	 * @inheritDoc
	 */
	public function getLanguage()
	{
		return $this->mySQLlanguage;
	}

	/**
	 * @inheritDoc
	 */
	public function dropTable($table)
	{
		$name = $this->mySQLPrefix.strtolower($table);
		return $this->gen("DROP TABLE IF EXISTS ".$name);
	}
}

/**
 * Backwards compatibility
 */

if(!class_exists('db'))
{
	class db extends e_db_mysql
	{

	}
}
