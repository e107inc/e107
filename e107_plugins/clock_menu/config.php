<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/menu_conf/comment_conf.php
|
|	©Edwin van der Wal 2003
|	http://e107.org
|	evdwal@xs4all.nl
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['update_menu'])){
	while(list($key, $value) = each($_POST)){
		if($value != "Update Menu Settings"){ $menu_pref[$key] = $value; }
	}
	$tmp = addslashes(serialize($menu_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Comments menu configuration saved</b></div>");
}

$text = "<div style='text-align:center'>
<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\" name=\"menu_conf_form\">
<table style=\"width:85%\" class=\"fborder\">

<tr>
<td style=\"width:40%\" class='forumheader3'>Caption: </td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"clock_caption\" size=\"20\" value=\"".$menu_pref['clock_caption']."\" maxlength=\"100\" />
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
$ns -> tablerender("Clock Menu Config", $text);
require_once(e_ADMIN."footer.php");
?>