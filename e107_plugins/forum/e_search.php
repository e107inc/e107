<?php
if (!defined('e107_INIT')) { exit(); }

e107::lan('forum', "search", true);
//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/'.e_LANGUAGE.'_search.php');

$search_info[] = array(
	'sfile' => e_PLUGIN.'forum/search/search_parser.php', 
	'qtype' => LAN_PLUGIN_FORUM_NAME, 
	'refpage' => 'forum', 
	'advanced' => e_PLUGIN.'forum/search/search_advanced.php', 
	'id' => 'forum'
);
