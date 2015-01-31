<?php
if (!defined('e107_INIT')) { exit(); }

//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_search.php'); // FIXME needs changing after forum lan rewrite 

$search_info[] = array(
	'sfile' => e_PLUGIN.'forum/search/search_parser.php', 
	'qtype' => LAN_PLUGIN_FORUM_NAME, 
	'refpage' => 'forum', 
	'advanced' => e_PLUGIN.'forum/search/search_advanced.php', 
	'id' => 'forum'
	);
