<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Tree menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tree_menu/config.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-08-15 11:55:30 $
 * $Author: marj_nl_fr $
 *
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
include_lan(e_PLUGIN."tree_menu/languages/".e_LANGUAGE.".php");

if (!getperms("4")) 
{
	header("location:".e_BASE."index.php");
	exit ;
}
require_once(e_ADMIN."auth.php");
	
if (isset($_POST['update_menu'])) 
{
	$temp = array();
	foreach($_POST as $key => $value) 
	{
		if ($value != TREE_L2) 
		{
			$temp[$key] = $value;
		}
	}

	if ($admin_log->logArrayDiffs($temp,$menu_pref,'MISC_01'))
	{
		$tmp = addslashes(serialize($menu_pref));
		$sql->db_Update("core", "e107_value='{$tmp}' WHERE e107_name='menu_pref' ");
	}
	$ns->tablerender("", "<div style='text-align:center'><b>".TREE_L3."</b></div>");
}
	
$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
	<table style='".ADMIN_WIDTH."' class='fborder' >
	 
	<tr>
	<td style='width: 50%;' class='forumheader3'>".TREE_L6."</td>
	<td style='width: 50%;' class='forumheader3'><input class='tbox' type='text' name='tm_class1' size='40' value='".$menu_pref['tm_class1']."' maxlength='20' /></td>
	</tr>

	<tr>
	<td style='width: 50%;' class='forumheader3'>".TREE_L7."</td>
	<td style='width: 50%;' class='forumheader3'><input class='tbox' type='text' name='tm_class2' size='40' value='".$menu_pref['tm_class2']."' maxlength='20' /></td>
	</tr>

	<tr>
	<td style='width: 50%;' class='forumheader3'>".TREE_L8."</td>
	<td style='width: 50%;' class='forumheader3'><input class='tbox' type='text' name='tm_class3' size='40' value='".$menu_pref['tm_class3']."' maxlength='20' /></td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".TREE_L9."</td>
	<td style='width:50%; text-align:right' class='forumheader3'>
	<input type='radio' name='tm_spacer' value='1'".($menu_pref['tm_spacer'] ? " checked='checked'" : "")." /> ".TREE_L4."&nbsp;&nbsp;
	<input type='radio' name='tm_spacer' value='0'".(!$menu_pref['tm_spacer'] ? " checked='checked'" : "")." /> ".TREE_L5."
	</td>
	</tr>

	
	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='".TREE_L2."' /></td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(TREE_L1, $text);
	
require_once(e_ADMIN."footer.php");

?>