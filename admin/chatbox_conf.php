<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin//chatbox_conf.php
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
if(!getperms("C")){ header("location:".e_HTTP."index.php"); exit ;}
require_once("auth.php");

if(e_QUERY != ""){
	$temp = explode("-", e_QUERY);
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

	$pref['chatbox_posts'][1] = $_POST['chatbox_posts'];

	$cb_linkc = str_replace("\"", "'", $_POST['cb_linkc']);
	$cb_linkc = stripslashes($cb_linkc);

	$pref['cb_linkc'][1] = $cb_linkc;
	$pref['cb_wordwrap'][1] = $_POST['cb_wordwrap'];
	$pref['cb_linkreplace'][1] = $_POST['cb_linkreplace'];
	
	$tmp = addslashes(serialize($pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref' ");
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
<form method=\"post\" action=\"".e_SELF."\" name=\"cbform\">
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
<input class=\"tbox\" type=\"text\" name=\"cb_linkc\" size=\"80\" value=\"$cb_linkc\" maxlength=\"200\" />
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
<?php
require_once("footer.php");
?>