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

$themename = "nordranious";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+<br />Made as an example of using different styles for left/right/center columns";

define("THEME_DISCLAIMER", "<br /><i>nordranious theme by jalist (e107 default)</i>");


// [layout]

$layout = "_default";

$HEADER = "
<div style=\"text-align:center\">
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width: 100%; background-color: #D5D5D5;\">
<tr>
<td colspan=\"4\" style=\"background-color: #D5D5D5; padding: 3px; text-align:left;\">
<table style=\"width:100%\"><tr><td style=\"vertical-align:top; width:2%\"><img src=\"".THEME."images/bullet3.gif\" alt=\"\" style=\"vertical-align:absmiddle\" /></td>
<td style=\"vertical-align:top\"><span class=\"captiontext\">
{SITENAME}
</span><br />
{SITETAG}
</td>
<td style=\"text-align:right; vertical-align:bottom\">
{CUSTOM=search}
{SITELINKS=flat}
</td>
</tr></table>
</td>
</tr>
<tr>
<td style=\"padding: 0px; margin: 0px; width: 150px;\"></td>
<td class=\"bar1 \"><img src=\"themes/shared/generic/blank.gif\" width=\"37\" height=\"37\" alt=\"\" style=\"display: block;\" /></td>
<td class=\"bar2\"><img src=\"themes/shared/generic/blank.gif\" width=\"2\" height=\"37\" alt=\"\" style=\"display: block;\" /></td>
<td class=\"bar2\"><img src=\"themes/shared/generic/blank.gif\" width=\"2\" height=\"37\" alt=\"\" style=\"display: block;\" /></td>
</tr>
<tr>
<td style=\"padding: 0px; margin: 0px; width: 150px; vertical-align:top\"><hr />
{SETSTYLE=leftmenu}
{MENU=1}
<br />
</td>
<td class=\"bar3\"><img src=\"images/blank.gif\" width=\"37\" height=\"2\" alt=\"\" style=\"display: block;\" /></td>
<td style=\"padding: 4px; margin: 0px; width: *; background-color: #EEF4F4; vertical-align:top;\">
{SETSTYLE=default}
";

$FOOTER = "
<br />
<div style=\"text-align:center\">
{SITEDISCLAIMER}
</div>
</td>
<td style=\"width:150px; vertical-align:top; background-color:#EEF4F4\">
{SETSTYLE=rightmenu}
{MENU=2}
</td>
</tr>
</table>
</div>";


//	[newsstyle]

define(TITLE_POSITION, "caption");	// put news title in caption or body?
define(TITLE_ALIGN,  "left");
define(TITLE_STYLE_START, "");
define(TITLE_STYLE_END, "");
define(ICON_SHOW, TRUE);
define(ICON_POSITION, "body");	 // put icon in caption or body?
define(ICON_ALIGN, "left");
define(TEXT_ALIGN, "justify");
define(EXTENDED_STRING, "<div class=\"mediumtext\">Read more ...</div>");
define(SHOW_EMAIL_PRINT, TRUE);	// show email and print icons?
define(INFO_TEXT, "posted by [administrator] on [date and time] ... [l] Comments: [count] [/l] ..."); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "belowcaption");	// caption or body
define(INFO_ALIGN, "center");
define(URL_TEXT, "Link: ");
define(SOURCE_TEXT, "Story source: ");


// [linkstyle]

define(PRELINK, "| ");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, " | ");
define(LINKDISPLAY, 1);
define(LINKALIGN, "right");


//	[tablestyle]

function tablestyle($caption, $text){
	global $style;
//	echo "Mode: ".$style;
	if($style == "leftmenu"){
		if($caption != ""){
			echo "<div class=\"border2\"><div class=\"caption2\">".$caption."</div></div>";
			if($text != ""){
				echo "\n<div class=\"bodytable2\">".$text."</div>";
			}
		}else{
			echo "<div class=\"border2\"><div class=\"bodytable2\">".$text."</div></div><br />";
		}
	}else if($style == "default"){
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption\">".$caption."</div></div><div class=\"bodytable\">".$text."</div><br />";
		}else{
			echo "<div class=\"bodytable\">".$text."</div><br />";
		}
	}else{
		if($caption != ""){
			echo "<div class=\"border3\"><div class=\"caption3\">".$caption."</div></div><div class=\"bodytable3\">".$text."</div><br />";
		}else{
			echo "<div class=\"bodytable3\">".$text."</div><br />";
		}
	}
}

?>