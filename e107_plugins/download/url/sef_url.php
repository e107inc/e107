<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Custom page routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class plugin_download_sef_url extends eUrlConfig
{
	
	public function config()
	{
		return array(
		
			'config' => array(
				'allowMain'		=> true,
				'legacy' 		=> '{e_PLUGIN}download/download.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'list/index',// [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
			
			//	'mapVars' 		=> array(  
			//		'download_id' => 'id', 
			//		'download_sef' => 'name',
					
			//	),
				
				'allowVars' 	=> false
			),

			'rules' => array(
				'Category/<id:{number}>/<name:{sefsecure}>' => array('list/category', 'allowVars'=> array('order','from','view','sort'), 'legacyQuery' => 'action=list&id={id}2&from={from}&view={view}&order={order}&sort={sort}'), // list.{id}.{view}.{order}.{sort}', ),
				'<id:{number}>/<name:{sefsecure}>' 			=> array('view/item', 'legacyQuery' => 'view.{id}' ),
				'Get/<id:{number}>/<name:{sefsecure}>' 		=> array('request/item', 'legacy'=> '{e_PLUGIN}download/request.php', 'legacyQuery' => 'view.{id}' ),
				'/' 										=> array('list/index', 'legacyQuery' => '', ),
				
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
				'name' => LAN_PLUGIN_DOWNLOAD_NAME, // Module name
				'label' => LAN_EURL_FRIENDLY, // Current profile name
				'description' => '', //
				'examples'  => array("{SITEURL}download/view/download-name")
			),
			'generate' => array('table'=> 'download', 'primary'=>'download_id', 'input'=>'download_name', 'output'=>'download_sef'),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
