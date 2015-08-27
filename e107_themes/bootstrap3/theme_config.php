<?php

if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme_bootstrap3 implements e_theme_config
{
	function process() // Save posted values from config() fields. 
	{
		$pref = e107::getConfig();
		$tp = e107::getParser();
		
		$theme_pref 					    = array();
		$theme_pref['nav_alignment']	    = $_POST['nav_alignment'];
		$theme_pref['usernav_placement'] 	= $_POST['usernav_placement'];
		$theme_pref['branding'] 	        = $_POST['branding'];

		$pref->set('sitetheme_pref', $theme_pref);
		return $pref->dataHasChanged();
	}

	function config()
	{
		$frm = e107::getForm();

		$brandingOpts = array('sitename'=>'Site Name', 'logo' => 'Logo', 'sitenamelogo'=>'Logo &amp; Site Name');

		$var[0]['caption'] 	= "Branding";
		$var[0]['html'] 	= $frm->select('branding', $brandingOpts, e107::pref('theme', 'branding', 'sitename'));
		$var[0]['help']		= "";

		$var[1]['caption'] 	= "Navbar Alignment";
		$var[1]['html'] 	= $frm->select('nav_alignment', array('left', 'right'), e107::pref('theme', 'nav_alignment', 'left'),'useValues=1' );
		$var[1]['help']		= "";

		$var[2]['caption'] 	= "Signup/Login Placement";
		$var[2]['html'] 	= $frm->select('usernav_placement', array('top', 'bottom'), e107::pref('theme', 'usernav_placement', 'top'),'useValues=1' );
		$var[2]['help']		= "";

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