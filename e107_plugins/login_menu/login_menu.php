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
			$new_news = $sql -> db_Count("news", "(*)", "WHERE news_datestamp>'".$time."' "); if(!$new_news){ $new_news = "no"; }
			$new_comments = $sql -> db_Count("comments", "(*)", "WHERE comment_datestamp>'".$time."' "); if(!$new_comments){ $new_comments = "no"; }
			$new_chat = $sql -> db_Count("chatbox", "(*)", "WHERE cb_datestamp>'".$time."' "); if(!$new_chat){ $new_chat = "no"; }
			$new_forum = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".$time."' "); if(!$new_forum){ $new_forum = "no"; }
			$new_users = $sql -> db_Count("user", "(*)", "WHERE user_join>'".$time."' "); if(!$new_users){ $new_users = "no"; }


			$text .= "<br /><br />
			<span class='smalltext'>
			Since your last visit there have been 
			$new_news ".($new_news == 1 ? "news item" : "news items").", 
			$new_chat ".($new_chat == 1 ? "chatbox post" : "chatbox posts").", 
			$new_comments ".($new_comments == 1 ? "comment" : "comments").", 
			$new_forum ".($new_forum == 1 ? "forum post" : "forum posts")." and 
			$new_users ".($new_users == 1 ? "new site member" : "new site members").".</span>";
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
<input class='tbox' type='text' name='username' size='15' value='' maxlength='20' />\n
<br />
".LOGIN_MENU_L2."
<br />
<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />\n
<br />
<input class='button' type='submit' name='userlogin' value='Login' />\n
<br />
<input type='checkbox' name='autologin' value='1' /> ".LOGIN_MENU_L6."
<br /><br />
[ <a href='".e_BASE."signup.php'>".LOGIN_MENU_L3."</a> ]<br />[ <a href='".e_BASE."fpw.php'> ".LOGIN_MENU_L4."</a> ]
</p>
</form>
</div>";
		$caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5 : LOGIN_MENU_L5);
		$ns -> tablerender($caption, $text);
	}
}
?>