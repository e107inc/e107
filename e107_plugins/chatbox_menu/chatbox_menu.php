<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(isset($_POST['chatbox_ajax']))
{
	define('e_MINIMAL',true);
	if(!defined('e107_INIT')) require_once('../../class2.php');
}

global $e107cache, $e_event, $e107;

$tp = e107::getParser();
$pref = e107::getPref(); 


if (!e107::isInstalled('chatbox_menu')) 
{
	return '';
}


e107::lan('chatbox_menu', e_LANGUAGE);

// FIXME - start - LAN is not loaded
/*
if(($pref['cb_layer']==2) || isset($_POST['chatbox_ajax']))
{
	if(isset($_POST['chat_submit']))
	{
		

		//Normally the menu.sc file will auto-load the language file, this is needed in case
		//ajax is turned on and the menu is not loaded from the menu.sc
		inclXXXude_lan(e_PLUGIN.'chatbox_menu/languages/'.e_LANGUAGE.'/'.e_LANGUAGE.'.php');
	}
}
// FIXME - end
*/

// if(!defined('e_HANDLER')){ exit; }
// require_once(e_HANDLER.'emote.php');

$emessage='';

/**
 * Chatbox Menu Shortcodes. 
 */
if(!class_exists('chatbox_shortcodes'))
{
	class chatbox_shortcodes extends e_shortcode
	{
		
		function sc_cb_username($parm='')
		{
			list($cb_uid, $cb_nick) = explode(".", $this->var['cb_nick'], 2);
			if($this->var['user_name'])
			{
				//$cb_nick = "<a href='".e_HTTP."user.php?id.{$cb_uid}'>".$this->var['user_name']."</a>";
				$uparams = array('id' => $cb_uid, 'name' => $this->var['user_name']);
				$link = e107::getUrl()->create('user/profile/view', $uparams);
				$cb_nick = "<a href='".$link."'>".$this->var['user_name']."</a>";
			}
			else
			{
				$tp = e107::getParser();
				$cb_nick = $tp -> toHTML($cb_nick,FALSE,'USER_TITLE, emotes_off, no_make_clickable');
				$cb_nick = str_replace("Anonymous", LAN_ANONYMOUS, $cb_nick);
			}
			
			return $cb_nick;	
		}	
		
		function sc_cb_timedate($parm='')
		{
			return  e107::getDate()->convert_date($this->var['cb_datestamp'], "relative");		
		}
			
	
		function sc_cb_message($parm = '')
		{
			if($this->var['cb_blocked'])
			{
				return CHATBOX_L6;	
			}
			
			$pref 			= e107::getPref();
			$emotes_active 	= $pref['cb_emote'] ? 'USER_BODY, emotes_on' : 'USER_BODY, emotes_off';
			
			list($cb_uid, $cb_nick) = explode(".", $this->var['cb_nick'], 2);
			
			$cb_message = e107::getParser()->toHTML($this->var['cb_message'], false, $emotes_active, $cb_uid, $pref['menu_wordwrap']);
	
			return $cb_message;
			/*
			$replace[0] = "["; $replace[1] = "]";
			$search[0] = "&lsqb;"; $search[1] =  "&rsqb;";
			$cb_message = str_replace($search, $replace, $cb_message);
			*/
		}
	
		function sc_cb_avatar($parm='')
		{
			return e107::getParser()->toAvatar($this->var); // parseTemplate("{USER_AVATAR=".vartrue($this->var['user_image'])."}");
		}
		
		function sc_cb_bullet($parm = '')
		{
			$bullet = "";
			
			if(defined('BULLET'))
			{
				$bullet = '<img src="'.THEME_ABS.'images/'.BULLET.'" alt="" class="icon" />';
			}
			elseif(file_exists(THEME.'images/bullet2.gif'))
			{
				$bullet = '<img src="'.THEME_ABS.'images/bullet2.gif" alt="" class="icon" />';
			}	
			
			return $bullet;
		}
	
	}
}



