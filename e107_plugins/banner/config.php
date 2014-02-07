<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner Menu Configuration (OLD - redirects to e107_admin/banner.php)
 *
 *
*/

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 *
 *	@todo - try and access file for menu config without a redirect
 */

$eplug_admin = TRUE;
require_once("../../class2.php");

/*
 * The same, cleaned up code is already part of banner.php
 * FIXME - we should be able to combine all core menus in a nice way... somehow
 */
header('Location:'.e_PLUGIN_ABS.'banner/admin_banner.php?menu');
exit;

?>