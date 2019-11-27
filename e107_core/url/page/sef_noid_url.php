<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Custom page routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
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

				'allowVars' 		=> array(  
					'page',
				),
			),

			'rules' => array(
				'chapter/<name:{sefsecureOptional}>' 	=> array('chapter/index',  	'allowVars' => false, 'mapVars' => array('chapter_id'=>'id', 'chapter_sef'=>'name'), 'legacyQuery' => 'ch={id}', 'parseCallback' => 'chapterIdByTitle'),
				'book/<name:{sefsecureOptional}>' 		=> array('book/index',  	'allowVars' => false, 'mapVars' => array('chapter_id'=>'id', 'chapter_sef'=>'name'), 'legacyQuery' => 'bk={id}', 'parseCallback' => 'chapterIdByTitle'),
				'<name:{secure}>' 						=> array('view/index', 		'mapVars' => array('page_id'=>'id', 'page_sef'=>'name'), 'legacyQuery' => '{id}.{page}', 'parseCallback' => 'itemIdByTitle'),
                ### Used for assembling only
                '<other:{secure}>' 						=> array('view/other', 		'mapVars' => array('page_id'=>'id', 'page_sef'=>'other'), 'legacyQuery' => '{id}.{page}', 'parseCallback' => 'itemIdByTitle'),
                '/'										=> array('list/index', 		'allowVars' => false, 'legacyQuery' => '', ), // page list
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
				'name' => LAN_EURL_CORE_PAGE, // Module name
				'label' => LAN_EURL_PAGE_SEFNOID_LABEL, // Current profile name
				'description' => LAN_EURL_PAGE_SEFNOID_DESCR, //
				'examples'  => array("{SITEURL}page/page-title")
			),
			'generate' => array('table'=> 'page', 'primary'=>'page_id', 'input'=>'page_title', 'output'=>'page_sef'),
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

	//	e107::getMessage()->addDebug('name = '.$name);
	//	e107::getMessage()->addDebug(print_r($request,true));
	//	e107::getAdminLog()->toFile('page_sef_noid_url');
		
		if(($id = $request->getRequestParam('id'))) 
		{
			$request->setRequestParam('name', $id);
			return;
		}
		elseif(!$name || is_numeric($name)) 
		{
			if(ADMIN)
			{
				e107::getMessage()->addError("One of your pages is missing a SEF URL value");
			}
			return;
		}
		
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		
		if($sql->select('page', 'page_id', "page_sef='{$name}'")) 
		{
			$name = $sql->fetch();
			$request->setRequestParam('name', $name['page_id'])
                ->setRequestParam('id', $name['page_id']);
		}
		else 
		{
			if(ADMIN)
			{
				e107::getMessage()->addError("Couldn't find a page with a SEF URL value of '".$name."'");
			}
			$request->setRequestParam('name', 0)
                ->setRequestParam('id', 0);
		}
	}
	
	/**
	 * chapter/index and book/index by name callback
	 * @param eRequest $request
	 */
	public function chapterIdByTitle(eRequest $request)
	{	
		$name = $request->getRequestParam('name');
		
		if(($id = $request->getRequestParam('id'))) 
		{
			$request->setRequestParam('name', $id);
			return;
		}
		elseif(!$name || is_numeric($name))
		{
			if(ADMIN)
			{
				e107::getMessage()->addError("One of your page-chapters is missing a SEF URL value");
			}
			return;
		} 
			
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		
		if($sql->select('page_chapters', 'chapter_id', "chapter_sef='{$name}'")) 
		{
			$name = $sql->fetch();
			$request->setRequestParam('id', $name['chapter_id'])
                ->setRequestParam('name', $name['chapter_id']);
		}
		else 
		{
			if(ADMIN)
			{
				e107::getMessage()->addError("Couldn't find a book or chapter with a SEF URL value of '".$name."'");
			}
			$request->setRequestParam('id', 0)
                ->setRequestParam('name', 0);
		}
	}
	
}
