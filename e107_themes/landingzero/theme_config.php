<?php

if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme_config implements e_theme_config
{

	function config()
	{
		// v2.1.4 format.

		$fields = array(
			'videobackground'       => array('title' => 'Image background for video [1920x1080px]', 'type'=>'image', 'help'=>''),
			'videomobilebackground' => array('title' => 'Image background for mobile devices', 'type'=>'image', 'help'=>''),
			'videoposter'           => array('title' => 'First frame of video  [1920x1080px]', 'type'=>'image', 'help'=>''),
			'videourl'              => array('title' => 'URL path to header video in mp4 format', 'type'=>'text', 'writeParms'=>array('size'=>'xxlarge'),'help'=>''),
			'usernav_placement'     => array('title' => 'Signup/Login Placement', 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array('top', 'bottom'), 'useValues'=>1)),
			'cdn'     							=> array('title' => 'CDN', 'type'=>'dropdown', 
			'writeParms'=>array('optArray'=>array( 'cdnjs' => 'CDNJS (Cloudflare)', 'jsdelivr' => 'jsDelivr' /*, 'local' => 'Local folder'*/)))
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