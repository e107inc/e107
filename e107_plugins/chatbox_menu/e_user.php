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
class chatbox_menu_user // plugin-folder + '_user' 
{

	function profile($udata)
	{
		$pref = e107::getPref();

		if (!$pref['cb_user_addon'])
		{
			return array();
		}

		if(!$chatposts = e107::getRegistry('total_chatposts'))
		{
			$chatposts = 0; // In case plugin not installed
			if(e107::isInstalled("chatbox_menu"))
			{
				$chatposts = e107::getDb()->count("chatbox");
			}
			e107::setRegistry('total_chatposts', $chatposts);
		}

		$perc = ($chatposts > 0) ? round(($udata['user_chats']/$chatposts) * 100, 2) : 0;


		$var = array(
			0 => array('label' => LAN_PLUGIN_CHATBOX_MENU_POSTS, 'text' => $udata['user_chats']." ( ".$perc."% )")
		);

		return $var;
	}

}