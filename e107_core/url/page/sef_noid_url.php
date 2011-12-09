<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Custom page routing config
 */
class core_page_sef_noid_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'allowMain'		=> true,
				'legacy' 		=> '{e_BASE}page.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'view/index',// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
			
				'mapVars' 		=> array(  
					'page_id' => 'id', 
					'page_sef' => 'name', 
				),
				
				'allowVars' 		=> array(  
					'page',
				),
			),

			'rules' => array(
			
				### using only title for pages is risky enough (empty sef for old DB's)
				'<name:{secure}>' => array('view/index', 'allowVars' => false, 'legacyQuery' => '{name}.{page}', 'parseCallback' => 'itemIdByTitle'),

				### page list
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
				'label' => LAN_EURL_PAGE_SEFNOID_LABEL, // Current profile name
				'description' => LAN_EURL_PAGE_SEFNOID_DESCR, //
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
		elseif(!$name || is_numeric($name)) return;
		
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		if($sql->db_Select('page', 'page_id', "page_theme='' AND page_sef='{$name}'")) 
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('name', $name['page_id']);
		}
		else $request->setRequestParam('name', 0);
	}
}
