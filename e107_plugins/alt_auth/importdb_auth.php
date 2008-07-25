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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/importdb_auth.php,v $
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
	
	function auth_login()
	{
	  global $importdb_conf, $sql;
	  if (!$sql -> db_Select("alt_auth", "*", "auth_type = 'importdb' ")) return AUTH_NOCONNECT;	// We should get at least one value
	  while ($row = $sql -> db_Fetch())
	  {
		$importdb_conf[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
	  }
	  $this->Available = TRUE;
	  $this->od = new importdb_mysql_class;
	}


	function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
//		global $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $sql;
		$ret = $this->od->login($uname, $pword, $newvals, $connect_only);
//		$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
		return $ret;
	}

}

class importdb_mysql_class
{
	
	var $conf;
	
	function importdb_mysql_class()
	{
	  global $importdb_conf;
	  $this->conf = $importdb_conf;
	}
	
	function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
	  if ($connect_only) return AUTH_SUCCESS;			// Big problem if can't connect to our own DB!

	  // See if the user's in the E107 database - otherwise they can go away
	  global $sql, $tp;
	  if (!$sql->db_Select("user", "user_loginname, user_password", "user_loginname = '".$tp -> toDB($uname)."'")) 
	  {	// Invalid user
		return AUTH_NOUSER;
	  }

	  // Now look at their password - we always need to verify it, even if its a core E107 format.
	  // Higher levels will always convert an authorised password to E107 format and save it for us.
	  if (!$row = $sql->db_Fetch())
	  {
	    return AUTH_NOCONNECT;			// Debateable return code - really a DB error. But consistent with other handler
	  }

	  require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');		// This auto-loads the 'standard' password handler as well
	  $pass_check = new ExtendedPasswordHandler();

	  $passMethod = $pass_check->passwordMapping($this->conf['importdb_password_method']);
	  if ($passMethod === FALSE) return AUTH_BADPASSWORD;

	  $pwFromDB = $row['user_password'];					// Password stored in DB
	  if ($pass_check->checkPassword($pword, $uname, $pwFromDB, $passMethod) !== PASSWORD_VALID)
	  {
		return AUTH_BADPASSWORD;
	  }
	  return AUTH_SUCCESS;
	}
}

?>