<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/chatbox_menu/e_notify.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-10-29 13:35:54 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if(defined('ADMIN_PAGE') && ADMIN_PAGE === true)
{
	include_lan(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
	$config_category = NT_LAN_CB_1;
	$config_events = array('cboxpost' => NT_LAN_CB_2);
}


if (!function_exists('notify_cboxpost')) {
	function notify_cboxpost($data) {
		global $nt;
		include_lan(e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/".e_LANGUAGE.".php");
		$message = NT_LAN_CB_3.': '.USERNAME.' ('.NT_LAN_CB_4.': '.$data['ip'].' )<br />';
		$message .= NT_LAN_CB_5.':<br />'.$data['cmessage'].'<br /><br />';
		$nt -> send('cboxpost', NT_LAN_CB_6, $message);
	}
}

