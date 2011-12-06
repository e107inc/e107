<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * $Id$
 * 
 * Mod rewrite & SEF URLs support, example of manually (rules-less) created/parsed urls
 */
class core_news_rewrite_url extends eUrlConfig
{
	public function config()
	{
		return array(
			'config' => array(
				'allowMain' 	=> true,
				'noSingleEntry' => false,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}news.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> true,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'list/items', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'urlSuffix' 	=> '.html',		// [optional] default empty; string to append to the URL (e.g. .html)
			),
		);
	}
	
	/**
	 * When returning array, module or it's corresponding alias will be prefixed
	 * Create link so that it can be mapped by the parse() method
	 * - view/item?id=xxx -> news/xxx
	 * - list/items[?page=xxx] -> news[?page=xxx]
	 * - list/category?id=xxx[&page=xxx] -> news/Category/xxx?page=xxx
	 * - list/category?id=0[&page=xxx] -> news?page=xxx
	 * - list/short?id=xxx[&page=xxx] -> news/Short/xxx?page=xxx
	 * - list/category?id=xxx[&page=xxx] -> news?page=xxx
	 * - list/day?id=xxx -> news/Day-id
	 * - list/month?id=xxx -> news/Month-id
	 * - list/year?id=xxx -> news/Year-id
	 * - list/nextprev?route=xxx -> PARSED_ROUTE?page=[FROM] (recursive parse() call)
	 */
	public function create($route, $params = array(), $options = array())
	{
		
		if('--FROM--' != vartrue($params['page'])) $page = varset($params['page']) ? intval($params['page']) : '0';
		else $page = '--FROM--';
		if(!$route) $route = 'list/items';
		
		if(is_string($route)) $route = explode('/', $route, 2);
		$r = array();
		$parm = array();
		
		if($route[0] == 'view')
		{
			## news are passing array as it is retrieved from the DB, map vars to proper values
			if(isset($params['news_id']) && !empty($params['news_id'])) $params['id'] = $params['news_id'];
			if(isset($params['news_title']) && !empty($params['news_title'])) $params['id'] = $params['news_title']; // TODO - news_sef
			
			switch ($route[1]) 
			{
				case 'item':
					$r[0] = $params['id']; // news/ID
				break;
				
				default:
					
				break;
			}
		}
		elseif($route[0] == 'list')
		{
			## news are passing array as it is retrieved from the DB, map vars to proper values
			if(isset($params['category_id']) && !empty($params['category_id'])) $params['id'] = $params['category_id'];
			if(isset($params['category_name']) && !empty($params['category_name'])) $params['name'] = $params['category_name']; // TODO - news_sef
			
			switch ($route[1]) 
			{
				
				case 'items':
						$r[0] = '';
						if($page) $parm = array('page' => $page); // news?page=xxx
				break;
				
				case 'category':
				case 'short':
					if(!vartrue($params['id']))
					{
						$r[0] = '';
						if($page) $parm = array('page' => $page); // news?page=xxx
					}
					else 
					{
						// news/Category/Category-Name?page=xxx
						// news/Short/Category-Name?page=xxx
						$r[0] = $route[1] == 'category' ? 'Category' :  'Short';
						$r[1] = $params['name'] ? $params['name'] : $params['id'];
						if($page) $parm = array('page' => $page);  
					}
				break;
				
				case 'day':
				case 'month':
				case 'year':
					$r = array($route[1], intval($params['id']));
					if($page) $parm = array('page' => $page);
				break;
				
				default:

				break;
			}
		}
		
		if(empty($r)) return false;
			
		return array($r, $parm);
	}
	
	/**
	 * Manually parse request
	 * Pathinfo DOESN'T contain leading 'module' (e.g news or alias 'Blog')
	 * Retruned route shouldn't contain module as well, unless you manipulate $request directly and set $request->routed to true
	 * Mapped URLs:
	 * - news/News-Item -> extend.xxx
	 * - news/Category/Category-Name?page=10 -> list.xxx.10
	 * - news/Day|Month-xxx -> day|month-xxx
	 */
	public function parse($pathInfo, $params, $request, $router, $config)
	{
		$page = $params['page'] ? intval($params['page']) : '0';
		if(!$pathInfo) 
		{
			## this var is used by default from legacy() method
			## you may override legacy() method
			## Keep in mind legacy() is not triggered at all if parse() returns false or $request->routed is set to true
			$this->legacyQueryString = $page ? 'default.0.'.$page : '';
			return $config['defaultRoute'];
		}
		
		## no controller/action pair - news item view - map to extend.xxx
		if(strpos($pathInfo, '/') === false)
		{
			$route = 'view/item';
			$id = is_numeric($pathInfo) ? intval($pathInfo) : $this->itemIdByTitle($pathInfo);
			if(!$id) 
			{
				## let news.php handle missing news item
				$this->legacyQueryString = 'extend.0';
				return $route;
			}
			$this->legacyQueryString = 'extend.'.$id;
			return $route;
		}
		
		$parts = explode('/', $pathInfo, 2);
		
		switch (strtolower($parts[0])) 
		{
			# map to list.xxx.xxx
			case 'short':
			case 'category':
				# Hardcoded leading string for categories, could be pref or LAN constant
				if(!vartrue($parts[1]))
				{
					## force not found as we don't want to have duplicated content (default.0.xxx)
					return false;
				}
				else 
				{
					if(!is_numeric($parts[1])) $id = $this->categoryIdByTitle($parts[1]);
					else $id = intval($parts[1]);
				}
				if(!$id)
				{
					# let news.php handle it
					$id = 0;
				}
				$action = $parts[0] == 'short' ? 'cat' : 'list';
				$this->legacyQueryString = $action.'.'.$id.'.'.$page;
				return 'item/list';
			break;
			
			# could be pref or LAN constant
			case 'day':
				if(!vartrue($parts[1])) $id = 0;
				else $id = intval($parts[1]);
				
				$this->legacyQueryString = 'day.'.$id.'.'.$page;
				return 'list/day';
			break;
			
			# could be pref or LAN constant
			case 'month':
				if(!vartrue($parts[1])) $id = 0;
				else $id = intval($parts[1]);
				
				$this->legacyQueryString = 'month.'.$id.'.'.$page;
				return 'list/month';
			break;
			
			# could be pref or LAN constant - not supported yet
			case 'year':
				if(!vartrue($parts[1])) $id = 0;
				else $id = intval($parts[1]);
				
				$this->legacyQueryString = 'year.'.$id.'.'.$page;
				//return 'list/year';
			break;
			
			# force not found
			default:
				return false;
			break;
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
				'name' => LAN_EURL_CORE_NEWS, // Module name
				'label' => LAN_EURL_NEWS_REWRITE_LABEL, // Current profile name
				'description' => LAN_EURL_NEWS_REWRITE_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
	
	### CUSTOM METHODS ###
	
	//retrieve news_id by Title (XXX - news_sef column, equals to news_title if not set explicit)
	public function itemIdByTitle($id)
	{
		$sql = e107::getDb('url');
		$tp = e107::getParser();
		$id = $tp->toDB($id);
		if($sql->db_Select('news', 'news_id', "news_title='{$id}'")) // TODO - it'll be news_url (new) field
		{
			$id = $sql->db_Fetch();
			return $id['news_id'];
		}
		return false;
	}
	
	//retrieve category_id by Title (XXX - category_sef column, equals to category_name if not set explicit)
	public function categoryIdByTitle($id)
	{
		$sql = e107::getDb('url');
		$tp = e107::getParser();
		$id = $tp->toDB($id);
		if($sql->db_Select('news_category', 'category_id', "category_name='{$id}'")) // TODO - it'll be category_url (new) field
		{
			$id = $sql->db_Fetch();
			return $id['category_id'];
		}
		return false;
	}
}
