<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * facebookd under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * facebook Plugin Administration UI
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_plugins/facebook/includes/admin.php $
 * $Id: admin.php 12754 2012-05-26 12:21:39Z e107coders $
*/

//require_once(e_HANDLER.'admin_handler.php'); - autoloaded - see class2.php __autoload()
class plugin_facebook_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
						'controller' => 'plugin_facebook_admin_ui',
		 				'path' => null,
		 				'ui' => 'plugin_facebook_admin_form_ui',
		  				'uipath' => null
					)
	);

	protected $adminMenu = array(
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);

	/**
	 * Navigation menu title
	 * @var string
	 */
	protected $menuTitle = 'facebook Menu';
}

class plugin_facebook_admin_ui extends e_admin_ui
{
		// required
		protected $pluginTitle = "e107 facebook";
		protected $pluginName = 'facebook';
	//	protected $table = "facebook";


		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array(
			'appId'		=> array('title'=> 'Facebook AppId', 'type'=>'text', 'data' => 'string', 'validate' => true),
			'appSecret'	=> array('title'=> 'Facebook AppSecret', 'type'=>'text', 'data' => 'string', 'validate' => true),
			
		//	'xfbml' 	=> array('title'=> 'xfbml', 'type' => 'boolean', 'data' => 'int'),
		//	'oauth' 	=> array('title'=> 'oAuth', 'type' => 'boolean', 'data' => 'int')
		);

		// optional
		public function init()
		{
		}
}

class plugin_facebook_admin_form_ui extends e_admin_form_ui
{
	
}
