<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

$eplug_admin = TRUE;
require_once('../../class2.php');
if (!getperms('1'))
{
	header('location:'.e_BASE.'index.php');
	 exit();
}
require_once(e_ADMIN.'auth.php');
$mes = e107::getMessage();
e107::lan('forum','menu',true); // English_menu.php or {LANGUAGE}_menu.php	
// include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_newforumposts_menu.php');

if (isset($_POST['update_menu'])) {
	while (list($key, $value) = each($_POST)) {
		if ($value != LAN_FORUM_MENU_009) {
			$menu_pref[$key] = $value;
		}
	}
	if (!$_POST['newforumposts_title']) {
		$menu_pref['newforumposts_title'] = 0;
	}
	$tmp = addslashes(serialize($menu_pref));
	$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns->tablerender("", "<div style=\"text-align:center\"><b>".LAN_FORUM_MENU_003."</b></div>");
}

$menu_pref['newforumposts_maxage'] = varset($menu_pref['newforumposts_maxage'],0);

$text = "<div>
	<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\" name=\"menu_conf_form\">
	<table style='width:85%' class='table table-bordered table-striped'>
	<colgroup>
	  <col style='width:40%;' />
	  <col style='width:60%;' />
	</colgroup>
	<tr>
	<td class='forumheader3'>".LAN_FORUM_MENU_004.": </td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_caption\" size=\"20\" value=\"".$menu_pref['newforumposts_caption']."\" maxlength=\"100\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".LAN_FORUM_MENU_005.":
	</td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_display\" size=\"20\" value=\"".$menu_pref['newforumposts_display']."\" maxlength=\"2\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".LAN_FORUM_MENU_0012.":
	</td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name='newforumposts_maxage' size='20' value='".$menu_pref['newforumposts_maxage']."' maxlength='3' /><br />
	<span class='smalltext'><em>".LAN_FORUM_MENU_0013."</em></span>
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".LAN_FORUM_MENU_006.": </td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_characters\" size=\"20\" value=\"".$menu_pref['newforumposts_characters']."\" maxlength=\"4\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".LAN_FORUM_MENU_007.":
	</td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_postfix\" size=\"30\" value=\"".$menu_pref['newforumposts_postfix']."\" maxlength=\"200\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".LAN_FORUM_MENU_008.":</td>
	<td class='forumheader3'>
	<input type='checkbox' name='newforumposts_title' value='1'".($menu_pref['newforumposts_title'] ? ' checked="checked"' : '')."
	</td>
	</tr>
	 
	<tr style=\"vertical-align:top\">
	<td colspan=\"2\"  style=\"text-align:center\" class='forumheader'>
	<input class=\"button\" type=\"submit\" name=\"update_menu\" value=\"".LAN_FORUM_MENU_009."\" />
	</td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(LAN_FORUM_MENU_0010, $mes->render(). $text);
require_once(e_ADMIN."footer.php");