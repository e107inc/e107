<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin//chatbox_conf.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("C")){ header("location:../index.php"); }
require_once("auth.php");

echo "--- > ".$_POST['cb_linkreplace'];

if($_SERVER['QUERY_STRING'] != ""){
	$temp = explode("-", $_SERVER['QUERY_STRING']);
	$action = $temp[0];
	$id = $temp[1];
	$url = $temp[2];

	if($action == "block"){
		$sql -> db_Update("chatbox", "cb_blocked='1' WHERE cb_id='$id' ");
		$message = "<b>Chatbox item blocked.</b><br /><br /><a href=\"$url\">Return</a>";
	}
	if($action == "unblock"){
		$sql -> db_Update("chatbox", "cb_blocked='0' WHERE cb_id='$id' ");
		$message = "<b>Chatbox item unblocked.</b><br /><br /><a href=\"$url\">Return</a>";
	}
	if($action == "delete"){
		$sql -> db_Delete("chatbox", "cb_id='$id' ");
		$message = "<b>Chatbox item deleted.</b><br /><br /><a href=\"$url\">Return</a>";
	}

	if($message != ""){
		echo "<div style=\"text-align:center\">$message</div>";
		require_once("footer.php");
		exit;
	}
}

if($action == "u"){
	$message = "Chatbox settings updated.";
}

if(IsSet($_POST['updatesettings'])){

	$sql -> db_Update("prefs", "pref_value='".$_POST['chatbox_posts']."' WHERE pref_name='chatbox_posts' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['cb_linkc']."' WHERE pref_name='cb_linkc' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['cb_wordwrap']."' WHERE pref_name='cb_wordwrap' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['cb_linkreplace']."' WHERE pref_name='cb_linkreplace' ");

	header("location:chatbox_conf.php?u");
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$chatbox_posts = $pref['chatbox_posts'][1];
$cb_linkreplace = $pref['cb_linkreplace'][1];
$cb_linkc = $pref['cb_linkc'][1];
$cb_wordwrap = $pref['cb_wordwrap'][1];

$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" name=\"cbform\">
<table style=\"width:95%\">
<tr>
<td style=\"width:20%\">Chatbox posts to display?: </td>
<td style=\"width:50%\" colspan=\"2\">
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

<td style=\"width:20%\">Replace links?: </td>
<td style=\"width:50%\" colspan=\"2\">";

if($cb_linkreplace){
	$text .= "<input type=\"checkbox\" name=\"cb_linkreplace\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"cb_linkreplace\" value=\"1\">";
}

$text .= "
</td>
</tr>

<td style=\"width:20%\">Replace string if activated: </td>
<td style=\"width:50%\" colspan=\"2\">
<input class='tbox' type='text' name='cb_linkc' size='80' value='$cb_linkc' maxlength='200' onFocus=\"checkenabled(this.form)\" />
</td>
</tr>

<td style=\"width:20%\">Wordwrap count: </td>
<td style=\"width:50%\" colspan=\"2\">
<input class=\"tbox\" type=\"text\" name=\"cb_wordwrap\" size=\"20\" value=\"$cb_wordwrap\" maxlength=\"200\" />
</td>
</tr>

<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"3\"  style=\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Chatbox Settings\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Chatbox Settings</div>", $text);
require_once("footer.php");

?>

<script language="javascript">
<!--
function disable(){
	frm=document.forms[0];
	frm.cb_linkc.disabled=true;
}
function enable(){
	frm=document.forms[0];
	frm.cb_linkc.disabled=false;
}





//-->
</script>