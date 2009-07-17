<?php

/*
+---------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/mysql_class.php,v $
|     $Revision: 1.84 $
|     $Date: 2009-07-17 14:20:00 $
|     $Author: marj_nl_fr $
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$db_time = 0.0;				// Global total time spent in all db object queries
$db_mySQLQueryCount = 0;	// Global total number of db object queries (all db's)

$db_ConnectionID = NULL;

/**
* MySQL Abstraction class
*
* @package e107
* @version $Revision: 1.84 $
* @author $Author: marj_nl_fr $
*/
class db {

	var $mySQLserver;
	var $mySQLuser;
	var $mySQLpassword;
	var $mySQLdefaultdb;
	var $mySQLaccess;
	var $mySQLresult;
	var $mySQLrows;
	var $mySQLerror;
	var $mySQLcurTable;
	var $mySQLlanguage;
	var $mySQLinfo;
	var $tabset;
	
	/**
	 * @public  MySQL Charset
	 **/
	var $mySQLcharset;

	/**
	 * db constructor gets language options from the cookie or session
	 * @access public
	 * @return void
	 */
	function db()
	{
		global $pref, $eTraffic;
		$eTraffic->BumpWho('Create db object', 1);
		$langid = 'e107language_'.$pref['cookie_name'];
		if ($pref['user_tracking'] == 'session')
		{
			if (!isset($_SESSION[$langid]))
			{
				return;
			}
			$this->mySQLlanguage = $_SESSION[$langid];
		}
		else
		{
			if (!isset($_COOKIE[$langid]))
			{
				return;
			}
			$this->mySQLlanguage = $_COOKIE[$langid];
		}
	}

