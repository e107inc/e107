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

$themename = "xog";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "18/01/2004";
$themeinfo = "To alter the height of the layer on the news page, edit line 70 of theme.php (height: 500px)";

define("THEME_DISCLAIMER", "<br /><i>'xog' theme by jalist</i>");

// [layout]
$layout = "_default";

$HEADER = 
"<div style='text-align:left'>
<table style='width:850px' cellspacing='0' cellpadding='0'>
<tr>
<td class='box2' style='width:150px; vertical-align: middle; text-align:center'>
<div class='spacer'><img src='".e_IMAGE."button.png' alt='' /></div>
</td>
<td class='box2' style='width:700px; vertical-align: middle; text-align:center;'>
{BANNER}
</td>
</tr>
</table>
<table style='width:850px' cellspacing='0' cellpadding='0'>
<tr>
<td class='box' style='width:850px;'>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='20%'>
{CUSTOM=search}
</td>
<td style='80%'>
{MENU=3}
Menu area 3
</td>
</tr>
</table>
</td>
</tr>
</table>
<table style='width:850px' cellspacing='0' cellpadding='0'>
<tr>
<td class='box2' style='width:850px;'>
{SITELINKS=flat}
</td>
</tr>
</table>
<table style='width:850px' cellspacing='0' cellpadding='0'>
<td style='width:200px; vertical-align: top;'>
{SETSTYLE=leftmenu}
{MENU=1}
</td>
<td style='width:650px; vertical-align: top;'>".(e_PAGE == "news.php" ? "<div style='border: 0; padding : 4px; width : auto; height: 500px; overflow: auto;'>" : "");

$FOOTER = (e_PAGE == "news.php" ? "</div>" : "")."
</td></tr>
<tr>
<td class='box2' colspan='3' style='text-align:center'>
<div class='linkbox'>
{SITEDISCLAIMER}
</div>
</td>
</tr>
</table>
</div>
";

//	[newsstyle]

$NEWSSTYLE = "
<div class='captiontext'>
{NEWSICON}
{NEWSTITLE}
</div>
<div class='bodytext' style='text-align:left'>
{NEWSBODY}
{EXTENDED}
<br />
<br />
<div class='mediumtext' style='text-align:right'>
<b>
{NEWSAUTHOR}
</b>
<br />
{NEWSDATE}
<br />
{NEWSCOMMENTS}
</span>
<br />
{EMAILICON}
{PRINTICON}
</div>
<br />
<img style='margin-top: 2px; margin-bottom: 2px;' width='610' height='1' src='".THEME."images/hr.png'><br />";

define("ICONSTYLE", "border:0; vertical-align:middle");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");


// [linkstyle]

define(PRELINK, "<div class='linkbox'>| ");
define(POSTLINK, "</div>");
define(LINKSTART, "");
//define(LINKEND, "<br /><img style='margin-top: 2px; margin-bottom: 2px;' width='190' height='1' src='".THEME."images/hr.png'><br />");
define(LINKEND, " | ");
define(LINKDISPLAY, 1);
define(LINKALIGN, "left");

//	[tablestyle]

function tablestyle($caption, $text){
	echo"<table style='width:100%'><tr><td style='whitespace:nowrap'><div class='border'><div class='caption'>$caption</div></div>
	<div class='bodytable'>$text</div></td></tr></table><br />";
}
?>