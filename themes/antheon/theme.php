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

$layout = "_antheon";	// uses layout 3, templates/header3.php
$columns = "1.1.0.0.0"; // menu columns used - uses left and right, doesn't use farleft, farright or center
$links_display = 1; // 1 - along top, 2 - in left or right column
$links_align = "left"; // alignment if link displayed across top, not used if links in left or right column
$maintableclass = "";
$logo_display = TRUE; // TRUE: display logo, FALSE, don't display logo
$logo_align="left"; // Alignment of logo
$tag_display = 0; // 0 - not displayed, 1 - displayed

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
define(POSTLINK, "");
define(LINKSTART, "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");
//	[sitelinks]	#############

/*
function sitelinks(){
	$sql = new dbFunc;
$sql -> dbQuery("SELECT * FROM ".MUSER."links WHERE link_category='1' ");
$text .= "<select name=\"NavSelect\" onChange=\"Navigate(this.form)\" class=\"tbox\">
<option value=''>jump to ...</option>\n";
if(ADMIN == TRUE){
	$text .= "<option value=\"admin/admin.php\">admin area</option>\n";
}
	while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_refer) = $sql-> dbFetch()){
		$text .= "<option value=\"".$link_url."\">".$link_name."</option>\n";
	}
	$text .= "</select>&nbsp;&nbsp;";
	echo $text;
}

// [sitelinks2]
function sitelinks2(){
	$sql = new dbFunc;
	$sql -> dbQuery("SELECT * FROM ".MUSER."links WHERE link_category='1' ");
	$text = "<table style=\"width:85%\">";
while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_refer) = $sql-> dbFetch()){
	$text .= "<tr><td style=\"width:2%\"><img src=\"".THEME."images/blue.png\" alt=\"bullet\" /></td>
<td><a href=\"".$link_url."\">".$link_name."</a></td></tr>";
}
$text .= "</table>";
$ns = new table;
$ns -> tablerender("Navigation", $text);
}


*/
?>