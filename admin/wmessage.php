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

if(IsSet($_POST['submit'])){
	if($_POST['message'] != "" || $_POST['wm_active'] == 0){
		$aj = new textparse;
		$message = $aj -> tp($_POST['message'], $mode = "on");

		if($sql -> db_Select("wmessage")){
			$sql -> db_Update("wmessage", "wm_text ='$message', wm_active='".$_POST['wm_active']."' ");
		}else{
			$sql -> db_Insert("wmessage", " '$message', '".$_POST['wm_active']."' ");
		}
		$message = "Welcome message set";
		if($_POST['wm_active']){
			$message .= " and made active.";
		}else{
			$message .= ".";
		}
	}else{
		$message = "Field left blank";
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$sql -> db_Select("wmessage");
list($wm_text, $wm_active) = $sql-> db_Fetch();

$text = "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n
<table style=\"width:95%\">";
$text .= "<tr> 
<td style=\"width:20%\">Message: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"message\" cols=\"70\" rows=\"10\">$wm_text</textarea>
</td>
</tr>

<tr>
<td style=\"width:20%\">Activate?: </td>
<td style=\"width:80%\">";
if($wm_active == 1){
	$text .= "<input type=\"checkbox\" name=\"wm_active\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"wm_active\" value=\"1\">";
}

$text .= "</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Set Welcome Message", $text);




require_once("footer.php");
?>	