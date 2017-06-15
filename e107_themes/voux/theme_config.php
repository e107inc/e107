<?php

if (!defined('e107_INIT')) { exit; }

// Theme Configuration File.
class theme_config implements e_theme_config
{

	function config()
	{

		$brandingOpts = array('sitename'=>'Site Name', 'logo' => 'Logo', 'sitenamelogo'=>'Logo &amp; Site Name');


		$fields = array(
			'branding'          => array('title'=> TPVLAN_95, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> $brandingOpts)),
			'nav_alignment'     => array('title'=> TPVLAN_96, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('left'=> TPVLAN_102,'right'=> TPVLAN_103))),
			'usernav_placement' => array('title'=> TPVLAN_97, 'type'=>'dropdown', 'writeParms'=>array('optArray'=> array('top'=> TPVLAN_104, 'bottom'=> TPVLAN_105))),
		);

		return $fields;
		
	}

	function help()
	{
	 	return '';
	}
}


?>