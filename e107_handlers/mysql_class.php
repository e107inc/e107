<?php
/*
+ ----------------------------------------------------------------------------+
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
|     $Revision: 1.2 $
|     $Date: 2004-10-06 02:47:22 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
$sDBdbg="";		//DB debug string
$db_time=0.0;	//Time spent in database

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class db{

	//	 global variables
	var $mySQLserver;
	var $mySQLuser;
	var $mySQLpassword;
	var $mySQLdefaultdb;
	var $mySQLaccess;
	var $mySQLresult;
	var $mySQLrows;
	var $mySQLerror;
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb){
		/*
		# Connect to mySQL server and select database
		#
		# - parameters #1:		string $mySQLserver, mySQL server
		# - parameters #2:		string $mySQLuser, mySQL username
		# - parameters #3:		string $mySQLpassword, mySQL password
		# - parameters #4:		string mySQLdefaultdb, mySQL default database
		# - return				error if encountered
		# - scope					public
		*/

		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;
		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		if(!$this->mySQL_access = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword)){
			return "e1";
		}else{
			if(!@mysql_select_db($this->mySQLdefaultdb)){
				return "e2";
			}else{
				$this->dbError("dbConnect/SelectDB");
			}
		}
	}


//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
		/**
		* Internal query, with debug capability
		*
		* Do the actual mysql_query(), time it, and analyse it if we're debugging
		*
		* @param		string $sql		SQL query string
		* @param		object $RLI		(optional) mysql Record Locator
      * @return   object 			query result
		*
		* @access	private
      * @author   <peteweb@icta.net>
		*
		* @global   float		$db_time total sql time spent
		* @global   int		$dbq		total query count
      * @global   string	$sDBdbg	accumulates debug info for this page. Displayed in footer.
		* @global	int		$HTTP_db_debug	user-settable in url (?HTTP_db_debug=1) to enable debug
		*/
	function db_Query($query,$rli = NULL ) {

	global $dbq,$HTTP_db_debug,$db_time,$sDBdbg;
	$dbq++; 

 	if ($HTTP_db_debug) { 
		$query = str_replace(","," , ",$query);
		$sQryRes =  is_null($rli) ? mysql_query("EXPLAIN $query") : mysql_query("EXPLAIN $query",$rli);
		$nFields = "";
		if ($sQryRes) { // There's something to explain
   		$nFields = mysql_num_fields($sQryRes);
		}

		$sDBdbg .= "\n<table style='width:100%; border:1px solid' cellpadding='2' cellspacing='1'>\n";
		$sDBdbg .= "<tr><td colspan='$nFields'><b>Query:</b> $query</td></tr>\n";
		if ($sQryRes)
		{
			$bRowHeaders = FALSE;
			while ($row = @mysql_fetch_assoc($sQryRes))
			{
				if (!$bRowHeaders)
				{
					$bRowHeaders = TRUE;
					$sDBdbg .= "<tr><td><b>".implode("</b></td><td><b>",array_keys($row))."</b></td></tr>\n";
				}
				$sDBdbg .= "<tr><td>".implode("&nbsp;</td><td>",array_values($row))."&nbsp;</td></tr>\n";
			}
		}
	}

	$_dbTimeStart = explode(' ',microtime());
	$sQryRes = is_null($rli) ? @mysql_query($query) : @mysql_query($query,$rli);
	$_dbTimeEnd = explode(' ',microtime());
	$mytime= ((float)$_dbTimeEnd[0]+(float)$_dbTimeEnd[1]) - ((float)$_dbTimeStart[0]+(float)$_dbTimeStart[1]);
	$db_time += $mytime;
	$mytime = round($mytime,4);  //round for local display

	if ($HTTP_db_debug && $sQryRes) {
		$sDBdbg .=  "<tr><td colspan=\"$nFields\"><b>Query time: $mytime</td></tr></table><br />";
	}

  return $sQryRes;
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Select($table, $fields="*", $arg="", $mode="default", $debug=FALSE){
		/*
		# Select with args
		#
		# - parameter #1:	string $table, table name
		# - parameter #2:	string $fields, table fields to be retrieved, default *
		# - parameter #3:	string $arg, query arguaments, default null
		# - parameter #4:	string $mode, arguament has WHERE or not, default=default (WHERE)
		# - return				affected rows
		# - scope					public
		*/

		if($arg != "" && $mode=="default"){
			if($debug){ echo "SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg."<br />"; }
			if($this->mySQLresult = $this->db_Query("SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table WHERE $arg)");
				return FALSE;
			}
		}else if($arg != "" && $mode != "default"){
			if($debug){ echo "@@SELECT ".$fields." FROM ".MPREFIX.$table." ".$arg."<br />"; }
			if($this->mySQLresult = $this->db_Query("SELECT ".$fields." FROM ".MPREFIX.$table." ".$arg)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table $arg)");
				return FALSE;
			}
		}else{
			if($debug){ echo "SELECT ".$fields." FROM ".MPREFIX.$table."<br />"; }
			if($this->mySQLresult = $this->db_Query("SELECT ".$fields." FROM ".MPREFIX.$table)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("db_Select (SELECT $fields FROM ".MPREFIX."$table)");
				return FALSE;
			}		
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Insert($table, $arg, $debug=FALSE){
		/*
		# Insert with args
		#
		# - parameter #1:	string $table, table name
		# - parameter #2:	string $arg, insert string
		# - return				sql identifier, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/

		if($debug){
			echo "INSERT INTO ".MPREFIX.$table." VALUES (".htmlentities($arg).")";
		}

//		if(!ANON && !USER && $table != "user"){ return FALSE; }

		if($result = $this->mySQLresult = $this->db_Query("INSERT INTO ".MPREFIX.$table." VALUES (".$arg.")" )){
			$tmp = mysql_insert_id();

			if(strstr(e_SELF, ADMINDIR) && $table != "online"){
				mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '<br /><b>Insert</b> - <b>$table</b> table (field id <b>$tmp</b>)<br />by <b>".USERNAME."</b>') ");
			}
			return $tmp;
		}else{
			$this->dbError("db_Insert ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Update($table, $arg, $debug=FALSE){
		/*
		# Update with args
		#
		# - parameter #1:	string $table, table name
		# - parameter #2:	string $arg, update string
		# - return				sql identifier, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/

		if($debug){ echo "UPDATE ".MPREFIX.$table." SET ".$arg."<br />"; }	
		if($result = $this->mySQLresult = $this->db_Query("UPDATE ".MPREFIX.$table." SET ".$arg)){
			$result = mysql_affected_rows();
			if(strstr(e_SELF, ADMINDIR) && $table != "online"){
				if(!strstr($arg, "link_order")){
					$str = addslashes(str_replace("WHERE", "", substr($arg, strpos($arg, "WHERE"))));
					mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '<br /><b>Update</b> - <b>$table</b> table (string: <b>$str</b>)<br />by <b>".USERNAME."</b>') ");
				}
			}
			return $result;
		}else{
			$this->dbError("db_Update ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Fetch($mode = "strip"){
		/*
		# Retrieve table row
		#
		# - parameters		none
		# - return				result array, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/
		if($row = @mysql_fetch_array($this->mySQLresult)){
			if($mode == strip){
				while (list($key,$val) = each($row)){
					$row[$key] = stripslashes($val);
				}
			}
			$this->dbError("db_Fetch");
			return $row;
		}else{
			$this->dbError("db_Fetch");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Count($table, $fields="(*)", $arg=""){
		/*
		# Retrieve result count
		#
		# - parameter #1:	string $table, table name
		# - parameter #2:	string $fields, count fields, default (*)
		# - parameter #3:	string $arg, count string, default null
		# - return				result array, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/
//		echo "SELECT COUNT".$fields." FROM ".MPREFIX.$table." ".$arg;

		if($fields == "generic"){
			if($this->mySQLresult = $this->db_Query($table)){
				$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
				return $rows[0];
			}else{
				$this->dbError("dbCount ($query)");
			}
		}

		if($this->mySQLresult = $this->db_Query("SELECT COUNT".$fields." FROM ".MPREFIX.$table." ".$arg)){
			$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
			return $rows[0];
		}else{
			$this->dbError("dbCount ($query)");
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Close(){
		/*
		# Close mySQL server connection
		#
		# - parameters		none
		# - return				null
		# - scope					public
		*/
		mysql_close();
		$this->dbError("dbClose");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Delete($table, $arg=""){
		/*
		# Delete with args
		#
		# - parameter #1:	string $table, table name
		# - parameter #2:	string $arg, delete string
		# - return				result array, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/
		if($table == "user"){
	//		echo "DELETE FROM ".MPREFIX.$table." WHERE ".$arg."<br />";			// debug
		}
		if(!$arg){
			if($result = $this->mySQLresult = $this->db_Query("DELETE FROM ".MPREFIX.$table)){
				if(strstr(e_SELF, ADMINDIR) && $table != "online" && $table != "tmp"){
					$str = addslashes(str_replace("WHERE", "", substr($arg, strpos($arg, "WHERE"))));

					if($table == "cache"){
						$string = "<br /><b>Delete</b> - <b>$table</b> table (routine tidy-up)<br />by <b>e107</b>";
					}else{
						$string = "<br /><b>Delete</b> - <b>$table</b> table (all entries deleted)<br />by <b>".USERNAME."</b>";
					}

					mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '$string') ");
				}
				return $result;
			}else{
				$this->dbError("db_Delete ($arg)");
				return FALSE;
			}
		}else{
			if($result = $this->mySQLresult = $this->db_Query("DELETE FROM ".MPREFIX.$table." WHERE ".$arg)){
				$tmp = mysql_affected_rows();
				if(strstr(e_SELF, ADMINDIR) && $table != "online" && $table != "tmp"){
					$str = addslashes(str_replace("WHERE", "", substr($arg, strpos($arg, "WHERE"))));
					mysql_query("INSERT INTO ".MPREFIX."tmp VALUES ('adminlog', '".time()."', '<b>Delete</b> - <b>$table</b> table (string: <b>$str</b>) by <b>".USERNAME."</b>') ");
				}
				return $tmp;
			}else{
				$this->dbError("db_Delete ($arg)");
				return FALSE;
			}
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Rows(){
		/*
		# Return affected rows
		#
		# - parameters		none
		# - return				affected rows, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		return $rows;
		$this->dbError("db_Rows");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function dbError($from){
		/*
		# Return affected rows
		#
		# - parameter #1		string $from, routine that called this function
		# - return				error message on mySQL error
		# - scope					private
		*/
		if($error_message = @mysql_error()){
			if($this->mySQLerror == TRUE){
				message_handler("ADMIN_MESSAGE", "<b>mySQL Error!</b> Function: $from. [".@mysql_errno()." - $error_message]",  __LINE__, __FILE__);
				return $error_message;
			}
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_SetErrorReporting($mode){
		$this->mySQLerror = $mode;
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function db_Select_gen($arg){

		//echo "\mysql_query($arg)";
		if($this->mySQLresult = $this->db_Query($arg)){
			$this->dbError("db_Select_gen");
			return $this->db_Rows();
		}else{
			$this->dbError("dbQuery ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function db_Fieldname($offset){

		$result = @mysql_field_name($this->mySQLresult, $offset);
		return $result;
	}

	function db_Num_fields(){
		$result = @mysql_num_fields($this->mySQLresult);
		return $result;
	}


}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>