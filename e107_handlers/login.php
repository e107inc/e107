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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/login.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_login.php");

class userlogin {
	function userlogin($username, $userpass, $autologin) 
	{
		/* Constructor
		# Class called when user attempts to log in
		#
		# - parameters #1:                string $username, $_POSTED user name
		# - parameters #2:                string $userpass, $_POSTED user password
		# - return                                boolean
		# - scope                                        public
		*/
		global $pref, $e_event, $sql, $e107, $tp;

		$username = trim($username);
		$userpass = trim($userpass);
		if($username == "" || $userpass == "")
		{
			define("LOGINMESSAGE", LAN_27."<br /><br />");
			return FALSE;
		}

	 	if(!is_object($sql))
		{
			$sql = new db;
		}

		$fip = $e107->getip();
		if($sql -> db_Select("banlist", "*", "banlist_ip='{$fip}' ")) 
		{
			exit;
		}

		$autologin = intval($autologin);

		if ($pref['auth_method'] && $pref['auth_method'] != "e107") 
		{
			$auth_file = e_PLUGIN."alt_auth/".$pref['auth_method']."_auth.php";
			if (file_exists($auth_file)) {
				require_once(e_PLUGIN."alt_auth/alt_auth_login_class.php");
				$result = new alt_login($pref['auth_method'], $username, $userpass);
			}
		}

		if ($pref['logcode'] && extension_loaded("gd")) 
		{
			require_once(e_HANDLER."secure_img_handler.php");
			$sec_img = new secure_image;
			if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify'])) 
			{
				define("LOGINMESSAGE", LAN_303."<br /><br />");
				return FALSE;
			}
		}
		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
		if (strlen($username) > varset($pref['loginname_maxlength'],30))
		{	// Login name too long - don't log as an error
			$this->checkibr($fip);
			return FALSE;
		}
		$ouserpass = $userpass;
		$userpass = md5($ouserpass);

		// This is only required for upgrades and only for those not using utf-8 to begin with..
		if(isset($pref['utf-compatmode']) && (CHARSET == "utf-8" || CHARSET == "UTF-8"))
		{
			$username = utf8_decode($username);
			$userpass = md5(utf8_decode($ouserpass));
		}

		if (!$sql->db_Select("user", "*", "user_loginname = '".$tp -> toDB($username)."'")) 
		{
			define("LOGINMESSAGE", LAN_300."<br /><br />");
			$sql->db_Insert('generic', "0, 'failed_login', '".time()."', 0, '{$fip}', 0, '".$tp->toDB(LAN_LOGIN_14.' ::: '.LAN_LOGIN_1.': '.$username)."' ");
			$this->checkibr($fip);
			return FALSE;
		}
		else if(!$sql->db_Select("user", "*", "user_loginname = '".$tp -> toDB($username)."' AND user_password = '{$userpass}'")) {
			define("LOGINMESSAGE", LAN_300."<br /><br />");
			return FALSE;
		}
		else if(!$sql->db_Select("user", "*", "user_loginname = '".$tp -> toDB($username)."' AND user_password = '{$userpass}' AND user_ban!=2 ")) {
			define("LOGINMESSAGE", LAN_302."<br /><br />");
               	$sql->db_Insert('generic', "0, 'failed_login', '".time()."', 0, '{$fip}', 0, '".$tp->toDB(LAN_LOGIN_15.' ::: '.LAN_LOGIN_1.': '.$username)."' ");
				$this -> checkibr($fip);
			return FALSE;
		} else {
			$ret = $e_event->trigger("preuserlogin", $username);
			if ($ret!='') {
				define("LOGINMESSAGE", $ret."<br /><br />");
				return FALSE;
			} else {
				$lode = $sql -> db_Fetch();
				$user_id = $lode['user_id'];
				$user_name = $lode['user_name'];
				$user_xup = $lode['user_xup'];

				/* restrict more than one person logging in using same us/pw */
				if($pref['disallowMultiLogin']) {
					if($sql -> db_Select("online", "online_ip", "online_user_id='".$user_id.".".$user_name."'")) {
						define("LOGINMESSAGE", LAN_304."<br /><br />");
						$sql->db_Insert('generic', "0, 'failed_login', '".time()."', 0, '{$fip}', '$user_id', '".$tp->toDB(LAN_LOGIN_16.' ::: '.LAN_LOGIN_1.': '.$username.', '.LAN_LOGIN_17.': ').md5($ouserpass)."' ");
						$this -> checkibr($fip);
						return FALSE;
					}
				}

				$cookieval = $user_id.".".md5($userpass);
				if($user_xup) {
					$this->update_xup($user_id, $user_xup);
				}

				if ($pref['user_tracking'] == "session") {
					$_SESSION[$pref['cookie_name']] = $cookieval;
				} else {
					if ($autologin == 1) {
						cookie($pref['cookie_name'], $cookieval, (time() + 3600 * 24 * 30));
					} else {
						cookie($pref['cookie_name'], $cookieval);
					}
				}
				$edata_li = array("user_id" => $user_id, "user_name" => $username);
				$e_event->trigger("login", $edata_li);
				$redir = str_replace('&amp;','&',(e_QUERY ? e_SELF."?".e_QUERY : e_SELF));

				header("Location: ".$redir);
				exit;
			}
		}
	}

