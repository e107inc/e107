<?php
if (file_exists(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_search.php")) {
	include_once(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_search.php");
} else {
	include_once(e_PLUGIN."forum/languages/English/lan_forum_search.php");
}
$search_info[] = array('sfile' => e_PLUGIN.'forum/forum_search.php', 'qtype' => FOR_SCH_LAN_1, 'refpage' => 'forum.php');

?>