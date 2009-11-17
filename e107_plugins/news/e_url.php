<?php


// Example only for now. 

class news_url // must match the plugin's folder name. ie. [PLUGIN_FOLDER]_url
{	

	function config()
	{
		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_newspost.php');
		
		$config = array();

		$config[] = array(
			'name'			=> LAN_EURL_MODREWR_TITLE,
			'description' 	=> LAN_EURL_MODREWR_DESCR
		);	
		
		return $config;	
	}
	
	
	function apache_create($parms)
	{
		
	}


	function apache_parse($parms)
	{
		
	}
}

?>