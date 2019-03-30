<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Login handler
 *
*/


if (!defined('e107_INIT')) { exit; }


// require_once(e_HANDLER.'user_handler.php'); //shouldn't be necessary
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_login.php');

// TODO - class constants
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

/**
 * TODO - use new user model, compact everything in max 2 classes
 */
class userlogin
{
	protected $e107;
	protected $userMethods;			// Pointer to user handler
	protected $userIP;				// IP address
	protected $lookEmail = false;	// Flag set if logged in using email address
	protected $userData = array();	// Information for current user
	protected $passResult = false;	// USed to determine if stored password needs update
	protected $testMode   = false;


	public function __construct()
	{
		$this->e107 = e107::getInstance();
		$this->userIP = e107::getIPHandler()->getIP();
		$this->userMethods = e107::getUserSession();
	}

	/**
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
	public function login($username, $userpass, $autologin, $response = '', $noredirect = false)
	{
		$pref = e107::getPref();
		$tp = e107::getParser();
		$sql = e107::getDb();

		$e_event = e107::getEvent();
		$_E107 = e107::getE107();
		
		$username = trim($username);
		$userpass = trim($userpass);

		if($_E107['cli'] && ($username == ''))
		{
			return FALSE;
		}
		
		$forceLogin = ($autologin === 'signup');
		if(!$forceLogin && $autologin === 'provider') $forceLogin = 'provider';

		if($username == "" || (($userpass == "") && ($response == '') && $forceLogin !== 'provider'))
		{	// Required fields blank
			return $this->invalidLogin($username,LOGIN_BLANK_FIELD);
		}

//	    $this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'IP: '.$fip,FALSE,LOG_TO_ROLLING);
//		$this->e107->check_ban("banlist_ip='{$this->userIP}' ",FALSE);			// This will exit if a ban is in force
		e107::getIPHandler()->checkBan("banlist_ip='{$this->userIP}' ",FALSE);			// This will exit if a ban is in force
		
		$autologin = intval($autologin);		// Will decode to zero if forced login
		$authorized = false;
		if (!$forceLogin && $this->e107->isInstalled('alt_auth'))
		{
			$authMethod[0] = varset($pref['auth_method'], 'e107');		// Primary authentication method
			$authMethod[1] = varset($pref['auth_method2'], 'none');		// Secondary authentication method (if defined)
			$result = false;
			foreach ($authMethod as $method)
			{
				if ($method == 'e107')
				{
					if ($this->lookupUser($username, $forceLogin))
					{
						if ($this->checkUserPassword($username, $userpass, $response, $forceLogin) === TRUE)
						{
							$authorized = true;
							$result = LOGIN_CONTINUE;		// Valid User exists in local DB
						}
						elseif(varset($pref['auth_badpassword'], TRUE))
						{
							$result = LOGIN_TRY_OTHER;
							continue; // Should use alternate method for password auth
						}
						else 
						{
							return $this->invalidLogin($username,LOGIN_ABORT);
						}
					}
				}
				else
				{
					if ($method != 'none')
					{
						$auth_file = e_PLUGIN.'alt_auth/'.$method.'_auth.php';
						if (file_exists($auth_file))
						{
							require_once(e_PLUGIN.'alt_auth/alt_auth_login_class.php');
							$al = new alt_login($method, $username, $userpass); 
							$result = $al->loginResult; 
							switch ($result)
							{
								case LOGIN_ABORT :
									return $this->invalidLogin($username,LOGIN_ABORT);
								break;
								case LOGIN_DB_ERROR :
									return $this->invalidLogin($username,LOGIN_DB_ERROR);
								break;
								case AUTH_SUCCESS:
									$authorized = true;
								break;
								case LOGIN_TRY_OTHER:
									continue;
								break;
							}
						}
					}
				}
				if ($result === LOGIN_CONTINUE)
				{
					break;
				}
			}
		}

		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);

		// Check secure image
		if (!$forceLogin && $pref['logcode'] && extension_loaded('gd'))
		{
			if ($secImgResult = e107::getSecureImg()->invalidCode($_POST['rand_num'], $_POST['code_verify'])) // Invalid code
			{
				return $this->invalidLogin($username, LOGIN_BAD_CODE, $secImgResult);
			}

		}

		if (empty($this->userData))		// May have retrieved user data earlier
		{
			if (!$this->lookupUser($username, $forceLogin))
			{
				return $this->invalidLogin($username,LOGIN_BAD_USERNAME);		// User doesn't exist
			}
		}

		if ($authorized !== true && $this->checkUserPassword($username, $userpass, $response, $forceLogin) !== true)
		{
			return $this->invalidLogin($username,LOGIN_BAD_PW);
		}


		// Check user status
		switch ($this->userData['user_ban'])
		{
			case USER_REGISTERED_NOT_VALIDATED : // User not fully signed up - hasn't activated account.
				return $this->invalidLogin($username, LOGIN_NOT_ACTIVATED);
			case USER_BANNED :		// User banned
				return $this->invalidLogin($username, LOGIN_BANNED,$this->userData['user_id']);
			case USER_VALIDATED :		// Valid user
				break;			// Nothing to do ATM
			case USER_EMAIL_BOUNCED:
				$bounceLAN      = LAN_LOGIN_36;
				$bounceMessage  =  $tp->lanVars($bounceLAN, $this->userData['user_email'],true );
				$bounceMessage  = str_replace(array('[',']'),array("<a href='".e_HTTP."usersettings.php'>","</a>"), $bounceMessage);

				e107::getMessage()->addWarning($bounceMessage, 'default', true);
				break;
			default :			// May want to pick this up
		}


		// User is OK as far as core is concerned
//	    $this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'User passed basics',FALSE,LOG_TO_ROLLING);
		if (($this->passResult !== FALSE) && ($this->passResult !== PASSWORD_VALID))
		{  // May want to rewrite password using salted hash (or whatever the preferred method is) - $pass_result has the value to write
			// If login by email address also allowed, will have to write that value too
//		  	$sql->update('user',"`user_password` = '{$pass_result}' WHERE `user_id`=".intval($this->userData['user_id']));
			$reHashedPass = $this->userMethods->rehashPassword($this->userData,$userpass);
			if($reHashedPass !==false)
			{
				$log = e107::getLog();
				$auditLog = "User Password ReHashed";
				$log->user_audit(USER_AUDIT_LOGIN, $auditLog, $this->userData['user_id'], $this->userData['user_name']);
				$this->userData['user_password'] = $reHashedPass;
			}
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
		$user_admin = $this->userData['user_admin'];
		$user_email = $this->userData['user_email'];

		/* restrict more than one person logging in using same us/pw */
		if(!empty($pref['track_online']) && !empty($pref['disallowMultiLogin']) && !empty($user_id))
		{
			if($sql->select("online", "online_ip", "online_user_id='".$user_id.".".$user_name."'"))
			{
				return $this->invalidLogin($username, LOGIN_MULTIPLE, $user_id);
			}
		}


