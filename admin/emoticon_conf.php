<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/emoticon_conf.php
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
if(!getperms("P")){ header("location:".e_HTTP."index.php"); }

if(IsSet($_POST['updatesettings'])){
	$pref['smiley_activate'][1] = $_POST['smiley_activate'];
	$sql -> db_Update("core", "e107_value='".serialize($pref)."' WHERE e107_name='pref' ");
	header("location:emoticon_conf.php?u");
}

require_once("auth.php");

if(e_QUERY == "u"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Emoticon settings updated.</b></div>");
}

$smiley_activate = $pref['smiley_activate'][1];

$text = "
<form method=\"post\" action=\"".e_SELF."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Activate emoticons?: </td>
<td style=\"width:70%\">";


if($smiley_activate == 1){
	$text .= "<input type=\"checkbox\" name=\"smiley_activate\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"smiley_activate\" value=\"1\">";
}

$text .= "</td>
</tr>

<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Emoticon Settings\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Emoticon Settings</div>", $text);
require_once("footer.php");

?>