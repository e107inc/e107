<?php
$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
if($use_imagecode){
	require_once(e_HANDLER."secure_img_handler.php");
	$sec_img = new secure_image;
}

$text = "";
	if(USER == TRUE || ADMIN == TRUE){
		list($uid, $upw) = ($_COOKIE[$pref['cookie_name']] ? explode(".", $_COOKIE[$pref['cookie_name']]) : explode(".", $_SESSION[$pref['cookie_name']]));
		$sql = new db;
		if($sql -> db_Select("user", "*", "user_id='$uid' AND md5(user_password)='$upw'")){
			if(ADMIN == TRUE){
				$adminfpage = (!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php");
				$text = ($pref['maintainance_flag']==1 ? "<div style='text-align:center'><b>".LOGIN_MENU_L10."</div></b><br />" : "" );
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_ADMIN.$adminfpage."'>".LOGIN_MENU_L11."</a><br />";
			}
			$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."usersettings.php'>".LOGIN_MENU_L12."</a>\n<br />\n<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."user.php?id.".USERID."'>".LOGIN_MENU_L13."</a>\n<br />\n<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."?logout'>".LOGIN_MENU_L8."</a>";		

			if(!$sql -> db_Select("online", "*", "online_ip='$ip' AND online_user_id='0' ")){
				$sql -> db_Delete("online", "online_ip='$ip' AND online_user_id='0' ");
			}

			$time = USERLV;
			$new_news = $sql -> db_Count("news", "(*)", "WHERE news_datestamp>'".$time."' "); if(!$new_news){ $new_news = LOGIN_MENU_L26; }
			$new_comments = $sql -> db_Count("comments", "(*)", "WHERE comment_datestamp>'".$time."' "); if(!$new_comments){ $new_comments = LOGIN_MENU_L26; }
			$new_chat = $sql -> db_Count("chatbox", "(*)", "WHERE cb_datestamp>'".$time."' "); if(!$new_chat){ $new_chat = LOGIN_MENU_L26; }
			$new_forum = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".$time."' "); if(!$new_forum){ $new_forum = LOGIN_MENU_L26; }
			$new_users = $sql -> db_Count("user", "(*)", "WHERE user_join>'".$time."' "); if(!$new_users){ $new_users = LOGIN_MENU_L26; }

			$text .= "<br /><br />\n<span class='smalltext'>\n".LOGIN_MENU_L25." 
			$new_news ".($new_news == 1 ? LOGIN_MENU_L14 : LOGIN_MENU_L15).", 
			$new_chat ".($new_chat == 1 ? LOGIN_MENU_L16 : LOGIN_MENU_L17).", 
			$new_comments ".($new_comments == 1 ? LOGIN_MENU_L18 : LOGIN_MENU_L19).", 
			$new_forum ".($new_forum == 1 ? LOGIN_MENU_L20 : LOGIN_MENU_L21)." ".LOGIN_MENU_L27." 
			$new_users ".($new_users == 1 ? LOGIN_MENU_L22 : LOGIN_MENU_L23).".</span>
			<br /><a href='".e_PLUGIN."list_new/new.php'>".LOGIN_MENU_L24."</a>";
			$caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5." ".USERNAME : LOGIN_MENU_L5." ".USERNAME);
			$ns -> tablerender($caption, $text);
		}else{
			$text = "<div style='text-align:center'>".LOGIN_MENU_L7."<br /><br />\n<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."index.php?logout'>".LOGIN_MENU_L8."</a></div>";
			$ns -> tablerender(LOGIN_MENU_L9, $text);
		}
	}else{
		if(LOGINMESSAGE != ""){
			$text = "<div style='text-align:center'>".LOGINMESSAGE."</div>";
		}
		$text .=  "<div style='text-align:center'>\n<form method='post' action='".e_SELF;
		if(e_QUERY){
			$text .= "?".e_QUERY;
		}

		$text .= "'><p>\n".LOGIN_MENU_L1."<br />\n
		<input class='tbox' type='text' name='username' size='15' value='' maxlength='30' />\n
		<br />\n".LOGIN_MENU_L2."\n<br />\n
		<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />\n\n<br />\n
		";
		if($use_imagecode){
			$text .= "<input type='hidden' name='rand_num' value='".$sec_img -> random_number."'>";
			$text .= $sec_img -> r_image();
			$text .= "<br /><input class='tbox' type='text' name='code_verify' size='15' maxlength='20'><br />";
		}
		$text .= "			
		<input class='button' type='submit' name='userlogin' value='".LOGIN_MENU_L28."' />\n\n
		<br />\n<input type='checkbox' name='autologin' value='1' /> ".LOGIN_MENU_L6;

		if($pref['user_reg']){
			$text .= "<br /><br />";
			if($pref['auth_method'] != "ldap"){
				$text .= "[ <a href='".e_BASE.e_SIGNUP."'>".LOGIN_MENU_L3."</a> ]<br />[ <a href='".e_BASE."fpw.php'> ".LOGIN_MENU_L4."</a> ]";
			}
		}
		$text .= "</p>
		</form>
		</div>";
		$caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5 : LOGIN_MENU_L5);
		$ns -> tablerender($caption, $text);
	}

?>