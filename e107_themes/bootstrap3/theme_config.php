<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('theme', 'admin',true);

// Theme Configuration File.
class theme_config implements e_theme_config
{

	function config($type='front')
	{

		$brandingOpts = array('sitename'=>TPVLAN_99, 'logo' => TPVLAN_100, 'sitenamelogo'=>TPVLAN_101);

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


		$previewLink = " <a class='btn btn-default e-modal' data-modal-caption=\"Use the 'Themes' menu to view the selection.\" href='http://bootswatch.com/default/'>".LAN_PREVIEW."</a>";

		$fields = array(
			'branding'          => array('title'=>TPVLAN_95, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $brandingOpts)),
			'nav_alignment'     => array('title'=>TPVLAN_96, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('left'=> TPVLAN_102,'right'=> TPVLAN_103))),
			'usernav_placement' => array('title'=>TPVLAN_97, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('top'=> TPVLAN_104, 'bottom'=> TPVLAN_105))),
			'bootswatch'        => array('title'=>TPVLAN_98, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $bootswatch, 'post'=>$previewLink, 'default'=>LAN_DEFAULT)),
		);

		return $fields;

	}


	function help()
	{
	 	return '';
	}
}


?>