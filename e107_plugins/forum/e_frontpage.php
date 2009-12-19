<?php

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_frontpage.php');

/**
 *	@todo - extend array to allow selection of any main forum, as well as the forum front page
 */
$front_page['forum'] = array('page' => $PLUGINS_DIRECTORY.'forum/forum.php', 'title' => FOR_FP_1);

?>