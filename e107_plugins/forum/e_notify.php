<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin notify configuration
 *
*/

// TODO - create notify messages + LAN

if (!defined('e107_INIT')) { exit; }

e107::lan('forum','notify',true); 

// v2.x Standard 
class forum_notify extends notify
{		
	function config()
	{
			
		$config = array();
	
		$config[] = array(
			'name'			=> LAN_FORUM_NT_NEWTOPIC,
			'function'		=> "forum_nt",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_NEWTOPIC_PROB,
			'function'		=> "forum_ntp",
			'category'		=> ''
		);

		$config[] = array(
			'name'			=> LAN_FORUM_NT_TOPIC_DELETED,
			'function'		=> "forum_topic_del",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_TOPIC_SPLIT,
			'function'		=> "forum_topic_split",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_POST_DELETED,
			'function'		=> "forum_post_del",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_POST_REPORTED,
			'function'		=> "forum_post_rep",
			'category'		=> ''
		);		
		
		return $config;
	}
	
	function forum_nt($data) 
	{
		$message = 'todo';
		$this->send('forum_nt', LAN_PLUGIN_FORUM_NAME, $message);
	}

	function forum_ntp($data)
	{
		$message = 'todo';
		$this->send('forum_nt', LAN_FORUM_NT_7, $message);
	}

	function forum_topic_del($data) 
	{
		$message = 'todo';
		$this->send('forum_topic_del', LAN_FORUM_NT_8, $message);
	}

	function forum_topic_split($data) 
	{
		$message = 'todo';
		$this->send('forum_topic_split', LAN_FORUM_NT_9, $message);
	}

	function forum_post_del($data) 
	{
		$message = 'todo';
		$this->send('forum_post_del', LAN_FORUM_NT_10, $message);
	}

	function forum_post_rep($data) 
	{
		$message = 'todo';
		$this->send('forum_post_rep', LAN_FORUM_NT_11, $message);
	}
	
}


?>