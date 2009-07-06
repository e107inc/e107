<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_themes/jayya/theme.php,v $
|     $Revision: 1.7 $
|     $Date: 2009-07-06 05:59:42 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

// Protect the file from direct access
if (!defined('e107_INIT')) { exit; }


// Get language definition files for this theme
include_lan(e_THEME."jayya/languages/".e_LANGUAGE.".php");


// [theme]

$themename = "Jayya";
$themeversion = "1.0";
$themeauthor = "e107devs";
$themedate = "";
$themeinfo = "";
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
define("THEME_DISCLAIMER", "");
define("IMODE", "lite");
define("STANDARDS_MODE", TRUE);

// [dont render core style sheet link]
	$no_core_css = TRUE;

// [layout]

$layout = "_default";

$HEADER = "<table class='page_container'>
<tr>
<td>

<table class='top_section'>
<tr>
<td class='top_section_left' style='width: 190px; padding-left: 5px; padding-right: 5px'>
{LOGO}
</td>
<td class='top_section_mid'>
{BANNER}
</td>

<td class='top_section_right' style='padding: 0px; white-space: nowrap; width: 170px'>
{CUSTOM=search+default}
</td>
</tr>
</table>

<div>
{SITELINKS_ALT=".THEME_ABS."images/arrow.png+noclick}
</div>

<table class='main_section'>
<colgroup>
<col style='width: 170px' />
<col style='width: auto' />
<col style='width: 170px' />
</colgroup>

<tr>
<td class='left_menu'>
<table class='menus_container'><tr><td>
{SETSTYLE=leftmenu}
{MENU=1}
</td></tr></table>
</td>
<td class='default_menu'>
{SETSTYLE=default}
{WMESSAGE}
";

$FOOTER = "<br />
</td>

<td class='right_menu'>
<table class='menus_container'><tr><td>
{SETSTYLE=rightmenu}
{MENU=2}
</td></tr></table>
</td>
</tr>
</table>
<div style='text-align:center'>
<br />
{SITEDISCLAIMER}
<br /><br />
</div>
</td>
</tr>
</table>
";


// [linkstyle]

define('PRELINK', '');
define('POSTLINK', '');
define('LINKSTART', '');
define('LINKEND', '');
define('LINKDISPLAY', 1);
define('LINKALIGN', 'left');


// [newsstyle]

$sc_style['NEWSIMAGE']['pre'] = "<td style='padding-right: 7px; vertical-align: top'>";
$sc_style['NEWSIMAGE']['post'] = "</td>";

$NEWSSTYLE = "<div class='cap_border'><div class='main_caption'><div class='bevel'>
{STICKY_ICON}{NEWSTITLE}
</div></div></div>
<div class='menu_content'>
<table style='width: 100%'>
<tr>
{NEWSIMAGE}
<td style='width: 100%; vertical-align: top'>
{NEWSBODY}
{EXTENDED}
<br />
</td>
</tr>
</table>
</div>
<div class='menu_content'>
<table class='news_info'>
<tr>
<td style='text-align: center; padding: 3px; padding-bottom: 0px; white-space: nowrap'>
{NEWSICON}
</td>
<td style='width: 100%; padding: 0px; padding-bottom: 0px; padding-left: 2px'>
".LAN_THEME_5."
{NEWSAUTHOR}
 ".LAN_THEME_6."
{NEWSDATE}
</td><td style='text-align: center; padding: 3px; padding-bottom: 0px; white-space: nowrap'>
<img src='".THEME_ABS."images/comments_16.png' style='width: 16px; height: 16px' alt='' />
</td>
<td style='padding: 0px; padding-left: 2px; white-space: nowrap'>
{NEWSCOMMENTS}
</td><td style='padding: 0px; white-space: nowrap'>
{TRACKBACK}
</td><td style='text-align: center; padding: 3px; padding-bottom: 0px; padding-left: 7px; white-space: nowrap'>
{EMAILICON}
{PRINTICON}
{PDFICON}
{ADMINOPTIONS}
</td></tr></table>
<br /></div>";

