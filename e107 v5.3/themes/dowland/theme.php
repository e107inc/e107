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

$themename = "dowland";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "29/07/02";
$themeinfo = "compatible with e107 v5+";

// [layout]	#############

$layout = "_default";

$HEADER = 
"<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\"><tr><td colspan=\"3\" style=\"text-align:left\">
<span class=\"captiontext\">
{LOGO}
</span>
<div style=\"text-align:left\">
{SITELINKS=flat}
</div>
</td></tr><tr> <td style=\"width:20%; vertical-align: top;\">
{SETSTYLE=leftmenu}
{MENU=1}
</td><td style=\"width:60%; vertical-align: top;\">";

$FOOTER = 
"</td><td style=\"width:20%; vertical-align:top\">
{MENU=2}
</td></tr>
<tr>
<td colspan=\"3\" style=\"text-align:center\">
{SITETAG}
<br />
{SITEDISCLAIMER}
</td>
</tr>
</table>
</div>
";

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

//	[newsstyle]

define(TITLE_POSITION, "caption");	// put news title in caption or body?
define(TITLE_ALIGN,  "left");
define(TITLE_STYLE_START, "");
define(TITLE_STYLE_END, "");
define(ICON_SHOW, TRUE);
define(ICON_POSITION, "body");	 // put icon in caption or body?
define(ICON_ALIGN, "left");
define(TEXT_ALIGN, "justify");
define(EXTENDED_STRING, "Read more ...");
define(SHOW_EMAIL_PRINT, TRUE);	// show email and print icons?
define(INFO_TEXT, "[administrator] | [date and time] | [l]  [count] comment(s) [/l]"); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "body");	// caption or body
define(INFO_ALIGN, "right");
define(URL_TEXT, "Link: ");
define(SOURCE_TEXT, "Story source: ");

// [linkstyle]

define(PRELINK, "<div class=\"spacer\">");
define(POSTLINK, "</div>");
define(LINKSTART, "<span class=\"border\"><span class=\"caption\">");
define(LINKEND, "</span></span>");
define(LINKDISPLAY, 1);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");

$CHATBOXSTYLE = "
<div class=\"spacer\"><b>
{USERNAME}
</b><span class=\"smalltext\"> ... 
{TIMEDATE}
</span><br />
<div class=\"smallblacktext\">
{MESSAGE}
</div></div>---------------------------------------------------------------";

define(CB_STYLE, $CHATBOXSTYLE);

?>