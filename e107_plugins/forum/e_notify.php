<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin notify configuration
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/e_notify.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }

if(defined('ADMIN_PAGE') && ADMIN_PAGE === true)
{
	include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_notify.php');
	$config_category = FORUM_NT_1;
	$config_events = array(
		'forum_nt' => FORUM_NT_NEWTHREAD, 
		'forum_ntp' => FORUM_NT_NEWTHREAD_PROB,
		'forum_thread_del' => FORUM_NT_THREAD_DELETED,
		'forum_thread_split' => FORUM_NT_THREAD_SPLIT,
		'forum_post_del' => FORUM_NT_POST_DELETED,
		'forum_post_rep' => FORUM_NT_POST_REPORTED
	);
}

if (!function_exists('notify_forum_nt'))
{
	function notify_forum_nt($data)
	{
		$e107 = e107::getInstance();
		include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_notify.php');
		$message = 'todo';
		$e107->notify->send('forum_nt', FORUM_NT_6, $message);
	}
}

if (!function_exists('notify_forum_ntp'))
{
	function notify_forum_ntp($data)
	{
		$e107 = e107::getInstance();
		include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_notify.php');
		$message = 'todo';
		$e107->notify->send('forum_ntp', FORUM_NT_7, $message);
	}
}

if (!function_exists('forum_thread_del'))
{
	function forum_thread_del($data)
	{
		$e107 = e107::getInstance();
		include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_notify.php');
		$message = 'todo';
		$e107->notify->send('forum_thread_del', FORUM_NT_8, $message);
	}
}

if (!function_exists('forum_thread_split'))
{
	function forum_thread_split($data)
	{
		$e107 = e107::getInstance();
		include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_notify.php');
		$message = 'todo';
		$e107->notify->send('forum_thread_split', FORUM_NT_8, $message);
	}
}

if (!function_exists('forum_post_rep'))
{
	function forum_post_rep($data)
	{
		$e107 = e107::getInstance();
		include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_notify.php');
		$message = 'todo';
		$e107->notify->send('forum_post_rep', FORUM_NT_9, $message);
	}
}

?>