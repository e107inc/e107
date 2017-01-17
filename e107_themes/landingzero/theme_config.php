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
			'usernav_placement'     => array('title' => 'Signup/Login Placement', 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array('top', 'bottom'), 'useValues'=>1))
		);

		return $fields;

		/*
				$var[1]['caption'] 	= "Image background for video [1920x1080px]";
				$var[1]['html'] 	= "<div class='form-inline'>".$frm->imagepicker('videobackground',e107::pref('theme', 'videobackground'), LAN_DEFAULT, 'help=Header background with video off on bigger devices')."</div>";
				$var[1]['help']		= "";

				$var[2]['caption'] 	= "Image background for mobile devices";
				$var[2]['html'] 	= "<div class='form-inline'>".$frm->imagepicker('videomobilebackground',e107::pref('theme', 'videomobilebackground'), LAN_DEFAULT, 'help=Header background with video off mobile devices')."</div>";
				$var[2]['help']		= "";

				$var[3]['caption'] 	= "First frame of video  [1920x1080px]";
				$var[3]['html'] 	= "<div class='form-inline'>".$frm->imagepicker('videoposter',e107::pref('theme', 'videoposter'), LAN_DEFAULT, 'help=First frame of video, displayed before video start to play')."</div>";
				$var[3]['help']		= "";

				$var[4]['caption'] 	= "URL path to header video in mp4 format";
				$var[4]['html'] 	= "<div class='form-inline'>".$frm->text('videourl',e107::pref('theme', 'videourl'), '250', 'size=block-level')."</div>";
				$var[4]['help']		= "f.e. https://s3-us-west-2.amazonaws.com/coverr/mp4/Traffic-blurred2.mp4";

				$var[5]['caption'] 	= "Signup/Login Placement";
				$var[5]['html'] 	= $frm->select('usernav_placement', array('top', 'bottom'), e107::pref('theme', 'usernav_placement', 'top'),'useValues=1' );
				$var[5]['help']		= "";

				return $var;
		*/

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