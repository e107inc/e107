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

if(IsSet($_SERVER['QUERY_STRING'])){
	$temp = explode("-", $_SERVER['QUERY_STRING']);
	$action = $temp[0];
	$id = $temp[1];
	$url = $temp[2];
	if($action == "block"){
		$sql -> db_Update("chatbox", "cb_blocked='1' WHERE cb_id='$id' ");
		header("location:".$url);
	}
	if($action == "unblock"){
		$sql -> db_Update("chatbox", "cb_blocked='0' WHERE cb_id='$id' ");
		header("location:".$url);
	}
	if($action == "delete"){
		$sql -> db_Delete("chatbox", "cb_id='$id' ");
		header("location:".$url);
	}
}

if($action == "u"){
	$message = "Chatbox settings updated.";
}

if(IsSet($_POST['updatesettings'])){
	$sql -> db_Update("prefs", "pref_value='".$_POST['chatbox_posts']."' WHERE pref_name='chatbox_posts' ");
	header("location:chatbox_conf.php?u");
}

require_once("auth.php");

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$chatbox_posts = $pref['chatbox_posts'][1];

$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Chatbox posts to display?: </td>
<td style=\"width:70%\">
<select name=\"chatbox_posts\" class=\"tbox\">";
if($chatbox_posts == 5){
	$text .= "<option selected>5</option>\n";
}else{
	$text .= "<option>5</option>\n";
}
if($chatbox_posts == 10){
	$text .= "<option selected>10</option>\n";
}else{
	$text .= "<option>10</option>\n";
}
if($chatbox_posts == 15){
	$text .= "<option selected>15</option>\n";
}else{
	$text .= "<option>15</option>\n";
}
if($chatbox_posts == 20){
	$text .= "<option selected>20</option>\n";
}else{
	$text .= "<option>20</option>\n";
}
if($chatbox_posts == 25){
	$text .= "<option selected>25</option>\n";
}else{
	$text .= "<option>25</option>\n";
}

$text .= "</select>
</td>
</tr>

<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Chatbox Settings\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Chatbox Settings</div>", $text);
require_once("footer.php");

?>