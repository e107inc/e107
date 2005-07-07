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
|		$Source: /cvs_backup/e107_0.7/e107_plugins/list_new/list.php,v $
|		$Revision: 1.6 $
|		$Date: 2005-07-07 06:37:32 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

//if(!getperms("P")){header("location:".e_BASE."index.php"); exit; }
if(!$sql -> db_Select("plugin", "*", "plugin_path = 'list_new' AND plugin_installflag = '1' ")){
	header("location:".e_BASE."index.php"); exit;
}
$listplugindir = e_PLUGIN."list_new/";
require_once($listplugindir."list_shortcodes.php");
require_once($listplugindir."list_class.php");
$rc = new listclass;
require_once(e_HANDLER."form_handler.php");
$rs = new form;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();

//get language file
$lan_file = $listplugindir."languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : $listplugindir."languages/English.php");

unset($text);

require_once(HEADERF);

global $tp;

// check query
if(e_QUERY){
	$qs = explode(".", e_QUERY);
	if($qs[0] == "new"){
		$mode = $qs[0];
	}
}
if(isset($mode) && $mode == "new"){
	$mode = "new_page";
}else{
	$mode = "recent_page";	//default to 'page'
}

$list_pref	= $rc -> getListPrefs();
$sections	= $rc -> prepareSection($mode);
$arr		= $rc -> prepareSectionArray($mode, $sections);

$text = "";
$timelapse = 0;
if(isset($qs[0]) && $qs[0] == "new"){
	if(isset($list_pref['new_page_timelapse']) && $list_pref['new_page_timelapse']){
		if(isset($list_pref['new_page_timelapse_days']) && is_numeric($list_pref['new_page_timelapse_days'])){
			$days = $list_pref['new_page_timelapse_days'];
		}else{
			$days = "30";
		}
		if(isset($qs[1]) && is_numeric($qs[1]) && $qs[1] <= $days){
			$timelapse = $qs[1];
		}
		$url		= e_PLUGIN."list_new/list.php?new";
		$selectjs	= "onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\"";

		$LIST_TIMELAPSE = LIST_MENU_6;
		$LIST_TIMELAPSE .= $rs -> form_select_open("timelapse", $selectjs).$rs -> form_option(LIST_MENU_5, 0);
		for($a=1; $a<=$days; $a++){
			$LIST_TIMELAPSE .= $rs -> form_option($a, ($timelapse == $a ? "1" : "0"), $url.".".$a);
		}
		$LIST_TIMELAPSE .= $rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_TIMELAPSE_TABLE);
	}
}



//display the sections
$LIST_COL_COLS = $list_pref[$mode."_colomn"];

$LIST_COL_CELLWIDTH = round((100/$list_pref[$mode."_colomn"]),0);
$text .= $LIST_COL_START;

if($list_pref[$mode."_welcometext"]){
	$LIST_COL_WELCOMETEXT = $tp -> toHTML($list_pref[$mode."_welcometext"]);
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_COL_WELCOME);
}
$k=0;
for($i=0;$i<count($arr);$i++){
	unset($rowswitch);
	if($arr[$i][1] == "1"){
				
		$sectiontext = $rc -> show_section_list($arr[$i], $mode);
		if($sectiontext != ""){
			if( intval($k/$list_pref[$mode."_colomn"]) == $k/$list_pref[$mode."_colomn"] ){
				$rowswitch = $LIST_COL_ROWSWITCH;
			}
			$text .= (isset($rowswitch) ? $rowswitch : "");
			$text .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_COL_CELL_START);
			$text .= $sectiontext;
			$text .= $LIST_COL_CELL_END;
			$k++;
		}
	}
}
$text .= $LIST_COL_END;

$caption = (isset($list_pref[$mode."_caption"]) && $list_pref[$mode."_caption"] ? $list_pref[$mode."_caption"] : LIST_MENU_1);
$ns -> tablerender($caption, $text);
unset($text);

require_once(FOOTERF);

?>