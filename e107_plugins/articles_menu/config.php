<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/menu_conf/articles_conf.php
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
		if($key=="articles_parents"){$value="1"; $found=1;}
		if($value != "Update Menu Settings"){ 
			$menu_pref[$key] = $value; 
		}
	}
	if(!$found){unset($menu_pref['articles_parents']);}
	$tmp = addslashes(serialize($menu_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns -> tablerender("", "<div style='text-align:center'><b>Articles menu configuration saved</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>Caption: </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='article_caption' size='20' value='".$menu_pref['article_caption']."' maxlength='100' />
</td>
</tr>


<tr>
<td style='width:40%' class='forumheader3'>Number of articles to display: </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='articles_display' size='6' value='".$menu_pref['articles_display']."' maxlength='3' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>Show article categories in menu?</td>
<td style='width:60%' class='forumheader3'>
<input type='checkbox' name='articles_parents' value='".$menu_pref['articles_parents']."' ".($menu_pref['articles_parents'] ? "checked" : "")." />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>Title for articles list page: 
</td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='articles_mainlink' size='30' value='".$menu_pref['articles_mainlink']."' maxlength='200' />
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='Update Menu Settings' /></td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("Articles Menu Configuration", $text);

require_once(e_ADMIN."footer.php");
?>