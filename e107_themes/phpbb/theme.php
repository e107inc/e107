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

$themename = "phpBB";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "";

define("THEME_DISCLAIMER", "<br />phpBB theme by jalist based on phpBB forum system, <a href='http://phpbb.com'>phpBB.com</a>");

// [layout]
$layout = "_default";

$HEADER = 
"<div class='centre'>
<table style='width:100%' class='maintable' cellspacing='10'>
<tr>
<td style='width:30%'>
{LOGO}
</td>
<td style='width:10%;'>&nbsp;</td>
<td style='width:50%; text-align:center'>
<span class='captiontext'>
{SITETAG}
</span>
<br /><br />
{SITELINKS=flat}
</td>
<td style='width:10%;'>&nbsp;</td>
</tr>
<tr>
<td colspan='4' >
<table style='width:100%' cellspacing='10'><tr>
<td style='width:25%; vertical-align: top;'>
{MENU=1}
</td><td style='width:50%; vertical-align: top;'>";


$FOOTER = 
"</td><td style='width:25%; vertical-align:top'>
{MENU=2}
</td></tr>
<tr>
<td colspan='3' style='text-align:center' class='smalltext'>
{SITEDISCLAIMER}
</td>
</tr>
</table>
<table style='width:60%'>
<tr>
<td style='width:33%; vertical-align:top'>
{MENU=3}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=4}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=5}
</td>
</tr>
</table>
</td></tr></table></div>";

$CUSTOMHEADER = "
<div style='text-align:center'>
<table style='width:100%' class='maintable' cellspacing='10'>
<tr>
<td style='width:30%'>
{LOGO}
</td>
<td style='width:10%;'>&nbsp;</td>
<td style='width:50%; text-align:center'>
<span class='captiontext'>
{SITETAG}
</span>
<br /><br />
{SITELINKS=flat}
</td>
<td style='width:10%;'>&nbsp;</td>
</tr>
<tr>
<td colspan='4' >
<table style='width:100%' cellspacing='10'><tr>
<td>";

$CUSTOMFOOTER = "
</td></tr>
<tr>
<td colspan='3' style='text-align:center' class='smalltext'>
{SITEDISCLAIMER}
</td>
</tr>
</table>
<table style='width:60%'>
<tr>
<td style='width:33%; vertical-align:top'>
{MENU=3}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=4}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=5}
</td>
</tr>
</table>
</td></tr></table></div>";



$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php e107_test/test.php";

//	[newsstyle]

$NEWSSTYLE = "
<div class='border'>
<div class='caption'>
<b>
{NEWSTITLE}
</b>
</div>
<div class='bodytable'>
<div style='text-align:left'>
{NEWSBODY}
{EXTENDED}
<div style='text-align:right'>
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
</div>
</div>
</div>
<div class='infobar'>
Posted by 
{NEWSAUTHOR}
on
{NEWSDATE}
&nbsp;::&nbsp;
{NEWSCOMMENTS}
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

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet3.gif' alt='bullet' style='vertical-align:middle' /> ");
define(LINKEND, "&nbsp;&nbsp;");
define(LINKALIGN, "center");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
	if($caption != ""){
		echo "<div class='border'><div class='caption'><b>".$caption."</b></div>\n";
		if($text != ""){
			echo "\n<div class='bodytable'>".$text."</div>\n";
		}
		echo "</div>";
	}else{
		echo "<div class='border'><div class='bodytable'>".$text."</div></div><br />\n";
	}
	echo "<br />";
}

// [commentstyle]

$COMMENTSTYLE = "
<table style='width:95%'>
<tr>
<td style='width:30%; vertical-align:top'>
<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
<b>
{USERNAME}
</b>
<br />
{LEVEL}
{AVATAR}
<span class='smalltext'>
{COMMENTS}
{JOINED}
{LOCATION}
</span>
</td>
<td style='width:70%; vertical-align:top'>
<span class='smalltext'>
{TIMEDATE}
</span>
<br />
{COMMENT}
<br /><i><span class='smalltext'>
{SIGNATURE}
</span></i>
</td>
</tr>
</table>
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