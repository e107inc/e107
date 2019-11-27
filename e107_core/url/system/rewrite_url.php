<?php
/*
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * System routing config
 */
if (!defined('e107_INIT')){ exit; }  
 
class core_system_rewrite_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'allowMain' 	=> true,
				'format'		=> 'path', 	
				'defaultRoute'	=> 'error/notfound', 
				'errorRoute'	=> 'error/notfound', 

			),
			
			// rule set array
			'rules' => array(
				'error404'		=> 'error/notfound',
				'hello'			=> 'error/hello-world',
				'<controller>/<action>'	=> '<controller>/<action>',
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
				'name' => LAN_EURL_CORE_SYSTEM, // Module name
				'label' => LAN_EURL_SYSTEM_REWRITE_LABEL, // Current profile name
				'description' => LAN_EURL_SYSTEM_REWRITE_DESCR, //
				'examples'  => array("{SITEURL}system/error/404")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
