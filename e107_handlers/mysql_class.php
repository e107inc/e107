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
|     $Revision: 1.11 $
|     $Date: 2004-12-19 01:48:01 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

$db_time=0.0;        //Time spent in database

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class db {

	//	global variables
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

	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db() { // need to add session support as well.

	$langid =  "e107language_".$GLOBALS['pref']['cookie_name'];
	if($GLOBALS['pref']['user_tracking'] == "session") {
		$this->mySQLlanguage = ($this->db_IsLang($_SESSION[$langid])) ? $_SESSION[$langid] : "";
	} else {
		$this->mySQLlanguage = ($this->db_IsLang($_COOKIE[$langid])) ? $_COOKIE[$langid] : "";
	}

	}
	// -------------------------------------------------------------------------------------------


	function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb) {
		/*
		# Connect to mySQL server and select database
		#
		# - parameters #1:                string $mySQLserver, mySQL server
		# - parameters #2:                string $mySQLuser, mySQL username
		# - parameters #3:                string $mySQLpassword, mySQL password
		# - parameters #4:                string mySQLdefaultdb, mySQL default database
		# - return                                error if encountered
		# - scope                                        public
		*/

		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;
		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		if(!$this->mySQL_access = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword)) {
			return 'e1';
		} else {
			if(!@mysql_select_db($this->mySQLdefaultdb)) {
				return 'e2';
			} else {
				$this->dbError("dbConnect/SelectDB");
			}
		}
	}

	/*
	* Insert time markers for simple performance analysis
	*
	* Goal: break down performance by big-picture modules (i.e. side menus and main page content)
	*/
	function db_Mark_Time($sMarker) {
		if (E107_DEBUG_LEVEL > 0) {
			global $db_debug;
			$db_debug->Mark_Time($sMarker);
		}
//		global $aTimeMarks,$curTimeMark,$aOBMarks,$aMarkNotes;
//
//		$timeNow = explode(' ',microtime());
//		$aTimeMarks[$sMarker]=array('What' => $sMarker, '%Time'=>0,'%DB Time'=>0,'%DB Count'=>0,'Time' => $timeNow,'DB Time'=>0,'DB Count'=>0);
//		$aOBMarks[$sMarker] = ob_get_level().'('.ob_get_length().')';
//		$curTimeMark = $sMarker;
	}
	/*
	* Render debug/performance data
	*
	* Chronological data: absolute (and delta) time of various items
	* Cumulative data: # queries by table and caller, with time spent
	*
	*/
	function db_Show_Performance() {
		if (E107_DEBUG_LEVEL > 0) {
			$db_debug->Show_Performance();
		}
	}


	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	/**
	* Internal query, with debug capability
	*
	* Do the actual mysql_query(), time it, and analyse it if we're debugging
	*
	* @param                string $sql                SQL query string
	* @param                object $RLI                (optional) mysql Record Locator
	* @return   object                         query result
	*
	* @access        private
	* @author   <peteweb@icta.net>
	*
	* @global   float                $db_time total sql time spent
	* @global   int                $dbq                total query count
	* @global   string        $sDBdbg        accumulates debug info for this page. Displayed in footer.
	* @global        int                $e107_debug        user-settable -- append ?[debug] or ?[debug=nn] to enable debug
	*/
	function db_Query($query, $rli = NULL) {
		global $dbq,$e107_debug,$db_time,$sDBdbg,$aTimeMarks,$aDBbyTable,$curTimeMark;
		$dbq++;

		if (E107_DEBUG_LEVEL > 0) {
			global $db_debug;
			$aTrace = debug_backtrace();
			$nFields = $db_debug->Mark_Query($query,$rli,$aTrace);
		}
		$_dbTimeStart = explode(' ',microtime());
		$sQryRes = is_null($rli) ? @mysql_query($query) : @mysql_query($query,$rli);
		$_dbTimeEnd = explode(' ',microtime());
		$mytime= ((float)$_dbTimeEnd[0]+(float)$_dbTimeEnd[1]) - ((float)$_dbTimeStart[0]+(float)$_dbTimeStart[1]);
		$db_time += $mytime;
		$this->mySQLresult = $sQryRes;
		if (E107_DEBUG_LEVEL > 0 && $sQryRes) {
			global $db_debug;
			$db_debug->Mark_Query_Results($mytime, $this->mySQLcurTable,$nFields);
		}
		return $sQryRes;
	}

	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Select($table, $fields="*", $arg="", $mode="default", $debug=FALSE) {
		/*
		# Select with args
		#
		# - parameter #1:        string $table, table name
		# - parameter #2:        string $fields, table fields to be retrieved, default *
		# - parameter #3:        string $arg, query arguaments, default null
		# - parameter #4:        string $mode, arguament has WHERE or not, default=default (WHERE)
		# - return                                affected rows
		# - scope                                        public
		*/

		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		if($arg != "" && $mode=="default") {
			if($debug){ echo "SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg."<br />"; }
			if($this->mySQLresult = $this->db_Query("SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg)) {
				$this->dbError("dbQuery");
				return $this->db_Rows();
			} else {
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table WHERE $arg)");
				return FALSE;
			}
		} elseif($arg != "" && $mode != "default") {
			if($debug){ echo "@@SELECT ".$fields." FROM ".MPREFIX.$table." ".$arg."<br />"; }
			if ($this->mySQLresult = $this->db_Query("SELECT ".$fields." FROM ".MPREFIX.$table." ".$arg)) {
				$this->dbError("dbQuery");
				return $this->db_Rows();
			} else {
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table $arg)");
				return FALSE;
			}
		} else {
			if($debug){ echo "SELECT ".$fields." FROM ".MPREFIX.$table."<br />"; }
			if ($this->mySQLresult = $this->db_Query("SELECT ".$fields." FROM ".MPREFIX.$table)) {
				$this->dbError("dbQuery");
				return $this->db_Rows();
			} else {
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table)");
				return FALSE;
			}
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Insert($table, $arg, $debug=FALSE) {
		/*
		# Insert with args
		#
		# - parameter #1:        string $table, table name
		# - parameter #2:        string $arg, insert string
		# - return                                sql identifier, or error if (error reporting = on, error occured, boolean)
		# - scope                                        public
		*/
		//     $table = "french";
		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		if ($debug) {
			echo "INSERT INTO ".MPREFIX.$table." VALUES (".htmlentities($arg).")";
		}

		if ($result = $this->mySQLresult = $this->db_Query("INSERT INTO ".MPREFIX.$table." VALUES (".$arg.")" )) {
			$tmp = mysql_insert_id();

			if(strstr(e_SELF, ADMINDIR) && $table != "online") {
				mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '<br /><b>Insert</b> - <b>$table</b> table (field id <b>$tmp</b>)<br />by <b>".USERNAME."</b>') ");
			}
			return $tmp;
		} else {
			$this->dbError("db_Insert ($query)");
			return FALSE;
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Update($table, $arg, $debug=FALSE) {
		/*
		# Update with args
		#
		# - parameter #1:        string $table, table name
		# - parameter #2:        string $arg, update string
		# - return                                sql identifier, or error if (error reporting = on, error occured, boolean)
		# - scope                                        public
		*/

		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		if ($debug) { echo "UPDATE ".MPREFIX.$table." SET ".$arg."<br />"; }
		if ($result = $this->mySQLresult = $this->db_Query("UPDATE ".MPREFIX.$table." SET ".$arg)) {
			$result = mysql_affected_rows();
			if (strstr(e_SELF, ADMINDIR) && $table != "online") {
				if (!strstr($arg, "link_order")) {
					$str = addslashes(str_replace("WHERE", "", substr($arg, strpos($arg, "WHERE"))));
					mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '<br /><b>Update</b> - <b>$table</b> table (string: <b>$str</b>)<br />by <b>".USERNAME."</b>') ");
				}
			}
			return $result;
		} else {
			$this->dbError("db_Update ($query)");
			return FALSE;
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Fetch($mode = "strip") {
		/*
		# Retrieve table row
		#
		# - parameters	none
		# - return		result array, or error if (error reporting = on, error occured, boolean)
		# - scope		public
		*/

		if ($row = @mysql_fetch_array($this->mySQLresult)) {
			if ($mode == 'strip') {
				while (list($key,$val) = each($row)){
					$row[$key] = stripslashes($val);
				}
			}
			$this->dbError("db_Fetch");
			return $row;
		} else {
			$this->dbError("db_Fetch");
			return FALSE;
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Count($table, $fields="(*)", $arg="") {
		/*
		# Retrieve result count
		#
		# - parameter #1:        string $table, table name
		# - parameter #2:        string $fields, count fields, default (*)
		# - parameter #3:        string $arg, count string, default null
		# - return                                result array, or error if (error reporting = on, error occured, boolean)
		# - scope                                        public
		*/

		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		//                echo "SELECT COUNT".$fields." FROM ".MPREFIX.$table." ".$arg;

		if ($fields == "generic") {
			if ($this->mySQLresult = $this->db_Query($table)) {
				$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
				return $rows[0];
			} else {
				$this->dbError("dbCount ($query)");
			}
		}

		if ($this->mySQLresult = $this->db_Query("SELECT COUNT".$fields." FROM ".MPREFIX.$table." ".$arg)) {
			$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
			return $rows[0];
		} else {
			$this->dbError("dbCount ($query)");
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Close() {
		/*
		# Close mySQL server connection
		#
		# - parameters                none
		# - return                                null
		# - scope                                        public
		*/
		mysql_close();
		$this->dbError("dbClose");
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Delete($table, $arg="") {
		/*
		# Delete with args
		#
		# - parameter #1:        string $table, table name
		# - parameter #2:        string $arg, delete string
		# - return                                result array, or error if (error reporting = on, error occured, boolean)
		# - scope                                        public
		*/


		$table = $this->db_IsLang($table);

		$this->mySQLcurTable = $table;
		if ($table == "user") {
			//                echo "DELETE FROM ".MPREFIX.$table." WHERE ".$arg."<br />";                        // debug
		}
		if (!$arg) {
			if ($result = $this->mySQLresult = $this->db_Query("DELETE FROM ".MPREFIX.$table)) {
				if (strstr(e_SELF, ADMINDIR) && $table != "online" && $table != "tmp") {
					$str = addslashes(str_replace("WHERE", "", substr($arg, strpos($arg, "WHERE"))));

					if ($table == "cache") {
						$string = "<br /><b>Delete</b> - <b>$table</b> table (routine tidy-up)<br />by <b>e107</b>";
					} else {
						$string = "<br /><b>Delete</b> - <b>$table</b> table (all entries deleted)<br />by <b>".USERNAME."</b>";
					}

					mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '$string') ");
				}
				return $result;
			} else {
				$this->dbError("db_Delete ($arg)");
				return FALSE;
			}
		} else {
			if ($result = $this->mySQLresult = $this->db_Query("DELETE FROM ".MPREFIX.$table." WHERE ".$arg)) {
				$tmp = mysql_affected_rows();
				if (strstr(e_SELF, ADMINDIR) && $table != "online" && $table != "tmp") {
					$str = addslashes(str_replace("WHERE", "", substr($arg, strpos($arg, "WHERE"))));
					mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '<b>Delete</b> - <b>$table</b> table (string: <b>$str</b>) by <b>".USERNAME."</b>') ");
				}
				return $tmp;
			} else {
				$this->dbError("db_Delete ($arg)");
				return FALSE;
			}
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Rows() {
		/*
		# Return affected rows
		#
		# - parameters                none
		# - return                                affected rows, or error if (error reporting = on, error occured, boolean)
		# - scope                                        public
		*/
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		return $rows;
		$this->dbError("db_Rows");
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function dbError($from) {
		/*
		# Return affected rows
		#
		# - parameter #1                string $from, routine that called this function
		# - return                                error message on mySQL error
		# - scope                                        private
		*/
		if ($error_message = @mysql_error()) {
			if ($this->mySQLerror == TRUE) {
				message_handler("ADMIN_MESSAGE", "<b>mySQL Error!</b> Function: $from. [".@mysql_errno()." - $error_message]",  __LINE__, __FILE__);
				return $error_message;
			}
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_SetErrorReporting($mode) {
		$this->mySQLerror = $mode;
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function db_Select_gen($arg) {

		//echo "\mysql_query($arg)";
		if ($this->mySQLresult = $this->db_Query($arg)) {
			$this->dbError("db_Select_gen");
			return $this->db_Rows();
		} else {
			$this->dbError("dbQuery ($query)");
			return FALSE;
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function db_Fieldname($offset) {
		$result = @mysql_field_name($this->mySQLresult, $offset);
		return $result;
	}

	function db_Field_info() { /* use immediately after a seek for info on next field */
	$result = @mysql_fetch_field($this->mySQLresult);
	return $result;
	}
	// ----------------------------------------------------------------------------
	function db_Num_fields() {
		$result = @mysql_num_fields($this->mySQLresult);
		return $result;
	}
	// ----------------------------------------------------------------------------
	function db_IsLang($table) {

		// Return table name based on mySQLlanguage.
		// Uses $GLOBALS to share table list between DB objects.

		if (!$this->mySQLlanguage || !$GLOBALS['pref']['multilanguage']) {
			return $table;   // multi-lang turned off
		}


		if (!$GLOBALS['mySQLtablelist']) {
			$tablist = mysql_list_tables($this->mySQLdefaultdb);
			while (list($temp) = mysql_fetch_array($tablist)) {
				$GLOBALS['mySQLtablelist'][] = $temp;
			}
		}


		$mltable = strtolower($this->mySQLlanguage."_".$table);
		if (in_array(MPREFIX.$mltable,$GLOBALS['mySQLtablelist'])) {
			return $mltable;  // language table found.
		}
		return $table; // language table not found.
	}
}

?>