	function checkibr($fip) 
	{
		global $sql, $pref, $tp;
		if($pref['autoban'] == 1 || $pref['autoban'] == 3)
		{ // Flood + Login or Login Only.
	   		$fails = $sql -> db_Count("generic", "(*)", "WHERE gen_ip='{$fip}' AND gen_type='failed_login' ");
			if($fails > 10) 
			{
				$sql -> db_Insert("banlist", "'$fip', '1', '".LAN_LOGIN_18."' ");
		   		$sql->db_Insert('generic', "0, 'auto_banned', '".time()."', 0, '{$fip}', 0, '".$tp->toDB(LAN_LOGIN_20.': '.$username.', '.LAN_LOGIN_17.': ').md5($ouserpass)."' ");
			}
		}
	}

	function update_xup($user_id, $user_xup = "") {
		global $sql, $tp;
		if($user_xup) {
			require_once(e_HANDLER."xml_class.php");
			$xml = new parseXml;
			if($rawData = $xml -> getRemoteXmlFile($user_xup)) {
				preg_match_all("#\<meta name=\"(.*?)\" content=\"(.*?)\" \/\>#si", $rawData, $match);
				$count = 0;
				foreach($match[1] as $value) {
					$$value = $tp -> toDB($match[2][$count]);
					$count++;
				}

				$sql -> db_Update("user", "user_login='{$FN}', user_hideemail='{$EMAILHIDE}', user_signature='{$SIG}', user_sess='{$PHOTO}', user_image='{$AV}', user_timezone='{$TZ}' WHERE user_id='".intval($user_id)."'");

				$ue_fields = "";
				$fields = array("URL" => "homepage",
					"ICQ" => "icq",
					"AIM" => "aim",
					"MSN" => "msn",
					"YAHOO" => "yahoo",
					"GEO" => "location",
					"BDAY" => "birthday");
					include_once(e_HANDLER."user_extended_class.php");
					$usere = new e107_user_extended;
					$extList = $usere->user_extended_get_fieldList();
					$extName = array();
					foreach($extList as $ext)
					{
						$extName[] = $ext['user_extended_struct_name'];
					}
					foreach($fields as $keyxup => $keydb)
					{
						if (in_array($keydb, $extName))
						{
							$key = "user_".$keydb;
							$key = $tp->toDB($key);
							$val = $tp->toDB($$keyxup);
							$ue_fields .= ($ue_fields) ? ", " : "";
							$ue_fields .= $key."='".$val."'";
						}
					}
					$sql -> db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('".intval($user_id)."')");
					$sql -> db_Update("user_extended", $ue_fields." WHERE user_extended_id = '".intval($user_id)."'");
			}
		}
	}
}

?>
