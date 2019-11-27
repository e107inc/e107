<?php
/**
 * Created by PhpStorm.
 * Date: 2/8/2019
 * Time: 11:46 AM
 */

// Legacy Fix.
define('MYSQL_ASSOC', 1);
define('MYSQL_NUM', 2);
define('MYSQL_BOTH', 3);
define('ALLOW_AUTO_FIELD_DEFS', true);

require_once('e_db_interface.php');
require_once('e_db_legacy_trait.php');



/**
 * PDO MySQL class. All legacy mysql_ methods removed.
 * Class e_db_pdo
 */
class e_db_pdo implements e_db
{

	use e_db_legacy;

	// TODO switch to protected vars where needed
	public      $mySQLserver;
	public      $mySQLuser;
	protected   $mySQLpassword;
	protected   $mySQLdefaultdb;
	protected   $mySQLport = 3306;
	public      $mySQLPrefix;

	/** @var PDO */
	protected   $mySQLaccess;
	public      $mySQLresult;
	protected   $mySQLrows;
	protected   $mySQLerror = false;			// Error reporting mode - TRUE shows messages

	protected   $mySQLlastErrNum = 0;		// Number of last error - now protected, use getLastErrorNumber()
	protected   $mySQLlastErrText = '';		// Text of last error - now protected, use getLastErrorText()
	protected   $mySQLlastQuery = '';

	protected   $mySQLcurTable;
	public      $mySQLlanguage;
	public      $mySQLinfo;
	public      $tabset;
	public      $mySQLtableList = array(); // list of all Db tables.

	public      $mySQLtableListLanguage = array(); // Db table list for the currently selected language
	public      $mySQLtablelist = array();

	protected	$dbFieldDefs = array();		// Local cache - Field type definitions for _FIELD_DEFS and _NOTNULL arrays
	public      $mySQLcharset;
	protected   $mySqlServerInfo = '?';			// Server info - needed for various things

	public      $total_results = false;			// Total number of results

	private     $pdo            = true; // using PDO or not.
	private     $pdoBind        = false;

	/** @var e107_traffic */
	private     $traffic;

	/** @var e107_db_debug */
	private     $dbg;

	private     $debugMode      = false;

	private     $queryCount     = 0;




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


		$this->mySQLserver      = $config['mySQLserver'];
		$this->mySQLuser        = $config['mySQLuser'];
		$this->mySQLpassword    = $config['mySQLpassword'];
		$this->mySQLdefaultdb   = $config['mySQLdefaultdb'];
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

		if (E107_DEBUG_LEVEL > 0)
		{
			$this->debugMode = true;
		}

