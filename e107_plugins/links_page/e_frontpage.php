<?php

if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE."/lan_links_page_frontpage.php")) {
	@include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE."/lan_links_page_frontpage.php");
	} else {
	@include_once(e_PLUGIN."links_page/languages/English/lan_links_page_frontpage.php");
}
$front_page['links_page'] = array('page' => $PLUGINS_DIRECTORY.'links_page/links.php', 'title' => LP_FP_1);

?>