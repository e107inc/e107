<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * $Id$
 * 
 * Most balanced config - performance and friendly URLs
 * It contains a lot of examples (mostly complex), use them to play around and learn things :/
 * Generally, things are much more simpler...
 * 
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_news_sef_url extends eUrlConfig
{
	public function config()
	{
		return array(
			'config' => array(
				'legacy' 		=> '{e_BASE}news.php', 	// [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script; override per rule is allowed
				'format'		=> 'path', 				// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'list/items', 		// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'urlSuffix' 	=> '',
				'allowMain' 	=> true,
				
				### default vars mapping (create URL), override per rule is allowed
				'mapVars' 		=> array(  
					'news_id' => 'id', 
					'news_sef' => 'name', 
				),
				
				### match will only check if parameter is empty to invalidate the assembling vs current rule
				'matchValue' => 'empty',	 
				
				### Numerical array containing allowed vars by default (create URL, used for legacyQuery parsing in parse routine as well), 
				### false means - disallow all vars beside those required by the rules
				### Override per rule is allowed
				'allowVars' 	=> false, 
				
				### Best news - you don't need to write one and the same
				### regex over and over again. Even better news - you might avoid
				### writing regex at all! Just use the core regex templates, they 
				### should fit almost every case. 
				### Here is a test custom regex template:
				'varTemplates' => array('testIt' => '[\d]+'),
				
				/*	Predefined Core regex templates, see usage below
				 	'az'					=> '[A-Za-z]+', // NOTE - it won't match non-latin word characters!
					'alphanum'  			=> '[\w\pL]+',
					'sefsecure' 			=> '[\w\pL.\-\s!,]+',
					'secure' 				=> '[^\/\'"\\<%]+',
					'number' 				=> '[\d]+',
					'username' 				=> '[\w\pL.\-\s!,]+', 
				 	'azOptional'			=> '[A-Za-z]{0,}',
					'alphanumOptional'  	=> '[\w\pL]{0,}',
					'sefsecureOptional' 	=> '[\w\pL.\-\s!,]{0,}',
					'secureOptional' 		=> '[^\/\'"\\<%]{0,}',
					'numberOptional' 		=> '[\d]{0,}',
					'usernameOptional' 		=> '[\w\pL.\-\s!,]{0,}', 
				 */
			),
			
			'rules' => array(
				### simple matches first - PERFORMANCE
				'' 							=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'default.0.{page}', ),
				'Category' 					=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'default.0.{page}', ),
				
				## URL with ID and Title - no DB call, balanced performance, name optional
				## Demonstrating the usage of custom user defined regex template defined above - 'testIt' 
				'Category/<id:{testIt}>/<name:{sefsecure}>' => array('list/category', 'allowVars' => array('page'), 'mapVars' => array('category_id' => 'id', 'category_sef' => 'name'), 'legacyQuery' => 'list.{id}.{page}'),
				
				## URL with Title only - prettiest and slowest! Example with direct regex - no templates
				//'Category/<name:{sefsecure}>' => array('list/category', 'allowVars' => array('page'), 'mapVars' => array('category_sef' => 'name'), 'legacyQuery' => 'list.{name}.{page}', 'parseCallback' => 'categoryIdByTitle'),
				
				## URL with ID only - best performance, fallback when no sef name provided
				'Category/<id:{number}>' 		=> array('list/category', 'allowVars' => array('page'), 'legacyQuery' => 'list.{id}.{page}', 'mapVars' => array('category_id' => 'id')),

				### View item requested by id or string, if you remove the catch ALL example, uncomment at least on row from this block
				### leading category name example - could be enabled together with the next example to handle creating of URLs without knowing the category title
				// 'View/<category:[\w\pL.\-\s]+>/<name:[\w\pL.\-\s]+>' => array('view/item', 'mapVars' => array('news_sef' => 'name', 'category_sef' => 'category'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				// to be noted here - value 'name' is replaced by item id within the callback method; TODO replace news_sef with news_sef field
				// 'View/<name:{sefsecure}>' 			=> array('view/item', 'mapVars' => array('news_sef' => 'name', 'news_id' => 'id'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				// 'View/<id:{number}>' 				=> array('view/item', 'mapVars' => array('news_id' => 'id'), 'legacyQuery' => 'extend.{id}'),

				## All news
				'All' => array('list/all', 'allowVars' => array('page'), 'legacyQuery' => 'all.0.{page}'),
				
				## URL with ID and Title - no DB call, balanced performance!
				'Short/<id:{number}>/<name:{sefsecure}>' => array('list/short', 'allowVars' => array('page'), 'mapVars' => array('category_id' => 'id', 'category_sef' => 'name'), 'legacyQuery' => 'cat.{id}.{page}'),
				## fallback when name is not provided	
				'Short/<id:{number}>' => array('list/short', 'allowVars' => array('page'), 'mapVars' => array('category_id' => 'id'), 'legacyQuery' => 'cat.{id}.{page}'),				
				
				// less used after
				//'Brief/<id:[\d]+>' 			=> array('list/short', 'allowVars' => array('page'), 'legacyQuery' => 'cat.{id}.{page}', 'mapVars' => array('category_id' => 'id')),
				'Day/<id:{number}>' 			=> array('list/day', 'allowVars' => array('page'), 'legacyQuery' => 'day.{id}.{page}'),
				'Month/<id:{number}>' 			=> array('list/month', 'allowVars' => array('page'), 'legacyQuery' => 'month.{id}.{page}'),
				//'Year/<id:[\d]+>' 			=> array('list/year', 'allowVars' => array('page'), 'legacyQuery' => 'year.{id}.{page}'), not supported yet
				
				### View news item - kinda catch all - very bad performance when News is chosen as default namespace - two additional DB queries on every site call!
				## Leading category name - uncomment to enable
				//'<category:{sefsecure}>/<name:{sefsecure}>' => array('view/item', 'mapVars' => array('category_sef' => 'category', 'news_sef' => 'name', 'news_id' => 'id'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				'View/<id:{number}>/<category:{sefsecure}>/<name:{sefsecure}>' => array('view/item', 'mapVars' => array('category_sef' => 'category', 'news_sef' => 'name', 'news_id' => 'id'), 'legacyQuery' => 'extend.{id}'),
				// Base location as item view - fallback if category sef is missing
				//'<name:{sefsecure}>' 						=> array('view/item', 'mapVars' => array('news_id' => 'id', 'news_sef' => 'name'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				// fallback if news sef is missing
				'View/<id:{number}>/<name:{sefsecure}>' 		=> array('view/item', 'mapVars' => array('news_id' => 'id', 'news_sef' => 'name'), 'legacyQuery' => 'extend.{id}'),
				
				'View/<id:{number}>' 		=> array('view/item', 'mapVars' => array('news_id' => 'id'), 'legacyQuery' => 'extend.{id}'),
				
				'Tag/<tag:{secure}>' 	=> array('list/tag', 'allowVars' => array('page'), 'legacyQuery' => 'tag={tag}&page={page}'),
			
				'Author/<author:{secure}>' 	=> array('list/author', 'allowVars' => array('page'), 'legacyQuery' => 'author={author}&page={page}'),
			) 
		);
	}
	
	/**
	 * Query mapping in format route?params:
	 * - item/view?id=xxx -> ?extend.id
	 * - list/items[?page=xxx] -> default.0.page
	 * - list/category?id=xxx[&page=xxx] -> list.id.page
	 * - list/category?id=0[&page=xxx] -> default.0.page
	 * - list/short?id=xxx[&page=xxx] -> cat.id.page
	 * - list/day?id=xxx -> ?day-id
	 * - list/month?id=xxx -> ?month-id
	 * - list/year?id=xxx -> ?year-id
	 * - list/nextprev?route=xxx -> PARSED_ROUTE.[FROM] (recursive parse() call)
	 */
	
	
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
				'label' => LAN_EURL_NEWS_REWRITEX_LABEL, // Current profile name
				'description' => LAN_EURL_NEWS_REWRITEX_DESCR, //
				'examples'  => array('{SITEURL}news/1/news-title')
			),
			'generate' => array('table'=> 'news', 'primary'=>'news_id', 'input'=>'news_title', 'output'=>'news_sef'),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
	
	### CUSTOM METHODS ###
	
	/**
	 * view/item by name callback
	 * @param eRequest $request
	 */
	/*public function itemIdByTitle(eRequest $request)
	{
		$name = $request->getRequestParam('name');
		if(($id = $request->getRequestParam('id'))) 
		{
			$request->setRequestParam('name', $id);
			return;
		}
		elseif(!$name) return;
		elseif(is_numeric($name)) 
		{
			return;
		}
		
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		if($sql->db_Select('news', 'news_id', "news_sef='{$name}'")) // TODO - it'll be news_sef (new) field
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('name', $name['news_id']);
		}
		else $request->setRequestParam('name', 0);
	}*/
	
	/**
	 * list/items by name callback
	 * @param eRequest $request
	 */
	/*public function categoryIdByTitle(eRequest $request)
	{
		$name = $request->getRequestParam('name');
		if(($id = $request->getRequestParam('id'))) 
		{
			$request->setRequestParam('name', $id);
			return;
		}
		elseif(!$name) return;
		elseif(is_numeric($name)) 
		{
			return;
		}
		
		$sql = e107::getDb('url');
		$id = e107::getParser()->toDB($name);
		if($sql->db_Select('news_category', 'category_id', "category_sef='{$name}'")) // TODO - it'll be category_sef (new) field
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('name', $name['category_id']);
		}
		else $request->setRequestParam('name', 0);
	}*/
}