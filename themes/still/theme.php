<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	still theme file																|
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

$themename = "still";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v4+";

// [layout]	#############

$layout = "_default";

$HEADER = 
"<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\"><tr><td colspan=\"3\" style=\"text-align:center\">
{LOGO}
<br />
{SITETAG}
</td></tr><tr> <td style=\"width:15%; vertical-align: top;\">
{SETSTYLE=leftmenu}
{SITELINKS=menu}
{MENU=1}
</td><td style=\"width:70%; vertical-align: top;\">";

$FOOTER = 
"</td><td style=\"width:15%; vertical-align:top\">
{MENU=2}
</td></tr>
<tr>
<td colspan=\"3\" style=\"text-align:center\">
{SITEDISCLAIMER}
</td>
</tr>
</table>
</div>";

//	[tablestyle]	#############

function tablestyle($caption, $text){
	if($caption != ""){
		echo"
<div class=\"border\">
<div class=\"caption\">
$caption
</div>";
if($text != ""){
	echo "<div class=\"bodytable\">
$text
</div>
</div>
<br />";
}else{
	echo "</div>
<br />";
}
	}else{
		echo "
<div class=\"border\">
<div class=\"bodytable\">".
$text
."
</div>
</div>
<br />";
	}
}

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
define(INFO_TEXT, "[administrator]<br />[date and time]<br />[l][count] comment(s) [/l]<br />"); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "body");	// caption or body
define(INFO_ALIGN, "right");
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