<?php

if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
	} else {
	include_once(e_PLUGIN."links_page/languages/English.php");
}
$front_page['links_page'] = array('page' => $PLUGINS_DIRECTORY.'links_page/links.php', 'title' => LCLAN_ADMIN_15);

?>