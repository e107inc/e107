<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * $Id$
 * 
 * Full SEF URLs support and the most risky one, almost the same as sef_noid, just working with rules only
 */
 
if (!defined('e107_INIT')){ exit; } 
 
class core_news_sef_full_url extends eUrlConfig
{
	public function config()
	{
		return array(
			'config' => array(
				'allowMain' 	=> true,
				'legacy' 		=> '{e_BASE}news.php', 
				'format'		=> 'path', 
				'defaultRoute'	=> 'list/items', 
				'urlSuffix' 	=> '',
				'allowVars' 	=> false, 
				'matchValue' => 'empty',	
				
				'mapVars' 		=> array(  
					'news_id' => 'id', 
					'news_sef' => 'name', 
				),
			),
			
			'rules' => array(
				'/' 										=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'default.0.{page}', ),
				'Category' 									=> array('list/items', 'allowVars' => array('page'), 'legacyQuery' => 'default.0.{page}', ),
				'Category/<name:{sefsecure}>' 				=> array('list/category', 'allowVars' => array('page'), 'mapVars' => array('category_sef' => 'name'), 'legacyQuery' => 'list.{name}.{page}', 'parseCallback' => 'categoryIdByTitle'),
				'All' 										=> array('list/all', 'allowVars' => array('page'), 'legacyQuery' => 'all.0.{page}'),
				
				'Short/<name:{sefsecure}>' 					=> array('list/short', 	'allowVars' => array('page'), 'mapVars' => array('category_sef' => 'name'), 'legacyQuery' => 'cat.{name}.{page}', 'parseCallback' => 'categoryIdByTitle'),
				'Short/<id:{number}>' 						=> array('list/short', 	'allowVars' => array('page'), 'mapVars' => array('category_id' => 'id'), 'legacyQuery' => 'cat.{id}.{page}'),				
				'Day/<id:{number}>' 						=> array('list/day', 	'allowVars' => array('page'), 'legacyQuery' => 'day.{id}.{page}'),
				'Month/<id:{number}>' 						=> array('list/month', 	'allowVars' => array('page'), 'legacyQuery' => 'month.{id}.{page}'),
				'Tag/<tag:{secure}>' 						=> array('list/tag', 	'allowVars' => array('page'), 'legacyQuery' => 'tag={tag}&page={page}'),
				'Author/<author:{secure}>' 					=> array('list/author', 'allowVars' => array('page'), 'legacyQuery' => 'author={author}&page={$page}'),

				'<category:{sefsecure}>/<name:{sefsecure}>' => array('view/item', 'mapVars' => array('category_sef' => 'category', 'news_sef' => 'name'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				'<name:{sefsecure}>' 						=> array('view/item', 'mapVars' => array('news_id' => 'id', 'news_sef' => 'name'), 'legacyQuery' => 'extend.{name}', 'parseCallback' => 'itemIdByTitle'),
				'<id:{number}>' 							=> array('view/item', 'mapVars' => array('news_id' => 'id'), 'legacyQuery' => 'extend.{id}'),
				
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
		static $admin = array(
		
			'labels' => array(
				'name' => LAN_EURL_CORE_NEWS, // Module name
				'label' => LAN_EURL_NEWS_REWRITEF_LABEL, // Current profile name
				'description' => LAN_EURL_NEWS_REWRITEF_DESCR, //
				'examples'  => array("{SITEURL}news/news-category/news-title","{SITEURL}news/category/news-category")
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
	public function itemIdByTitle(eRequest $request)
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
		if($sql->select('news', 'news_id', "news_sef='{$name}'")) // TODO - it'll be news_sef (new) field
		{
			$name = $sql->fetch();
			$request->setRequestParam('name', $name['news_id']);
		}
		else $request->setRequestParam('name', 0);
	}
	
	/**
	 * list/items by name callback
	 * @param eRequest $request
	 */
	public function categoryIdByTitle(eRequest $request)
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
		if($sql->select('news_category', 'category_id', "category_sef='{$name}'")) // TODO - it'll be category_sef (new) field
		{
			$name = $sql->fetch();
			$request->setRequestParam('name', $name['category_id']);
		}
		else $request->setRequestParam('name', 0);
	}
}
