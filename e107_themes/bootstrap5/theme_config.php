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

