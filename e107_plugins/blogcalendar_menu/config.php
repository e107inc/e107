<?php
/******************************************************************\
 *                                                                *
 *  :: e107 blogcal addon ::                                      *
 *                                                                *
 *  file:     config.php                                          *
 *  author:   Thomas Bouve                                        *
 *  email:    crahan@gmx.net                                      *
 *  Date:     2004-02-08                                          *
 *                                                                *
\******************************************************************/
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['update_menu'])){
    while(list($key, $value) = each($_POST)){
        if($value != "Update Menu Settings"){ 
            $pref[$key] = $value;
        }
    }
    save_prefs();
    $ns -> tablerender("", "<div style='text-align:center'><b>BlogCal menu configuration saved</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>Months/row: </td>
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
<td style='width:40%' class='forumheader3'>Cellpadding: </td>
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
    <input class='button' type='submit' name='update_menu' value='Update Menu Settings' />
</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("BlogCal Menu Configuration", $text);

require_once(e_ADMIN."footer.php");
?>