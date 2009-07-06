<?php

// Dummy Theme Configuration File.

function e107v4a_process()
{
	global $theme_pref;
	$theme_pref['something'] = $_POST['e1074a_something'];
	$theme_pref['something2'] = $_POST['e1074a_something2'];
	save_prefs('theme');
	return "Custom Settings Saved Successfully";
}


function e107v4a_config()
{
	global $theme_pref;

	$var[0]['caption'] = "This is a sample theme configuration page";
	$var[0]['html'] = "<input type='text' name='e1074a_something' value='".$theme_pref['something']."' />";

	$var[1]['caption'] = "Another Example";
	$var[1]['html'] = "<input type='text' name='e1074a_something2' value='".$theme_pref['something2']."' />";

	return $var;
}




?>