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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/chatbox_menu/chatbox_menu.php,v $
|     $Revision: 1.15 $
|     $Date: 2009-08-23 10:57:50 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/

global $tp, $e107cache, $e_event, $e107, $pref, $footer_js, $PLUGINS_DIRECTORY;
if (!plugInstalled('chatbox_menu')) 
{
	return '';
}

if(($pref['cb_layer']==2) || isset($_POST['chatbox_ajax']))
{
	if(isset($_POST['chat_submit']))
	{
		include_once("../../class2.php");

		//Normally the menu.sc file will auto-load the language file, this is needed in case
		//ajax is turned on and the menu is not loaded from the menu.sc
		include_lan(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
	}
  //	$footer_js[] = e_FILE_ABS.'e_ajax.php';


}

if(!defined("e_HANDLER")){ exit; }
require_once(e_HANDLER."emote.php");

$emessage='';
if(isset($_POST['chat_submit']) && $_POST['cmessage'] != "")
{
	if(!USER && !$pref['anon_post'])
	{
		// disallow post
	}
	else
	{
		$nick = trim(preg_replace("#\[.*\]#si", "", $tp -> toDB($_POST['nick'])));

		$cmessage = $_POST['cmessage'];
		$cmessage = preg_replace("#\[.*?\](.*?)\[/.*?\]#s", "\\1", $cmessage);

		$fp = new floodprotect;
		if($fp -> flood("chatbox", "cb_datestamp"))
		{
			if((strlen(trim($cmessage)) < 1000) && trim($cmessage) != "")
			{
				$cmessage = $tp -> toDB($cmessage);
				if($sql -> db_Select("chatbox", "*", "cb_message='$cmessage' AND cb_datestamp+84600>".time()))
				{
					$emessage = CHATBOX_L17;
				}
				else
				{
					$datestamp = time();
					$ip = $e107->getip();
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
							$nick = "0.".$nick;
						}
					}
					if(!$emessage)
					{
						$sql -> db_Insert("chatbox", "0, '$nick', '$cmessage', '".time()."', '0' , '$ip' ");
						$edata_cb = array("cmessage" => $cmessage, "ip" => $ip);
						$e_event -> trigger("cboxpost", $edata_cb);
						$e107cache->clear("nq_chatbox");
					}
				}
			}
			else
			{
				$emessage = CHATBOX_L15;
			}
		}
		else
		{
			$emessage = CHATBOX_L19;
		}
	}
}

if(!USER && !$pref['anon_post']){
	if($pref['user_reg'])
	{
		$texta = "<div style='text-align:center'>".CHATBOX_L3."</div><br /><br />";
	}
}
else
{
	$cb_width = (defined("CBWIDTH") ? CBWIDTH : "");

	if($pref['cb_layer'] == 2)
	{
		$texta =  "\n<form id='chatbox' action='".e_SELF."?".e_QUERY."'  method='post' onsubmit='return(false);'>
		<div><input type='hidden' name='chatbox_ajax' id='chatbox_ajax' value='1' /></div>
		";
	}
	else
	{
		$texta =  (e_QUERY ? "\n<form id='chatbox' method='post' action='".e_SELF."?".e_QUERY."'>" : "\n<form id='chatbox' method='post' action='".e_SELF."'>");
	}
	$texta .= "<div style='text-align:center; width:100%'>";

	if(($pref['anon_post'] == "1" && USER == FALSE))
	{
		$texta .= "\n<input class='tbox chatbox' type='text' id='nick' name='nick' value='' maxlength='50' ".($cb_width ? "style='width: ".$cb_width.";'" : '')." /><br />";
	}

	if($pref['cb_layer'] == 2)
	{

		$oc = "onclick=\"javascript:sendInfo('".SITEURL.$PLUGINS_DIRECTORY."chatbox_menu/chatbox_menu.php', 'chatbox_posts', this.form);\"";
	}
	else
	{
		$oc = "";
	}
	$texta .= "
	<textarea class='e-wysiwyg tbox chatbox' id='cmessage' name='cmessage' cols='20' rows='5' style='".($cb_width ? "width:".$cb_width.";" : '')." overflow: auto' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>
	<br />
	<input class='button' type='submit' id='chat_submit' name='chat_submit' value='".CHATBOX_L4."' {$oc}/>
	<input class='button' type='reset' name='reset' value='".CHATBOX_L5."' />";

	if($pref['cb_emote'] && $pref['smiley_activate']){
		$texta .= "
		<input class='button' type ='button' style='cursor:pointer' size='30' value='".CHATBOX_L14."' onclick=\"expandit('emote')\" />
		<div style='display:none' id='emote'>".r_emote()."
		</div>\n";
	}

	$texta .="</div>\n</form>\n";
}

if($emessage != ""){
	$texta .= "<div style='text-align:center'><b>".$emessage."</b></div>";
}

