<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newforumposts_menu/config.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-18 16:11:55 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

$lan_file = e_PLUGIN."newforumposts_menu/languages/".e_LANGUAGE.".php";
require_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."newforumposts_menu/languages/English.php");


if(isset($_POST['update_menu'])){
        while(list($key, $value) = each($_POST)){
                if($value != NFP_9){ $menu_pref[$key] = $value; }
        }
        if(!$_POST['newforumposts_title']){ $menu_pref['newforumposts_title'] = 0; }
        $tmp = addslashes(serialize($menu_pref));
        $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
        $ns -> tablerender("", "<div style=\"text-align:center\"><b>".NFP_3."</b></div>");
}

$text = "<div style='text-align:center'>
<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\" name=\"menu_conf_form\">
<table style=\"width:85%\" class=\"fborder\">

<tr>
<td style=\"width:40%\" class='forumheader3'>".NFP_4.": </td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_caption\" size=\"20\" value=\"".$menu_pref['newforumposts_caption']."\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>".NFP_5.":
</td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_display\" size=\"20\" value=\"".$menu_pref['newforumposts_display']."\" maxlength=\"2\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>".NFP_6.": </td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_characters\" size=\"20\" value=\"".$menu_pref['newforumposts_characters']."\" maxlength=\"4\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>".NFP_7.":
</td>
<td style=\"width:60%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" name=\"newforumposts_postfix\" size=\"30\" value=\"".$menu_pref['newforumposts_postfix']."\" maxlength=\"200\" />
</td>
</tr>

<tr>
<td style=\"width:40%\" class='forumheader3'>".NFP_8.":</td>
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
<input class=\"button\" type=\"submit\" name=\"update_menu\" value=\"".NFP_9."\" />
</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender(NFP_10, $text);
require_once(e_ADMIN."footer.php");
?>