<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class forum_user // plugin-folder + '_user' 
{		
		
	function profile($udata) 
	{
		
		$sql = e107::getDb();
		
		if(!$total_forumposts = e107::getRegistry('total_forumposts'))
		{
			$total_forumposts = intval($sql->count("forum_post"));
			e107::setRegistry('total_forumposts', $total_forumposts);
		}
		
		$count = $sql->retrieve('user_extended', 'user_plugin_forum_posts', 'user_extended_id = '.$udata['user_id']);
		
		$perc = ($total_forumposts > 0 && $count) ? round(($count / $total_forumposts) * 100, 2) : 0;

		$url = ($count> 0) ? e_HTTP."userposts.php?0.forums.".$udata['user_id'] : null;

		$var = array(
			0 => array('label' => LAN_PLUGIN_FORUM_POSTS, 'text' => intval($count)." ( ".$perc."% )", 'url'=> $url)
		);
		
		return $var;
	}
	
}