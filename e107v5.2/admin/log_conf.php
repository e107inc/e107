<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/login_conf.php													|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("P")){ header("location:../index.php"); }

if(IsSet($_POST['updatesettings'])){
	$sql -> db_Update("prefs", "pref_value='".$_POST['log_activate']."' WHERE pref_name='log_activate' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['refertype']."' WHERE pref_name='log_refertype' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['lvcount']."' WHERE pref_name='log_lvcount' ");
	$pref['log_refertype'][1] = $_POST['refertype'];
	header("location:log_conf.php?u");
}



if($_SERVER['QUERY_STRING'] == "u"){
	$message = "Logger settings updated.";
}

require_once("auth.php");

if(IsSet($_POST['wipe']) && IsSet($_POST['log_wipe'])){
	$sql -> db_Delete("stat_info", "");
	$message = "Stats_info table emptied.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$log_activate = $pref['log_activate'][1];
$lvcount = $pref['log_lvcount'][1];

$text = "
<form method=\"post\" action=\"$PHP_SELF\">
<table style=\"width:95%\">

<tr>
<td style=\"width:30%\">Activate Logging/Counter?: </td>
<td style=\"width:70%\">";
if($log_activate == 1){
	$text .= "<input type=\"checkbox\" name=\"log_activate\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"log_activate\" value=\"1\">";
}

$text .= "</td>
</tr>

<tr>
<td style=\"width:30%\">Refer log type: </td>
<td style=\"width:70%\">";

if($pref['log_refertype'][1] == 0){
	$text .= "Domain only: <input type=\"radio\" name=\"refertype\" value=\"0\" checked>
	Complete URL: <input type=\"radio\" name=\"refertype\" value=\"1\">";
}else{
	$text .= "Domain only: <input type=\"radio\" name=\"refertype\" value=\"0\">
	Complete URL: <input type=\"radio\" name=\"refertype\" value=\"1\" checked>";
}

$text .= "</td>
</tr>

<tr>
<td style=\"width:30%\">Count how many last visitors?: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"lvcount\" size=\"10\" value=\"$lvcount\" maxlength=\"5\" />
</td>
</tr>


<tr>
<td style=\"width:30%\">Clear stats tables: </td>
<td style=\"width:70%\">
<input type=\"checkbox\" name=\"log_wipe\" value=\"1\">
<input class=\"button\" type=\"submit\" name=\"wipe\" value=\"Clear!\" /> This will erase all your visitor site stats, not your counter stats
</td>
</tr>

<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Logger Settings\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Logger/Counter Settings</div>", $text);
require_once("footer.php");

?>