<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/menu_conf/newforumposts_conf.php
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
		if($value != "Update Menu Settings"){ $menu_pref[$key] = $value; }
	}
	if(!$_POST['newforumposts_title']){ $menu_pref['newforumposts_title'] = 0; }
	$tmp = addslashes(serialize($menu_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>New Forum Posts menu configuration saved</b></div>");
}

$text = "<div style='text-align:center'>
<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\" name=\"menu_conf_form\">
<table style=\"width:85%\" class=\"fborder\">

<tr>
<td style=\"width:40%\" class='forumheader3'>Caption: </td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_caption\" size=\"20\" value=\"".$menu_pref['newforumposts_caption']."\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>Number of posts to display?: 
</td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_display\" size=\"20\" value=\"".$menu_pref['newforumposts_display']."\" maxlength=\"2\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>Number of characters to display?: </td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_characters\" size=\"20\" value=\"".$menu_pref['newforumposts_characters']."\" maxlength=\"4\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>Postfix for too long posts?:
</td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_postfix\" size=\"30\" value=\"".$menu_pref['newforumposts_postfix']."\" maxlength=\"200\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>Show original topics in menu?:</td>
<td style=\"width:60%\" class='forumheader3'>
<input type=\"checkbox\" name=\"newforumposts_title\" value=\"1\" ";
if ($menu_pref['newforumposts_title']) {
	$text .= " checked ";
} 
$text .= "
</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\" class='forumheader'>
<input class=\"button\" type=\"submit\" name=\"update_menu\" value=\"Update menu Settings\" />
</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("New Forum Posts Menu Configuration", $text);
require_once(e_ADMIN."footer.php");
?>