<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	'example' theme file														|
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

$themename = "example";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+<br />Made as an example of using different styles for left/right/center columns";

// [layout]

$layout = "_default";

$HEADER = "
<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
<td style=\"background-color:#E2E2E2; text-align:left\">
{LOGO}
</td></tr>
<tr><td style=\"background-color:#000\"></td></tr>
<tr><td style=\"background-color:#fff\"></td></tr>
<tr><td style=\"background-color:#ccc\">&nbsp;
{SITETAG}
</td></tr>
<tr>
<td style=\"background-color:#000\"></td>
</tr>
</table>
<table style=\"width:100%\" cellspacing=\"10\" cellpadding=\"10\">
<tr> 
<td style=\"width:15%; vertical-align: top;\">
{SETSTYLE=leftmenu}
(SITELINKS=menu}
{MENU=1}
<br />
</td>
{SETSTYLE=default}
<td style=\"width:70%; vertical-align: top\">";

$FOOTER = "
<br />
</td>
<td style=\"width:15%; vertical-align:top\">
{SETSTYLE=rightmenu}
{MENU=2}
</td>
</tr>
</table>
</div>
<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
<td style=\"background-color:#000\"></td>
</tr>
<tr>
<td style=\"background-color:#fff\"></td>
</tr>
<tr>
<td style=\"background-color:#E2E2E2; text-align:center\">
{SITEDISCLAIMER}
<br />
<img src=\"files/images/php-small-trans-light.gif\" alt=\"\" /> <img src=\"button.png\" alt=\"\" /> <img src=\"files/images/poweredbymysql-88.png\" alt=\"\" />
</td>
</tr>
<tr>
<td style=\"background-color:#000\"></td>
</tr>
</table>";


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
define(INFO_TEXT, "<hr />Category: [nc] | posted by [administrator] on [date and time] | [l] Comments: [count] [/l] |"); // please leave the text inside square brackets intact
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