<?php

/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/login.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/


if (!defined('e107_INIT')) { exit; }

error_reporting(E_ALL);


// require_once(e_HANDLER.'user_handler.php'); //shouldn't be necessary
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_login.php');

define ('LOGIN_TRY_OTHER', 2);		// Try some other authentication method
define ('LOGIN_CONTINUE',1);		// Not rejected (which is not exactly the same as 'accepted') by alt_auth
define ('LOGIN_ABORT',-1);			// Rejected by alt_auth
define ('LOGIN_BAD_PW', -2);		// Password wrong
define ('LOGIN_BAD_USER', -3);		// User not in DB
define ('LOGIN_BAD_USERNAME', -4);	// Username format unacceptable (e.g. too long)
define ('LOGIN_BAD_CODE', -5);		// Wrong image code entered
define ('LOGIN_MULTIPLE', -6); 		// Error if multiple logins not allowed
define ('LOGIN_NOT_ACTIVATED', -7);	// User in DB, not activated
define ('LOGIN_BLANK_FIELD', -8);	// Username or password blank
define ('LOGIN_BAD_TRIGGER', -9);	// Rejected by trigger event
define ('LOGIN_BANNED', -10);		// Banned user attempting login
define ('LOGIN_CHAP_FAIL', -11);	// CHAP login failed
define ('LOGIN_DB_ERROR', -12);		// Error adding user to main DB


class userlogin
{
	protected $e107;
	protected $userMethods;			// Pointer to user handler
	protected $userIP;				// IP address
	protected $lookEmail = FALSE;	// Flag set if logged in using email address
	protected $userData = array();	// Information for current user
	protected $passResult = FALSE;	// USed to determine if stored password needs update


	/** Constructor
	# Class called when user attempts to log in
	#
	# @param string $username, $_POSTED user name
	# @param string $userpass, $_POSTED user password
	# @param $autologin - 'signup' - uses a specially encoded password - logs in if matches
	#					- zero for 'normal' login
	#					- non-zero sets the 'remember me' flag in the cookie
	' @param string $response - response string returned by CHAP login (instead of password)
	# @return  boolean - FALSE on login fail, TRUE on login successful
	*/
	public function __construct($username, $userpass, $autologin, $response = '')
	{
		global $pref, $e_event, $_E107;

		$username = trim($username);
		$userpass = trim($userpass);

		if($_E107['cli'] && ($username == ""))
		{
			return FALSE;
		}
		
		$tp = e107::getParser();
		$sql = e107::getDb();

		$this->e107 = e107::getInstance();
		$this->userIP = $this->e107->getip();

		if($username == "" || (($userpass == "") && ($response == '')))
		{	// Required fields blank
			return $this->invalidLogin($username,LOGIN_BLANK_FIELD);
		}

//	    $this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'IP: '.$fip,FALSE,LOG_TO_ROLLING);
		$this->e107->check_ban("banlist_ip='{$this->userIP}' ",FALSE);			// This will exit if a ban is in force

		$forceLogin = ($autologin == 'signup');
		$autologin = intval($autologin);		// Will decode to zero if forced login

		if (!$forceLogin && $this->e107->isInstalled('alt_auth'))
		{
			$authMethod[0] = varset($pref['auth_method'], 'e107');		// Primary authentication method
			$authMethod[1] = varset($pref['auth_method2'], 'none');		// Secondary authentication method (if defined)
			foreach ($authMethod as $method)
			{
				if ($method == 'e107')
				{
					if ($this->lookupUser($username, $forceLogin))
					{
						if (varset($pref['auth_badpassword'], TRUE) || ($this->checkUserPassword($userpass, $response, $forceLogin) === TRUE))
						{
							$result = LOGIN_CONTINUE;		// Valid User exists in local DB 
						}
					}
				}
				else
				{
					if ($method != 'none')
					{
						$auth_file = e_PLUGIN."alt_auth/".$method."_auth.php";
						if (file_exists($auth_file))
						{
							require_once(e_PLUGIN.'alt_auth/alt_auth_login_class.php');
							$result = new alt_login($method, $username, $userpass);
							switch ($result)
							{
								case LOGIN_ABORT :
									return $this->invalidLogin($username,LOGIN_ABORT);
								case LOGIN_DB_ERROR :
									return $this->invalidLogin($username,LOGIN_DB_ERROR);
							}
						}
					}
				}
				if ($result == LOGIN_CONTINUE)
				{
					break;
				}
			}
		}

		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);

