<?php
$text = "";
if($pref['user_reg'] == 1 || ADMIN == TRUE){

	if(USER == TRUE || ADMIN == TRUE){
		$tmp = ($_COOKIE[$pref['cookie_name']] ? explode(".", $_COOKIE[$pref['cookie_name']]) : explode(".", $_SESSION[$pref['cookie_name']]));
		$uid = $tmp[0]; $upw = $tmp[1];
		$sql = new db;
		if($sql -> db_Select("user", "*", "user_id='$uid' AND user_password='$upw' ")){
			if(ADMIN == TRUE){
				$text = ($pref['maintainance_flag']==1 ? "<div style='text-align:center'><b>".LOGIN_MENU_L10."</div></b><br />" : "" );
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_ADMIN."admin.php'>".LOGIN_MENU_L11."</a><br />";
			}
			$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."usersettings.php'>".LOGIN_MENU_L12."</a>
<br />
<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."user.php?id.".USERID."'>".LOGIN_MENU_L13."</a>
<br />
<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."?logout'>".LOGIN_MENU_L8."</a>";		

			if(!$sql -> db_Select("online", "*", "online_ip='$ip' AND online_user_id='0' ")){
				$sql -> db_Delete("online", "online_ip='$ip' AND online_user_id='0' ");
			}

			$time = USERLV;
			$new_news = $sql -> db_Count("news", "(*)", "WHERE news_datestamp>'".$time."' "); if(!$new_news){ $new_news = LOGIN_MENU_L26; }
			$new_comments = $sql -> db_Count("comments", "(*)", "WHERE comment_datestamp>'".$time."' "); if(!$new_comments){ $new_comments = LOGIN_MENU_L26; }
			$new_chat = $sql -> db_Count("chatbox", "(*)", "WHERE cb_datestamp>'".$time."' "); if(!$new_chat){ $new_chat = LOGIN_MENU_L26; }
			$new_forum = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".$time."' "); if(!$new_forum){ $new_forum = LOGIN_MENU_L26; }
			$new_users = $sql -> db_Count("user", "(*)", "WHERE user_join>'".$time."' "); if(!$new_users){ $new_users = LOGIN_MENU_L26; }


			$text .= "<br /><br />
			<span class='smalltext'>
			".LOGIN_MENU_L25." 
			$new_news ".($new_news == 1 ? LOGIN_MENU_L14 : LOGIN_MENU_L15).", 
			$new_chat ".($new_chat == 1 ? LOGIN_MENU_L16 : LOGIN_MENU_L17).", 
			$new_comments ".($new_comments == 1 ? LOGIN_MENU_L18 : LOGIN_MENU_L19).", 
			$new_forum ".($new_forum == 1 ? LOGIN_MENU_L20 : LOGIN_MENU_L21)." ".LOGIN_MENU_L27." 
			$new_users ".($new_users == 1 ? LOGIN_MENU_L22 : LOGIN_MENU_L23).".</span>
			<br /><a href='".e_PLUGIN."list_new/new.php'>".LOGIN_MENU_L24."</a>";
			$caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5." ".USERNAME : LOGIN_MENU_L5." ".USERNAME);
			$ns -> tablerender($caption, $text);


		}else{
			$text = "<div style='text-align:center'>".LOGIN_MENU_L7."<br /><br />
			<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."index.php?logout'>".LOGIN_MENU_L8."</a></div>";
			$ns -> tablerender(LOGIN_MENU_L9, $text);
		}
	}else{
		if(LOGINMESSAGE != ""){
			$text = "<div style='text-align:center'>".LOGINMESSAGE."</div>";
		}
		$text .=  "<div style='text-align:center'>
<form method='post' action='".e_SELF;
if(e_QUERY){
	$text .= "?".e_QUERY;
}

$text .= "'><p>
".LOGIN_MENU_L1."<br />
<input class='tbox' type='text' name='username' size='15' value='' maxlength='30' />\n
<br />
".LOGIN_MENU_L2."
<br />
<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />\n
<br />
<input class='button' type='submit' name='userlogin' value='".LOGIN_MENU_L28."' />\n
<br />
<input type='checkbox' name='autologin' value='1' /> ".LOGIN_MENU_L6."
<br /><br />";
if($pref['auth_method'] != "ldap"){
	$text .= "[ <a href='".e_BASE."signup.php'>".LOGIN_MENU_L3."</a> ]<br />[ <a href='".e_BASE."fpw.php'> ".LOGIN_MENU_L4."</a> ]";
}
$text .= "</p>
</form>
</div>";
		$caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5 : LOGIN_MENU_L5);
		$ns -> tablerender($caption, $text);
	}
}
?>