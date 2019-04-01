<?php
/*
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * User routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_user_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}user.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
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
	 * Query mapping in format route?params:
	 * - profile/view?id=xxx -> user.php?id.xxx
	 * - profile/list?page=xxx -> user.php?xxx
	 * - myprofile/view -> user.php
	 * - profile/edit?id=xxx -> usersettings.php?xxx
	 * - myprofile/edit -> usersettings.php
	 * - login/index (or just 'login') -> login.php
	 * - register/index (or just 'register') -> signup.php
	 */
	public function create($route, $params = array(), $options = array())
	{
		// Some routes require no params
		//if(!$params) return 'user.php';
		
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[1])) $route[1] = 'index';
		
		## aliases as retrieved from the DB, map vars to proper values
		if(isset($params['user_name']) && !empty($params['user_name'])) $params['id'] = $params['user_name'];
		if(isset($params['user_id']) && !empty($params['user_id'])) $params['id'] = $params['user_id'];
		
		$url = 'user.php';
		$page = vartrue($params['page']) ? intval($params['page']) : '0';
		
		if($route[0] == 'profile')
		{
			// Params required for user view, list & edit
			if(!$params) return 'user.php';

			switch ($route[1]) 
			{
				case '':
				case 'view':
					$url .= '?id.'.$params['id']; 
				break;
				
				case 'list':
					$url .= $page ? '?'.$page : '';
				break;
				
				case 'edit':
					//$url = e_ADMIN_ABS."user.php?mode=main&action=edit&id=".$params['id'];// 'usersettings.php?'.$params['id'];
					$url = e_ADMIN."users.php?mode=main&action=edit&id=".$params['id'];// 'usersettings.php?'.$params['id'];
				break;
			}
		}
		elseif($route[0] == 'myprofile')
		{
			switch ($route[1]) 
			{
				case '':
				case 'view':
					// user.php
				break;
				
				case 'edit':
					$url = 'usersettings.php';
				break;
			}
		}
		elseif($route[0] == 'login')
		{
			$url = 'login.php';
		}
		elseif($route[0] == 'register') $url = 'signup.php'; // XXX signup URL parameters
		
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
				'name'          => LAN_EURL_CORE_USER, // Module name
				'label'         => LAN_EURL_DEFAULT, // Current profile name
				'description'   => LAN_EURL_LEGACY, //
				'examples'      => array("{SITEURL}user.php?id.1")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}

	public function parse($pathInfo, $params = array(), eRequest $request = NULL, eRouter $router = NULL, $config = array())
	{
		// this config doesn't support parsing, it's done by the module entry script (news.php)
		// this means News are not available via single entry point if this config is currently active
		return false;
	}
}
