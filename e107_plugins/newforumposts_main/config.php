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

if(IsSet($_POST['updatesettings'])){
	$pref['nfp_display'] = $_POST['nfp_display'];
	$pref['nfp_caption'] = $_POST['nfp_caption'];
	$pref['nfp_amount'] = $_POST['nfp_amount'];
	$pref['nfp_layer'] = $_POST['nfp_layer'];
	$pref['nfp_layer_height'] = ($_POST['nfp_layer_height'] ? $_POST['nfp_layer_height'] : 200);
	save_prefs();
	$message = "New Forum Posts settings updated.";
}

if($message){
        $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}



$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder'>

<tr>
<td style='width:40%' class='forumheader3'>Activate in which area?</td>
<td style='width:60%' class='forumheader3'>
<select class='tbox' name='nfp_display'>"
.($pref['nfp_display'] == "0" ? "<option value=0 selected>Inactive</option>" : "<option value=0>Inactive</option>")
.($pref['nfp_display'] == "1" ? "<option value=1 selected>Top of page</option>" : "<option value=1>Top of page</option>")
.($pref['nfp_display'] == "2" ? "<option value=2 selected>Bottom of page</option>" : "<option value=2>Bottom of page</option>")
."</select>
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>Caption: </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='nfp_caption' size='20' value='".$pref['nfp_caption']."' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>Number of new posts to display: </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='nfp_amount' size='6' value='".$pref['nfp_amount']."' maxlength='3' />
</td>
</tr>

<td class='forumheader3' style='width:40%'>Display inside scrolling layer?: </td>
<td class='forumheader3' style='width:60%'>".
($pref['nfp_layer'] ? "<input type='checkbox' name='nfp_layer' value='1' checked>" : "<input type='checkbox' name='nfp_layer' value='1'>")."&nbsp;&nbsp;Layer height: <input class='tbox' type='text' name='nfp_layer_height' size='8' value='".$pref['nfp_layer_height']."' maxlength='3' />
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='updatesettings' value='Update New Forum Posts Settings' /></td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("New Forum Posts Configuration", $text);

require_once(e_ADMIN."footer.php");
?>