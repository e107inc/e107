<?php
if (file_exists(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
} else {
	include_once(e_PLUGIN."chatbox_menu/languages/English/English.php");
}

$search_info[] = array(
'sfile' => e_PLUGIN.'chatbox_menu/search_chatbox.php',
 'qtype' => CHATBOX_L2,
 'refpage' => 'chatbox.php'
);
?>