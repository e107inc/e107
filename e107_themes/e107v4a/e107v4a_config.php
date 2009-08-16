<?php

// Dummy Theme Configuration File.
class theme_e107v4a
{
	function process()
	{
		global $theme_pref;
		$theme_pref['example'] = $_POST['e1074a_example'];
		$theme_pref['example2'] = $_POST['e1074a_example2'];
		save_prefs('theme');
		return "Custom Settings Saved Successfully";
	}


	function config()
	{
		global $theme_pref;

		$var[0]['caption'] = "Sample configuration field";
		$var[0]['html'] = "<input type='text' name='e1074a_example' value='".$theme_pref['example']."' />";

		$var[1]['caption'] = "Another Example";
		$var[1]['html'] = "<input type='text' name='e1074a_example' value='".$theme_pref['example2']."' />";

		return $var;
	}
}



?>