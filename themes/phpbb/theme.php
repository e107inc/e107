<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	default theme file														|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/

// [theme]

$themename = "e107";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+";

// [layout]

$layout = "_default";
$admin_logo = "1";

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

$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php";

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
{NEWSSOURCE}
{NEWSURL}
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

define("PRE_SOURCESTRING", "<br />");
define("SOURCESTRING", "Source: ");
define("POST_SOURCESTRING", "<br />");

define("PRE_URLSTRING", "<br />");
define("URLSTRING", "Link: ");
define("POST_URLSTRING", "<br />");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet3.gif' alt='bullet' /> ");
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
<span class='smalltext'>
{TIMEDATE}
</span>
<br />
{COMMENT}
<br /><i><span class='smalltext'>
{SIGNATURE}
</span></i>
<br />
<div class='smalltext'>
{ADMINOPTIONS}
</div>
</td>
</tr>
</table>
<br />";

//	[chatboxstyle]

$CHATBOXSTYLE = "
<span class='smalltext'><b>
{USERNAME}
</b> on 
{TIMEDATE}
</span><br />
<div class='mediumtext' style='text-align:left'>
{MESSAGE}
</div>
<div class='smalltext' style='text-align:right'>
{ADMINOPTIONS}
</div>";

define(CB_STYLE, $CHATBOXSTYLE);

?>