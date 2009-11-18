<?php
/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_setup.php,v $
* $Revision: 1.3 $
* $Date: 2009-11-18 01:49:18 $
* $Author: marj_nl_fr $
*
*/

class pm_setup
{
	
	function uninstall_post()
	{
		$sql = e107::getDb();
		$sql->db_Delete("core", "e107_name = 'pm_prefs'");
		$sql->db_Delete("menus", "menu_name = 'private_msg_menu'");
	}
	
}
