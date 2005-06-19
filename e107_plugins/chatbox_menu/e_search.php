<?php
if (file_exists(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/lan_chatbox_search.php")) {
	include_once(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/lan_chatbox_search.php");
} else {
	include_once(e_PLUGIN."chatbox_menu/languages/English/lan_chatbox_search.php");
}

$search_info[] = array('sfile' => e_PLUGIN.'chatbox_menu/search/search_parser.php', 'qtype' => CB_SCH_LAN_1, 'refpage' => 'chat.php', 'advanced' => e_PLUGIN.'chatbox_menu/search/search_advanced.php');
?>