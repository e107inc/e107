<?php
if (!defined('e107_INIT')) { exit; }


class _blank_dashboard // include plugin-folder in the name.
{
	
	function chart()
	{
		return false;
	}
	
	
	function status() // Status Panel in the admin area
	{

		$var[0]['icon'] 	= "<img src='' alt='' />";
		$var[0]['title'] 	= "My Title";
		$var[0]['url']		= e_PLUGIN_ABS."_blank/_blank.php";
		$var[0]['total'] 	= 10;

		return $var;
	}	
	
	
	function latest() // Latest panel in the admin area.
	{
		$var[0]['icon'] 	= "<img src='' alt='' />";
		$var[0]['title'] 	= "My Title";
		$var[0]['url']		= e_PLUGIN_ABS."_blank/_blank.php";
		$var[0]['total'] 	= 10;

		return $var;
	}	
	
	
}
?>