		$this->dbg = e107::getDebug();

	}

	function getPDO()
	{
		return true;
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
	 * Connect ONLY  - used in v2.x
	 * @param string $mySQLserver IP Or hostname of the MySQL server
	 * @param string $mySQLuser MySQL username
	 * @param string $mySQLpassword MySQL Password
	 * @param string $newLink force a new link connection if TRUE. Default false
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
			$this->mySQLaccess = new PDO("mysql:host=".$this->mySQLserver."; port=".$this->mySQLport, $this->mySQLuser, $this->mySQLpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		}
		catch(PDOException $ex)
		{
			$this->mySQLlastErrText = $ex->getMessage();
			$this->mySQLLastErrNum = $ex->getCode();
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


		try
		{
			$this->mySQLaccess->query("use `".$database."`");
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
	 * Get system config
	 * @return e_core_pref
	 */
	public function getConfig()
	{
		return e107::getConfig('core', false);
	}

	/**
	*
	* @param string $sMarker
	* @desc Enter description here...
	 * @return null|true
	*/
	public function markTime($sMarker)
	{
		if($this->debugMode !== true)
		{
			return null;
		}

		$this->dbg->Mark_Time($sMarker);

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
			'dblog_remarks'     => $qry

		);

		$this->insert('dblog', $insert);

		// $this->insert('dblog', "0, {$time_sec}, {$time_usec}, '{$log_type}', 'DBDEBUG', {$uid}, '{$userstring}', '{$ip}', '', '{$log_remark}', '{$qry}'");
	}


	/**
	 * This is the 'core' routine which handles much of the interface between other functions and the DB
	 *
	 * If a SELECT query includes SQL_CALC_FOUND_ROWS, the value of FOUND_ROWS() is retrieved and stored in $this->total_results
	 *
	 * @param string|array  $query ['BIND'] eg. array['my_field'] = array('value'=>'whatever', 'type'=>'str');
	 * @param object $rli connection resource.
	 * @param string $qry_from eg. SELECT, INSERT, UPDATE mode.
	 * @param bool   $debug
	 * @param string $log_type
	 * @param string $log_remark
	 * @return boolean|PDOStatement | resource - as mysql_query() function.
	 *            false indicates an error
	 *            For SELECT, SHOW, DESCRIBE, EXPLAIN and others returning a result set, returns a resource
	 *            TRUE indicates success in other cases
	 */
	public function db_Query($query, $rli = NULL, $qry_from = '', $debug = false, $log_type = '', $log_remark = '')
	{
		global $db_time, $queryinfo;
		$this->queryCount++;

		$this->_getMySQLaccess();
		$this->mySQLlastQuery = $query;

		if ($debug == 'now')
		{
			$this->dbg->log($query);
		}
		if ($debug !== false || strstr($_SERVER['QUERY_STRING'], 'showsql'))
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
					$prep->bindValue(':'.$k, $v['value'],$v['type']);
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
			$pTable = $this->mySQLcurTable;

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
	 * @return mixed
	 */
	public function retrieve($table=null, $fields = null, $where=null, $multi = false, $indexField = null, $debug = false)
	{
		// fetch mode
		if(empty($table))
		{

			if(!$multi)
			{
				 return $this->fetch();
			}

			$ret = array();

			while($row = $this->fetch())
			{
				if(null !== $indexField)
				{
					 $ret[$row[$indexField]] = $row;
				}
				else
				{
					 $ret[] = $row;
				}
			}
			return $ret;
		}

		// detect mode
		$mode = 'one';
		if($table && !$where && is_bool($fields))
		{
			// table is the query, fields used for multi
			if($fields)
			{
				 $mode = 'multi';
			}
			else
			{
				 $mode = 'single';
			}

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
			if($mode == 'one') $mode = 'single';
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

		return null;
	}

	/**
	* Perform a SELECT  using the arguments suplpied by calling db::db_Query()
	*
	* @param string $table
	* @param string $fields
	* @param string|array $arg;
	*
	* @example e107::getDb()->select("comments", "*", "comment_item_id = '$id' AND comment_type = '1' ORDER BY comment_datestamp");
	* @example e107::getDb('sql2')->select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT $from, ".$view, true);</code>
	* @example select('user', 'user_id, user_name', 'user_id=:id OR user_name=:name ORDER BY user_name', array('id' => 999, 'name'=>'e107')); // bind support.
	* @return integer Number of rows or false on error
	*/
	public function select($table, $fields = '*', $arg = '', $noWhere = false, $debug = false, $log_type = '', $log_remark = '')
	{

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
	function insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
	{
		$table = $this->hasLanguage($tableName);
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
			if(!isset($arg['_FIELD_TYPES']) && defined('ALLOW_AUTO_FIELD_DEFS') && ALLOW_AUTO_FIELD_DEFS === true)
			{
				$arg = array_merge($arg, $this->getFieldDefs($tableName));
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
			$bind = array();

			foreach($arg['data'] as $fk => $fv)
			{
				$tmp[] = ':'.$fk;
				$bind[$fk] = array('value'=>$this->_getPDOValue($fieldTypes[$fk],$fv), 'type'=> $this->_getPDOType($fieldTypes[$fk],$this->_getPDOValue($fieldTypes[$fk],$fv)));
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


			$query = array(
				'PREPARE' => $query,
				'BIND'  => $bind,
			);



		}
		else
		{
			$query = 'INSERT INTO '.$this->mySQLPrefix."{$table} VALUES ({$arg})";
		}

		$this->_getMySQLaccess();

		$this->mySQLresult = $this->db_Query($query, NULL, 'db_Insert', $debug, $log_type, $log_remark);

		if($DUPEKEY_UPDATE === true)
		{
			$result = false; // ie. there was an error.

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
				$tmp = $this->mySQLresult ;
				$this->dbError('db_Replace');
				// $tmp == -1 (error), $tmp == 0 (not modified), $tmp == 1 (added), greater (replaced)
				if ($tmp == -1) { return false; } // mysql_affected_rows error
				return $tmp;
			}

		//	$tmp = ($this->pdo) ? $this->mySQLaccess->lastInsertId() : mysql_insert_id($this->mySQLaccess);

			$tmp = $this->lastInsertId();

			$this->dbError('db_Insert');
			return ($tmp) ? $tmp : TRUE; // return true even if table doesn't have auto-increment.
		}
		else
		{
		//	$this->dbError("db_Insert ({$query})");
			return false;
		}
	}


	public function lastInsertId()
	{
		$tmp = (int) $this->mySQLaccess->lastInsertId();
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
	function replace($table, $arg, $debug = false, $log_type = '', $log_remark = '')
	{
		$arg['_REPLACE'] = TRUE;
		return $this->insert($table, $arg, $debug, $log_type, $log_remark);
	}




	private function _prepareUpdateArg($tableName, $arg)
	{
		$this->pdoBind = array();
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
			if(!isset($arg['_FIELD_TYPES']) && ALLOW_AUTO_FIELD_DEFS)
			{
				$arg = array_merge($arg, $this->getFieldDefs($tableName));
			}

			$fieldTypes = $this->_getTypes($arg);


			$new_data = '';
			//$this->pdoBind = array(); // moved up to the beginning of the method to make sure it is initialized properly
			foreach ($arg['data'] as $fn => $fv)
			{
				$new_data .= ($new_data ? ', ' : '');
				$ftype =  isset($fieldTypes[$fn]) ? $fieldTypes[$fn] : 'str';

				$new_data .= ($ftype !='cmd') ? "`{$fn}`= :". $fn : "`{$fn}`=".$this->_getFieldValue($fn, $fv, $fieldTypes);

				if($fv === '_NULL_')
				{
					$ftype = 'null';
				}

				if($ftype != 'cmd')
				{
					$this->pdoBind[$fn] = array('value'=>$this->_getPDOValue($ftype,$fv), 'type'=> $this->_getPDOType($ftype,$this->_getPDOValue($ftype,$fv)));
				}
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
	function update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
	{
		$table = $this->hasLanguage($tableName);
		$this->mySQLcurTable = $table;

		$this->_getMySQLaccess();

		$arg = $this->_prepareUpdateArg($tableName, $arg);

		$query = 'UPDATE '.$this->mySQLPrefix.$table.' SET '.$arg;

		if(!empty($this->pdoBind))
		{
			$query = array(
					'PREPARE' => $query,
					'BIND'  => $this->pdoBind,
			);
		}

		$result = $this->mySQLresult = $this->db_Query($query, NULL, 'db_Update', $debug, $log_type, $log_remark);

		if ($result !==false)
		{

			if(is_object($result))
			{
					// make sure to return the number of records affected, instead of an object
				$result = $this->rowCount();
			}


			$this->dbError('db_Update');
			if ($result === -1) { return false; }	// Error return from mysql_affected_rows
			return $result;
		}
		else
		{
			$this->dbError("db_Update ({$query})");
			return false;
		}
	}



	private function _getTypes(&$arg)
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
	private function _getFieldValue($fieldKey, $fieldValue, &$fieldTypes)
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
					return "'".e107::serialize($fieldValue, true)."'";
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
	 * Return a value for use in PDO bindValue() - based on field-type.
	 * @param $type
	 * @param $fieldValue
	 * @return int|string
	 */
	private function _getPDOValue($type, $fieldValue)
	{


		if(is_string($fieldValue) && ($fieldValue === '_NULL_'))
		{
			$type = 'null';
		}

		switch($type)
		{
			case "int":
			case "integer":
				return (int) $fieldValue;
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
			    return (
                    is_string($fieldValue) && (
                        ($fieldValue !== '_NULL_') && ($fieldValue !== '')
                    )
                ) ? $fieldValue : null;
				break;

			case 'array':
				if(is_array($fieldValue))
				{
					return e107::serialize($fieldValue);
				}
				return $fieldValue;
			break;

			case 'todb': // using as default causes serious BC issues.
				if($fieldValue == '') { return ''; }
				return e107::getParser()->toDB($fieldValue);
			break;

				case 'cmd':
			case 'safestr':
			case 'str':
			case 'string':
			case 'escape':
			default:

				return $fieldValue;
				break;

		}


	}


	/**
	 * Convert FIELD_TYPE to PDO compatible Field-Type
	 * @param $type
	 * @return int
	 */
	private function _getPDOType($type, $value = null)
	{
		switch($type)
		{
			case "int":
			case "integer":
				return PDO::PARAM_INT;
				break;

			case 'null':
				return ($value === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;
				break;

			case 'cmd':
			case 'safestr':
			case 'str':
			case 'string':
			case 'escape':
			case 'array':
			case 'todb':
			case 'float':
				return PDO::PARAM_STR;
				break;

		}

		// e107::getMessage()->addDebug("MySQL Missing Field-Type: ".$type);
		return PDO::PARAM_STR;
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
	function count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '')
	{
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
	function delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '')
	{
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
	* @return void
	* @param bool $mode
	* @desc Enter description here...
	* @access private
	*/
	function setErrorReporting($mode)
	{
		$this->mySQLerror = $mode;
	}


	/**
	* Function to handle any MySQL query
	* @param string $query - the MySQL query string, where '#' represents the database prefix in front of table names.
	*		Strongly recommended to enclose all table names in backticks, to minimise the possibility of erroneous substitutions - its
	*			likely that this will become mandatory at some point
	* @return boolean | integer
	*		Returns false if there is an error in the query
	*		Returns TRUE if the query is successful, and it does not return a row count
	*		Returns the number of rows added/updated/deleted for DELETE, INSERT, REPLACE, or UPDATE
	*/
	public function gen($query, $debug = false, $log_type = '', $log_remark = '')
	{
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



	function ml_check($matches)
	{
		$table = $this->hasLanguage($matches[1]);
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
	public function hasLanguage($table, $multiple=false)
	{
		//When running a multi-language site with english included. English must be the main site language.
		// WARNING!!! false is critical important - if missed, expect dead loop (prefs are calling db handler as well when loading)
		// Temporary solution, better one is needed
		$core_pref = $this->getConfig();
		//if ((!$this->mySQLlanguage || !$pref['multilanguage'] || $this->mySQLlanguage=='English') && $multiple==false)
		if ((!$this->mySQLlanguage || !$core_pref->get('multilanguage') || !$core_pref->get('sitelanguage') /*|| $this->mySQLlanguage==$core_pref->get('sitelanguage')*/) && $multiple==false)
		{
		  	return $table;
		}

		$this->_getMySQLaccess();

		if($multiple == false)
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
				$this->mySQLtableList = $this->_getTableList();
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
	* @return array
	* @param string fields to retrieve
	* @desc returns fields as structured array
	* @access public
	* @return array rows of the database as an array.
	*/
	function rows($fields = 'ALL', $amount = false, $maximum = false, $ordermode=false)
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
	 * @return mixed
	 */
	public function max($table, $field, $where='')
	{
		$qry = "SELECT MAX(".$field.") FROM ".$this->mySQLPrefix.$table;

		if(!empty($where))
		{
			$qry .= " WHERE ".$where;
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
	public function queryCount()
	{
		return $this->queryCount;
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



	function columnCount()
	{
		/** @var PDOStatement $resource */
		$resource = $this->mySQLresult;
		return $resource->columnCount();

	}



	/**
	 *	Determines if a plugin field (and key) exist. OR if fieldid is numeric - return the field name in that position.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $fieldid - Numeric offset or field/key name
	 *	@param string $key - PRIMARY|INDEX|UNIQUE - type of key when searching for key name
	 *	@param boolean $retinfo = false - just returns true|false. TRUE - returns all field info
	 *	@return array|boolean - false on error, field information on success
	 */
    function field($table,$fieldid="",$key="", $retinfo = false)
	{

		$convert = array("PRIMARY"=>"PRI","INDEX"=>"MUL","UNIQUE"=>"UNI");
		$key = (isset($convert[$key])) ? $convert[$key] : "OFF";

		$this->_getMySQLaccess();

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
		return false;
	}


	/**
	 *	Determines if a table index (key) exist.
	 *
	 *	@param string $table - table name (no prefix)
	 *	@param string $keyname - Name of the key to
	 *  @param array $fields - OPTIONAL list of fieldnames, the index (key) must contain
	 *	@param boolean $retinfo = false - just returns true|false. TRUE - returns all key info
	 *	@return array|boolean - false on error, key information on success
	 */
	function index($table, $keyname, $fields=null, $retinfo = false)
	{


		$this->_getMySQLaccess();

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
		return false;
	}


	/**
	 * @param $table
	 * @return array  field name => key name
	 */
	private function _getUnique($table)
	{

		$unique = array();

		$result = $this->retrieve("SHOW INDEXES FROM #".$table, true);
		foreach($result as $row)
		{
			$notUnique = (int) $row['Non_unique'];

			if(!$notUnique)
			{
				$field = $row['Column_name'];
				$unique[$field] = $row['Key_name'];
			}

		}

		return $unique;
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

		$this->_getMySQLaccess();

		return $data;

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
	private function _getTableList($language='')
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


	/**
	 *
	 */
	public function resetTableList()
	{
		$this->mySQLtableList = array();
		$this->mySQLtableListLanguage = array();
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

		return array();
	}


	/**
	 * Duplicate a Table Row in a table.
	 */
	function copyRow($table, $fields = '*', $args='')
	{
		if(!$table || !$args )
		{
			return false;
		}

		if($fields === '*')
		{
			$fields = $this->db_FieldList($table);
			$unique = $this->_getUnique($table);

			$flds = array();
			// randomize fields that must be unique.
			foreach($fields as $fld)
			{
				if(isset($unique[$fld]))
				{
					$flds[] = $unique[$fld] === 'PRIMARY' ? 0 : "'rand-".rand(0,999)."'"; // keep it short.
					continue;
				}

				$flds[] = $fld;
			}

			$fieldList = implode(",", $fields);
			$fieldList2 = implode(",", $flds);
		}
		else
		{
			$fieldList = $fields;
			$fieldList2 = $fieldList;
		}

		if(empty($fieldList))
		{
			$this->mysqlLastErrText = "copyRow \$fields list was empty";
			return false;
		}

		$id = $this->gen("INSERT INTO ".$this->mySQLPrefix.$table."(".$fieldList.") SELECT ".$fieldList2." FROM ".$this->mySQLPrefix.$table." WHERE ".$args);
		$lastInsertId = $this->lastInsertId();

		return ($id && $lastInsertId) ? $lastInsertId : false;

	}


	/**
	 * @param string $oldtable
	 * @param string $newtable
	 * @param bool $drop
	 * @param bool $data
	 * @return bool|int|PDOStatement|resource
	 */
	public function copyTable($oldtable, $newtable, $drop = false, $data = false)
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
			return false;
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
	 * Drop/delete table and all it's data
	 * @param string $table name without the prefix
	 * @return bool|int
	 */
	public function dropTable($table)
	{
		$name = $this->mySQLPrefix.strtolower($table);
		return $this->gen("DROP TABLE IF EXISTS ".$name);
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
			$this->mysqlLastErrText = "PDO is required to use the mysql backup() method";
			return false;
		}*/

	//	$dbtable 		= $this->mySQLdefaultdb;
		$fileName		= ($table =='*') ? str_replace(" ","_",SITENAME) : $table;
		$fileName	 	= preg_replace('/[^\w]/i',"",$fileName);

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


        include_once(dirname(__FILE__) . '/Ifsnop/Mysqldump/Mysqldump.php');

        $config = e107::getMySQLConfig();

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
            $dumpSettings['include-tables'][] = $config['mySQLprefix'].trim($tab);
        }


        try
        {
            $dump = new Ifsnop\Mysqldump\Mysqldump('mysql:host='.$config['mySQLserver'].';dbname='.$config['mySQLdefaultdb'], $config['mySQLuser'], $config['mySQLpassword'], $dumpSettings);
		    $dump->start($backupFile);
		    return $backupFile;
		}
		catch (\Exception $e)
		{
			$this->mysqlLastErrText = 'mysqldump-php error: ' .$e->getMessage();
		    return false;
		}


	}











	/**
	* @return string relating to error (empty string if no error)
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

	/**
	 * Returns the last database query used.
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->mySQLlastQuery;
	}



	private function setSQLMode()
	{
		$this->db_Query("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");
	}



	/**
	 * Set Database charset to utf8
	 *
	 * @access private
	 */
	public function setCharset($charset = 'utf8')
	{
		$this->db_Query("SET NAMES `$charset`");

		$this->mySQLcharset = $charset;
	}


	public function getCharset()
	{
		return $this->mySQLcharset;
	}


	/**
	 * Set the database language
	 * @param string $lang French, German etc.
	 */
	public function setLanguage($lang)
	{
		$this->mySQLlanguage = $lang;
	}

	/**
	 * Get the current database language. eg. English, French etc.
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->mySQLlanguage;
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
				$temp = file_get_contents(e_CACHE_DB.$tableName.'.php', FILE_TEXT);
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
						case 'integer':
						case 'smallint':
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
	private function _getMySQLaccess()
	{
		/*if (!$this->mySQLaccess) {
			global $db_ConnectionID;
			$this->mySQLaccess = $db_ConnectionID;
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);

		}*/
		
		if (!$this->mySQLaccess)
		{
		//	debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
			$this->connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword);
			$this->database($this->mySQLdefaultdb);
			//$this->mySQLaccess = e107::getDb()->get_mySQLaccess();
		}
	}



}

/**
 * Backwards compatibility
 */
class db extends e_db_pdo
{

}
