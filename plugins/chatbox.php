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
							//$nick = "0.Anonymous";
							$emessage = LAN_310;
						}
					}else{
						$nick = "0.".$nick;
					}
				}
				if($emessage == ""){
					$sql -> db_Insert("chatbox", "0, '$nick', '$message', '".time()."', '0' , '$ip' ");
				}
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
$text .= "\n<textarea class=\"tbox\" name=\"message\" cols=\"26\" rows=\"5\" style=\"overflow:hidden\"></textarea>
<br />
<input class=\"button\" type=\"submit\" name=\"chat_submit\" value=\"".LAN_156."\" />
<input class=\"button\" type=\"reset\" name=\"reset\" value=\"".LAN_157."\" />
</p>
</form>
</div>";
}
global $nickstore;
if($sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT 0, ".$chatbox_posts, $mode="no_where")){
	$obj2 = new convert;
	$aj = new textparse();
	$cb_wordwrap = $pref['cb_wordwrap'][1];
	$sql2 = new db;
	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql-> db_Fetch()){

		// get available vars
		$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);
		if($nickstore[$cb_nick]){
			$cb_nick = "<a href=\"user.php?id.".$nickstore[$cb_nick]."\">".$cb_nick."</a>";
		}else{
			if($sql2 -> db_Select("user", "*", "user_name='$cb_nick'")){
				list($cuser_id, $cuser_name) = $sql2-> db_Fetch();
				$nickstore[$cb_nick] = $cuser_id;
				$cb_nick = "<a href=\"user.php?id.".$cuser_id."\">".$cb_nick."</a>";
			}else{
				$cb_nick = $aj -> tpa($cb_nick);
			}
		}
		$datestamp = $obj2->convert_date($cb_datestamp, "short");
		$cb_message = $aj -> tpa($cb_message, "on");
		if($pref['cb_linkreplace'][1]){
			$cb_message = preg_replace("#\>(.*?)\</a#si", ">".$pref['cb_linkc'][1]."</a", $cb_message);
		}

		$cb_message = preg_replace("#src='#si" ,"src='".e_BASE, $cb_message);
		$cb_message = preg_replace("/([^\s]{".$cb_wordwrap."})/", "$1\n", $cb_message);
		if(CB_STYLE != "CB_STYLE"){
			$CHATBOXSTYLE = CB_STYLE;
		}else{
			// default chatbox style
			$CHATBOXSTYLE = "
			<div class=\"spacer\">
			<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /><b>
			{USERNAME}
			</b><br /><span class=\"smalltext\">
			{TIMEDATE}
			</span><br />
			<div class=\"smallblacktext\">
			{MESSAGE}
			</div></div>
			{ADMINOPTIONS}
			<br />";
		}
		
		$text .= parsechatbox($CHATBOXSTYLE, $cb_nick, $datestamp, $cb_id, $cb_message, $cb_ip, $cb_blocked);
	}
}else{
	$text .= "<span class=\"mediumtext\">".LAN_158."</span>";
}
$total_chats = $sql -> db_Count("chatbox");
if($total_chats > $chatbox_posts){
	$text .= "<br /><div style=\"text-align:center\"><a href=\"chat.php\">".LAN_159."</a> (".$total_chats.")</div>";
}

if($emessage != ""){
	$text = "<div style='text-align:center'><b>".$emessage."</b></div><br />".$text;
}
$ns -> tablerender(LAN_182, $text);
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parsechatbox($cbd, $cb_nick, $datestamp, $cb_id, $cb_message, $cb_ip, $cb_blocked){
	global $text;
	
	$tmp = explode("\n", $cbd);
	for($c=0; $c < count($tmp); $c++){ 
	//	echo htmlentities($tmp[$c])."<br />";
		if(preg_match("/[\{|\}]/", $tmp[$c])){
			$var .=  checklayoutcb($tmp[$c], $cb_nick, $datestamp, $cb_id, $cb_message, $cb_ip, $cb_blocked);
		}else{
			$var .= $tmp[$c];
		}
	}
	return $var;
//	echo htmlentities($text)."<br />";
}
function checklayoutcb($str, $cb_nick, $datestamp, $cb_id, $cb_message, $cb_ip, $cb_blocked){
	if(strstr($str, "USERNAME")){
		$var = $cb_nick;
	}else if(strstr($str, "TIMEDATE")){
		$var = $datestamp;
	}else if(strstr($str, "MESSAGE")){
		if($cb_blocked == 1){
			$var .= LAN_0;
		}else{
			$var = $cb_message;
		}	
	}else if(strstr($str, "ADMINOPTIONS")){
		if(ADMIN == TRUE && getperms("C")){
			if($cb_blocked == 1){
				$var = "<div class=\"smalltext\">[<a href=\"".e_BASE."admin/chatbox_conf.php?unblock-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_1."</a>]";
			}else{
				$var = "<div class=\"smalltext\">[<a href=\"".e_BASE."admin/chatbox_conf.php?block-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_2."</a>]";
			}
			$var .= "[<a href=\"".e_BASE."admin/chatbox_conf.php?delete-".$cb_id."-".$_SERVER['PHP_SELF']."\">".LAN_3."</a>][<a href=\"".e_BASE."admin/userinfo.php?$cb_ip\">".LAN_4."</a>]</div>\n";
		}
	}
	return $var;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>