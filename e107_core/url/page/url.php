<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Custom page routing config
 */
class core_page_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}page.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> true,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'errorRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found but no inner route is matched, leave empty to force error 404 page
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
				'mapVars' 		=> array(),
				'allowVars' 	=> array(),
			),
			
			'rules' => array() // rule set array
		);
	}
	
	/**
	 * 
	 */
	public function create($route, $params = array())
	{
		if(!$params) return 'page.php';
		
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[1])) $route[1] = 'index';
		
		## aliases as retrieved from the DB, map vars to proper values
		if(isset($params['page_title']) && !empty($params['page_title'])) $params['name'] = $params['page_title'];
		if(isset($params['page_id']) && !empty($params['page_id'])) $params['id'] = $params['page_id'];
		
		$url = 'page.php?';
		if('--FROM--' != vartrue($params['page'])) $page = varset($params['page']) ? intval($params['page']) : '0';
		else $page = '--FROM--';
		
		$url .= intval($params['id']).($page ? '.'.$page : '');
		return $url;
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
				'name' => LAN_EURL_CORE_PAGE, // Module name
				'label' => LAN_EURL_PAGE_DEFAULT_LABEL, // Current profile name
				'description' => LAN_EURL_PAGE_DEFAULT_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
	
	public function parse($pathInfo)
	{
		// this config doesn't support parsing, it's done by the module entry script (news.php)
		// this means News are not available via single entry point if this config is currently active
		return false;
	}
}
