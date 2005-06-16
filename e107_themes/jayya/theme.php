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

// [multilanguage]

@include_once(e_THEME."jayya/languages/".e_LANGUAGE.".php");
@include_once(e_THEME."jayya/languages/English.php");


// [theme]

$themename = "Jayya";
$themeversion = "1.0";
$themeauthor = "";
$themedate = "";
$themeinfo = "";
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
define("THEME_DISCLAIMER", "");
define("IMODE", "lite");

// [dont render core style sheet link]

$no_core_css = TRUE;


// [layout]

$layout = "_default";

$HEADER = "<table class='top_section'>
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
{SITELINKS_ALT=".THEME."images/arrow.png}
</div>

<table class='main_section'>
<tr style='display: none'>
<td style='width: 170px'></td>
<td style='width: *'></td>
<td style='width: 170px'></td>
</tr>

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
";


// [linkstyle]

define('PRELINK', '');
define('POSTLINK', '');
define('LINKSTART', '');
define('LINKEND', '');
define('LINKDISPLAY', 1);
define('LINKALIGN', 'left');


// [newsstyle]

$NEWSSTYLE = "<div class='cap_border'><div class='main_caption'><div class='bevel'>
{STICKY_ICON}{NEWSTITLE}
</div></div></div>
<div class='menu_content'>
{NEWSBODY}
{EXTENDED}
<br /></div>
<div class='menu_content'>
<table class='news_info'>
<tr>
<td style='text-align: center; padding: 3px; padding-bottom: 0px; white-space: nowrap'>
<img src='".THEME."images/postedby_16.png' style='width: 16px; height: 16px' alt='' />
</td>
<td style='width: 100%; padding: 0px; padding-bottom: 0px; padding-left: 2px'>
Posted by 
{NEWSAUTHOR}
 on 
{NEWSDATE}
</td><td style='text-align: center; padding: 3px; padding-bottom: 0px; white-space: nowrap'>
<img src='".THEME."images/comments_16.png' style='width: 16px; height: 16px' alt='' />
</td>
<td style='padding: 0px; padding-left: 2px; white-space: nowrap'>
{NEWSCOMMENTS}
</td><td style='padding: 0px; white-space: nowrap'>
{TRACKBACK}
</td><td style='text-align: center; padding: 3px; padding-bottom: 0px; padding-left: 7px; white-space: nowrap'>
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
</td></tr></table>
<br /></div>";

define('ICONMAIL', 'email_16.png');
define('ICONPRINT', 'print_16.png');
define('ICONSTYLE', 'float: left; border:0');
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

$CHATBOXSTYLE = "
<img src='".e_IMAGE."admin_images/chatbox_16.png' alt='' style='width: 16px; height: 16px; vertical-align: bottom' />
<b>{USERNAME}</b><br />{TIMEDATE}<br />{MESSAGE}<br /><br />";

$COMMENTSTYLE = "
<table style='width: 100%;'>
<tr>
<td style='width: 10%;'>{USERNAME}<br />{TIMEDATE}<br />{AVATAR}<br />{REPLY}</td>
<td style='width: 90%; background-color: #f9f9fd; vertical-align: top; padding: 4px'>
{COMMENT}
</td>
</tr>
</table>
<br />
";

$POLLSTYLE = "<img src='".THEME."images/polls.png' style='width: 10px; height: 14px; vertical-align: bottom' /> {QUESTION}
<br /><br />
{OPTIONS=<img src='".THEME."images/bullet2.gif' style='width: 10px; height: 10px' /> OPTION<br />BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br /><br />}
<div style='text-align:center' class='smalltext'>{AUTHOR}<br />{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>";

?>
