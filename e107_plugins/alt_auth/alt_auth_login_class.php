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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/alt_auth_login_class.php,v $
|     $Revision: 1.4 $
|     $Date: 2008-12-01 21:47:17 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
class alt_login
{
	function alt_login($method, &$username, &$userpass)
	{
	  global $pref;
	  $newvals=array();
	  define("AUTH_SUCCESS", -1);
	  define("AUTH_NOUSER", 1);
	  define("AUTH_BADPASSWORD", 2);
	  define("AUTH_NOCONNECT", 3);
	  require_once(e_PLUGIN."alt_auth/".$method."_auth.php");
	  $_login = new auth_login;

	  if(isset($_login->Available) && ($_login->Available === FALSE))
	  {	// Relevant auth method not available (e.g. PHP extension not loaded)
		return AUTH_NOCONNECT;
	  }

	  $login_result = $_login -> login($username, $userpass, $newvals, FALSE);

	  if($login_result === AUTH_SUCCESS )
	  {
		if (MAGIC_QUOTES_GPC == FALSE)
		{
		  $username = mysql_real_escape_string($username);
		}
		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
		$username = substr($username, 0, varset($pref['loginname_maxlength'],30));

		$aa_sql = new db;
		$uh = new UserHandler;
		$db_vals = array('user_password' => $aa_sql->escape($uh->HashPassword($userpass,$username)));
		foreach ($newvals as $k => $v)
		{
		  if (strpos($k,'user_' !== 0)) $k = 'user_'.$k;			// translate the field names (but latest handlers don't need translation)
		  $db_vals[$k] = $v;
		}
		if($aa_sql -> db_Select("user","*","user_loginname='{$username}' "))
		{ // Existing user - get current data, see if any changes
		  $row = $aa_sql->db_Fetch();
		  foreach ($db_vals as $k => $v)
		  {
		    if ($row[$k] == $v) unset($db_vals[$k]);
		  }
		  if (count($db_vals)) $aa_sql->db_UpdateArray('user',$db_vals," WHERE `user_id`=".$row['user_id']);
		}
		else
		{  // Just add a new user
			if (!isset($db_vals['user_name'])) $db_vals['user_name'] = $username;
			if (!isset($db_vals['user_loginname'])) $db_vals['user_loginname'] = $username;
			if (!isset($db_vals['user_join'])) $db_vals['user_join'] = time();
			$db_vals['user_class'] = varset($pref['initial_user_classes'],'');
			if (!isset($db_vals['user_signature'])) $db_vals['user_signature'] = '';
			if (!isset($db_vals['user_prefs'])) $db_vals['user_prefs'] = '';
			if (!isset($db_vals['user_perms'])) $db_vals['user_perms'] = '';
			$aa_sql->db_Insert('user',$db_vals);
		}
		return LOGIN_CONTINUE;
	  }
	  else
	  {	// Failure modes
		switch($login_result)
		{
		  case AUTH_NOUSER:
			if(!varset($pref['auth_nouser'],0))
			{
			  $username=md5("xx_nouser_xx");
			  return LOGIN_ABORT;
			}
			break;
		  case AUTH_NOCONNECT:
			if(!varset($pref['auth_noconn']))
			{
			  $username=md5("xx_noconn_xx");
			  return LOGIN_ABORT;
			}
			break;
		  case AUTH_BADPASSWORD:
			$userpass=md5("xx_badpassword_xx");
			return LOGIN_ABORT;					// Not going to magically be able to log in!
			break;
		}
	  }
	  return LOGIN_ABORT;			// catch-all just in case
	}
}
?>