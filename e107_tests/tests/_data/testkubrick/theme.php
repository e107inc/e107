<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_themes/kubrick/theme.php $
|     $Revision: 11678 $
|     $Id: theme.php 11678 2010-08-22 00:43:45Z e107coders $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// [multilanguage]

include_lan(e_THEME."testkubrick/languages/".e_LANGUAGE.".php");

// [theme]
$themename = "kubrick";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist] &amp; William Moffett [Que]";
$themeemail = "jalist@e107.org";
$themewebsite = "http://e107.org";
$themedate = "29/01/2005";
$themeinfo = "Based on 'kubrick' by Michael Heilemann (http://binarybonsai.com/kubrick/).<br />This theme is intended for minimilist blog sites.";
define("STANDARDS_MODE", TRUE);
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
define("IMODE", "lite");
define("THEME_DISCLAIMER", "<br /><i>".LAN_THEME_1."</i>");

if(!defined("e_THEME")){ exit; }

$register_sc[]= "UL";  // register shortcode ul.sc for inclusion.

// [layout]

$layout = "_default";

$HEADER = "<div id='page'>
<div id='header'>
<h1><a href='".SITEURL."' title='{SITENAME}'>{SITENAME}</a></h1>
<h2>{SITETAG}</h2>
{UL}
</div>
<div id='content' class='narrowcolumn'>";

$FOOTER = "
</div>
<div id='sidebar'>
{MENU=1}
{MENU=2}
</div>
<hr />
<div id='footer'>
<p>
{SITEDISCLAIMER}<br />{THEME_DISCLAIMER}
</p>
</div>
</div>
";

$CUSTOMHEADER = "<div id='page2'>
<div id='header'>
<h1><a href='".SITEURL."' title='{SITENAME}'>{SITENAME}</a></h1>
<h2>{SITETAG}</h2>
{UL}
</div>
<div id='content' class='widecolumn'>";


$CUSTOMFOOTER = "
</div>
<hr />
<div id='footer'>
<p>
{SITEDISCLAIMER}<br />{THEME_DISCLAIMER}
</p>
</div>
</div>
";

$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php user.php submitnews.php download.php links.php stats.php usersettings.php signup.php";

$NEWSSTYLE = "
<h2>{NEWSTITLE}</h2>
<small>".LAN_THEME_6." {NEWSDATE} | ".LAN_THEME_7." {NEWSAUTHOR}</small>
<div class='entry' style='text-align:left'>
{NEWSBODY}
{EXTENDED}
</div>
<div style='text-align:right' class='smalltext'>
{NEWSCOMMENTS}{TRACKBACK}
</div>
<br />";
define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", LAN_THEME_3);
define("COMMENTOFFSTRING", LAN_THEME_2);
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_4);
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", LAN_THEME_5);
define("TRACKBACKBEFORESTRING", " | ");


// [linkstyle]

define('PRELINK', "");
define('POSTLINK', "");
define('LINKSTART', "");
define('LINKEND', "");
define('LINKDISPLAY', 1);
define('LINKALIGN', "left");
define('LINKCLASS', "");

//	[tablestyle]

function tablestyle($caption, $text, $mode)
{
	echo "<h3>$caption</h3>\n<div>$text</div><br />\n";
}

$COMMENTSTYLE = "
<table style='width: 450px;'>
<tr>
<td style='width: 30%; vertical-align: top;'><span class='mediumtext'>{USERNAME}</span><br /><span class='smalltext'>{TIMEDATE}</span><br />{AVATAR}{REPLY}</td>
<td style='width: 70%; vertical-align: top;'><span class='mediumtext'>{COMMENT} {COMMENTEDIT}</span></td>
</tr>
</table>";


$CHATBOXSTYLE = "
<img src='".e_IMAGE_ABS."admin_images/chatbox_16.png' alt='' style='vertical-align: middle;' />
<b>{USERNAME}</b>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>