<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/plugin_conf/chatbox_conf.php						|
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
if(ADMINPERMS != 0 && ADMINPERMS != 1){
	header("location:../index.php");
}

if(IsSet($_POST['updatesettings'])){
	$sql -> db_Update("prefs", "pref_value='".$_POST['maintainance_flag']."' WHERE pref_name='maintainance_flag' ");
	header("location:ugflag.php?u");
}

require_once("auth.php");

if($_SERVER['QUERY_STRING'] == "u"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Maintainance setting updated.</b></div>");
}

$maintainance_flag = $pref['maintainance_flag'][1];

$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Activate maintenance flag: </td>
<td style=\"width:70%\">";


if($maintainance_flag == 1){
	$text .= "<input type=\"checkbox\" name=\"maintainance_flag\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"maintainance_flag\" value=\"1\">";
}

$text .= "</td>
</tr>

<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Maintanance Setting\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Maintanance Setting</div>", $text);
require_once("footer.php");

?>