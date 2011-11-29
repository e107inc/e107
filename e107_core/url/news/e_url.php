<?php

/**
 * Default config - create ONLY - old legacy URLs
 */
class core_news_url extends eUrlConfig
{
	public function config()
	{
		return array(
			'config' => array(
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}news.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> true,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'errorRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found but no inner route is matched, leave empty to force error 404 page
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
			),
			
			'rules' => array() // rule set array
		);
	}
	
	/**
	 * If create returns string, 'module' value won't be prefixed from the router
	 * Query mapping in format route?params:
	 * - view/item?id=xxx -> ?extend.id
	 * - list/items[?page=xxx] -> default.0.page
	 * - list/category?id=xxx[&page=xxx] -> list.id.page
	 * - list/category?id=0[&page=xxx] -> default.0.page
	 * - list/short?id=xxx[&page=xxx] -> cat.id.page
	 * - list/day?id=xxx -> ?day-id
	 * - list/month?id=xxx -> ?month-id
	 * - list/year?id=xxx -> ?year-id
	 * - list/nextprev?route=xxx -> PARSED_ROUTE.[FROM] (recursive parse() call)
	 */
	public function create($route, $params = array())
	{
		if(!$params) return 'news.php';
		
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[1])) $route[1] = '';
		
		## news are passing array as it is retrieved from the DB, map vars to proper values
		if(isset($params['news_id']) && !empty($params['news_id'])) $params['id'] = $params['news_id'];
		if(isset($params['news_sef']) && !empty($params['news_sef'])) $params['id'] = $params['news_sef'];
		if(isset($params['category_name']) && !empty($params['category_name'])) $params['category'] = $params['category_name'];
		
		$url = 'news.php?';
		if('--FROM--' != $params['page']) $page = $params['page'] ? intval($params['page']) : '0';
		else $page = '--FROM--';

		if($route[0] == 'view')
		{
			switch ($route[1]) 
			{
				case 'item':
					$url .= 'extend.'.$params['id']; //item.* view is deprecated
				break;
				
				default:
					$url = 'news.php';
				break;
			}
		}
		elseif($route[0] == 'list')
		{
			switch ($route[1]) 
			{
				case '':
				case 'items':
					if(!$page) $url = 'news.php';
					else $url .= 'default.0.'.$page; //item.* view is deprecated
				break;
				
				case 'category':
					if(!vartrue($params['id'])) $url .= 'default.0.'.$page;
					else $url .= 'list.'.$params['id'].'.'.$page;
				break;
				
				case 'short':
					$url .= 'cat.'.$params['id'].'.'.$page;
				break;
				
				case 'day':
				case 'month':
				case 'year':
					$url .= $route[1].'-'.$params['id'];
				break;
				
				case 'nextprev':
					$route = $params['route'];
					unset($params['route']);
					if($route != 'list/nextprev')
					{
						$params['page'] = '[FROM]';
						$url = $this->create($route, $params);
						unset($tmp);
					}
				break;
				
				
				default:
					$url = 'news.php';
				break;
			}
		}
		else $url = 'news.php';
		
		return $url;
	}
	
	public function parse($request)
	{
		// this config doesn't support parsing, it's done by the module entry script (news.php)
		// this means News are not available via single entry point if this config is currently active
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
				'name' => LAN_EURL_CORE_NEWS, // Module name
				'label' => LAN_EURL_NEWS_DEFAULT_LABEL, // Current profile name
				'description' => LAN_EURL_NEWS_DEFAULT_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}

