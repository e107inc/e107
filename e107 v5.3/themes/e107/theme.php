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

$layout = "_default";
$admin_logo = "1";

$HEADER = 
"<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\"><tr><td colspan=\"3\" style=\"text-align:left\">
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
</div>
<div style=\"text-align:center\">
<table style=\"width:60%\">
<tr>
<td style=\"width:33%; vertical-align:top\">
{MENU=3}
</td>
<td style=\"width:33%; vertical-align:top\">
{MENU=4}
</td>
<td style=\"width:33%; vertical-align:top\">
{MENU=5}
</td>
</tr>
</table></div>";



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
//define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
//	echo "Style: ".$style.", Mode: ".$mode;
	if($mode == "mode2"){
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption\">".$caption."</div></div>\n";
			if($text != ""){
				echo "\n<div class=\"bodytable\">".$text."</div>\n";
			}
		}else{
			echo "<div class=\"border\"><div class=\"bodytable\">".$text."</div></div><br />\n";
		}
	}else{
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption2\">".$caption."</div></div><div class=\"bodytable2\">".$text."</div><br />\n";
		}else{
			echo "<div class=\"bodytable2\">".$text."</div><br />\n";
		}
	}
}

// [commentstyle]

$COMMENTSTYLE = "
<table style=\"width:95%\">
<tr>
<td style=\"width:20%; vertical-align:top\">
<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> 
<b>
{USERNAME}
</b>
<div class=\"spacer\">
{AVATAR}
</div>
<span class=\"smalltext\">
Comments: 
{COMMENTS}
<br />
Joined: 
{JOINED}
</span>
</td>
<td style=\"width:80%; vertical-align:top\">
<span class=\"smalltext\">
{TIMEDATE}
</span>
<br />
{COMMENT}
<br /><i><span class=\"smalltext\">Signature: 
{SIGNATURE}
</span></i>
<br />
<div class=\"smalltext\">
{ADMINOPTIONS}
</div>
</td>
</tr>
</table>
<br />";

//	[chatbox]

$CHATBOXSTYLE = "
<div class=\"indent\">
<span class=\"smalltext\">...: <b>
{USERNAME}
</b> :...<br />
{TIMEDATE}
</span><br />
<div class=\"mediumtext\" style=\"text-align:right\">
{MESSAGE}
</div></div>";

define(CB_STYLE, $CHATBOXSTYLE);

?>