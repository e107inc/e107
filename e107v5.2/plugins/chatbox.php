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
	$tp = new textparse;
	if($fp -> flood("chatbox", "cb_datestamp") == FALSE){
		header("location:index.php");
	}else{
		if((strlen($message) < 1000) && $message != ""){
			$message = $tp -> tp($message, "off");

			$datestamp = time();
			$ip = getip();

			if(USER == TRUE){
				$nick = USERNAME;

			list($user_id, $user_name) = $sql-> db_Fetch();

			}
			if(!$sql -> db_Select("chatbox", "*", "cb_message='$message' ")){
				if(USER == TRUE){
					$nick = USERID.".".USERNAME;
					$sql -> db_Update("user", "user_chats=user_chats+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
				}else if($nick == ""){
					$nick = "0.Anonymous";
				}else{
					$sql2 = new db;
					if($sql2 -> db_Select("user", "*", "user_name='$nick' ")){
						$ip = getip();
						if($sql2 -> db_Select("user", "*", "user_name='$nick' AND user_ip='$ip' ")){
							list($cuser_id, $cuser_name) = $sql2-> db_Fetch();
							$nick = $cuser_id.".".$cuser_name;
						}else{
							$nick = "0.Anonymous";
						}
					}else{
						$nick = "0.".$nick;
					}
				}
				$sql -> db_Insert("chatbox", "0, '$nick', '$message', '".time()."', '0' , '$ip' ");
			}
		}
	}
}

$chatbox_posts = $pref['chatbox_posts'][1];
if($pref['user_reg'][1] == 1 && USER != TRUE && $pref['anon_post'][1] != "1"){
	$text = "<div style=\"text-align:center\">".LAN_6."</div><br /><br />";
}else{
	$text =  "<div style=\"text-align:center\">";
	if($_SERVER['QUERY_STRING'] != ""){
		$text .=  "\n<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\"><p>";
	}else{
		$text .=  "\n<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\"><p>";
	}

if(($pref['anon_post'][1] == "1" && USER == FALSE)){
	$text .= "\n<input class=\"tbox\" type=\"text\" name=\"nick\" size=\"27\" value=\"\" maxlength=\"50\" /><br />";
}
$text .= "\n<textarea class=\"tbox\" name=\"message\" cols=\"26\" rows=\"5\"></textarea>
<br />
<input class=\"button\" type=\"submit\" name=\"chat_submit\" value=\"".LAN_156."\" />
<input class=\"button\" type=\"reset\" name=\"reset\" value=\"".LAN_157."\" />
</p>
</form>
</div>";
}

if($sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT 0, ".$chatbox_posts, $mode="no_where")){
	$obj2 = new convert;
	$aj = new textparse();
	$cb_wordwrap = $pref['cb_wordwrap'][1];
	if(CHATBOXSTYLE != TRUE || !defined("CHATBOXSTYLE")){
		$cb_display1 = $pref['cb_display1'][1];
		$cb_display2 = $pref['cb_display2'][1];
		$cb_display3 = $pref['cb_display3'][1];
	}

	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql-> db_Fetch()){
		
		$cb_message = $aj -> tpa($cb_message, "on");
		if($pref['cb_linkreplace'][1] == "enabl"){
			$cb_message = preg_replace("/\<a href=\"(.*?)\">(.*?)<\/a>/si", "<a href=\"\\1\">".$pref['cb_linkc'][1]."</a>", $cb_message);
		}
		$cb_message = preg_replace("/([^\s]{".$cb_wordwrap."})/", "$1\n", $cb_message);

		$datestamp = $obj2->convert_date($cb_datestamp, "short");

		$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);
		$sql2 = new db;
		if($sql2 -> db_Select("user", "*", "user_name='$cb_nick'")){
			list($cuser_id, $cuser_name) = $sql2-> db_Fetch();
			$cb_nick = "<a href=\"user.php?id.".$cuser_id."\">".$cb_nick."</a>";
		}else{

			$cb_nick = $aj -> tpa($cb_nick);
		}
	
		$search = array("NICKNAME", "DATE");
		$replace = array($cb_nick, $datestamp);
		$d1 = str_replace($search, $replace, $cb_display1);

		$d2 = str_replace("MESSAGE", $cb_message, $cb_display2);


		$text .= $d1;
		if($cb_blocked == 1){
			if($blocked == ""){
				$text .= "[blocked by admin]";
			}else{
				$text .= $blocked;
			}
		}else{
			$text .= $d2;
		}
		if(ADMIN == TRUE && getperms("C")){
			if($cb_blocked == 1){
				$text .= "<div class=\"smalltext\">[<a href=\"admin/chatbox_conf.php?unblock-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_1."</a>]";
			}else{
				$text .= "<div class=\"smalltext\">[<a href=\"admin/chatbox_conf.php?block-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_2."</a>]";
			}
			$text .= "[<a href=\"admin/chatbox_conf.php?delete-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_3."</a>][<a href=\"admin/userinfo.php?$cb_ip\">".LAN_4."</a>]</div>\n";
		}
		$text .= $cb_display3;
	}
}else{
	$text .= "<span class=\"mediumtext\">".LAN_158."</span>";
}
$total_chats = $sql -> db_Count("chatbox");
if($total_chats > $chatbox_posts){
	$text .= "<br />
<div style=\"text-align:center\"><a href=\"chat.php\">".LAN_159."</a> (".$total_chats.")</div>";
}

$ns -> tablerender(LAN_182, $text);


?>