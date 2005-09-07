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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/pm_shortcodes.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-09-07 02:51:18 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
include_once(e_HANDLER.'shortcode_handler.php');
include_once(e_PLUGIN.'pm/pm_func.php');
$pm_shortcodes = e_shortcode::parse_scbatch(__FILE__);
		
/*
SC_BEGIN FORM_TOUSER
global $pm_prefs, $pm_info;
if($pm_info['from_name'])
{
	return "<input type='hidden' name='pm_to' value='{$pm_info['from_name']}' />{$pm_info['from_name']}";
}
require_once(e_HANDLER."user_select_class.php");
$us = new user_select;
$type = ($pm_prefs['dropdown'] == TRUE ? 'list' : 'popup');
if(check_class($pm_prefs['multi_class']))
{
	$ret = $us->select_form($type, 'textarea.pm_to');
}
else
{
	$ret = $us->select_form($type, 'pm_to');
}
return $ret;
SC_END

SC_BEGIN FORM_TOCLASS
global $pm_prefs, $pm_info;
if($pm_info['from_name'])
{
	return "";
}
if($pm_prefs['allow_userclass'])
{
	$ret = "<input type='checkbox' name='to_userclass' value='1' />".LAN_PM_4." ";
	require_once(e_HANDLER."userclass_class.php");
	$args = (ADMIN ? "admin, classes" : "matchclass");
	if(check_class($pm_prefs['sendall_class']))
	{
		$args = "member, ".$args;
	}
	$ret .= r_userclass("pm_userclass", "", "off", $args);
}
return $ret;
SC_END

SC_BEGIN FORM_SUBJECT
global $pm_info;
$value = "";
if($pm_info['pm_subject'])
{
	$value = $pm_info['pm_subject'];
	if(substr($value, 0, strlen(LAN_PM_58)) != LAN_PM_58)
	{
		$value = LAN_PM_58.$value;
	}
}
return "<input class='tbox' type='text' name='pm_subject' value='{$value}' size='63' maxlength='255' />";
SC_END

SC_BEGIN FORM_MESSAGE
global $pm_info;
$value = "";
if($pm_info['pm_text'])
{
	if(isset($_POST['quote']))
	{
		$t = time();
		$value = "[quote{$t}={$pm_info['from_name']}]\n{$pm_info['pm_text']}\n[/quote{$t}]\n\n";
	}
}
return "<textarea class='tbox' name='pm_message' cols='60' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>{$value}</textarea>";
SC_END

SC_BEGIN EMOTES
require_once(e_HANDLER."emote.php");
return r_emote();
SC_END

SC_BEGIN PM_POST_BUTTON
return "<input class='button' type='submit' name='postpm' value='".LAN_PM_1."' />";
SC_END

SC_BEGIN PM_PREVIEW_BUTTON
return "<input class='button' type='submit' name='postpm' value='".LAN_PM_3."' />";
SC_END

SC_BEGIN ATTACHMENT
global $pm_prefs;
if (check_class($pm_prefs['attach_class']))
{
	$ret = "
	<div id='up_container' >
	<span id='upline' style='white-space:nowrap'>
	<input class='tbox' type='file' name='file_userfile[]' size='40' />
	</span>
	</div>
	<input type='button' class='button' value='".LAN_PM_11."' onclick=\"duplicateHTML('upline','up_container');\"  />
	";
	return $ret;
}
return "";
SC_END

SC_BEGIN PM_ATTACHMENT_ICON
global $pm_info;
if($pm_info['pm_attachments'] != "")
{
	return ATTACHMENT_ICON;
}
SC_END

SC_BEGIN PM_ATTACHMENTS
global $pm_info;
if($pm_info['pm_attachments'] != "")
{
	$attachments = explode(chr(0), $pm_info['pm_attachments']);
	$i = 0;
	foreach($attachments as $a)
	{
		list($timestamp, $fromid, $rand, $filename) = explode("_", $a, 4);
		$ret .= "<a href='".e_SELF."?get.{$pm_info['pm_id']}.{$i}'>{$filename}</a><br />";
		$i++;
	}
	$ret = substr($ret, 0, -3);
	return $ret;
}
SC_END

SC_BEGIN RECEIPT
global $pm_prefs;
if (check_class($pm_prefs['receipt_class']))
{
	return "<input type='checkbox' name='receipt' value='1' />".LAN_PM_10;
}
return "";
SC_END

SC_BEGIN INBOX_TOTAL
$pm_inbox = pm_getInfo('inbox');
return $pm_inbox['inbox']['total'];
SC_END

SC_BEGIN INBOX_UNREAD
$pm_inbox = pm_getInfo('inbox');
return $pm_inbox['inbox']['unread'];
SC_END

SC_BEGIN INBOX_FILLED
$pm_inbox = pm_getInfo('inbox');
return $pm_inbox['inbox']['filled'];
SC_END

SC_BEGIN OUTBOX_TOTAL
$pm_outbox = pm_getInfo('outbox');
return $pm_outbox['outbox']['total'];
SC_END

SC_BEGIN OUTBOX_UNREAD
$pm_outbox = pm_getInfo('outbox');
return $pm_outbox['outbox']['unread'];
SC_END

SC_BEGIN OUTBOX_FILLED
$pm_outbox = pm_getInfo('outbox');
return $pm_outbox['outbox']['filled'];
SC_END

SC_BEGIN PM_DATE
global $pm_info;
require_once(e_HANDLER."date_handler.php");
if("lapse" != $parm)
{
	return convert::convert_date($pm_info['pm_sent'], $parm);
}
else
{
	return convert::computeLapse($pm_info['pm_sent']);
}
SC_END

SC_BEGIN PM_READ
global $pm_info;
if($pm_info['pm_read'] == 0)
{
	return LAN_PM_27;
}
if($pm_info['pm_read'] == 1)
{
	return LAN_PM_28;
}
require_once(e_HANDLER."date_handler.php");
if("lapse" != $parm)
{
	return convert::convert_date($pm_info['pm_read'], $parm);
}
else
{
	return convert::computeLapse($pm_info['pm_read']);
}
SC_END


SC_BEGIN PM_FROM_TO
global $pm_info, $tp;
if($pm_info['pm_from'] == USERID)
{
	$ret = "To: <br />";
	$pm_info['user_name'] = $pm_info['sent_name'];
	$ret .= $tp->parseTemplate("{PM_TO=link}");
}
else
{
	$ret = "From: <br />";
	$pm_info['user_name'] = $pm_info['from_name'];
	$ret .= $tp->parseTemplate("{PM_FROM=link}");
}	
return $ret;
SC_END

SC_BEGIN PM_SUBJECT
global $pm_info, $tp;
$ret = $tp->toHTML($pm_info['pm_subject'], true);
if('link' == $parm)
{
	$ret = "<a href='".e_PLUGIN_ABS."pm/pm.php?show.{$pm_info['pm_id']}'>".$ret."</a>";
}
return $ret; 
SC_END

SC_BEGIN PM_FROM
global $pm_info;
if("link" == $parm)
{
	return "<a href='".e_BASE."user.php?id.{$pm_info['pm_from']}'>{$pm_info['user_name']}</a>";
}
else
{
	return $pm_info['user_name'];
}
SC_END

SC_BEGIN PM_SELECT
global $pm_info;
return "<input type='checkbox' name='selected_pm[{$pm_info['pm_id']}]' value='1' />";
SC_END

SC_BEGIN PM_READ_ICON
global $pm_info;
if($pm_info['pm_read'] > 0 )
{
	return PM_READ_ICON;
}
else
{
	return PM_UNREAD_ICON;
}
SC_END

SC_BEGIN PM_AVATAR
global $pm_info, $tp;
return $tp->parseTemplate("{USER_AVATAR={$pm_info['user_image']}}");
SC_END

SC_BEGIN PM_BLOCK_USER
global $pm_info, $pm_blocks;
if(in_array($pm_info['pm_from'], $pm_blocks))
{
	return "<a href='".e_PLUGIN_ABS."pm/pm.php?unblock.{$pm_info['pm_from']}'><img src='".e_PLUGIN_ABS."pm/images/mail_unblock.png' title='".LAN_PM_51."' alt='".LAN_PM_51."' style='width: 16px; height: 16px; border: 0px' /></a>";
}
else
{
	return "<a href='".e_PLUGIN_ABS."pm/pm.php?block.{$pm_info['pm_from']}'><img src='".e_PLUGIN_ABS."pm/images/mail_block.png' title='".LAN_PM_50."' alt='".LAN_PM_50."' style='width: 16px; height: 16px; border: 0px' /></a>";
}
SC_END

SC_BEGIN PM_DELETE
global $pm_info;
$extra = $parm != "" ? ".{$parm}" : "";
return "<a href='".e_PLUGIN_ABS."pm/pm.php?del.{$pm_info['pm_id']}{$extra}'><img src='".e_PLUGIN_ABS."pm/images/mail_delete.png' title='".LAN_PM_52."' alt='".LAN_PM_52."' style='width: 16px; height: 16px; border: 0px' /></a>";
SC_END

SC_BEGIN DELETE_SELECTED
global $pm_info;
return "<input type='submit' name='pm_delete_selected' class='button' value='".LAN_PM_53."' />";
SC_END

SC_BEGIN PM_TO
global $pm_info;
if(is_numeric($pm_info['pm_to']))
{
	if("link" == $parm)
	{
		return "<a href='".e_BASE."user.php?id.{$pm_info['pm_to']}'>{$pm_info['user_name']}</a>";
	}
	else
	{
		return $pm_info['user_name'];
	}
}
else
{
	return "class: ".$pm_info['pm_to'];
}
SC_END

SC_BEGIN PM_MESSAGE
global $pm_info, $tp;
return $tp->toHTML($pm_info['pm_text'], true);
SC_END

SC_BEGIN PM_REPLY
global $pm_info;
if($pm_info['pm_to'] == USERID)
{
	$ret = "
	<form method='post' action='".e_SELF."?reply.{$pm_info['pm_id']}'>
	<input type='checkbox' name='quote' /> ".LAN_PM_54." &nbsp;&nbsp;&nbsp<input class='button' type='submit' name='reply' value='".LAN_PM_55."' />
	</form>
	";
	return $ret;
}
SC_END

SC_BEGIN SEND_PM_LINK
$pm_outbox = pm_getInfo('outbox');
if($pm_outbox['outbox']['filled'] < 100)
{
	return "<a href='".e_PLUGIN_ABS."pm/pm.php?send'>".PM_SEND_LINK."</a>";
}
return "";
SC_END

SC_BEGIN NEWPM_ANIMATE
global $pm_prefs, $pm_inbox;
if($pm_prefs['animate'])
{
	$pm_inbox = pm_getInfo('inbox');
	if($pm_inbox['inbox']['new'] > 0)
	{
		return NEWPM_ANIMATION;
	}
}
return "";
SC_END

SC_BEGIN PM_NEXTPREV
global $pmlist, $tp, $pm_start, $pm_prefs;
//$sc = "{NEXTPREV={$pmlist['total_messages'][0]}.10.{$pm_start}.".e_SELF."?inbox}";
//return $sc;
return $tp->parseTemplate("{NEXTPREV={$pmlist['total_messages'][0]},{$pm_prefs['perpage']},{$pm_start},".e_SELF."?inbox.[FROM]}");
SC_END

*/
?>