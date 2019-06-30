<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * $Id$
 * 
 * Default config - create ONLY - old legacy URLs
 * All possible config options added here - to be used as a reference.
 * A good programming practice is to remove all non-used options.
 */
if (!defined('e107_INIT')){ exit; }  

class core_news_url extends eUrlConfig
{
	public function config()
	{
		return array(
			'config' => array(
				'allowMain' 	=> false,	// [optional] default false; disallow this module (while using this config) to be set as site main URL namespace
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}news.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> true,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'list/new',// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'errorRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found but no inner route is matched, leave empty to force error 404 page
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html), not used when format is 'get' or legacy non-empty
				
				###  [optional] used only when assembling URLs via rules(); 
				### if 'empty' - check if the required parameter is empty (results in assemble fail), 
				### if 1 or true - it uses the route pattern to match every parameter - EXTREMELY SLOW, be warned
				'matchValue' => false,	 
				
			
				### [optional] vars mapping (create URL routine), override per rule is allowed
				### Keys of this array will be used as a map for finding values from the provided parameters array.
				### Those values will be assigned to new keys - corresponding values of mapVars array
				### It gives extremely flexibility when used with allowVars. For example we pass $news item array as 
				### it's retrieved from the DB, with no modifications. This gives us the freedom to create any variations of news
				### URLs using the DB data with a single line URL rule. Another aspect of this feature is the simplified code
				### for URL assembling - we just do eRouter::create($theRoute, $newsDbArray)
				### Not used when in selfCreate mod (create url)
				'mapVars' 		=> array(  
					//'news_id' => 'id', 
					//'news_sef' => 'name', 
				),
				
				### [optional] allowed vars definition (create URL routine), override per rule is allowed
				### This numerical array serves as a filter for passed vars when creating URLs
				### Everything outside this scope is ignored while assembling URLs. Exception are route variables.
				### For example: when <id:[\d]+> is present in the route string, there is no need to extra allow 'id'
				### To disallow everything but route variables, set allowVars to false
				### When format is get, false value will disallow everything (no params) and default preserved variables
				### will be extracted from mapVars (if available)
				### Default value is empty array
				### Not used when in selfCreate mod (create url)
				'allowVars' 		=> array(/*'page', 'name'*/),
				
				### Those are regex templates, allowing us to avoid the repeating regex patterns writing in your rules.
				### varTemplates are merged with the core predefined templates. Full list with core regex templates and examples can be found
				### in rewrite_extended news URL config
				'varTemplates' => array(/*'testIt' => '[\d]+'*/),
			),
			
			'rules' => array(), // rule set array - can't be used with format 'get' and noSingleEntry true
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
	public function create($route, $params = array(),$options = array())
	{
		if(!$params) return 'news.php';
		
		if(!$route) $route = 'list/items';
		if(is_string($route)) $route = explode('/', $route, 2);
		if('index' == $route[0])
		{
			$route[0] = 'list';
			$route[1] = 'items';
		}
		elseif('index' == $route[1])
		{
			$route[1] = 'items';
		}
		
	//	return print_a($route,true);
		## news are passing array as it is retrieved from the DB, map vars to proper values
		if(isset($params['news_id']) && !empty($params['news_id'])) $params['id'] = $params['news_id'];
		//if(isset($params['news_sef']) && !empty($params['news_sef'])) $params['id'] = $params['news_sef'];
		//if(isset($params['category_sef']) && !empty($params['category_sef'])) $params['category'] = $params['category_sef'];
		
		$url = 'news.php?';
		if('--FROM--' != vartrue($params['page'])) $page = varset($params['page']) ? intval($params['page']) : '0';
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
					if(!vartrue($params['id']))
					{
						 $url .= 'default.0.'.$page;
					}
					else 
					{
						$url .= 'list.'.$params['id'].'.'.$page;	// 'category_id' would break news_categories_menu. 
					}
				break;
					
				case 'all':
					$url .= 'all.'.$params['id'].'.'.$page;
				break;
				
				case 'tag':
					$url .= 'tag='.$params['tag'].'&page='.$page;
				break;

				case 'author':
					$url .= 'author='.$params['author'].'&page='.$page;
				break;
				
				case 'short':
					$url .= 'cat.'.$params['id'].'.'.$page;
				break;
				
				case 'day':
				case 'month':
				case 'year':
					if($page) $page = '.'.$page;
					$url .= $route[1].'.'.$params['id'].$page;
				break;	
				
				default:
					$url = 'news.php';
				break;
			}
		}
		else 
		{
			$url = 'news.php';
		}
		
		return $url;
	}
	
	public function parse($pathInfo, $params = array(), eRequest $request = null, eRouter $router = null, $config = array())
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
				'label' => LAN_EURL_DEFAULT, // Current profile name
				'description' => LAN_EURL_LEGACY, //
				'examples'  => array("{SITEURL}news.php?extend.1")
			),
		//	'generate' => array('table'=> 'news', 'primary'=>'news_id', 'input'=>'news_title', 'output'=>'news_sef'),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}

