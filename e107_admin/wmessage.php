<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/wmessage.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("M")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."ren_help.php");
$aj = new textparse;
if(IsSet($_POST['wmsubmit'])){

	$guestmessage = $aj -> formtpa($_POST['guestmessage'], "admin");
	$membermessage = $aj -> formtpa($_POST['membermessage'], "admin");
	$adminmessage = $aj -> formtpa($_POST['adminmessage'], "admin");
	$sql -> db_Update("wmessage", "wm_text ='$guestmessage', wm_active='".$_POST['wm_active1']."' WHERE wm_id='1' ");
	$sql -> db_Update("wmessage", "wm_text ='$membermessage', wm_active='".$_POST['wm_active2']."' WHERE wm_id='2' ");
	$sql -> db_Update("wmessage", "wm_text ='$adminmessage', wm_active='".$_POST['wm_active3']."' WHERE wm_id='3' ");
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$sql -> db_Select("wmessage");
list($id, $guestmessage, $wm_active1) = $sql-> db_Fetch();
list($id, $membermessage, $wm_active2) = $sql-> db_Fetch();	
list($id, $adminmessage, $wm_active3) = $sql-> db_Fetch();


$guestmessage = $aj -> formtparev($aj -> editparse($guestmessage));
$membermessage = $aj -> formtparev($aj -> editparse($membermessage));
$adminmessage = $aj -> formtparev($aj -> editparse($adminmessage));

$text = "
<div style='text-align:center'>
<form method='post' action='".$_SERVER['PHP_SELF']."'  id='getElementById('wmform')'>
<table style='width:85%' class='fborder'>
<tr>";

$text .= "

<td style='width:20%' class='forumheader3'>".WMGLAN_1.": <br />
".WMGLAN_6.":";
if($wm_active1){
	$text .= "<input type='checkbox' name='wm_active1' value='1'  checked='checked' />";
}else{
	$text .= "<input type='checkbox' name='wm_active1' value='1' />";
}
$text .= "</td>
<td style='width:60%' class='forumheader3'>
<textarea class='tbox' name='guestmessage' cols='70' rows='10'>$guestmessage</textarea>
<br />
<input class='helpbox' type='text' name='helpguest' size='100' />
<br />
".ren_help(1,"addtext1","help1")."
</td>

</tr>

<tr>
<td style='width:20%' class='forumheader3'>".WMGLAN_2.": <br />
".WMGLAN_6.":";
if($wm_active2){
	$text .= "<input type='checkbox' name='wm_active2' value='1'  checked='checked' />";
}else{
	$text .= "<input type='checkbox' name='wm_active2' value='1' />";
}
$text .= "</td>
<td style='width:60%' class='forumheader3'>
<textarea class='tbox' name='membermessage' cols='70' rows='10'>$membermessage</textarea>
<br />
<input class='helpbox' type='text' name='helpmember' size='100' />
<br />
".ren_help(1,"addtext2","help2")."
</td>


<tr>
<td style='width:20%' class='forumheader3'>".WMGLAN_3.": <br />
".WMGLAN_6.": ";

if($wm_active3){
	$text .= "<input type='checkbox' name='wm_active3' value='1'  checked='checked' />";
}else{
	$text .= "<input type='checkbox' name='wm_active3' value='1' />";
}

$text .= "</td>
<td style='width:60%' class='forumheader3'>
<textarea class='tbox' name='adminmessage' cols='70' rows='10'>$adminmessage</textarea>
<br />
<input class='helpbox' type='text' name='helpadmin' size='100' />
<br />
".ren_help(1,"addtext3","help3")."
</td>
</tr>

<tr style='vertical-align:top'> 
<td class='forumheader3'>&nbsp;</td>
<td style='width:60%' class='forumheader3'>
<input class='button' type='submit' name='wmsubmit' value='".WMGLAN_4."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(WMGLAN_5, $text);

?>
<script type="text/javascript">
function addtext1(sc){
	document.getElementById('wmform').guestmessage.value += sc;
}
function addtext2(sc){
	document.getElementById('wmform').membermessage.value += sc;
}
function addtext3(sc){
	document.getElementById('wmform').adminmessage.value += sc;
}
function fclear(){
	document.newspostform.message.value = "";
}
function help1(help){
	document.getElementById('wmform').helpguest.value = help;
}
function help2(help){
	document.getElementById('wmform').helpmember.value = help;
}
function help3(help){
	document.getElementById('wmform').helpadmin.value = help;
}
</script>
<?php

require_once("footer.php");
?>	