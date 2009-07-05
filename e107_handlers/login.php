<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ?Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/login.php,v $
|     $Revision: 1.23 $
|     $Date: 2009-07-05 18:47:51 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/


if (!defined('e107_INIT')) { exit; }

error_reporting(E_ALL);


require_once(e_HANDLER.'user_handler.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_login.php');

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
	var $userMethods;			// Pointer to user handler


	function userlogin($username, $userpass, $autologin, $response = '') 
	{
		/* Constructor
		# Class called when user attempts to log in
		#
		# - parameters #1:      string $username, $_POSTED user name
		# - parameters #2:      string $userpass, $_POSTED user password
		# @param $autologin - 'signup' - uses a specially encoded password - logs in if matches
		#					- zero for 'normal' login
		#					- non-zero sets the 'remember me' flag in the cookie
		# - return              boolean
		# - scope				public
		*/
		global $pref, $e_event, $sql, $e107, $tp;
		global $admin_log,$_E107;

		$username = trim($username);
		$userpass = trim($userpass);

		if($_E107['cli'] && ($username == ""))
		{
			return FALSE;
		}

		$fip = $e107->getip();
		if($username == "" || (($userpass == "") && ($response == '')))
		{	// Required fields blank
			return $this->invalidLogin($username,LOGIN_BLANK_FIELD,$fip);
		}

	 	if(!is_object($sql)) { $sql = new db; }

//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'IP: '.$fip,FALSE,LOG_TO_ROLLING);
		$e107->check_ban("banlist_ip='{$fip}' ",FALSE);			// This will exit if a ban is in force

		$forceLogin = ($autologin == 'signup');
		$autologin = intval($autologin);		// Will decode to zero if forced login

		if ($pref['auth_method'] && $pref['auth_method'] != 'e107' && !$forceLogin) 
		{
			$auth_file = e_PLUGIN."alt_auth/".$pref['auth_method']."_auth.php";
			if (file_exists($auth_file)) 
			{
				require_once(e_PLUGIN."alt_auth/alt_auth_login_class.php");
				$result = new alt_login($pref['auth_method'], $username, $userpass);
				switch ($result)
				{
					case LOGIN_ABORT :
						return $this->invalidLogin($username,LOGIN_ABORT,$fip);
					case LOGIN_DB_ERROR :
						return $this->invalidLogin($username,LOGIN_DB_ERROR,$fip);
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
				return $this->invalidLogin($username,LOGIN_BAD_CODE,$fip);
			}
		}

		// Check username general format
		if (!$forceLogin && (strlen($username) > varset($pref['loginname_maxlength'],30)))
		{  // Error - invalid username
			return $this->invalidLogin($username,LOGIN_BAD_USERNAME,$fip);
		}


        $qry[0] = "`user_loginname`= '".$tp -> toDB($username)."'";  // username only  (default)
		$qry[1] = "`user_email` = '".$tp -> toDB($username)."'";   // email only
		$qry[2] = (strpos($username,'@') !== FALSE ) ? "`user_loginname`= '".$tp -> toDB($username)."'  OR `user_email` = '".$tp -> toDB($username)."'" : $qry[0];  //username or email
        	// Look up user in DB - even if email addresses allowed, still look up by user name as well - user could have specified email address for their login name

        $query = (!$forceLogin && varset($pref['allowEmailLogin'],0)) ? $qry[$pref['allowEmailLogin']] : $qry[0];

		if ($sql->db_Select('user', '*', $query) !== 1) 	// Handle duplicate emails as well
		{	// Invalid user
			return $this->invalidLogin($username,LOGIN_BAD_USER,$fip);
		}

		// User is in DB here
		$lode = $sql -> db_Fetch(MYSQL_ASSOC);		// Get user info
		$lode['user_perms'] = trim($lode['user_perms']);
		$lookemail = $lookemail && ($tp -> toDB($username) == $lode['user_email']);		// Know whether login name or email address used now
		if ($lookemail && varsettrue($pref['passwordEncoding']))
		{
			$tmp = unserialize($lode['user_prefs']);
			$requiredPassword = varset($tmp['email_password'],$lode['user_password']);	// Use email-specific password if set. Otherwise, 'normal' one might work
			unset($tmp);
		}
		else
		{
			$requiredPassword = $lode['user_password'];
		}

		// Now check password
		$this->userMethods = new UserHandler;
		if ($forceLogin)
		{
			if (md5($lode['user_name'].$lode['user_password'].$lode['user_join']) != $userpass)
			{
				return $this->invalidLogin($username,LOGIN_BAD_PW,$fip);
			}
		}
		else
		{
			if ((($pref['password_CHAP'] > 0) && ($response && isset($_SESSION['challenge'])) && ($response != $_SESSION['challenge'])) || ($pref['password_CHAP'] == 2))
			{  // Verify using CHAP
	//		  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","CHAP login","U: {$username}, P: {$userpass}, C: {$_SESSION['challenge']} R:{$response} S: {$lode['user_password']}",FALSE,LOG_TO_ROLLING);
				if (($pass_result = $this->userMethods->CheckCHAP($_SESSION['challenge'], $response, $username, $requiredPassword)) === PASSWORD_INVALID)
				{
					return $this->invalidLogin($username,LOGIN_CHAP_FAIL,$fip);
				}
			}
			else
			{	// Plaintext password
	//		  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Plaintext login","U: {$username}, P: {$userpass}, C: {$_SESSION['challenge']} R:{$response} S: {$lode['user_password']}",FALSE,LOG_TO_ROLLING);
				if (($pass_result = $this->userMethods->CheckPassword($userpass,($lookemail ? $lode['user_loginname'] : $username),$requiredPassword)) === PASSWORD_INVALID)
				{
					return $this->invalidLogin($username,LOGIN_BAD_PW,$fip);
				}
			}
		}

		// Check user status
		switch ($lode['user_ban'])
		{
		  case USER_REGISTERED_NOT_VALIDATED : // User not fully signed up - hasn't activated account. 
			return $this->invalidLogin($username,LOGIN_NOT_ACTIVATED,$fip);
		  case USER_BANNED :		// User banned
			return $this->invalidLogin($username,LOGIN_BANNED,$fip,$lode['user_id']);
		  case USER_VALIDATED :		// Valid user
		    break;			// Nothing to do ATM
		  default :			// May want to pick this up
		}


		// User is OK as far as core is concerned
//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'User passed basics',FALSE,LOG_TO_ROLLING);
		if ($pass_result !== PASSWORD_VALID)
		{  // May want to rewrite password using salted hash (or whatever the preferred method is) - $pass_result has the value to write
			// If login by email address also allowed, will have to write that value too
//		  $sql->db_Update('user',"`user_password` = '{$pass_result}' WHERE `user_id`=".intval($lode['user_id']));
		}


		$userpass = '';				// Finished with any plaintext password - can get rid of it
	

		$ret = $e_event->trigger("preuserlogin", $username);
		if ($ret != '') 
		{
			return $this->invalidLogin($username,LOGIN_BAD_TRIGGER,$fip,$ret);
		} 


		// Trigger events happy as well
		$user_id = $lode['user_id'];
		$user_name = $lode['user_name'];
		$user_xup = $lode['user_xup'];

		/* restrict more than one person logging in using same us/pw */
		if($pref['disallowMultiLogin']) 
		{
			if($sql -> db_Select("online", "online_ip", "online_user_id='".$user_id.".".$user_name."'")) 
			{
				return $this->invalidLogin($username,LOGIN_MULTIPLE,$fip,$user_id);
			}
		}


		// User login definitely accepted here


		if($user_xup) 
		{
			$this->update_xup($user_id, $user_xup);
		}


		$cookieval = $this->userMethods->makeUserCookie($lode,$autologin);


		// Calculate class membership - needed for a couple of things
		// Problem is that USERCLASS_LIST just contains 'guest' and 'everyone' at this point
		$class_list = $this->userMethods->addCommonClasses($lode, TRUE);

		$user_logging_opts = array_flip(explode(',',varset($pref['user_audit_opts'],'')));
		if (isset($user_logging_opts[USER_AUDIT_LOGIN]) && in_array(varset($pref['user_audit_class'],''),$class_list))
		{  // Need to note in user audit trail
			$admin_log->user_audit(USER_AUDIT_LOGIN,'', $user_id,$user_name);
		}

		$edata_li = array('user_id' => $user_id, 'user_name' => $username, 'class_list' => implode(',',$class_list), 'remember_me' => $autologin);
		$e_event->trigger("login", $edata_li);

		if($_E107['cli'])
		{
			return $cookieval;
		}

		if (in_array(e_UC_NEWUSER,$class_list))
		{
			if (time() > ($lode['user_join'] + (varset($pref['user_new_period'],0)*86400)))
			{	// 'New user' probationary period expired - we can take them out of the class
				$lode['user_class'] = $e107->user_class->ucRemove(e_UC_NEWUSER, $lode['user_class']);
//				$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login new user complete",$lode['user_class'],FALSE,FALSE);
				$sql->db_Update('user',"`user_class` = '".$lode['user_class']."'", 'WHERE `user_id`='.$lode['user_id']);
				unset($class_list[e_UC_NEWUSER]);
				$edata_li = array('user_id' => $user_id, 'user_name' => $username, 'class_list' => implode(',',$class_list));
				$e_event->trigger('userNotNew', $edata_li);
			}
		}

		$redir = e_SELF;
		if (e_QUERY) $redir .= '?'.str_replace('&amp;','&',e_QUERY);
		if (isset($pref['frontpage_force']) && is_array($pref['frontpage_force'])) 
		{	// See if we're to force a page immediately following login - assumes $pref['frontpage_force'] is an ordered list of rules
//		  $log_info = "New user: ".$lode['user_name']."  Class: ".$lode['user_class']."  Admin: ".$lode['user_admin']."  Perms: ".$lode['user_perms'];
//		  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login Start",$log_info,FALSE,FALSE);
			foreach ($pref['frontpage_force'] as $fk=>$fp)
			{
				if (in_array($fk,$class_list))
				{  // We've found the entry of interest
					if (strlen($fp))
					{
						$redir = ((strpos($fp, 'http') === FALSE) ? e_BASE : '').$tp -> replaceConstants($fp, TRUE, FALSE);
		//				$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Redirect active",$redir,FALSE,FALSE);
					}
					break;
				}
			}
		}



		if (strstr($_SERVER['SERVER_SOFTWARE'], "Apache")) 
		{
			header("Location: ".$redir);
			exit();
		} 
		else 
		{
			echo "<script type='text/javascript'>document.location.href='{$redir}'</script>\n";
		}
	}


	// Function called to log the reason for a failed login. Currently always returns false - could return some other value
	function invalidLogin($username,$reason, $fip = '?', $extra_text = '')
	{
		global $sql, $pref, $tp, $e107;
	  
		$doCheck = FALSE;			// Flag set if need to ban check
		switch ($reason)
		{
			case LOGIN_ABORT :		// alt_auth reject
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->genNote($fip,$username,'Alt_auth: '.LAN_LOGIN_14);
			  $this->logNote('LAN_ROLL_LOG_04','Alt_Auth: '.$username);
			  $doCheck = TRUE;
			  break;
			case LOGIN_DB_ERROR :	// alt_auth couldn't add valid user
				define("LOGINMESSAGE", LAN_LOGIN_31."<br /><br />");
				$this->genNote($fip,$username,'Alt_auth: '.LAN_LOGIN_30);
//				$this->logNote('LAN_ROLL_LOG_04','Alt_Auth: '.$username);	// Added in alt_auth login
				$doCheck = TRUE;
				break;
			case LOGIN_BAD_PW :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_03',$username);
			  break;
			case LOGIN_CHAP_FAIL :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_03','CHAP: '.$username);
			  break;
			case LOGIN_BAD_USER :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->genNote($fip,$username,LAN_LOGIN_14);
			  $this->logNote('LAN_ROLL_LOG_04',$username);
			  $doCheck = TRUE;
			  break;
			case LOGIN_BAD_USERNAME :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_08',$username);
			  break;
			case LOGIN_MULTIPLE :
			  define("LOGINMESSAGE", LAN_LOGIN_24."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_07',"U: {$username} IP: {$fip}");
			  $this->genNote($fip,$username,LAN_LOGIN_16);
			  $doCheck = TRUE;
			  break;
			case LOGIN_BAD_CODE :
			  define("LOGINMESSAGE", LAN_LOGIN_23."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_02',$username);
			  break;
			case LOGIN_NOT_ACTIVATED :
			  define("LOGINMESSAGE", LAN_LOGIN_22."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_05',$username);
			  $this->genNote($fip,$username,LAN_LOGIN_27);
			  $doCheck = TRUE;
			  break;
			case LOGIN_BLANK_FIELD :
			  define("LOGINMESSAGE", LAN_LOGIN_20."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_01',$username);
			  break;
			case LOGIN_BAD_TRIGGER :
			  define("LOGINMESSAGE", $extra_text."<br /><br />");
			  $this->logNote('LAN_ROLL_LOG_06',$username);
			  break;
			case LOGIN_BANNED :
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");		// Just give 'incorrect login' message
			  $this->genNote($fip,$username,LAN_LOGIN_25);
			  $this->logNote('LAN_ROLL_LOG_09',$username);
			  break;
			default :		// Something's gone wrong!
			  define("LOGINMESSAGE", LAN_LOGIN_21."<br /><br />");		// Just give 'incorrect login' message
			  $this->genNote($fip,$username,LAN_LOGIN_26);
			  $this->logNote('LAN_ROLL_LOG_10',$username);
		}

		if ($doCheck)
		{		// See if ban required (formerly the checkibr() function)
			if($pref['autoban'] == 1 || $pref['autoban'] == 3)
			{ // Flood + Login or Login Only.
				$fails = $sql -> db_Count("generic", "(*)", "WHERE gen_ip='{$fip}' AND gen_type='failed_login' ");
				if($fails > 10) 
				{
					$e107->add_ban(4,LAN_LOGIN_18,$fip,1);
					$sql -> db_Insert("generic", "0, 'auto_banned', '".time()."', 0, '{$fip}', '{$extra_text}', '".LAN_LOGIN_20.": ".$tp -> toDB($username).", ".LAN_LOGIN_17.": ".md5($ouserpass)."' ");
				}
			}
		}
		return FALSE;		// Passed back to signal failed login
	}


	// Make a note of an event in the rolling log
	function logNote($title,$text)
	{
		global $admin_log;
		$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"LOGIN",$title,$text,FALSE,LOG_TO_ROLLING);
	}


	// Make a note of an event in the 'generic' table
	function genNote($fip,$username,$msg1)
	{
		global $sql, $tp;
		$sql -> db_Insert("generic", "0, 'failed_login', '".time()."', 0, '{$fip}', 0, '".$msg1." ::: ".LAN_LOGIN_1.": ".$tp -> toDB($username)."'");
	}


	// This is called to update user settings from a XUP file - usually because the file name has changed.
	// $user_xup has the new file name
	function update_xup($user_id, $user_xup = "") 
	{
		global $sql, $tp;
		$user_id = intval($user_id);		// Should already be an integer - but just in case...
		if($user_xup) 
		{
			require_once(e_HANDLER.'xml_class.php');
			$xml = new xmlClass;
			$xupData = array();
			if($rawData = $xml -> getRemoteFile($user_xup)) 
			{
				preg_match_all("#\<meta name=\"(.*?)\" content=\"(.*?)\" \/\>#si", $rawData, $match);
				$count = 0;
				foreach($match[1] as $value) 
				{	// Process all the data into an array
					$xupData[$value] = $tp -> toDB($match[2][$count]);
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
					$sql -> db_Update('user', $new_values);
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
						$ueList['data'][$keydb] = $tp->toDB($xupData[$keyxup]);
					}
				}
				if (count($ueList['data']))
				{
					$usere->addFieldTypes($ueList);
					$ueList['WHERE'] = 'user_extended_id = '.$user_id;
					$sql -> db_Select_gen('INSERT INTO #user_extended (user_extended_id) values ('.$user_id.')');
					$sql -> db_Update('user_extended', $ueList);
				}
			}
		}
	}
}

?>