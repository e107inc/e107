<?php
if (file_exists(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content_search.php")) {
	include_once(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content_search.php");
} else {
	include_once(e_PLUGIN."content/languages/English/lan_content_search.php");
}
$search_info[] = array( 'sfile' => e_PLUGIN.'content/content_search.php', 'qtype' => CONT_SCH_LAN_1, 'refpage' => 'content.php');

?>