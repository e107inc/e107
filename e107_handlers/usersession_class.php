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
|     $Revision: 1.4 $
|     $Date: 2005-01-16 17:21:33 $
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
	var $_RawPermissions;
	var $_Permissions   = array();
	var $SuperAdmin   = false;
	var $SessionData  = array();
	var $IsUser = false;

	var $UserIP;

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

		$this->GetIP();
	}

	function UserSessionStart(){
		print_r($_POST);
		
		if($_POST['username'] && $_POST['userpass']){
			if(ini_get('magic_quotes_gpc' != 1)){
				$_POST['username'] = addslashes($_POST['username']);
				$_POST['userpass'] = addslashes($_POST['userpass']);
			}
			$_POST['autologin'] = intval($_POST['autologin']);
			$this->LoginUser(USERLOGIN_TYPE_POST, $_POST['username'], $_POST['userpass'], false, $_POST['autologin']);
		} elseif ($this->_UserTrackingType == 'session' && $_COOKIE[$this->$_SessionName]){
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
		if($this->_LoginResult != LOGINRESULT_OK){
			$this->AnonUser();
		}
		$this->CompatabiltyMode();
	}

	function LoginUser($LoginType = false, $UserName = false, $UserPassword = false, $UserID = false, $AutoLogin = false){
		global $sql;
		$RetrieveFields = '*';
		switch ($LoginType) {
			case USERLOGIN_TYPE_COOKIE:
			if(!$sql->db_Select('user', $RetrieveFields, '`user_id` = \''.$UserID.'\' AND md5(`user_password`) = \''.$UserPassword.'\''/*, 'default', true*/)){
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
			if(!$sql->db_Select('user', $RetrieveFields, '`user_name` = \''.$UserName.'\' AND `user_password` = \''.$UserPassword.'\'', 'default', true)){
				$this->_LoginResult = LOGINRESULT_BADUSERPASS;
			} else {
				$row = $sql->db_Fetch();
				$this->IsUser = true;
				$this->_LoginResult = LOGINRESULT_OK;
				$this->ExtractDetails($row);
				if($AutoLogin == true){
					header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
					setcookie($this->_CookieName, $row['user_id'].'.'.md5($UserPassword), (time() + 3600 * 24 * 30));
					$_COOKIE[$this->_CookieName] = $row['user_id'].'.'.md5($UserPassword);
				} else {
					header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
					setcookie($this->_CookieName, $row['user_id'].'.'.$UserPassword);
					$_COOKIE[$this->_CookieName] = $row['user_id'].'.'.md5($UserPassword);
				}
				if ($this->_UserTrackingType == 'session'){
					session_start();
				}
			}
			break;
			if ($this->_LoginResult == LOGINRESULT_INVALIDCOOKIE) {
				header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
				setcookie($pref['cookie_name'], '', (time()-2592000));
				unset($_COOKIE[$this->_CookieName]);
			}
		}
	}

	function ExtractDetails($MySQL_Row){
		global $user_pref, $pref;
		if($MySQL_Row['user_ban'] == 1){
			exit();
		}
		$this->UserDetails['Name'] = $MySQL_Row['user_name'];
		$this->UserDetails['ID'] = $MySQL_Row['user_id'];
		$this->UserDetails['Email'] = $MySQL_Row['user_email'];
		$this->UserDetails['Class'] = $MySQL_Row['user_class'];
		$this->UserDetails['Viewed'] = $MySQL_Row['user_viewed'];
		$this->UserDetails['Image'] = $MySQL_Row['user_image'];
		$this->UserTimes['PasswordChange'] = $MySQL_Row['user_pwchange'];
		$this->UserTimes['LastVisit'] = $MySQL_Row['user_lastvisit'];
		$this->UserTimes['CurrentVisit'] = $MySQL_Row['user_currentvisit'];
		$this->UserTimes['Join'] = $MySQL_Row['user_join'];
		$this->UserTimes['Lastpost'] = $MySQL_Row['user_lastpost'];
		$this->UserPrefs = unserialize($MySQL_Row['user_prefs']);
		$this->_UserSession = $MySQL_Row['user_sess'];
		if($MySQL_Row['user_admin'] == 1){
			$this->UserIsAdmin = true;
			$this->_RawPermissions = $MySQL_Row['user_perms'];
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
		if ($this->UserTimes['CurrentVisit'] + 3600 < time()) {
			$this->UserTimes['LastVisit'] = $this->UserTimes['CurrentVisit'];
			$this->UserTimes['CurrentVisit'] = time();
			$sql->db_Update('user', "user_visits = user_visits + 1, user_lastvisit = '{$this->UserTimes['LastVisit']}', user_currentvisit='{$this->UserTimes['CurrentVisit']}', user_viewed='' WHERE user_id='{$this->UserDetails['ID']}'");
		}
		if (isset($_POST['settheme'])) {
			$this->UserPrefs['sitetheme'] = ($pref['sitetheme'] == $_POST['sitetheme'] ? '' : $_POST['sitetheme']);
			$user_pref = $this->UserPrefs;
			save_prefs('user', $this->UserDetails['ID']);
		}
		$user_pref = $this->UserPrefs;
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

	function CompatabiltyMode(){
		if($this->IsUser == true){
			define("USERID", $this->UserDetails['ID']);
			define("USERNAME", $this->UserDetails['Name']);
			define("USER", TRUE);
			define("USERCLASS", $this->UserDetails['Class']);
			define("USERVIEWED", $this->UserDetails['Viewed']);
			define("USERIMAGE", $this->UserDetails['Image']);
			define("USERSESS", $this->_UserSession);

			define("USERTHEME", ($this->UserPrefs['sitetheme'] && file_exists(e_THEME.$this->UserPrefs['sitetheme'].'/theme.php') ? $this->UserPrefs['sitetheme'] : false));

			if($this->UserIsAdmin == true){
				define("ADMIN", TRUE);
				define("ADMINID", $this->UserDetails['ID']);
				define("ADMINNAME", $this->UserDetails['Name']);
				define("ADMINPERMS", $this->_RawPermissions);
				define("ADMINEMAIL", $this->UserDetails['Email']);
				define("ADMINPWCHANGE", $this->UserTimes['PasswordChange']);
			} else {
				define("ADMIN", FALSE);
			}
		} else {
			define("USER", FALSE);
			define("USERTHEME", FALSE);
			define("ADMIN", FALSE);
			define("GUEST", TRUE);
		}
	}

	function GetIP() {
		if(!$this->UserIP){
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip = $_SERVER['REMOTE_ADDR'];
				if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3)) {
					$ip2 = array('/^0\./', '/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.16\..*/', '/^10..*/', '/^224..*/', '/^240..*/');
					$ip = preg_replace($ip2, $ip, $ip3[1]);
				}
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			if ($ip == "") {
				$ip = "x.x.x.x";
			}
			$this->UserIP = $ip;
		}
		return $this->UserIP;
	}
}

?>