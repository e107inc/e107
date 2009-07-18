<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	(C)Steve Dunstan 2001-2005
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

// [multilanguage]
include_lan(e_THEME."khatru/languages/".e_LANGUAGE.".php");

// [theme]
$themename = "khatru";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themeemail = "jalist@e107.org";
$themewebsite = "http://e107.org";
$themedate = "28/05/2005";
$themeinfo = "";
define("STANDARDS_MODE", TRUE);
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
define("IMODE", "lite");
define("THEME_DISCLAIMER", "<br /><i>".LAN_THEME_1."</i>");

// [layout]

$layout = "_default";


define("BOXOPEN", "
<div class='spacer'>
<div class='dcaption'>
<div class='left'></div>
<div class='right'></div>
<div class='center'>");

define("BOXMAIN", "
</div>
</div>
<div class='dbody'>
<div class='leftwrapper'>
<div class='rightwrapper'>
<div class='leftcontent'></div>
<div class='rightcontent'></div>
<div class='dcenter'><div class='dinner'>
");

define("BOXCLOSE", "
</div></div>
</div>
</div>
</div>
<div class='dbottom'>
<div class='left'></div>
<div class='right'></div>
<div class='center'></div>
</div>
</div>");

define("BOXOPEN2", "
<div class='spacer'>
<div class='dntop'></div>
<div class='dbody'>
<div class='leftwrapper'>
<div class='rightwrapper'>
<div class='leftcontent'></div>
<div class='rightcontent'></div>
<div class='dcenter'><div class='dinner'>
");

define("BOXCLOSE2", "
</div></div>
</div>
</div>
</div>
<div class='dbottom'>
<div class='left'></div>
<div class='right'></div>
<div class='center'></div>
</div>
</div>
");



$HEADER = "
<div id='header'>
<div id='logo1'>&nbsp;</div>
<div id='headerr'>&nbsp;</div>
<div id='banner'>
{BANNER}
</div>
</div>


<div id='mainbox'>
	<div class='clear'>
		<div id='leftcontent'>
			{SITELINKS}
			{MENU=1}
		</div>
		<div id='rightcontent'>
			{MENU=2}
		</div>
		<div id='centercontent'>
";


$FOOTER = "
		</div>
	</div>
	<div id='footer'>
		{SITEDISCLAIMER}<br />{THEME_DISCLAIMER}
		<br />
		<a href='http://www.spreadfirefox.com/?q=affiliates&amp;id=0&amp;t=86'><img alt='e107 recommends Firefox' title='e107 recommends Firefox' src='http://sfx-images.mozilla.org/affiliates/Buttons/125x50/takebacktheweb_125x50.png' style='border: 0;' /></a>
	</div>
</div>
";



function tablestyle($caption, $text)
{
	echo "
<div class='dcaption'>
<div class='left'></div>
<div class='right'></div>
<div class='center'>$caption</div>
</div>
<div class='dbody'>
<div class='leftwrapper'>
<div class='rightwrapper'>
<div class='leftcontent'></div>
<div class='rightcontent'></div>
<div class='dcenter'><div class='dinner'>$text</div></div>
</div>
</div>
</div>
<div class='dbottom'>
<div class='left'></div>
<div class='right'></div>
<div class='center'></div>
</div>
";
}

// [linkstyle]

define('PRELINK', "");
define('POSTLINK', "");
define('LINKSTART', "");
define("LINKSTART_HILITE", "");
define('LINKEND', "<br />");
define('LINKDISPLAY', 2);
define('LINKALIGN', "left");
define("BULLET", "bullet.png");
define("bullet", "bullet.png");

$NEWSSTYLE = "
<div class='newheadline'>
{NEWSTITLE}
</div>
<div class='newsinfo'>
{NEWSAUTHOR}
, 
{NEWSDATE}
 // 
{NEWSCOMMENTS}{TRACKBACK}
</div>
<br />
<div class='newstext'>
{NEWSBODY}
{EXTENDED}
</div>
<br /><br />";
define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", LAN_THEME_3);
define("COMMENTOFFSTRING", LAN_THEME_2);
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_4);
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", LAN_THEME_5);
define("TRACKBACKBEFORESTRING", ", ");

$CHATBOXSTYLE = "
<br />
<img src='".THEME_ABS."images/bullet.png' alt=''  />
<span class='chatb'>{USERNAME}</span>
<div class='smalltext'>
{TIMEDATE}<br />
</div>
{MESSAGE}
<br /><br />";

$COMMENTSTYLE = "
<table style='width:100%'>
<tr>
<td colspan='2' class='commentinfo'>
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
<br />
{REPLY}
</td>
<td style='width:70%; vertical-align:top'>
{COMMENT}
<div style='text-align: right;' class='smallext'>{IPADDRESS}</div>
</td>
</tr>
</table>
<br />";

?>