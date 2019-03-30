<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Custom page routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_page_sef_url extends eUrlConfig
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
				'<id:{number}>/<name:{sefsecureOptional}>'			=> array('view/index', 		'legacyQuery' => '{id}.{page}', ),
                ### Used for assembling only
                '<id:{number}>/<other:{sefsecureOptional}>'			=> array('view/other', 		'mapVars' => array('page_id'=>'id', 'page_sef'=>'other'), 'legacyQuery' => '{id}.{page}', ),
				'chapter/<id:{number}>/<name:{sefsecureOptional}>' 	=> array('chapter/index', 	'allowVars' => false, 'mapVars' => array('chapter_id'=>'id','chapter_sef'=>'name'), 'legacyQuery' => 'ch={id}' ),
				'book/<id:{number}>/<name:{sefsecureOptional}>' 	=> array('book/index', 		'allowVars' => false, 'mapVars' => array('chapter_id'=>'id','chapter_sef'=>'name'), 'legacyQuery' => 'bk={id}' ),
			
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
				'label' => LAN_EURL_PAGE_SEF_LABEL, // Current profile name
				'description' => LAN_EURL_PAGE_SEF_DESCR, //
				'examples'  => array("{SITEURL}page/1/page-name")
			),
			'generate' => array('table'=> 'page', 'primary'=>'page_id', 'input'=>'page_title', 'output'=>'page_sef'),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
