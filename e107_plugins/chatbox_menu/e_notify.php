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
|     $Revision: 1.1 $
|     $Date: 2005-05-29 18:19:25 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$config_category = 'Chatbox Events';
$config_events = array('cboxpost' => 'Message posted');

if (!function_exists('notify_cboxpost')) {
	function notify_cboxpost($data) {
		global $sql;
		$nt = new notify;
		$message = 'Posted by: '.USERNAME.' (IP Address: '.$data['ip'].' )<br />';
		$message .= 'Message:<br />'.$data['cmessage'].'<br /><br />';
		$nt -> send('cboxpost', 'Chatbox Message Posted', $message);
	}
}

?>