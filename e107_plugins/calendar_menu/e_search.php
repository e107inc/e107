<?php
if (file_exists(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE."_search.php")) {
	include_once(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE."_search.php");
} else {
	include_once(e_PLUGIN."calendar_menu/languages/English_search.php");
}
$search_info[] = array('sfile' => e_PLUGIN.'calendar_menu/search/search_parser.php', 'qtype' => CM_SCH_LAN_1, 'refpage' => 'calendar.php');

?>