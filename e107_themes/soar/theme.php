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

$themename = "soar";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "18/01/2004";
$themeinfo = "To alter the height of the layer on the news page, edit line 70 of theme.php (height: 500px)";

define("THEME_DISCLAIMER", "<br /><i>'soar'</i> theme by jalist");

// [layout]
$layout = "_default";

$HEADER = 
"<div style='text-align:center'>
<table style='width:751px' cellspacing='0' cellpadding='0'>
<tr>
<td><img src='".THEME."images/logo.jpg' alt='' /></div></td>
</tr>
</table>
<table style='width:750px' cellspacing='0' cellpadding='0'>
<tr>
<td class='box' style='width:750px;'>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='50%'>
{CUSTOM=clock}
</td>
<td style='50%' style='text-align:right'>
{CUSTOM=search}
</td>
</tr>
</table>
</td>
</tr>
</table>
<table style='width:751px; background-color:#A5A5A9' cellspacing='10' cellpadding='0'>
<tr>
<td colspan='2'>
{SITELINKS=flat}
</td>
</tr>
<tr>
<td style='width:201px; vertical-align: top;'>
{SETSTYLE=leftmenu}
{MENU=1}
</td>
{SETSTYLE=default}
<td style='width:550px; vertical-align: top;'>
";

$FOOTER = "
</td>
</tr>
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


$CUSTOMHEADER = 
"<div style='text-align:center'>
<table style='width:751px' cellspacing='0' cellpadding='0'>
<tr>
<td><img src='".THEME."images/logo.jpg' alt='' /></div></td>
</tr>
</table>
<table style='width:751px' cellspacing='0' cellpadding='0'>
<tr>
<td class='box' style='width:750px;'>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='50%'>
{CUSTOM=clock}
</td>
<td style='50%' style='text-align:right'>
{CUSTOM=search}
</td>
</tr>
</table>
</td>
</tr>
</table>
<table style='width:751px; background-color:#A5A5A9' cellspacing='10' cellpadding='0'>
<tr>
<td>
{SITELINKS=flat}
</td>
</tr>
<tr>
{SETSTYLE=default}
<td style='width:751px; vertical-align: top;'>
";





$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php stats.php user.php links.php subcontent.php submitnews.php download.php search.php chat.php comment.php usersettings.php fpw.php";

//	[newsstyle]

$NEWSSTYLE = "

<table class='border' style='width:100%' cellpadding='0' cellspacing='0'>
<tr>
<td colspan='3' class='caption'>
{NEWSTITLE}
</td>
</tr>

<tr>
<td class='shadow_left'><img src='".THEME."images/blank.gif' width='7' height='21' alt='' style='display: block;' /></td>
<td class='shadow_middle'></td>
<td class='shadow_right'><img src='".THEME."images/blank.gif' width='7' height='21' alt='' style='display: block;' /></td>
</tr>

<tr>
<td colspan='3' class='bodytable'>
{NEWSICON}
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
</td>
</tr>
</table>
<br />
";

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
define(LINKALIGN, "center");

//	[tablestyle]

function tablestyle($caption, $text){
	global $style;
	if($style == "leftmenu"){
		echo"<table class='border' style='width:100%' cellpadding='0' cellspacing='0'><tr><td class='caption2' style='whitespace:nowrap'>$caption</td></tr><tr><td class='bodytable2'>$text</td></tr></table><br />";
	}else{
		echo "<table class='border' style='width:100%' cellpadding='0' cellspacing='0'>\n<tr>\n<td colspan='3' class='caption'>$caption</td>\n<tr>\n<td class='shadow_left'><img src='".THEME."images/blank.gif' width='7' height='21' alt='' style='display: block;' /></td>\n<td class='shadow_middle'></td>\n<td class='shadow_right'><img src='".THEME."images/blank.gif' width='7' height='21' alt='' style='display: block;' /></td>\n</tr>\n<tr>\n<td colspan='3' class='bodytable'>\n$text\n</td>\n</tr>\n</table>";	
	}
}

// Forum design
if(strstr(e_SELF,"forum.php")||strstr(e_SELF,"forum_post.php")||strstr(e_SELF,"forum_viewforum.php")||strstr(e_SELF,"forum_viewtopic.php")){
	@require_once("forum_design.php");
}

?>