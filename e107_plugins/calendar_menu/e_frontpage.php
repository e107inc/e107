<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar plugin - Front page
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/e_frontpage.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'_admin_calendar_menu.php');

$front_page['calendar'] = array(
	'title' => EC_ADLAN_1,
	'page' => array(
	array('page' => e_PLUGIN_ABS.'calendar_menu/calendar.php', 'title' => EC_ADLAN_A09 ),
	array('page' => e_PLUGIN_ABS.'calendar_menu/event.php', 'title' => EC_LAN_163 ))
	);

?>