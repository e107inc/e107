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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/login.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:52:27 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_login.php");
@include(e_LANGUAGEDIR."English/lan_login.php");
class userlogin {
	function userlogin($username, $userpass, $autologin) {
		/* Constructor
		# Class called when user attempts to log in
		#
		# - parameters #1:                string $username, $_POSTED user name
		# - parameters #2:                string $userpass, $_POSTED user password
		# - return                                boolean
		# - scope                                        public
		*/
		global $pref, $e_event;
		$sql = new db;
		 
		if ($pref['auth_method'] && $pref['auth_method'] != "e107") {
			$auth_file = e_PLUGIN."alt_auth/".$pref['auth_method']."_auth.php";
			if (file_exists($auth_file)) {
				require_once(e_PLUGIN."alt_auth/alt_auth_login_class.php");
				$result = new alt_login($pref['auth_method'], $username, $userpass);
			}
		}
		 
		if ($pref['logcode'] && extension_loaded("gd")) {
			require_once(e_HANDLER."secure_img_handler.php");
			$sec_img = new secure_image;
			if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify'])) {
				define("LOGINMESSAGE", LAN_303."<br /><br />");
				return FALSE;
			}
		}
		if ($username != "" && $userpass != "") {
			$username = ereg_replace("\sOR\s|\=|\#", "", $username);
			$userpass = md5($userpass);
			if (!$sql->db_Select("user", "*", "user_name='$username' ")) {
				define("LOGINMESSAGE", LAN_300."<br /><br />");
				return FALSE;
			}
			else if(!$sql->db_Select("user", "*", "user_name='$username' AND user_password='$userpass'")) {
				define("LOGINMESSAGE", LAN_301."<br /><br />");
				return FALSE;
			}
			else if(!$sql->db_Select("user", "*", "user_name='$username' AND user_password='$userpass' AND user_ban!=2 ")) {
				define("LOGINMESSAGE", LAN_302."<br /><br />");
				return FALSE;
			} else {
				list($user_id) = $sql->db_Fetch();
				 
				$cookieval = $user_id.".".md5($userpass);
				 
				if ($pref['user_tracking'] == "session") {
					$_SESSION[$pref['cookie_name']] = $cookieval;
				} else {
					if ($autologin == 1) {
						cookie($pref['cookie_name'], $cookieval, (time()+3600 * 24 * 30));
					} else {
						cookie($pref['cookie_name'], $cookieval, (time()+3600 * 3));
					}
				}
				$edata_li = array("user_id" => $user_id, "user_name" => $username);
				$e_event->trigger("login", $edata_li);
				$redir = (e_QUERY ? e_SELF."?".e_QUERY : e_SELF);
				if (strstr($_SERVER['SERVER_SOFTWARE'], "Apache")) {
					header("Location: ".$redir);
					exit;
				} else {
					echo "<script type='text/javascript'>document.location.href='$redir'</script>\n";
				}
			}
		} else {
			define("LOGINMESSAGE", LAN_27."<br /><br />");
			return FALSE;
		}
	}
}
?>