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

$themename = "clan1";
$themeversion = "2.0";
$themeauthor = "jalist";
$themedate = "28/06/2003";
$themeinfo = "";

define("THEME_DISCLAIMER", "<br /><i>clan</i> theme by jalist");

// [layout]

$layout = "_default";

$HEADER = "
<div style='text-align:center'>
<table style='width:848px' cellspacing='0' cellpadding='0'>
<tr>
<td style='text-align:left; background-color: #123134; '>
<img src='".THEME."images/logo.png' alt='bullet' />
</td>
<td style='text-align:right; background-color: #123134; '>
{BANNER}
</td>
</tr>
</table>
<table style='width:848px' cellspacing='0' cellpadding='0'>
<tr>
<td style='width:50%; text-align:left;' class='caption2'>
{SITETAG}
</td>
<td style='width:50%; text-align:right;' class='caption2'>
{CUSTOM=search}
</td>
</tr></table><br />
<table style='width:848px; ' cellspacing='0' cellspacing='0'>
<tr> <td style='width:174px; vertical-align: top;' class='menubg'>
{SETSTYLE=leftmenu}
{SITELINKS=menu}
{MENU=1}
</td><td style='500px; vertical-align: top;'>";

$FOOTER = 
"<td style='width:174px; vertical-align: top;' class='menubg'>
{MENU=2}
</td></tr>
<tr>
<td style='width:174px; vertical-align: top;' class='menuend'><img src='images/blank.gif' width='174' height='5' alt='' style='display: block;'/></td>
</td><td style='500px; vertical-align: top;'></td>
<td style='width:174px; vertical-align: top;' class='menuend'><img src='images/blank.gif' width='174' height='5' alt='' style='display: block;'/></td>
</tr>
</table>
<table style='width:848px'>
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
<tr>
<td colspan='3' style='text-align:center'>
{SITEDISCLAIMER}
</td>
</tr>
</table></div>";

$CUSTOMHEADER = "
<div style='text-align:center'>
<table style='width:848px' cellspacing='0' cellpadding='0'>
<tr>
<td style='text-align:left; background-color: #123134; '>
<img src='".THEME."images/logo.png' alt='bullet' />
</td>
<td style='text-align:right; background-color: #123134; '>
{BANNER}
</td>
</tr>
</table>
<table style='width:848px' cellspacing='0' cellpadding='0'>
<tr>
<td style='width:50%; text-align:left;' class='caption2'>
{SITETAG}
</td>
<td style='width:50%; text-align:right;' class='caption2'>
{CUSTOM=search}
</td>
</tr></table><br />
<table style='width:848px; ' cellspacing='0' cellspacing='0'>
<tr><td style='text-align:center'>";

$CUSTOMFOOTER = "
<td>
</tr>
</table>
<table style='width:848px'>
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
<tr>
<td colspan='3' style='text-align:center'>
{SITEDISCLAIMER}
</td>
</tr>
</table></div>";

$CUSTOMPAGES = "forum.php forum_viewforum.php forum_viewtopic.php forum_post.php";

//	[newsstyle]

$NEWSSTYLE = "
<div style='text-align:center'>
<table style='width:80%'>
<tr>
<td style='text-align:center'>
{NEWSICON}
</td></tr>
<tr><td style='text-align:justify'>
<b><i>{NEWSTITLE}</i></b> ...
{NEWSBODY}
<br />
<div style='text-align:right' class='smalltext'>
by  
{NEWSAUTHOR}
on
{NEWSDATE}. 
{NEWSCOMMENTS}
{EXTENDED}
</div>
</td></tr></table>
</div>
<br /><br />";

define("ICONSTYLE", "border:0");
define("COMMENTLINK", "Comments: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("EXTENDEDSTRING", "Read more ...");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ");
define(LINKEND, "<br />");
//define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
	if($mode == "default"){
		if($caption != ""){
			echo "<div class='border'><div class='caption'>".$caption."</div></div>\n";
			if($text != ""){
				echo "\n<div class='bodytable'>".$text."</div>\n";
			}
		}else{
			echo "<div class='border'><div class='bodytable'>".$text."</div></div><br />\n";
		}
	}else{
		if($caption != ""){
			echo "<div class='border'><div class='caption2'>".$caption."</div></div>";
			if($text != ""){
				echo "<div class='bodytable2'>".$text."</div><br />\n";
			}
		}else{
			echo "<div class='bodytable2'>".$text."</div><br />\n";
		}
	}
}

// [commentstyle]

$COMMENTSTYLE = "
<table style='width:95%'>
<tr>
<td style='width:20%; vertical-align:top'>
<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
<b>
{USERNAME}
</b>
<div class='spacer'>
{AVATAR}
</div>
<span class='smalltext'>
Comments: 
{COMMENTS}
<br />
Joined: 
{JOINED}
</span>
</td>
<td style='width:80%; vertical-align:top'>
<span class='smalltext'>
{TIMEDATE}
</span>
<br />
{COMMENT}
<br /><i><span class='smalltext'>Signature: 
{SIGNATURE}
</span></i>
</td>
</tr>
</table>
<br />";

//	[chatboxstyle]

$CHATBOXSTYLE = "
<span class='smalltext'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b>
{USERNAME}
</b><br />
{TIMEDATE}
</span><br />
<div class='mediumtext'>
{MESSAGE}
</div>
";

?>