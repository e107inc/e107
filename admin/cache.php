<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/cache.php
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

if(IsSet($_POST['clearcache'])){
	$sql -> db_Delete("cache");
	header("location:".e_SELF."?c");
}

if(IsSet($_POST['updatesettings'])){
	$pref['cache_activate'][1] = $_POST['cache_activate'];
	$pref['cache_timeout'][1] = ($_POST['cache_timeout'] ? $_POST['cache_timeout'] : 600);
	save_prefs();
	header("location:".e_SELF."?u");
}

require_once("auth.php");

if(e_QUERY == "u"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Cache settings updated.</b></div>");
}
if(e_QUERY == "c"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Cache cleared.</b></div>");
}

$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."\">
<table style=\"width:95%\" class=\"fborder\">
<tr>
<td style=\"width:30%\" class=\"forumheader3\">Activate cache system?: </td>
<td style=\"width:70%\" class=\"forumheader3\">";

$text .= ($pref['cache_activate'][1] ? "<input type=\"checkbox\" name=\"cache_activate\" value=\"1\"  checked>" : "<input type=\"checkbox\" name=\"cache_activate\" value=\"1\">");

$text .= "</td>
</tr>

<td style=\"width:30%\" class=\"forumheader3\">Cache timeout: </td>
<td style=\"width:70%\" class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" name=\"cache_timeout\" size=\"10\" value=\"".$pref['cache_timeout'][1]."\" maxlength=\"10\" />
<span class=\"smalltext\"> ( in seconds, ie 300 = 5 minutes )</span>
</td>
</tr>

<td style=\"width:30%\" class=\"forumheader3\">Manually clear cache: </td>
<td style=\"width:70%\" class=\"forumheader3\">
<input class=\"button\" type=\"submit\" name=\"clearcache\" value=\"Click here to clear all cached pages\" />
</td>
</tr>


<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\"  class=\"forumheader\">
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Cache Settings\" />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">Cache Settings</div>", $text);
require_once("footer.php");

?>