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

// [linkstyle]

define(PRELINK, "::  ");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, "  ::  ");
define(LINKDISPLAY, 1);
define(LINKALIGN, "right");


//	[tablestyle]

function tablestyle($caption, $text, $mode){
	global $style;
	if ($caption == '') { $caption = '&nbsp;'; }
	if ($style == "leftmenu") {
		echo "<table cellpadding='0' cellspacing='0' border='0' style='width:100%;'><tr><td class='left_caption_border'><div class='left_caption'>".$caption."</div></td></tr>";
		if ($text != "") {
			echo "<tr><td class='bodytable'>".$text."<br /></td></tr>";
		}
		echo "</table>";
	}  else if ($style == "rightmenu") {
		echo "<table cellpadding='0' cellspacing='0' border='0' style='width: 100%'><tr><td class='right_caption_border'><div class='right_caption'>".$caption."</div></td></tr>";
		if ($text != "") {
			echo "<tr><td class='bodytable'>".$text."<br /></td></tr>";
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
