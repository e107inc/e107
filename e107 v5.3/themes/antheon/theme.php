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

$layout = "_default";

$HEADER = "
<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\">
<tr>
<td style=\"background-color: #EFEFEF\">
&nbsp;&nbsp;
{LOGO}
<br />
</td>
<td style=\"background-color: #EFEFEF; text-align:right\">
<img src=\"button.png\" alt=\"\" />&nbsp;&nbsp;
</td>
</tr>
</table>
<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\">
<tr>
<td class=\"caption2\">
{SITETAG}
</td>
</tr>
</table>
<table style=\"width:100%\" cellpadding=\"2\" cellspacing=\"0\">
<tr>
<td style=\"background-color: #A8A8A8\">
{CUSTOM=login}
</td>
<td style=\"text-align:right; background-color: #A8A8A8\">
{CUSTOM=search}
</td>
</tr>
<tr>
<td colspan=\"2\" style=\"background-color: #000\">
</td>
</tr>
<tr>
<td colspan=\"2\" style=\"background-color: #D4D4D4\">
</td>
</tr>
<tr>
<td class=\"smalltext\" style=\"background-color: #A8A8A8\">
{SITELINKS=flat}
</td>
<td style=\"background-color: #A8A8A8; text-align:right\">
&nbsp;
</td>
</tr>
<tr>
<td colspan=\"2\" style=\"background-color: #000\">
</td>
</tr>
</table>
<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\" cellpadding=\"3\">
<tr> 
<td style=\"width:20%; vertical-align: top;\">
{MENU=1}
</td>
<td style=\"vertical-align: top;width:60%\">";

$FOOTER = "<br />
</td>
<td style=\"width:20%; vertical-align:top\">
{MENU=2}
</td>
</tr>
</table>
<br />
<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\">
<tr>
<td class=\"caption2\">
<div style=\"text-align:center\">
{SITEDISCLAIMER}
</div>
</td>
</tr>
</table>
<br />
</div>";

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
define(POSTLINK, "&nbsp;<img src=\"themes/antheon/images/bullet3.gif\" alt=\"\" />&nbsp;");
define(LINKSTART, "&nbsp;<img src=\"themes/antheon/images/bullet3.gif\" alt=\"\" />&nbsp; ");
define(LINKEND, "");
define(LINKDISPLAY, 1);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[chatbox]

$CHATBOXSTYLE = "
<div class=\"spacer\"><b>
{USERNAME}
</b><span class=\"smalltext\">
{TIMEDATE}
</span><br />
<div class=\"smallblacktext\">
{MESSAGE}
</div></div>----------------------------------------";

define(CB_STYLE, $CHATBOXSTYLE);
?>