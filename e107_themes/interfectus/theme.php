<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2005
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [multilanguage]
@include_once(e_THEME."interfectus/languages/".e_LANGUAGE.".php");
@include_once(e_THEME."interfectus/languages/English.php");

// [theme]
$themename = "interfectus";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themedate = "16/03/2005";
$themeinfo = "Dark theme suitable for gaming / clan sites";
define("STANDARDS_MODE", TRUE);

// [layout]

$layout = "_default";

$HEADER = "
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='logo'><div id='sitename'>[ <a href='".e_BASE."index.php'>{SITENAME}</a> ]</div></td>
</tr>
</table>
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='collefttop'></td>
<td id='infoleft'>
<div class='padder'>
{CUSTOM=search+25+".THEME."images/search.png+19+18}
</div>
</td>
<td id='inforight'>
<div class='padder'>
{CUSTOM=clock}
</div>
</td>
<td id='colrighttop'></td>
</tr>
</table>
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='colleft'></td>
<td>
<table class='tablewrapper' cellpadding='0' cellspacing='0'>
<tr>
<td id='contentarea'>
<div class='padder'>
<table class='tablewrapper' cellpadding='0' cellspacing='0'>
<tr><td class='pageheader'></td></tr>
<tr><td class='pagebody'>
{SETSTYLE=main}

";

$FOOTER = "
</td></tr>
<tr><td class='pagefooter'></td></tr>
</table>
</div>
</td>
<td id='menuarea'>
<table class='menutable' cellpadding='0' cellspacing='0'>
<tr>
<td class='menutop'></td>
</tr>
<tr>
<td class='menubody'>
<div class='menuwrapper'>
{SETSTYLE=menu1}
{SITELINKS}
{MENU=1}
</div>
</td>
</tr>
<tr>
<td class='menubottom'></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
<td id='colright'></td>
</tr>
</table>
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='colbotleft'><img src='".THEME."images/blank.gif' width='14' height='14' alt='' style='display: block;' /></td>
<td id='colbot'></td>
<td id='colbotright'><img src='".THEME."images/blank.gif' width='14' height='14' alt='' style='display: block;' /></td>
</tr>
</table>

";


/*
<table class='menutable' cellpadding='0' cellspacing='0'>
<tr>
<td class='menutop2'></td>
</tr>
<tr>
<td class='menubody2'>
<div class='menuwrapper'>
{PLUGIN=other_news_menu/other_news2_menu}
</div>
</td>
</tr>
<tr>
<td class='menubottom2'></td>
</tr>
</table>
*/


$CUSTOMHEADER = "
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='logo'><div id='sitename'>[ <a href='".e_BASE."index.php'>{SITENAME}</a> ]</div></td>
</tr>
</table>
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='collefttop'></td>
<td id='infoleft'>
<div class='padder'>
{CUSTOM=search+25+".THEME."images/search.png+19+18}
</div>
</td>
<td id='inforight'>
<div class='padder'>
{CUSTOM=clock}
</div>
</td>
<td id='colrighttop'></td>
</tr>
</table>
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='colleft'></td>
<td>
<table class='tablewrapper' cellpadding='0' cellspacing='0'>
<tr>
<td id='fullcontentarea'>
<div class='padder'>
<table class='tablewrapper' cellpadding='0' cellspacing='0'>
<tr><td class='pageheader'></td></tr>
<tr><td class='pagebody'>
{SETSTYLE=main}

";

$CUSTOMFOOTER = "

</td></tr>
<tr><td class='pagefooter'></td></tr>
</table>
</div>
</td>
</tr>
</table>
</td>
<td id='colright'></td>
</tr>
</table>
<table class='maintable' cellpadding='0' cellspacing='0'>
<tr>
<td id='colbotleft'><img src='".THEME."images/blank.gif' width='14' height='14' alt='' style='display: block;' /></td>
<td id='colbot'></td>
<td id='colbotright'><img src='".THEME."images/blank.gif' width='14' height='14' alt='' style='display: block;' /></td>
</tr>
</table>

";


$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php user.php submitnews.php download.php links.php stats.php usersettings.php signup.php";




$NEWSSTYLE = "
<div class='captiontext'>{NEWSTITLE}</div>
{NEWSBODY}
{EXTENDED}
</div>
<div style='text-align:right' class='smalltext'>
{NEWSAUTHOR}
on
{NEWSDATE}
<br />
{NEWSCOMMENTS}{TRACKBACK}
</div>
<br />";

define("ICONSTYLE", "");
define("COMMENTLINK", LAN_THEME_2);
define("COMMENTOFFSTRING", LAN_THEME_3);
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_4);
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", LAN_THEME_5);
define("TRACKBACKBEFORESTRING", " | ");


// [linkstyle]

define('PRELINK', "");
define('POSTLINK', "");
define('LINKSTART', "<div class='link1' onmouseover=\"this.className='link2';\" onmouseout=\"this.className='link1';\"><div class='linktext'><img src='".THEME."images/bullet1.gif' alt='' />&nbsp;&nbsp;");
define("LINKSTART_HILITE", "<div class='link2' onmouseover=\"this.className='link1';\" onmouseout=\"this.className='link2';\"><div class='linktext'><img src='".THEME."images/bullet1.gif' alt='' />&nbsp;&nbsp;");
define('LINKEND', "</div></div>");
define('LINKDISPLAY', 1);
define('LINKALIGN', "left");




//	[tablestyle]

function tablestyle($caption, $text)
{
	global $style;

	if($style == "menu1")
	{
		echo "<div class='caption'><div class='captionpadder'>$caption</div></div><br /><div class='padder'>$text</div><br />";
	}
	else if($style == "menu2")
	{
		echo "<table class='menutable' cellpadding='0' cellspacing='0'>
<tr>
<td class='menutop2'></td>
</tr>
<tr>
<td class='menubody2'>
<div class='menuwrapper'>
$caption<br /><br />
$text
</div>
</td>
</tr>
<tr>
<td class='menubottom2'></td>
</tr>
</table>";
	}
	else
	{
		echo "<div class='captiontext'>$caption</div>$text<br />";
	}
}

$COMMENTSTYLE = "<br /><br />
<div class='captiontext'><img src='".THEME."images/bullet1.gif' alt='' style='vertical-align: middle;' /> {USERNAME} | {TIMEDATE}</div>
{COMMENT}<br />
<span class='smalltext'>{REPLY}{IPADDRESS}</span>
";



$CHATBOXSTYLE = "
<div class='link2'><div class='linktext'><img src='".THEME."images/bullet1.gif' alt='' style='vertical-align: middle;' /> {USERNAME} | <span class='cbdate'>{TIMEDATE}</span></div></div>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";











?>