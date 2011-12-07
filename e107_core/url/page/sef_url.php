<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * User routing config
 */
class core_page_sef_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'allowMain'		=> true,
				'noSingleEntry' => false,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}page.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> false,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> false,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'view/index',// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'errorRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found but no inner route is matched, leave empty to force error 404 page
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
			
				'mapVars' 		=> array(  
					'page_id' => 'id', 
					'page_title' => 'name', 
				),
				
				'allowVars' 		=> array(  
					'page', 'id',
				),
			),

			'rules' => array(
				### using only title for pages is risky enough (non-unique title, possible bad characters)
				//'<name:{secure}>' => array('view/index', 'allowVars' => array('name'),'legacyQuery' => '{name}.{page}', 'parseCallback' => 'itemIdByTitle'),
				'<id:{number}>/<name:{secure}>' => array('view/index', 'legacyQuery' => '{id}.{page}', ),
				
				### fallback when assembling method don't know the title of the page - build by ID only
				'<id:{number}>' => array('view/index', 'legacyQuery' => '{id}.{page}', ),
				
				### page list
				'list' => array('list/index', 'legacyQuery' => '', ),
				'/' => array('list/index', 'legacyQuery' => '', ),
			) // rule set array
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
				'name' => LAN_EURL_CORE_PAGE, // Module name
				'label' => LAN_EURL_PAGE_SEF_LABEL, // Current profile name
				'description' => LAN_EURL_PAGE_SEF_DESCR, //
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
		$name = e107::getParser()->toDB($name);var_dump($name);
		if($sql->db_Select('page', 'page_id', "page_theme='' AND page_title='{$name}'")) // TODO - it'll be page_sef (new) field
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('name', $name['page_id']);
		}
		else $request->setRequestParam('name', 0);
	}
}
