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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/banner_menu/config.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:06 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

@include_once(e_PLUGIN."banner_menu/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."banner_menu/languages/English.php");

if(IsSet($_POST['update_menu'])){
        foreach($_POST as $k => $v){
                if(preg_match("#^banner_#",$k)){
                        $menu_pref[$k] = $v;
                }
        }
        $tmp = addslashes(serialize($menu_pref));
        $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
        $ns -> tablerender("", "<div style='text-align:center'><b>".BANNER_MENU_L2."</b></div>");
}

if(!$menu_pref['banner_caption']){
        $menu_pref['banner2_caption'] = BANNER_MENU_L1;
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>".BANNER_MENU_L3.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='banner_caption' size='20' value='".$menu_pref['banner_caption']."' maxlength='100' />
</td>
</tr>


<tr>
<td style='width:40%' class='forumheader3'>".BANNER_MENU_L4.": </td>
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
$ns -> tablerender(BANNER_MENU_L5, $text);

require_once(e_ADMIN."footer.php");
?>