define('ICONMAIL', 'email_16.png');
define('ICONPRINT', 'print_16.png');
define('ICONSTYLE', 'border: 0px');
define('COMMENTLINK', LAN_THEME_2);
define('COMMENTOFFSTRING', LAN_THEME_1);
define('PRE_EXTENDEDSTRING', '<br /><br />[ ');
define('EXTENDEDSTRING', LAN_THEME_3);
define('POST_EXTENDEDSTRING', ' ]<br />');
define('TRACKBACKSTRING', LAN_THEME_4);
define('TRACKBACKBEFORESTRING', '&nbsp;|&nbsp;');


//	[tablestyle]

function tablestyle($caption, $text, $mode){
	global $style;
	$caption = $caption ? $caption : '&nbsp;';
	if ((isset($mode['style']) && $mode['style'] == 'button_menu') || (isset($mode) && ($mode == 'menus_config'))) {
		$menu = ' buttons';
		$bodybreak = '';
		$but_border = ' button_menu';
	} else {
		$menu = '';
		$bodybreak = '<br />';
		$but_border = '';
	}

	$menu .= ($style && $style != 'default') ? ' non_default' : '';

	echo "<div class='cap_border".$but_border."'>";
	if ($style == 'leftmenu') {
		echo "<div class='left_caption'><div class='bevel'>".$caption."</div></div>";
	}  else if ($style == 'rightmenu') {
		echo "<div class='right_caption'><div class='bevel'>".$caption."</div></div>";
	} else {
		echo "<div class='main_caption'><div class='bevel'>".$caption."</div></div>";
	}
	echo "</div>";
	if ($text != "") {
		echo "<table class='cont'><tr><td class='menu_content ".$menu."'>".$text.$bodybreak."</td></tr></table>";
	}
}


// chatbox post style
$CHATBOXSTYLE = "
<img src='".e_IMAGE_ABS."admin_images/chatbox_16.png' alt='' style='width: 16px; height: 16px; vertical-align: bottom' />
<b>{USERNAME}</b><br />{TIMEDATE}<br />{MESSAGE}<br /><br />";


// comment post style
$sc_style['REPLY']['pre'] = "<tr><td class='forumheader'>";
$sc_style['REPLY']['post'] = "</td>";

$sc_style['SUBJECT']['pre'] = "<td class='forumheader'>";
$sc_style['SUBJECT']['post'] = "</td></tr>";

$sc_style['COMMENTEDIT']['pre'] = "<tr><td class='forumheader' colspan='2' style='text-align: right'>";
$sc_style['COMMENTEDIT']['post'] = "</td></tr>";

$sc_style['JOINED']['post'] = "<br />";

$sc_style['LOCATION']['post'] = "<br />";

$sc_style['RATING']['post'] = "<br /><br />";

$sc_style['COMMENT']['post'] = "<br />";

$COMMENTSTYLE = "<div class='spacer' style='text-align:center'><table class='fborder' style='width: 95%'>
<tr>
<td class='fcaption' colspan='2'>".LAN_THEME_5." {USERNAME} ".LAN_THEME_6." {TIMEDATE}
</td>
</tr>
{REPLY}{SUBJECT}
<tr>
<td style='width: 20%; vertical-align: top' class='forumheader3'>
<div style='text-align: center'>
{AVATAR}
</div>
{LEVEL}<span class='smalltext'>{JOINED}{COMMENTS}{LOCATION}{IPADDRESS}</span>
</td>
<td style='width: 80%; vertical-align: top' class='forumheader3'>
{COMMENT}
{RATING}
{SIGNATURE}
</td>
</tr>
{COMMENTEDIT}
</table>
</div>";


// poll style
$POLLSTYLE = "<img src='".THEME_ABS."images/polls.png' style='width: 10px; height: 14px; vertical-align: bottom' /> {QUESTION}
<br /><br />
{OPTIONS=<img src='".THEME_ABS."images/bullet2.gif' style='width: 10px; height: 10px' /> OPTION<br />BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br /><br />}
<div style='text-align:center' class='smalltext'>{AUTHOR}<br />{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>";

?>