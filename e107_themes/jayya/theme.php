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

$themename = "Jayya";
$themeversion = "1.0";
$themeauthor = "e107";
$themedate = "22/12/04";
$themeinfo = "Jayya";

define("THEME_DISCLAIMER", "");


// [output js nav css in <head>]

function theme_head() {
	return "<link rel='stylesheet' href='".e_FILE."nav_menu.css' />\n";
}


// [dont render core style sheet link]

$no_core_css = TRUE;


// [layout]

$layout = "_default";

$HEADER = "<table class='top_section'>
<tr>
<td class='top_section_left' style='width: 170px'>
<img src='".THEME."images/logo.png' style='width: 170px; height: 68px; display: block;' alt='' />
</td>
<td class='top_section_mid'>
{BANNER}
</td>

<td class='top_section_right' style='padding: 0px; white-space: nowrap; width: 170px'>
{CUSTOM=search++default}
</td>
</tr>
</table>

<div>
{SITELINKS_ALT}
</div>

<table class='main_section'>
<tr>
<td class='left_menu'>
<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
<tr>
<td>
{SETSTYLE=leftmenu}
{MENU=1}
<br />
</td></tr></table>
</td>
<td class='default_menu'>
{SETSTYLE=default}
";

$FOOTER = "<br />
</td>

<td class='right_menu'>
<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
<tr>
<td>
{SETSTYLE=rightmenu}
{MENU=2}
<br />
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

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, "");
define(LINKDISPLAY, 1);
define(LINKALIGN, "right");


// [newsstyle]

$NEWSSTYLE = "<div class='cap_border'><div class='main_caption'>
{NEWSTITLE}
</div></div>
<div class='menu_content'>
{NEWSBODY}
{EXTENDED}
<br /></div>
<div class='menu_content'>
{NEWSAUTHOR}
{NEWSDATE}
{NEWSCOMMENTS}
{EMAILICON}
{PRINTICON}
<br /></div>";
	
define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest...");
define("POST_EXTENDEDSTRING", " ]<br />");


//	[tablestyle]

function tablestyle($caption, $text, $mode){
	global $style;
	if ($caption == '') { $caption = '&nbsp;'; }
	$bodytable = $mode['style']=='button_menu' ? 'menu_content_buttons' : 'menu_content';
	$bodybreak = $mode['style']=='button_menu' ? '' : '<br />';
	$r_caption_bord_but = $mode['style']=='button_menu' ? ' button_menu' : '';
	if ($caption == 'Select Language') { $image = 'language.png'; } else { $image = 'gears.png'; }
	if ($style == "leftmenu") {
		echo "<div class='cap_border'><div class='left_caption'>".$caption."</div></div>";
		if ($text != "") {
			echo "<div class='menu_content' style='background-image: url(".THEME."images/".$image."); background-repeat: no-repeat'>
			".$text."<br /></div>";
		}
	}  else if ($style == "rightmenu") {
		echo "<div class='cap_border".$r_caption_bord_but."'>
		<div class='right_caption'>".$caption."</div>
		</div>";
		if ($text != "") {
			echo "<div class='".$bodytable."'>".$text.$bodybreak."</div>";
		}
	} else {
		echo "<div class='cap_border'>
		<div class='main_caption'>".$caption."</div>
		</div>";
		if ($text != "") {
			echo "<div class='menu_content'>".$text."<br /></div>";
		}
	}
}

?>