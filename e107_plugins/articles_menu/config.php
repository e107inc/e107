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
|	Based on code by Edwin van der Wal (evdwal@xs4all.nl), Multilanguage by Juan
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");

$lan_file=e_PLUGIN."articles_menu/languages/".e_LANGUAGE.".php";
if(file_exists($lan_file)){
	require_once($lan_file);
} else {
	require_once(e_PLUGIN."articles_menu/languages/English.php");
}
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['update_menu'])){
	while(list($key, $value) = each($_POST)){
		if($key=="articles_parents"){$value="1"; $found=1;}
		if($value != ARTICLE_MENU_L8){ 
			$menu_pref[$key] = $value; 
		}
	}
	if(!$found){unset($menu_pref['articles_parents']);}
	$tmp = addslashes(serialize($menu_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns -> tablerender("", "<div style='text-align:center'><b>".ARTICLE_MENU_L9."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>".ARTICLE_MENU_L3.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='article_caption' size='20' value='".$menu_pref['article_caption']."' maxlength='100' />
</td>
</tr>


<tr>
<td style='width:40%' class='forumheader3'>".ARTICLE_MENU_L4.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='articles_display' size='6' value='".$menu_pref['articles_display']."' maxlength='3' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".ARTICLE_MENU_L5.":</td>
<td style='width:60%' class='forumheader3'>
<input type='checkbox' name='articles_parents' value='".$menu_pref['articles_parents']."' ".($menu_pref['articles_parents'] ? "checked" : "")." />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".ARTICLE_MENU_L6.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='articles_mainlink' size='30' value='".$menu_pref['articles_mainlink']."' maxlength='200' />
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='".ARTICLE_MENU_L8."' /></td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("".ARTICLE_MENU_L7."", $text);

require_once(e_ADMIN."footer.php");
?>