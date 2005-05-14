<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107_plugins/recent/admin_recent_config.php
|	Recent Plugin, lisa, 2004
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
+---------------------------------------------------------------+
*/

//include and require several classes
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
require_once(e_PLUGIN."recent/recent_class.php");
$rc = new recent;

//get language file
$lan_file = e_PLUGIN."recent/languages/".e_LANGUAGE.".php";
include(file_exists($lan_file) ? $lan_file : e_PLUGIN."recent/languages/English.php");

//get all sections to use (and reload if new e_recent.php files are added)
$rc -> getSections();

//check preferences from database
$num_rows = $sql -> db_Select("core", "*", "e107_name='recent' ");
$row = $sql -> db_Fetch();

//insert default preferences
if (empty($row['recent'])) {

	$recent_pref = $rc -> getDefaultPrefs();

	$tmp = addslashes(serialize($recent_pref));
	$sql -> db_Insert("core", "'recent', '$tmp' ");
	$sql -> db_Select("core", "*", "e107_name='recent' ");
}

$recent_pref = unserialize(stripslashes($row['e107_value']));
if(!is_array($recent_pref)){ $recent_pref = unserialize($row['e107_value']); }


//update preferences in database
if(isset($_POST['update_menu'])){
	while(list($key, $value) = each($_POST)){
		if($value != RECENT_ADMIN_2){ $recent_pref[$tp->toDB($key)] = $tp->toDB($value); }
	}

	$tmp = addslashes(serialize($recent_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='recent' ");

	$message = RECENT_ADMIN_3;
}

//render message if set
if(isset($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


//define some variables
$stylespacer = "style='border:0; height:20px;'";
$stylehelp = "style='border:0; font-style:italic; color:#0087E5;'";
$td1 = "style='width:20%; white-space:nowrap;'";

//template for non expanding row
$TOPIC_ROW_NOEXPAND = "
<tr>
	<td class='forumheader3' $td1>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>{TOPIC_FIELD}</td>
</tr>
";

//template for expanding row
$TOPIC_ROW = "
<tr>
	<td class='forumheader3' $td1>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>{TOPIC_HEADING}</a>
		<div style='display: none;'>
			<div $stylehelp>{TOPIC_HELP}</div><br />
			{TOPIC_FIELD}
		</div>
	</td>
</tr>
";

//template for spacer row
$TOPIC_ROW_SPACER = "<tr><td $stylespacer colspan='2'></td></tr>";

$TOPIC_TABLE_START = "
<div style='text-align:center;'>
".$rs -> form_open("post", e_SELF, "menu_conf_form", "", "enctype='multipart/form-data'")."
<table style='".ADMIN_WIDTH."' class='fborder'>";

$TOPIC_TABLE_END = "
</table>
</form>
</div>";




$text = $TOPIC_TABLE_START;

//show sections
$TOPIC_TOPIC = RECENT_ADMIN_SECT_1;
$TOPIC_HEADING = RECENT_ADMIN_SECT_2;
$TOPIC_HELP = RECENT_ADMIN_SECT_3;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
$TOPIC_FIELD .= $rc -> parse_headerrow();
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_display($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//open or closed
$TOPIC_TOPIC = RECENT_ADMIN_SECT_4;
$TOPIC_HEADING = RECENT_ADMIN_SECT_5;
$TOPIC_HELP = RECENT_ADMIN_SECT_6;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
$TOPIC_FIELD .= $rc -> parse_headerrow();
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_openclose($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//show author
$TOPIC_TOPIC = RECENT_ADMIN_SECT_7;
$TOPIC_HEADING = RECENT_ADMIN_SECT_8;
$TOPIC_HELP = RECENT_ADMIN_SECT_9;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
$TOPIC_FIELD .= $rc -> parse_headerrow();
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_author($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//show category
$TOPIC_TOPIC = RECENT_ADMIN_SECT_10;
$TOPIC_HEADING = RECENT_ADMIN_SECT_11;
$TOPIC_HELP = RECENT_ADMIN_SECT_12;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
$TOPIC_FIELD .= $rc -> parse_headerrow();
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_category($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//show date
$TOPIC_TOPIC = RECENT_ADMIN_SECT_13;
$TOPIC_HEADING = RECENT_ADMIN_SECT_14;
$TOPIC_HELP = RECENT_ADMIN_SECT_15;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
$TOPIC_FIELD .= $rc -> parse_headerrow();
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_date($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//amount items
$maxitems_amount = "15";
$TOPIC_TOPIC = RECENT_ADMIN_SECT_16;
$TOPIC_HEADING = RECENT_ADMIN_SECT_17;
$TOPIC_HELP = RECENT_ADMIN_SECT_18;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
$TOPIC_FIELD .= $rc -> parse_headerrow();
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_amount($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//order items
$max = count($sections);
$TOPIC_TOPIC = RECENT_ADMIN_SECT_19;
$TOPIC_HEADING = RECENT_ADMIN_SECT_20;
$TOPIC_HELP = RECENT_ADMIN_SECT_21;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_order($sections[$i], $titles[$i], $max);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//icon
$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
$iconlist = $fl->get_files(e_PLUGIN."recent/images/","",$rejectlist);
$TOPIC_TOPIC = RECENT_ADMIN_SECT_22;
$TOPIC_HEADING = RECENT_ADMIN_SECT_23;
$TOPIC_HELP = RECENT_ADMIN_SECT_24;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_icon($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//caption
$TOPIC_TOPIC = RECENT_ADMIN_SECT_25;
$TOPIC_HEADING = RECENT_ADMIN_SECT_26;
$TOPIC_HELP = RECENT_ADMIN_SECT_27;
$TOPIC_FIELD = "
<table style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0'>";
for($i=0;$i<count($sections);$i++){
	$TOPIC_FIELD .= $rc -> parse_caption($sections[$i], $titles[$i]);
}						
$TOPIC_FIELD .= "
</table>
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);


$text .= $TOPIC_ROW_SPACER;


$text .= $rc -> parse_headerrow_title(RECENT_ADMIN_MENU_1);

//menu preference : caption
$TOPIC_TOPIC = RECENT_ADMIN_MENU_2;
$TOPIC_HEADING = RECENT_ADMIN_MENU_3;
$TOPIC_HELP = RECENT_ADMIN_MENU_4;
$TOPIC_FIELD = $rs -> form_text("menu_caption", "30", $recent_pref['menu_caption'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//menu preference : style icon
$TOPIC_TOPIC = RECENT_ADMIN_MENU_5;
$TOPIC_HEADING = RECENT_ADMIN_MENU_6;
$TOPIC_HELP = RECENT_ADMIN_MENU_7;
//$TOPIC_FIELD = $rs -> form_checkbox("style_icon", 1, $recent_pref['style_icon']);
$TOPIC_FIELD = "
	".$rs -> form_radio("menu_style_icon", "1", ($recent_pref['menu_style_icon'] ? "1" : "0"), "", "").RECENT_ADMIN_7."
	".$rs -> form_radio("menu_style_icon", "0", ($recent_pref['menu_style_icon'] ? "0" : "1"), "", "").RECENT_ADMIN_8."
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//menu preference : amount chars
$TOPIC_TOPIC = RECENT_ADMIN_MENU_8;
$TOPIC_HEADING = RECENT_ADMIN_MENU_9;
$TOPIC_HELP = RECENT_ADMIN_MENU_10;
$TOPIC_FIELD = $rs -> form_text("menu_char_heading", "30", $recent_pref['menu_char_heading'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//menu preference : postfix
$TOPIC_TOPIC = RECENT_ADMIN_MENU_11;
$TOPIC_HEADING = RECENT_ADMIN_MENU_12;
$TOPIC_HELP = RECENT_ADMIN_MENU_13;
$TOPIC_FIELD = $rs -> form_text("menu_char_postfix", "30", $recent_pref['menu_char_postfix'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//menu preference : date
$TOPIC_TOPIC = RECENT_ADMIN_MENU_14;
$TOPIC_HEADING = RECENT_ADMIN_MENU_15;
$TOPIC_HELP = RECENT_ADMIN_MENU_16;
$TOPIC_FIELD = $rs -> form_text("menu_datestyle", "30", $recent_pref['menu_datestyle'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//menu preference : date today
$TOPIC_TOPIC = RECENT_ADMIN_MENU_17;
$TOPIC_HEADING = RECENT_ADMIN_MENU_18;
$TOPIC_HELP = RECENT_ADMIN_MENU_19;
$TOPIC_FIELD = $rs -> form_text("menu_datestyletoday", "30", $recent_pref['menu_datestyletoday'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//menu preference : show empty
$TOPIC_TOPIC = RECENT_ADMIN_MENU_20;
$TOPIC_HEADING = RECENT_ADMIN_MENU_21;
$TOPIC_HELP = RECENT_ADMIN_MENU_22;
$TOPIC_FIELD = "
	".$rs -> form_radio("menu_showempty", "1", ($recent_pref['menu_showempty'] ? "1" : "0"), "", "").RECENT_ADMIN_7."
	".$rs -> form_radio("menu_showempty", "0", ($recent_pref['menu_showempty'] ? "0" : "1"), "", "").RECENT_ADMIN_8."
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);



$text .= $TOPIC_ROW_SPACER;

$text .= $rc -> parse_headerrow_title(RECENT_ADMIN_PAGE_1);

//page preference : caption
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_2;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_3;
$TOPIC_HELP = RECENT_ADMIN_PAGE_4;
$TOPIC_FIELD = $rs -> form_text("page_caption", "30", $recent_pref['page_caption'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : style icon
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_5;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_6;
$TOPIC_HELP = RECENT_ADMIN_PAGE_7;
$TOPIC_FIELD = "
	".$rs -> form_radio("page_style_icon", "1", ($recent_pref['page_style_icon'] ? "1" : "0"), "", "").RECENT_ADMIN_7."
	".$rs -> form_radio("page_style_icon", "0", ($recent_pref['page_style_icon'] ? "0" : "1"), "", "").RECENT_ADMIN_8."
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : amount chars
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_8;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_9;
$TOPIC_HELP = RECENT_ADMIN_PAGE_10;
$TOPIC_FIELD = $rs -> form_text("page_char_heading", "30", $recent_pref['page_char_heading'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : postfix
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_11;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_12;
$TOPIC_HELP = RECENT_ADMIN_PAGE_13;
$TOPIC_FIELD = $rs -> form_text("page_char_postfix", "30", $recent_pref['page_char_postfix'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : date
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_14;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_15;
$TOPIC_HELP = RECENT_ADMIN_PAGE_16;
$TOPIC_FIELD = $rs -> form_text("page_datestyle", "30", $recent_pref['page_datestyle'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : date today
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_17;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_18;
$TOPIC_HELP = RECENT_ADMIN_PAGE_19;
$TOPIC_FIELD = $rs -> form_text("page_datestyletoday", "30", $recent_pref['page_datestyletoday'], "50", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : show empty
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_26;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_27;
$TOPIC_HELP = RECENT_ADMIN_PAGE_28;
$TOPIC_FIELD = "
	".$rs -> form_radio("page_showempty", "1", ($recent_pref['page_showempty'] ? "1" : "0"), "", "").RECENT_ADMIN_7."
	".$rs -> form_radio("page_showempty", "0", ($recent_pref['page_showempty'] ? "0" : "1"), "", "").RECENT_ADMIN_8."
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : colomn
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_20;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_21;
$TOPIC_HELP = RECENT_ADMIN_PAGE_22;
$TOPIC_FIELD = "
	".$rs -> form_select_open("page_colomn");
	for($a=1; $a<=count($sections); $a++){
		$TOPIC_FIELD .= ($recent_pref['page_colomn'] == $a ? $rs -> form_option($a, 1) : $rs -> form_option($a, 0));
	}
	$TOPIC_FIELD .= $rs -> form_select_close()."					
";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

//page preference : welcome text
$TOPIC_TOPIC = RECENT_ADMIN_PAGE_23;
$TOPIC_HEADING = RECENT_ADMIN_PAGE_24;
$TOPIC_HELP = RECENT_ADMIN_PAGE_25;
$TOPIC_FIELD = $rs -> form_textarea("page_welcometext", "50", "5", $recent_pref['page_welcometext'], "", "tbox");
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

$text .= $TOPIC_ROW_SPACER;


//submit
$TOPIC_TOPIC = RECENT_ADMIN_11;
$TOPIC_FIELD = $rs -> form_button("submit", update_menu, RECENT_ADMIN_2);
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND);

$text .= $TOPIC_TABLE_END;

$ns -> tablerender(RECENT_ADMIN_1, $text);

require_once(e_ADMIN."footer.php");

?>
