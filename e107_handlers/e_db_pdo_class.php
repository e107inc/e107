<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * PDO MySQL Handler
 */

// Legacy Fix.
defined('MYSQL_ASSOC') or define('MYSQL_ASSOC', 1);
defined('MYSQL_NUM') or define('MYSQL_NUM', 2);
defined('MYSQL_BOTH') or define('MYSQL_BOTH', 3);

require_once('e_db_interface.php');
require_once('e_db_legacy_trait.php');

/**
 * PDO MySQL class. All legacy mysql_ methods removed.
 * Class e_db_pdo
 */
class e_db_pdo implements e_db
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

	/** @var PDO */
	protected   $mySQLaccess;
	protected   $mySQLrows;

	protected   $mySQLlastQuery = '';

	public      $mySQLinfo;
	public      $mySQLtablelist = array();

	protected	$dbFieldDefs = array();		// Local cache - Field type definitions for _FIELD_DEFS and _NOTNULL arrays
	protected   $mySqlServerInfo = '?';			// Server info - needed for various things

	private     $pdo            = true; // using PDO or not.

	/** @var e107_traffic */
	private     $traffic;

	protected static $querycount = 0;




	/**
	* Constructor - gets language options from the cookie or session
	* @access public
	*/
	public function __construct()
	{

		$this->traffic = e107::getSingleton('e107_traffic');
		$this->traffic->BumpWho('Create db object', 1);
						// Set the default prefix - may be overridden

		$config =  e107::getMySQLConfig();


		$this->mySQLserver      = $config['mySQLserver'] ?? '';
		$this->mySQLuser        = $config['mySQLuser'] ?? '';
		$this->mySQLpassword    = $config['mySQLpassword'] ?? '';
		$this->mySQLdefaultdb   = $config['mySQLdefaultdb'] ?? '';
		$this->mySQLport        = varset($config['port'], 3306);
		$this->mySQLPrefix      = varset($config['mySQLprefix'], 'e107_');

		/*

		if($port = e107::getMySQLConfig('port'))
		{
			$this->mySQLport = intval($port);
		}*/

		// Detect is already done in language handler, use it if not too early
		if(defined('e_LANGUAGE'))
		{
			 $this->mySQLlanguage = e107::getLanguage()->e_language;
		}

		if (defset('E107_DEBUG_LEVEL') > 0)
		{
			$this->debugMode = true;
		}

		$this->dbg = e107::getDebug();

	}

	/**
	 * @return bool
	 */
	function getPDO()
	{
		return true;
	}

	/**
	 * Connect ONLY  - used in v2.x
	 * @param string $mySQLserver IP Or hostname of the MySQL server
	 * @param string $mySQLuser MySQL username
	 * @param string $mySQLpassword MySQL Password
	 * @param bool $newLink force a new link connection if TRUE. Default false
	 * @return boolean true on success, false on error.
	 */
	public function connect($mySQLserver, $mySQLuser, $mySQLpassword, $newLink = false)
	{

		$this->traffic->BumpWho('db Connect', 1);

		$this->mySQLserver 		= $mySQLserver;
		$this->mySQLuser 		= $mySQLuser;
		$this->mySQLpassword 	= $mySQLpassword;
		$this->mySQLerror 		= false;

		if(strpos($mySQLserver,':')!==false && substr_count($mySQLserver, ':')===1)
		{
			list($this->mySQLserver,$this->mySQLport) = explode(':',$mySQLserver,2);
		}

	//	if($this->mySQLserver === 'localhost') // problematic.
		{
	//		$this->mySQLserver = '127.0.0.1'; // faster by almost 1 second.
		}


		try
		{
			$this->mySQLaccess = new PDO("mysql:host={$this->mySQLserver};port={$this->mySQLport}", $this->mySQLuser, $this->mySQLpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		}
		catch(PDOException $ex)
		{
			$this->mySQLlastErrText = $ex->getMessage();
			$this->mySQLlastErrNum = $ex->getCode();
			$this->dbg->log($this->mySQLlastErrText);
			return false;
		}

		$this->setCharset();
		$this->setSQLMode();

		return true;
	}


	/**
	 * Get Server Info
	 * @return mixed
	 */
	public function getServerInfo()
	{

	//	var_dump($this->mySQLaccess);
		$this->_getMySQLaccess();
		$this->mySqlServerInfo =  $this->mySQLaccess->query('select version()')->fetchColumn();
	//	$this->mySqlServerInfo = $this->mySQLaccess->getAttribute(PDO::ATTR_SERVER_VERSION);
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
			$this->mySQLPrefix 		= "`".$database."`.".$prefix;
			return true;
		}


		try
		{
			$this->mySQLaccess->exec("use `".$database."`");
       		// $this->mySQLaccess->select_db($database); $dbh->query("use newdatabase");
	    }
		catch (PDOException $e)
		{
			$this->mySQLlastErrText = $e->getMessage();
			$this->mySQLlastErrNum = $e->getCode();
			return false;
	    }

		return true;

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
			// 'dblog_id'          => 0,
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

		// $this->insert('dblog', "0, {$time_sec}, {$time_usec}, '{$log_type}', 'DBDEBUG', {$uid}, '{$userstring}', '{$ip}', '', '{$log_remark}', '{$qry}'");
	}


	/**
	 * This is the 'core' routine which handles much of the interface between other functions and the DB
	 *
	 * If a SELECT query includes SQL_CALC_FOUND_ROWS, the value of FOUND_ROWS() is retrieved and stored in $this->total_results
	 *
	 * The array ['PREPARE' => ..., 'BIND' => ..., 'EXECUTE' => ...] contract is
	 * internal plumbing; new code should call {@see e_db::execute()} instead.
	 *
	 * @param string|array  $query ['BIND'] eg. array['my_field'] = array('value'=>'whatever', 'type'=>'str');
	 * @param object $rli connection resource.
	 * @param string $qry_from eg. SELECT, INSERT, UPDATE mode.
	 * @param bool   $debug
	 * @param string $log_type
	 * @param string $log_remark
	 * @return boolean|int|PDOStatement - as mysql_query() function.
	 *            false indicates an error
	 *            For SELECT, SHOW, DESCRIBE, EXPLAIN and others returning a result set, returns a resource
	 *            TRUE indicates success in other cases
	 */
	public function db_Query($query, $rli = NULL, $qry_from = '', $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('db_Query', 'Use $sql->execute($query, $params); it accepts the same SQL with a friendlier parameter map.');

		global $db_time, $queryinfo;
		self::$querycount++;

		$this->_getMySQLaccess();
		$this->mySQLlastQuery = $query;

		if ($debug == 'now')
		{
			$this->dbg->log($query);
		}
		if ($debug !== false || strpos($_SERVER['QUERY_STRING'], 'showsql') !== false)
		{
			$debugQry = is_array($query) ? print_a($query,true) : $query;
			$queryinfo[] = "<b>{$qry_from}</b>: ".$debugQry;
		}
		if ($log_type != '')
		{
			$this->log($log_type, $log_remark, $query);
		}

		$b = microtime();


		if(is_array($query) && !empty($query['PREPARE']))
		{
			/** @var PDOStatement $prep */
			$prep = $this->mySQLaccess->prepare($query['PREPARE']);

			if(!empty($query['BIND']))
			{
				foreach($query['BIND'] as $k=>$v)
				{
					$prep->bindValue(':'.$k, $v['value'], $v['type']);
				}
			}

			$execute = !empty($query['EXECUTE']) ? $query['EXECUTE'] : null;

			try
			{
				$prep->execute($execute);
				$sQryRes = ($qry_from == 'db_Select') ? $prep : $prep->rowCount();
			}
			catch(PDOException $ex)
			{
				$sQryRes = false;
				$this->mySQLlastErrText = $ex->getMessage();
				$this->mySQLlastErrNum = $ex->getCode();
			}
		}
		else
		{
			try
			{
				if(preg_match('#^(CREATE TABLE|DROP TABLE|ALTER TABLE|RENAME TABLE|CREATE DATABASE|CREATE INDEX)#',$query, $matches))
				{
					/** @var PDO $rli */
					$sQryRes = is_null($rli) ? $this->mySQLaccess->exec($query) : $rli->exec($query);
					if($sQryRes !==false)
					{
						$sQryRes = true; // match with non-PDO results.
					}
				}
				else
				{
						/** @var PDO $rli */
						$sQryRes = is_null($rli) ? $this->mySQLaccess->query($query) : $rli->query($query);
				}

			}
			catch(PDOException $ex)
			{
				$sQryRes = false;
				$this->mySQLlastErrText = $ex->getMessage();
				$this->mySQLlastErrNum = $ex->getCode();
			}
		}




		$e = microtime();

		$this->traffic->Bump('db_Query', $b, $e);
		$mytime = $this->traffic->TimeDelta($b,$e);
		$db_time += $mytime;
		$this->mySQLresult = $sQryRes;


		if ($this->debugMode !== true)
		{
			$this->total_results = false;
		}
		// Need to get the total record count as well. Return code is a resource identifier
		// Have to do this before any debug action, otherwise this bit gets messed up



		if (!is_array($query) && (strpos($query,'EXPLAIN') !==0) && (strpos($query,'SQL_CALC_FOUND_ROWS') !== false) && (strpos($query,'SELECT') !== false))
		{

			$rc = $this->mySQLaccess->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);
			$this->total_results = intval($rc);
		}

		if ($this->debugMode === true)
		{
			$aTrace = debug_backtrace();
			$pTable = (string) $this->mySQLcurTable;

			if(!strlen($pTable))
			{
				$pTable = '(complex query)';
			}
			else
			{
				$this->mySQLcurTable = ''; // clear before next query
			}

			if(is_object($this->dbg))
			{
				$buglink = is_null($rli) ? $this->mySQLaccess : $rli;

				if(is_array($query))
				{
					$query['BIND'] = isset($query['BIND']) ? $query['BIND'] : null;
					$query = "PREPARE: " . $query['PREPARE'] . "<br />BIND:" . print_a($query['BIND'], true); // ,true);
				}

				if(isset($ex) && is_object($ex))
				{
					$query = $ex->getMessage();
				 // 	 $arr = $ex->getTrace(); // @todo runs out of memory when tested.
				  // $query .= print_a($arr, true);
				}


				if($buglink instanceof PDO)
				{
					$this->dbg->Mark_Query($query, 'PDO', $sQryRes, $aTrace, $mytime, $pTable);
				}

			}


		}

		return $sQryRes;
	}

	/**
	 * Documented at {@see e_db::select()}.
	 *
	 * @return int|false Number of rows or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see e_db::select()}.
	 */
	public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('select', 'Use the query builder: $sql->createQueryBuilder()->select(...)->from(\'table\')->where(...)->fetchAll().');


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


		if (!empty($arg) && ($noWhere === false || $noWhere === 'default'))  // 'default' for BC.
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.$this->mySQLPrefix.$table.' WHERE '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark))
			{
				$this->dbError('dbQuery');
				return $this->rowCount();
			}
			else
			{
				$this->dbError("db_Select (SELECT $fields FROM ".$this->mySQLPrefix."{$table} WHERE {$arg})");
				return false;
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
				return false;
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
				return false;
			}
		}
	}

	/**
	 * @return bool|int
	 */
	public function lastInsertId()
	{
		$tmp = (int) $this->mySQLaccess->lastInsertId();
		return ($tmp) ? $tmp : true; // return true even if table doesn't have auto-increment.
	}

	/**
	 * @param $result
	 * @return int
	 */
	public function rowCount($result=null)
	{

		if(!$this->mySQLresult)
		{
			return -1;
		}


		/** @var PDOStatement $resource */
		$resource = $this->mySQLresult;
		$rows = $this->mySQLrows = $resource->rowCount();
		$this->dbError('db_Rows');
		return $rows;
	}

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
	function fetch($type = null)
	{
		switch ($type)
		{
				case 'both':
				case 3: // MYSQL_BOTH:
					$type = PDO::FETCH_BOTH; // 3
				break;

				case 'num':
				case 2; // MYSQL_NUM: // 2
					$type = PDO::FETCH_NUM;
				break;

				default:
				case 'assoc':
				case 1; // MYSQL_ASSOC // 1
					$type =  PDO::FETCH_ASSOC;
				break;
		}

		$b = microtime();

		if($this->mySQLresult)
		{
			/** @var PDOStatement $resource */
			$resource = $this->mySQLresult;
			$row = $resource->fetch($type);
			$this->traffic->Bump('db_Fetch', $b);

			if ($row)
			{
				$this->dbError('db_Fetch');
				return $row;		// Success - return data
			}
		}

		$this->dbError('db_Fetch');

		return false;
	}



	/**
	 * Documented at {@see e_db::count()}.
	 *
	 * @return int|false number of affected rows or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see e_db::count()}.
	 */
	function count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('count', 'Use the query builder: $sql->createQueryBuilder()->selectCount()->from(\'table\')->where(...)->fetchOne().');

		$table = $this->hasLanguage($table);

		if ($fields == 'generic')
		{
			$query=$table;
			if ($this->mySQLresult = $this->db_Query($query, NULL, 'db_Count', $debug, $log_type, $log_remark))
			{
				$rows = $this->mySQLrows = $this->mySQLresult->fetch(PDO::FETCH_ASSOC);
				$this->dbError('db_Count');
				return (int) $rows['COUNT(*)'];
			}
			else
			{
				$this->dbError("db_Count ({$query})");
				return false;
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
			$rows = $this->mySQLrows = $this->mySQLresult->fetch(PDO::FETCH_NUM);
			$this->dbError('db_Count');
			return (int) $rows[0];
		}
		else
		{
			$this->dbError("db_Count({$query})");
			return false;
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
		$this->traffic->BumpWho('db Close', 1);
		$this->mySQLaccess = null; // correct way to do it when using shared links.
		$this->dbError('dbClose');
	}





	/**
	 * Documented at {@see e_db::delete()}.
	 *
	 * @return int|false number of affected rows, or false on error
	 * @deprecated v2.4.0 Prefer the query builder; see {@see e_db::delete()}.
	 */
	function delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('delete', 'Use the query builder: $sql->createQueryBuilder()->delete(\'table\')->where(...)->execute().');

		$table = $this->hasLanguage($table);
		$this->mySQLcurTable = $table;

		$this->_getMySQLaccess();


		if (!$arg)
		{
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.$this->mySQLPrefix.$table, NULL, 'db_Delete', $debug, $log_type, $log_remark))
			{
				// return the number of records deleted instead of an object
				$tmp = $this->mySQLresult->rowCount();
				$this->dbError('db_Delete');
				return $tmp;
			}
			else
			{
				$this->dbError("db_Delete({$arg})");
				return false;
			}
		}
		else
		{
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.$this->mySQLPrefix.$table.' WHERE '.$arg, NULL, 'db_Delete', $debug, $log_type, $log_remark))
			{
				$tmp = $this->mySQLresult->rowCount();
				$this->dbError('db_Delete');
				return $tmp;
			}
			else
			{
				$this->dbError('db_Delete ('.$arg.')');
				return false;
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

		if($result instanceof PDOStatement)
		{
			if($result->columnCount() > 0) // result set; rows readable via fetch()
			{
				return $this->rowCount();
			}

			$affected = $result->rowCount();
			return preg_match('#^\s*(DELETE|INSERT|REPLACE|UPDATE)#i', $sql) ? $affected : true;
		}

		return true; // PDO::exec() path (DDL); db_Query() already normalized to boolean
	}

	/**
	 * Documented at {@see e_db::gen()}.
	 *
	 * @return boolean | int
	 * @deprecated v2.4.0 Use {@see e_db::execute()} instead; see {@see e_db::gen()}.
	 */
	public function gen($query, $debug = false, $log_type = '', $log_remark = '')
	{
		$this->_notifyDeprecated('gen', 'Use $sql->execute($query, $params) with :named parameters; for ordinary CRUD prefer the query builder ($sql->createQueryBuilder()).');

		$this->tabset = false;

		$query .= " "; // temp fix for failing regex below, when there is no space after the table name;

		if(strpos($query,'`#') !== false)
		{
			//$query = str_replace('`#','`'.$this->mySQLPrefix,$query);	// This simple substitution should be OK when backticks used
			// SecretR - reverted back - breaks multi-language
			$query = preg_replace_callback("/\s`#([\w]*?)`\W/", array($this, 'ml_check'), $query);
		}
		elseif(strpos($query,'#') !== false)
		{	// Deprecated scenario - caused problems when '#' appeared in data - hence use of backticks
			$query = preg_replace_callback("/\s#([\w]*?)\W/", array($this, 'ml_check'), $query);
		}

		//$query = str_replace("#",$this->mySQLPrefix,$query); //FIXME - quick fix for those that slip-thru - but destroys
																// the point of requiring backticks round table names - wrecks &#039;, for example
		if (($this->mySQLresult = $this->db_Query($query, NULL, 'db_Select_gen', $debug, $log_type, $log_remark)) === false)
		{	// Failed query
			$this->dbError('db_Select_gen('.$query.')');
			return false;
		}
		elseif ($this->mySQLresult === TRUE)
		{	// Successful query which may return a row count (because it operated on a number of rows without returning a result set)
			if(preg_match('#^(DELETE|INSERT|REPLACE|UPDATE)#',$query, $matches))
			{
				/** @var PDOStatement $resource */
				$resource = $this->mySQLresult;
				$tmp = $resource->rowCount();
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
		return self::$querycount;
	}


	/**
	 * Multi-language Query Function. Run a query on the same table across all languages.
	 * @param $query
	 * @param bool $debug
	 * @return bool
	 */
	public function db_Query_all($query, $debug=false)
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
	 *	@param boolean $retinfo = false - just returns array of field names. TRUE - returns all field info
	 *	@return array|boolean - false on error, field list array on success
	 */
	public function fields($table, $prefix = '', $retinfo = false)
	{


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
		/** @var PDOStatement $resource */
		$resource = $this->mySQLresult;
		return $resource->columnCount();

	}

	/**
	 * Documented at {@see \e107\Database\ConnectionInterface::quoteStringLiteral()}.
	 *
	 * @param string $value
	 * @return string quoted literal, including the surrounding quotes
	 * @throws PDOException if the PDO driver does not support quoting
	 */
	public function quoteStringLiteral($value)
	{
		$this->_getMySQLaccess();

		$quoted = $this->mySQLaccess->quote((string) $value);

		if($quoted === false) // pdo_mysql always supports quoting
		{
			throw new PDOException('quoteStringLiteral() requires a PDO driver that supports quoting');
		}

		return $quoted;
	}

	/**
	 * escape() without the deprecation notice, for internal legacy paths:
	 * {@see e_db_pdo::quoteStringLiteral()} with the surrounding quotes stripped.
	 *
	 * @param string $data
	 * @return string
	 * @throws PDOException if the PDO driver does not support quoting
	 */
	protected function _escape($data)
	{
		return substr($this->quoteStringLiteral($data), 1, -1);
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

		return array();
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
/*
		if($this->pdo === false)
		{
			$this->mySQLlastErrText = "PDO is required to use the mysql backup() method";
			return false;
		}*/

	//	$dbtable 		= $this->mySQLdefaultdb;
		$fileName		= ($table =='*') ? str_replace(" ","_",SITENAME) : $table;
		$fileName	 	= preg_replace('/[\W]/',"",$fileName);

		$backupFile 	= ($file) ? e_BACKUP.$file  :  e_BACKUP.strtolower($fileName)."_".$this->mySQLPrefix.date("Y-m-d-H-i-s").".sql";

		if($table === '*')
		{
			$nolog 		= vartrue($options['nologs']) ? 'nologs' : 'all';
			$tableList 	= $this->tables($nolog);
		}
		else
		{
			$tableList 	= explode(",",$table);
		}

		if(!empty($options['gzip']))
		{
			$backupFile .= '.gz';
		}


   //     include_once(dirname(__FILE__) . '/Ifsnop/Mysqldump/Mysqldump.php');

		$dumpSettings = array(
	        'compress'                      => !empty($options['gzip']) ? Ifsnop\Mysqldump\Mysqldump::GZIP : Ifsnop\Mysqldump\Mysqldump::NONE,
	        'include-tables'                => array(),
		    'no-data'                       => false,
		    'add-drop-table'                => !empty($options['droptable']) ? true : false,
		    'single-transaction'            => true,
		    'lock-tables'                   => true,
		    'add-locks'                     => true,
		    'extended-insert'               => true,
		    'disable-foreign-keys-check'    => true,
		    'skip-triggers'                 => false,
		    'add-drop-trigger'              => true,
		    'databases'                     => false,
		    'add-drop-database'             => false,
		    'hex-blob'                      => true,
		    'reset-auto-increment'          => false,
	    );

        foreach($tableList as $tab)
        {
            $dumpSettings['include-tables'][] = $this->mySQLPrefix.trim($tab);
        }


        try
        {
            $dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host={$this->mySQLserver};port={$this->mySQLport};dbname={$this->mySQLdefaultdb}", $this->mySQLuser, $this->mySQLpassword, $dumpSettings);
		    $dump->start($backupFile);
		    return $backupFile;
		}
		catch (\Exception $e)
		{
			$this->mySQLlastErrText = 'mysqldump-php error: ' .$e->getMessage();
		    return false;
		}


	}











	/**
	* @return string|null relating to error (empty string if no error)
	* @param string $from
	* @desc Calling method from within this class
	* @access private
	*/
	function dbError($from)
	{

		$this->mySQLerror = true;

		if($this->mySQLlastErrNum === 0)
		{
			return null;
		}

		return $from." :: ".$this->mySQLlastErrText;


	}


	// Return error number for last operation

	// Return error text for last operation

	/**
	 * Returns the last database query used.
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->mySQLlastQuery;
	}


	/**
	 * @return void
	 */
	private function setSQLMode()
	{
		$this->db_Query("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");
		/**
		 * Disable PHP 8.1 PDO result set typing casting for consistency with PHP 5.6 through 8.0
		 * @link https://github.com/php/php-src/blob/4025cf2875f895e9f7193cebb1c8efa4290d052e/UPGRADING#L130-L134
		 */
		$this->mySQLaccess->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
	}



	/**
	 * Set Database charset to utf8mb4
	 *
	 * @access private
	 */
	public function setCharset($charset = 'utf8mb4')
	{
		$this->db_Query("SET NAMES `$charset`");

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
	 *	@return boolean|array - false if not found/not to be used. Array of field names and processing types and null overrides if found
	 */
	public function getFieldDefs($tableName)
	{
		if (!isset($this->dbFieldDefs[$tableName]))
		{
			if (is_readable(e_CACHE_DB.$tableName.'.php'))
			{
				$temp = file_get_contents(e_CACHE_DB.$tableName.'.php');
				if ($temp !== false)
				{
					$typeDefs = e107::unserialize($temp);
					unset($temp);
					$this->dbFieldDefs[$tableName] = $typeDefs;
				}
			}
			else
			{		// Need to try and find a table definition
				$searchArray = array(e_CORE.'sql/db_field_defs.php');
				// e107::getPref() shouldn't be used inside db handler! See hasLanguage() comments
				$sqlFiles = (array) $this->getConfig()->get('e_sql_list', array()); // kill any PHP notices
				foreach ($sqlFiles as $p => $f)
				{
					$searchArray[] = e_PLUGIN.$p.'/db_field_defs.php';
				}
				unset($sqlFiles);
				$found = false;
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
	 *	Always also update $this->dbFieldDefs[$tableName] - false if not found, data if found
	 *	@param	string $defFile - file name, including path
	 *	@param	string $tableName - name of table sought
	 *	@return boolean TRUE on success, false on not found (some errors intentionally ignored)
	 */
	protected function loadTableDef($defFile, $tableName)
	{
		$result =false;

		if (is_readable($defFile))
		{
			// Read the file using the array handler routines
			// File structure is a nested array - first level is table name, second level is either false (for do nothing) or array(_FIELD_DEFS => array(), _NOTNULL => array())
			$temp = file_get_contents($defFile);
			// Strip any comments  (only /*...*/ supported)
			$temp = preg_replace("#\/\*.*?\*\/#ms", '', $temp);
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
					{	// Could do something with error - but mustn't return false - would trigger auto-generated structure

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
	 *	Also update $this->dbFieldDefs[$tableName] - false if error, data if found
	 *	@param	string $tableName - name of table sought
	 *	@return array|boolean array on success, false on not found (some errors intentionally ignored)
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

		if (false === file_put_contents(e_CACHE_DB.$tableName.'.php', $toSave))
		{	// Could do something with error - but mustn't return false - would trigger auto-generated structure
			$mes = e107::getMessage();
			$mes->addDebug("Error writing file: ".e_CACHE_DB.$tableName.'.php'); //Fix for during v1.x -> 2.x upgrade.
			// echo "Error writing file: ".e_CACHE_DB.$tableName.'.php'.'<br />';
		}

		return empty($outDefs) ? false : $outDefs;

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
		if (!$this->mySQLaccess)
		{
			$success = $this->connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword);
			if ($success) $success = $this->database($this->mySQLdefaultdb, $this->mySQLPrefix);
			if (!$success) throw new PDOException($this->mySQLlastErrText);
		}
	}



}

/**
 * Backwards compatibility
 */
class db extends e_db_pdo
{

}
