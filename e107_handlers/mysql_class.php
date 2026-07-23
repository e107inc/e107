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

require_once(__DIR__.'/e_db_interface.php');
require_once(__DIR__.'/e_db_legacy_trait.php');

if(defined('MYSQL_LIGHT'))
{
	define('E107_DEBUG_LEVEL', 0);
	define('e_QUERY', '');
	$path = (MYSQL_LIGHT !== true ? MYSQL_LIGHT : '');
	$config = require_once($path.'e107_config.php');

	if(is_array($config) && !empty($config['database'])) // New e107_config.php format. v2.4+
	{
		$dbInfo = $config['database'];
		define('MPREFIX', $dbInfo['prefix'] ?? '');
		$sql = new db;
		$sql->db_Connect(
			$dbInfo['server']   ?? '',
			$dbInfo['user']     ?? '',
			$dbInfo['password'] ?? '',
			$dbInfo['db']       ?? ''
		);
	}
	else // old e107_config.php format with legacy globals.
	{
		define('MPREFIX', $mySQLprefix ?? '');
		$sql = new db;
		$sql->db_Connect(
			$mySQLserver   ?? '',
			$mySQLuser     ?? '',
			$mySQLpassword ?? '',
			$mySQLdefaultdb ?? ''
		);
	}
}
elseif(defined('E107_INSTALL'))
{
	define('E107_DEBUG_LEVEL', 0);
	$config = require('e107_config.php');

	if(is_array($config) && !empty($config['database'])) // New e107_config.php format. v2.4+
	{
		$sql_info = $config['database']; // server / user / password / db / prefix
	}
	else // old e107_config.php format with legacy globals.
	{
		$sql_info = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');
	}
	e107::getInstance()->initInstallSql($sql_info);
	$sql = new db;
	$sql->db_Connect(
		$sql_info['server']   ?? ($sql_info['mySQLserver']   ?? ''),
		$sql_info['user']     ?? ($sql_info['mySQLuser']     ?? ''),
		$sql_info['password'] ?? ($sql_info['mySQLpassword'] ?? ''),
		$sql_info['db']       ?? ($sql_info['mySQLdefaultdb'] ?? '')
	);
}
else
{
	if (!defined('e107_INIT')) { exit; }
}

$db_time = 0.0;				// Global total time spent in all db object queries
$db_mySQLQueryCount = 0;	// Global total number of db object queries (all db's)

$db_ConnectionID = NULL;	// Stores ID for the first DB connection used - which should be the main E107 DB - then used as default


/**
 *
 */
class e_db_mysql implements e_db
{

	use e_db_legacy;
	use e_db_common;

	// Shared connection state lives in e_db_common (ConnectionTrait);
	// only driver-specific members are declared here.
	public      $mySQLserver;
	public      $mySQLuser;
	protected   $mySQLpassword;
	protected   $mySQLdefaultdb;
	protected   $mySQLport = 3306;

	/** @var mysqli */
	protected   $mySQLaccess;
	protected   $mySQLrows;

	protected   $mySQLlastQuery = '';

	public      $mySQLinfo;
	public      $mySQLtablelist = array();

	protected	$dbFieldDefs = array();		// Local cache - Field type definitions for _FIELD_DEFS and _NOTNULL arrays
	protected   $mySqlServerInfo = '?';			// Server info - needed for various things

	private     $stringifyFetch = false;	// Prepared-statement results carry native types; stringify on fetch for PDO parity.

	/**
	* Constructor - gets language options from the cookie or session
	* @access public
	*/
	public function __construct()
	{
		e107::getSingleton('e107_traffic')->BumpWho('Create db object', 1);

		$config = e107::getMySQLConfig();

		$this->mySQLserver      = isset($config['mySQLserver']) ? $config['mySQLserver'] : '';
		$this->mySQLuser        = isset($config['mySQLuser']) ? $config['mySQLuser'] : '';
		$this->mySQLpassword    = isset($config['mySQLpassword']) ? $config['mySQLpassword'] : '';
		$this->mySQLdefaultdb   = isset($config['mySQLdefaultdb']) ? $config['mySQLdefaultdb'] : '';
		$this->mySQLport        = varset($config['port'], 3306);
		$this->mySQLPrefix      = varset($config['mySQLprefix'], MPREFIX);

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

		/**
		 * Revert PHP 8.1 mysqli default error mode
		 * @link https://github.com/php/php-src/blob/4025cf2875f895e9f7193cebb1c8efa4290d052e/UPGRADING#L101-L105
		 */
		mysqli_report(MYSQLI_REPORT_OFF);
	}

