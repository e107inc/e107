<?php


// Example only for now. 

class news_url // must match the plugin's folder name. ie. [PLUGIN_FOLDER]_url
{	

	function config()
	{
		// e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_newspost.php');
		
		$urls = array();

		$urls[] = array(
			'path'				=> "", // default only - should also be configurable from admin->url
			'function'			=> "myfunction",
			'description' 		=> "SEF Urls for Custom-Pages"
		);	
		
		return $this->urls;	
	}
	
	function myfunction($curVal) 
	{
		
		//Simulated  
		$urls = array(
			'welcome-to-e107'	=> "{e_BASE}news.php?extend.1"
		);
		
		return (isset($urls[$curVal])) ? $urls[$curVal] : FALSE;
				
	}
	
	function create($data,$mode='default') // generate a URL from Table Data. 
	{	
		if($mode == 'default')
		{
			if($data['news_id']==1)
			{
				return "{e_BASE}welcome-to-e107";	
			}					
		}	
	}
	
}

?>