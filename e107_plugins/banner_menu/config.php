<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
|	Based on code by Edwin van der Wal (evdwal@xs4all.nl)
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['update_menu'])){
	while(list($key, $value) = each($_POST)){
		$menu_pref[$key] = $value;
	}
	if(!$found){unset($menu_pref['articles_parents']);}
	$tmp = addslashes(serialize($menu_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns -> tablerender("", "<div style='text-align:center'><b>Articles menu configuration saved</b></div>");
}

if(!$menu_pref['banner_caption']){
	$menu_pref['banner_caption'] = "Advertisement";
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>Caption: </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='banner_caption' size='20' value='".$menu_pref['banner_caption']."' maxlength='100' />
</td>
</tr>


<tr>
<td style='width:40%' class='forumheader3'>Campaign: </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='banner_campaign' size='30' value='".$menu_pref['banner_campaign']."' maxlength='100' />
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='Update Menu Settings' /></td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("Banner Menu Configuration", $text);

require_once(e_ADMIN."footer.php");
?>