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
$themeauthor = "SweetAs";
$themedate = "22/12/04";
$themeinfo = "Admin Theme Jayya";

define("THEME_DISCLAIMER", "<br />Admin Theme Jayya<br /><br />");

// [layout]

$layout = "_default";

$HEADER = "
<table cellpadding='0' cellspacing='0' border='0' class='top_section'>
<tr>
<td style='vertical-align: top; padding: 0px 0px 0px 0px'>
{LOGO}
</td>
<td style='vertical-align: bottom; text-align: right; padding: 3px; background-color: #efefef; border-left: 1px solid #3D4251; width: 100%; background-image: url(".THEME."images/computer.jpg); background-repeat: no-repeat'>
<div style='height: 23px'>
{CUSTOM=search}
</div>
{SITELINKS=flat}
</td>
</tr>
</table>

<table cellpadding='0' cellspacing='0' border='0' class='main_section'>
<tr>
<td class='left_menu'>
<table cellpadding='0' cellspacing='0' border='0' style='width:100%;'>
<tr>
<td>
{MENU=1}
<br />
</td></tr></table>
</td>
<td class='default_menu'>
{SETSTYLE=default}
";

$FOOTER = "<br />
<div style='text-align:center'>
{SITEDISCLAIMER}
</div>
</td>

<td class='right_menu'>

	<table cellpadding='0' cellspacing='0' border='0' style='width:100%;'>
	<tr>
	<td>
	{SETSTYLE=rightmenu}
	{MENU=2}
	<br />
	</td></tr></table>

</td>
</tr>
</table>
";

// [linkstyle]

define(PRELINK, "|  ");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, "  |  ");
define(LINKDISPLAY, 1);
define(LINKALIGN, "right");

//        [newsstyle]

	$NEWSSTYLE = "<table cellpadding='0' cellspacing='0' border='0' style='width: 100%'><tr><td class='caption_border'><div class='caption'>
	{NEWSTITLE}
	</div></td></tr>
	<tr><td class='bodytable'>
	{NEWSBODY}
	{EXTENDED}
	<br /></td></tr>
	<tr><td class='bodytable'>
	{NEWSAUTHOR}
	{NEWSDATE}
	{NEWSCOMMENTS}
	{EMAILICON}
	{PRINTICON}
	<br /></td></tr></table>";
	
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
	$bodytable = $mode['style']=='button_menu' ? 'bodytable_buttons' : 'bodytable';
	$bodybreak = $mode['style']=='button_menu' ? '' : '<br />';
	$r_caption_bord_but = $mode['style']=='button_menu' ? ' button_menu' : '';
	if ($caption == 'Select Language') { $image = 'language.png'; } else { $image = 'gears.png'; }
	if ($style == "leftmenu") {
		echo "<table cellpadding='0' cellspacing='0' border='0' style='width:100%;'><tr><td class='left_caption_border'><div class='left_caption'>".$caption."</div></td></tr>";
		if ($text != "") {
			echo "<tr><td class='bodytable' style='background-image: url(".THEME."images/".$image."); background-repeat: no-repeat'>".$text."<br /></td></tr>";
		}
		echo "</table>";
	}  else if ($style == "rightmenu") {
		echo "<table cellpadding='0' cellspacing='0' border='0' style='width: 100%'><tr><td class='right_caption_border".$r_caption_bord_but."'><div class='right_caption'>".$caption."</div></td></tr>";
		if ($text != "") {
			echo "<tr><td class='".$bodytable."'>".$text.$bodybreak."</td></tr>";
		}
		echo "</table>";
	} else {
		echo "<table cellpadding='0' cellspacing='0' border='0' style='width: 100%'><tr><td class='caption_border'><div class='caption'>".$caption."</div></td></tr>";
		if ($text != "") {
			echo "<tr><td class='bodytable'>".$text."<br /></td></tr>";
		}
		echo "</table>";
	}
}

?>
