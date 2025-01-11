<?php


/*
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * System routing config
 */


class plugin_gallery_url extends eUrlConfig
{

	public function config()
	{
		return array(

			'config' => array(
				'allowMain'    => true,
				'format'       => 'path',    // get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'defaultRoute' => 'index/category', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/

				// false - disable all parameters passed to assemble method by default
				'allowVars'    => array('cat', 'frm'),
			),

			// rule set array
			'rules'  => array(
				'/'    => 'index/category',
				'list' => array('index/list', 'mapVars' => array('media_cat_sef' => 'cat', 'from' => 'frm'), 'allowVars' => array('cat', 'frm'),),
			)
		);
	}

	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance - XXX LANS
		static $admin = array(
			'labels'    => array(
				'name'        => LAN_PLUGIN_GALLERY_TITLE, // Module name
				'label'       => LAN_PLUGIN_GALLERY_SEF_04, // Current profile name
				'description' => LAN_PLUGIN_GALLERY_SEF_03,
				'examples'    => array("{e_PLUGIN_ABS}gallery/?cat=gallery_1")
			),
			'form'      => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);

		return $admin;
	}
}
