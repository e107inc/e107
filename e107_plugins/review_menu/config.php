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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/review_menu/config.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-18 16:11:57 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/


require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

$lan_file = e_PLUGIN."review_menu/languages/".e_LANGUAGE.".php";
require_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."review_menu/languages/English.php");


if(isset($_POST['update_menu'])){
        while(list($key, $value) = each($_POST)){
                if($key=="reviews_parents"){$value="1"; $found=1;}
                if($key=="reviews_submitlink"){$value="1"; $found1=1;}
                if($value != LAN_RVW_1){
                        $menu_pref[$key] = $value;
                }
        }
        if(!$found){unset($menu_pref['reviews_parents']);}
        if(!$found1){unset($menu_pref['reviews_submitlink']);}
        $tmp = addslashes(serialize($menu_pref));
        $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
        $ns -> tablerender("", "<div style='text-align:center'><b>".LAN_RVW_2."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>".LAN_RVW_3.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='reviews_caption' size='20' value='".$menu_pref['reviews_caption']."' maxlength='100' />
</td>
</tr>


<tr>
<td style='width:40%' class='forumheader3'>".LAN_RVW_4.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='reviews_display' size='6' value='".$menu_pref['reviews_display']."' maxlength='3' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_RVW_5."</td>
<td style='width:60%' class='forumheader3'>
<input type='checkbox' name='reviews_parents' value='".$menu_pref['reviews_parents']."' ".($menu_pref['reviews_parents'] ? "checked" : "")." />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_RVW_6.":
</td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='reviews_mainlink' size='30' value='".$menu_pref['reviews_mainlink']."' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_RVW_7."</td>
<td style='width:60%' class='forumheader3'>
<input type='checkbox' name='reviews_submitlink' value='".$menu_pref['reviews_submitlink']."' ".($menu_pref['reviews_submitlink'] ? "checked" : "")." />
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='".LAN_RVW_1."' /></td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender(LAN_RVW_8, $text);

require_once(e_ADMIN."footer.php");
?>

?>