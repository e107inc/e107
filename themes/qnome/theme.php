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

$layout = "Qnome";	// uses layout 6, templates/header6.php
$columns = "1.1.0.1.0"; // menu columns used - uses left and right, doesn't use farleft, farright or center
$maintableclass = "";
$logo_display = TRUE; // TRUE: display logo, FALSE, don't display logo
$logo_align="center"; // Alignment of logo
$tag_display = TRUE; // 0 - not displayed, 1 - displayed
$admin_logo = "2"; // uses logo2.png for admin logo

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