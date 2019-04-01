<?php
/*
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * System routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_system_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'error/notfound', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/

			), 
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
				'name' => LAN_EURL_CORE_SYSTEM, // Module name
				'label' => LAN_EURL_SYSTEM_DEFAULT_LABEL, // Current profile name
				'description' => LAN_EURL_SYSTEM_DEFAULT_DESCR, //
				'examples'  => array("{SITEURL}?route=system/error/notfound")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
