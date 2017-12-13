<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Search routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_search_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}search.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> true,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
			),
			
			'rules' => array() // rule set array
		);
	}
	
	/**
	 * Query mapping
	 */
	public function create($route, $params = array(), $options=array())
	{
		if(!$params) return 'search.php';
		
		return 'search.php?'.eFront::instance()->getRouter()->createPathInfo($params, $options);
	}
	
	/*
	public function parse($pathInfo)
	{
		// this config doesn't support parsing, it's done by the module entry script (search.php)
		// this means Search is not available via single entry point if this config is currently active
		return false;
	}*/
	
	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance
		static $admin = array(
			'labels' => array(
				'name' => LAN_EURL_CORE_SEARCH, // Module name
				'label' => LAN_EURL_DEFAULT, // Current profile name
				'description' => LAN_EURL_LEGACY, //
				'examples'  => array("{SITEURL}search.php")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
