<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107.v4 theme file 
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [theme]

$themename = "nagrunium";
$themeversion = "4.0";
$themeauthor = "jalist";
$themedate = "23/06/2003";
$themeinfo = "";

define("THEME_DISCLAIMER", "<br /><i>'nagrunium' theme by jalist</i>");

// [layout]
$layout = "_default";

$HEADER = 
"<div style='text-align:center'>
<table style='width:100%' cellspacing='3'><tr><td colspan='3' style='text-align:left'>
<span class='captiontext'>
{SITENAME}
</span>
<br />
{SITETAG}
<div style='text-align:right'>
{SITELINKS=flat}
</div>
</td></tr><tr> <td style='width:20%; vertical-align: top;'>
{SETSTYLE=leftmenu}
{MENU=1}
</td><td style='width:60%; vertical-align: top;'>";

$FOOTER = 
"</td><td style='width:20%; vertical-align:top'>
{MENU=2}
</td></tr>
<tr>
<td colspan='3' style='text-align:center'>
{SITEDISCLAIMER}
</td>
</tr>
</table>
</div>
";

//	[newsstyle]

$NEWSSTYLE = "
<div class='border'>
<div class='caption'>
{NEWSTITLE}
</div>
<div class='bodytable' style='text-align:left'>
{NEWSICON}
{NEWSBODY}
{EXTENDED}
<div style='text-align:right'>
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
<br />
Posted by 
{NEWSAUTHOR}
on
{NEWSDATE}
&nbsp;::&nbsp;
{NEWSCOMMENTS}
</div>
</div>
</div>
<br />";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");


// [linkstyle]

define(PRELINK, "<div class='mediumtext'>.:. ");
define(POSTLINK, "</div>");
define(LINKSTART, "");
define(LINKEND, " .:. ");
define(LINKDISPLAY, 1);
define(LINKALIGN, "right");

//	[tablestyle]

function tablestyle($caption, $text){
	echo"<table style='width:100%'><tr><td style='whitespace:nowrap'><div class='border'><div class='caption'>$caption</div><div class='bodytable'>$text</div></div></td></tr></table><br />";
}
?>