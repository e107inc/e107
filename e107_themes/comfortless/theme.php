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

$themename = "'comfortless'";
$themeversion = "1.0";
$themeauthor = "tazilon";
$themedate = "01/05/2003";
$themeinfo = "compatible with e107 v5+";

define("THEME_DISCLAIMER", "<br />'comfortless' theme ©tazilon 2003");

// [layout]	#############
$layout = "_default";
$clock_flat = TRUE;
$HEADER = 
"<a name='top'></a>
<div style='text-align:center'>
<table style='width:850px' cellspacing='1' cellpadding='3' class='mainwindow'><tr><td colspan='3' style='text-align:left' bgcolor='#efefef'>
<span class='captiontextorange'> </span>
<span class='captiontext'>
{SITENAME}
</span>
<span class='captiontextorange'>  </span>
</td>
</tr> 
<tr bgcolor='#ffffff'> 
<td colspan='3' style='text-align:left' class='mediumtext'> <b>".
strftime("%A, %B %d, %Y - %H:%M:%S ", time()+7200)."EST</b>
</td></tr> 
<tr>
<td bgcolor='#ffffff' valign='top' width='3'>&nbsp;</td>
<td valign='top' bgcolor='#ffffff'> 
<table width='100%' border='0' cellspacing='2' cellpadding='3'>
<tr>
<td style='width:75%; vertical-align: top;'>
";

$sql -> db_Select("links", "*", "link_category='1' ORDER BY link_order ASC");
while($row = $sql -> db_Fetch()){
	extract($row);
	$links .= " .:. <a class='forumlink' href='".e_BASE."$link_url'>$link_name</a>";
}

$FOOTER = 
"</td><td style='width:25%; vertical-align:top'>
(SITELINKS=menu}
{SETSTYLE=menu}
{MENU=1}
</td>
</tr>
</table>
<td bgcolor='#ffffff' valign='top' width='3'>&nbsp;</td>
</tr>
<tr bgcolor='#DBE1E8'>
<td colspan='3' style='text-align:center' class='mediumtext'><a class='forumlink' href='#top'>^TOP</a> 
$links
</td>
</tr>
<tr bgcolor='#efefef'>
<td colspan='3' style='text-align:center' class='smalltext'>
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

// [linkstyle]


define("PRELINK", "<table cellpadding='2' cellspacing='1' bgcolor='#000000' style='width:100%'><tr><td class='caption2'> Navigation  </td></tr>");
define("POSTLINK", "</table></div></div><br />");
define("LINKSTART", "<tr bgcolor='#DBE1E8'> <td onMouseOver=\"this.style.backgroundColor='#f7f7f7';\" onMouseOut=\"this.style.backgroundColor='#DBE1E8'\"> <span class='captiontextorange'> ");
define("LINKEND", "</span></td>");
define("LINKDISPLAY", 1);			// 1 - along top, 2 - in left or right column
define("LINKALIGN", "left");
define("LINKCLASS", "forumlink");



//	[tablestyle]

function tablestyle($caption, $text, $mode=""){

	if($mode = "menu"){
		echo "<div class='border'><div class='caption'>$caption</div></div><div class='border2'><div class='bodytable2'>$text</div></div><br />";
	}else{
		echo "<div class='caption'>$caption</div><div class='bodytable'>$text</div><br />";
	}
}
?>