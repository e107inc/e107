<?php
/*
* e107 website system
*
* Copyright ( c ) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_setup.php,v $
* $Revision: 1.1 $
* $Date: 2009-10-20 03:58:47 $
* $Author: e107coders $
*
*/

class pm_setup
{
	
	function pm_uninstall_post()
	{
		$sql = e107::getDb();
		$sql->db_Delete("core", "e107_name = 'pm_prefs'");
		$sql->db_Delete("menus", "menu_name = 'private_msg_menu'");
	}
	
}
