<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * mySQL Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/mysql_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/


/**
 *	MySQL Abstraction class
 *
 *	@package    e107
 *	@subpackage	e107_handlers
 *	@version 	$Id$;
 *
 *	@todo separate cache for db type tables
 */

/*
	Parameters related to auto-generation of field definitions on db_Insert() and db_Update()
*/
	define('ALLOW_AUTO_FIELD_DEFS', TRUE);	// Temporary so new functionality can be disabled if it causes problems
	define('e_DB_CACHE', e_CACHE);			// Use standard cache directory for now - should really be elsewhere


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


class e_db_mysql
{
	// TODO switch to protected vars where needed
	public $mySQLserver;
	public $mySQLuser;
	public $mySQLpassword;
	public $mySQLdefaultdb;
	public $mySQLPrefix;
	public $mySQLaccess;
	public $mySQLresult;
	public $mySQLrows;
	public $mySQLerror = '';			// Error reporting mode - TRUE shows messages

	protected $mySQLlastErrNum = 0;		// Number of last error - now protected, use getLastErrorNumber()
	protected $mySQLlastErrText = '';		// Text of last error - now protected, use getLastErrorText()
	protected $mySQLlastQuery = '';

	public $mySQLcurTable;
	public $mySQLlanguage;
	public $mySQLinfo;
	public $tabset;
	public $mySQLtableList = array(); // list of all Db tables.

	public $mySQLtableListLanguage = array(); // Db table list for the currently selected language

	protected	$dbFieldDefs = array();		// Local cache - Field type definitions for _FIELD_DEFS and _NOTNULL arrays
	/**
	 * MySQL Charset
	 *
	 * @var string
	 */
	public $mySQLcharset;
	public	$mySqlServerInfo = '?';			// Server info - needed for various things

	public $total_results = false;			// Total number of results

