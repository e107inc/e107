<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/comfort.php theme file
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [theme]	#############

$themename = "comfort";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "27/04/2003";
$themeinfo = "compatible with e107 v5+";

define("THEME_DISCLAIMER", "<br />comfort theme by jalist, designed By <a class=\"forumlink\" href=\"http://www28.brinkster.com/nix1\">Point Click Kill Industries</a> for <a class=\"forumlink\" href=\"http://www.oswd.org/\">OSWD.org</a>");

// [layout]	#############
$layout = "_default";
$HEADER = 
"<a name=\"top\"></a>
<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"1\" cellpadding=\"3\" class=\"mainwindow\"><tr><td colspan=\"3\" style=\"text-align:left\">
<span class=\"captiontextorange\"> &gt; </span>
<span class=\"captiontext\">
{SITENAME}
</span>
<span class=\"captiontextorange\"> :.</span>
</td>
</tr>
<tr bgcolor=\"#354463\">
<td colspan=\"3\" style=\"text-align:left\" class=\"normalorange\"> <b>".
strftime("%A, %d %B, %Y .:. %H:%M:%S %Z", time())."
</b>
</td></tr>
<tr>
<td bgcolor=\"#253047\" valign=\"top\" width=\"3\">&nbsp;</td>
<td valign='top' bgcolor='#CCCCCC'> 
<table width='100%' border='0' cellspacing='2' cellpadding='3'>
<tr>
</td><td style='width:30%; vertical-align:top'>
(SITELINKS=menu}
{SETSTYLE=menu}
{MENU=1}
</td>
<td style='width:70%; vertical-align: top;'>
";

$sql -> db_Select("links", "*", "link_category='1' ORDER BY link_order ASC");
while($row = $sql -> db_Fetch()){
	extract($row);
	$links .= " .:. <a class='forumlink' href='".e_BASE."$link_url'>$link_name</a>";
}

$FOOTER = 
"
</tr>
</table>
<td bgcolor='#253047' valign='top' width='3'>&nbsp;</td>
</tr>
<tr bgcolor='#354463'>
<td colspan='3' style='text-align:center' class='mediumtext'><a class='forumlink' href='#top'>^TOP</a> 
$links
</td>
</tr>
<tr bgcolor='#101842'>
<td colspan='3' style='text-align:right' class='smallwhitetext'>
{SITEDISCLAIMER}
</td>
</tr>
</table>
</div>
";

//	[newsstyle]


$NEWSSTYLE = "
<table style='width:100%' cellpadding='0' cellspacing='0'>
<tr class='caption'>
<td style='width:50%'>
<b>
{NEWSTITLE}
</b>
</td>
<td style='width:50%; text-align:right' class='smallblacktext'>Posted {NEWSDATE}</td>
</tr>
<tr class='bodytable'>
<td colspan='2' style='text-align:left'>
{NEWSBODY}
{EXTENDED}
<div style='text-align:right'>
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
</div>
{NEWSCOMMENTS}
</td>
</tr>
</table>
<br />";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");

define("PRE_SOURCESTRING", "<br />");
define("SOURCESTRING", "Source: ");
define("POST_SOURCESTRING", "<br />");

define("PRE_URLSTRING", "<br />");
define("URLSTRING", "Link: ");
define("POST_URLSTRING", "<br />");




// [linkstyle]


define("PRELINK", "\n\n<table cellpadding='2' cellspacing='1' bgcolor='#000000' style='width:100%'><tr><td class='caption2'>:: Navigation : </td></tr>\n");
define("POSTLINK", "\n\n</table></div></div><br />\n\n");
define("LINKSTART", "\n<tr bgcolor='#758393'> <td onMouseOver=\"this.style.backgroundColor='#9DA8B3';\" onMouseOut=\"this.style.backgroundColor='#758393'\"> <span class='linktext'>\n");
define("LINKEND", "</span></td>");
define("LINKDISPLAY", 1);			// 1 - along top, 2 - in left or right column
define("LINKALIGN", "left");
define("LINKCLASS", "forumlink");



//	[tablestyle]	#############

function tablestyle($caption, $text, $mode=""){

	if($mode = "menu"){
		echo "<div class='border'><div class='caption'>$caption</div></div><div class='border2'><div class='bodytable2'>$text</div></div><br />";
	}else{
		echo "<div class='caption'>$caption</div><div class='bodytable'>$text</div><br />";
	}
}
?>