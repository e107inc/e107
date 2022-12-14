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


	// BELOW IS OPTIONAL - R IF YOU WISH YOUR PLUGIN TO BECOME A ROUTER OF NOTIFICATIONS. eg. sending sms or messages to other platforms.

	/**
	 * REMOVE THIS METHOD WHEN NOT IN USE.
	 * @return array
	 */
	function router()
	{
		$ret = [];

		$ret['other_type'] = array( // 'other_type' method will be called when this notification is triggered (see method below)
			'label'			=> "Blank Example", // label used in admin Notify area
			'field'		    => "handle", // rendered in the admin Notify area when this option is selected. see method below.
			'category'		=> ''
		);

		return $ret;
	}

	/**
	 * Custom method used to render a field in Admin Notify area.
	 * REMOVE THIS METHOD WHEN NOT IN USE.
	 * @param string $name Field name.
	 * @param mixed $curVal current value.
	 * @return string
	 */
	function handle($name, $curVal)
	{
		return e107::getForm()->text($name, $curVal, 80, ['size'=>'large','placeholder'=>'eg. account name']);
	}

	/**
	 * Custom Method to handle notification.
	 * REMOVE THIS METHOD WHEN NOT IN USE
	 * @param array $data
	 * @return array
	 */
	function other_type($data)
	{
		return $data; // Peform some other kind of notification and return true on success / false on error.
	}
}