		// User login definitely accepted here

		$cookieval = $this->userMethods->makeUserCookie($this->userData,$autologin);


		// Calculate class membership - needed for a couple of things
		// Problem is that USERCLASS_LIST just contains 'guest' and 'everyone' at this point
		$class_list = $this->userMethods->addCommonClasses($this->userData, TRUE);

	//	$user_logging_opts = e107::getConfig()->get('user_audit_opts');

	/*	if (in_array(varset($pref['user_audit_class'],''), $class_list))
		{  // Need to note in user audit trail
			$log = e107::getLog();
			$log->user_audit(USER_AUDIT_LOGIN,'', $user_id, $user_name);
		}*/

		$edata_li = array('user_id' => $user_id, 'user_name' => $user_name, 'class_list' => implode(',',$class_list), 'remember_me' => $autologin, 'user_admin'=>$user_admin, 'user_email'=> $user_email);
		e107::getEvent()->trigger("login", $edata_li);

		if($_E107['cli'])
		{
			return $cookieval;
		}

		if (in_array(e_UC_NEWUSER,$class_list))//XXX Why not just add a check in check_class ?
		{
			if (time() > ($this->userData['user_join'] + (varset($pref['user_new_period'],0)*86400)))
			{	// 'New user' probationary period expired - we can take them out of the class
				$this->userData['user_class'] = $this->e107->user_class->ucRemove(e_UC_NEWUSER, $this->userData['user_class']);
//				$this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login new user complete",$this->userData['user_class'],FALSE,FALSE);

				/**
				 * issue e107inc/e107#3657: Third argument of update() function is for debugging purposes and NOT used for the WHERE clause.
				 * Therefore the query was run without WHERE, which resulted into applyiing the new classes to all users....
				 */
				//$sql->update('user',"`user_class` = '".$this->userData['user_class']."'", 'WHERE `user_id`='.$this->userData['user_id']. " LIMIT 1");
				$sql->update('user',"`user_class` = '" . $this->userData['user_class'] . "' WHERE `user_id`=" . $this->userData['user_id'] . " LIMIT 1");
				unset($class_list[e_UC_NEWUSER]);
				$edata_li = array('user_id' => $user_id, 'user_name' => $username, 'class_list' => implode(',',$class_list), 'user_email'=> $user_email);
				$e_event->trigger('userNotNew', $edata_li);
			}
		}

