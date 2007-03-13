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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/e_notify.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-03-13 16:51:05 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

include_lan(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php");

$config_category = CONTENT_NOTIFY_LAN_1;
$config_events = array('content' => CONTENT_NOTIFY_LAN_2);

if (!function_exists('notify_content')) {
	function notify_content($data) {
		global $nt;
		foreach ($data as $key => $value) {
			$message .= $key.': '.$value.'<br />';
		}
		$nt -> send('content', CONTENT_NOTIFY_LAN_3, $message);
	}
}

?>