<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	e109 theme file															|
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

$themename = "e109";
$themeversion = "5.0";
$themeauthor = "jalist/CraHan";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+";

// [layout]

$layout = 1;	// uses layout 1, templates/header1.php
$columns = "1.1.0.0.0"; // menu columns used - uses left and right, doesn't use farleft, farright or center
$maintableclass = "border";
$logo_display = TRUE; // TRUE: display logo, FALSE, don't display logo
$logo_align="left"; // Alignment of logo
$tag_display = TRUE; // 0 - not displayed, 1 - displayed
$admin_logo = "2"; // uses logo2.png for admin logo
$leftcolumn = "20%";
$maincolumn = "60%";
$rightcolumn = "20%";

//	[tablestyle]

function tablestyle($caption, $text, $mode=""){
	opentable();
	if($caption != "" && $mode != "nocaption"){
		echo "<div class=\"captiontext\">$caption</div>";
		if($text != ""){
			echo "\n<br />$text";
		}
	}else{
		echo $text;
	}
	closetable();
}

//	[table]

function opentable(){
echo "<div class=\"spacer\">
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" >
<tr>
<td class=\"toplf\"><img src=\"images/blank.gif\" width=\"5\" height=\"5\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"topcr\"><img src=\"images/blank.gif\" width=\"1\" height=\"5\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"toprt\"><img src=\"images/blank.gif\" width=\"5\" height=\"5\" alt=\"\" style=\"display: block;\"/></td>
</tr>
<tr>
<td class=\"bodylt\"><img src=\"images/blank.gif\" width=\"5\" height=\"1\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"bodycr\">";
}

function closetable(){
echo "</td>
<td class=\"bodyrt\"><img src=\"images/blank.gif\" width=\"5\" height=\"1\" alt=\"\" style=\"display: block;\"/></td>
</tr>
<tr>
<td class=\"bottomlt\"><img src=\"images/blank.gif\" width=\"5\" height=\"5\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"bottomcr\"><img src=\"images/blank.gif\" width=\"1\" height=\"5\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"bottomrt\"><img src=\"images/blank.gif\" width=\"5\" height=\"5\" alt=\"\" style=\"display: block;\"/></td>
</tr>
</table>
</div>";
}

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
define(INFO_TEXT, "<hr />Category: [nc] | posted by [administrator] on [date and time] | [l] Comments: [count] [/l] | "); // please leave the text inside square brackets intact
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
?>