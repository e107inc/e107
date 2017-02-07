<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('theme', 'admin',true);

class theme_config implements e_theme_config
{

	function __construct()
	{
		e107::themeLan('admin','landingzero',true);
	}


	function config()
	{
		// v2.1.4 format.

		$fields = array(
			'videobackground'       => array('title' => LAN_LZ_THEMEPREF_00, 'type'=>'image', 'help'=>''),
			'videomobilebackground' => array('title' => LAN_LZ_THEMEPREF_01, 'type'=>'image', 'help'=>''),
			'videoposter'           => array('title' => LAN_LZ_THEMEPREF_02, 'type'=>'image', 'help'=>''),
			'videourl'              => array('title' => LAN_LZ_THEMEPREF_03, 'type'=>'text', 'writeParms'=>array('size'=>'xxlarge'),'help'=>''),
			'usernav_placement'     => array('title' => LAN_LZ_THEMEPREF_04, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array('top'=>LAN_LZ_THEMEPREF_05, 'bottom'=>LAN_LZ_THEMEPREF_06))),
		//	'cdn'   		        => array('title' => 'CDN', 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array( 'cdnjs' => 'CDNJS (Cloudflare)', 'jsdelivr' => 'jsDelivr')))
		);

		return $fields;

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


?>
