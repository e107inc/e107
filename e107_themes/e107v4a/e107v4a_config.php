<?php
if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme_e107v4a implements e_theme_config
{
	function process()
	{
		$pref = e107::getConfig();
		
		$theme_pref = array();
		$theme_pref['example'] = $_POST['e1074a_example'];
		$theme_pref['example2'] = $_POST['e1074a_example2'];

		$pref->set('sitetheme_pref', $theme_pref);
		
		return $pref->dataHasChanged();
	}


	function config()
	{
		global $theme_pref;

		$var[0]['caption'] = "Sample configuration field";
		$var[0]['html'] = "<input type='text' name='e1074a_example' value='".e107::getThemePref('example')."' />";

		$var[1]['caption'] = "Another Example";
		$var[1]['html'] = "<input type='text' name='e1074a_example2' value='".e107::getThemePref('example2')."' />";

		return $var;
	}
	
	function help()
	{
	}
}


?>