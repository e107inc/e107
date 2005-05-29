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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/e_notify.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-05-29 18:19:26 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$config_category = 'Links Page Events';
$config_events = array('linksub' => 'Link submitted by user');

if (!function_exists('notify_linksub')) {
	function notify_linksub($data) {
		global $sql;
		$nt = new notify;
		foreach ($data as $key => $value) {
			$message .= $key.': '.$value.'<br />';
		}
		$nt -> send('linksub', 'Link Submitted', $message);
	}
}

?>