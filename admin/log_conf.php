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
if(!getperms("P")){ header("location:".e_HTTP."index.php"); exit;}

if(IsSet($_POST['updatesettings'])){

	$pref['log_activate'][1] = $_POST['log_activate'];
	$pref['log_refertype'][1] = $_POST['refertype'];
	$pref['log_lvcount'][1] = $_POST['lvcount'];
	save_prefs();
	header("location:log_conf.php?u");
	exit;
}

if(e_QUERY == "u"){
	$message = "Logger settings updated.";
}

require_once("auth.php");

if(IsSet($_POST['wipe'])){
	
	if(IsSet($_POST['log_wipe_info'])){
		$sql -> db_Delete("stat_info", "");
	}
	if(IsSet($_POST['log_wipe_counter'])){
		$sql -> db_Delete("stat_counter", "");
	}

	$message = "Stats table(s) emptied.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$log_activate = $pref['log_activate'][1];
$lvcount = $pref['log_lvcount'][1];

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>

<tr>
<td style='width:30%' class='forumheader3'>Activate Logging/Counter?: </td>
<td style='width:70%' class='forumheader3'>";
if($log_activate == 1){
	$text .= "<input type='checkbox' name='log_activate' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='log_activate' value='1'>";
}

$text .= "</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Refer log type: </td>
<td style='width:70%' class='forumheader3'>";

if($pref['log_refertype'][1] == 0){
	$text .= "Domain only: <input type='radio' name='refertype' value='0' checked>
	Complete URL: <input type='radio' name='refertype' value='1'>";
}else{
	$text .= "Domain only: <input type='radio' name='refertype' value='0'>
	Complete URL: <input type='radio' name='refertype' value='1' checked>";
}

$text .= "</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Count how many last visitors?: </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='lvcount' size='10' value='$lvcount' maxlength='5' />
</td>
</tr>


<tr>
<td style='width:30%' class='forumheader3'>Clear stats tables: </td>
<td style='width:70%' class='forumheader3'>
<input type='checkbox' name='log_wipe_info' value='1'> Tick to clear information stats<br />
<input type='checkbox' name='log_wipe_counter' value='1'> Tick to clear counter stats<br />
<input class='button' type='submit' name='wipe' value='Clear!' />
</td>
</tr>

<tr>
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='Update Logger Settings' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>Logger/Counter Settings</div>", $text);
require_once("footer.php");

?>