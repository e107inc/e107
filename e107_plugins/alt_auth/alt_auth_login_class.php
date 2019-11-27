<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Alternate login
 *
 * $URL$
 * $Id$
 * 
 */

/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */

define('AA_DEBUG',FALSE);
define('AA_DEBUG1',FALSE);


//TODO convert to class constants (but may be more useful as globals, perhaps within a general login manager scheme)
define('AUTH_SUCCESS', -1);
define('AUTH_NOUSER', 1);
define('AUTH_BADPASSWORD', 2);
define('AUTH_NOCONNECT', 3);
define('AUTH_UNKNOWN', 4);
define('AUTH_NOT_AVAILABLE', 5);
define('AUTH_NORESOURCE', 6);		// Used to indicate, for example, that a required PHP module isn't loaded


/**
 *	Methods used by a number of alt_auth classes.
 *	The login authorisation classes are descendants of this one.
 *	Admin functions also use it - a little extra overhead by including this file, but less of a problem for admin
 */
class alt_auth_base
{
	public function __construct()
	{
	}


	/**
	 *	Get configuration parameters for an authentication method
	 *
	 *	@param string $prefix - the method
	 *
	 *	@return array
	 */
	public function altAuthGetParams($prefix)
	{
		$sql = e107::getDb();

		$sql->db_Select('alt_auth', '*', "auth_type = '".$prefix."' ");
		$parm = array();
		while($row = $sql->db_Fetch())
		{
			$parm[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		}
		return $parm;
	}
}


class alt_login
{
	protected $e107;
	public $loginResult = false;

	public function __construct($method, &$username, &$userpass)
	{
		$this->e107 = e107::getInstance();
		$newvals=array();

		if ($method == 'none')
		{
			$this->loginResult = AUTH_NOCONNECT;
			return;
		}

		require_once(e_PLUGIN.'alt_auth/'.$method.'_auth.php');
		$_login = new auth_login;

		if(isset($_login->Available) && ($_login->Available === FALSE))
		{	// Relevant auth method not available (e.g. PHP extension not loaded)
			$this->loginResult = AUTH_NOT_AVAILABLE;
			return;
		}

		$login_result = $_login->login($username, $userpass, $newvals, FALSE);

		if($login_result === AUTH_SUCCESS )
		{
			require_once (e_HANDLER.'user_handler.php');
			require_once(e_HANDLER.'validator_class.php');

			if (MAGIC_QUOTES_GPC == FALSE)
			{
				$username = mysql_real_escape_string($username);
			}
			$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
			$username = substr($username, 0, e107::getPref('loginname_maxlength'));

			$aa_sql = e107::getDb('aa');
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
			$ulogin = new userlogin();
			if (count($xFields))
			{	// We're going to have to do something with extended fields as well - make sure there's an object
				require_once (e_HANDLER.'user_extended_class.php');
				$ue = new e107_user_extended;
				$q = 
				$qry = "SELECT u.user_id,u.".implode(',u.',array_keys($db_vals)).", ue.user_extended_id, ue.".implode(',ue.',array_keys($xFields))." FROM `#user` AS u
						LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id
						WHERE ".$ulogin->getLookupQuery($username, FALSE, 'u.');
				if (AA_DEBUG) $this->e107->admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Alt auth login","Query: {$qry}[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
			}
			else
			{
				$qry = "SELECT * FROM `#user` WHERE ".$ulogin->getLookupQuery($username, FALSE);
			}
			if($aa_sql -> db_Select_gen($qry))
			{ // Existing user - get current data, see if any changes
				$row = $aa_sql->db_Fetch();
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
				$db_vals['user_class'] = e107::getPref('initial_user_classes');
				if (!isset($db_vals['user_signature'])) $db_vals['user_signature'] = '';
				if (!isset($db_vals['user_prefs'])) $db_vals['user_prefs'] = '';
				if (!isset($db_vals['user_perms'])) $db_vals['user_perms'] = '';
				$userMethods->userClassUpdate($db_vals, 'userall');
				$newUser = array();
				$newUser['data'] = $db_vals;
				$userMethods->addNonDefaulted($newUser);
				validatorClass::addFieldTypes($userMethods->userVettingInfo,$newUser);
				
				$newID = $aa_sql->insert('user',$newUser);
				
				if ($newID !== FALSE)
				{
					if (count($xFields))
					{
						$xFields['user_extended_id'] = $newID;
						$xArray = array();
						$xArray['data'] = $xFields;

						e107::getUserExt()->addDefaultFields($xArray);		// Add in the data types for storage, plus any default values
						$result = $aa_sql->insert('user_extended',$xArray);
						if (AA_DEBUG) e107::getLog()->e_log_event(10,debug_backtrace(),'DEBUG','Alt auth login',"Add extended: UID={$newID}  result={$result}",FALSE,LOG_TO_ROLLING);
					}
				}
				else
				{	// Error adding user to database - possibly a conflict on unique fields
					$this->e107->admin_log->e_log_event(10,__FILE__.'|'.__FUNCTION__.'@'.__LINE__,'ALT_AUTH','Alt auth login','Add user fail: DB Error '.$aa_sql->getLastErrorText()."[!br!]".print_r($db_vals,TRUE),FALSE,LOG_TO_ROLLING);
					$this->loginResult = LOGIN_DB_ERROR;
					return;
				}
			}
			$this->loginResult = LOGIN_CONTINUE;
			return;
		}
		else
		{	// Failure modes
			switch($login_result)
			{
				case AUTH_NOCONNECT:
					if(varset(e107::getPref('auth_noconn'), TRUE))
					{
						$this->loginResult = LOGIN_TRY_OTHER;
						return;
					}
					$username=md5('xx_noconn_xx');
					$this->loginResult = LOGIN_ABORT;
					return;
				case AUTH_BADPASSWORD:
					if(varset(e107::getPref('auth_badpassword'), TRUE))
					{
						$this->loginResult = LOGIN_TRY_OTHER;
						return;
					}
					$userpass=md5('xx_badpassword_xx');
					$this->loginResult = LOGIN_ABORT;					// Not going to magically be able to log in!
					return;
			}
		}
		$this->loginResult = LOGIN_ABORT;			// catch-all just in case
		return;
	}


	// Function to implement copy methods
	public function translate($method, $word)
	{
		$tp = e107::getParser();
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