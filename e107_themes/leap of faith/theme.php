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

$themename = "leap of faith";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "23/06/2003";
$themeinfo = "";

define("THEME_DISCLAIMER", "<br /><i>leap of faith theme by jalist</i>");


// [layout]

$layout = "_default";
$logo = THEME."images/bullet3.gif";

$HEADER = 
"
<table style='width:100%; background-color:white' cellspacing='3' class='topborder'>
<tr>
<td colspan='2' style='text-align:left'>
{LOGO}
</td>
<td style='text-align:right'>
{BANNER}
</td>
</tr>
</table>

<table style='width:100%' class='forumheader' cellspacing='3'>
<tr>
<td>
{SITETAG}
</td>
<td style='text-align:right'>
{CUSTOM=search}
</td>
</tr>
</table>


<table style='width:100%' cellspacing='3'>
<tr>
<td style='width:20%; vertical-align: top;'>
{SITELINKS=menu}
{MENU=1}
</td><td style='width:60%; vertical-align: top;'>";

$FOOTER = 
"</td><td style='width:20%; vertical-align:top'>
{MENU=2}
</td></tr></table>
<div style='text-align:center' class='smalltext'>
{SITEDISCLAIMER}
<table style='width:60%'>
<tr>
<td style='width:33%; vertical-align:top'>
{MENU=4}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=3}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=5}
</td>
</tr>
</table>
</div>";

function rand_tag(){
	$tags = file(e_FILE."taglines.txt");
	return stripslashes(htmlspecialchars($tags[rand(0, count($tags))]));
}

//	[newsstyle]

$NEWSSTYLE = "
<div class='spacer'>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captiontopleft'><img src='".THEME."images/blank.gif' width='21' height='4' alt='' style='display: block;' /></td>
<td class='captiontopmiddle'><img src='".THEME."images/blank.gif' width='1' height='4' alt='' style='display: block;' /></td>
<td class='captiontopright'><img src='".THEME."images/blank.gif' width='8' height='4' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captionleft'><img src='".THEME."images/blank.gif' width='21' height='17' alt='' style='display: block;' /></td>
<td class='captionbar' style='white-space:nowrap'>
{NEWSTITLE}
</td>
<td class='captionend'><img src='".THEME."images/blank.gif' width='21' height='17' alt='' style='display: block;' /></td>
<td class='captionmain'><img src='".THEME."images/blank.gif' width='1' height='17' alt='' style='display: block;' /></td>
<td class='captionright'><img src='".THEME."images/blank.gif' width='9' height='17' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bodyleft'><img src='".THEME."images/blank.gif' width='3' height='1' alt='' style='display: block;' /></td>
<td class='bodymain'>
{NEWSBODY}
{EXTENDED}
<div class='alttd' style='text-align:right'>
Posted by 
{NEWSAUTHOR}
on
{NEWSDATE}
 | 
{NEWSCOMMENTS}
 | 
{EMAILICON}
{PRINTICON}
</div>
</td>
<td class='bodyright'><img src='".THEME."images/blank.gif' width='3' height='1' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bottomleft'><img src='".THEME."images/blank.gif' width='12' height='8' alt='' style='display: block;' /></td>
<td class='bottommain'><img src='".THEME."images/blank.gif' width='1' height='8' alt='' style='display: block;' /></td>
<td class='bottomright'><img src='".THEME."images/blank.gif' width='12' height='8' alt='' style='display: block;' /></td>
</tr>
</table>
</div>";



define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);
define(LINKALIGN, "left");



//	[tablestyle]

function tablestyle($caption, $text){
	global $style;
//	echo "Mode: ".$style;

	echo "<div class='spacer'>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captiontopleft'><img src='".THEME."images/blank.gif' width='21' height='4' alt='' style='display: block;' /></td>
<td class='captiontopmiddle'><img src='".THEME."images/blank.gif' width='1' height='4' alt='' style='display: block;' /></td>
<td class='captiontopright'><img src='".THEME."images/blank.gif' width='8' height='4' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captionleft'><img src='".THEME."images/blank.gif' width='21' height='17' alt='' style='display: block;' /></td>
<td class='captionbar' style='white-space:nowrap'>".$caption."</td>
<td class='captionend'><img src='".THEME."images/blank.gif' width='21' height='17' alt='' style='display: block;' /></td>
<td class='captionmain'><img src='".THEME."images/blank.gif' width='1' height='17' alt='' style='display: block;' /></td>
<td class='captionright'><img src='".THEME."images/blank.gif' width='9' height='17' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bodyleft'><img src='".THEME."images/blank.gif' width='3' height='1' alt='' style='display: block;' /></td>
<td class='bodymain'>".$text."</td>
<td class='bodyright'><img src='".THEME."images/blank.gif' width='3' height='1' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bottomleft'><img src='".THEME."images/blank.gif' width='12' height='8' alt='' style='display: block;' /></td>
<td class='bottommain'><img src='".THEME."images/blank.gif' width='1' height='8' alt='' style='display: block;' /></td>
<td class='bottomright'><img src='".THEME."images/blank.gif' width='12' height='8' alt='' style='display: block;' /></td>
</tr>
</table>
</div>";

}

/*
$POLLSTYLE = <<< EOT
<b>Poll: {QUESTION}</b>
<br /><br />
{OPTIONS=<div class='alttd'>OPTION</div>BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br />\n}
<div style='text-align:center' class='smalltext'>{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>
EOT;
*/
$CHATBOXSTYLE = "
<table class='alttd' style='width:100%'><tr>
<td class='mediumtext'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b>{USERNAME}</b></td>
<td class='smalltext' style='text-align:right'>{TIMEDATE}</td>
</tr></table>
{MESSAGE}
";


$COMMENTSTYLE = "
<div style='text-align:center'>
<table style='width:100%'>
<tr>
<td colspan='2' class='alttd'>
<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
<b>
{USERNAME}
</b>
 | 
 {TIMEDATE}
</td>
</tr>
<tr>
<td style='width:30%; vertical-align:top'>
<div class='spacer'>
{AVATAR}
</div>
<span class='smalltext'>
{COMMENTS}
<br />
{JOINED}
</span>
</td>
<td style='width:70%; vertical-align:top'>
{COMMENT}
</td>
</tr>
</table>
</div>
<br />";

?>