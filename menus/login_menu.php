<?php
$text = "";
if($pref['user_reg'][1] == 1 || ADMIN == TRUE){

	if(USER == TRUE || ADMIN == TRUE){
		if(IsSet($_SESSION['userkey'])){ $uk = $_SESSION['userkey']; }else{ $uk = $_COOKIE['userkey']; }
		$tmp = explode(".", $uk); $uid = $tmp[0]; $upw = $tmp[1];
		$sql = new db;
		if($sql -> db_Select("user", "*", "user_id='$uid' AND user_password='$upw' ")){
			if(ADMIN == TRUE){
				$text = "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"admin/admin.php\">Admin</a><br />";
			}else{
				unset($text);
			}
			$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"usersettings.php\">Settings</a>
<br />
<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"".$_SERVER['PHP_SELF']."?logout\">".LAN_172."</a>";
		

			if(!$sql -> db_Select("online", "*", "online_ip='$ip' AND online_user_id='0' ")){
				$sql -> db_Delete("online", "online_ip='$ip' AND online_user_id='0' ");
				$sql -> db_Insert("online", " '$timestamp', '1', '".USERID."', '$ip', '".$_SERVER['PHP_SELF']."' ");
			}

			$time = USERLV;
			$new_news = $sql -> db_Count("news", "(*)", "WHERE news_datestamp>'".$time."' ");
			$new_comments = $sql -> db_Count("comments", "(*)", "WHERE comment_datestamp>'".$time."' ");
			$new_chat = $sql -> db_Count("chatbox", "(*)", "WHERE cb_datestamp>'".$time."' ");
			$new_forum = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".$time."' ");
			$new_users = $sql -> db_Count("user", "(*)", "WHERE user_join>'".$time."' ");


			$text .= "<br /><br />
			<span class=\"smalltext\">
			Since your last visit ...
			<br />
			$new_news news item(s)<br />
			$new_chat chatbox post(s)<br />
			$new_comments news comment(s)<br />
			$new_forum forum post(s)<br />
			$new_users new site member(s)</span>";

			$ns -> tablerender(LAN_30." ".USERNAME, $text);


		}else{
			$text = "<div style=\"text-align:center\">".LAN_171."<br /><br />
			<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"index.php?logout\">".LAN_172."</a></div>";
			$ns -> tablerender(LAN_173, $text);
		}
	}else{
		if(LOGINMESSAGE != ""){
			$text = "<div style=\"text-align:center\">".LOGINMESSAGE."</div>";
		}
		$text .=  "<div style=\"text-align:center\">
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\"><p>
".LAN_16."<br />
<input class=\"tbox\" type=\"text\" name=\"username\" size=\"15\" value=\"\" maxlength=\"20\" />\n
<br />
".LAN_17."
<br />
<input class=\"tbox\" type=\"password\" name=\"userpass\" size=\"15\" value=\"\" maxlength=\"20\" />\n
<br />
<input class=\"button\" type=\"submit\" name=\"userlogin\" value=\"Login\" />\n
<br />
<input type=\"checkbox\" name=\"autologin\" value=\"1\" /> Auto Login
<br /><br />
[ <a href=\"signup.php\">".LAN_174."</a> ]<br />[ <a href=\"fpw.php\">".LAN_212."</a> ]
</p>
</form>
</div>";
		$ns -> tablerender(LAN_175, $text);
	}
}
?>