<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	awaken theme file														|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/

// [theme]	#############

$themename = "antheon";
$themeversion = "4.0";
$themeauthor = "jalist";
$themedate = "22.09.02";

// [layout]	#############

$layout = "_antheon";	// uses layout 3, templates/header3.php
$columns = "1.1.0.0.0"; // menu columns used - uses left and right, doesn't use farleft, farright or center
$links_display = 1; // 1 - along top, 2 - in left or right column
$links_align = "left"; // alignment if link displayed across top, not used if links in left or right column
$maintableclass = "";
$logo_display = TRUE; // TRUE: display logo, FALSE, don't display logo
$logo_align="left"; // Alignment of logo
$tag_display = 0; // 0 - not displayed, 1 - displayed

//	[tablestyle]	#############

function tablestyle($caption, $text){
	if($caption != ""){
		echo"<div class=\"border\"><div class=\"caption\">$caption</div>";
		if($text != ""){
			echo "<div class=\"bodytable\">$text</div></div><br />";
		}else{
			echo "</div><br />";
		}
	}else{
		echo "
		<div class=\"border\">
		<div class=\"bodytable\">".
		$text
		."
		</div>
		</div>
		<br />
		";
	}
}

//	[newsstyle]

define(TITLE_POSITION, "caption");	// put news title in caption or body?
define(TITLE_ALIGN,  "left");
define(TITLE_STYLE_START, "<b>");
define(TITLE_STYLE_END, "</b>");
define(ICON_SHOW, TRUE);
define(ICON_POSITION, "caption");	 // put icon in caption or body?
define(ICON_ALIGN, "right");
define(TEXT_ALIGN, "justify");
define(EXTENDED_STRING, "<div class=\"mediumtext\">Read more ...</div>");
define(SHOW_EMAIL_PRINT, TRUE);	// show email and print icons?
define(INFO_TEXT, "<span class=\"smalltext\">[administrator] on [date and time]<br />[l]Comments: [count] [/l]</span>"); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "caption");	// caption or body
define(INFO_ALIGN, "left");
define(URL_TEXT, "Link: ");
define(SOURCE_TEXT, "Story source: ");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[chatbox]

define("CHATBOXSTYLE", TRUE);		// If sefined as TRUE, the settings below will override the default chatbox display settings
$blocked = "-blocked by admin-";
$cb_display1 = "<div class=\"spacer\"><b>NICKNAME: </b><span class=\"smalltext\">[DATE]</span><br />";
$cb_display2 = "<div class=\"smallblacktext\">MESSAGE</div>";
$cb_display3 = "</div>----------------------------------------";
?>