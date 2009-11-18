<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Comment menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/comment_menu/config.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
 *
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
	
include_lan(e_PLUGIN."comment_menu/languages/".e_LANGUAGE.".php");
if (!getperms("1")) 
{
	header("location:".e_BASE."index.php");
	exit() ;
}
require_once(e_ADMIN."auth.php");
	
$menu_config = e107::getConfig('menu'); 
if (isset($_POST['update_menu'])) 
{
	$temp = $old = $menu_config->getPref();
	
	$tp = e107::getParser();
	while (list($key, $value) = each($_POST)) 
	{
		if ($value != CM_L9) 
		{
			$temp[$tp->toDB($key)] = $tp->toDB($value);
		}
	}
	if (!$_POST['comment_title']) 
	{
		$temp['comment_title'] = 0;
	}
	
	$menu_config->setPref($temp);
	if ($admin_log->logArrayDiffs($old, $menu_config->getPref(), 'MISC_04'))
	{
		if($menu_config->save(false))
		{
			e107::getMessage()->add(CM_L10, E_MESSAGE_SUCCESS);
		}
	}
	else
	{
		e107::getMessage()->add(LAN_NO_CHANGE);
	}
}

// TODO - 0.8 aware markup, e_form usage
$text = "<div style='text-align:center'>
	<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\" id=\"plugin-menu-config-form\">
	<table style=\"width:85%\" class=\"fborder\" >
	 
	<tr>
	<td style=\"width:40%\" class='forumheader3'>".CM_L3.": </td>
	<td style=\"width:60%\" class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"comment_caption\" size=\"20\" value=\"".$menu_config->get('comment_caption')."\" maxlength=\"100\" />
	</td>
	</tr>
	 
	<tr>
	<td style=\"width:40%\" class='forumheader3'>".CM_L4.": </td>
	<td style=\"width:60%\" class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"comment_display\" size=\"20\" value=\"".$menu_config->get('comment_display')."\" maxlength=\"2\" />
	</td>
	</tr>
	 
	<tr>
	<td style=\"width:40%\" class='forumheader3'>".CM_L5.": </td>
	<td style=\"width:60%\" class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"comment_characters\" size=\"20\" value=\"".$menu_config->get('comment_characters')."\" maxlength=\"4\" />
	</td>
	</tr>
	 
	<tr>
	<td style=\"width:40%\" class='forumheader3'>".CM_L6.": </td>
	<td style=\"width:60%\" class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"comment_postfix\" size=\"30\" value=\"".$menu_config->get('comment_postfix')."\" maxlength=\"200\" />
	</td>
	</tr>
	 
	<tr>
	<td style=\"width:40%\" class='forumheader3'>".CM_L7.": </td>
	<td style=\"width:60%\" class='forumheader3'>
	<input type=\"checkbox\" name=\"comment_title\" value=\"1\"".($menu_config->get('comment_title') ? ' checked="checked"' : '')." />
	</td>
	</tr>
	 
	<tr>
	<td colspan=\"2\" class='forumheader' style=\"text-align:center\"><input class=\"button\" type=\"submit\" name=\"update_menu\" value=\"".CM_L9."\" /></td>
	</tr>
	</table>
	</form>
	</div>";
	
e107::getRender()->tablerender(CM_L8, e107::getMessage()->render().$text);
require_once(e_ADMIN."footer.php");
?>