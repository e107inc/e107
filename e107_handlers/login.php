<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/mysql_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.$language."/lan_login.php");
class userlogin{
	function userlogin($username, $userpass, $autologin){
		/* Constructor
		# Class called when user attempts to log in
		#
		# - parameters #1:		string $username, $_POSTED user name
		# - parameters #2:		string $userpass, $_POSTED user password
		# - return				boolean
		# - scope					public
		*/
		global $pref;
		$sql = new db;


		if($username != "" && $userpass != ""){
			$userpass = md5($userpass);
			if(!$sql -> db_Select("user",  "*", "user_name='$username' ")){
				define("LOGINMESSAGE", LAN_300."<br /><br />");
				return FALSE;
			}else if(!$sql -> db_Select("user", "*", "user_name='$username' AND user_password='$userpass'")){
				define("LOGINMESSAGE", LAN_301."<br /><br />");
				return FALSE;
			}else if(!$sql -> db_Select("user", "*", "user_name='$username' AND user_password='$userpass' AND user_ban!=2 ")){
				define("LOGINMESSAGE", LAN_302."<br /><br />");
				return FALSE;
			}else{
				list($user_id) = $sql-> db_Fetch();

				if($pref['user_tracking'] == "session"){
					$_SESSION[$pref['cookie_name']] = $user_id.".".$userpass;
				}else{
					if($autologin == 1){
						cookie($pref['cookie_name'], $user_id.".".$userpass, ( time()+3600*24*30));
					}else{
						cookie($pref['cookie_name'], $user_id.".".$userpass, ( time()+3600*3));
					}
				}

				$redir = (e_QUERY ? e_SELF."?".e_QUERY : e_SELF);
				echo "<script type='text/javascript'>document.location.href='$redir'</script>\n";

/*
				if(!eregi("Apache", $_SERVER['SERVER_SOFTWARE'])){
					header("Refresh: 0; URL: ".$redir);
					exit;
				}else{
					header("Location: ".$redir);
					exit;
				}
*/
			}
		}else{
			define("LOGINMESSAGE", LAN_27."<br /><br />");
			return FALSE;
		}
	}
}
?>