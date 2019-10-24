<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('forum', "front", true);

class forum_frontpage // include plugin-folder in the name.
{
	function config()
	{
		$sql 	= e107::getDb();
		$config = array();

		$config['title'] = LAN_PLUGIN_FORUM_NAME;

		// Always show the 'forum index' option
		$config['page'][] = array('page' => e107::url('forum', 'index'), 'title' => "Main forum index"); 

		// Retrieve all forums (exclude parents)
		if($sql->select('forum', 'forum_id, forum_name, forum_sef', "forum_parent != 0"))
		{
			while($row = $sql->fetch())
			{
				$url =  e107::url('forum', 'forum', 
							array(
								'forum_id' => $row['forum_id'], 
								'forum_sef' => $row['forum_sef']
							)
						);

				$config['page'][] = array('page' => $url, 'title' => $row['forum_sef']);
			}
		}

		return $config;
	}
}