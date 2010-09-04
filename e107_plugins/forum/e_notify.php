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
|     $Source: ... $
|     $Revision: 1.1 $
|     $Date: 2010-08-25 23:00:14 -0500 (Thu, 25 Aug 2010) $
|     $Author: Nowwhat $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if(defined('ADMIN_PAGE') && ADMIN_PAGE === true)
{
	include_lan(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_notify.php");
	$config_category = NT_LAN_FT_1;
	$config_events = array(
		'forumthreadcreate'	=> NT_LAN_FO_1,
		'forumpostcreate' 	=> NT_LAN_MP_1,
		'forumthreaddelete'	=> NT_LAN_FD_1,
		'forumpostdelete'		=> NT_LAN_FP_1,
		'forumthreadmove'		=> NT_LAN_FM_1);
}

if (!function_exists('notify_forumthreadcreate')) {
	function notify_forumthreadcreate($data) {
		global $nt;
		include_lan(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_notify.php");
		$message = NT_LAN_FO_3.': '.USERNAME.' ('.NT_LAN_FO_4.': '.$data['forum_name'].' )<br />';
		$message .= NT_LAN_FO_5.': '.$data['subject'].'<br /><br />';
		$message .= NT_LAN_FO_6.':<br />'.$data['post'].'<br /><br />';
		$nt -> send('forumthreadcreate', NT_LAN_FO_7, $message);
	}
}

if (!function_exists('notify_forumpostcreate')) {
	function notify_forumpostcreate($data) {
		global $nt;
		include_lan(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_notify.php");
		$message = NT_LAN_MP_3.': '.USERNAME.' ('.NT_LAN_MP_4.': '.$data['forum_name'].' )<br />';
		$message .= NT_LAN_MP_6.':<br />'.$data['post'].'<br /><br />';
		$nt -> send('forumpostcreate', NT_LAN_MP_7, $message);
	}
}

if (!function_exists('notify_forumthreaddelete')) {
	function notify_forumthreaddelete($data) {
		global $nt;
		include_lan(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_notify.php");
		$message = NT_LAN_FD_3.': '.$data['poster'].' ('.NT_LAN_FD_4.': '.$data['forum_name'].' )<br />';
		$message .= NT_LAN_FD_5.': '.$data['subject'].'<br /><br />';
		$message .= NT_LAN_FD_8.': '.$data['deleter'].'<br /><br />';
		$message .= NT_LAN_FD_6.':<br />'.$data['post'].'<br /><br />';
		$nt -> send('forumthreaddelete', NT_LAN_FD_7, $message);
	}
}

if (!function_exists('notify_forumpostdelete')) {
	function notify_forumpostdelete($data) {
		global $nt;
		include_lan(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_notify.php");
		$message = NT_LAN_FP_3.': '.$data['poster'].' ('.NT_LAN_FP_4.': '.$data['forum_name'].' )<br />';
		$message .= NT_LAN_FP_8.': '.$data['deleter'].'<br /><br />';
		$message .= NT_LAN_FP_6.':<br />'.$data['post'].'<br /><br />';
		$nt -> send('forumpostdelete', NT_LAN_FP_7, $message);
	}
}

if (!function_exists('notify_forumthreadmove')) {
	function notify_forumthreadmove($data) {
		global $nt;
		include_lan(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_notify.php");
		$message = NT_LAN_FM_3.': '.$data['poster'].'<br />';
		$message .= NT_LAN_FM_4.': '.$data['old_thread_name'].'<br />';
		$message .= NT_LAN_FM_5.': '.$data['new_thread_name'].'<br />';
		$message .= NT_LAN_FM_6.': '.$data['old_forum'].'<br />';		
		$message .= NT_LAN_FM_7.': '.$data['new_forum'].'<br />';		
		$message .= NT_LAN_FM_9.': '.$data['mover'].'<br /><br />';
		$nt -> send('forumthreadmove', NT_LAN_FM_8, $message);
	}
}
?>