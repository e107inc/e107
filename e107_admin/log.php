<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/log_conf.php
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
if(!getperms("S")){ header("location:".e_BASE."index.php"); exit;}

if(IsSet($_POST['updatesettings'])){

	$pref['log_activate'] = $_POST['log_activate'];
	$pref['log_refertype'] = $_POST['refertype'];
	$pref['log_lvcount'] = $_POST['lvcount'];
	save_prefs();
	header("location:".e_SELF."?u");
	exit;
}

if(e_QUERY == "u"){
	$message = LOGLAN_1;
}

require_once("auth.php");

if(IsSet($_POST['wipe'])){
	
	if(IsSet($_POST['log_wipe_info'])){
		$sql -> db_Delete("stat_info", "");
		$sql -> db_Delete("stat_last", "");
	}
	if(IsSet($_POST['log_wipe_counter'])){
		$sql -> db_Delete("stat_counter", "");
	}

	$message = LOGLAN_2;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$log_activate = $pref['log_activate'];
$lvcount = $pref['log_lvcount'];

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>

<tr>
<td style='width:30%' class='forumheader3'>".LOGLAN_3.": </td>
<td style='width:70%' class='forumheader3'>";
if($log_activate == 1){
	$text .= "<input type='checkbox' name='log_activate' value='1'  checked='checked' />";
}else{
	$text .= "<input type='checkbox' name='log_activate' value='1' />";
}

$text .= "</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LOGLAN_4.": </td>
<td style='width:70%' class='forumheader3'>";

if($pref['log_refertype'] == 0){
	$text .= LOGLAN_5.": <input type='radio' name='refertype' value='0' checked='checked' />
	".LOGLAN_6.": <input type='radio' name='refertype' value='1' />";
}else{
	$text .= LOGLAN_5.": <input type='radio' name='refertype' value='0' />
	".LOGLAN_6.": <input type='radio' name='refertype' value='1' checked='checked' />";
}

$text .= "</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LOGLAN_7.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='lvcount' size='10' value='$lvcount' maxlength='5' />
</td>
</tr>


<tr>
<td style='width:30%' class='forumheader3'>".LOGLAN_8.": </td>
<td style='width:70%' class='forumheader3'>
<input type='checkbox' name='log_wipe_info' value='1' /> ".LOGLAN_9."<br />
<input type='checkbox' name='log_wipe_counter' value='1' /> ".LOGLAN_10."<br />
<input class='button' type='submit' name='wipe' value='".LOGLAN_11."' />
</td>
</tr>

<tr>
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='".LOGLAN_12."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".LOGLAN_13."</div>", $text);
require_once("footer.php");

?>