	/**
	 * @return false
	 */
	function getPDO()
	{
		return false;
	}

	/**
	 * Connect ONLY  - used in v2.x
	 * @param string $mySQLserver IP Or hostname of the MySQL server
	 * @param string $mySQLuser MySQL username
	 * @param string $mySQLpassword MySQL Password
	 * @param bool $newLink force a new link connection if TRUE. Default FALSE
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

		// Save the connection resource
		if ($db_ConnectionID == null)
		{
			$db_ConnectionID = $this->mySQLaccess;
		}

		return true;
	}


	/**
	 * Get Server Info
	 * @return string
	 */
	public function getServerInfo()
	{
		$this->_getMySQLaccess();
		return $this->mySqlServerInfo;
	}



	/**
	 * Select the database to use.
	 * @param string $database name
	 * @param string $prefix prefix . eg. e107_
	 * @param boolean $multiple set to maintain connection to a secondary database.
	 * @return boolean true when database selection was successful otherwise false.
	 */
	public function database($database, $prefix = MPREFIX, $multiple=false)
	{
		$this->mySQLdefaultdb 	= $database;
		$this->mySQLPrefix 		= $prefix;

		if($multiple === true)
		{
			$this->mySQLPrefix 		= "`".str_replace('`', '``', $database)."`.".$prefix;
			return true;
		}

		if (!@mysqli_select_db($this->mySQLaccess, $database))
		{
			return false;
		}

		return true;
	}

	/**
	 * @deprecated v2.0.0 No-op retained for backwards compatibility; query
	 *             performance output lives in the debug panel ({@see e107_db_debug}).
	 * @return void
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
	function log($log_type = '', $log_remark = '', $log_query = '')
	{
		$tp = e107::getParser();
		list($time_usec, $time_sec) = explode(" ", microtime());
		$uid = (USER) ? USERID : '0';
		$userstring = ( USER === true ? USERNAME : "LAN_ANONYMOUS");
		$ip = e107::getIPHandler()->getIP(false);
		$qry = $tp->toDB($log_query);

		$insert = array(
			'dblog_datestamp'   => $time_sec,
			'dblog_microtime'   => $time_usec,
			'dblog_type'        => $log_type,
			'dblog_eventcode'   => 'DBDEBUG',
			'dblog_user_id'     => $uid,
			'dblog_user_name'   => $userstring,
			'dblog_ip'          => $ip,
			'dblog_caller'      => '',
			'dblog_title'       => $log_remark,
			'dblog_remarks'     => is_array($qry) ? e107::serialize($qry) : $qry

		);

		$this->insert('dblog', $insert);
	}


	/**
	* This is the 'core' routine which handles much of the interface between other functions and the DB
	*
	* If a SELECT query includes SQL_CALC_FOUND_ROWS, the value of FOUND_ROWS() is retrieved and stored in $this->total_results
	*
	* The array ['PREPARE' => ..., 'BIND' => ..., 'EXECUTE' => ...] contract is
	* internal plumbing; new code should call {@see e_db::execute()} instead.
	*
	* @param string|array $query
	* @param mysqli $rli Your own mysqli connection instead of the one in this object
	* @return boolean|mysqli_result - as mysqli_query() function.
	*			FALSE indicates an error
	*			For SELECT, SHOW, DESCRIBE, EXPLAIN and others returning a result set, returns a resource
	*			TRUE indicates success in other cases
	*/
	public function db_Query($query, $rli = NULL, $qry_from = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('db_Query', 'Use $sql->execute($query, $params); it accepts the same SQL with a friendlier parameter map.');

		global $db_time,$db_mySQLQueryCount,$queryinfo;
		$db_mySQLQueryCount++;

		$this->mySQLlastQuery = $query;

		if ($debug == 'now')
		{
			$this->dbg->log($query);
		}
		if ($debug !== FALSE || strpos($_SERVER['QUERY_STRING'], 'showsql') !== false)
		{
			$debugQry = is_array($query) ? print_a($query,true) : $query;
			$queryinfo[] = "<b>{$qry_from}</b>: ".$debugQry;
		}
		if ($log_type != '')
		{
			$this->log($log_type, $log_remark, $query);
		}

		$this->_getMySQLaccess();

		$this->stringifyFetch = false;

		$b = microtime();

		if(is_array($query) && !empty($query['PREPARE']))
		{
			$sQryRes = $this->_executePrepared($query, $qry_from);
		}
		else
		{
			$sQryRes = is_null($rli) ? @mysqli_query($this->mySQLaccess, $query) : @mysqli_query($rli, $query);
			$this->mySQLlastErrNum = mysqli_errno($this->mySQLaccess);
			$this->mySQLlastErrText = mysqli_error($this->mySQLaccess);
		}

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
			global $db_debug;
			$aTrace = debug_backtrace();
			$pTable = (string) $this->mySQLcurTable;
			if (!strlen($pTable)) {
				$pTable = '(complex query)';
			} else {
				$this->mySQLcurTable = ''; // clear before next query
			}
			if(is_object($db_debug))
			{
				$buglink = is_null($rli) ? $this->mySQLaccess : $rli;

				if(is_array($query))
				{
					$query['BIND'] = isset($query['BIND']) ? $query['BIND'] : null;
					$query = "PREPARE: " . $query['PREPARE'] . "<br />BIND:" . print_a($query['BIND'], true);
				}

				$db_debug->Mark_Query($query, $buglink, $sQryRes, $aTrace, $mytime, $pTable);
			}
		}

