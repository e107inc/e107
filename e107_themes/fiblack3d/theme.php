<?php
/*
+---------------------------------------------------------------+
|	e107 website system
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

$themename = "fiblack3d";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "Based on fiblack3d by xtreme, http://6ig.com";

define("THEME_DISCLAIMER", "<br />fiblack3d theme by jalist ported from original theme by xtreme, <a href='http://6ig.com'>6ig.com</a>, released with permission.");

// [layout]
$layout = "_default";

$HEADER = 
"<div class='centre'>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captiontopleft'><img src='".THEME."images/blank.gif' width='14' height='23' alt='' style='display: block;' /></td>
<td class='captiontopmiddle'><img src='".THEME."images/blank.gif' width='1' height='23' alt='' style='display: block;' /></td>
<td class='captiontopright'><img src='".THEME."images/blank.gif' width='15' height='23' alt='' style='display: block;' /></td>
</tr>

<tr>
<td class='captionleft'><img src='".THEME."images/blank.gif' width='14' height='1' alt='' style='display: block;' /></td>
<td class='captionmain'><img src='".THEME."images/logo2.png' alt='' style='width:100%' /></td>
<td class='captionright'><img src='".THEME."images/blank.gif' width='15' height='1' alt='' style='display: block;' /></td>
</tr>

<tr>
<td class='captionbottomleft'><img src='".THEME."images/blank.gif' width='14' height='26' alt='' style='display: block;' /></td>
<td class='captionbottommiddle'><img src='".THEME."images/blank.gif' width='1' height='26' alt='' style='display: block;' /></td>
<td class='captionbottomright'><img src='".THEME."images/blank.gif' width='15' height='26' alt='' style='display: block;' /></td>
</tr>
</table>

<table class='topbar' style='width:100%' cellpadding='2' cellspacing='0'>
<tr>
<td class='clock' style='width:25%; text-align:left'>&nbsp;
{SITETAG}
</td>
<td style='width:50%; text-align:center'>

{SITELINKS=flat}
</td>
<td class='clock' style='width:25%; text-align:right; vertical-align:middle'>
{CUSTOM=clock}
&nbsp;
</td>
</tr>
</table>


<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captiontopleft'><img src='".THEME."images/blank.gif' width='14' height='23' alt='' style='display: block;' /></td>
<td class='captiontopmiddle'><img src='".THEME."images/blank.gif' width='1' height='23' alt='' style='display: block;' /></td>
<td class='captiontopright'><img src='".THEME."images/blank.gif' width='15' height='23' alt='' style='display: block;' /></td>
</tr>

<tr>
<td class='captionleft'><img src='".THEME."images/blank.gif' width='14' height='1' alt='' style='display: block;' /></td>
<td class='captionmain'>

<table style='width:100%' cellspacing='8'>
<tr>
<td style='width:15%; vertical-align: top'>
{MENU=1}
</td>
<td style='width:70%; vertical-align: top'>";


$FOOTER = 
"<div style='text-align:center' class='smalltext'>
{SITEDISCLAIMER}
</div>
</td>
<td style='width:15%; vertical-align:top'>
{MENU=2}
</td>
</tr>
</table>



</td>
<td class='captionright'><img src='".THEME."images/blank.gif' width='15' height='1' alt='' style='display: block;' /></td>
</tr>

<tr>
<td class='captionbottomleft'><img src='".THEME."images/blank.gif' width='14' height='26' alt='' style='display: block;' /></td>
<td class='captionbottommiddle'><img src='".THEME."images/blank.gif' width='1' height='26' alt='' style='display: block;' /></td>
<td class='captionbottomright'><img src='".THEME."images/blank.gif' width='15' height='26' alt='' style='display: block;' /></td>
</tr>
</table></div>



";


//	[newsstyle]

$NEWSSTYLE = "
<table style='width:100%' cellpadding='0' cellspacing='0'>
<tr>
<td class='caption'>

<table style='width:100%'>
<tr>
<td class='captiontext' style='width:80%; text-align:left'>
{NEWSTITLE}
</td>
<td style='width:20%; text-align:right'>
{EMAILICON}
 | 
{PRINTICON}
</td>
</tr>
</table>

</td>
</tr>

<tr>
<td class='bodytable'>
{NEWSBODY}
{EXTENDED}
</td>



</tr>
<tr>
<td class='newsspacer'><img src='".THEME."images/blank.gif' alt='' /></td>
</tr>
<tr>
<td class='caption'>
<div style='text-align:center' class='smalltext'>
Posted by:
{NEWSAUTHOR}
on
{NEWSDATE}
 | 
{NEWSCOMMENTS}
</div>
</td>
</tr>
</table>


<br />";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "comments: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");


// [linkstyle]

define(PRELINK, "<img src='".THEME."images/bullet3.gif' alt='' />&nbsp;");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, "<img src='".THEME."images/bullet3.gif' alt='' />&nbsp;");
define(LINKALIGN, "center");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
	echo "<div class='spacer'>";
	if($caption != ""){
		echo "<div class='border'><div class='caption'><b>".$caption."</b></div>\n";
		if($text != ""){
			echo "\n<div class='bodytable'>".$text."</div>\n";
		}
		echo "</div>";
	}else{
		echo "<div class='border'><div class='bodytable'>".$text."</div></div><br />\n";
	}
	echo "</div>";
}

// [commentstyle]

$COMMENTSTYLE = "
<div style='text-align:center'>
<table style='width:100%'>
<tr>
<td colspan='2' class='forumheader3'>
{SUBJECT}
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
{LEVEL}
{COMMENTS}
<br />
{JOINED}
<br />
{REPLY}
</span>
</td>
<td style='width:70%; vertical-align:top'>
{COMMENT}
</td>
</tr>
</table>
</div>
<br />";

//	[chatboxstyle]

$CHATBOXSTYLE = "
<span class='defaulttext'><b>{USERNAME}</b></span>
<span class='smalltext'>on {TIMEDATE}</span><br />
<div class='defaulttext' style='text-align:left'>
{MESSAGE}
</div>
<br />
";





?>