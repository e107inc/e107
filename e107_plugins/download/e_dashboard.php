<?php

if (!defined('e107_INIT')) { exit; }

class download_dashboard
{
	function chart()
	{
		return false;
	}
	
	function status()
	{
		return false; 
	}	
	
	function latest()
	{
		$sql = e107::getDb();
		$reported_downloads = $sql->count('generic', '(*)', "WHERE gen_type='Broken Download'");
		
		$var[0]['icon'] 	= "<img src='".e_PLUGIN_ABS."download/images/downloads_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='download plugin icon' /> ";
		$var[0]['title'] 	= LAN_DL_LATEST_01;
		$var[0]['url']		= e_PLUGIN."download/admin_download.php?mode=broken&action=list";
		$var[0]['total'] 	= $reported_downloads;

		return $var;
	}	
}