		return $sQryRes;
	}

	/**
	 * Execute the array-form prepared-statement contract on mysqli:
	 * ['PREPARE' => SQL with :named placeholders,
	 *  'BIND'    => [name => ['value' => mixed, 'type' => e_db::PARAM_*]],
	 *  'EXECUTE' => [name => value]]
	 *
	 * Mirrors the e_db_pdo behavior: when 'EXECUTE' is non-empty it takes
	 * precedence over 'BIND' and every value binds as a string, exactly like
	 * PDOStatement::execute($input_parameters). Returns the result set for
	 * 'db_Select' calls, a row/affected count otherwise, false on error.
	 *
	 * @param array $query
	 * @param string $qry_from
	 * @return mysqli_result|int|false
	 */
	private function _executePrepared($query, $qry_from)
	{
		if(!function_exists('mysqli_stmt_get_result'))
		{
			$this->mySQLlastErrNum = -1;
			$this->mySQLlastErrText = 'Prepared statements on the mysqli backend require the mysqlnd driver (mysqli_stmt_get_result() is missing)';
			return false;
		}

		list($sql, $order) = $this->_compileNamedQuery($query['PREPARE']);

		$bind = !empty($query['BIND']) ? $query['BIND'] : array();
		$execute = !empty($query['EXECUTE']) ? $query['EXECUTE'] : array();
		$useExecute = !empty($execute);

		$stmt = mysqli_prepare($this->mySQLaccess, $sql);

		if($stmt === false)
		{
			$this->mySQLlastErrNum = mysqli_errno($this->mySQLaccess);
			$this->mySQLlastErrText = mysqli_error($this->mySQLaccess);
			return false;
		}

		$types = '';
		$values = array();

		foreach($order as $i => $name)
		{
			if($useExecute)
			{
				if(!array_key_exists($name, $execute))
				{
					$this->mySQLlastErrNum = 2031; // CR_NO_DATA
					$this->mySQLlastErrText = 'No value supplied for placeholder :'.$name;
					mysqli_stmt_close($stmt);
					return false;
				}
				$types .= 's'; // PDOStatement::execute() binds every input parameter as PARAM_STR
				$values[$i] = $execute[$name];
				continue;
			}

			if(!array_key_exists($name, $bind))
			{
				$this->mySQLlastErrNum = 2031; // CR_NO_DATA
				$this->mySQLlastErrText = 'No value supplied for placeholder :'.$name;
				mysqli_stmt_close($stmt);
				return false;
			}

			$type = isset($bind[$name]['type']) ? (int) $bind[$name]['type'] : e_db::PARAM_STR;
			$value = isset($bind[$name]['value']) ? $bind[$name]['value'] : null;

			switch($type)
			{
				case e_db::PARAM_INT:
					$types .= 'i';
					$values[$i] = $value;
					break;

				case e_db::PARAM_BOOL:
					$types .= 'i';
					$values[$i] = (int) $value;
					break;

				case e_db::PARAM_NULL:
					$types .= 's';
					$values[$i] = null;
					break;

				case e_db::PARAM_LOB: // 's' is binary-safe; 'b' would require mysqli_stmt_send_long_data()
				case e_db::PARAM_STR:
				default:
					$types .= 's';
					$values[$i] = $value;
					break;
			}
		}

		if($types !== '')
		{
			$params = array($types);
			foreach(array_keys($values) as $i)
			{
				$params[] = &$values[$i];
			}

			if(!call_user_func_array(array($stmt, 'bind_param'), $params))
			{
				$this->mySQLlastErrNum = mysqli_stmt_errno($stmt);
				$this->mySQLlastErrText = mysqli_stmt_error($stmt);
				mysqli_stmt_close($stmt);
				return false;
			}
		}

		if(!mysqli_stmt_execute($stmt))
		{
			$this->mySQLlastErrNum = mysqli_stmt_errno($stmt);
			$this->mySQLlastErrText = mysqli_stmt_error($stmt);
			mysqli_stmt_close($stmt);
			return false;
		}

		$this->mySQLlastErrNum = 0;
		$this->mySQLlastErrText = '';

		$result = mysqli_stmt_get_result($stmt); // buffered; survives closing the statement
		$affected = mysqli_stmt_affected_rows($stmt);
		mysqli_stmt_close($stmt);

		if($result instanceof mysqli_result)
		{
			$this->stringifyFetch = true;

			// Match e_db_pdo: 'db_Select' callers receive the result set itself,
			// others receive a count like PDOStatement::rowCount().
			return ($qry_from === 'db_Select') ? $result : mysqli_num_rows($result);
		}

		return (int) $affected; // no result set: affected rows, like PDOStatement::rowCount()
	}

	/**
	 * Compile a query with :named placeholders into positional ? placeholders.
	 *
	 * Quote-aware: single-quoted strings, double-quoted strings, backticked
	 * identifiers, comments and :: are consumed first, so tokens that merely
	 * look like placeholders inside them are never rewritten.
	 *
	 * @param string $sql
	 * @return array [compiled SQL, placeholder names in positional order]
	 */
	private function _compileNamedQuery($sql)
	{
		$order = array();

		$compiled = preg_replace_callback(
			'/\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|`[^`]*`|\/\*[\s\S]*?\*\/|--[^\r\n]*|::|:([A-Za-z0-9_]+)/',
			function ($matches) use (&$order)
			{
				if(!isset($matches[1]) || $matches[1] === '')
				{
					return $matches[0];
				}
				$order[] = $matches[1];
				return '?';
			},
			$sql
		);

		return array($compiled, $order);
	}

	/**
	 * Cast native int/float values to strings, so prepared-statement results
	 * (mysqlnd returns native types) match plain mysqli_query() results and
	 * the PDO::ATTR_STRINGIFY_FETCHES behavior of e_db_pdo.
	 *
	 * @param array $row
	 * @return array
	 */
	private function _stringifyRow($row)
	{
		foreach($row as $key => $value)
		{
			if(is_int($value) || is_float($value))
			{
				$row[$key] = (string) $value;
			}
		}

		return $row;
	}

	/**
	 * Documented at {@see e_db::select()}.
	 *
	 * @return int Number of rows or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see e_db::select()}.
	 */
	public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('select', 'Use the query builder: $sql->createQueryBuilder()->select(...)->from(\'table\')->where(...)->fetchAll().');

		global $db_mySQLQueryCount;

		// Fail closed if the table name is not a plain identifier - it is always
		// interpolated unquoted into the FROM clause below.
		if($this->_safeIdentifier($table) === false)
		{
			$this->dbError('select() invalid table identifier');
			return false;
		}

		$table = $this->hasLanguage($table);

		$this->mySQLcurTable = $table;

		// e107 v2.2 PDO bind params.
		if(!empty($arg) && is_array($noWhere))
		{

			$query = array(
				'PREPARE'   => 'SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table.' WHERE '.$arg,
				'EXECUTE'   => $noWhere
			);

			if ($this->mySQLresult = $this->db_Query($query, null, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->rowCount();
			}
			else
			{
				$this->dbError('select() with prepare/execute');
				return false;
			}

		}


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
	 * @return bool|int
	 */
	public function lastInsertId()
	{
		$tmp = (int) mysqli_insert_id($this->mySQLaccess);
		return ($tmp) ? $tmp : true; // return true even if table doesn't have auto-increment.
	}

	/**
	 * @param mysqli_result $result
	 * @return int
	 */
	public function rowCount($result=null)
	{
		if (!($result instanceof mysqli_result))
		{
			$result = $this->mySQLresult;
		}
		if (!$result)
		{
			return -1;
		}
		if ($result instanceof mysqli_result)
		{
			$this->mySQLrows = mysqli_num_rows($result);
		}
		elseif ($result === true) // no result set; report affected rows like PDOStatement::rowCount()
		{
			$this->mySQLrows = mysqli_affected_rows($this->mySQLaccess);
		}
		$this->dbError('db_Rows');
		return $this->mySQLrows;
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
				if($this->stringifyFetch)
				{
					$row = $this->_stringifyRow($row);
				}
				$this->dbError('db_Fetch');
				return $row;		// Success - return data
			}
		}
		$this->dbError('db_Fetch');
		return FALSE;				// Failure
	}

	/**
	 * Documented at {@see e_db::count()}.
	 *
	 * @return int number of affected rows or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see e_db::count()}.
	 */
	function count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('count', 'Use the query builder: $sql->createQueryBuilder()->selectCount()->from(\'table\')->where(...)->fetchOne().');

		// $fields === 'generic' is the documented raw-SQL escape hatch ($table holds
		// the full query); every other path interpolates $table unquoted into FROM,
		// so validate it as a plain identifier and fail closed otherwise.
		if ($fields != 'generic' && $this->_safeIdentifier($table) === false)
		{
			$this->dbError('count() invalid table identifier');
			return false;
		}

		$table = $this->hasLanguage($table);

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
		$this->_getMySQLaccess();
		e107::getSingleton('e107_traffic')->BumpWho('db Close', 1);
		@mysqli_close($this->mySQLaccess);
	}


	/**
	 * Documented at {@see e_db::delete()}.
	 *
	 * @return int number of affected rows, or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see e_db::delete()}.
	 */
	function delete($table, $arg = '', $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('delete', 'Use the query builder: $sql->createQueryBuilder()->delete(\'table\')->where(...)->execute().');

		// Fail closed if the table name is not a plain identifier - it is always
		// interpolated unquoted into the DELETE statement below.
		if($this->_safeIdentifier($table) === false)
		{
			$this->dbError('delete() invalid table identifier');
			return false;
		}

		$table = $this->hasLanguage($table);
		$this->mySQLcurTable = $table;

		$this->_getMySQLaccess();


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
	 * Execute an SQL statement with bound parameters. The canonical way to run
	 * SQL against an e107 database; the full contract is documented at {@see e_db::execute()}.
	 *
	 * @param string $sql SQL with optional `#table` markers and :named placeholders
	 * @param array $params name => value, or name => array('value' => mixed, 'type' => e_db::PARAM_*)
	 * @return int|bool row count for result sets (read rows with {@see e_db::fetch()});
	 *                  affected rows for DELETE/INSERT/REPLACE/UPDATE;
	 *                  true for other successful statements; false on error
	 */
	public function execute($sql, $params = array())
	{
		$sql = $this->_substituteTableNames($sql);

		if(!empty($params))
		{
			$bind = array();

			foreach($params as $name => $value)
			{
				$bind[$name] = is_array($value) ? $value : array('value' => $value, 'type' => $this->_detectParamType($value));
			}

			$query = array('PREPARE' => $sql, 'BIND' => $bind);
		}
		else
		{
			$query = $sql;
		}

		$result = $this->mySQLresult = $this->db_Query($query, null, 'db_Select');

		if($result === false)
		{
			$this->dbError('execute('.$sql.')');
			return false;
		}

		$this->dbError('execute');

		if($result instanceof mysqli_result) // result set; rows readable via fetch()
		{
			return $this->rowCount();
		}

		if(is_int($result)) // prepared statement without a result set: affected rows
		{
			return preg_match('#^\s*(DELETE|INSERT|REPLACE|UPDATE)#i', $sql) ? $result : true;
		}

		// plain query success without a result set
		if(preg_match('#^\s*(DELETE|INSERT|REPLACE|UPDATE)#i', $sql))
		{
			return mysqli_affected_rows($this->mySQLaccess);
		}

		return true;
	}

	/**
	 * Documented at {@see e_db::gen()}.
	 *
	 * @return boolean | int
	 * @deprecated v2.4.0 Use {@see e_db::execute()} instead; see {@see e_db::gen()}.
	 */
	public function gen($query, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('gen', 'Use $sql->execute($query, $params) with :named parameters; for ordinary CRUD prefer the query builder ($sql->createQueryBuilder()).');

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
	* @return int
	* @desc returns total number of queries made so far
	* @access public
	*/
	public function queryCount()
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

        if($tablist = $this->hasLanguage($table, true))
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
	public function fields($table, $prefix = '', $retinfo = false)
	{
		// $table becomes a SQL identifier (cannot be bound); validate it like field().
		if(($table = $this->_safeIdentifier($table)) === false)
		{
			return false;
		}

		$this->_getMySQLaccess();

		if ($prefix == '')
		{
			 $prefix = $this->mySQLPrefix;
		}

		if (false === ($result = $this->gen('SHOW COLUMNS FROM '.$prefix.$table)))
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

	/**
	 * @return int
	 */
	function columnCount()
	{
		return mysqli_num_fields($this->mySQLresult);
	}

	/**
	 * escape() without the deprecation notice, for internal legacy paths.
	 *
	 * @param string $data
	 * @return string
	 */
	protected function _escape($data)
	{
		$this->_getMySQLaccess();

		return mysqli_real_escape_string($this->mySQLaccess, (string) $data);
	}

	/**
	 * Documented at {@see \e107\Database\ConnectionInterface::quoteStringLiteral()}.
	 *
	 * @param string $value
	 * @return string quoted literal, including the surrounding quotes
	 */
	public function quoteStringLiteral($value)
	{
		return "'".$this->_escape($value)."'";
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
				$this->mySQLtableListLanguage = $this->_getTableList($language);
			}

			return in_array('lan_'.strtolower($language)."_".$table,$this->mySQLtableListLanguage[$language]);
		}
		else // regular search
		{
			if(!$this->mySQLtableList)
			{
				$this->mySQLtableList = $this->_getTableList();
			}

			return in_array($table,$this->mySQLtableList);
		}

	}

	/**
	 * Populate mySQLtableList and mySQLtableListLanguage
	 * TODO - better runtime cache - use e107::getRegistry() && e107::setRegistry()
	 * @return array
	 */
	protected function _getTableList($language='')
	{

		$database = !empty($this->mySQLdefaultdb) ? "FROM  `".$this->mySQLdefaultdb."`" : "";
		$prefix = $this->mySQLPrefix;

		if(strpos($prefix, ".") !== false) // eg. `my_database`.$prefix
		{
			$tmp = explode(".",$prefix);
			$prefix = $tmp[1];
		}

		// $prefix is interpolated into SHOW TABLES ... LIKE patterns below; escape LIKE
		// wildcards/metacharacters so a config prefix cannot match unintended tables
		// or break out of the string literal.
		$prefixLike = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $prefix);

		if($language)
		{
			// $language is interpolated into the LIKE pattern below; only accept a
			// plain identifier so it cannot break out of the string literal.
			if(!preg_match('/^[A-Za-z0-9_]+$/D', (string) $language))
			{
				return array();
			}

			if(!isset($this->mySQLtableListLanguage[$language]))
			{
				$table = array();
				if($res = $this->db_Query("SHOW TABLES ".$database." LIKE '".$prefixLike."lan_".strtolower($language)."%' "))
				{
					while($rows = $this->fetch('num'))
					{
						$table[] = str_replace($prefix,"",$rows[0]);
					}
				}

				return array($language =>$table);
			}
			else
			{
				return $this->mySQLtableListLanguage[$language];
			}
		}

		if(!$this->mySQLtableList)
		{
			$table = array();

			if($res = $this->db_Query("SHOW TABLES ".$database." LIKE '".$prefixLike."%' "))
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

	/**
	 * Return a filtered list of DB tables.
	 * @param object $mode [optional] all|lan|nolan|nologs
	 * @return array
	 */
	public function tables($mode='all')
	{

		if(!$this->mySQLtableList)
		{
			$this->mySQLtableList = $this->_getTableList();
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
				if(strpos($tab,'lan_') === 0)
				{
					$lan[] = $tab;
				}
				else
				{
					$nolan[] = $tab;
				}
			}

			return ($mode == 'lan') ? $lan : $nolan;
		}

	}

	/**
	 * Dump MySQL Table(s) to a file in the Backup folder.
	 * @param $table string - name without the prefix or '*' for all
	 * @param $file string - optional file name. or leave blank to generate.
	 * @param $options - additional preferences.
	 * @return bool backup file path.
	 */
	function backup($table='*', $file='', $options=null)
	{
		$this->mySQLlastErrText = "PDO is required to use the mysql backup() method";
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

	// Return error text for last operation

	/**
	 * @return string
	 */
	function getLastQuery()
	{
		return $this->mySQLlastQuery;
	}

	/**
	 * @return void
	 */
	private function setSQLMode()
	{

		$this->db_Query("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");

	}



	/**
	 * Check if MySQL version is utf8mb4 compatible and may be used as it accordingly to the user choice
	 *
	 * @TODO Simplify when the conversion script will be available
	 * @deprecated v2.4.0 Use {@see \e107\Database\ConnectionInterface::setCharset()}.
	 *             Avoid in new code and migrate existing call sites when
	 *             refactoring; this method remains supported and tested, with no
	 *             removal planned.
	 * @param string    MySQL charset may be forced in special circumstances
	 *                  UTF-8 encoding and decoding is left to the progammer
	 * @param bool      TRUE enter debug mode. default FALSE
	 * @return string   hardcoded error message
	 */
	function db_Set_Charset($charset = '', $debug = FALSE)
	{
		$this->_notifyDeprecated('db_Set_Charset', 'Use $sql->setCharset() instead.');

		// Get the default user choice
		global $mySQLcharset;
		if (isset($mySQLcharset) && $mySQLcharset != 'utf8mb4')
		{
			// Only utf8mb4 is accepted
			$mySQLcharset = '';
		}
		$charset = ($charset ? $charset : $mySQLcharset);
		// $charset is interpolated into "SET NAMES `$charset`" below (both the
		// mysqli_query and db_Query paths); a backtick would break out of the
		// identifier context, so reject anything that is not a plain charset token.
		if($charset && !preg_match('/^[A-Za-z0-9_]+$/D', $charset))
		{
			return 'Invalid charset';
		}
		$message = (( ! $charset && $debug) ? 'Empty charset!' : '');
		if($charset)
		{
			$this->mySQLaccess->set_charset($charset);
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
	 * Set Database charset to utf8mb4
	 *
	 * @access private
	 */
	public function setCharset($charset = 'utf8mb4')
	{
		$this->_getMySQLaccess();
		$this->mySQLaccess->set_charset($charset);

		$this->mySQLcharset = $charset;
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
				$temp = file_get_contents(e_CACHE_DB.$tableName.'.php');
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
						$result = false;
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

		$outDefs = $dbAdm->make_field_types($fieldDefs);

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
	protected function _getMySQLaccess()
	{
		if (!$this->mySQLaccess) {
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
		}
		if (!$this->mySQLaccess && ($db = e107::getDb()) !== $this) {
			$this->mySQLaccess = $db->get_mySQLaccess();
		}
		if (!$this->mySQLaccess) {
			// lazy self-connect from the config loaded in the constructor, like e_db_pdo::_getMySQLaccess()
			$success = $this->connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword);
			if ($success) $success = $this->database($this->mySQLdefaultdb, $this->mySQLPrefix);
			if (!$success) throw new RuntimeException($this->mySQLlastErrText);
		}
	}

}

/**
 * Backwards compatibility
 */

if(!class_exists('db'))
{
	/**
	 *
	 */
	class db extends e_db_mysql
	{

	}
}
