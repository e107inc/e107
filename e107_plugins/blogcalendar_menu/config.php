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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/blogcalendar_menu/config.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:06 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");

$lan_file=e_PLUGIN."blogcalendar_menu/languages/".e_LANGUAGE.".php";
if(file_exists($lan_file)){
        require_once($lan_file);
} else {
        require_once(e_PLUGIN."blogcalendar_menu/languages/English.php");
}
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['update_menu'])){
    while(list($key, $value) = each($_POST)){
        if($value != BLOGCAL_CONF3){
            $pref[$key] = $value;
        }
    }
    save_prefs();
    $ns -> tablerender("", "<div style='text-align:center'><b>".BLOGCAL_CONF5."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>".BLOGCAL_CONF1.": </td>
<td style='width:60%' class='forumheader3'>
<select class='tbox' name='blogcal_mpr'>";

// if the nr of months per row is undefined, default to 3
$months_per_row = $pref['blogcal_mpr']?$pref['blogcal_mpr']:"3";
for($i=1; $i<=12; $i++){
   $text .= "<option value='$i'";
   $text .= $months_per_row==$i?"selected":"";
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
$padding = $pref['blogcal_padding']?$pref['blogcal_padding']:"2";
$text .= $padding;
$text.= "' maxlength='100' />
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
$ns -> tablerender(BLOGCAL_CONF4, $text);

require_once(e_ADMIN."footer.php");
?>