<?php

/*
* Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Tagwords Default URL configuration
*/
class plugin_tagwords_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'legacy' 		=> '{e_PLUGIN}tagwords/tagwords.php', // this config won't work in single entry point mod (legacy not used at all), so just set this to default plugin file to notify router it's legacy module
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute'	=> 'search/index', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
			),
			
			'allowVars' => array(
				'q', 'page','type', 'sort', 'area',/* 's', 'so',*/
			),
			
			// rule set array
			'rules' => array(
				'<area:{alphanum}>/<q:{secure}>' => 'search/area',
				'<q:{secure}>' => 'search/index',
				'' => 'search/index',
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
		e107::plugLan('pm', 'url', true);
		static $admin = array(
			'labels' => array(
				'name' => TAGW_LAN_URL_NAME, // Module name
				'label' => TAGW_LAN_URL_DEFAULT_LABEL, // Current profile name
				'description' => TAGW_LAN_URL_DEFAULT_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
