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
|     $Revision: 1.4 $
|     $Date: 2009-10-27 20:46:27 $
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

	var $Available;
	var $ErrorText;
	var	$conf;				// Configuration parameters

	
	function auth_login()
	{
		global $sql;
		$this->conf = array();
		$this->ErrorText = '';
		$sql -> db_Select("alt_auth", "*", "auth_type = 'e107db' ");
		while($row = $sql -> db_Fetch())
		{
			$this->conf[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		}
		$this->Available = TRUE;
	}


	// Add the reconnect function in here - might be needed
	function makeErrorText($extra = '')
	{
		$this->ErrorText = $extra;
		global $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $sql;
		$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
	}


	function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
	  //Attempt to open connection to sql database
	  if(!$res = mysql_connect($this->conf['e107db_server'], $this->conf['e107db_username'], $this->conf['e107db_password']))
	  {
		$this->makeErrorText('Cannot connect to remote server');
		return AUTH_NOCONNECT;
	  }
	  //Select correct db
	  if(!mysql_select_db($this->conf['e107db_database'], $res))
	  {
		mysql_close($res);
		$this->makeErrorText('Cannot connect to remote DB');
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

		$filterClass = intval(varset($this->conf['e107db_filter_class'], e_UC_PUBLIC));
		if (($filterClass != e_UC_PUBLIC) && (!in_array('user_class',$sel_fields)))
		{
			$sel_fields[] = 'user_class';
		}

		$sel_fields[] = 'user_password';
		$user_field = 'user_loginname';


		//Get record containing supplied login name
		$qry = "SELECT ".implode(',',$sel_fields)." FROM ".$this->conf['e107db_prefix']."user WHERE {$user_field} = '{$uname}' AND `user_ban` = 0";
//	  echo "Query: {$qry}<br />";
		if(!$r1 = mysql_query($qry))
		{
			mysql_close($res);
			$this->makeErrorText('Lookup query failed');
			return AUTH_NOCONNECT;
		}
		if(!$row = mysql_fetch_array($r1))
		{
			mysql_close($res);
			$this->makeErrorText('User not found');
			return AUTH_NOUSER;
		}

		mysql_close($res);				// Finished with 'foreign' DB now

		// Got something from the DB - see whether password valid
		require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');		// This auto-loads the 'standard' password handler as well
		$pass_check = new ExtendedPasswordHandler();

		$passMethod = $pass_check->passwordMapping($this->conf['e107db_password_method']);
		if ($passMethod === FALSE) 
		{
			$this->makeErrorText('Password error - invalid method');
			return AUTH_BADPASSWORD;
		}

		$pwFromDB = $row['user_password'];					// Password stored in DB

		if ($pass_check->checkPassword($pword, $uname, $pwFromDB, $passMethod) !== PASSWORD_VALID)
		{
			$this->makeErrorText('Password incorrect');
			return AUTH_BADPASSWORD;
		}

		// Valid user - check he's in an appropriate class
		if ($filterClass != e_UC_PUBLIC)
		{
			$tmp = explode(',', $row['user_class']);
			if (!in_array($filterClass, $tmp))
			{
				$this->makeErrorText('Userc not found');
				return AUTH_NOUSER;			// Treat as non-existent user
			}
			unset($tmp);
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
		$this->makeErrorText('');		// Success - just reconnect to E107 DB if needed
	  return AUTH_SUCCESS;
	}
}

?>