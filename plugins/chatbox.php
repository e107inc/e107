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
	if(!$fp -> flood("chatbox", "cb_datestamp")){
		header("location:index.php");
	}else{
		if((strlen($message) < 1000) && $message != ""){
			if($sql -> db_Select("chatbox", "*", "cb_message='$message' AND cb_datestamp+84600>".time())){
				$emessage = "Unable to post";
			}else{
				$message = $tp -> tp($message, "off");
				$datestamp = time();
				if(USER){
					$nick = USERID.".".USERNAME;
					$sql -> db_Update("user", "user_chats=user_chats+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
				}else if(!$nick){
					$nick = "0.Anonymous";
				}else{
					if($sql -> db_Select("user", "*", "user_name='$nick' ")){
						$emessage = LAN_310;
					}else{
						$ip = getip();
						$nick = "0.".$nick;
					}
				}
				if(!$emessage){
					$sql -> db_Insert("chatbox", "0, '$nick', '$message', '".time()."', '0' , '$ip' ");
				}
			}
		}
	}
}

$chatbox_posts = $pref['chatbox_posts'][1];
if($pref['user_reg'][1] == 1 && USER != TRUE && $pref['anon_post'][1] != "1"){
	$text = "<div style='text-align:center'>".LAN_6."</div><br /><br />";
}else{
	$text =  "<div style='text-align:center'>";
	if($_SERVER['QUERY_STRING'] != ""){
		$text .=  "\n<form method='post' action='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."'><p>";

	}else{
		$text .=  "\n<form method='post' action='".$_SERVER['PHP_SELF']."'><p>";
	}

if(($pref['anon_post'][1] == "1" && USER == FALSE)){
	$text .= "\n<input class='tbox' type='text' name='nick' size='27' value='' maxlength='50' /><br />";
}
$text .= "\n<textarea class='tbox' name='message' cols='26' rows='5' style='overflow:hidden'></textarea>
<br />
<input class='button' type='submit' name='chat_submit' value='".LAN_156."' />
<input class='button' type='reset' name='reset' value='".LAN_157."' />
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
		if($cb_nick == "Anonymous"){
			$cuser_id = 0;
		}else{
			if($nickstore[$cb_nick]){
				$cb_nick = "<a href='user.php?id.".$nickstore[$cb_nick]."'>".$cb_nick."</a>";
			}else{
				if($sql2 -> db_Select("user", "*", "user_name='$cb_nick'")){
					list($cuser_id, $cuser_name) = $sql2-> db_Fetch();
					$nickstore[$cb_nick] = $cuser_id;
					$cb_nick = "<a href='user.php?id.".$cuser_id."'>".$cb_nick."</a>";
				}else{
					$cb_nick = $aj -> tpa($cb_nick);
				}
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
			$CHATBOXSTYLE = "<!-- chatbox -->\n<div class='spacer'>
			<img src='".THEME."images/bullet2.gif' alt='bullet' /><b>{USERNAME}</b><br /><span class='smalltext'>{TIMEDATE}</span><br /><div class='smallblacktext'>{MESSAGE}</div></div>{ADMINOPTIONS}<br />\n";
		}

		$search[0] = "/\{USERNAME\}(.*?)/si";
		$replace[0] = $cb_nick;
		$search[1] = "/\{TIMEDATE\}(.*?)/si";
		$replace[1] = $datestamp;
		$search[2] = "/\{MESSAGE\}(.*?)/si";
		$replace[2] = ($cb_blocked ? LAN_0 : $cb_message);
		$search[3] = "/\{ADMINOPTIONS\}(.*?)/si";
		if(ADMIN){
			$replace[3] = ($cb_blocked ? "<div class='smalltext'>[<a href='".e_ADMIN."chatbox_conf.php?unblock-".$cb_id."-".$_SERVER['PHP_SELF']."'>".LAN_1."</a>]" : "<div class='smalltext'>[<a href='".e_ADMIN."chatbox_conf.php?block-".$cb_id."-".$_SERVER['PHP_SELF']."'>".LAN_2."</a>]")."[<a href='".e_ADMIN."chatbox_conf.php?delete-".$cb_id."-".$_SERVER['PHP_SELF']."'>".LAN_3."</a>][<a href='".e_ADMIN."userinfo.php?$cb_ip'>".LAN_4."</a>]</div>";
		}else{
			$replace[3] = "<br />";
		}
		$text .= preg_replace($search, $replace, $CHATBOXSTYLE);
	}

}else{
	$text .= "<span class='mediumtext'>".LAN_158."</span>";

}
$total_chats = $sql -> db_Count("chatbox");
if($total_chats > $chatbox_posts){
	$text .= "<br /><div style='text-align:center'><a href='chat.php'>".LAN_159."</a> (".$total_chats.")</div>";
}

if($emessage != ""){
	$text = "<div style='text-align:center'><b>".$emessage."</b></div><br />".$text;
}
$ns -> tablerender(LAN_182, $text);
?>