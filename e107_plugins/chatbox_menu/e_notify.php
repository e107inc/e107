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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/chatbox_menu/e_notify.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-05-31 21:38:58 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

@include_once(e_PLUGIN.'chatbox_menu/languages/'.e_LANGUAGE.'_notify.php');
@include_once(e_PLUGIN.'chatbox_menu/languages/English_notify.php');

$config_category = NT_LAN_CB_1;
$config_events = array('cboxpost' => NT_LAN_CB_2);

if (!function_exists('notify_cboxpost')) {
	function notify_cboxpost($data) {
		global $nt;
		$message = NT_LAN_CB_3.': '.USERNAME.' ('.NT_LAN_CB_4.': '.$data['ip'].' )<br />';
		$message .= NT_LAN_CB_5.':<br />'.$data['cmessage'].'<br /><br />';
		$nt -> send('cboxpost', NT_LAN_CB_6, $message);
	}
}

?>