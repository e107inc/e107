<?php


/*
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * System routing config
 */


class plugin_gallery_rewrite_url extends eUrlConfig
{

	public function config()
	{
		return array(

			'config' => array(
				'allowMain'    => true,
				'format'       => 'path',
				'defaultRoute' => 'index/category',

				// false - disable all parameters passed to assemble method by default
				'allowVars'    => array('cat', 'frm'),

				// custom assemble/parse URL regex template
				'varTemplates' => array('galleryCat' => '[\w\pL\s\-+.,]+'),
			),

			// rule set array
			'rules'  => array(
				'/'                  => 'index/category',
				// allow only mapped vars - cat and frm parameters to be passed
				'<cat:{galleryCat}>' => array('index/list', 'mapVars' => array('media_cat_sef' => 'cat', 'from' => 'frm')),
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
				'label'       => LAN_PLUGIN_GALLERY_SEF_01, // Current profile name
				'description' => LAN_PLUGIN_GALLERY_SEF_02,
				'examples'    => array('{SITEURL}gallery/my-gallery-title'), //
			),
			'form'      => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);

		return $admin;
	}
}
