<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin//meta.php
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

if(IsSet($_POST['metasubmit'])){
	$sql -> db_Update("prefs", "pref_value='".$_POST['meta']."' WHERE pref_name='meta_tag' ");
	header("location:meta.php?e");
}

if(!getperms("C")){ header("location:../index.php"); }
require_once("auth.php");

if($_SERVER['QUERY_STRING'] != ""){
	$ns -> tablerender("Updated", "<div style=\"text-align:center\">Meta tags updated in database.</div>");
}

$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:20%\">Enter meta-tags: </td>
<td style=\"width:50%\">
<textarea class=\"tbox\" name=\"meta\" cols=\"70\" rows=\"10\">".$pref['meta_tag'][1]."</textarea>
</td>
</tr>

<td style=\"width:20%\">&nbsp;</td>
<td style=\"width:50%\">
<input class=\"button\" type=\"submit\" name=\"metasubmit\" value=\"Enter new meta tag settings\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Meta Tags", $text);
require_once("footer.php");
?>


