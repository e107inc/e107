<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	Qnome theme file
|
|	©William Moffett II 2001-2002
|	http://qnome.d2g.com
|	qnome@attbi.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [theme]

$themename = "Qnome";
$themeversion = "3.0";
$themeauthor = "Que";
$themedate = "30/07/02";

// [layout]

$layout = "_default";	// uses layout 6, templates/header6.php


$HEADER = "
<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
<tr>
<td class=\"leftside\"><img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"1\" alt=\"\" /></td>
<td style=\"width:100%; vertical-align: top;\">
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
<tr>
<td class=\"top\" style=\"width:50%; vertical-align: center;\">
<div style=\"text-align:left; vertical-align:bottom\">
<img src=\"".THEME."images/blank.gif\" width=\"25\" height=\"1\" alt=\"\" /><img src=\"".THEME."images/divider.gif\" alt=\"\" /><img src=\"".THEME."images/divider.gif\" alt=\"\" />
<b>
{SITENAME}
</b>
<img src=\"".THEME."images/divider.gif\" alt=\"\" />
{SITETAG}
<img src=\"".THEME."images/divider.gif\" alt=\"\" /></div>
</td>
<td class=\"top\" style=\"width:50%; vertical-align: center;\">
</td>
</tr>
<tr>
<td class=\"content1\" colspan=\"3\"><img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"1\" alt=\"\" style=\"display: block;\"/></td>
</tr>
<tr>
<td colspan=\"3\">
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
<tr>
<td class=\"logoline\"><img src=\"".THEME."images/logo.png\" alt=\"Qnome.2y.net\" style=\"display: block;\"/>
</td>
<td class=\"logoline\" style=\"width:100%; vertical-align: center;\">
<img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"1\" alt=\"\" style=\"display: block;\"/>
</td>
<td class=\"logoline\" style=\"width:70%; vertical-align: center;\">
<img src=\"".THEME."images/logor.png\" width=\"97\" height=\"97\" alt=\"\" />
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td colspan=\"4\" class=\"content2\" style=\"width:70%;\"><span class=\"mytext3\"><img src=\"".THEME."images/blank.gif\" width=\"25\" height=\"1\" alt=\"\" /></span>
<span class=\"mytext2\"><b>Random Quote <img src=\"".THEME."images/divider.gif\" alt=\"\" /></b></span><span class=\"mytext3\">
{CUSTOM=quote}   
</span></td>
</tr>
<tr>
<td colspan=\"3\" class=\"content3\"></td>
</tr>
<tr>
<td colspan=\"3\" class=\"content4\">
<img src=\"".THEME."images/blank.gif\" width=\"1\" height=\"14\" alt=\"\" /></td>
</tr>
</table>
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
<tr>
<td class=\"spacer\"></td> 
<td style=\"vertical-align: top; width:15%; text-align:left\">
<div class=\"defaulttext\">
{SITELINKS=menu}
{MENU=1}
</div>
</td><td class=\"spacer\"></td><td class=\"line-left\"></td><td class=\"spacer\"></td>
<td style=\"vertical-align: top; width:50%;\">";

$FOOTER = "
</td><td class=\"spacer\"></td>
<td style=\"vertical-align: top; width:15%; text-align:right\">
<div class=\"defaulttext\" style=\"text-align:justify\">
{MENU=2}
</div>
</td>
<td class=\"spacer\"></td>
<td style=\"vertical-align: top; width:15%; text-align:right\">
<div class=\"defaulttext\" style=\"text-align:justify\">
{MENU=3} 
<br />
</div>
</td><td class=\"spacer\"></td></tr></table>
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"> 
<tr>
<td>
</td>
<td>
<div style=\"text-align:center\">
{SITEDISCLAIMER}
</div>
</td>
<td>
</td>
</tr>
</table>";















//	[tablestyle]

function tablestyle($caption, $text, $mode=""){
	if($caption != "" && $mode != "nocaption"){
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
           <tr>
           
           <td class=\"head-left\"></td>
            <td class=\"head\"><span class =\"captiontext\">$caption</span></td>
            <td class=\"head-right\"></td>
           </tr>
           <tr>
            <td colspan=\"2\">
              <div style=\"vertical-align: top; text-align:left\">

		

		$text       </div><hr />
         </td>
       </tr>
    </table><br /> ";
	}else{
		echo $text;
	}
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
define(INFO_TEXT, "<hr />Category: [nc]<br />posted by [administrator] on [date and time][l] Comments: [count] [/l]"); // please leave the text inside square brackets intact
define(COMMENT_OFF_TEXT, " turned off for this item");
define(INFO_POSITION, "body");	// caption or body
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

?>