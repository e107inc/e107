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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/newforumposts_menu_config.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-19 09:52:35 $
 * $Author: marj_nl_fr $
 */

$eplug_admin = TRUE;
require_once('../../class2.php');
if (!getperms('1'))
{
	header('location:'.e_BASE.'index.php');
	 exit();
}
require_once(e_ADMIN.'auth.php');
	
include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_newforumposts_menu.php');

if (isset($_POST['update_menu'])) {
	while (list($key, $value) = each($_POST)) {
		if ($value != NFP_9) {
			$menu_pref[$key] = $value;
		}
	}
	if (!$_POST['newforumposts_title']) {
		$menu_pref['newforumposts_title'] = 0;
	}
	$tmp = addslashes(serialize($menu_pref));
	$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns->tablerender("", "<div style=\"text-align:center\"><b>".NFP_3."</b></div>");
}

$menu_pref['newforumposts_maxage'] = varset($menu_pref['newforumposts_maxage'],0);

$text = "<div style='text-align:center'>
	<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\" name=\"menu_conf_form\">
	<table style='width:85%' class=\"fborder\">
	<colgroup>
	  <col style='width:40%;' />
	  <col style='width:60%;' />
	</colgroup>
	<tr>
	<td class='forumheader3'>".NFP_4.": </td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_caption\" size=\"20\" value=\"".$menu_pref['newforumposts_caption']."\" maxlength=\"100\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".NFP_5.":
	</td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_display\" size=\"20\" value=\"".$menu_pref['newforumposts_display']."\" maxlength=\"2\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".NFP_12.":
	</td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name='newforumposts_maxage' size='20' value='".$menu_pref['newforumposts_maxage']."' maxlength='3' /><br />
	<span class='smalltext'><em>".NFP_13."</em></span>
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".NFP_6.": </td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_characters\" size=\"20\" value=\"".$menu_pref['newforumposts_characters']."\" maxlength=\"4\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".NFP_7.":
	</td>
	<td class='forumheader3'>
	<input class=\"tbox\" type=\"text\" name=\"newforumposts_postfix\" size=\"30\" value=\"".$menu_pref['newforumposts_postfix']."\" maxlength=\"200\" />
	</td>
	</tr>
	 
	<tr>
	<td class='forumheader3'>".NFP_8.":</td>
	<td class='forumheader3'>
	<input type=\"checkbox\" name=\"newforumposts_title\" value=\"1\" ";
if ($menu_pref['newforumposts_title']) {
	$text .= " checked ";
}
$text .= "
	</td>
	</tr>
	 
	<tr style=\"vertical-align:top\">
	<td colspan=\"2\"  style=\"text-align:center\" class='forumheader'>
	<input class=\"button\" type=\"submit\" name=\"update_menu\" value=\"".NFP_9."\" />
	</td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(NFP_10, $text);
require_once(e_ADMIN."footer.php");
?>