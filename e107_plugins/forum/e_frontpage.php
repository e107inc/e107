<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('forum', 'English_front');

/**
 *	@todo - extend array to allow selection of any main forum, as well as the forum front page
 */
$front_page['forum'] = array('page' => $PLUGINS_DIRECTORY.'forum/forum.php', 'title' => LAN_PLUGIN_FORUM_NAME);

?>