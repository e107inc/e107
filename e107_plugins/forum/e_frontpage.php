<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('forum', "front", true);
// e107::lan('forum', 'English_front');

/**
 *	@todo - extend array to allow selection of any main forum, as well as the forum front page
 */
// $front_page['forum'] = array('page' =>'{e_PLUGIN}forum/forum.php', 'title' => LAN_PLUGIN_FORUM_NAME);

//v2.x spec.
class forum_frontpage // include plugin-folder in the name.
{
	function config()
	{

		$frontPage = array(
			'title'		=> LAN_PLUGIN_FORUM_NAME,
			'page'		=> '{e_PLUGIN}forum/forum.php',
		);

		return $frontPage;
	}
}


?>