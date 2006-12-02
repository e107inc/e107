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
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:56 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

$lan_file = e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."content/languages/English/lan_content.php");

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