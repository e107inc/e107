<?php

if (!defined('e107_INIT')) { exit; }    

$sitetheme = e107::getPref('sitetheme');

e107::themeLan('admin', $sitetheme, true);


class theme_config implements e_theme_config
{

	function __construct()
	{
 
	}


	function config()
	{
		$brandingOpts = array('sitename' => LAN_THEMEPREF_04, 'logo' => LAN_THEMEPREF_05, 'sitenamelogo' => LAN_THEMEPREF_06);
		return array(
			'login_iframe' => array('title' => LAN_THEMEPREF_03, 'type' => 'boolean', 'writeParms' => array(), 'help' => ''),
			'branding'          => array('title' => LAN_THEMEPREF_00, 'type' => 'dropdown', 'writeParms' => array('optArray' => $brandingOpts)),
			'nav_alignment'     => array('title' => LAN_THEMEPREF_01, 'type' => 'radio', 'writeParms' => array('optArray' => array('left' => LAN_THEMEPREF_07, 'right' => LAN_THEMEPREF_08)))
		);

	}

	function help()
	{
		return null; 
	}
	
	function process()
	{
	 	return null;
	}

}

