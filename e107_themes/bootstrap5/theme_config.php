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

		return array(
		//	'cardmenu_look' => array('title' => LAN_THEMEPREF_02, 'type'=>'boolean', 'writeParms'=>array(),'help'=>''),
			'login_iframe' => array('title' => LAN_THEMEPREF_03, 'type'=>'boolean', 'writeParms'=>array(),'help'=>''),
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

