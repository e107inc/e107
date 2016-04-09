<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('theme', 'admin',true); // e_PLUGIN.'tinymce4/languages/'.e_LANGUAGE.'_admin.php'

// Dummy Theme Configuration File.
class theme_bootstrap3 implements e_theme_config
{
	function process() // Save posted values from config() fields. 
	{
		$pref = e107::getConfig();

		$theme_pref 					    = array();
		$theme_pref['nav_alignment']	    = $_POST['nav_alignment'];
		$theme_pref['usernav_placement'] 	= $_POST['usernav_placement'];
		$theme_pref['branding'] 	        = $_POST['branding'];
		$theme_pref['bootswatch'] 	        = $_POST['bootswatch'];

		$pref->set('sitetheme_pref', $theme_pref);
		return $pref->dataHasChanged();
	}

	function config()
	{
		$frm = e107::getForm();

		$brandingOpts = array('sitename'=>'Site Name', 'logo' => 'Logo', 'sitenamelogo'=>'Logo &amp; Site Name');

		$var[0]['caption'] 	= THEME_PREF_00;
		$var[0]['html'] 	= $frm->select('branding', $brandingOpts, e107::pref('theme', 'branding', 'sitename'));
		$var[0]['help']		= "";

		$var[1]['caption'] 	= THEME_PREF_01;
		$var[1]['html'] 	= $frm->select('nav_alignment', array('left', 'right'), e107::pref('theme', 'nav_alignment', 'left'),'useValues=1' );
		$var[1]['help']		= "";

		$var[2]['caption'] 	= THEME_PREF_02;
		$var[2]['html'] 	= $frm->select('usernav_placement', array('top', 'bottom'), e107::pref('theme', 'usernav_placement', 'top'),'useValues=1' );
		$var[2]['help']		= "";


		$bootswatch = array(
		//	''  => LAN_DEFAULT,
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

		$var[3]['caption'] 	= THEME_PREF_03;
		$var[3]['html'] 	= "<div class='form-inline'>".$frm->select('bootswatch', $bootswatch, e107::pref('theme', 'bootswatch', ''),null,LAN_DEFAULT ).$previewLink."</div>";
		$var[3]['help']		= "";




	//	$var[1]['caption'] 	= "Sample configuration field 2";
	//	$var[1]['html'] 	= $frm->text('_blank_example2', e107::pref('theme', 'example2', 'default'));
		
		return $var;
	}

	function help()
	{
	 	return '';
	}
}


?>