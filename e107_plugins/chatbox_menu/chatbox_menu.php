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
if(!is_object($aj)){ $aj = new textparse; }
if(IsSet($_POST['chat_submit']))
{
	if(!USER && !$pref['anon_post'])
	{
		// disallow post
	} else
	{
		$cmessage = $_POST['cmessage'];
		$cmessage = htmlentities($cmessage);
		$nick = trim(chop(preg_replace("/\[.*\]/si", "", $_POST['nick'])));
		$fp = new floodprotect;
		if(!$fp -> flood("chatbox", "cb_datestamp"))
		{
			header("location:".e_BASE."index.php");
			exit;
		} else
		{
			if((strlen(trim(chop($cmessage))) < 1000) && trim(chop($cmessage)) != "")
			{
				$cmessage = $aj -> formtpa($cmessage, "public");
				if($sql -> db_Select("chatbox", "*", "cb_message='$cmessage' AND cb_datestamp+84600>".time()))
				{
					$emessage = CHATBOX_L17;
				} else 
				{
					$datestamp = time();
					$ip = getip();
					if(USER)
					{
						$nick = USERID.".".USERNAME;
						$sql -> db_Update("user", "user_chats=user_chats+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
					} else if(!$nick)
					{
						$nick = "0.Anonymous";
					} else {
						if($sql -> db_Select("user", "*", "user_name='$nick' ")){
							$emessage = CHATBOX_L1;
						}else{
							$nick = "0.".$aj -> formtpa($nick, "public");
						}
					}
					if(!$emessage){
						$cmessage = str_replace("<iframe", "&lt;iframe", $cmessage);
						$sql -> db_Insert("chatbox", "0, '$nick', '$cmessage', '".time()."', '0' , '$ip' ");
						clear_cache("chatbox");
					}
				}
			}else{
				$emessage = CHATBOX_L15;
			}
		}
	}
}

$pref['cb_linkc'] = str_replace("e107_images/", e_IMAGE, $pref['cb_linkc']);
if(!USER && !$pref['anon_post']){
	if($pref['user_reg'])
	{
		$texta = "<div style='text-align:center'>".CHATBOX_L3."</div><br /><br />";
	}
} else 
{
	$texta =  "<div style='text-align:center'>".(e_QUERY ? "\n<form id='chatbox' method='post' action='".e_SELF."?".e_QUERY."'><p>" : "\n<form id='chatbox' method='post' action='".e_SELF."'><p>");
	if(($pref['anon_post'] == "1" && USER == FALSE)){
		$texta .= "\n<input class='tbox' type='text' name='nick' size='27' value='' maxlength='50' /><br />";
	}
	$texta .= "\n<textarea class='tbox' name='cmessage' cols='26' rows='5' style='overflow:hidden'></textarea>\n<br />\n<input class='button' type='submit' name='chat_submit' value='".CHATBOX_L4."' />\n<input class='button' type='reset' name='reset' value='".CHATBOX_L5."' />";
		
	if($pref['cb_emote']){
		$texta .= " \n<input class='button' type ='button' style='cursor:hand; cursor:pointer' size='30' value='".CHATBOX_L14."' onclick='expandit(this)' />\n<span style='display:none;'>".emote()."\n</span>\n";
	}
	
	$texta .="</p>\n</form>\n</div>\n";
}

if($emessage != ""){
	$texta .= "<div style='text-align:center'><b>".$emessage."</b></div>";
}

if(!$text = retrieve_cache("chatbox")){

	$chatbox_posts = $pref['chatbox_posts'];
	global $nickstore;
	if($sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT 0, ".$chatbox_posts, $mode="no_where")){
		$obj2 = new convert;
		$cb_wordwrap = $pref['cb_wordwrap'];
		$sql2 = new db;
		while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql-> db_Fetch()){

			// get available vars
			$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);
			if($cb_nick == "Anonymous"){
				$cuser_id = 0;
			}else{
				if($nickstore[$cb_nick]){
					$cb_nick = "<a href='".e_BASE."user.php?id.".$nickstore[$cb_nick]."'>".$cb_nick."</a>";
				}else{
					if($sql2 -> db_Select("user", "*", "user_name='$cb_nick'")){
						list($cuser_id, $cuser_name) = $sql2-> db_Fetch();
						$nickstore[$cb_nick] = $cuser_id;
						$cb_nick = "<a href='".e_BASE."user.php?id.".$cuser_id."'>".$cb_nick."</a>";
					}else{
						$cb_nick = $aj -> tpa($cb_nick);
					}
				}
			}
			$datestamp = $obj2->convert_date($cb_datestamp, "short");

			$search[0] = "["; $search[1] = "]";
			$replace[0] = "&lsqb;"; $replace[1] =  "&rsqb;";
			$cb_message = str_replace($search, $replace, $cb_message);

			if($pref['cb_linkreplace']){
				$cb_message = " ".$cb_message;
				$cb_message = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3">'.$pref['cb_linkc'].'</a>', $cb_message);
				$cb_message = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3">'.$pref['cb_linkc'].'</a>', $cb_message);
				$cb_message = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $cb_message);
			}

			$cb_message = $aj -> tpa($cb_message);


			if(!eregi("<a href|<img|&#", $cb_message)){
				
				$message_array = explode(" ", $cb_message);
				for($i=0; $i<=(count($message_array)-1); $i++){
					if(strlen($message_array[$i]) > $cb_wordwrap){
						$message_array[$i] = wordwrap( $message_array[$i], $cb_wordwrap, "<br />", 1);
					}
				}
				$cb_message = implode(" ",$message_array);
			}

			$replace[0] = "["; $replace[1] = "]";
			$search[0] = "&lsqb;"; $search[1] =  "&rsqb;";
			$cb_message = str_replace($search, $replace, $cb_message);
			
			global $CHATBOXSTYLE;
			if(!$CHATBOXSTYLE){
				// default chatbox style
				$CHATBOXSTYLE = "<!-- chatbox -->\n<div class='spacer'>
				<img src='".THEME."images/bullet2.gif' alt='' /> <b>{USERNAME}</b><br /><span class='smalltext'>{TIMEDATE}</span><br /><div class='smallblacktext'>{MESSAGE}</div></div><br />\n";
			}

			$search[0] = "/\{USERNAME\}(.*?)/si";
			$replace[0] = $cb_nick;
			$search[1] = "/\{TIMEDATE\}(.*?)/si";
			$replace[1] = $datestamp;
			$search[2] = "/\{MESSAGE\}(.*?)/si";
			$replace[2] = ($cb_blocked ? CHATBOX_L6 : $cb_message);
			
			$text .= preg_replace($search, $replace, $CHATBOXSTYLE);
//			$text .= $aj -> formtparev($str, "public");
//			$text .= stripslashes($str);

		}

	}else{
		$text .= "<span class='mediumtext'>".CHATBOX_L11."</span>";
	}
	$total_chats = $sql -> db_Count("chatbox");
	if($total_chats > $chatbox_posts){
		$text .= "<br /><div style='text-align:center'><a href='".e_BASE."chat.php'>".CHATBOX_L12."</a> (".$total_chats.")</div>";
	}

	set_cache("chatbox", $text);

}
if(ADMIN && getperms("C")){$text .= "<br />[ <a href='".e_ADMIN."chatbox.php'>".CHATBOX_L13."</a> ]";}
$caption = (file_exists(THEME."images/chatbox_menu.png") ? "<img src='".THEME."images/chatbox_menu.png' alt='' /> ".CHATBOX_L2 : CHATBOX_L2);


$text = ($pref['cb_layer'] ? $texta."<div style='border : 0; padding : 4px; width : auto; height : ".$pref['cb_layer_height']."px; overflow : auto; '>".$text."</div>" : $texta.$text);


$ns -> tablerender($caption, $text);

function emote(){
	$sql = new db;
	$sql -> db_Select("core", "*", "e107_name='emote'");
	$row = $sql -> db_Fetch(); extract($row);
	$emote = unserialize($e107_value);
	$str="<br />";
	$c=0;
	while(list($code, $name) = @each($emote[$c])){
		if(!$orig[$name]){
			$code = htmlentities($code);
			$str .= "\n<a href=\"javascript:caddtext(' $code')\"><img src=\"".e_IMAGE."emoticons/$name\" style=\"border:0; padding-top:2px;\" alt=\"\" /></a> ";
			$orig[$name] = TRUE;
		}
		$c++;
	}
	return $str;
}

echo "
<script type='text/javascript'>
function caddtext(sc){
	document.getElementById('chatbox').cmessage.value +=sc;
}
</script>";

?>