		if($noredirect) return true;
		$redir = e_REQUEST_URL;
		//$redir = e_SELF;
		//if (e_QUERY) $redir .= '?'.str_replace('&amp;','&',e_QUERY);
		if (isset($pref['frontpage_force']) && is_array($pref['frontpage_force']))
		{	// See if we're to force a page immediately following login - assumes $pref['frontpage_force'] is an ordered list of rules
//		  $log_info = "New user: ".$this->userData['user_name']."  Class: ".$this->userData['user_class']."  Admin: ".$this->userData['user_admin']."  Perms: ".$this->userData['user_perms'];
//		  $this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login Start",$log_info,FALSE,FALSE);
			// FIXME - front page now supports SEF URLs - make a check here
			foreach ($pref['frontpage_force'] as $fk=>$fp)
			{
				if (in_array($fk,$class_list))
				{  // We've found the entry of interest
					if (strlen($fp))
					{
						if (strpos($fp, 'http') === FALSE)
						{
							$fp = str_replace(e_HTTP, '', $fp);		// This handles sites in a subdirectory properly (normally, will replace nothing)
							$fp = SITEURL.$fp;
						}
						//$redir = ((strpos($fp, 'http') === FALSE) ? SITEURL : '').$tp->replaceConstants($fp, TRUE, FALSE);
						$redir = e107::getParser()->replaceConstants($fp, TRUE, FALSE);
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


	public function getUserData()
	{
		return $this->userData;
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
		$pref = e107::getPref();
		$log = e107::getLog();

		$maxLength = varset($pref['loginname_maxlength'],30);

		/*
		 * 2: Username/Email and Password
		 * 1: Email and Password
		 * 0: Username and Password
		 */
		if(!empty($pref['allowEmailLogin'])) // Email login only
		{
			$maxLength = 254; // Maximum email length	
		}

		// Check username general format
		if (!$forceLogin && (strlen($username) > $maxLength)) // Error - invalid username
		{
			$auditLog = array('reason'=>'username longer than maxlength', 'maxlength'=> $maxLength, 'username'=>$username);
			$log->user_audit(USER_AUDIT_LOGIN, $auditLog, 0, $username);
			$this->invalidLogin($username,LOGIN_BAD_USERNAME);
			return FALSE;
		}

		$query = $this->getLookupQuery($username, $forceLogin);

		if (e107::getDb()->select('user', '*', $query) !== 1) 	// Handle duplicate emails as well // Invalid user
		{
			$auditLog = array('reason'=>'query failed to return a result', 'query'=>$query, 'username'=>$username);
			$log->user_audit(USER_AUDIT_LOGIN, $auditLog, 0, $username);
			return $this->invalidLogin($username,LOGIN_BAD_USER);
		}

		// User is in DB here
		$this->userData = e107::getDb()->fetch();		// Get user info
		$this->userData['user_perms'] = trim($this->userData['user_perms']);
		$this->lookEmail = ($username == $this->userData['user_email']) ? 1 : 0;		// Know whether login name or email address used now
		
		return TRUE;
	}



	/**
	 *	Generate a DB query to look up a user, dependent on the various login options supported.
	 */
	public function getLookupQuery($username, $forceLogin, $dbAlias = '')
	{
		$pref = e107::getPref();
		$tp = e107::getParser();

		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
		
		if($forceLogin === 'provider')
		{
			return "{$dbAlias}`user_xup`='".$tp->toDB($username)."'";
		}

        $qry[0] = "{$dbAlias}`user_loginname`= '".$tp->toDB($username)."'";  // username only  (default)
		$qry[1] = "{$dbAlias}`user_email` = '".$tp->toDB($username)."'";   // email only
		$qry[2] = (strpos($username,'@') !== false ) ? "{$dbAlias}`user_loginname`= '".$tp->toDB($username)."'  OR {$dbAlias}`user_email` = '".$tp->toDB($username)."'" : $qry[0];  //username or email
		

		// Look up user in DB - even if email addresses allowed, still look up by user name as well - user could have specified email address for their login name
        $query = (!$forceLogin && varset($pref['allowEmailLogin'],0)) ? $qry[$pref['allowEmailLogin']] : $qry[0];
		return $query;
	}


	/**
	 * Checks user password againt preferences set etc
	 * Assumes that $this->userData array already set up
	 *
	 * @param string $username - the user name string as entered (might not relate to the intended user at this stage)
	 * @param string $userpass - as entered
	 * @param string $response - received string if CHAP used
	 * @param boolean $forceLogin - TRUE if login is being forced from clicking signup link; normally FALSE
	 * @return TRUE if valid password
	 *		   otherwise FALSE
	 */
	protected function checkUserPassword($username, $userpass, $response, $forceLogin)
	{
		$pref = e107::getPref();
		$log = e107::getAdminLog();
		
		if($forceLogin === 'provider') return true;
		/*
		if ($this->lookEmail && vartrue($pref['passwordEncoding']))
		{
			$tmp = e107::getArrayStorage()->unserialize($this->userData['user_prefs']);
            if(!$tmp && $this->userData['user_prefs']) $tmp = unserialize($this->userData['user_prefs']); // try old storage type
			$requiredPassword = varset($tmp['email_password'], $this->userData['user_password']);	// Use email-specific password if set. Otherwise, 'normal' one might work
			unset($tmp);
		}
		else
		{
			$requiredPassword = $this->userData['user_password'];
		}
		*/

		// Now check password
		if ($forceLogin)
		{
			if (md5($this->userData['user_name'].$this->userData['user_password'].$this->userData['user_join']) != $userpass)
			{
				return $this->invalidLogin($username,LOGIN_BAD_PW);
			}
		}
		else
		{
			$session = e107::getSession();
			$gotChallenge = $session->is('challenge');
			//$aLogVal = "U: {$username}, P: ******, C: ".$session->get('challenge')." R:{$response} S: {$this->userData['user_password']} Prf: {$pref['password_CHAP']}/{$gotChallenge}";
			if ((($pref['password_CHAP'] > 0) && ($response && $gotChallenge) && ($response != $session->get('challenge'))) || ($pref['password_CHAP'] == 2))
			{  // Verify using CHAP
			  	//$this->e107->admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","CHAP login",$aLogVal, FALSE, LOG_TO_ROLLING);
				if (($pass_result = $this->userMethods->CheckCHAP($session->get('challenge'), $response, $username, $this->userData['user_password'])) === PASSWORD_INVALID)
				{
					return $this->invalidLogin($username,LOGIN_CHAP_FAIL);
				}
			}
			else // Plaintext password
			{

				$login_name = ($this->lookEmail) ? $this->userData['user_loginname'] : $username;

			  	$auditLog = array(
					'type'              => (($this->lookEmail) ? 'email' : 'userlogin'),
					'login_name'        => $login_name,
					'userpass'          => $userpass,
					'pwdHash'           => $this->userData['user_password']
				);

				if (($pass_result = $this->userMethods->CheckPassword($userpass, $login_name, $this->userData['user_password'])) === PASSWORD_INVALID)
				{
					$auditLog['result'] = intval($pass_result);
					$log->user_audit(USER_AUDIT_LOGIN, $auditLog, $this->userData['user_id'], $this->userData['user_name']);
					return $this->invalidLogin($username,LOGIN_BAD_PW);
				}

				$auditLog['result'] = intval($pass_result);

				$log->user_audit(USER_AUDIT_LOGIN, $auditLog, $this->userData['user_id'], $this->userData['user_name']);
			}


			$this->passResult = $pass_result;
		}

		return true;
	}


	public function test()
	{

		$this->testMode = true;
		$errors = array(
			'LOGIN_TRY_OTHER'=> 2,		// Try some other authentication method
			'LOGIN_CONTINUE'=>1,		// Not rejected (which is not exactly the same as 'accepted') by alt_auth
			'LOGIN_ABORT'=>-1,			// Rejected by alt_auth
			'LOGIN_BAD_PW'=> -2,		// Password wrong
			'LOGIN_BAD_USER'=> -3,		// User not in DB
			'LOGIN_BAD_USERNAME'=> -4,	// Username format unacceptable (e.g. too long)
			'LOGIN_BAD_CODE'=> -5,		// Wrong image code entered
			'LOGIN_MULTIPLE'=> -6, 		// Error if multiple logins not allowed
			'LOGIN_NOT_ACTIVATED'=> -7,	// User in DB=> not activated
			'LOGIN_BLANK_FIELD'=> -8,	// Username or password blank
			'LOGIN_BAD_TRIGGER'=> -9,	// Rejected by trigger event
			'LOGIN_BANNED'=> -10,		// Banned user attempting login
			'LOGIN_CHAP_FAIL'=> -11,	// CHAP login failed
			'LOGIN_DB_ERROR'=> -12,		// Error adding user to main DB
		);

		foreach($errors as $k=>$v)
		{
			$this->invalidLogin("John Smith", $v, 'Custom error text');
			echo "<h4>".$k."</h4>";
			echo e107::getMessage()->render();
		}

	}

	/**
	 * called to log the reason for a failed login.
	 * @param string $plugname
	 * @return boolean Currently always returns false - could return some other value
	 */
	protected function invalidLogin($username, $reason, $extra_text = '')
	{
		global $pref, $sql;

		$doCheck = FALSE;			// Flag set if need to ban check


		switch($reason)
		{
			case LOGIN_ABORT :        // alt_auth reject
				$message = LAN_LOGIN_21;
				$this->genNote($this->userIP, $username, 'Alt_auth: ' . LAN_LOGIN_14);
				$this->logNote('LAN_ROLL_LOG_04', 'Alt_Auth: ' . $username);
				$doCheck = true;
				break;
			case LOGIN_DB_ERROR :    // alt_auth couldn't add valid user
				$message = LAN_LOGIN_31;
				$this->genNote($username, 'Alt_auth: ' . LAN_LOGIN_30);
//				$this->logNote('LAN_ROLL_LOG_04', 'Alt_Auth: '.$username);	// Added in alt_auth login
				$doCheck = true;
				break;
			case LOGIN_BAD_PW :
				$message = LAN_LOGIN_21;
				$this->logNote('LAN_ROLL_LOG_03', $username);
				break;
			case LOGIN_CHAP_FAIL :
				$message = LAN_LOGIN_21;
				$this->logNote('LAN_ROLL_LOG_03', 'CHAP: ' . $username);
				break;
			case LOGIN_BAD_USER :
				$message = LAN_LOGIN_21;
				$this->genNote($username, LAN_LOGIN_14);
				$this->logNote('LAN_ROLL_LOG_04', $username);
				$doCheck = true;
				break;
			case LOGIN_BAD_USERNAME :
				$message = LAN_LOGIN_21;
				$this->logNote('LAN_ROLL_LOG_08', $username);
				break;
			case LOGIN_MULTIPLE :
				$message = LAN_LOGIN_24;
				$this->logNote('LAN_ROLL_LOG_07', "U: {$username} IP: {$this->userIP}");
				$this->genNote($username, LAN_LOGIN_16);
				$doCheck = true;
				break;
			case LOGIN_BAD_CODE :
				$message = $extra_text; // LAN_LOGIN_23;
				$this->logNote('LAN_ROLL_LOG_02', $username);
				break;
			case LOGIN_NOT_ACTIVATED :
				if($pref['user_reg_veri'] == 2)
				{
					$message = LAN_LOGIN_37;
				}
				else
				{
					$srch = array("[", "]");
					$repl = array("<a href='" . e_HTTP . "signup.php?resend'>", "</a>");
					$message = str_replace($srch, $repl, LAN_LOGIN_22);
				}
				$this->logNote('LAN_ROLL_LOG_05', $username);
				$this->genNote($username, LAN_LOGIN_27);
				$doCheck = true;
				break;
			case LOGIN_BLANK_FIELD :
				$message = LAN_LOGIN_20;
				$this->logNote('LAN_ROLL_LOG_01', $username);
				break;
			case LOGIN_BAD_TRIGGER :
				$message = $extra_text;
				$this->logNote('LAN_ROLL_LOG_06', $username);
				break;
			case LOGIN_BANNED :
				$message = LAN_LOGIN_21;        // Just give 'incorrect login' message
				$this->genNote($username, LAN_LOGIN_25);
				$this->logNote('LAN_ROLL_LOG_09', $username);
				break;
			default :        // Something's gone wrong!
				$message = LAN_LOGIN_21;        // Just give 'incorrect login' message
				$this->genNote($username, LAN_LOGIN_26);
				$this->logNote('LAN_ROLL_LOG_10', $username);
		}

		e107::getMessage()->reset()->addError($message); // prevent duplicates.

		if($this->testMode === true)
		{
			return $message;
		}

		define('LOGINMESSAGE', $message);

	//	$sql->update('online', 'user_active = 0 WHERE user_ip = "'.$this->userIP.'" LIMIT 1');

		if ($doCheck) // See if ban required (formerly the checkibr() function)
		{
			if($pref['autoban'] == 1 || $pref['autoban'] == 3) // Flood + Login or Login Only.
			{
				$fails = $sql->count("generic", "(*)", "WHERE gen_ip='{$this->userIP}' AND gen_type='failed_login' ");

				$failLimit = vartrue($pref['failed_login_limit'],10);

				if($fails >= $failLimit)
				{
					$time = time();
					$description = e107::getParser()->lanVars(LAN_LOGIN_18,$failLimit);
					e107::getIPHandler()->add_ban(4, $description, $this->userIP, 1);
					e107::getDb()->insert("generic", "0, 'auto_banned', '".$time."', 0, '{$this->userIP}', '{$extra_text}', '".LAN_LOGIN_20.": ".e107::getParser()->toDB($username).", ".LAN_LOGIN_17.": ".md5($ouserpass)."' ");
					e107::getEvent()->trigger('user_ban_failed_login', array('time'=>$time, 'ip'=>$this->userIP, 'other'=>$extra_text)); 
				}
			}
		}
		return false;		// Passed back to signal failed login
	}


	/**
	 * Make a note of an event in the rolling log
	 * @param string $title - title of logged event
	 * @param string $text - detail of event
	 * @return none
	 */
	protected function logNote($title, $text)
	{
		$title = e107::getParser()->toDB($title);
	//	$text  = e107::getParser()->toDB($text);
	//	$text = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS_);

		$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,4);

		// unset($debug[0]);
		$debug[0] = e_REQUEST_URI;

	//	$array = debug_backtrace();
	//	e107::getLog()->e_log_event(4, $array, "LOGIN", $title, $text, FALSE, LOG_TO_ROLLING);
		e107::getLog()->e_log_event(4, $debug[1]['file']."|".$debug[1]['function']."@".$debug[1]['line'], "LOGIN", $title, $debug, FALSE, LOG_TO_ROLLING);
	}


	/**
	 * Make a note of a failed login in the 'generic' table
	 * @param string $username - as entered
	 * @param string $msg1 - detail of event
	 * @return none
	 */
	protected function genNote($username, $msg1)
	{
		$message = e107::getParser()->toDB($msg1." ::: ".LAN_LOGIN_1.": ".$username);
		e107::getDb()->insert("generic", "0, 'failed_login', '".time()."', 0, '{$this->userIP}', 0, '{$message}'");
	}


}