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
|     $Revision: 1.15 $
|     $Date: 2004-12-22 15:47:45 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

$db_time = 0.0;

/**
* MySQL Abstraction class
*
* @package e107
* @version $Revision: 1.15 $
* @author $Author: streaky $
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

	/**
	* @return db
	* @desc db constructor gets language options from the cookie or session
	* @access public
	*/
	function db() {
		global $pref;
		$langid = 'e107language_'.$pref['cookie_name'];
		if ($pref['user_tracking'] == 'session') {
			$this->mySQLlanguage = ($this->db_IsLang($_SESSION[$langid])) ? $_SESSION[$langid] : '';
		} else {
			$this->mySQLlanguage = ($this->db_IsLang($_COOKIE[$langid])) ? $_COOKIE[$langid] : '';
		}
	}

	/**
	* @return null or string error code
	* @param string $mySQLserver IP Or hostname of the MySQL server
	* @param string $mySQLuser MySQL username
	* @param string $mySQLpassword MySQL Password
	* @param string $mySQLdefaultdb The database schema to connect to
	* @desc Connects to mySQL server and selects database - generally not required if your table is in the main DB.<br />
	* <br />
	* Example using e107 database with variables defined in config.php:<br />
	* <code>$sql = new db;
	* $sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);</code>
	* <br />
	* OR to connect an other database:<br />
	* <code>$sql = new db;
	* $sql -> db_Connect('url_server_database', 'user_database', 'password_database', 'name_of_database');</code>
	* 
	* @access public
	*/
	function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb) {
		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;
		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		if (!$this->mySQL_access = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword)) {
			return 'e1';
		} else {
			if (!@mysql_select_db($this->mySQLdefaultdb)) {
				return 'e2';
			} else {
				$this->dbError('dbConnect/SelectDB');
			}
		}
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
		if (E107_DEBUG_LEVEL > 0) {
			$db_debug->Show_Performance();
		}
	}

	/**
	* @return unknown
	* @param unknown $query
	* @param unknown $rli
	* @desc Enter description here...
	* @access private
	*/
	function db_Query($query, $rli = NULL) {
		global $dbq, $e107_debug, $db_time, $sDBdbg, $aTimeMarks, $aDBbyTable, $curTimeMark;
		$dbq++;
		if (E107_DEBUG_LEVEL > 0) {
			global $db_debug;
			$aTrace = debug_backtrace();
			$nFields = $db_debug->Mark_Query($query, $rli, $aTrace);
		}
		$_dbTimeStart = explode(' ', microtime());
		$sQryRes = is_null($rli) ? @mysql_query($query) : @mysql_query($query, $rli);
		$_dbTimeEnd = explode(' ', microtime());
		$mytime = ((float)$_dbTimeEnd[0] + (float)$_dbTimeEnd[1]) - ((float)$_dbTimeStart[0] + (float)$_dbTimeStart[1]);
		$db_time += $mytime;
		$this->mySQLresult = $sQryRes;
		if (E107_DEBUG_LEVEL > 0 && $sQryRes) {
			global $db_debug;
			$db_debug->Mark_Query_Results($mytime, $this->mySQLcurTable, $nFields);
		}
		return $sQryRes;
	}

	/**
	* @return int Number of rows or false on error
	* 
	* @param string $table Table name to select data from
	* @param string $fields	Table fields to be retrieved, default * (all in table)
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
	function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = FALSE) {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;
		if ($arg != '' && $mode == 'default') {
			if ($debug) {
				echo 'SELECT '.$fields.' FROM '.MPREFIX.$table.' WHERE '.$arg.'<br />';
			}
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.MPREFIX.$table.' WHERE '.$arg)) {
				$this->dbError('dbQuery');
				return $this->db_Rows();
			} else {
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table WHERE $arg)");
				return FALSE;
			}
		} elseif($arg != '' && $mode != 'default') {
			if ($debug) {
				echo '@@SELECT '.$fields.' FROM '.MPREFIX.$table.' '.$arg.'<br />';
			}
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.MPREFIX.$table.' '.$arg)) {
				$this->dbError('dbQuery');
				return $this->db_Rows();
			} else {
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table $arg)");
				return FALSE;
			}
		} else {
			if ($debug) {
				echo 'SELECT '.$fields.' FROM '.MPREFIX.$table.'<br />';
			}
			if ($this->mySQLresult = $this->db_Query('SELECT '.$fields.' FROM '.MPREFIX.$table)) {
				$this->dbError('dbQuery');
				return $this->db_Rows();
			} else {
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table)");
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
	* <code>$sql -> db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0");</code>
	* 
	* @access public
	*/
	function db_Insert($table, $arg, $debug = FALSE) {
		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		if ($debug) {
			echo 'INSERT INTO '.MPREFIX.$table.' VALUES ('.htmlentities($arg).')';
		}

		if ($result = $this->mySQLresult = $this->db_Query('INSERT INTO '.MPREFIX.$table.' VALUES ('.$arg.')' )) {
			$tmp = mysql_insert_id();

			if (strstr(e_SELF, ADMINDIR) && $table != 'online') {
				mysql_query('INSERT INTO '.MPREFIX.'tmp VALUES (\'adminlog\', \''.time()."', '<br /><b>Insert</b> - <b>$table</b> table (field id <b>$tmp</b>)<br />by <b>".USERNAME."</b>') ");
			}
			return $tmp;
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
	* <code>$sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");</code>
	* <br />
	* OR as second connection<br />
	* <code>$sql2 = new db;
	* $sql2 -> db_Update("user", "user_viewed = '$u_new' WHERE user_id = '".USERID."' ");</code><br />
	* 
	* @access public
	*/
	function db_Update($table, $arg, $debug = FALSE) {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;
		if ($debug) {
			echo 'UPDATE '.MPREFIX.$table.' SET '.$arg.'<br />';
		}
		if ($result = $this->mySQLresult = $this->db_Query('UPDATE '.MPREFIX.$table.' SET '.$arg)) {
			$result = mysql_affected_rows();
			if (strstr(e_SELF, ADMINDIR) && $table != 'online') {
				if (!strstr($arg, 'link_order')) {
					$str = addslashes(str_replace('WHERE', '', substr($arg, strpos($arg, 'WHERE'))));
					mysql_query('INSERT INTO '.MPREFIX."tmp VALUES ('adminlog', '".time()."', '<br /><b>Update</b> - <b>$table</b> table (string: <b>$str</b>)<br />by <b>".USERNAME."</b>') ");
				}
			}
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
	* <code>while($row = $sql -> db_Fetch()){
	* 	$text .= $row['username'];
	* }</code>
	* 
	* @access public
	*/
	function db_Fetch($mode = 'strip') {
		if ($row = @mysql_fetch_array($this->mySQLresult)) {
			if ($mode == 'strip') {
				while (list($key, $val) = each($row)) {
					$row[$key] = stripslashes($val);
				}
			}
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
	* <code>$topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");</code>
	* 
	* @access public
	*/
	function db_Count($table, $fields = '(*)', $arg = '') {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;
		if ($fields == 'generic') {
			if ($this->mySQLresult = $this->db_Query($table)) {
				$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
				return $rows[0];
			} else {
				$this->dbError("dbCount ($query)");
			}
		}
		if ($this->mySQLresult = $this->db_Query('SELECT COUNT'.$fields.' FROM '.MPREFIX.$table.' '.$arg)) {
			$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
			return $rows[0];
		} else {
			$this->dbError("dbCount ($query)");
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
	* <code>$sql -> db_Close();</code>
	* 
	* @access public
	*/
	function db_Close() {
		mysql_close();
		$this->dbError('dbClose');
	}

	/**
	* @return int number of affected rows, or false on error
	* @param string $table
	* @param string $arg
	* @desc Delete rows from a table<br />
	* <br />
	* Example:
	* <code>$sql -> db_Delete("tmp", "tmp_ip='$ip'");</code><br />
	* <br />
	* @access public
	*/
	function db_Delete($table, $arg = '') {
		$table = $this->db_IsLang($table);
		$this->mySQLcurTable = $table;
		if (!$arg) {
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.MPREFIX.$table)) {
				if (strstr(e_SELF, ADMINDIR) && $table != 'online' && $table != 'tmp') {
					$str = addslashes(str_replace('WHERE', '', substr($arg, strpos($arg, 'WHERE'))));
					mysql_query('INSERT INTO '.MPREFIX."tmp VALUES ('adminlog', '".time()."', '$string') ");
				}
				return $result;
			} else {
				$this->dbError("db_Delete ($arg)");
				return FALSE;
			}
		} else {
			if ($result = $this->mySQLresult = $this->db_Query('DELETE FROM '.MPREFIX.$table.' WHERE '.$arg)) {
				$tmp = mysql_affected_rows();
				if (strstr(e_SELF, ADMINDIR) && $table != 'online' && $table != 'tmp') {
					$str = addslashes(str_replace('WHERE', '', substr($arg, strpos($arg, 'WHERE'))));
					mysql_query('INSERT INTO '.MPREFIX."tmp VALUES ('adminlog', '".time()."', '<b>Delete</b> - <b>$table</b> table (string: <b>$str</b>) by <b>".USERNAME."</b>') ");
				}
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
		return $rows;
		$this->dbError('db_Rows');
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
	function db_Select_gen($arg) {
		if ($this->mySQLresult = $this->db_Query($arg)) {
			$this->dbError('db_Select_gen');
			return $this->db_Rows();
		} else {
			$this->dbError('dbQuery ('.$query.')');
			return FALSE;
		}
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
	function db_IsLang($table) {
		global $pref, $mySQLtablelist;
		if (!$this->mySQLlanguage || !$pref['multilanguage']) {
			return $table;
		}
		if (!$mySQLtablelist) {
			$tablist = mysql_list_tables($this->mySQLdefaultdb);
			while (list($temp) = mysql_fetch_array($tablist)) {
				$mySQLtablelist[] = $temp;
			}
		}
		$mltable = strtolower($this->mySQLlanguage.'_'.$table);
		if (in_array(MPREFIX.$mltable, $mySQLtablelist)) {
			return $mltable;
		}
		return $table;
	}
}

?>