<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/banlist.php														|
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
if(ADMINPERMS != 0 && ADMINPERMS != 1 && ADMINPERMS != 2){
	header("location:../index.php");
}
require_once("auth.php");

if(IsSet($_SERVER['QUERY_STRING'])){ $ban_ip = $_SERVER['QUERY_STRING']; }

if(IsSet($_POST['add_ban'])){
	$sql -> db_Insert("banlist", "'".$_POST['ban_ip']."', '".ADMINID."', '".$_POST['ban_reason']."' ");
	unset($ban_ip);
}

if(IsSet($_POST['delete'])){
	$sql -> db_Delete("banlist", "banlist_ip='".$_POST['existing']."' ");
	$message = "Ban removed.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$ban_total = $sql -> db_Select("banlist");

if($ban_total == "0"){
	$text = "<div style=\"text-align:center\"><b>No bans.</b></div>
	<br />";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
	
	Existing Bans: 
	<select name=\"existing\" class=\"tbox\">";
	while(list($ban_ip_) = $sql-> db_Fetch()){
		$text .= "<option>".$ban_ip_."</option>";
	}
	$text .= "</select> 
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Remove ban\" />
	</form>
	</div>
	<br />";
}
	

$text .= "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Ban IP: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"ban_ip\" size=\"40\" value=\"$ban_ip\" maxlength=\"200\" />
</td>
</tr>

<tr> 
<td style=\"width:20%\">Reason: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"ban_reason\" cols=\"50\" rows=\"4\">$ban_reason</textarea>
</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">

<input class=\"button\" type=\"submit\" name=\"add_ban\" value=\"Add Banned IP Address\" />

</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Add/Delete Site Bans</div>", $text);

require_once("footer.php");
?>	