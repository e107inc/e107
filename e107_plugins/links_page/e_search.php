<?php
if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE."/lan_links_page_search.php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE."/lan_links_page_search.php");
} else {
	include_once(e_PLUGIN."links_page/languages/English/lan_links_page_search.php");
}
$search_info[] = array('sfile' => e_PLUGIN.'links_page/links_search.php', 'qtype' => LNK_SCH_LAN_1, 'refpage' => 'links.php');

?>