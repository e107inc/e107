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

require_once("../../class2.php");
require_once(e_PLUGIN."recent/recent_shortcodes.php");
require_once(HEADERF);

//get language file
$lan_file = e_PLUGIN."recent/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."recent/languages/English.php");

unset($text);

require_once(e_PLUGIN."recent/recent_class.php");
$rc = new recent;

global $tp;

//check preferences from database
$sql = new db;
$num_rows = $sql -> db_Select("core", "*", "e107_name='recent' ");
$row = $sql -> db_Fetch();

//insert default preferences
if (empty($row['e107_value'])) {

	$rc -> getSections();
	$recent_pref = $rc -> getDefaultPrefs();

	$tmp = addslashes(serialize($recent_pref));
	$sql -> db_Insert("core", "'recent', '$tmp' ");
	$sql -> db_Select("core", "*", "e107_name='recent' ");
}

$recent_pref = unserialize(stripslashes($row['e107_value']));
if(!is_array($recent_pref)){ $recent_pref = unserialize($row['e107_value']); }

//get all sections to use
foreach ($recent_pref as $key => $value) {
	if(substr($key,-12) == "_pagedisplay" && $value == "1"){
		$sections[] = substr($key,0,-12);
	}
}

//section reference
for($i=0;$i<count($sections);$i++){
	$arr[$sections[$i]][0] = $recent_pref["$sections[$i]_caption"];
	$arr[$sections[$i]][1] = $recent_pref["$sections[$i]_pagedisplay"];
	$arr[$sections[$i]][2] = $recent_pref["$sections[$i]_pageopen"];
	$arr[$sections[$i]][3] = $recent_pref["$sections[$i]_pageauthor"];
	$arr[$sections[$i]][4] = $recent_pref["$sections[$i]_pagecategory"];
	$arr[$sections[$i]][5] = $recent_pref["$sections[$i]_pagedate"];
	$arr[$sections[$i]][6] = $recent_pref["$sections[$i]_icon"];
	$arr[$sections[$i]][7] = $recent_pref["$sections[$i]_pageamount"];
	$arr[$sections[$i]][8] = $recent_pref["$sections[$i]_order"];
	$arr[$sections[$i]][9] = $sections[$i];
}

//sort array on order values set in preferences
usort($arr, create_function('$e,$f','return $e[8]==$f[8]?0:($e[8]>$f[8]?1:-1);'));

//display the sections
$RECENT_PAGE_TABLE_COLS = $recent_pref["page_colomn"];
$RECENT_PAGE_TABLE_CELLWIDTH = round((100/$recent_pref["page_colomn"]),0);
$text = $RECENT_PAGE_TABLE_START;

if($recent_pref["page_welcometext"]){
	$RECENT_PAGE_TABLE_WELCOMETEXT = $tp -> toHTML($recent_pref["page_welcometext"]);
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_PAGE_TABLE_WELCOME);
}

for($i=0;$i<count($arr);$i++){
	if($arr[$i][1] == "1"){
		if( intval($i/$recent_pref["page_colomn"]) == $i/$recent_pref["page_colomn"] ){
			$text .= $RECENT_PAGE_TABLE_ROWSWITCH;
		}
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_PAGE_TABLE_CELL_START);
		$text .= $rc -> show_section_recent($arr[$i], "page");
		$text .= $RECENT_PAGE_TABLE_CELL_END;
	}
}
$text .= $RECENT_PAGE_TABLE_END;

$caption = ($recent_pref['page_caption'] ? $recent_pref['page_caption'] : RECENT_MENU_1);
$ns -> tablerender($caption, $text);
unset($text);

require_once(FOOTERF);

?>