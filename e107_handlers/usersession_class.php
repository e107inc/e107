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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/usersession_class.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-15 19:24:28 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

class eUserSession {

	var $_UserTrackingType;
	var $_CookieName;
	var $_SessionID;
	var $_SessionName;
	var $_LoginResult;

	var $UserDetails  = array();
	var $UserTimes    = array();
	var $UserPrefs    = array();
	var $UserIsAdmin  = false;
	var $_Permissions   = array();
	var $SuperAdmin   = false;
	var $SessionData  = array();
	var $IsUser = false;

	function eUserSession(){
		global $pref;

		// Login types operators
		define('USERLOGIN_TYPE_COOKIE', 0);
		define('USERLOGIN_TYPE_SESSION', 1);
		define('USERLOGIN_TYPE_POST', 2);

		// badlogin operators
		define('LOGINRESULT_OK', 0);
		define('LOGINRESULT_INVALIDCOOKIE', 1);
		define('LOGINRESULT_INVALIDSESSION', 2);
		define('LOGINRESULT_INVALIDSESSIONCOOKIE', 3);
		define('LOGINRESULT_BADUSERPASS', 4);
		define('LOGINRESULT_NOTLOGGEDIN', 5);

		// Session handler options - adjust to taste
		ini_set('session.auto_start', 0);
		ini_set('session.serialize_handler', 'php');
		ini_set('session.cookie_lifetime', 0);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('url_rewriter.tags', '');
		ini_set('session.use_trans_sid', 0);

		$this->_SessionName = session_name();
		$this->_UserTrackingType = $pref['user_tracking'];
		$this->_CookieName = $pref['cookie_name'];
	}

	function UserSessionStart(){
		if($_POST['username'] && $_POST['userpass']){
			if(ini_get('magic_quotes_gpc' != 1)){
				$_POST['username'] = addslashes($_POST['username']);
				$_POST['userpass'] = addslashes($_POST['userpass']);
			}
			$_POST['autologin'] = intval($_POST['autologin']);
			$this->LoginUser(USERLOGIN_TYPE_POST, $_POST['username'], $_POST['userpass'], false, $_POST['autologin']);
		} elseif ($this->_UserTrackingType == 'session' && $_COOKIE[$this->$_SessionName]){
			session_start();
		} elseif ($this->_UserTrackingType == 'cookie' && isset($_COOKIE[$this->_CookieName])){
			$Cookie = explode('.', $_COOKIE[$this->_CookieName]);
			if (count($Cookie) != 2) {
				$this->_LoginResult = LOGINRESULT_INVALIDCOOKIE;
			} elseif(preg_match('/^[A-Fa-f0-9]{32}$/', $Cookie[1]) && intval($Cookie[0]) > 0){
				$this->LoginUser(USERLOGIN_TYPE_COOKIE, false, $Cookie[1], $Cookie[0]);
			} else {
				$this->_LoginResult = LOGINRESULT_INVALIDCOOKIE;
			}
		} else {
			$this->AnonUser();
			$this->_LoginResult = LOGINRESULT_NOTLOGGEDIN;
		}
	}

	function LoginUser($LoginType = false, $UserName = false, $UserPassword = false, $UserID = false, $AutoLogin = false){
		global $sql;
		$RetrieveFields = '`user_name`, `user_id`, `user_email`, `user_lastvisit`, `user_currentvisit`, `user_join`, `user_lastpost`, `user_prefs`, `user_admin`, `user_perms`';
		switch ($LoginType) {
			case USERLOGIN_TYPE_COOKIE:
				if(!$sql->db_Select('users', $RetrieveFields, '`user_id` = \''.$UserID.'\' AND md5(`user_password`) = \''.$UserPassword.'\'', 'default', true)){
					$this->_LoginResult = LOGINRESULT_INVALIDCOOKIE;
				} else {
					$row = $sql->db_Fetch();
					$this->ExtractDetails($row);
					$this->IsUser = true;
					$this->_LoginResult = LOGINRESULT_OK;
				}
			break;
			case USERLOGIN_TYPE_SESSION:
				echo "Session Handling Not Fully Implemented Yet!";
			break;
			case USERLOGIN_TYPE_POST:
				$UserPassword = md5($UserPassword);
				if(!$sql->db_Select('users', $RetrieveFields, '`user_name` = \''.$UserName.'\' AND `user_password` = \''.$UserPassword.'\'')){
					$this->_LoginResult = LOGINRESULT_BADUSERPASS;
				} else {
					$row = $sql->db_Fetch();
					$this->IsUser = true;
					$this->_LoginResult = LOGINRESULT_OK;
					$this->ExtractDetails($row);
					if($AutoLogin == true){
						header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
						setcookie($this->_CookieName, $row['user_id'].'.'.md5($UserPassword), (time() + 3600 * 24 * 30));
						$_COOKIE[$this->_CookieName] = $row['user_id'].'.'.md5($UserPassword);
					} else {
						header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
						setcookie($this->_CookieName, $row['user_id'].'.'.$UserPassword);
						$_COOKIE[$this->_CookieName] = $row['user_id'].'.'.md5($UserPassword);
					}
					if ($this->_UserTrackingType == 'session'){
						session_start();
					}
				}
			break;
			if ($this->_LoginResult == LOGINRESULT_INVALIDCOOKIE) {
				setcookie($pref['cookie_name'], '', (time()-2592000));
			}
		}
	}

	function ExtractDetails($MySQL_Row){
		$this->UserDetails['Name'] = $MySQL_Row['user_name'];
		$this->UserDetails['ID'] = $MySQL_Row['user_id'];
		$this->UserDetails['Email'] = $MySQL_Row['user_email'];
		$this->UserTimes['LastVisit'] = $MySQL_Row['user_lastvisit'];
		$this->UserTimes['CurrentVisit'] = $MySQL_Row['user_currentvisit'];
		$this->UserTimes['Join'] = $MySQL_Row['user_join'];
		$this->UserTimes['Lastpost'] = $MySQL_Row['user_lastpost'];
		$this->UserPrefs = unserialize($MySQL_Row['user_prefs']);
		if($MySQL_Row['user_admin'] == 1){
			$this->UserIsAdmin = true;
			$Perms = explode('.', $MySQL_Row['user_perms']);
			$pTotal = count($Perms) - 1;
			if($Perms[$pTotal] == ''){
				unset($Perms[$pTotal]);
			}
			if($Perms[0] == '0'){
				$this->SuperAdmin = true;
			} else {
				$this->_Permissions = $Perms;
			}
		}
	}

	function AnonUser(){
		$this->UserDetails['Name'] = 'Anonymous';
		$this->UserDetails['ID'] = 0;
		$this->UserDetails['Email'] = '';
		$this->UserTimes['LastVisit'] = time();
		$this->UserTimes['CurrentVisit'] = time();
		$this->UserTimes['Join'] = time();
		$this->UserTimes['Lastpost'] = time();
		$this->UserPrefs = array();
		$this->UserIsAdmin = false;
		$this->SuperAdmin = false;
		$this->_Permissions = array();
	}
}

?>