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
|		$Source: /cvs_backup/e107_0.7/e107_plugins/list_new/list_new_menu.php,v $
|		$Revision: 1.1 $
|		$Date: 2005-06-17 13:35:39 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

if(!getperms("P")){
	return;
}
if(!$sql -> db_Select("plugin", "*", "plugin_path = 'list_new' AND plugin_installflag = '1' ")){
	return;
}

global $sysprefs, $tp, $eArrayStorage;
$listplugindir = e_PLUGIN."list_new/";
unset($text);
require_once($listplugindir."list_shortcodes.php");

//get language file
$lan_file = $listplugindir."languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : $listplugindir."languages/English.php");

require_once($listplugindir."list_class.php");
$rc = new listclass;

$list_pref	= $rc -> getListPrefs();
$mode		= "new_menu";
$sections	= $rc -> prepareSection($mode);
$arr		= $rc -> prepareSectionArray($mode);

//display the sections
$text = "";
for($i=0;$i<count($arr);$i++){
	if($arr[$i][1] == "1"){
		$sectiontext = $rc -> show_section_list($arr[$i], $mode);
		if($sectiontext != ""){
			$text .= $sectiontext;
		}
	}
}

$caption = ($list_pref[$mode."_caption"] ? $list_pref[$mode."_caption"] : LIST_MENU_1);
$ns -> tablerender($caption, $text);
unset($text);

?>