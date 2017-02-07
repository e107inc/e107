<?php

/*
* Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* PM Default URL configuration
* TODO - SEF URL configuration
*/
if (!defined('e107_INIT')){ exit; } 

class plugin_download_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'allowMain'    => true,
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_PLUGIN}download/download.php', // this config won't work in single entry point mod (legacy not used at all), so just set this to default plugin file to notify router it's legacy module
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'list', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
			),
			
			// rule set array
			'rules' => array() 
		);

	}

	/**
	 * Describe all pm routes. 
	 * Routes vs legacy queries:
	 * list/ 			-> {no query} 
	 * list/category	-> download.php?action=list&id={category-id}
	 * view/item 		-> download.php?action=view&id={download-id}
	 * request/item 	-> request.php?{download-id}
	 */
	public function create($route, $params = array(), $options = array())
	{
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[0]) || 'index' == $route[0]) $route[0] = 'list';
		
		$base = e107::getInstance()->getFolder('plugins').'download/';
		
	//	var_dump($options, $route, $params);
	
//	print_a($route);
	
	
		if($route[0] == 'list')
		{
			if(!isset($params['id']) && isset($params['download_category_id'])) $params['id'] = $params['download_category_id'];
			
			switch($route[1])
			{
				case 'index':		
					$this->legacyQueryString = '';
					return $base.'download.php';	
				break;
						
				case 'category':
					$url = 'action=list&id='.$params['id'];
					
					if(isset($params['from']))
					{
						$url .= "&from=".$params['from']."&view=".$params['view']."&order=".$params['order']."&sort=".$params['sort'];		
					}
					
					$this->legacyQueryString = $url;
					return $base.'download.php?'.$url;
				break;
					
			}
		}
		elseif($route[0] == 'view')
		{
			if(!isset($params['id']) && isset($params['download_id']))
			{
				 $params['id'] = $params['download_id'];
			}
			
			switch($route[1])
			{						
				case 'item':
					$this->legacyQueryString = 'action=view&id='.$params['id'];
					return $base.'download.php?action=view&id='.$params['id'];
				break;
					
			}
		}
		elseif($route[0] == 'request')
		{
			$this->legacyQueryString = $params['id'];
			return $base.'request.php?'.$params['id'];	
		}

		return false;
	}

	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance
		static $admin = array(
			'labels' => array(
				'name'          => LAN_PLUGIN_DOWNLOAD_NAME, // Module name
				'label'         => "Legacy", // Current profile name
				'description'   => LAN_PLUGIN_PM_URL_DEFAULT_DESCR, //
				'examples'      => array("{e_PLUGIN_ABS}download/download.php")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