	/**
	 * @access public
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
	 * @return null|string error code
	 */
	function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb)
	{
		global $eTraffic, $db_ConnectionID;
		$eTraffic->BumpWho('db Connect', 1);

		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;

		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		if(defined("USE_PERSISTANT_DB") && USE_PERSISTANT_DB == TRUE)
		{
			if ( ! $this->mySQLaccess = @mysql_pconnect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword))
			{
				return 'e1';
			}
			else
			{
				if ( ! @mysql_select_db($this->mySQLdefaultdb, $this->mySQLaccess))
				{
					return 'e2';
				}
				else
				{
					$this->dbError('dbConnect/SelectDB');
				}
			}
		}
		else
		{

			if ( ! $this->mySQLaccess = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword))
			{
				return 'e1';
			}
			else
			{
				if ( ! @mysql_select_db($this->mySQLdefaultdb, $this->mySQLaccess))
				{
					return 'e2';
				}
				else
				{
					$this->dbError('dbConnect/SelectDB');
				}
			}
		}
		// Set utf8 connection?
		//@TODO: simplify when yet undiscovered side-effects will be fixed
		$this->db_Set_Charset();

		// Save the connection resource
		if ($db_ConnectionID == NULL)
			$db_ConnectionID = $this->mySQLaccess;
	}


	/**
	* @return void
	* @param unknown $sMarker
	* @desc Enter description here...
	* @access private
	*/
	function db_Mark_Time($sMarker) {
		if (E107_DEBUG_LEVEL > 0) {
			global $db_debug;
			$db_debug->Mark_Time($sMarker);
		}
	}

	/**
	* @return void
	* @desc Enter description here...
	* @access private
	*/
	function db_Show_Performance() {
		return $db_debug->Show_Performance();
	}

	/**
	* @return void
	* @desc add query to dblog table
	* @access private
	*/
	function db_Write_log($log_type = '', $log_remark = '', $log_query = '') {
		global $tp, $e107;
		$d = time();
		$uid = (USER) ? USERID : '0';
		$ip = $e107->getip();
		$qry = $tp->toDB($log_query);
		$this->db_Insert('dblog', "0, '{$log_type}', {$d}, {$uid}, '{$ip}', '{$qry}', '{$log_remark}'");
	}

	/**
	* @return unknown
	* @param unknown $query
	* @param unknown $rli
	* @desc Enter description here...
	* @access private
	* This is the 'core' routine which handles much of the interface between other functions and the DB
	*/
	function db_Query($query, $rli = NULL, $qry_from = '', $debug = FALSE, $log_type = '', $log_remark = '') {
		global $db_time,$db_mySQLQueryCount,$queryinfo, $eTraffic;
		$db_mySQLQueryCount++;

		if ($debug == 'now') {
			echo "** $query";
		}
		if ($debug !== FALSE || strstr(e_QUERY, 'showsql'))
		{
			$queryinfo[] = "<b>{$qry_from}</b>: $query";
		}
		if ($log_type != '') {
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

		$eTraffic->Bump('db_Query', $b, $e);
		$mytime = $eTraffic->TimeDelta($b,$e);
		$db_time += $mytime;
		$this->mySQLresult = $sQryRes;
		if (E107_DEBUG_LEVEL) {
			global $db_debug;
			$aTrace = debug_backtrace();
			$pTable = $this->mySQLcurTable;
			if (!strlen($pTable)) {
				$pTable = '(complex query)';
			} else {
				$this->mySQLcurTable = ''; // clear before next query
			}
			if(is_object($db_debug)) {
				$buglink = is_null($rli) ? $this->mySQLaccess : $rli;
			   	$nFields = $db_debug->Mark_Query($query, $buglink, $sQryRes, $aTrace, $mytime, $pTable);
			} else {
				echo "what happened to db_debug??!!<br />";
			}
		}
		return $sQryRes;
	}

	/**
	* @return int Number of rows or false on error
	*
	* @param string $table Table name to select data from
	* @param string $fields Table fields to be retrieved, default * (all in table)
	* @param string $arg Query arguments, default null
	* @param string $mode Argument has WHERE or not, default=default (WHERE)
	*
	* @param bool $debug Debug mode on or off
	*
	* @desc Perform a mysql_query() using the arguments suplied by calling db::db_Query()<br />
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
	* @access public
	*/
	function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = FALSE, $log_type = '', $log_remark = '') {
		global $db_mySQLQueryCount;
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;
		if ($arg != '' && $mode == 'default')
		{
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.MPREFIX.$table.' WHERE '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark)) {
				$this->dbError('dbQuery');
				return $this->db_Rows();
			} else {
			 	$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."{$table} WHERE {$arg})");
				return FALSE;
			}
		} elseif ($arg != '' && $mode != 'default') {
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.MPREFIX.$table.' '.$arg, NULL, 'db_Select', $debug, $log_type, $log_remark)) {
				$this->dbError('dbQuery');
				return $this->db_Rows();
			} else {
			  	$this->dbError("db_Select (SELECT {$fields} FROM ".MPREFIX."{$table} {$arg})");
				return FALSE;
			}
		} else {
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.MPREFIX.$table, NULL, 'db_Select', $debug, $log_type, $log_remark)) {
			 	$this->dbError('dbQuery');
				return $this->db_Rows();
			} else {
			 	$this->dbError("db_Select (SELECT {$fields} FROM ".MPREFIX."{$table})");
				return FALSE;
			}
		}
	}

	/**
	* @return int Last insert ID or false on error
	* @param string $table
	* @param string $arg
	* @param string $debug
	* @desc Insert a row into the table<br />
	* <br />
	* Example:<br />
	* <code>$sql->db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0");</code>
	*
	* @access public
	*/
	function db_Insert($table, $arg, $debug = FALSE, $log_type = '', $log_remark = '') {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;
		if(is_array($arg))
		{
			$keyList= "`".implode("`,`", array_keys($arg))."`";
			$valList= "'".implode("','", $arg)."'";
			$query = "INSERT INTO `".MPREFIX."{$table}` ({$keyList}) VALUES ({$valList})";
		}
		else
		{
			$query = 'INSERT INTO '.MPREFIX."{$table} VALUES ({$arg})";
		}

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}


		if ($result = $this->mySQLresult = $this->db_Query($query, NULL, 'db_Insert', $debug, $log_type, $log_remark )) {
			$tmp = mysql_insert_id($this->mySQLaccess);
			return ($tmp) ? $tmp : TRUE; // return true even if table doesn't have auto-increment.
		} else {
			$this->dbError("db_Insert ($query)");
			return FALSE;
		}
	}

	/**
	* @return int number of affected rows, or false on error
	* @param string $table
	* @param string $arg
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
	function db_Update($table, $arg, $debug = FALSE, $log_type = '', $log_remark = '') {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}

		if ($result = $this->mySQLresult = $this->db_Query('UPDATE '.MPREFIX.$table.' SET '.$arg, NULL, 'db_Update', $debug, $log_type, $log_remark)) {
			$result = mysql_affected_rows($this->mySQLaccess);
			if ($result == -1) return FALSE;	// Error return from mysql_affected_rows
			return $result;
		} else {
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
	function db_Fetch($type = MYSQL_BOTH) {
		global $eTraffic;
		if (!(is_int($type))) {
			$type=MYSQL_BOTH;
		}
		$b = microtime();
		$row = @mysql_fetch_array($this->mySQLresult,$type);
		$eTraffic->Bump('db_Fetch', $b);
		if ($row) {
			$this->dbError('db_Fetch');
			return $row;
		} else {
			$this->dbError('db_Fetch');
			return FALSE;
		}
	}

	/**
	* @return int number of affected rows or false on error
	* @param string $table
	* @param string $fields
	* @param string $arg
	* @desc Count the number of rows in a select<br />
	* <br />
	* Example:<br />
	* <code>$topics = $sql->db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");</code>
	*
	* @access public
	*/
	function db_Count($table, $fields = '(*)', $arg = '', $debug = FALSE, $log_type = '', $log_remark = '') {
		$table = $this->db_IsLang($table);

		if ($fields == 'generic') {
			$query=$table;
			if ($this->mySQLresult = $this->db_Query($query, NULL, 'db_Count', $debug, $log_type, $log_remark)) {
				$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
				return $rows['COUNT(*)'];
			} else {
				$this->dbError("dbCount ($query)");
				return FALSE;
			}
		}

		$this->mySQLcurTable = $table;
		$query='SELECT COUNT'.$fields.' FROM '.MPREFIX.$table.' '.$arg;
		if ($this->mySQLresult = $this->db_Query($query, NULL, 'db_Count', $debug, $log_type, $log_remark)) {
			$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
			return $rows[0];
		} else {
			$this->dbError("dbCount ($query)");
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
	function db_Close() {
		global $eTraffic;
		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}
		$eTraffic->BumpWho('db Close', 1);
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
	function db_Delete($table, $arg = '', $debug = FALSE, $log_type = '', $log_remark = '') {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}


		if (!$arg) {
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.MPREFIX.$table, NULL, 'db_Delete', $debug, $log_type, $log_remark)) {
				return $result;
			} else {
				$this->dbError("db_Delete ($arg)");
				return FALSE;
			}
		} else {
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.MPREFIX.$table.' WHERE '.$arg, NULL, 'db_Delete', $debug, $log_type, $log_remark)) {
				$tmp = mysql_affected_rows($this->mySQLaccess);
				return $tmp;
			} else {
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
	function db_Rows() {
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		$this->dbError('db_Rows');
		return $rows;
	}

	/**
	* @return unknown
	* @param unknown $from
	* @desc Enter description here...
	* @access private
	*/
	function dbError($from) {
		if ($error_message = @mysql_error()) {
			if ($this->mySQLerror == TRUE) {
				message_handler('ADMIN_MESSAGE', '<b>mySQL Error!</b> Function: '.$from.'. ['.@mysql_errno().' - '.$error_message.']', __LINE__, __FILE__);
				return $error_message;
			}
		}
	}

	/**
	* @return void
	* @param unknown $mode
	* @desc Enter description here...
	* @access private
	*/
	function db_SetErrorReporting($mode) {
		$this->mySQLerror = $mode;
	}


	/**
	* @return unknown
	* @param unknown $arg
	* @desc Enter description here...
	* @access private
	*/
	function db_Select_gen($query, $debug = FALSE, $log_type = '', $log_remark = '')
	{
		/*
		changes by jalist 19/01/05:
		added string replace on table prefix to tidy up long database queries
		usage: instead of sending "SELECT * FROM ".MPREFIX."table", do "SELECT * FROM #table"
		Returns result compatible with mysql_query - may be TRUE for some results, resource ID for others
		*/

		$this->tabset = FALSE;
		if(strpos($query,'`#') !== FALSE)
		{
			$query = preg_replace_callback("/\s`#([\w]*?)`\W/", array($this, 'ml_check'), $query);
		}
		elseif(strpos($query,'#') !== FALSE)
	  {
			$query = preg_replace_callback("/\s#([\w]*?)\W/", array($this, 'ml_check'), $query);
	  }

		if (($this->mySQLresult = $this->db_Query($query, NULL, 'db_Select_gen', $debug, $log_type, $log_remark)) === TRUE)
		{	// Successful query which doesn't return a row count
		  $this->dbError('db_Select_gen');
		  return TRUE;
		}
		elseif ($this->mySQLresult === FALSE)
		{	// Failed query
		  $this->dbError('dbQuery ('.$query.')');
		  return FALSE;
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
		return ' `'.MPREFIX.$table.'`'.substr($matches[0],-1);
	}


	/**
	* @return unknown
	* @param unknown $offset
	* @desc Enter description here...
	* @access private
	*/
	function db_Fieldname($offset) {
		$result = @mysql_field_name($this->mySQLresult, $offset);
		return $result;
	}

	/**
	* @return unknown
	* @desc Enter description here...
	* @access private
	*/
	function db_Field_info() {
		$result = @mysql_fetch_field($this->mySQLresult);
		return $result;
	}

	/**
	* @return unknown
	* @desc Enter description here...
	* @access private
	*/
	function db_Num_fields() {
		$result = @mysql_num_fields($this->mySQLresult);
		return $result;
	}

	/**
	* @return unknown
	* @param unknown $table
	* @desc Enter description here...
	* @access private
	*/
	function db_IsLang($table,$multiple=FALSE) {
		global $pref, $mySQLtablelist;
		if ((!$this->mySQLlanguage || !$pref['multilanguage']) && $multiple==FALSE) {
		  	return $table;
		}

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}

		if (!$mySQLtablelist) {
			$tablist = mysql_list_tables($this->mySQLdefaultdb,$this->mySQLaccess);
			while (list($temp) = mysql_fetch_array($tablist)) {
				$mySQLtablelist[] = $temp;
			}
		}

		$mltable = "lan_".strtolower($this->mySQLlanguage.'_'.$table);

	// ---- Find all multi-language tables.

		if($multiple == TRUE){ // return an array of all matching language tables. eg [french]->e107_lan_news
			if(!is_array($table)){
				$table = array($table);
			}

			foreach($mySQLtablelist as $tab){
 				if(stristr($tab, MPREFIX."lan_") !== FALSE){
					$tmp = explode("_",str_replace(MPREFIX."lan_","",$tab));
			   		$lng = $tmp[0];
                    foreach($table as $t){
                    	if(eregi($t."$",$tab)){
							$lanlist[$lng][MPREFIX.$t] = $tab;
						}
					}
			  	}
			}
			return ($lanlist) ? $lanlist : FALSE;
		}
	// -------------------------

		if (in_array(MPREFIX.$mltable, $mySQLtablelist)) {
			return $mltable;
		}
	 	return $table;
	}

	/**
	* @return array
	* @param string fields to retrieve
	* @desc returns fields as structured array
	* @access public
	*/
	function db_getList($fields = 'ALL', $amount = FALSE, $maximum = FALSE, $ordermode=FALSE) {
		$list = array();
		$counter = 1;
		while ($row = $this->db_Fetch()) {
			foreach($row as $key => $value) {
				if (is_string($key)) {
					if (strtoupper($fields) == 'ALL' || in_array ($key, $fields)) {

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
			if ($amount && $amount == $counter || ($maximum && $counter > $maximum)) {
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
	function db_QueryCount() {
		global $db_mySQLQueryCount;
		return $db_mySQLQueryCount;
	}


    /*
    	Multi-language Query Function.
	*/


	function db_Query_all($query,$debug=""){
        $error = "";

		$query = str_replace("#",MPREFIX,$query);

        if(!$this->db_Query($query)){  // run query on the default language first.
        	$error .= $query. " failed";
		}

        $tmp = explode(" ",$query);
      	foreach($tmp as $val){
   			if(strpos($val,MPREFIX) !== FALSE){
    			$table[] = str_replace(MPREFIX,"",$val);
				$search[] = $val;
			}
		}

     // Loop thru relevant language tables and replace each tablename within the query.
        if($tablist = $this->db_IsLang($table,TRUE)){
			foreach($tablist as $key=>$tab){
				$querylan = $query;
                foreach($search as $find){
                    $lang = $key;
					$replace = ($tab[$find] !="") ? $tab[$find] : $find;
               	  	$querylan = str_replace($find,$replace,$querylan);
				}

				if(!$this->db_Query($querylan)){ // run query on other language tables.
					$error .= $querylan." failed for language";
				}
			 	if($debug){ echo "<br />** lang= ".$querylan; }
			}
		}


		return ($error)? FALSE : TRUE;
	}

	// Determines if a plugin field (and key) exist. OR if fieldid is numeric - return the field name in that position.
    function db_Field($table,$fieldid="",$key=""){
		if(!$this->mySQLdefaultdb){
			global $mySQLdefaultdb;
			$this->mySQLdefaultdb = $mySQLdefaultdb;
		}
        $convert = array("PRIMARY"=>"PRI","INDEX"=>"MUL","UNIQUE"=>"UNI");
        $key = ($convert[$key]) ? $convert[$key] : "OFF";

		if(!$this->mySQLaccess)
		{
			global $db_ConnectionID;
        	$this->mySQLaccess = $db_ConnectionID;
		}

        $result = mysql_query("SHOW COLUMNS FROM ".MPREFIX.$table,$this->mySQLaccess);
        if (mysql_num_rows($result) > 0) {
            $c=0;
   			while ($row = mysql_fetch_assoc($result)) {
				if(is_numeric($fieldid))
				{
                	if($c == $fieldid)
					{
                       return $row['Field']; // field number matches.
					}
				}
                else
				{
					if(($key == "OFF") && ($fieldid == $row['Field']))
					{
                    	return TRUE;  // key not in use, but field matches.
					}
					elseif(($fieldid == $row['Field']) && $key == $row['Key'])
					{
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
	 * Check if MySQL version is utf8 compatible and may be used as it accordingly to the user choice
	 *
	 * @access public
	 * @param string    MySQL charset may be forced in special occasion.
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
				preg_match('/^(.*?)($|-)/', mysql_get_server_info(), $mysql_version);
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
	
}

?>