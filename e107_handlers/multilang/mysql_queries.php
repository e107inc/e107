<?php
/*
*
* MySql Handler for multilanguage (works with the default e107 mysql handler)
*
*/
// $Id: mysql_queries.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 
class e107_ml {
	//global $list_e107lang;
	var $tab_sql;
	// Function to execute a SQL SELECT with multilanguage system
	// Return same value as normal db_Select
	function e107_ml_Select($ml_table, $ml_fields="*", $ml_arg="", $ml_mode="default", $ml_debug=FALSE, $var_sql="sql"){
    global $pref, $$var_sql;
    //echo $$var_sql;
		//$sql = new db;
		if(!is_object($$var_sql)){
      $$var_sql = new db;
    }
		require_once(e_HANDLER."mail.php");
		
		$ml_fullname = strtolower(e_DBLANGUAGE."_".$ml_table);
		if(e_DBLANGUAGE!=e_LAN){
			if($tab_sql = $$var_sql -> db_Select($ml_fullname, $ml_fields, $ml_arg, $ml_mode, $ml_debug)){
        return $tab_sql;
			}else if($tab_sql = $$var_sql -> db_Select($ml_table, $ml_fields, $ml_arg, $ml_mode, $ml_debug)){
				if($pref['e107ml_mailalert']==1){
          echo "<br />Error1: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following SELECT request was required but was not successful:\n".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug.".\n\nPlease report bug to the e107 Dev team, on e107.org");
				}
				
				return $tab_sql;
			}
		}else if($tab_sql = $$var_sql -> db_Select($ml_table, $ml_fields, $ml_arg, $ml_mode, $ml_debug)){
			return $tab_sql;
		}
		return FALSE;
	}
	
	
	// Function to execute a UNIQUE SQL INSERT with multilanguage system
	// Return same value as normal db_Insert
	function e107_ml_Insert($ml_table, $ml_arg, $ml_debug=FALSE){
		global $pref, $sql;
    //$sql = new db;
		require_once(e_HANDLER."mail.php");
		$ml_fullname = strtolower(e_DBLANGUAGE."_".$ml_table);
		if(e_DBLANGUAGE!=e_LAN){
			if($tab_sql = $sql -> db_Insert($ml_fullname, $ml_arg, $ml_debug)){
				return $tab_sql;
			}else if($tab_sql = $sql -> db_Insert($ml_table, $ml_arg, $ml_debug)){
				if($pref['e107ml_mailalert']==1){
          echo "<br />Error2: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following INSERT request was required but was not successful:\n".$ml_fullname." - ".$ml_arg." - ".$ml_debug.".\n\nPlease report bug to the e107 Dev team, on e107.org");
				}
        return FALSE;
			}
		}else if($tab_sql = $sql -> db_Insert($ml_table, $ml_arg, $ml_debug)){
			return $tab_sql;
		}
		return FALSE;
	}
	
	
	// Function to execute a SQL MULTI INSERT with multilanguage system
	// Return an array with all values of normal db_Insert
	function e107_ml_MultiInsert($ml_table, $ml_arg, $ml_debug=FALSE){
		global $ns, $pref;
		//$sql = new db;
		require_once(e_HANDLER."mail.php");
		global $list_e107lang,$sql,$tables_lang;
		// Default Insert
		$tab_sql_arr = "";
		$report_ml_error = "";
		$tab_sqltmp = $sql -> db_Insert($ml_table, $ml_arg, $ml_debug);
		$tab_sql_arr = $tab_sqltmp;
		// Other tables
		for($i=0;$i<e_MLANGNBR;$i++){
			//echo "<br />->".$tables_lang[$list_e107lang[$i]][2];
      if(in_array($ml_table,$tables_lang[$list_e107lang[$i]])){
          if($ml_debug==TRUE){echo "Nbr lang: ".e_MLANGNBR."<br />Lang".$i." ".$list_e107lang[$i];}
    			$ml_fullname = strtolower($list_e107lang[$i]."_".$ml_table);
          if($tab_sqltmp = $sql -> db_Insert($ml_fullname, $ml_arg, $ml_debug)){
            if($tab_sql_arr != $tab_sqltmp){
    					//$res2 = mysql_query("SHOW INDEX FROM ".$mySQLprefix."news");
    					$res2 = $sql->db_Select_gen("SHOW INDEX FROM ".MPREFIX.$ml_table);
    					while ($row = $sql->db_Fetch()){
    						//echo $row[4]." - ".$tab_sqltmp;
                $sql->db_Update($ml_fullname,$row[4]."='".$tab_sql_arr."' WHERE ".$row[4]."='".$tab_sqltmp."'");
    					}
    				}
    			}else{
    				$report_ml_error .= $ml_fullname." - ".$ml_arg." - ".$ml_debug."\n";
    			}
    	}
		}
		if($report_ml_error != ""){
			if($pref['e107ml_mailalert']==1){
          echo "<br />Error3: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following INSERT request(s) was/were required but was/were not successful:\n".$report_ml_error."\nPlease report bug to the e107 Dev team, on e107.org");
			}
      $ns -> tablerender("XX",$report_ml_error);
		}
		return $tab_sql_arr;
	}
	
	
	// Function to execute a UNIQUE SQL UPDATE with multilanguage system
	// Return same value as normal db_Update
	function e107_ml_Update($ml_table, $ml_arg, $ml_debug=FALSE, $lang_up=e_DBLANGUAGE){
		// $lang_up = language to update
		global $pref;
		$sql = new db;
		require_once(e_HANDLER."mail.php");
		$ml_fullname = strtolower($lang_up."_".$ml_table);
    if($lang_up!=e_LAN){
      if($tab_sql = $sql -> db_Update($ml_fullname, $ml_arg, $ml_debug)){
        return $tab_sql;
			}else if($tab_sql = $sql -> db_Update($ml_table, $ml_arg, $ml_debug)){
				if($pref['e107ml_mailalert']==1){
          echo "<br />Error4: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following UPDATE request was required but was not successful:\n".$ml_fullname." - ".$ml_arg." - ".$ml_debug.".\n\nPlease report bug to the e107 Dev team, on e107.org");
				}
        return $tab_sql;
			}
		}else if($tab_sql = $sql -> db_Update($ml_table, $ml_arg, $ml_debug)){
      return $tab_sql;
		}
		return FALSE;
	}
	
	// Function to execute a SQL MULTI UPDATE with multilanguage system
	// Return an array with all values of normal db_Update
	function e107_ml_MultiUpdate($ml_table, $ml_arg, $ml_debug=FALSE){
		global $ns, $pref;
		//$sql = new db;
		require_once(e_HANDLER."mail.php");
		global $list_e107lang,$sql,$tables_lang;
		// Default Insert
		$tab_sql_arr = "";
		$report_ml_error = "";
		$tab_sqltmp = $sql -> db_Update($ml_table, $ml_arg, $ml_debug);
		$tab_sql_arr = $tab_sqltmp;
		// Other tables
		for($i=0;$i<e_MLANGNBR;$i++){
			//echo "<br />->".$tables_lang[$list_e107lang[$i]][2];
      if(in_array($ml_table,$tables_lang[$list_e107lang[$i]])){
          if($ml_debug==TRUE){echo "Nbr lang: ".e_MLANGNBR."<br />Lang".$i." ".$list_e107lang[$i];}
    			$ml_fullname = strtolower($list_e107lang[$i]."_".$ml_table);
          if($tab_sqltmp = $sql -> db_Update($ml_fullname, $ml_arg, $ml_debug)){
            //
    			}else{
    				$report_ml_error .= $ml_fullname." - ".$ml_arg." - ".$ml_debug."\n";
    			}
    	}
		}
		if($report_ml_error != ""){
			if($pref['e107ml_mailalert']==1){
          echo "<br />Error5: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following INSERT request(s) was/were required but was/were not successful:\n".$report_ml_error."\nPlease report bug to the e107 Dev team, on e107.org");
			}
      $ns -> tablerender("XX",$report_ml_error);
		}
		return $tab_sql_arr;

	}
	
	
	// Function to execute a SQL DELETE with multilanguage system
	// Return same value as normal db_Delete
	function e107_ml_Delete($ml_table, $ml_arg=""){
		global $pref;
    $sql = new db;
		require_once(e_HANDLER."mail.php");
		$ml_fullname = strtolower(e_DBLANGUAGE."_".$ml_table);
		if(e_DBLANGUAGE!=e_LAN){
			if($tab_sql = $sql -> db_Delete($ml_fullname, $ml_arg)){
				return $tab_sql;
			}else{
				if($pref['e107ml_mailalert']==1){
          echo "<br />Error6: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following DELETE request was required but was not successful:\n".$ml_fullname." - ".$ml_arg.".\n\nPlease report bug to the e107 Dev team, on e107.org");
				}
        return FALSE;
			}
		}else if($tab_sql = $sql -> db_Delete($ml_table, $ml_arg)){
			return $tab_sql;
		}
		return FALSE;
	}
	
	// Function to execute a SQL MULTI DELETE with multilanguage system
	// Return an array with all values of normal db_Delete
	function e107_ml_MultiDelete($ml_table, $ml_arg){
		global $ns, $pref;
		//$sql = new db;
		require_once(e_HANDLER."mail.php");
		global $list_e107lang,$sql,$tables_lang;
		// Default Insert
		$tab_sql_arr = "";
		$report_ml_error = "";
		$tab_sqltmp = $sql -> db_Delete($ml_table, $ml_arg);
		$tab_sql_arr = $tab_sqltmp;
		// Other tables
		for($i=0;$i<e_MLANGNBR;$i++){
			//echo "<br />->".$tables_lang[$list_e107lang[$i]][2];
      if(in_array($ml_table,$tables_lang[$list_e107lang[$i]])){
          if($ml_debug==TRUE){echo "Nbr lang: ".e_MLANGNBR."<br />Lang".$i." ".$list_e107lang[$i];}
    			$ml_fullname = strtolower($list_e107lang[$i]."_".$ml_table);
          if($tab_sqltmp = $sql -> db_Delete($ml_fullname, $ml_arg, $ml_debug)){
            //
    			}else{
    				$report_ml_error .= $ml_fullname." - ".$ml_arg." - ".$ml_debug."\n";
    			}
    	}
		}
		if($report_ml_error != ""){
			if($pref['e107ml_mailalert']==1){
          echo "<br />Error5: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following INSERT request(s) was/were required but was/were not successful:\n".$report_ml_error."\nPlease report bug to the e107 Dev team, on e107.org");
			}
      $ns -> tablerender("XX",$report_ml_error);
		}
		return $tab_sql_arr;
	}
	
	// Function to execute a SQL COUNT with multilanguage system
	// Return same value as normal db_Count
	function e107_ml_Count($ml_table, $ml_fields="(*)", $ml_arg=""){
		global $pref;
    $sql = new db;
		require_once(e_HANDLER."mail.php");
		$ml_fullname = strtolower(e_DBLANGUAGE."_".$ml_table);
		if(e_DBLANGUAGE!=e_LAN){
			if($tab_sql = $sql -> db_Count($ml_fullname, $ml_fields, $ml_arg)){
				return $tab_sql;
			}else if($tab_sql = $sql -> db_Count($ml_table, $ml_fields, $ml_arg)){
				if($pref['e107ml_mailalert']==1){
          echo "<br />Error8: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following COUNT request was required but was not successful:\n".$ml_fullname." - ".$ml_fields." - ".$ml_arg.".\n\nPlease report bug to the e107 Dev team, on e107.org");
				}
        return $tab_sql;
			}
		}else if($tab_sql = $sql -> db_Count($ml_table, $ml_fields, $ml_arg)){
			return $tab_sql;
		}
		return FALSE;
	}
	
	
	// Function to execute a SQL SELECT_GEN with multilanguage system
	// Return same value as normal db_Select_gen
	function e107_ml_Select_gen($ml_arg){
		global $pref;
    $sql = new db;
		require_once(e_HANDLER."mail.php");
		$ml_fullname = strtolower(e_DBLANGUAGE."_".$ml_table);
		if(e_DBLANGUAGE!=e_LAN){
			if($tab_sql = $sql -> db_Select_gen($ml_arg)){
				return $tab_sql;
			}else if($tab_sql = $sql -> db_Select_gen($ml_arg)){
				if($pref['e107ml_mailalert']==1){
          echo "<br />Error9: ".$ml_fullname." - ".$ml_fields." - ".$ml_arg." - ".$ml_mode." - ".$ml_debug." - ".e_SELF;
          sendemail(SITEADMINEMAIL,"Error with the multilanguage system on your site ".SITENAME."The following SELECT_GEN request was required but was not successful:\n".$ml_arg.".\n\nPlease report bug to the e107 Dev team, on e107.org");
				}
        return $tab_sql;
			}
		}else if($tab_sql = $sql -> db_Select_gen($ml_arg)){
			return $tab_sql;
		}
		return FALSE;
	}
	
	
	function e107_ml_Fetch($mode = "strip"){
		//echo "XX".$this->mySQLresult;
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
			$this->e107_ml_Error("db_Fetch");
			return $row;
		}else{
			$this->e107_ml_Error("db_Fetch");
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function e107_ml_Rows(){
		/*
		# Return affected rows
		#
		# - parameters		none
		# - return				affected rows, or error if (error reporting = on, error occured, boolean)
		# - scope					public
		*/
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		return $rows;
		$this->e107_ml_Error("db_Rows");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function e107_ml_Error($from){
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
	
}


?>
