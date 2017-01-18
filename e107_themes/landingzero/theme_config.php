<?php

if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme_config implements e_theme_config
{

	function config()
	{
		// v2.1.4 format.

		$fields = array(
			'videobackground'       => array('title' => LAN_LZ_THEMEPREF_00, 'type'=>'image', 'help'=>''),
			'videomobilebackground' => array('title' => LAN_LZ_THEMEPREF_01, 'type'=>'image', 'help'=>''),
			'videoposter'           => array('title' => LAN_LZ_THEMEPREF_02, 'type'=>'image', 'help'=>''),
			'videourl'              => array('title' => LAN_LZ_THEMEPREF_03, 'type'=>'text', 'writeParms'=>array('size'=>'xxlarge'),'help'=>''),
			'usernav_placement'     => array('title' => LAN_LZ_THEMEPREF_04, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array(LAN_LZ_THEMEPREF_05, LAN_LZ_THEMEPREF_06), 'useValues'=>1)),
			'cdn'   		=> array('title' => 'CDN', 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array( 'cdnjs' => 'CDNJS (Cloudflare)', 'jsdelivr' => 'jsDelivr')))
		);

		return $fields;

	}

	function help()
	{
	 	return '';
	}
	
	function process()
	{
	 	return '';
	}
}


?>