		// Check secure image
		if (!$forceLogin && $pref['logcode'] && extension_loaded("gd"))
		{
			require_once(e_HANDLER."secure_img_handler.php");
			$sec_img = new secure_image;
			if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify']))
			{	// Invalid code
				return $this->invalidLogin($username,LOGIN_BAD_CODE);
			}
		}

		if (empty($this->userData))		// May have retrieved user data earlier
		{
			if (!$this->lookupUser($username, $forceLogin))
			{
				return $this->invalidLogin($username,LOGIN_BAD_USERNAME);		// User doesn't exist
			}
		}


		if ($this->checkUserPassword($userpass, $response, $forceLogin) !== TRUE)
		{
			return FALSE;
		}


		// Check user status
		switch ($this->userData['user_ban'])
		{
			case USER_REGISTERED_NOT_VALIDATED : // User not fully signed up - hasn't activated account.
				return $this->invalidLogin($username,LOGIN_NOT_ACTIVATED);
			case USER_BANNED :		// User banned
				return $this->invalidLogin($username,LOGIN_BANNED,$this->userData['user_id']);
			case USER_VALIDATED :		// Valid user
				break;			// Nothing to do ATM
			default :			// May want to pick this up
		}


		// User is OK as far as core is concerned
//	    $this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'User passed basics',FALSE,LOG_TO_ROLLING);
		if (($this->passResult !== FALSE) && ($this->passResult !== PASSWORD_VALID))
		{  // May want to rewrite password using salted hash (or whatever the preferred method is) - $pass_result has the value to write
			// If login by email address also allowed, will have to write that value too
//		  	$this->e107->sql->db_Update('user',"`user_password` = '{$pass_result}' WHERE `user_id`=".intval($this->userData['user_id']));
		}


		$userpass = '';				// Finished with any plaintext password - can get rid of it


		$ret = $e_event->trigger("preuserlogin", $username);
		if ($ret != '')
		{
			return $this->invalidLogin($username,LOGIN_BAD_TRIGGER,$ret);
		}


		// Trigger events happy as well
		$user_id = $this->userData['user_id'];
		$user_name = $this->userData['user_name'];
		$user_xup = $this->userData['user_xup'];

		/* restrict more than one person logging in using same us/pw */
		if($pref['disallowMultiLogin'])
		{
			if($this->e107->sql -> db_Select("online", "online_ip", "online_user_id='".$user_id.".".$user_name."'"))
			{
				return $this->invalidLogin($username,LOGIN_MULTIPLE,$user_id);
			}
		}


		// User login definitely accepted here
		if($user_xup)
		{
			$this->update_xup($user_id, $user_xup);
		}


		$cookieval = $this->userMethods->makeUserCookie($this->userData,$autologin);


		// Calculate class membership - needed for a couple of things
		// Problem is that USERCLASS_LIST just contains 'guest' and 'everyone' at this point
		$class_list = $this->userMethods->addCommonClasses($this->userData, TRUE);

		$user_logging_opts = array_flip(explode(',',varset($pref['user_audit_opts'],'')));
		if (isset($user_logging_opts[USER_AUDIT_LOGIN]) && in_array(varset($pref['user_audit_class'],''),$class_list))
		{  // Need to note in user audit trail
			$this->e107->admin_log->user_audit(USER_AUDIT_LOGIN,'', $user_id,$user_name);
		}

		$edata_li = array('user_id' => $user_id, 'user_name' => $username, 'class_list' => implode(',',$class_list), 'remember_me' => $autologin);
		$e_event->trigger("login", $edata_li);

		if($_E107['cli'])
		{
			return $cookieval;
		}

		if (in_array(e_UC_NEWUSER,$class_list))
		{
			if (time() > ($this->userData['user_join'] + (varset($pref['user_new_period'],0)*86400)))
			{	// 'New user' probationary period expired - we can take them out of the class
				$this->userData['user_class'] = $this->e107->user_class->ucRemove(e_UC_NEWUSER, $this->userData['user_class']);
//				$this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login new user complete",$this->userData['user_class'],FALSE,FALSE);
				$this->e107->sql->db_Update('user',"`user_class` = '".$this->userData['user_class']."'", 'WHERE `user_id`='.$this->userData['user_id']);
				unset($class_list[e_UC_NEWUSER]);
				$edata_li = array('user_id' => $user_id, 'user_name' => $username, 'class_list' => implode(',',$class_list));
				$e_event->trigger('userNotNew', $edata_li);
			}
		}

