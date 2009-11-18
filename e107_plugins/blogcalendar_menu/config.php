<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Blog calendar menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/blogcalendar_menu/config.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:05:23 $
 * $Author: e107coders $
 *
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
	
include_lan(e_PLUGIN."blogcalendar_menu/languages/".e_LANGUAGE.".php");
if (!getperms("1")) 
{
	header("location:".e_BASE."index.php");
	 exit ;
}
require_once(e_ADMIN."auth.php");
	
if (isset($_POST['update_menu'])) 
{
	$temp = array();
	while (list($key, $value) = each($_POST)) 
	{
		if ($value != BLOGCAL_CONF3) 
		{
			$temp[$key] = $value;
		}
	}
	if ($admin_log->logArrayDiffs($temp,$pref,'MISC_06'))
	{
		save_prefs();
	}
	$ns->tablerender("", "<div style='text-align:center'><b>".BLOGCAL_CONF5."</b></div>");
}
	
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
	<table style='width:85%' class='fborder' >
	 
	<tr>
	<td style='width:40%' class='forumheader3'>".BLOGCAL_CONF1.": </td>
	<td style='width:60%' class='forumheader3'>
	<select class='tbox' name='blogcal_mpr'>";
	
// if the nr of months per row is undefined, default to 3
$months_per_row = $pref['blogcal_mpr']?$pref['blogcal_mpr']:
"3";
for($i = 1; $i <= 12; $i++) {
	$text .= "<option value='$i'";
	$text .= $months_per_row == $i?"selected":
	"";
	$text .= ">$i</option>";
}
	
$text .= "</select>
	</td>
	</tr>
	 
	<tr>
	<td style='width:40%' class='forumheader3'>".BLOGCAL_CONF2.": </td>
	<td style='width:60%' class='forumheader3'>
	<input class='tbox' type='text' name='blogcal_padding' size='20' value='";
// if the cellpadding isn't defined
$padding = $pref['blogcal_padding']?$pref['blogcal_padding']:
"2";
$text .= $padding;
$text .= "' maxlength='100' />
	</td>
	</tr>
	 
	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'>
	<input class='button' type='submit' name='update_menu' value='".BLOGCAL_CONF3."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(BLOGCAL_CONF4, $text);
	
require_once(e_ADMIN."footer.php");
?>