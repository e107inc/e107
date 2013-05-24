<?php

/*
* Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Tagwords Default URL configuration
*/
if (!defined('e107_INIT')){ exit; } 

class plugin_tagwords_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'legacy' 		=> '{e_PLUGIN}tagwords/tagwords.php', // this config won't work in single entry point mod (legacy not used at all), so just set this to default plugin file to notify router it's legacy module
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'search/index', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
			),
			
			'allowVars' => array(
				'q', 'page','type', 'sort', 'area',/* 's', 'so',*/
			),
			
			// rule set array
			'rules' => array(
				'<area:{alphanum}>/<q:{secure}>' => 'search/area',
				'<q:{secure}>' => 'search/index',
				'' => 'search/index',
			) 
		);

	}


	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance
		e107::plugLan('tagwords');
		
		static $admin = array(
			'labels' => array(
				'name' => LAN_TAG_URL_NAME, // Module name
				'label' => LAN_TAG_URL_DEFAULT_LABEL, // Current profile name
				'description' => LAN_TAG_URL_DEFAULT_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}

	public function create($route, $params = array(), $options = array())
	{
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[0]) || 'index' == $route[0]) $route[0] = 'tagwords';
		if(!varset($route[1])) $route[1] = 'tagwords';
		$base = e107::getInstance()->getFolder('plugins').'tagwords/';

		if(($route[0] == 'tagwords') || ($route[0] == "search"))
		{
			if(isset($params['q']))
				return $base.'tagwords.php?q='.$params['q'];
			else
				return $base.'tagwords.php';
		}
	}
}
