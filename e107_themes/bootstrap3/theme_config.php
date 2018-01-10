<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('theme', 'admin',true);

// Theme Configuration File.
class theme_config implements e_theme_config
{

	function config($type='front')
	{

		$brandingOpts = array('sitename'=>LAN_THEMEPREF_04, 'logo' => LAN_THEMEPREF_05, 'sitenamelogo'=>LAN_THEMEPREF_06);

		$bootswatch = array(
			"cerulean"=> 'Cerulean',
			"cosmo"=> 'Cosmo',
            "cyborg"=> 'Cyborg',
            "darkly"=> 'Darkly',
            "flatly"=> 'Flatly',
            "journal"=> 'Journal',
            "lumen"=> 'Lumen',
            "paper"=> 'Paper',
            "readable"=> 'Readable',
            "sandstone"=> 'Sandstone',
            "simplex"=> 'Simplex',
            "slate"=> 'Slate',
            "spacelab"=> 'Spacelab',
            "superhero"=> 'Superhero',
            "united"=> 'United',
            "yeti"=> 'Yeti',
		);


		$previewLink = " <a class='btn btn-default btn-secondary e-modal' data-modal-caption=\"Use the 'Themes' menu to view the selection.\" href='http://bootswatch.com/default/'>".LAN_PREVIEW."</a>";

		$fields = array(
			'branding'          => array('title'=>LAN_THEMEPREF_00, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $brandingOpts)),
			'nav_alignment'     => array('title'=>LAN_THEMEPREF_01, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('left'=> LAN_THEMEPREF_07,'right'=> LAN_THEMEPREF_08))),
			'usernav_placement' => array('title'=>LAN_THEMEPREF_02, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('top'=> LAN_THEMEPREF_09, 'bottom'=> LAN_THEMEPREF_10))),
			'bootswatch'        => array('title'=>LAN_THEMEPREF_03, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $bootswatch, 'post'=>$previewLink, 'default'=>LAN_DEFAULT)),
		);

		return $fields;

	}


	function help()
	{
	 	return '';
	}
}

/*
// Custom Methods
class theme_config_form extends e_form
{

	function custom_method($value,$mode,$parms) // named the same as $fields key.(eg. 'branding') Used when type = 'method'
	{

		return $this->text('custom_method', $value);

	}

}
*/