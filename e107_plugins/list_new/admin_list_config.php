<?php
/*
+---------------------------------------------------------------+
|       e107 website system
|
|       ©Steve Dunstan 2001-2002
|       http://e107.org
|       jalist@e107.org
|
|       Released under the terms and conditions of the
|       GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/list_new/admin_list_config.php,v $
|		$Revision: 1.1 $
|		$Date: 2005-06-17 13:35:39 $
|		$Author: lisa_ $
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
$listplugindir = e_PLUGIN."list_new/";
require_once($listplugindir."list_class.php");
$rc = new listclass;

//get language file
$lan_file = $listplugindir."languages/".e_LANGUAGE.".php";
include(file_exists($lan_file) ? $lan_file : $listplugindir."languages/English.php");

//get all sections to use (and reload if new e_list.php files are added)
$rc -> getSections();

//update preferences in database
if(isset($_POST['update_menu'])){
	while(list($key, $value) = each($_POST)){
		if($value != LIST_ADMIN_2){ $list_pref[$tp->toDB($key)] = $tp->toDB($value); }
	}

	$tmp = $eArrayStorage->WriteArray($list_pref);
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='list' ");

	$message = LIST_ADMIN_3;
}

//check preferences from database
$num_rows = $sql -> db_Select("core", "*", "e107_name='list' ");
$row = $sql -> db_Fetch();

//insert default preferences
if (empty($row['list'])) {

	$list_pref = $rc -> getDefaultPrefs();
	$tmp = $eArrayStorage->WriteArray($list_pref);

	$sql -> db_Insert("core", "'list', '$tmp' ");
	$sql -> db_Select("core", "*", "e107_name='list' ");
}

$list_pref = $eArrayStorage->ReadArray($row['e107_value']);



//render message if set
if(isset($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


//define some variables
$stylespacer = "style='border:0; height:20px;'";
$styletable = "style='width:90%; border:1px solid #444; border-collapse:collapse;' cellpadding='0' cellspacing='0' ";

//template for non expanding row
$TOPIC_ROW_NOEXPAND = "
<tr>
	<td class='forumheader3' style='width:20%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>{TOPIC_FIELD}</td>
</tr>
";

//template for expanding row
$TOPIC_ROW = "
<tr>
	<td class='forumheader3' style='width:20%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>{TOPIC_HEADING}</a>
		<div style='display: none;'>
			<div class='smalltext'>{TOPIC_HELP}</div><br />
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

//$TOPIC_TABLE_END = "
//</table>
//</form>
//</div>";
$TOPIC_TABLE_END = pref_submit()."</table></div>";


$text = "
<script type=\"text/javascript\">
<!--
var hideid=\"recent_page\";
function showhideit(showid){
	if (hideid!=showid){
		show=document.getElementById(showid).style;
		hide=document.getElementById(hideid).style;
		show.display=\"\";
		hide.display=\"none\";
		hideid = showid;
	}
}
//-->
</script>";

$text .= "
<div style='text-align:center'>
".$rs -> form_open("post", e_SELF, "menu_conf_form", "", "enctype='multipart/form-data'")."\n";

$text .= parse_menu_options("recent_menu");
$text .= parse_menu_options("new_menu");
$text .= parse_page_options("recent_page");
$text .= parse_page_options("new_page");

$text .= "
</form>
</div>";

$ns -> tablerender(LIST_ADMIN_1, $text);


function parse_global_options($type){
	global $rc, $list_pref, $rs, $sections, $titles, $iconlist, $TOPIC_ROW, $TOPIC_ROW_SPACER, $TOPIC_TABLE_END;

	//show sections
	$TOPIC_TOPIC = LIST_ADMIN_SECT_1;
	$TOPIC_HEADING = LIST_ADMIN_SECT_2;
	$TOPIC_HELP = LIST_ADMIN_SECT_3;
	$TOPIC_FIELD = "";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= $rs -> form_checkbox($sections[$i]."_".$type."_display", 1, ($list_pref[$sections[$i]."_".$type."_display"]) ? "1" : "0")." ".$titles[$i]."<br />";
	}
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//open or closed
	$TOPIC_TOPIC = LIST_ADMIN_SECT_4;
	$TOPIC_HEADING = LIST_ADMIN_SECT_5;
	$TOPIC_HELP = LIST_ADMIN_SECT_6;
	$TOPIC_FIELD = "";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= $rs -> form_checkbox($sections[$i]."_".$type."_open", 1, (isset($list_pref[$sections[$i]."_".$type."_open"]) ? "1" : "0"))." ".$titles[$i]."<br />";
	}
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//author
	$TOPIC_TOPIC = LIST_ADMIN_SECT_7;
	$TOPIC_HEADING = LIST_ADMIN_SECT_8;
	$TOPIC_HELP = LIST_ADMIN_SECT_9;
	$TOPIC_FIELD = "";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= $rs -> form_checkbox($sections[$i]."_".$type."_author", 1, (isset($list_pref[$sections[$i]."_".$type."_author"]) ? "1" : "0"))." ".$titles[$i]."<br />";
	}
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//category
	$TOPIC_TOPIC = LIST_ADMIN_SECT_10;
	$TOPIC_HEADING = LIST_ADMIN_SECT_11;
	$TOPIC_HELP = LIST_ADMIN_SECT_12;
	$TOPIC_FIELD = "";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= $rs -> form_checkbox($sections[$i]."_".$type."_category", 1, (isset($list_pref[$sections[$i]."_".$type."_category"]) ? "1" : "0"))." ".$titles[$i]."<br />";
	}
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//date
	$TOPIC_TOPIC = LIST_ADMIN_SECT_13;
	$TOPIC_HEADING = LIST_ADMIN_SECT_14;
	$TOPIC_HELP = LIST_ADMIN_SECT_15;
	$TOPIC_FIELD = "";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= $rs -> form_checkbox($sections[$i]."_".$type."_date", 1, (isset($list_pref[$sections[$i]."_".$type."_date"]) ? "1" : "0"))." ".$titles[$i]."<br />";
	}
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//icon
	$TOPIC_TOPIC = LIST_ADMIN_SECT_22;
	$TOPIC_HEADING = LIST_ADMIN_SECT_23;
	$TOPIC_HELP = LIST_ADMIN_SECT_24;
	$TOPIC_FIELD = "<table $styletable>";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= "
		<tr>
		<td class='forumheader3' style='width:10%; white-space:nowrap; vertical-align:top;'>".$titles[$i]."</td>
		<td class='forumheader3'>
			".$rs -> form_text($sections[$i]."_".$type."_icon", 15, $list_pref[$sections[$i]."_".$type."_icon"], 100)."
			<input class='button' type='button' style='cursor:hand' size='30' value='".LIST_ADMIN_12."' onClick=\"expandit('div_".$sections[$i]."_".$type."_icon')\" />
			<div id='div_".$sections[$i]."_".$type."_icon' style='display:none;'>";
			foreach($iconlist as $icon){
				$TOPIC_FIELD .= "<a href=\"javascript:insertext('".$icon['fname']."','".$sections[$i]."_".$type."_icon','div_".$sections[$i]."_".$type."_icon')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
			}
			$TOPIC_FIELD .= "</div>
		</td>
		</tr>";
	}
	$TOPIC_FIELD .= "</table>";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//amount
	$maxitems_amount = "15";
	$TOPIC_TOPIC = LIST_ADMIN_SECT_16;
	$TOPIC_HEADING = LIST_ADMIN_SECT_17;
	$TOPIC_HELP = LIST_ADMIN_SECT_18;
	$TOPIC_FIELD = "<table $styletable>";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= "
		<tr>
		<td class='forumheader3' style='width:10%; white-space:nowrap; vertical-align:top;'>".$titles[$i]."</td>
		<td class='forumheader3'>
			".$rs -> form_select_open($sections[$i]."_".$type."_amount");
			for($a=1; $a<=$maxitems_amount; $a++){
				$TOPIC_FIELD .= ($list_pref[$sections[$i]."_".$type."_amount"] == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
			}
			$TOPIC_FIELD .= $rs -> form_select_close()."
		</td>
		</tr>";
	}
	$TOPIC_FIELD .= "</table>";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//order
	$max = count($sections);
	$TOPIC_TOPIC = LIST_ADMIN_SECT_19;
	$TOPIC_HEADING = LIST_ADMIN_SECT_20;
	$TOPIC_HELP = LIST_ADMIN_SECT_21;
	$TOPIC_FIELD = "<table $styletable>";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= "
		<tr>
		<td class='forumheader3' style='width:10%; white-space:nowrap; vertical-align:top;'>".$titles[$i]."</td>
		<td class='forumheader3'>
		".$rs -> form_select_open($sections[$i]."_".$type."_order");
		for($a=1; $a<=$max; $a++){
			$TOPIC_FIELD .= ($list_pref[$sections[$i]."_".$type."_order"] == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
		}
		$TOPIC_FIELD .= $rs -> form_select_close()."
		</td>
		</tr>";
	}
	$TOPIC_FIELD .= "</table>";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//caption
	$TOPIC_TOPIC = LIST_ADMIN_SECT_25;
	$TOPIC_HEADING = LIST_ADMIN_SECT_26;
	$TOPIC_HELP = LIST_ADMIN_SECT_27;
	$TOPIC_FIELD = "<table $styletable>";
	for($i=0;$i<count($sections);$i++){
		$TOPIC_FIELD .= "
		<tr>
		<td class='forumheader3' style='width:10%; white-space:nowrap; vertical-align:top;'>".$titles[$i]."</td>
		<td class='forumheader3'>
			".$rs -> form_text($sections[$i]."_".$type."_caption", 30, $list_pref[$sections[$i]."_".$type."_caption"], "50", "tbox")."
		</td>
		</tr>";
	}
	$TOPIC_FIELD .= "</table>";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	$text .= $TOPIC_ROW_SPACER;

	return $text;
}


//---------------------------------------------------------------------------------------------------
function parse_menu_options($type){
	global $rc, $list_pref, $rs, $sections, $titles, $iconlist, $TOPIC_ROW, $TOPIC_ROW_SPACER, $TOPIC_TABLE_END;

	$text = "
	<div id='".$type."' style='display:none; text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	if($type == "new_menu"){
		$text .= $rc -> parse_headerrow_title(LIST_ADMIN_OPT_5);
	}else{
		$text .= $rc -> parse_headerrow_title(LIST_ADMIN_OPT_3);
	}

	$text .= parse_global_options($type);

	//menu preference : caption
	$TOPIC_TOPIC = LIST_ADMIN_LAN_2;
	$TOPIC_HEADING = LIST_ADMIN_LAN_3;
	$TOPIC_HELP = LIST_ADMIN_LAN_4;
	$TOPIC_FIELD = $rs -> form_text($type."_caption", "30", $list_pref[$type."_caption"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : icon : use
	$TOPIC_TOPIC = LIST_ADMIN_LAN_5;
	$TOPIC_HEADING = LIST_ADMIN_LAN_6;
	$TOPIC_HELP = LIST_ADMIN_LAN_7;
	$TOPIC_FIELD = "
		".$rs -> form_radio($type."_icon_use", "1", ($list_pref[$type."_icon_use"] ? "1" : "0"), "", "").LIST_ADMIN_7."
		".$rs -> form_radio($type."_icon_use", "0", ($list_pref[$type."_icon_use"] ? "0" : "1"), "", "").LIST_ADMIN_8."
	";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : icon : show default theme bullet
	$TOPIC_TOPIC = LIST_ADMIN_MENU_2;
	$TOPIC_HEADING = LIST_ADMIN_MENU_3;
	$TOPIC_HELP = LIST_ADMIN_MENU_4;
	$TOPIC_FIELD = "
		".$rs -> form_radio($type."_icon_default", "1", ($list_pref[$type."_icon_default"] ? "1" : "0"), "", "").LIST_ADMIN_7."
		".$rs -> form_radio($type."_icon_default", "0", ($list_pref[$type."_icon_default"] ? "0" : "1"), "", "").LIST_ADMIN_8."
	";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : amount chars
	$TOPIC_TOPIC = LIST_ADMIN_LAN_8;
	$TOPIC_HEADING = LIST_ADMIN_LAN_9;
	$TOPIC_HELP = LIST_ADMIN_LAN_10;
	$TOPIC_FIELD = $rs -> form_text($type."_char_heading", "30", $list_pref[$type."_char_heading"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : postfix
	$TOPIC_TOPIC = LIST_ADMIN_LAN_11;
	$TOPIC_HEADING = LIST_ADMIN_LAN_12;
	$TOPIC_HELP = LIST_ADMIN_LAN_13;
	$TOPIC_FIELD = $rs -> form_text($type."_char_postfix", "30", $list_pref[$type."_char_postfix"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : date
	$TOPIC_TOPIC = LIST_ADMIN_LAN_14;
	$TOPIC_HEADING = LIST_ADMIN_LAN_15;
	$TOPIC_HELP = LIST_ADMIN_LAN_16;
	$TOPIC_FIELD = $rs -> form_text($type."_datestyle", "30", $list_pref[$type."_datestyle"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : date today
	$TOPIC_TOPIC = LIST_ADMIN_LAN_17;
	$TOPIC_HEADING = LIST_ADMIN_LAN_18;
	$TOPIC_HELP = LIST_ADMIN_LAN_19;
	$TOPIC_FIELD = $rs -> form_text($type."_datestyletoday", "30", $list_pref[$type."_datestyletoday"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//menu preference : show empty
	$TOPIC_TOPIC = LIST_ADMIN_LAN_26;
	$TOPIC_HEADING = LIST_ADMIN_LAN_27;
	$TOPIC_HELP = LIST_ADMIN_LAN_28;
	$TOPIC_FIELD = "
		".$rs -> form_radio($type."_showempty", "1", ($list_pref[$type."_showempty"] ? "1" : "0"), "", "").LIST_ADMIN_7."
		".$rs -> form_radio($type."_showempty", "0", ($list_pref[$type."_showempty"] ? "0" : "1"), "", "").LIST_ADMIN_8."
	";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	$text .= $TOPIC_ROW_SPACER;
	$text .= $TOPIC_TABLE_END;

	return $text;
}
//---------------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------------
function parse_page_options($type){
	global $rc, $list_pref, $rs, $sections, $TOPIC_ROW, $TOPIC_ROW_SPACER, $TOPIC_TABLE_END;

	if($type == "recent_page"){
		$display = "display:;";
	}else{
		$display = "display:none;";
	}

	$text = "
	<div id='".$type."' style='".$display." text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	if($type == "new_page"){
		$text .= $rc -> parse_headerrow_title(LIST_ADMIN_OPT_4);
	}else{
		$text .= $rc -> parse_headerrow_title(LIST_ADMIN_OPT_2);
	}

	$text .= parse_global_options($type);

	//page preference : caption
	$TOPIC_TOPIC = LIST_ADMIN_LAN_2;
	$TOPIC_HEADING = LIST_ADMIN_LAN_3;
	$TOPIC_HELP = LIST_ADMIN_LAN_4;
	$TOPIC_FIELD = $rs -> form_text($type."_caption", "30", $list_pref[$type."_caption"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : icon : use
	$TOPIC_TOPIC = LIST_ADMIN_LAN_5;
	$TOPIC_HEADING = LIST_ADMIN_LAN_6;
	$TOPIC_HELP = LIST_ADMIN_LAN_7;
	$TOPIC_FIELD = "
		".$rs -> form_radio($type."_icon_use", "1", ($list_pref[$type."_icon_use"] ? "1" : "0"), "", "").LIST_ADMIN_7."
		".$rs -> form_radio($type."_icon_use", "0", ($list_pref[$type."_icon_use"] ? "0" : "1"), "", "").LIST_ADMIN_8."
	";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : icon : show default theme bullet
	$TOPIC_TOPIC = LIST_ADMIN_LAN_29;
	$TOPIC_HEADING = LIST_ADMIN_LAN_30;
	$TOPIC_HELP = LIST_ADMIN_LAN_31;
	$TOPIC_FIELD = "
		".$rs -> form_radio($type."_icon_default", "1", ($list_pref[$type."_icon_default"] ? "1" : "0"), "", "").LIST_ADMIN_7."
		".$rs -> form_radio($type."_icon_default", "0", ($list_pref[$type."_icon_default"] ? "0" : "1"), "", "").LIST_ADMIN_8."
	";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : amount chars
	$TOPIC_TOPIC = LIST_ADMIN_LAN_8;
	$TOPIC_HEADING = LIST_ADMIN_LAN_9;
	$TOPIC_HELP = LIST_ADMIN_LAN_10;
	$TOPIC_FIELD = $rs -> form_text($type."_char_heading", "30", $list_pref[$type."_char_heading"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : postfix
	$TOPIC_TOPIC = LIST_ADMIN_LAN_11;
	$TOPIC_HEADING = LIST_ADMIN_LAN_12;
	$TOPIC_HELP = LIST_ADMIN_LAN_13;
	$TOPIC_FIELD = $rs -> form_text($type."_char_postfix", "30", $list_pref[$type."_char_postfix"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : date
	$TOPIC_TOPIC = LIST_ADMIN_LAN_14;
	$TOPIC_HEADING = LIST_ADMIN_LAN_15;
	$TOPIC_HELP = LIST_ADMIN_LAN_16;
	$TOPIC_FIELD = $rs -> form_text($type."_datestyle", "30", $list_pref[$type."_datestyle"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : date today
	$TOPIC_TOPIC = LIST_ADMIN_LAN_17;
	$TOPIC_HEADING = LIST_ADMIN_LAN_18;
	$TOPIC_HELP = LIST_ADMIN_LAN_19;
	$TOPIC_FIELD = $rs -> form_text($type."_datestyletoday", "30", $list_pref[$type."_datestyletoday"], "50", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : show empty
	$TOPIC_TOPIC = LIST_ADMIN_LAN_26;
	$TOPIC_HEADING = LIST_ADMIN_LAN_27;
	$TOPIC_HELP = LIST_ADMIN_LAN_28;
	$TOPIC_FIELD = "
		".$rs -> form_radio($type."_showempty", "1", ($list_pref[$type."_showempty"] ? "1" : "0"), "", "").LIST_ADMIN_7."
		".$rs -> form_radio($type."_showempty", "0", ($list_pref[$type."_showempty"] ? "0" : "1"), "", "").LIST_ADMIN_8."
	";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : colomn
	$TOPIC_TOPIC = LIST_ADMIN_LAN_20;
	$TOPIC_HEADING = LIST_ADMIN_LAN_21;
	$TOPIC_HELP = LIST_ADMIN_LAN_22;
	$TOPIC_FIELD = $rs -> form_select_open($type."_colomn");
		for($a=1; $a<=count($sections); $a++){
			$TOPIC_FIELD .= ($list_pref[$type."_colomn"] == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
		}
		$TOPIC_FIELD .= $rs -> form_select_close();
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	//page preference : welcome text
	$TOPIC_TOPIC = LIST_ADMIN_LAN_23;
	$TOPIC_HEADING = LIST_ADMIN_LAN_24;
	$TOPIC_HELP = LIST_ADMIN_LAN_25;
	$TOPIC_FIELD = $rs -> form_textarea($type."_welcometext", "50", "5", $list_pref[$type."_welcometext"], "", "tbox");
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

	$text .= $TOPIC_ROW_SPACER;
	$text .= $TOPIC_TABLE_END;

	return $text;
}
//---------------------------------------------------------------------------------------------------



function pref_submit() {
	global $rs, $TOPIC_ROW_NOEXPAND;

	$TOPIC_TOPIC = LIST_ADMIN_11;
	$TOPIC_FIELD = $rs -> form_button("submit", update_menu, LIST_ADMIN_2);
	return (preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW_NOEXPAND));
}


// ##### Display options --------------------------------------------------------------------------
function admin_list_config_adminmenu(){

				$act = "";
				unset($var);
				$var=array();
				//$var['general']['text']			= LIST_ADMIN_OPT_1;
				$var['recent_page']['text']		= LIST_ADMIN_OPT_2;
				$var['recent_menu']['text']		= LIST_ADMIN_OPT_3;
				$var['new_page']['text']		= LIST_ADMIN_OPT_4;
				$var['new_menu']['text']		= LIST_ADMIN_OPT_5;

				show_admin_menu(LIST_ADMIN_OPT_6, $act, $var, TRUE);

}
// ##### End --------------------------------------------------------------------------------------

require_once(e_ADMIN."footer.php");

?>
