<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
*/



if (!defined('e107_INIT')) { exit; }

// e107::lan('_blank','notify',true);

// v2.x Standard 
class _blank_notify extends notify
{		
	function config()
	{
			
		$config = array();
	
		$config[] = array(
			'name'			=> "Notify about something",
			'function'		=> "customNotify",
			'category'		=> ''
		);	


		
		return $config;
	}


	function customNotify($data)
	{
		$subject = "My Notification";
		$message = print_a($data,true);
		$this->send('customNotify', $subject, $message);
	}

	
}