		$redir = e_SELF;
		if (e_QUERY) $redir .= '?'.str_replace('&amp;','&',e_QUERY);
		if (isset($pref['frontpage_force']) && is_array($pref['frontpage_force']))
		{	// See if we're to force a page immediately following login - assumes $pref['frontpage_force'] is an ordered list of rules
//		  $log_info = "New user: ".$this->userData['user_name']."  Class: ".$this->userData['user_class']."  Admin: ".$this->userData['user_admin']."  Perms: ".$this->userData['user_perms'];
//		  $this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login Start",$log_info,FALSE,FALSE);
			foreach ($pref['frontpage_force'] as $fk=>$fp)
			{
				if (in_array($fk,$class_list))
				{  // We've found the entry of interest
					if (strlen($fp))
					{
						$redir = ((strpos($fp, 'http') === FALSE) ? e_BASE : '').$this->e107->tp->replaceConstants($fp, TRUE, FALSE);
		//				$this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Redirect active",$redir,FALSE,FALSE);
					}
					break;
				}
			}
		}
		
		$redirPrev = e107::getRedirect()->getPreviousUrl();  
		
		if($redirPrev)
		{		
			e107::getRedirect()->redirect($redirPrev);	
		}

		e107::getRedirect()->redirect($redir);	
		exit();
	}


	/**
	 * Look up a user in the e107 database, according to the options set (for login name/email address)
	 * Note: PASSWORD IS NOT VERIFIED BY THIS ROUTINE
	 * @param string $username - as entered
	 * @param boolean $forceLogin - TRUE if login is being forced from clicking signup link; normally FALSE
	 * @return TRUE if name exists, and $this->userData array set up
	 *		   otherwise FALSE
	 */
	protected function lookupUser($username, $forceLogin)
	{
		global $pref;

		// Check username general format
		if (!$forceLogin && (strlen($username) > varset($pref['loginname_maxlength'],30)))
		{  // Error - invalid username
			$this->invalidLogin($username,LOGIN_BAD_USERNAME);
			return FALSE;
		}

		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);

        $qry[0] = "`user_loginname`= '".$this->e107->tp->toDB($username)."'";  // username only  (default)
		$qry[1] = "`user_email` = '".$this->e107->tp->toDB($username)."'";   // email only
		$qry[2] = (strpos($username,'@') !== FALSE ) ? "`user_loginname`= '".$this->e107->tp->toDB($username)."'  OR `user_email` = '".$this->e107->tp -> toDB($username)."'" : $qry[0];  //username or email

		// Look up user in DB - even if email addresses allowed, still look up by user name as well - user could have specified email address for their login name
        $query = (!$forceLogin && varset($pref['allowEmailLogin'],0)) ? $qry[$pref['allowEmailLogin']] : $qry[0];

		if ($this->e107->sql->db_Select('user', '*', $query) !== 1) 	// Handle duplicate emails as well
		{	// Invalid user
			return $this->invalidLogin($username,LOGIN_BAD_USER);
		}
	
		// User is in DB here
		$this->userData = $this->e107->sql -> db_Fetch(MYSQL_ASSOC);		// Get user info
		$this->userData['user_perms'] = trim($this->userData['user_perms']);
		$this->lookEmail = $this->lookEmail && ($username == $this->userData['user_email']);		// Know whether login name or email address used now
		return TRUE;
	}


	/**
	 * Checks user password againt preferences set etc
	 * Assumes that $this->userData array already set up
	 * @param string $userpass - as entered
	 * @param string $response - received string if CHAP used
	 * @param boolean $forceLogin - TRUE if login is being forced from clicking signup link; normally FALSE
	 * @return TRUE if valid password
	 *		   otherwise FALSE
	 */
	protected function checkUserPassword($userpass, $response, $forceLogin)
	{
		global $pref;
		if ($this->lookEmail && varsettrue($pref['passwordEncoding']))
		{
			$tmp = unserialize($this->userData['user_prefs']);
			$requiredPassword = varset($tmp['email_password'],$this->userData['user_password']);	// Use email-specific password if set. Otherwise, 'normal' one might work
			unset($tmp);
		}
		else
		{
			$requiredPassword = $this->userData['user_password'];
		}

		// Now check password
		$this->userMethods = e107::getSession();
		if ($forceLogin)
		{
			if (md5($this->userData['user_name'].$this->userData['user_password'].$this->userData['user_join']) != $userpass)
			{
				return $this->invalidLogin($username,LOGIN_BAD_PW);
			}
		}
		else
		{
			if ((($pref['password_CHAP'] > 0) && ($response && isset($_SESSION['challenge'])) && ($response != $_SESSION['challenge'])) || ($pref['password_CHAP'] == 2))
			{  // Verify using CHAP
	//		  	$this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","CHAP login","U: {$username}, P: {$userpass}, C: {$_SESSION['challenge']} R:{$response} S: {$this->userData['user_password']}",FALSE,LOG_TO_ROLLING);
				if (($pass_result = $this->userMethods->CheckCHAP($_SESSION['challenge'], $response, $username, $requiredPassword)) === PASSWORD_INVALID)
				{
					return $this->invalidLogin($username,LOGIN_CHAP_FAIL);
				}
			}
			else
			{	// Plaintext password
	//		  	$this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Plaintext login","U: {$username}, P: {$userpass}, C: {$_SESSION['challenge']} R:{$response} S: {$this->userData['user_password']}",FALSE,LOG_TO_ROLLING);
				if (($pass_result = $this->userMethods->CheckPassword($userpass,($this->lookEmail ? $this->userData['user_loginname'] : $username),$requiredPassword)) === PASSWORD_INVALID)
				{
					return $this->invalidLogin($username,LOGIN_BAD_PW);
				}
			}
			$this->passResult = $pass_result;
		}
		return TRUE;
	}



	/**
	 * called to log the reason for a failed login.
	 * @param string $plugname
	 * @return boolean Currently always returns false - could return some other value
	 */
	protected function invalidLogin($username, $reason, $extra_text = '')
	{
		global $pref;

		$doCheck = FALSE;			// Flag set if need to ban check
		switch ($reason)
		{
			case LOGIN_ABORT :		// alt_auth reject
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->genNote($this->userIP,$username, 'Alt_auth: '.LAN_LOGIN_14);
			  $this->logNote('LAN_ROLL_LOG_04', 'Alt_Auth: '.$username);
			  $doCheck = TRUE;
			  break;
			case LOGIN_DB_ERROR :	// alt_auth couldn't add valid user
				define("LOGINMESSAGE", LAN_LOGIN_31."<br /><br />");
				$this->genNote($username, 'Alt_auth: '.LAN_LOGIN_30);
//				$this->logNote('LAN_ROLL_LOG_04', 'Alt_Auth: '.$username);	// Added in alt_auth login
				$doCheck = TRUE;
				break;
			case LOGIN_BAD_PW :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_03', $username);
			  break;
			case LOGIN_CHAP_FAIL :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_03', 'CHAP: '.$username);
			  break;
			case LOGIN_BAD_USER :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->genNote($username, LAN_LOGIN_14);
			  $this->logNote('LAN_ROLL_LOG_04', $username);
			  $doCheck = TRUE;
			  break;
			case LOGIN_BAD_USERNAME :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_08', $username);
			  break;
			case LOGIN_MULTIPLE :
			  define("LOGINMESSAGE", LAN_LOGIN_24."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_07', "U: {$username} IP: {$this->userIP}");
			  $this->genNote($username, LAN_LOGIN_16);
			  $doCheck = TRUE;
			  break;
			case LOGIN_BAD_CODE :
			  define("LOGINMESSAGE", LAN_LOGIN_23."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_02', $username);
			  break;
			case LOGIN_NOT_ACTIVATED :
			  $srch = array("[","]");
			  $repl = array("<a href='".e_BASE_ABS."signup.php?resend'>","</a>");					
			  define("LOGINMESSAGE", str_replace($srch,$repl,LAN_LOGIN_22)."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_05', $username);
			  $this->genNote($username, LAN_LOGIN_27);
			  $doCheck = TRUE;
			  break;
			case LOGIN_BLANK_FIELD :
			  define("LOGINMESSAGE", LAN_LOGIN_20."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_01', $username);
			  break;
			case LOGIN_BAD_TRIGGER :
			  define("LOGINMESSAGE", $extra_text."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_06', $username);
			  break;
			case LOGIN_BANNED :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");		// Just give 'incorrect login' message
			  $this->genNote($username, LAN_LOGIN_25);
			  $this->logNote('LAN_ROLL_LOG_09', $username);
			  break;
			default :		// Something's gone wrong!
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");		// Just give 'incorrect login' message
			  $this->genNote($username, LAN_LOGIN_26);
			  $this->logNote('LAN_ROLL_LOG_10', $username);
		}

		if ($doCheck)
		{		// See if ban required (formerly the checkibr() function)
			if($pref['autoban'] == 1 || $pref['autoban'] == 3)
			{ // Flood + Login or Login Only.
				$fails = $this->e107->sql -> db_Count("generic", "(*)", "WHERE gen_ip='{$this->userIP}' AND gen_type='failed_login' ");
				if($fails > 10)
				{
					$this->e107->add_ban(4,LAN_LOGIN_18,$this->userIP,1);
					$this->e107->sql -> db_Insert("generic", "0, 'auto_banned', '".time()."', 0, '{$this->userIP}', '{$extra_text}', '".LAN_LOGIN_20.": ".$this->e107->tp -> toDB($username).", ".LAN_LOGIN_17.": ".md5($ouserpass)."' ");
				}
			}
		}
		return FALSE;		// Passed back to signal failed login
	}


	/**
	 * Make a note of an event in the rolling log
	 * @param string $title - title of logged event
	 * @param string $text - detail of event
	 * @return none
	 */
	protected function logNote($title, $text)
	{
		$e107 = &e107::getInstance();
		$title = $e107->tp->toDB($title);
		$text  = $e107->tp->toDB($text);
		$e107->admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", $title, $text, FALSE, LOG_TO_ROLLING);
	}


	/**
	 * Make a note of a failed login in the 'generic' table
	 * @param string $username - as entered
	 * @param string $msg1 - detail of event
	 * @return none
	 */
	protected function genNote($username, $msg1)
	{
		$e107 = &e107::getInstance();
		$message = $e107->tp->toDB($msg1." ::: ".LAN_LOGIN_1.": ".$username);
		$e107->sql->db_Insert("generic", "0, 'failed_login', '".time()."', 0, '{$this->userIP}', 0, '{$message}'");
	}



	/**
	 * called to update user settings from a XUP file - usually because the file name has changed.
	 * @param string $user_id - integer user ID
	 * @param string $user_xup - file name/location for XUP file
	 * @return none
	 */
	public function update_xup($user_id, $user_xup = "")
	{
		$e107 = &e107::getInstance();
		$user_id = intval($user_id);		// Should already be an integer - but just in case...
		$user_xup = trim($user_xup);
		if($user_xup)
		{
			$xml = e107::getXml();
			$xupData = array();
			if($rawData = $xml -> getRemoteFile($user_xup))
			{
				preg_match_all("#\<meta name=\"(.*?)\" content=\"(.*?)\" \/\>#si", $rawData, $match);
				$count = 0;
				foreach($match[1] as $value)
				{	// Process all the data into an array
					$xupData[$value] = $e107->tp -> toDB($match[2][$count]);
					$count++;
				}

				// List of fields in main user record, and their corresponding XUP fields
				$main_fields = array('user_realname' => 'FN',
									'user_hideemail'=>'EMAILHIDE',
									'user_signature'=>'SIG',
									'user_sess'=>'PHOTO',
									'user_image'=>'AV');

				$new_values = array();
				foreach ($main_fields as $f => $v)
				{
					if (isset($xupData[$v]) && $xupData[$v])
					{
						$new_values['data'][$f] = $xupData[$v];
					}
				}

				if (count($new_values['data']))
				{
					if (!is_object($this->userMethods))
					{
						$this->userMethods = new userHandler;
					}
					require_once(e_HANDLER.'validator_class.php');
					$this->userMethods($new_values);
					$new_values['WHERE'] = 'user_id='.$user_id;
					validatorClass::addFieldTypes($this->userMethods->userVettingInfo,$new_values);
					$e107->sql -> db_Update('user', $new_values);
				}

				$ueList = array();
				$fields = array('URL' => 'user_homepage',
							'ICQ' 	=> 'user_icq',
							'AIM' 	=> 'user_aim',
							'MSN' 	=> 'user_msn',
							'YAHOO' => 'user_yahoo',
							'GEO' 	=> 'user_location',
							'TZ' 	=> 'user_timezone',
							'BDAY' 	=> 'user_birthday');
				include_once(e_HANDLER.'user_extended_class.php');
				$usere = new e107_user_extended;
				$extName = array();
				foreach ($fields as $keyxup => $keydb)
				{
					if (in_array($keydb, $usere->nameIndex) && in_array($keyxup,$xupData))
					{
						$ueList['data'][$keydb] = $e107->tp->toDB($xupData[$keyxup]);
					}
				}
				if (count($ueList['data']))
				{
					$usere->addFieldTypes($ueList);
					$ueList['WHERE'] = 'user_extended_id = '.$user_id;
					$e107->sql -> db_Select_gen('INSERT INTO #user_extended (user_extended_id) values ('.$user_id.')');
					$e107->sql -> db_Update('user_extended', $ueList);
				}
			}
		}
	}
}
