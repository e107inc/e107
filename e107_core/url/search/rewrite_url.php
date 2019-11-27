<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Search routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_search_rewrite_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'legacy' 		=> '{e_BASE}search.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script to be included
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'index/index', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/

			),
			
			// rule set array
			'rules' => array(
				'/'			=> array('index/index', 'defaultVars' => array('id' => 0)),
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
				'name' => LAN_EURL_CORE_SEARCH, // Module name
				'label' => LAN_EURL_SEARCH_REWRITE_LABEL, // Current profile name
				'description' => LAN_EURL_SEARCH_REWRITE_DESCR, //
				'examples'  => array("{SITEURL}search/")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
