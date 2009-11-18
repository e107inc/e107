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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/e_notify.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
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