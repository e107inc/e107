<?php

if (!defined('e107_INIT')) { exit; }

//e107::lan('theme', 'admin',true);

// Theme Configuration File.
class theme_config implements e_theme_config
{

	function config($type='front')
	{


	}


	function help()
	{

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