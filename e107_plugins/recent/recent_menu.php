<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2003
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

if(!getperms("P")){
	return;
}
if(!$sql -> db_Select("plugin", "*", "plugin_path = 'recent' AND plugin_installflag = '1' ")){
	return;
}

global $sysprefs, $tp;
unset($text);
require_once(e_PLUGIN."recent/recent_shortcodes.php");

//get language file
$lan_file = e_PLUGIN."recent/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."recent/languages/English.php");

require_once(e_PLUGIN."recent/recent_class.php");
$rc = new recent;

//check preferences from database
$sql = new db;
$num_rows = $sql -> db_Select("core", "*", "e107_name='recent' ");
$row = $sql -> db_Fetch();

//insert default preferences
if (empty($row['e107_value'])) {

	$rc -> getSections();
	$recent_pref = $rc -> getDefaultPrefs();
	$tmp = $eArrayStorage->WriteArray($recent_pref);

	$sql -> db_Insert("core", "'recent', '$tmp' ");
	$sql -> db_Select("core", "*", "e107_name='recent' ");
}

$recent_pref = $eArrayStorage->ReadArray($row['e107_value']);

//get all sections to use
foreach ($recent_pref as $key => $value) {
	if(substr($key,-12) == "_menudisplay" && $value == "1"){
		$sections[] = substr($key,0,-12);
	}
}

//section reference
for($i=0;$i<count($sections);$i++){
	$arr[$sections[$i]][0] = $recent_pref["$sections[$i]_caption"];
	$arr[$sections[$i]][1] = $recent_pref["$sections[$i]_menudisplay"];
	$arr[$sections[$i]][2] = $recent_pref["$sections[$i]_menuopen"];
	$arr[$sections[$i]][3] = $recent_pref["$sections[$i]_menuauthor"];
	$arr[$sections[$i]][4] = $recent_pref["$sections[$i]_menucategory"];
	$arr[$sections[$i]][5] = $recent_pref["$sections[$i]_menudate"];
	$arr[$sections[$i]][6] = $recent_pref["$sections[$i]_icon"];
	$arr[$sections[$i]][7] = $recent_pref["$sections[$i]_menuamount"];
	$arr[$sections[$i]][8] = $recent_pref["$sections[$i]_order"];
	$arr[$sections[$i]][9] = $sections[$i];
}

//sort array on order values set in preferences
usort($arr, create_function('$e,$f','return $e[8]==$f[8]?0:($e[8]>$f[8]?1:-1);'));
global $rc;
if(!is_object($rc)){ $rc = new recent; }
//display the sections
$text = "";
for($i=0;$i<count($arr);$i++) {
	if($arr[$i][1] == "1") {
		$text .= $rc -> show_section_recent($arr[$i], "menu");
	}
}

$caption = ($recent_pref['menu_caption'] ? $recent_pref['menu_caption'] : RECENT_MENU_1);
$ns -> tablerender($caption, $text);
unset($text);

?>