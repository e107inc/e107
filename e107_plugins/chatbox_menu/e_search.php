<?php
if (file_exists(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."_search.php")) {
	include_once(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."_search.php");
} else {
	include_once(e_PLUGIN."chatbox_menu/languages/English_search.php");
}

$search_info[] = array('sfile' => e_PLUGIN.'chatbox_menu/search_chatbox.php', 'qtype' => CB_SCH_LAN_1, 'refpage' => 'chatbox.php');
?>