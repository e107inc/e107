<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class calendar_menu_notify extends notify
{		
	function config()
	{
			
		$config = array();
	
		$config[] = array(
			'name'			=> NT_LAN_EC_7,
			'function'		=> "ecalnew",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> NT_LAN_EC_2,
			'function'		=> "ecaledit",
			'category'		=> ''
		);	
		
		return $config;
	}
	
	function ecalnew($data) 
	{
		$message = NT_LAN_EC_3.': '.USERNAME.' ('.NT_LAN_EC_4.': '.$data['ip'].' )<br />';
		$message .= NT_LAN_EC_5.':<br />'.$data['cmessage'].'<br /><br />';
		
		$this->send('ecalnew', NT_LAN_EC_6, $message);
	}

	function ecaledit($data)
	{
		$message = NT_LAN_EC_3.': '.USERNAME.' ('.NT_LAN_EC_4.': '.$data['ip'].' )<br />';
		$message .= NT_LAN_EC_5.':<br />'.$data['cmessage'].'<br /><br />';

		$this->send('ecaledit', NT_LAN_EC_8, $message);
	}
	
}


?>