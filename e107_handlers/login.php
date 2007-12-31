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
|     $Revision: 1.11 $
|     $Date: 2007-12-31 14:45:50 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_login.php");

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
		global $pref, $e_event, $sql, $e107, $tp;
		global $admin_log;

		$username = trim($username);
		$userpass = trim($userpass);
		if($username == "" || $userpass == "")
		{
			define("LOGINMESSAGE", LAN_27."<br /><br />");
			return FALSE;
		}

	 	if(!is_object($sql)){
		$sql = new db;
		}

		$fip = $e107->getip();
//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'IP: '.$fip,FALSE,LOG_TO_ROLLING);
		$e107->check_ban("banlist_ip='{$fip}' ",FALSE);
//		if($sql -> db_Select("banlist", "*", "banlist_ip='{$fip}' ")) {	exit;}

		$autologin = intval($autologin);

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
		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
		$username = substr($username, 0, 30);
		$ouserpass = $userpass;
		$userpass = md5($ouserpass);

		// This is only required for upgrades and only for those not using utf-8 to begin with..
		if(isset($pref['utf-compatmode']) && (CHARSET == "utf-8" || CHARSET == "UTF-8")){
			$username = utf8_decode($username);
			$userpass = md5(utf8_decode($ouserpass));
		}

//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'Doing final checks',FALSE,LOG_TO_ROLLING);
		if (!$sql->db_Select("user", "*", "user_loginname = '".$tp -> toDB($username)."'")) 
		{	// Invalid user
			define("LOGINMESSAGE", LAN_300."<br /><br />");
			$sql -> db_Insert("generic", "0, 'failed_login', '".time()."', 0, '{$fip}', 0, '".LAN_LOGIN_14." ::: ".LAN_LOGIN_1.": ".$tp -> toDB($username)."'");
			$this -> checkibr($fip);
			return FALSE;
		}
		else if(!$sql->db_Select("user", "*", "user_loginname = '".$tp -> toDB($username)."' AND user_password = '{$userpass}'")) 
		{	// Invalid user/password combination
			define("LOGINMESSAGE", LAN_300."<br /><br />");
			return FALSE;
		}
		else if(!$sql->db_Select("user", "*", "user_loginname = '".$tp -> toDB($username)."' AND user_password = '{$userpass}' AND user_ban!=2 ")) 
		{	// Banned user
		  define("LOGINMESSAGE", LAN_302."<br /><br />");
//		  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'User is banned: '.$tp -> toDB($username),FALSE,LOG_TO_ROLLING);
		  $sql -> db_Insert("generic", "0, 'failed_login', '".time()."', 0, '{$fip}', 0, '".LAN_LOGIN_15." ::: ".LAN_LOGIN_1.": ".$tp -> toDB($username)."'");
		  $this -> checkibr($fip);
		  return FALSE;
		} 
		else 
		{	// User is OK as far as core is concerned
//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User login",'User passed basics',FALSE,LOG_TO_ROLLING);
		  $ret = $e_event->trigger("preuserlogin", $username);
		  if ($ret!='') 
		  {
			define("LOGINMESSAGE", $ret."<br /><br />");
			return FALSE;
		  } 
		  else 
		  {	// Trigger events happy as well
			$lode = $sql -> db_Fetch();		// Get user info
			$user_id = $lode['user_id'];
			$user_name = $lode['user_name'];
			$user_xup = $lode['user_xup'];

			/* restrict more than one person logging in using same us/pw */
			if($pref['disallowMultiLogin']) 
			{
			  if($sql -> db_Select("online", "online_ip", "online_user_id='".$user_id.".".$user_name."'")) 
			  {
				define("LOGINMESSAGE", LAN_304."<br /><br />");
				$sql -> db_Insert("generic", "0, 'failed_login', '".time()."', 0, '$fip', '$user_id', '".LAN_LOGIN_16." ::: ".LAN_LOGIN_1.": ".$tp -> toDB($username).", ".LAN_LOGIN_17.": ".md5($ouserpass)."' ");
				$this -> checkibr($fip);
				return FALSE;
				}
			  }

			  $cookieval = $user_id.".".md5($userpass);
			  if($user_xup) 
			  {
				$this->update_xup($user_id, $user_xup);
			  }

			  if ($pref['user_tracking'] == "session") 
			  {
				$_SESSION[$pref['cookie_name']] = $cookieval;
			  } 
			  else 
			  {
				if ($autologin == 1) 
				{
				  cookie($pref['cookie_name'], $cookieval, (time() + 3600 * 24 * 30));
				} 
				else 
				{
				  cookie($pref['cookie_name'], $cookieval);
				}
			  }
			  
			  // User login definitely accepted here


			  // Calculate class membership - needed for a couple of things
			  $class_list = explode(',',$lode['user_class']);
			  if ($lode['user_admin'] && strlen($lode['user_perms']))
			  {
			    $class_list[] = e_UC_ADMIN;
				if (strpos($lode['user_perms'],'0') === 0)
				{
				  $class_list[] = e_UC_MAINADMIN;
				}
			  }
			  $class_list[] = e_UC_MEMBER;
			  $class_list[] = e_UC_PUBLIC;

			  $user_logging_opts = array_flip(explode(',',varset($pref['user_audit_opts'],'')));
			  if (isset($user_logging_opts[USER_AUDIT_LOGIN]) && in_array(varset($pref['user_audit_class'],''),$class_list))
			  {  // Need to note in user audit trail
			    $admin_log->user_audit(USER_AUDIT_LOGIN,'', $user_id,$user_name);
			  }

			  $edata_li = array("user_id" => $user_id, "user_name" => $username, 'class_list' = implode(',',$class_list));
			  $e_event->trigger("login", $edata_li);
			  $redir = (e_QUERY ? e_SELF."?".e_QUERY : e_SELF);



				if (isset($pref['frontpage_force']) && is_array($pref['frontpage_force'])) 
				{	// See if we're to force a page immediately following login - assumes $pref['frontpage_force'] is an ordered list of rules
					// Problem is that USERCLASS_LIST just contains 'guest' and 'everyone' at this point
				  $lode['user_perms'] = trim($lode['user_perms']);
//				  $log_info = "New user: ".$lode['user_name']."  Class: ".$lode['user_class']."  Admin: ".$lode['user_admin']."  Perms: ".$lode['user_perms'];
//				  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Login Start",$log_info,FALSE,FALSE);
//				  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","New User class",implode(',',$class_list),FALSE,FALSE);
				  foreach ($pref['frontpage_force'] as $fk=>$fp)
				  {
					if (in_array($fk,$class_list))
					{  // We've found the entry of interest
					  if (strlen($fp))
					  {
						$redir = ((strpos($fp, 'http') === FALSE) ? e_BASE : '').$tp -> replaceConstants($fp, TRUE, FALSE);
//						$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Redirect active",$redir,FALSE,FALSE);
					  }
					  break;
					}
				  }
				}


				if (strstr($_SERVER['SERVER_SOFTWARE'], "Apache")) {
					header("Location: ".$redir);
					exit;
				} else {
					echo "<script type='text/javascript'>document.location.href='{$redir}'</script>\n";
				}
			}
		}
	}

	function checkibr($fip) 
	{
	  global $sql, $pref, $tp, $e107;
	  if($pref['autoban'] == 1 || $pref['autoban'] == 3)
	  { // Flood + Login or Login Only.
		$fails = $sql -> db_Count("generic", "(*)", "WHERE gen_ip='{$fip}' AND gen_type='failed_login' ");
		if($fails > 10) 
		{
		  $e107->add_ban(4,LAN_LOGIN_18,$fip,1);
//				$sql -> db_Insert("banlist", "'$fip', '1', '".LAN_LOGIN_18."' ");
		  $sql -> db_Insert("generic", "0, 'auto_banned', '".time()."', 0, '$fip', '$user_id', '".LAN_LOGIN_20.": ".$tp -> toDB($username).", ".LAN_LOGIN_17.": ".md5($ouserpass)."' ");
		}
	  }
	}

	// This is called to update user settings from a XUP file - usually because the file name has changed.
	// $user_xup has the new file name
	function update_xup($user_id, $user_xup = "") 
	{
		global $sql, $tp;
		if($user_xup) 
		{
			require_once(e_HANDLER."xml_class.php");
			$xml = new parseXml;
			if($rawData = $xml -> getRemoteXmlFile($user_xup)) 
			{
				preg_match_all("#\<meta name=\"(.*?)\" content=\"(.*?)\" \/\>#si", $rawData, $match);
				$count = 0;
				foreach($match[1] as $value) 
				{
					$$value = $tp -> toDB($match[2][$count]);
					$count++;
				}

				// List of fields in main user record, and their corresponding XUP fields
				$main_fields = array('user_realname' => 'FN',
							'user_hideemail'=>'EMAILHIDE', 
							'user_signature'=>'SIG', 
							'user_sess'=>'PHOTO', 
							'user_image'=>'AV', 
							'user_timezone'=>'TZ');
				
				$new_values = array();
				foreach ($main_fields as $f => $v)
				{
				  if (isset($$v) && $$v)
				  {
				    $new_values[$f] = $$v;
				  }
				}

				// Use of db_updateArray() ensures only non-empty fields are changed
				$sql -> db_UpdateArray("user", $new_values, "WHERE user_id='".intval($user_id)."'");
//				$sql -> db_Update("user", "user_realname='{$FN}', user_hideemail='{$EMAILHIDE}', user_signature='{$SIG}', user_sess='{$PHOTO}', user_image='{$AV}', user_timezone='{$TZ}' WHERE user_id='".intval($user_id)."'");

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
