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
if(!defined("e_THEME")){ exit; }
// [theme]
$themename = "ranyart";
$themeversion = "1";
$themeauthor = "jalist";
$themedate = "14/09/2003";
$themeinfo = "To use sectioning with this theme uncomment the relevant code from theme.php.";
define("STANDARDS_MODE", TRUE);
// [layout]

$layout = "_default";

$HEADER = "
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='background-color:#fff'>
<table style='width:100%'>
<tr>
<td style='width:50%; text-align:left'>
{LOGO}
</td>
<td style='width:50%; text-align:right'>
{BANNER=campaign_one}
</td>
</tr>
</table>
</td></tr>
<tr><td class='thinblackline'></td></tr>
<tr><td class='thinwhiteline'></td></tr>
<tr><td class='thingreyline'>
<table style='width:100%'>
<tr>
<td style='width:50%; text-align:left'>
{CUSTOM=login}
</td>
<td style='width:50%; text-align:right'>
{CUSTOM=search}
</td>
</tr>
</table>
</td></tr>
<tr>
<td class='thinblackline'></td>
</tr>
</table>
<table >
<tr> 
<td style='padding:10px;width:15%; vertical-align: top;'>
{SETSTYLE=menu1}
{SITELINKS=menu}
{MENU=1}
<br />
</td>
{SETSTYLE=default}
<td style='padding-top:10px;width:70%; vertical-align: top'>";

/*

// uncomment to use sectioning and listing

$NEWSHEADER = "
<div style='text-align:center'>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='background-color:#fff'>
<table style='width:100%'>
<tr>
<td style='width:50%; text-align:left'>
{LOGO}
</td>
<td style='width:50%; text-align:right'>
{BANNER=campaign_one}
</td>
</tr>
</table>
</td></tr>
<tr><td style='background-color:#000'></td></tr>
<tr><td style='background-color:#fff'></td></tr>
<tr><td style='background-color:#B3BAC5'>
<table style='width:100%'>
<tr>
<td style='width:50%; text-align:left'>
{CUSTOM=login}
</td>
<td style='width:50%; text-align:right'>
{CUSTOM=search}
</td>
</tr>
</table>
</td></tr>
<tr>
<td style='background-color:#000'></td>
</tr>
</table>
<table style='width:100%' cellspacing='10' cellpadding='10'>
<tr> 
<td style='width:15%; vertical-align: top;'>
{SETSTYLE=menu1}
{SITELINKS=menu}
{MENU=1}
<br />
</td>
{SETSTYLE=default}
<td style='width:70%; vertical-align: top'>
<table style='width:100%'>
<tr>
<td style='width:50%; vertical-align:top'>
{NEWS_CATEGORY=1}
</td>
<td style='width:50%; vertical-align:top'>
{NEWS_CATEGORY=2}
</td>
</tr>
</table>";
*/

$NEWSLISTSTYLE = "
{NEWSICON}
<b>
{NEWSTITLELINK}
</b>
<div class='smalltext'>
{NEWSAUTHOR}
on
{NEWSDATE}
{NEWSCOMMENTS}
</div>
<hr />
";


$FOOTER = "
<br />
</td>
<td style='padding:10px;width:15%; vertical-align:top'>
{SETSTYLE=menu1}
{MENU=2}
</td>
</tr>
</table>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td class='thinblackline'></td>
</tr>
<tr>
<td class='thinwhiteline'></td>
</tr>
<tr>
<td class='thingreyline' style='text-align:center'>
{SITEDISCLAIMER}
<br />
<img src='".e_IMAGE."generic/php-small-trans-light.gif' alt='' /> <img src='".e_IMAGE."button.png' alt='' /> <img src='".e_IMAGE."generic/poweredbymysql-88.png' alt='' />
</td>
</tr>
<tr>
<td class='thinblackline'></td>
</tr>
</table>
";

$NEWSSTYLE = "
<div class='border2'>
<div class='caption2'>
{NEWSTITLE}
</div>
<div class='bodytable2' style='text-align:left'>
{NEWSICON}
{NEWSBODY}
{EXTENDED}
</div>
<div style='text-align:right' class='alttd9'>
{NEWSAUTHOR}
on
{NEWSDATE}
<br />
{NEWSCOMMENTS}{TRACKBACK}
</div>
</div>
<br />";
define("TRACKBACKSTRING", "Trackbacks: ");
define("TRACKBACKBEFORESTRING", " | ");
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
define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]

function tablestyle($caption, $text){
	global $style;
	if($style == "menu1" || !$style){
		if($caption != ""){
			echo "<div class='border'><div class='caption'>".$caption."</div>";
			if($text != ""){
				echo "\n<div class='bodytable'>".$text."</div></div><br />";
			}
		}else{
			echo "<div class='border2'><div class='bodytable2'>".$text."</div></div><br />";
		}
	}else if($style == "default"){
		if($caption != ""){
			echo "<div class='border2'><div class='caption2'>".$caption."</div><div class='bodytable2'>".$text."</div></div><br />";
		}else{
			echo "<div class='bodytable'>".$text."</div><br />";
		}
	}else{
		if($caption != ""){
			echo "<div class='border3'><div class='caption3'>".$caption."</div></div><div class='bodytable3'>".$text."</div><br />";
		}else{
			echo "<div class='bodytable3'>".$text."</div><br />";
		}
	}
}

$COMMENTSTYLE = "
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
{COMMENTS}
<br />
{JOINED}
</span>
<br/>
{REPLY}
</td>
<td style='width:70%; vertical-align:top'>
{COMMENT}
</td>
</tr>
</table>
<br />";

$POLLSTYLE = <<< EOF
<b>Poll:</b> {QUESTION}
<br /><br />
{OPTIONS=<div class='alttd8'>OPTION</div>BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br />\n}
<br /><div style='text-align:center' class='smalltext'>{AUTHOR}<br />{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>
EOF;

$CHATBOXSTYLE = "
<div class='alttd9'>
<img src='".THEME."images/bullet2.gif' alt='bullet' />
<b>{USERNAME}</b><br />{TIMEDATE}
</div>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>