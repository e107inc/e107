<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/e107db_auth.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-07-25 19:33:03 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

/* 
	return values
	AUTH_NOCONNECT 		= unable to connect to db
	AUTH_NOUSER			= user not found	
	AUTH_BADPASSWORD	= supplied password incorrect

	AUTH_SUCCESS 		= valid login
*/

class auth_login
{

	var $od;
	var $Available;
	
	function auth_login()
	{
//		global $otherdb_conf, $sql;
		global $sql;
		$sql -> db_Select("alt_auth", "*", "auth_type = 'e107db' ");
		while($row = $sql -> db_Fetch())
		{
			$e107db_conf[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		}
		$class_name = "e107db_mysql_class";

		if(class_exists($class_name))
		{
		  $this->od = new $class_name($e107db_conf);
		  $this->Available = TRUE;
		}
		else
		{
		  $this->Available = FALSE;
		  return AUTH_NOCONNECT;
		}
	}

	function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
		global $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $sql;
		$ret = $this->od->login($uname, $pword, $newvals, $connect_only);
		$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
		return $ret;
	}

}

class e107db_mysql_class
{
	
	var $conf;
	
	function e107db_mysql_class($otherdb_conf)
	{
		$this->conf = $otherdb_conf;
//		print_a($this->conf);
	}
	


	function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
	  //Attempt to open connection to sql database
	  if(!$res = mysql_connect($this->conf['e107db_server'], $this->conf['e107db_username'], $this->conf['e107db_password']))
	  {
		return AUTH_NOCONNECT;
	  }
	  //Select correct db
	  if(!mysql_select_db($this->conf['e107db_database'], $res))
	  {
		mysql_close($res);
		return AUTH_NOCONNECT;
	  }
	  if ($connect_only) return AUTH_SUCCESS;		// Test mode may just want to connect to the DB
	  
	  $sel_fields = array();
	  // Make an array of the fields we want from the source DB
	  foreach($this->conf as $k => $v)
	  {
	    if ($v && (strpos($k,'e107db_xf_') === 0))
		{
		  $sel_fields[] = substr($k,strlen('e107db_xf_'));
		}
	  }
	  $sel_fields[] = 'user_password';
	  $user_field = 'user_loginname';


	  //Get record containing supplied login name
	  $qry = "SELECT ".implode(',',$sel_fields)." FROM ".MPREFIX."user WHERE {$user_field} = '{$uname}'";
//	  echo "Query: {$qry}<br />";
	  if(!$r1 = mysql_query($qry))
	  {
		mysql_close($res);
		return AUTH_NOCONNECT;
	  }
	  if(!$row = mysql_fetch_array($r1))
	  {
		mysql_close($res);
		return AUTH_NOUSER;
	  }
		
	  mysql_close($res);				// Finished with 'foreign' DB now

	  // Got something from the DB - see whether password valid
	  require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');		// This auto-loads the 'standard' password handler as well
	  $pass_check = new ExtendedPasswordHandler();

	  $passMethod = $pass_check->passwordMapping($this->conf['e107db_password_method']);
	  if ($passMethod === FALSE) return AUTH_BADPASSWORD;

	  $pwFromDB = $row['user_password'];					// Password stored in DB

	  if ($pass_check->checkPassword($pword, $uname, $pwFromDB, $passMethod) !== PASSWORD_VALID)
	  {
		return AUTH_BADPASSWORD;
	  }

	  // Now copy across any values we have selected
	  foreach($this->conf as $k => $v)
	  {
	    if ($v && (strpos($k,'e107db_xf_') === 0))
		{
		  $f = substr($k,strlen('e107db_xf_'));
		  if (isset($row[$f])) $newvals[$f] = $row[$f];
		}
	  }
	  return AUTH_SUCCESS;
	}
}

?>