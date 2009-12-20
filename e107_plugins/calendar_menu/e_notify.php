<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/e_notify.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-12-20 22:47:28 $
 * $Author: e107steved $
 */

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id: e_notify.php,v 1.5 2009-12-20 22:47:28 e107steved Exp $;
 */

if (!defined('e107_INIT')) { exit; }

if(defined('ADMIN_PAGE') && ADMIN_PAGE === true)
{
	include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');
	$config_category = NT_LAN_EC_1;
	$config_events = array('ecalnew' => NT_LAN_EC_7, 'ecaledit' => NT_LAN_EC_2);
}

if (!function_exists('notify_ecalnew'))
{
	function notify_ecalnew($data) 
	{
		global $nt;
		include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');
		$message = NT_LAN_EC_3.': '.USERNAME.' ('.NT_LAN_EC_4.': '.$data['ip'].' )<br />';
		$message .= NT_LAN_EC_5.':<br />'.$data['cmessage'].'<br /><br />';
		$nt -> send('ecaledit', NT_LAN_EC_6, $message);
	}
}

if (!function_exists('notify_ecaledit')) 
{
	function notify_ecaledit($data) 
	{
		global $nt;
		include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');
		$message = NT_LAN_EC_3.': '.USERNAME.' ('.NT_LAN_EC_4.': '.$data['ip'].' )<br />';
		$message .= NT_LAN_EC_5.':<br />'.$data['cmessage'].'<br /><br />';
		$nt -> send('ecaledit', NT_LAN_EC_8, $message);
	}
}



?>