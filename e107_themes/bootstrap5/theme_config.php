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
		// v2.2.2  
		$bootswatch = array(
			"cerulean"=> 'Cerulean',
			"cosmo"=> 'Cosmo',
            "cyborg"=> 'Cyborg',
            "darkly"=> 'Darkly',
            "flatly"=> 'Flatly',
            "journal"=> 'Journal',
            "litera"=> 'Litera',
            "lumen"=> 'Lumen',
            "lux"=> 'Lux',
            "materia"=> 'Materia', 
            "minty"=> 'Minty', 
            "pulse"=> 'Pulse', 
            "sandstone"=> 'Sandstone',
            "simplex"=> 'Simplex',
            "sketchy"=> 'sketchy', 
            "slate"=> 'Slate',
            "solar"=> 'Solar',
            "spacelab"=> 'Spacelab',
            "superhero"=> 'Superhero',
            "united"=> 'United',
            "yeti"=> 'Yeti',
		);
		
		$previewLink = " <a class='btn btn-default btn-secondary e-modal' data-modal-caption=\"Use the 'Themes' menu to view the selection.\" href='http://bootswatch.com/default/'>".LAN_PREVIEW."</a>";

		return array(
			'bootswatch'        => array('title'=>LAN_THEMEPREF_01, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $bootswatch, 'post'=>$previewLink, 'default'=>LAN_DEFAULT)),
			'cardmenu_look' => array('title' => LAN_THEMEPREF_02, 'type'=>'boolean', 'writeParms'=>array(),'help'=>''),
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