if(!$text = $e107cache->retrieve("nq_chatbox"))
{
	global $pref,$tp;
	$pref['chatbox_posts'] = ($pref['chatbox_posts'] ? $pref['chatbox_posts'] : 10);
	$chatbox_posts = $pref['chatbox_posts'];
	if(!isset($pref['cb_mod']))
	{
		$pref['cb_mod'] = e_UC_ADMIN;
	}
	define("CB_MOD", check_class($pref['cb_mod']));

	$qry = "
	SELECT c.*, u.user_name FROM #chatbox AS c
	LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
	ORDER BY c.cb_datestamp DESC LIMIT 0, ".intval($chatbox_posts);

	if($sql -> db_Select_gen($qry))
	{
		$obj2 = new convert;
		$cbpost = $sql -> db_getList();
		foreach($cbpost as $cb)
		{
			// get available vars
			list($cb_uid, $cb_nick) = explode(".", $cb['cb_nick'], 2);
			if($cb['user_name'])
			{
				$cb_nick = "<a href='".e_BASE."user.php?id.{$cb_uid}'>{$cb['user_name']}</a>";
			}
			else
			{
				$cb_nick = $tp -> toHTML($cb_nick,FALSE,'USER_TITLE, emotes_off, no_make_clickable');
				$cb_nick = str_replace("Anonymous", LAN_ANONYMOUS, $cb_nick);
			}

			$datestamp = $obj2->convert_date($cb['cb_datestamp'], "short");
			$emotes_active = $pref['cb_emote'] ? 'USER_BODY, emotes_on' : 'USER_BODY, emotes_off';

			$cb_message = $tp -> toHTML($cb['cb_message'], FALSE, $emotes_active, $cb_uid, $pref['menu_wordwrap']);

			$replace[0] = "["; $replace[1] = "]";
			$search[0] = "&lsqb;"; $search[1] =  "&rsqb;";
			$cb_message = str_replace($search, $replace, $cb_message);

			global $CHATBOXSTYLE;
			if( ! $CHATBOXSTYLE)
			{
				$bullet = '';
				if(defined('BULLET'))
				{
					$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
				}
				elseif(file_exists(THEME.'images/bullet2.gif'))
				{
					$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
				}
				// default chatbox style
				$CHATBOXSTYLE = "<!-- chatbox -->\n<div class='spacer'>
				$bullet <b>{USERNAME}</b><br /><span class='smalltext'>{TIMEDATE}</span><br /><div class='smallblacktext'>{MESSAGE}</div></div><br />\n";
			}
			$search = array('{USERNAME}', '{TIMEDATE}', '{MESSAGE}');
			$replace = array($cb_nick,$datestamp,($cb['cb_blocked'] ? CHATBOX_L6 : $cb_message));
			$text .= str_replace($search,$replace,$CHATBOXSTYLE);
		}
	}
	else
	{
		$text .= "<span class='mediumtext'>".CHATBOX_L11."</span>";
	}
	$total_chats = $sql -> db_Count("chatbox");
	if($total_chats > $chatbox_posts || CB_MOD)
	{
		$text .= "<br /><div style='text-align:center'><a href='".e_PLUGIN."chatbox_menu/chat.php'>".(CB_MOD ? CHATBOX_L13 : CHATBOX_L12)."</a> (".$total_chats.")</div>";
	}
	$e107cache->set("nq_chatbox", $text);
}

$caption = (file_exists(THEME."images/chatbox_menu.png") ? "<img src='".THEME_ABS."images/chatbox_menu.png' alt='' /> ".CHATBOX_L2 : CHATBOX_L2);

if($pref['cb_layer'] == 1)
{
	$text = $texta."<div style='border : 0; padding : 4px; width : auto; height : ".$pref['cb_layer_height']."px; overflow : auto; '>".$text."</div>";
	$ns -> tablerender($caption, $text, 'chatbox');
}
elseif($pref['cb_layer'] == 2 && isset($_POST['chat_submit']))
{
	$text = $texta.$text;
	$text = str_replace(e_IMAGE, e_IMAGE_ABS, $text);
	echo $text;
}
else
{
	$text = $texta.$text;
	if($pref['cb_layer'] == 2)
	{
		$text = "<div id='chatbox_posts'>".$text."</div>";
	}
	$ns -> tablerender($caption, $text, 'chatbox');
}

//$text = ($pref['cb_layer'] ? $texta."<div style='border : 0; padding : 4px; width : auto; height : ".$pref['cb_layer_height']."px; overflow : auto; '>".$text."</div>" : $texta.$text);
//if(ADMIN && getperms("C")){$text .= "<br /><div style='text-align: center'>[ <a href='".e_PLUGIN."chatbox_menu/admin_chatbox.php'>".CHATBOX_L13."</a> ]</div>";}
//$ns -> tablerender($caption, $text, 'chatbox');

?>