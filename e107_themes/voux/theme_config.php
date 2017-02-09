<?php

if (!defined('e107_INIT')) { exit; }

// Theme Configuration File.
class theme_config implements e_theme_config
{

	function config()
	{

		$brandingOpts = array('sitename'=>'Site Name', 'logo' => 'Logo', 'sitenamelogo'=>'Logo &amp; Site Name');


		$fields = array(
			'branding'          => array('title'=> "Branding", 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $brandingOpts)),
			'nav_alignment'     => array('title'=> "Navbar Alignment", 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('left'=> "Left",'right'=> "Right"))),
			'usernav_placement' => array('title'=> "Signup/Login Placement", 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('top'=> "Top", 'bottom'=> "Bottom"))),
		);

		return $fields;
		
	}

	function help()
	{
	 	return '';
	}
}


?>