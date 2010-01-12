<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/alt_auth_login_class.php,v $
 * $Revision: 1.13 $
 * $Date: 2010-01-12 13:11:48 $
 * $Author: secretr $
 */

define('AA_DEBUG',FALSE);
define('AA_DEBUG1',FALSE);

class alt_login
{
	protected $e107;

	public function __construct($method, &$username, &$userpass)
	{
		global $pref;
		$this->e107 = e107::getInstance();
		$newvals=array();
		define('AUTH_SUCCESS', -1);
		define('AUTH_NOUSER', 1);
		define('AUTH_BADPASSWORD', 2);
		define('AUTH_NOCONNECT', 3);

		if ($method == 'none')
		{
			return AUTH_NOCONNECT;
		}

		require_once(e_PLUGIN.'alt_auth/'.$method.'_auth.php');
		$_login = new auth_login;

		if(isset($_login->Available) && ($_login->Available === FALSE))
		{	// Relevant auth method not available (e.g. PHP extension not loaded)
			return AUTH_NOCONNECT;
		}

		$login_result = $_login -> login($username, $userpass, $newvals, FALSE);

		if($login_result === AUTH_SUCCESS )
		{
			require_once (e_HANDLER.'user_handler.php');
			require_once(e_HANDLER.'validator_class.php');

			if (MAGIC_QUOTES_GPC == FALSE)
			{
				$username = mysql_real_escape_string($username);
			}
			$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
			$username = substr($username, 0, varset($pref['loginname_maxlength'],30));

			$aa_sql = new db;
			$userMethods = new UserHandler;
			$db_vals = array('user_password' => $aa_sql->escape($userMethods->HashPassword($userpass,$username)));
			$xFields = array();					// Possible extended user fields
			
			// See if any of the fields need processing before save
			if (isset($_login->copyMethods) && count($_login->copyMethods))
			{
				foreach ($newvals as $k => $v)
				{
					if (isset($_login->copyMethods[$k]))
					{
						$newvals[$k] = $this->translate($_login->copyMethods[$k], $v);
						if (AA_DEBUG1) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth convert",$k.': '.$v.'=>'.$newvals[$k],FALSE,LOG_TO_ROLLING);
					}
				}
			}
			foreach ($newvals as $k => $v)
			{
				if (strpos($k,'x_') === 0)
				{	// Extended field
					$k = substr($k,2);
					$xFields['user_'.$k] = $v;
				}
				else
				{	// Normal user table
					if (strpos($k,'user_' !== 0)) $k = 'user_'.$k;			// translate the field names (but latest handlers don't need translation)
					$db_vals[$k] = $v;
				}
			}
			if (count($xFields))
			{	// We're going to have to do something with extended fields as well - make sure there's an object
				require_once (e_HANDLER.'user_extended_class.php');
				$ue = new e107_user_extended;
				$qry = "SELECT u.user_id,u.".implode(',u.',array_keys($db_vals)).", ue.user_extended_id, ue.".implode(',ue.',array_keys($xFields))." FROM `#user` AS u
						LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id
						WHERE u.user_loginname='{$username}' ";
				if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","Query: {$qry}[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
			}
			else
			{
				$qry = "SELECT * FROM `#user` WHERE `user_loginname`='{$username}'";
			}
			if($aa_sql -> db_Select_gen($qry))
			{ // Existing user - get current data, see if any changes
				$row = $aa_sql->db_Fetch(MYSQL_ASSOC);
				foreach ($db_vals as $k => $v)
				{
					if ($row[$k] == $v) unset($db_vals[$k]);
				}
				if (count($db_vals)) 
				{
					$newUser = array();
					$newUser['data'] = $db_vals;
					validatorClass::addFieldTypes($userMethods->userVettingInfo,$newUser);
					$newUser['WHERE'] = '`user_id`='.$row['user_id'];
					$aa_sql->db_Update('user',$newUser);
					if (AA_DEBUG1) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","User data update: ".print_r($newUser,TRUE),FALSE,LOG_TO_ROLLING);
				}
				foreach ($xFields as $k => $v)
				{
					if ($row[$k] == $v) unset($xFields[$k]);
				}
				if (AA_DEBUG1) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","User data read: ".print_r($row,TRUE)."[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
				if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","User xtnd read: ".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
				if (count($xFields))
				{
					$xArray = array();
					$xArray['data'] = $xFields;
					if ($row['user_extended_id'])
					{
						$ue->addFieldTypes($xArray);		// Add in the data types for storage
						$xArray['WHERE'] = '`user_extended_id`='.intval($row['user_id']);
						if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","User xtnd update: ".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
						$aa_sql->db_Update('user_extended',$xArray );
					}
					else
					{	// Never been an extended user fields record for this user
						$xArray['data']['user_extended_id'] = $row['user_id'];
						$ue->addDefaultFields($xArray);		// Add in the data types for storage, plus any default values
						if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","Write new extended record".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
						$aa_sql->db_Insert('user_extended',$xArray);
					}
				}
			}
			else
			{  // Just add a new user
				if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","Add new user: ".print_r($db_vals,TRUE)."[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
				if (!isset($db_vals['user_name'])) $db_vals['user_name'] = $username;
				if (!isset($db_vals['user_loginname'])) $db_vals['user_loginname'] = $username;
				if (!isset($db_vals['user_join'])) $db_vals['user_join'] = time();
				$db_vals['user_class'] = varset($pref['initial_user_classes'],'');
				if (!isset($db_vals['user_signature'])) $db_vals['user_signature'] = '';
				if (!isset($db_vals['user_prefs'])) $db_vals['user_prefs'] = '';
				if (!isset($db_vals['user_perms'])) $db_vals['user_perms'] = '';
				$userMethods->userClassUpdate($db_vals, 'userall');
				$newUser = array();
				$newUser['data'] = $db_vals;
				$userMethods->addNonDefaulted($newUser);
				validatorClass::addFieldTypes($userMethods->userVettingInfo,$newUser);
				$newID = $aa_sql->db_Insert('user',$newUser);
				if ($newID !== FALSE)
				{
					if (count($xFields))
					{
						$xFields['user_extended_id'] = $newID;
						$xArray = array();
						$xArray['data'] = $xFields;
						$ue->addDefaultFields($xArray);		// Add in the data types for storage, plus any default values
						$result = $aa_sql->db_Insert('user_extended',$xArray);
						if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),'DEBUG','Alt auth login',"Add extended: UID={$newID}  result={$result}",FALSE,LOG_TO_ROLLING);
					}
				}
				else
				{	// Error adding user to database - possibly a conflict on unique fields
					$this->e107->admin_log->e_log_event(10,__FILE__.'|'.__FUNCTION__.'@'.__LINE__,'ALT_AUTH','Alt auth login','Add user fail: DB Error '.$aa_sql->mySQLlastErrText."[!br!]".print_r($db_vals,TRUE),FALSE,LOG_TO_ROLLING);
					return LOGIN_DB_ERROR;
				}
			}
			return LOGIN_CONTINUE;
		}
		else
		{	// Failure modes
			switch($login_result)
			{
/*
				case AUTH_NOUSER:			// Now handled differently
					if(!varset($pref['auth_nouser'],0))
					{
						$username=md5('xx_nouser_xx');
						return LOGIN_ABORT;
					}
					break;
*/
				case AUTH_NOCONNECT:
					if(varset($pref['auth_noconn'], TRUE))
					{
						return LOGIN_TRY_OTHER;
					}
					$username=md5('xx_noconn_xx');
					return LOGIN_ABORT;
					break;
				case AUTH_BADPASSWORD:
					if(varset($pref['auth_badpassword'], TRUE))
					{
						return LOGIN_TRY_OTHER;
					}
					$userpass=md5('xx_badpassword_xx');
					return LOGIN_ABORT;					// Not going to magically be able to log in!
					break;
			}
		}
		return LOGIN_ABORT;			// catch-all just in case
	}


	// Function to implement copy methods
	public function translate($method, $word)
	{
		global $tp;
		switch ($method)
		{
			case 'bool1' :
				switch ($tp->ustrtoupper($word))
				{
					case 'TRUE' : return TRUE;
					case 'FALSE' : return FALSE;
				}
				return $word;
			case 'ucase' :
				return $tp->ustrtoupper($word);
			case 'lcase' :
				return $tp->ustrtolower($word);
			case 'ucfirst' :
				return ucfirst($word);						// TODO: Needs changing to utf-8 function
			case 'ucwords' :
				return ucwords($word);						// TODO: Needs changing to utf-8 function
			case 'none' :
				return $word;
		}
	}

}
?>