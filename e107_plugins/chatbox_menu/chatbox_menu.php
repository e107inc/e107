<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/chatbox_menu/chatbox_menu.php,v $
|     $Revision: 1.33 $
|     $Date: 2005-03-21 12:28:41 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_HANDLER")){ exit; }
require_once(e_HANDLER."emote.php");
global $tp, $e107cache, $e_event;
$emessage='';
if(isset($_POST['chat_submit']))
{
	if(!USER && !$pref['anon_post'])
	{
		// disallow post
	}
	else
	{
		$cmessage = $_POST['cmessage'];
		$nick = trim(chop(preg_replace("/\[.*\]/si", "", $_POST['nick'])));
		$fp = new floodprotect;
		if(!$fp -> flood("chatbox", "cb_datestamp"))
		{
			header("location:".e_BASE."index.php");
			exit;
		}
		else
		{
			if((strlen(trim(chop($cmessage))) < 1000) && trim(chop($cmessage)) != "")
			{
				$cmessage = $tp -> toDB($cmessage);
				if($sql -> db_Select("chatbox", "*", "cb_message='$cmessage' AND cb_datestamp+84600>".time()))
				{
					$emessage = CHATBOX_L17;
				}
				else
				{
					$datestamp = time();
					$ip = getip();
					if(USER)
					{
						$nick = USERID.".".USERNAME;
						$sql -> db_Update("user", "user_chats=user_chats+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
					}
					else if(!$nick)
					{
						$nick = "0.Anonymous";
					}
					else
					{
						if($sql -> db_Select("user", "*", "user_name='$nick' ")){
							$emessage = CHATBOX_L1;
						}
						else
						{
							$nick = "0.".$tp -> toDB($nick);
						}
					}
					if(!$emessage)
					{
						$sql -> db_Insert("chatbox", "0, '$nick', '$cmessage', '".time()."', '0' , '$ip' ");
						$edata_cb = array("cmessage" => $cmessage, "ip" => $ip);
						$e_event -> trigger("cboxpost", $edata_cb);
						$e107cache->clear("chatbox");
					}
				}
			}
			else
			{
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
}
else
{
	$texta =  "<div style='text-align:center'>".(e_QUERY ? "\n<form id='chatbox' method='post' action='".e_SELF."?".e_QUERY."'><p>" : "\n<form id='chatbox' method='post' action='".e_SELF."'><p>");
	if(($pref['anon_post'] == "1" && USER == FALSE)){
		$texta .= "\n<input class='tbox' type='text' name='nick' value='' maxlength='50' style='width: 100%;' /><br />";
	}
	
	$cb_width = (defined("CBWIDTH") ? CBWIDTH : "100%");
	
	$texta .= "\n<textarea class='tbox chatbox' name='cmessage' cols='20' rows='5' style='overflow:hidden;' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>\n<br />\n<input class='button' type='submit' name='chat_submit' value='".CHATBOX_L4."' />\n<input class='button' type='reset' name='reset' value='".CHATBOX_L5."' />";

	if($pref['cb_emote']){
		$texta .= " \n<input class='button' type ='button' style='cursor:hand; cursor:pointer' size='30' value='".CHATBOX_L14."' onclick='expandit(this)' />\n<span style='display:none;'>".r_emote()."\n</span>\n";
	}

	$texta .="</p>\n</form>\n</div>\n";
}

if($emessage != ""){
	$texta .= "<div style='text-align:center'><b>".$emessage."</b></div>";
}

if(!$text = $e107cache->retrieve("chatbox"))
{
	global $pref,$tp;
	$chatbox_posts = $pref['chatbox_posts'];
	if($sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT 0, ".$chatbox_posts, $mode="no_where"))
	{
		$obj2 = new convert;
		$cbpost = $sql -> db_getList();
		foreach($cbpost as $cb)
		{
			extract($cb);
			// get available vars
			list($cb_uid,$cb_nick) = explode(".",$cb_nick,2);
			//			$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);
			if($cb_uid == 0)
			{
				$cb_nick = $tp -> toHTML($cb_nick);
			}
			else
			{
				$cb_nick = "<a href='".e_BASE."user.php?id.{$cb_uid}'>{$cb_nick}</a>";
			}

			$datestamp = $obj2->convert_date($cb_datestamp, "short");
			if(!$pref['cb_wordwrap']) { $pref['cb_wordwrap'] = 30; }
			$emotes_active = $pref['cb_emote'] ? 'emotes_on' : 'emotes_off';
			$cb_message = $tp -> toHTML($cb_message, TRUE, $emotes_active, $cb_uid, $pref['menu_wordwrap']);

			$replace[0] = "["; $replace[1] = "]";
			$search[0] = "&lsqb;"; $search[1] =  "&rsqb;";
			$cb_message = str_replace($search, $replace, $cb_message);

			global $CHATBOXSTYLE;
			if(!$CHATBOXSTYLE)
			{
				$bullet = (defined("BULLET") ? "<img src='".THEME."images/".BULLET."' alt='' style='vertical-align: middle;' />" : "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='vertical-align: middle;' />");
				// default chatbox style
				$CHATBOXSTYLE = "<!-- chatbox -->\n<div class='spacer'>
				$bullet <b>{USERNAME}</b><br /><span class='smalltext'>{TIMEDATE}</span><br /><div class='smallblacktext'>{MESSAGE}</div></div><br />\n";
			}

			$search[0] = "/\{USERNAME\}(.*?)/si";
			$replace[0] = $cb_nick;
			$search[1] = "/\{TIMEDATE\}(.*?)/si";
			$replace[1] = $datestamp;
			$search[2] = "/\{MESSAGE\}(.*?)/si";
			$replace[2] = ($cb_blocked ? CHATBOX_L6 : $cb_message);

			$text .= preg_replace($search, $replace, $CHATBOXSTYLE);

		}
	}
	else
	{
		$text .= "<span class='mediumtext'>".CHATBOX_L11."</span>";
	}
	$total_chats = $sql -> db_Count("chatbox");
	if($total_chats > $chatbox_posts)
	{
		$text .= "<br /><div style='text-align:center'><a href='".e_PLUGIN."chatbox_menu/chat.php'>".CHATBOX_L12."</a> (".$total_chats.")</div>";
	}
	$e107cache->set("chatbox", $text);
}

if(ADMIN && getperms("C")){$text .= "<br />[ <a href='".e_PLUGIN."chatbox_menu/admin_chatbox.php'>".CHATBOX_L13."</a> ]";}
$caption = (file_exists(THEME."images/chatbox_menu.png") ? "<img src='".THEME."images/chatbox_menu.png' alt='' /> ".CHATBOX_L2 : CHATBOX_L2);


$text = ($pref['cb_layer'] ? $texta."<div style='border : 0; padding : 4px; width : auto; height : ".$pref['cb_layer_height']."px; overflow : auto; '>".$text."</div>" : $texta.$text);

$ns -> tablerender($caption, $text, 'chatbox');

?>
