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

$themename = "smacks";
$themeversion = "2.0";
$themeauthor = "jalist";
$themedate = "04/06/2003";
$themeinfo = "compatible with e107 v5+";

// [layout]

$layout = "_default";

$logo = "themes/smacks/images/logo.png";

$HEADER = opentable()."
<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"10\" cellpadding=\"0\">
<tr>
<td style=\"width: 80%; vertical-align:top\">
<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
<td style=\"width: 10%; vertical-align:top\">
<img src='".e_BASE.$logo."' alt='' />
<br />
{SITETAG}
</td>
<td style=\"width: 1%; vertical-align:top\"></td>
<td class=\"topbar\" style=\"width: 90%; vertical-align:top\">
<table style=\"width:100%\" cellspacing=\"5\" cellpadding=\"0\">
<tr>
<td style=\"width: 33%; vertical-align:top\">
{MENU=2}
</td>
<td style=\"width: 34%; vertical-align:top\">
{MENU=3}
</td>
<td style=\"width: 33%; vertical-align:top\">
{MENU=4}
</td></tr></table>
</td>
</tr>
</table>
<br />
{SETSTYLE=default}
";


$FOOTER = "
</td><td style=\"width:20%; vertical-align:top\">

{SETSTYLE=rightmenu}
{SITELINKS}
{MENU=1}

</td>
</tr>
</table>
</div>".
closetable()."

<div style=\"text-align:center\">
{SITEDISCLAIMER}
<br />
<img src=\"files/images/php-small-trans-light.gif\" alt=\"\" /> <img src=\"button.png\" alt=\"\" /> <img src=\"files/images/poweredbymysql-88.png\" alt=\"\" />
</div>";

//	[newsstyle]

define(TITLE_POSITION, "caption");	// put news title in caption or body?
define(TITLE_ALIGN,  "left");
define(TITLE_STYLE_START, "<b>");
define(TITLE_STYLE_END, "</b>");
define(ICON_SHOW, TRUE);
define(ICON_POSITION, "body");	 // put icon in caption or body?
define(ICON_ALIGN, "right");
define(TEXT_ALIGN, "justify");
define(EXTENDED_STRING, "<div class=\"mediumtext\">Read more ...</div>");
define(SHOW_EMAIL_PRINT, FALSE);	// show email and print icons?
define(INFO_TEXT, "<span class=\"smalltext\">posted by [administrator] on [date and time] [l] Comments: [count] [/l]"); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "caption");	// caption or body
define(INFO_ALIGN, "left");
define(URL_TEXT, "Link: ");
define(SOURCE_TEXT, "Story source: ");


//	[tablestyle]

function tablestyle($caption, $text, $mode=""){
//	opentable();
	global $style;

//echo $style." - ".$mode;

	if($style == "default"){
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption\">".$caption."</div></div>\n";
			if($text != ""){
				echo "\n<div class=\"bodytable\">".$text."</div>\n";
			}
		}else{
			echo "<div class=\"border\"><div class=\"bodytable\">".$text."</div></div><br />\n";
		}

	}else if($style == "customtable"){
		echo "<img src=\"".THEME."/images/bullet2.gif\">&nbsp;<b><span class=\"captiontext\">".$caption."</span></b><hr />".$text;
	}else{
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption2\">".$caption."</div><div class=\"bodytable2\">".$text."</div></div><br />\n";
		}else{
			echo "<div class=\"bodytable2\">".$text."</div><br />\n";
		}
	}
//	closetable();
}

//	[table]

function opentable(){
return "<div class=\"spacer\">
<table style=\"width:90%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" >
<tr>
<td class=\"toplf\"><img src=\"themes\shared\generic\blank.gif\" width=\"15\" height=\"15\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"topcr\"><img src=\"themes\shared\generic\blank.gif\" width=\"1\" height=\"15\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"toprt\"><img src=\"themes\shared\generic\blank.gif\" width=\"15\" height=\"15\" alt=\"\" style=\"display: block;\"/></td>
</tr>
<tr>
<td class=\"bodylt\"><img src=\"themes\shared\generic\blank.gif\" width=\"15\" height=\"1\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"bodycr\">";
}

function closetable(){
return "</td>
<td class=\"bodyrt\"><img src=\"themes\shared\generic\blank.gif\" width=\"15\" height=\"1\" alt=\"\" style=\"display: block;\"/></td>
</tr>
<tr>
<td class=\"bottomlt\"><img src=\"themes\shared\generic\blank.gif\" width=\"15\" height=\"15\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"bottomcr\"><img src=\"themes\shared\generic\blank.gif\" width=\"1\" height=\"15\" alt=\"\" style=\"display: block;\"/></td>
<td class=\"bottomrt\"><img src=\"themes\shared\generic\blank.gif\" width=\"15\" height=\"15\" alt=\"\" style=\"display: block;\"/></td>
</tr>
</table>
</div>";
}

// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");

?>