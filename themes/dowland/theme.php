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

$layout = 1;	// uses layout 1, templates/header1.php
$columns = "1.1.0.0.0"; // menu columns used - uses left and right, doesn't use farleft, farright or center
$maintableclass = "";
$logo_display = TRUE; // TRUE: display logo, FALSE, don't display logo
$logo_align="left"; // Alignment of logo
$tag_display = FALSE; // 0 - not displayed, 1 - displayed
$admin_logo = "2"; // uses logo2.png for admin logo
$leftcolumn = "20%";
$maincolumn = "60%";
$rightcolumn = "20%";

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

//	[newsstyle]	#############

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


/*
//	[sitelinks]	#############

function sitelinks(){
echo "<div class=\"spacer\">";
$sql = new dbFunc;
$sql -> dbQuery("SELECT * FROM ".MUSER."links WHERE link_category='1' ORDER BY link_order");

if($_COOKIE['admin']){
	echo "<span class=\"border\">
<span class=\"caption\">
<a href=\"admin/admin.php\">Admin Area</a>
</span></span>";
}
while(list($link_id_, $link_name_, $link_url_) = $sql-> dbFetch()){
	echo "<span class=\"border\">
<span class=\"caption\">
<a href=\"".$link_url_."\">".$link_name_."</a>
</span></span>";
}
echo "</div>";
}
*/
?>