<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Chatbox plugin - Dashboard (Status)
 *
*/

/**
 *	e107 Chatbox plugin
 *
 *	@package	e107_plugins
 *	@subpackage	chatbox
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }

class chatbox_menu_dashboard // include plugin-folder in the name.
{
	function chart()
	{
		return false;
	}
	
	
	
	function status()
	{
		$sql = e107::getDb();
		$chatbox_posts = $sql->count('chatbox');
		
		$var[0]['icon'] 	= "<img src='".e_PLUGIN_ABS."chatbox_menu/images/chatbox_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' /> ";
		$var[0]['title'] 	= LAN_PLUGIN_CHATBOX_MENU_POSTS;
		$var[0]['url']		= e_PLUGIN."chatbox_menu/admin_chatbox.php";
		$var[0]['total'] 	= $chatbox_posts;

		return $var;
	}	
}



