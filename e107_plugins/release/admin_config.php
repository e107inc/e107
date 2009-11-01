<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Release Plugin
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/release/admin_config.php,v $
 * $Revision: 1.13 $
 * $Date: 2009-11-01 19:05:26 $
 * $Author: secretr $
 *
*/

require_once("../../class2.php");
if (!getperms("P")) { header("location:".e_BASE."index.php"); exit; }

/*
 * After initialization we'll be able to call dispatcher via e107::getAdminUI()
 * so this is the first we should do on admin page.
 * Global instance variable is not needed.
 * NOTE: class is auto-loaded - see class2.php __autoload()
 */
/* $dispatcher = */new plugin_release_admin();

/*
 * Uncomment the below only if you disable the auto observing above
 * Example: $dispatcher = new plugin_release_admin(null, null, false);
 */
//$dispatcher->runObservers(true);

require_once(e_ADMIN."auth.php");

/*
 * Send page content
 */
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");

/* OBSOLETE - see admin_shortcodes::sc_admin_menu()
function admin_config_adminmenu() 
{
	//global $rp;
	//$rp->show_options();
	e107::getRegistry('admin/release_dispatcher')->renderMenu();
}
*/

/* OBSOLETE - done within header.php
function headerjs() // needed for the checkboxes - how can we remove the need to duplicate this code?
{
	return e107::getAdminUI()->getHeader();
}
*/
?>