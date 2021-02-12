<?php
/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*	PM plugin - install/uninstall routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_setup.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

/**
 *	e107 Private messenger plugin
 *
 *	install/uninstall routines
 *
 *	@package	e107_plugins
 *	@subpackage	pm
 *	@version 	$Id$;
 */

class pm_setup
{
	
	function uninstall_post()
	{
		$sql = e107::getDb();
		$sql->delete('core', "e107_name = 'pm_prefs'");
		$sql->delete('menus', "menu_name = 'private_msg_menu'");
	}
	
}
