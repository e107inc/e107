<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/plugins/chatbox.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

if(IsSet($_POST['chat_submit'])){
	$message = $_POST['message'];
	$nick = $_POST['nick'];
	$fp = new floodprotect;
	if($fp -> flood("chatbox", "cb_datestamp") == FALSE){
		header("location:index.php");
	}else{
		if((strlen($message) < 1000) && $message != ""){
			$message = strip_tags($message, "<a>");
			for($a=0;$a<strlen($message);$a++){
				$char = ord($message[$a]);
				$tmp[$char] = 1;
			}
//			if(count($tmp) <=4){ $message = "Invalid input"; }
			$message_array = explode(" ", $message);
			for($i=0; $i<=(count($message_array)-1); $i++){
				if(eregi("http", $message_array[$i])){
					$message_array[$i] =  "<a href=\"".$message_array[$i]."\">-link-</a>";
				}else if(eregi("www", $message_array[$i])){
					$message_array[$i] =  "<a href=\"http://".$message_array[$i]."\">-link-</a>";
				}else if(strlen($message_array[$i]) > 30){
					$message_array[$i] = preg_replace("/([^\s]{30})/", "$1<br />", $message_array[$i]);
				}
			}
			$message = implode(" ", $message_array);
			$nick = addslashes($nick);
			$message = addslashes($message);

			$datestamp = time();
			$ip = getip();

			if(USER == TRUE){
				$nick = USERNAME;
			}
			if(!$sql -> db_Select("chatbox", "*", "cb_message='$message' ")){
				if(USER == TRUE){
					$nick = USERID.".".USERNAME;
					$sql -> db_Update("user", "user_chats=user_chats+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
				}else if($nick == ""){
					$nick = "0.Anonymous";
				}else{
					$nick = "0.".$nick;
				}
//				if($message != "Invalid input"){
					$sql -> db_Insert("chatbox", "0, '$nick', '$message', '".time()."', '0' , '$ip' ");
//				}
			}
		}
	}
}

$chatbox_posts = $pref['chatbox_posts'][1];
if($pref['user_reg'][1] == 1 && USER != TRUE && $pref['anon_post'][1] != "1"){
	$text = "<div style=\"text-align:center\">".LAN_6."</div><br /><br />";
}else{
	if($_SERVER['QUERY_STRING'] != ""){
		$text =  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\"><p>";
	}else{
		$text =  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\"><p>";
	}

if(($pref['anon_post'][1] == "1" && USER == FALSE)){
	$text .= "<input class=\"tbox\" type=\"text\" name=\"nick\" size=\"27\" value=\"\" maxlength=\"50\" /><br />";
}
$text .= "<textarea class=\"tbox\" name=\"message\" cols=\"26\" rows=\"5\"></textarea>
<br />
<input class=\"button\" type=\"submit\" name=\"chat_submit\" value=\"".LAN_156."\" />
<input class=\"button\" type=\"reset\" name=\"reset\" value=\"".LAN_157."\" />
</p>
</form>";
}

$text .= "<div style=\"text-align:center\"><table style=\"width:88%\"><tr><td>";
if($sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT 0, ".$chatbox_posts, $mode="no_where")){
	$obj2 = new convert;
	$aj = new textparse();

	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql-> db_Fetch()){
		
		$cb_message = $aj -> tpa($cb_message);
		$cb_message = stripslashes($cb_message);

		$datestamp = $obj2->convert_date($cb_datestamp, "short");

		$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);
		$cb_nick = $aj -> tpa($cb_nick);
	
		$text .= "\n<div class=\"spacer\">
<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" />
<b>".$cb_nick."</b><br />\n".$datestamp."<br />\n";
		if($cb_blocked == 1){
			$text .= "[blocked by admin]";
			if(ADMIN == TRUE && ADMINPERMS <=2){
				$text .= "<div class=\"smalltext\">
[<a href=\"admin/chatbox_conf.php?unblock-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_1."</a>]
[<a href=\"admin/chatbox_conf.php?delete-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_3."</a>]
[<a href=\"admin/userinfo.php?$cb_ip\">".LAN_4."</a>]
</div><br />\n";

				}
			}else{

				$text .= "<div class=\"smalltext\">".$cb_message."</div></div>\n";
				if(ADMIN == TRUE && ADMINPERMS <=2){
					$text .= "<div class=\"smalltext\">
[<a href=\"admin/chatbox_conf.php?block-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_2."</a>]
[<a href=\"admin/chatbox_conf.php?delete-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_3."</a>]
[<a href=\"admin/userinfo.php?$cb_ip\">Info</a>]
</div>\n";
				}
			$text .= "<br />";
		}
	}
}else{
	$text .= "<span class=\"mediumtext\">".LAN_158."</span>";
}
$total_chats = $sql -> db_Count("chatbox");
if($total_chats > $chatbox_posts){
	$text .= "<br />
<div style=\"text-align:center\"><a href=\"chat.php\">".LAN_159."</a> (".$total_chats.")</div>";
}
$text .= "</td></tr></table></div>";
$ns -> tablerender(LAN_182, $text);


?>