	/**
	* Constructor - gets language options from the cookie or session
	* @access public
	*/
	public function __construct()
	{

		global $pref, $db_defaultPrefix;
		e107::getSingleton('e107_traffic')->BumpWho('Create db object', 1);

		$this->mySQLPrefix = MPREFIX;				// Set the default prefix - may be overridden

		$langid = (isset($pref['cookie_name'])) ? 'e107language_'.$pref['cookie_name'] : 'e107language_temp';
		if (isset($pref['user_tracking']) && ($pref['user_tracking'] == 'session'))
		{
			if (!isset($_SESSION[$langid])) { return; }
			$this->mySQLlanguage = $_SESSION[$langid];
		}
		else
		{
			if (!isset($_COOKIE[$langid])) { return; }
			$this->mySQLlanguage = $_COOKIE[$langid];
		}
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

		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;
		$this->mySQLPrefix = $mySQLPrefix;

		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		if(defined("USE_PERSISTANT_DB") && USE_PERSISTANT_DB == TRUE)
		{
			// No persistent link parameter permitted
			if ( ! $this->mySQLaccess = @mysql_pconnect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword))
			{
				return 'e1';
			}
		}
		else
		{
			if ( ! $this->mySQLaccess = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword, $newLink))
			{
				return 'e1';
			}
		}

		$this->mySqlServerInfo = mysql_get_server_info();		// We always need this for db_Set_Charset() - so make generally available

		// Set utf8 connection?
		//@TODO: simplify when yet undiscovered side-effects will be fixed
		$this->db_Set_Charset();

		if (!@mysql_select_db($this->mySQLdefaultdb, $this->mySQLaccess))
		{
			return 'e2';
		}

		$this->dbError('dbConnect/SelectDB');

		// Save the connection resource
		if ($db_ConnectionID == NULL)
			$db_ConnectionID = $this->mySQLaccess;
		return TRUE;
	}


	/**
	* @return void
	* @param unknown $sMarker
	* @desc Enter description here...
	* @access private
	*/
	function db_Mark_Time($sMarker)
	{
		if (E107_DEBUG_LEVEL > 0)
		{
			global $db_debug;
			$db_debug->Mark_Time($sMarker);
		}
	}


	/**
	* @return void
	* @desc Enter description here...
	* @access private
	*/
	function db_Show_Performance()
	{
		return $db_debug->Show_Performance();
	}


	/**
	* @return void
	* @desc add query to dblog table
	* @access private
	*/
	function db_Write_log($log_type = '', $log_remark = '', $log_query = '')
	{
		global $tp, $e107;
		list($time_usec, $time_sec) = explode(" ", microtime());
		$uid = (USER) ? USERID : '0';
		$userstring = ( USER === true ? USERNAME : "LAN_ANONYMOUS");
		$ip = $e107->getip();
		$qry = $tp->toDB($log_query);
		$this->db_Insert('dblog', "0, {$time_sec}, {$time_usec}, '{$log_type}', 'DBDEBUG', {$uid}, '{$userstring}', '{$ip}', '', '{$log_remark}', '{$qry}'");
	}


	/**
	* This is the 'core' routine which handles much of the interface between other functions and the DB
	*
	* If a SELECT query includes SQL_CALC_FOUND_ROWS, the value of FOUND_ROWS() is retrieved and stored in $this->total_results
	* @param string $query
	* @param unknown $rli
	* @return boolean | resource - as mysql_query() function.
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
			echo "** $query<br />\n";
		}
		if ($debug !== FALSE || strstr(e_QUERY, 'showsql'))
		{
			$queryinfo[] = "<b>{$qry_from}</b>: $query";
		}
		if ($log_type != '')
		{
			$this->db_Write_log($log_type, $log_remark, $query);
		}

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}

		$b = microtime();
		$sQryRes = is_null($rli) ? @mysql_query($query,$this->mySQLaccess) : @mysql_query($query, $rli);
		$e = microtime();

		e107::getSingleton('e107_traffic')->Bump('db_Query', $b, $e);
		$mytime = e107::getSingleton('e107_traffic')->TimeDelta($b,$e);
		$db_time += $mytime;
		$this->mySQLresult = $sQryRes;

		$this->total_results = false;
		if ((strpos($query,'SQL_CALC_FOUND_ROWS') !== FALSE) && (strpos($query,'SELECT') !== FALSE))
		{	// Need to get the total record count as well. Return code is a resource identifier
			// Have to do this before any debug action, otherwise this bit gets messed up
			$fr = mysql_query('SELECT FOUND_ROWS()', $this->mySQLaccess);
			$rc = mysql_fetch_array($fr);
			$this->total_results = (int) $rc['FOUND_ROWS()'];
		}

		if (E107_DEBUG_LEVEL)
		{
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
			   	$nFields = $db_debug->Mark_Query($query, $buglink, $sQryRes, $aTrace, $mytime, $pTable);
			}
			else
			{
				echo "what happened to db_debug??!!<br />";
			}
		}
		return $sQryRes;
	}


	/**
	* Perform a mysql_query() using the arguments suplied by calling db::db_Query()<br />
	* <br />
	* If you need more requests think to call the class.<br />
	* <br />
	* Example using a unique connection to database:<br />
	* <code>$sql->db_Select("comments", "*", "comment_item_id = '$id' AND comment_type = '1' ORDER BY comment_datestamp");</code><br />
	* <br />
	* OR as second connection:<br />
	* <code>$sql2 = new db;
	* $sql2->db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT $from, ".$view, 'no_where');</code>
	*
	* @return integer Number of rows or false on error
	*/
	public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		global $db_mySQLQueryCount;

		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		if ($arg != '' && $mode == 'default')
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table.' WHERE '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->db_Rows();
			}
			else
			{
				$this->dbError("db_Select (SELECT $fields FROM ".$this->mySQLPrefix."{$table} WHERE {$arg})");
				return FALSE;
			}
		}
		elseif ($arg != '' && $mode != 'default')
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table.' '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->db_Rows();
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
				return $this->db_Rows();
			}
			else
			{
				$this->dbError("db_Select (SELECT {$fields} FROM ".$this->mySQLPrefix."{$table})");
				return FALSE;
			}
		}
	}


	/**
	* @return int Last insert ID or false on error
	* @param string $tableName - Name of table to access, without any language or general DB prefix
	* @param string/array $arg
	* @param string $debug
	* @desc Insert a row into the table<br />
	* <br />
	* Example:<br />
	* <code>$sql->db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0");</code>
	*
	* @access public
	*/
	function db_Insert($tableName, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($tableName);
		$this->mySQLcurTable = $table;
		$REPLACE = false; // kill any PHP notices
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
			else
			{
				$REPLACE = FALSE;
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
			if(!isset($arg['_FIELD_TYPES']) && ALLOW_AUTO_FIELD_DEFS)
			{
				$arg = array_merge($arg, $this->getFieldDefs($tableName));
			}


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

			if($REPLACE === FALSE)
			{
				$query = "INSERT INTO `".$this->mySQLPrefix."{$table}` ({$keyList}) VALUES ({$valList})";
			}
			else
			{
				$query = "REPLACE INTO `".$this->mySQLPrefix."{$table}` ({$keyList}) VALUES ({$valList})";
			}

		}
		else
		{
			$query = 'INSERT INTO '.$this->mySQLPrefix."{$table} VALUES ({$arg})";
		}

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
		}

		$this->mySQLresult = $this->db_Query($query, NULL, 'db_Insert', $debug, $log_type, $log_remark);
		if ($this->mySQLresult)
		{
			if(true === $REPLACE)
			{
				$tmp = mysql_affected_rows($this->mySQLaccess);
				$this->dbError('db_Replace');
				// $tmp == -1 (error), $tmp == 0 (not modified), $tmp == 1 (added), greater (replaced)
				if ($tmp == -1) { return false; } // mysql_affected_rows error
				return $tmp;
			}

			$tmp = mysql_insert_id($this->mySQLaccess);
			$this->dbError('db_Insert');
			return ($tmp) ? $tmp : TRUE; // return true even if table doesn't have auto-increment.
		}
		else
		{
			$this->dbError("db_Insert ({$query})");
			return FALSE;
		}
	}

	/**
	* @return int Last insert ID or false on error
	* @param string $table
	* @param array $arg
	* @param string $debug
	* @desc Insert/REplace a row into the table<br />
	* <br />
	* Example:<br />
	* <code>$sql->db_Replace("links", $array);</code>
	*
	* @access public
	*/
	function db_Replace($table, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$arg['_REPLACE'] = TRUE;
		return $this->db_Insert($table, $arg, $debug, $log_type, $log_remark);
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
	* <code>$sql->db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");</code>
	* <br />
	* OR as second connection<br />
	* <code>$sql2 = new db;
	* $sql2->db_Update("user", "user_viewed = '$u_new' WHERE user_id = '".USERID."' ");</code><br />
	*
	* @access public
	*/
	function db_Update($tableName, $arg, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($tableName);
		$this->mySQLcurTable = $table;

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
		}

	  	if (is_array($arg))  // Remove the need for a separate db_UpdateArray() function.
	  	{
		   	$new_data = '';
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
			if(!isset($arg['_FIELD_TYPES']) && ALLOW_AUTO_FIELD_DEFS)
			{
				$arg = array_merge($arg, $this->getFieldDefs($tableName));
			}

			$fieldTypes = $this->_getTypes($arg);
			foreach ($arg['data'] as $fn => $fv)
			{
				$new_data .= ($new_data ? ', ' : '');
				$new_data .= "`{$fn}`=".$this->_getFieldValue($fn, $fv, $fieldTypes);
			}
			$arg = $new_data .(isset($arg['WHERE']) ? ' WHERE '. $arg['WHERE'] : '');
		}

		$query = 'UPDATE '.$this->mySQLPrefix.$table.' SET '.$arg;
		if ($result = $this->mySQLresult = $this->db_Query($query, NULL, 'db_Update', $debug, $log_type, $log_remark))
		{
			$result = mysql_affected_rows($this->mySQLaccess);
			$this->dbError('db_Update');
			if ($result == -1) { return false; }	// Error return from mysql_affected_rows
			return $result;
		}
		else
		{
			$this->dbError("db_Update ({$query})");
			return FALSE;
		}
	}

	function _getTypes(&$arg)
	{
		if(isset($arg['_FIELD_TYPES']))
		{
			if(!isset($arg['_FIELD_TYPES']['_DEFAULT']))
			{
				$arg['_FIELD_TYPES']['_DEFAULT'] = 'todb';
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

			case 'str':
			case 'string':
				return "'{$fieldValue}'";
				break;

			case 'float':
				return (float) $fieldValue;
			break;

			case 'null':
				return ($fieldValue && $fieldValue !== 'NULL' ? "'{$fieldValue}'" : 'NULL');
				break;

			case 'escape':
				return "'".mysql_real_escape_string($fieldValue)."'";
				break;

			case 'todb':
			default:
				if($fieldValue == '') { return "''"; }
				$e107 = e107::getInstance();
				return "'".$e107->tp->toDB($fieldValue)."'";
				break;
	  	}
	}

	/* Similar to db_Update(), but splits the variables and the 'WHERE' clause.
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

	  if(!$this->mySQLaccess)
	  {
		global $db_ConnectionID;
        $this->mySQLaccess = $db_ConnectionID;
	  }

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
		$result = mysql_affected_rows($this->mySQLaccess);
		if ($result == -1) return FALSE;	// Error return from mysql_affected_rows
		return $result;
	  }
	  else
	  {
		$this->dbError("db_Update ($query)");
		return FALSE;
	  }
	}

	/**
	* @return array MySQL row
	* @param string $mode
	* @desc Fetch an array containing row data (see PHP's mysql_fetch_array() docs)<br />
	* <br />
	* Example :<br />
	* <code>while($row = $sql->db_Fetch()){
	*  $text .= $row['username'];
	* }</code>
	*
	* @access public
	*/
	function db_Fetch($type = MYSQL_ASSOC)
	{
		if (!(is_int($type)))
		{
			$type=MYSQL_ASSOC;
		}
		$b = microtime();
		if($this->mySQLresult)
		{
			$row = @mysql_fetch_array($this->mySQLresult,$type);
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
	* @return int number of affected rows or false on error
	* @param string $table
	* @param string $fields
	* @param string $arg
	* @desc Count the number of rows in a select<br />
	* <br />
	* Example:<br />
	* <code>$topics = $sql->db_Count("forum_t", "(*)", "thread_forum_id='".$forum_id."' AND thread_parent='0'");</code>
	*
	* @access public
	*/
	function db_Count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($table);

		if ($fields == 'generic')
		{
			$query=$table;
			if ($this->mySQLresult = $this->db_Query($query, NULL, 'db_Count', $debug, $log_type, $log_remark))
			{
				$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
				$this->dbError('db_Count');
				return $rows['COUNT(*)'];
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
			$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
			$this->dbError('db_Count');
			return $rows[0];
		}
		else
		{
			$this->dbError("db_Count({$query})");
			return FALSE;
		}
	}


	/**
	* @return void
	* @desc Closes the mySQL server connection.<br />
	* <br />
	* Only required if you open a second connection.<br />
	* Native e107 connection is closed in the footer.php file<br />
	* <br />
	* Example :<br />
	* <code>$sql->db_Close();</code>
	*
	* @access public
	*/
	function db_Close()
	{
		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}
		e107::getSingleton('e107_traffic')->BumpWho('db Close', 1);
		$this->mySQLaccess = NULL; // correct way to do it when using shared links.
		$this->dbError('dbClose');
	}


	/**
	* @return int number of affected rows, or false on error
	* @param string $table
	* @param string $arg
	* @desc Delete rows from a table<br />
	* <br />
	* Example:
	* <code>$sql->db_Delete("tmp", "tmp_ip='$ip'");</code><br />
	* <br />
	* @access public
	*/
	function db_Delete($table, $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}


		if (!$arg)
		{
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.$this->mySQLPrefix.$table, NULL, 'db_Delete', $debug, $log_type, $log_remark))
			{
				$this->dbError('db_Delete');
				return $result;
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
				$tmp = mysql_affected_rows($this->mySQLaccess);
				$this->dbError('db_Delete');
				return $tmp;
			}
			else
			{
				$this->dbError('db_Delete ('.$arg.')');
				return FALSE;
			}
		}
	}


	/**
	* @return unknown
	* @desc Enter description here...
	* @access private
	*/
	function db_Rows()
	{
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		$this->dbError('db_Rows');
		return $rows;
	}


	/**
	* @return void
	* @param unknown $mode
	* @desc Enter description here...
	* @access private
	*/
	function db_SetErrorReporting($mode)
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
	public function db_Select_gen($query, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		global $db_mySQLQueryCount;

		$this->tabset = FALSE;

		$query .= " "; // temp fix for failing regex below, when there is no space after the table name;

		if(strpos($query,'`#') !== FALSE)
		{
			$query = str_replace('`#','`'.$this->mySQLPrefix,$query);	// This simple substitution should be OK when backticks used
			//$query = preg_replace_callback("/\s`#([\w]*?)`\W/", array($this, 'ml_check'), $query);
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
			{	// Need to check mysql_affected_rows() - to return number of rows actually updated
				$tmp = mysql_affected_rows($this->mySQLaccess);
				$this->dbError('db_Select_gen');
				return $tmp;
			}
			$this->dbError('db_Select_gen');		// No row count here
			return TRUE;
		}
		else
		{	// Successful query which does return a row count - get the count and return it
			$this->dbError('db_Select_gen');
			return $this->db_Rows();
		}
	}

	function ml_check($matches)
	{
		$table = $this->db_IsLang($matches[1]);
		if($this->tabset == false)
		{
			$this->mySQLcurTable = $table;
			$this->tabset = true;
		}
		return ' `'.$this->mySQLPrefix.$table.'`'.substr($matches[0],-1);
	}


	/**
	* @return unknown
	* @param unknown $offset
	* @desc Enter description here...
	* @access private
	*/
	/* Function not used
	function db_Fieldname($offset)
	{
		$result = @mysql_field_name($this->mySQLresult, $offset);
		return $result;
	}
	*/


	/**
	* @return unknown
	* @desc Enter description here...
	* @access private
	*/
	/*
	function db_Field_info()
	{
		$result = @mysql_fetch_field($this->mySQLresult);
		return $result;
	}
	*/


	/**
	* @return unknown
	* @desc Enter description here...
	* @access private
	*/
	/* Function not used
	function db_Num_fields()
	{
		$result = @mysql_num_fields($this->mySQLresult);
		return $result;
	}
	*/


	/**
	* Check for the existence of a matching language table when multi-language tables are active.
	* @param string $table Name of table, without the prefix.
	* @access private
	* @return name of the language table (eg. lan_french_news)
	*/
	function db_IsLang($table,$multiple=FALSE)
	{
		global $pref;

		//When running a multi-language site with english included. English must be the main site language.

		if ((!$this->mySQLlanguage || !$pref['multilanguage'] || $this->mySQLlanguage=='English') && $multiple==FALSE)
		{
		  	return $table;
		}

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}

		if($multiple == FALSE)
		{
			$mltable = "lan_".strtolower($this->mySQLlanguage.'_'.$table);
			return ($this->db_Table_exists($table,$this->mySQLlanguage)) ? $mltable : $table;
		}
		else // return an array of all matching language tables. eg [french]->e107_lan_news
		{
			if(!is_array($table))
			{
				$table = array($table);
			}

			foreach($this->mySQLtablelist as $tab)
			{
 				if(stristr($tab, $this->mySQLPrefix."lan_") !== FALSE)
				{
					$tmp = explode("_",str_replace($this->mySQLPrefix."lan_","",$tab));
			   		$lng = $tmp[0];
                    foreach($table as $t)
					{
                    	if(eregi($t."$",$tab))
						{
							$lanlist[$lng][$this->mySQLPrefix.$t] = $tab;
						}
					}
			  	}
			}

			return (varset($lanlist)) ? $lanlist : FALSE;
		}
	// -------------------------


	}

	/**
	* @return array
	* @param string fields to retrieve
	* @desc returns fields as structured array
	* @access public
	*/
	function db_getList($fields = 'ALL', $amount = FALSE, $maximum = FALSE, $ordermode=FALSE)
	{
		$list = array();
		$counter = 1;
		while ($row = $this->db_Fetch())
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
	* @return integer
	* @desc returns total number of queries made so far
	* @access public
	*/
	function db_QueryCount()
	{
		global $db_mySQLQueryCount;
		return $db_mySQLQueryCount;
	}


    /*
    	Multi-language Query Function.
	*/
	function db_Query_all($query,$debug="")
	{
        $error = "";

		$query = str_replace("#",$this->mySQLPrefix,$query);

        if(!$this->db_Query($query))
		{  // run query on the default language first.
        	$error .= $query. " failed";
		}

        $tmp = explode(" ",$query);
      	foreach($tmp as $val)
		{
   			if(strpos($val,$this->mySQLPrefix) !== FALSE)
			{
    			$table[] = str_replace($this->mySQLPrefix,"",$val);
				$search[] = $val;
			}
		}

     // Loop thru relevant language tables and replace each tablename within the query.
        if($tablist = $this->db_IsLang($table,TRUE))
		{
			foreach($tablist as $key=>$tab)
			{
				$querylan = $query;
                foreach($search as $find)
				{
                    $lang = $key;
					$replace = ($tab[$find] !="") ? $tab[$find] : $find;
               	  	$querylan = str_replace($find,$replace,$querylan);
				}

				if(!$this->db_Query($querylan))
				{ // run query on other language tables.
					$error .= $querylan." failed for language";
				}
			 	if($debug){ echo "<br />** lang= ".$querylan; }
			}
		}


		return ($error)? FALSE : TRUE;
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

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
		}

		if ($prefix == '') $prefix = $this->mySQLPrefix;

		if (FALSE === ($result = mysql_query('SHOW COLUMNS FROM '.$prefix.$table,$this->mySQLaccess)))
		{
			return FALSE;		// Error return
		}
		$ret = array();
        if (mysql_num_rows($result) > 0)
		{
			while ($row = mysql_fetch_assoc($result))
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



	/**
	 *	Determines if a plugin field (and key) exist. OR if fieldid is numeric - return the field name in that position.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $fieldid - Numeric offset or field/key name
	 *	@param string $key - PRIMARY|INDEX|UNIQUE - type of key when searching for key name
	 *	@param boolean $retinfo = FALSE - just returns array of field names. TRUE - returns all field info
	 *	@return array|boolean - FALSE on error, field information on success
	 */
    function db_Field($table,$fieldid="",$key="", $retinfo = FALSE)
	{
		if(!$this->mySQLdefaultdb)
		{
			global $mySQLdefaultdb;
			$this->mySQLdefaultdb = $mySQLdefaultdb;
		}
		$convert = array("PRIMARY"=>"PRI","INDEX"=>"MUL","UNIQUE"=>"UNI");
		$key = (isset($convert[$key])) ? $convert[$key] : "OFF";

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
		}

        $result = mysql_query("SHOW COLUMNS FROM ".$this->mySQLPrefix.$table,$this->mySQLaccess);
        if (mysql_num_rows($result) > 0)
		{
			$c=0;
			while ($row = mysql_fetch_assoc($result))
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
						return TRUE;
					}
				}
				$c++;
			}
		}
		return FALSE;
	}


	/**
	 * A pointer to mysql_real_escape_string() - see http://www.php.net/mysql_real_escape_string
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

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}

		return mysql_real_escape_string($data,$this->mySQLaccess);
	}


	/**
	 * Verify whether a table exists, without causing an error
	 *
	 * @param string $table Table name without the prefix
	 * @param string $lanMode [optional] When set to TRUE, searches for multilanguage tables
	 * @return boolean TRUE if exists
	 *
	 * NOTES: Slower (28ms) than "SELECT 1 FROM" (4-5ms), but doesn't produce MySQL errors.
	 * Multiple checks on a single page will only use 1 query. ie. faster on multiple calls.
	 */
	public function db_Table_exists($table,$language='')
	{
		global $pref;
		$table = strtolower($table); // precaution for multilanguage

		if($language && ($language != $pref['sitelanguage']))
		{
			if(!isset($this->mySQLtableListLanguage[$language]))
			{
				$this->mySQLtableListLanguage = $this->db_mySQLtableList($language);
			}
			return in_array('lan_'.strtolower($language)."_".$table,$this->mySQLtableListLanguage[$language]);
		}
		else
		{
			if(!$this->mySQLtableList)
			{
				$this->mySQLtableList = $this->db_mySQLtableList();
			}
			return in_array($table,$this->mySQLtableList);
		}

	}


	/**
	 * Populate $this->mySQLtableList;
	 * @return array
	 */
	private function db_mySQLtableList($language='')
	{
		if($language)
		{
			if(!isset($this->mySQLtableListLanguage[$language]))
			{
				$table = array();
				if($res = $this->db_Query("SHOW TABLES LIKE '".$this->mySQLPrefix."lan_".strtolower($language)."%' "))
				{
					while($rows = $this->db_Fetch(MYSQL_NUM))
					{
						$table[] = str_replace($this->mySQLPrefix,"",$rows[0]);
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
			if($res = $this->db_Query("SHOW TABLES LIKE '".$this->mySQLPrefix."%' "))
			{
				while($rows = $this->db_Fetch(MYSQL_NUM))
				{
					$table[] = str_replace($this->mySQLPrefix,"",$rows[0]);
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
	 * Return a filtered list of DB tables.
	 * @param object $mode [optional] all|lan|nolan
	 * @return array
	 */
	public function db_TableList($mode='all')
	{

		if(!$this->mySQLtableList)
		{
			$this->mySQLtableList = $this->db_mySQLtableList();

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

	function db_CopyTable($oldtable, $newtable, $drop = FALSE, $data = FALSE)
	{
		$old = $this->mySQLPrefix.strtolower($oldtable);
		$new = $this->mySQLPrefix.strtolower($newtable);

		if ($drop)
		{
			$this->db_Select_gen("DROP TABLE IF EXISTS {$new}");
		}

		//Get $old table structure
		$this->db_Select_gen('SET SQL_QUOTE_SHOW_CREATE = 1');

		$qry = "SHOW CREATE TABLE {$old}";
		if ($this->db_Select_gen($qry))
		{
			$row = $this->db_Fetch(MYSQL_NUM);
			$qry = $row[1];
			//        $qry = str_replace($old, $new, $qry);
			$qry = preg_replace("#CREATE\sTABLE\s`{0,1}".$old."`{0,1}\s#", "CREATE TABLE `{$new}` ", $qry, 1); // More selective search
		}
		else
		{
			return FALSE;
		}

		if(!$this->db_Table_exists($newtable))
		{
			$result = $this->db_Query($qry);
		}

		if ($data) //We need to copy the data too
		{
			$qry = "INSERT INTO {$new} SELECT * FROM {$old}";
			$result = $this->db_Select_gen($qry);
		}
		return $result;
	}



	/**
	* @return text string relating to error (empty string if no error)
	* @param unknown $from
	* @desc Calling method from within this class
	* @access private
	*/
	function dbError($from)
	{
		$this->mySQLlastErrNum = mysql_errno();
		$this->mySQLlastErrText = '';
		if ($this->mySQLlastErrNum == 0)
		{
			return '';
		}
		$this->mySQLlastErrText = mysql_error();		// Get the error text.
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

	function getLastQuery()
	{
		return $this->mySQLlastQuery;
	}

	/**
	 * Check if MySQL version is utf8 compatible and may be used as it accordingly to the user choice
	 *
	 * @TODO Simplify when the conversion script will be available
	 *
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
		if (varset($mySQLcharset) != 'utf8')
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
			    @mysql_query("SET NAMES `$charset`");
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
	 *		b) in the directory e_DB_CACHEDIR - file name $tableName.php
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
	 *
	 *	@return boolean|array - FALSE if not found/not to be used. Array of field names and processing types and null overrides if found
	 */
	public function getFieldDefs($tableName)
	{
		if (!isset($this->dbFieldDefs[$tableName]))
		{
			if (is_readable(e_DB_CACHE.$tableName.'.php'))
			{
				$temp = file_get_contents(e_DB_CACHE.$tableName.'.php', FILE_TEXT);
				if ($temp !== FALSE)
				{
					$array = e107::getArrayStorage();
					$typeDefs = $array->ReadArray($temp);
					unset($temp);
					$this->dbFieldDefs[$tableName] = $typeDefs;
				}
			}
			else
			{		// Need to try and find a table definition
				$searchArray = array(e_ADMIN.'sql/db_field_defs.php');
				$sqlFiles = (array) e107::getPref('e_sql_list', array()); // kill any PHP notices
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


	/*
	 *	Search the specified file for a field type definition of the specified table.
	 *
	 *	If found, generate and save a cache file in the e_DB_CACHE directory,
	 *	Always also update $this->dbFieldDefs[$tableName] - FALSE if not found, data if found
	 *
	 *	@param	string $defFile - file name, including path
	 *	@param	string $tableName - name of table sought
	 *
	 *	@return boolean TRUE on success, FALSE on not found (some errors intentionally ignored)
	 */
	protected function loadTableDef($defFile, $tableName)
	{
		$result = FALSE;
		// Read the file using the array handler routines
		// File structure is a nested array - first level is table name, second level is either FALSE (for do nothing) or array(_FIELD_DEFS => array(), _NOTNULL => array())
		$temp = file_get_contents($defFile);
		// Strip any comments  (only /*...*/ supported)
		$temp = preg_replace("#\/\*.*?\*\/#mis", '', $temp);
		//echo "Check: {$defFile}, {$tableName}<br />";
		if ($temp !== FALSE)
		{
			$array = e107::getArrayStorage();
			$typeDefs = $array->ReadArray($temp);
			unset($temp);
			if (isset($typeDefs[$tableName]))
			{
				$this->dbFieldDefs[$tableName] = $typeDefs[$tableName];
				$fileData = $array->WriteArray($typeDefs[$tableName], FALSE);
				if (FALSE === file_put_contents(e_DB_CACHE.$tableName.'.php', $fileData))
				{	// Could do something with error - but mustn't return FALSE - would trigger auto-generated structure
				}
				$result = TRUE;
			}
		}

		if (!$result)
		{
			$this->dbFieldDefs[$tableName] = FALSE;
		}
		return $result;
	}


	/**
	 *	Creates a field type definition from the structure of the table in the DB
	 *
	 *	Generate and save a cache file in the e_DB_CACHE directory,
	 *	Also update $this->dbFieldDefs[$tableName] - FALSE if error, data if found
	 *
	 *	@param	string $tableName - name of table sought
	 *
	 *	@return boolean TRUE on success, FALSE on not found (some errors intentionally ignored)
	 */
	protected function makeTableDef($tableName)
	{
		require_once(e_HANDLER.'db_table_admin_class.php');
		$dbAdm = new db_table_admin();

		$baseStruct = $dbAdm->get_current_table($tableName);
		$fieldDefs = $dbAdm->parse_field_defs($baseStruct[0][2]);					// Required definitions
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
						case 'shortint' :
						case 'tinyint' :
							$outDefs['_FIELD_TYPES'][$v['name']] = 'int';
							break;
						case 'char' :
						case 'text' :
						case 'varchar' :
							$outDefs['_FIELD_TYPES'][$v['name']] = 'todb';
							break;
					}
					if (isset($v['nulltype']) && !isset($v['default']))
					{
						$outDefs['_NOTNULL'][$v['name']] = '';
					}
					break;
				case 'pkey' :
				case 'ukey' :
				case 'key' :
					break;			// Do nothing with keys for now
				default :
					echo "Unexpected field type: {$k} => {$v['type']}<br />";
			}
		}
		$array = e107::getArrayStorage();
		$this->dbFieldDefs[$tableName] = $outDefs;
		$toSave = $array->WriteArray($outDefs, FALSE);	// 2nd parameter to TRUE if needs to be written to DB
		if (FALSE === file_put_contents(e_DB_CACHE.$tableName.'.php', $toSave))
		{	// Could do something with error - but mustn't return FALSE - would trigger auto-generated structure
			echo "Error writing file: ".e_DB_CACHE.$tableName.'.php'.'<br />';
		}
	}

}

/**
 * BC
 */
class db extends e_db_mysql
{

}

?>