if((isset($_POST['chat_submit']) || e_AJAX_REQUEST) && $_POST['cmessage'] != '')
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
				if($sql->select("chatbox", "*", "cb_message='$cmessage' AND cb_datestamp+84600>".time()))
				{
					$emessage = CHATBOX_L17;
				}
				else
				{
					$datestamp = time();
					$ip = e107::getIPHandler()->getIP(FALSE);
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
						if($sql->select("user", "*", "user_name='$nick' ")){
							$emessage = CHATBOX_L1;
						}
						else
						{
							$nick = "0.".$nick;
						}
					}
					if(!$emessage)
					{
						$insertId = $sql->insert("chatbox", "0, '{$nick}', '{$cmessage}', '{$datestamp}', '0' , '{$ip}' ");
						
						if($insertId)
						{
							$edata_cb = array("id" => $insertId, "nick" => $nick, "cmessage" => $cmessage, "datestamp" => $datestamp, "ip" => $ip);
							$e_event->trigger("cboxpost", $edata_cb); // deprecated
							e107::getEvent()->trigger('user_chatbox_post_created', $edata_cb);
							$e107cache->clear("nq_chatbox");
						}
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
		$text1 = str_replace(array('[',']'), array("<a href='".e_LOGIN."'>", "</a>"), CHATBOX_L3);
		if($pref['user_reg'] == 1 ){
		  $text1 .= str_replace(array('[',']'), array("<a href='".e_SIGNUP."'>", "</a>"), CHATBOX_L3b);		
		}
		$texta = "<div style='text-align:center'>".$text1."</div><br /><br />";
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
	$texta .= "<div class='control-group form-group' id='chatbox-input-block'>";

	if(($pref['anon_post'] == "1" && USER == FALSE))
	{
		$texta .= "\n<input class='tbox chatbox' type='text' id='nick' name='nick' value='' maxlength='50' ".($cb_width ? "style='width: ".$cb_width.";'" : '')." /><br />";
	}

	if($pref['cb_layer'] == 2)
	{

		$oc = "onclick=\"javascript:sendInfo('".SITEURLBASE.e_PLUGIN_ABS."chatbox_menu/chatbox_menu.php', 'chatbox_posts', this.form);\"";
	}
	else
	{
		$oc = "";
	}
	$texta .= "
	<textarea placeholder=\"".LAN_CHATBOX_100."\" required class='tbox chatbox form-control input-xlarge' id='cmessage' name='cmessage' cols='20' rows='5' style='max-width:97%; ".($cb_width ? "width:".$cb_width.";" : '')." overflow: auto' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>
	<br />
	<input class='btn btn-default btn-secondary button' type='submit' id='chat_submit' name='chat_submit' value='".CHATBOX_L4."' {$oc}/>
	";
	
	// $texta .= "<input type='reset' name='reset' value='".CHATBOX_L5."' />"; // How often do we see these lately? ;-)

	if($pref['cb_emote'] && $pref['smiley_activate'])
	{
		$texta .= "
		<input class='btn btn-default btn-secondary button' type='button' style='cursor:pointer' size='30' value='".CHATBOX_L14."' onclick=\"expandit('emote')\" />
		<div class='well' style='display:none' id='emote'>".r_emote()."</div>\n";
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

	if(!defined('CB_MOD'))
	{
		define("CB_MOD", check_class($pref['cb_mod']));
	}

	$qry = "
	SELECT c.*, u.user_name, u.user_image FROM #chatbox AS c
	LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
	ORDER BY c.cb_datestamp DESC LIMIT 0, ".intval($chatbox_posts);

	global $CHATBOXSTYLE;
	
	if($CHATBOXSTYLE)
	{
		$legacySrch = array('{USERNAME}','{MESSAGE}','{TIMEDATE}');
		$legacyRepl = array('{CB_USERNAME}','{CB_MESSAGE}','{CB_TIMEDATE}');	
		
		
		$CHATBOX_TEMPLATE['start'] = "";
		$CHATBOX_TEMPLATE['item'] = str_replace($legacySrch,$legacyRepl,$CHATBOXSTYLE);
		$CHATBOX_TEMPLATE['end'] = "";
	}
	else 	// default chatbox style
	{
		$tp->parseTemplate("{SETIMAGE: w=40}",true); // set thumbnail size. 
		// FIXME - move to template
		$CHATBOX_TEMPLATE['start'] 	= "<ul class='media-list unstyled list-unstyled'>";
		$CHATBOX_TEMPLATE['item'] 	= "<li class='media'>
										<span class='media-object pull-left'>{CB_AVATAR}</span> 
										<div class='media-body'><b>{CB_USERNAME}</b>&nbsp;
										<small class='muted smalltext'>{CB_TIMEDATE}</small><br />
										<p>{CB_MESSAGE}</p>
										</div>
										</li>\n";
										
		$CHATBOX_TEMPLATE['end'] 	= "</ul>";
	}
		
	// FIX - don't call getScBatch() if don't need to globally register the methods
	// $sc = e107::getScBatch('chatbox');
	
	// the good way in this case - it works with any object having sc_*, models too
	$sc = new chatbox_shortcodes();		

	if($sql->gen($qry))
	{
		$cbpost = $sql->db_getList();
		$text .= "<div id='chatbox-posts-block'>\n";

		$text .= $tp->parseTemplate($CHATBOX_TEMPLATE['start'], false, $sc);

		foreach($cbpost as $cb)
		{
			$sc->setVars($cb);
			$text .= $tp->parseTemplate($CHATBOX_TEMPLATE['item'], false, $sc);
		}
		
		$text .= $tp->parseTemplate($CHATBOX_TEMPLATE['end'], false, $sc);

		$text .= "</div>";
	}
	else
	{
		$text .= "<span class='mediumtext'>".CHATBOX_L11."</span>";
	}
	
	
	$total_chats = $sql->count("chatbox");
	if($total_chats > $chatbox_posts || CB_MOD)
	{
		$text .= "<br /><div style='text-align:center'><a href='".e_PLUGIN_ABS."chatbox_menu/chat.php'>".(CB_MOD ? CHATBOX_L13 : CHATBOX_L12)."</a> (".$total_chats.")</div>";
	}
	$e107cache->set("nq_chatbox", $text);
}





$caption = (file_exists(THEME."images/chatbox_menu.png") ? "<img src='".THEME_ABS."images/chatbox_menu.png' alt='' /> ".LAN_PLUGIN_CHATBOX_MENU_NAME : LAN_PLUGIN_CHATBOX_MENU_NAME);

if($pref['cb_layer'] == 1)
{
	$text = $texta."<div style='border : 0; padding : 4px; width : auto; height : ".$pref['cb_layer_height']."px; overflow : auto; '>".$text."</div>";
	$ns -> tablerender($caption, $text, 'chatbox');
}
elseif($pref['cb_layer'] == 2 && e_AJAX_REQUEST)
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
