<?php

/**
 * Mod rewrite & SEF URLs support, managed entirely by the core router (rules)
 */
class core_news_rewrite_extended_url extends eUrlConfig
{
	public function config()
	{
		return array(
			'config' => array(
				'legacy' 		=> '{e_BASE}news.php', 	// [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script; override per rule is allowed
				'format'		=> 'path', 				// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'list/items', 		// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'legacyQuery'	=> '', 					// [optional] default null; default legacy query string template, parsed (simpleParse) with requestParams values (request object) and GET vars part of allowVars array (rule); override per rule is allowed
				
				### default vars mapping (create URL), override per rule is allowed
				'mapVars' 		=> array(  
					'news_id' => 'id', 
					'news_title' => 'name', 
				),
				
				### Numerical array containing allowed vars by default (create URL, used for legacyQuery parsing in parse routine as well), 
				### false means - disallow all vars beside those required by the rules
				### Override per rule is allowed
				'allowVars' 	=> false, 
			),
			
			'rules' => array(
				### simple matches first - PERFORMANCE
				'' 							=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'default.0.{page}', ),
				'Category' 					=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'default.0.{page}', ),
				
				## URL with ID and Title - no DB call, balanced performance!
				'Category/<id:[\d]+>/<name:[\w\pL.\-\s]+>' => array('list/items', 'allowVars' => array('page'), 'mapVars' => array('category_id' => 'id', 'category_title' => 'name'), 'legacyQuery' => 'list.{id}.{page}'),
				
				## URL with ID only - best performance!
				// 'Category/<id:[\d]+>' 		=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'list.{id}.{page}', 'mapVars' => array('category_id' => 'id')),
				## URL with Title only - prettiest and slowest!
				##'Category/<name:[\w\pL.\-\s]+>' => array('list/items', 'allowVars' => array('page'), 'mapVars' => array('category_title' => 'name'), 'legacyQuery' => 'list.{id}.{page}', 'parseCallback' => 'categoryIdByTitle'),
				
				### View item requested by id or string, if you remove the catch ALL example, uncomment at least on row from this block
				### leading category name example - could be enabled together with the next example to handle creating of URLs without knowing the category title
				// 'View/<category:[\w\pL.\-\s]+>/<name:[\w\pL.\-\s]+>' => array('view/item', 'mapVars' => array('news_title' => 'name', 'category_name' => 'category'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				// to be noted here - value 'name' is replaced by item id within the callback method; TODO replace news_title with news_sef field
				// 'View/<name:[\w\pL.\-\s]+>' 			=> array('view/item', 'mapVars' => array('news_title' => 'name'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				// 'View/<id:[\d]+>' 					=> array('view/item', 'mapVars' => array('news_id' => 'id'), 'legacyQuery' => 'extend.{id}'),

				## URL with ID and Title - no DB call, balanced performance!
				'Short/<id:[\d]+>/<name:[\w\pL.\-\s]+>' => array('list/short', 'allowVars' => array('page'), 'mapVars' => array('category_id' => 'id', 'category_title' => 'name'), 'legacyQuery' => 'list.{id}.{page}'),				
				
				// less used after
				'Brief/<id:[\d]+>' 			=> array('list/short', 'allowVars' => array('page'), 'legacyQuery' => 'cat.{id}.{page}', 'mapVars' => array('news_id' => 'id')),
				'Day/<id:[\d]+>' 			=> array('list/day', 'legacyQuery' => 'day-{id}'),
				'Month/<id:[\d]+>' 			=> array('list/month', 'legacyQuery' => 'month-{id}'),
				'Year/<id:[\d]+>' 			=> array('list/year', 'legacyQuery' => 'year-{id}'),
				
				### View news item - kinda catch all - very bad performance when News is chosen as default namespace - two additional DB queries on every site call!
				// Leading category name - uncomment to enable
				'<category:[\w\pL.\-\s]+>/<name:[\w\pL.\-\s]+>' => array('view/item', 'mapVars' => array('news_title' => 'name', 'category_name' => 'category'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				// Base location as item view - uncomment to enable
				// '<name:[\w\pL.\-\s]+>' 							=> array('view/item', 'mapVars' => array('news_title' => 'name'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				
				
			) 
		);
	}
	
	/**
	 * Query mapping in format route?params:
	 * - item/vew?id=xxx -> ?extend.id
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
			),
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
	public function itemIdByTitle(eRequest $request)
	{
		$name = $request->getRequestParam('name');
		if(!$name || is_numeric($name)) return;
		
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		if($sql->db_Select('news', 'news_id', "news_title='{$name}'")) // TODO - it'll be news_url (new) field
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('name', $name['news_id']);
		}
	}
	
	/**
	 * list/items by name callback
	 * @param eRequest $request
	 */
	public function categoryIdByTitle(eRequest $request)
	{
		$name = $request->getRequestParam('name');
		if(!$name || is_numeric($name)) return;
		
		$sql = e107::getDb('url');
		$id = e107::getParser()->toDB($name);
		if($sql->db_Select('news_category', 'category_id', "category_name='{$name}'")) // TODO - it'll be category_url (new) field
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('name', $name['category_id']);
		}
	}
}