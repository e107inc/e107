<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	default theme file														|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/

// [theme]

$themename = "e107";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+";

// [layout]

$layout = 5;	// uses layout 5, templates/header5.php
$columns = "1.1.0.0.0"; // menu columns used - uses left and right, doesn't use farleft, farright or center
$maintableclass = "";
$logo_display = TRUE; // TRUE: display logo, FALSE, don't display logo
$logo_align="left"; // Alignment of logo
$tag_display = TRUE; // 0 - not displayed, 1 - displayed
$admin_logo = "1"; // uses logo.png for admin logo
$leftcolumn = "15%";
$maincolumn = "70%";
$rightcolumn = "15%";

//	[newsstyle]

define(TITLE_POSITION, "caption");	// put news title in caption or body?
define(TITLE_ALIGN,  "left");
define(TITLE_STYLE_START, "<b>");
define(TITLE_STYLE_END, "</b>");
define(ICON_SHOW, TRUE);
define(ICON_POSITION, "body");	 // put icon in caption or body?
define(ICON_ALIGN, "left");
define(TEXT_ALIGN, "justify");
define(EXTENDED_STRING, "<div class=\"mediumtext\">Read more ...</div>");
define(SHOW_EMAIL_PRINT, TRUE);	// show email and print icons?
define(INFO_TEXT, "<hr />Category: [nc] | posted by [administrator] on [date and time] <br /> [l] Comments: [count] [/l]"); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "body");	// caption or body
define(INFO_ALIGN, "center");
define(URL_TEXT, "Link: ");
define(SOURCE_TEXT, "Story source: ");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
	if($mode == "default" || $mode == ""){
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption\">".$caption."</div></div>\n";
			if($text != ""){
				echo "\n<div class=\"bodytable\">".$text."</div>\n";
			}
		}else{
			echo "<div class=\"border\"><div class=\"bodytable\">".$text."</div></div><br />\n";
		}
	}else if($mode == "mode2"){
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption2\">".$caption."</div></div><div class=\"bodytable2\">".$text."</div><br />\n";
		}else{
			echo "<div class=\"bodytable2\">".$text."</div><br />\n";
		}
	}else{
		echo "<div class=\"border\"><div class=\"bodytable\">".$text."</div></div><br />\n";
	}
}

?>