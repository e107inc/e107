<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Download Plugin Administration UI
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_plugins/release/includes/admin.php $
 * $Id: admin.php 12212 2011-05-11 22:25:02Z e107coders $
*/

//require_once(e_HANDLER.'admin_handler.php'); - autoloaded - see class2.php __autoload()

// new media_admin();


class plugin_gallery_admin extends e_admin_dispatcher
{
	/**
	 * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'index' => 'list', 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
	 * Note - default mode/action is autodetected in this order:
	 * - $defaultMode/$defaultAction (owned by dispatcher - see below)
	 * - $adminMenu (first key if admin menu array is not empty)
	 * - $modes (first key == mode, corresponding 'index' key == action)
	 * @var array
	 */
	protected $modes = array (
		'main'		=> array (
					'controller' => 'gallery_cat_admin_ui',
					'path' => null,
					'ui' => 'gallery_cat_admin_form_ui',
					'uipath' => null
		),
		'cat'		=> array (
					'controller' 	=> 'gallery_cat_ui',
					'path' 			=> null,
					'ui' 			=> 'gallery_cat_form_ui',
					'uipath' 		=> null
		)	
	);

	/* Both are optional
	protected $defaultMode = null;
	protected $defaultAction = null;
	*/

	/**
	 * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}release/admin_config.php', 'perm' => '0']);
	 * Additionally, any valid e_admin_menu() key-value pair could be added to the above array
	 * @var array
	 */
	protected $adminMenu = array(
	//	'main/list'			=> array('caption'=> LAN_CATEGORY, 'perm' => 'P'),
	//	'main/create' 		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		'main/gallery' 		=> array('caption'=> 'Info', 'perm' => 'P')//, 'url'=>'{e_ADMIN}image.php'
	);


	/**
	 * Optional, mode/action aliases, related with 'selected' menu CSS class
	 * Format: 'MODE/ACTION' => 'MODE ALIAS/ACTION ALIAS';
	 * This will mark active main/list menu item, when current page is main/edit
	 * @var array
	 */
	protected $adminMenuAliases = array(
	///	'main/edit'	=> 'main/list',
	//	'cat/edit'	=> 'cat/list'
	);

	/**
	 * Navigation menu title
	 * @var string
	 */
	protected $menuTitle = 'Gallery';
}


class gallery_cat_admin_ui extends e_admin_ui
{ 	 	 
		protected $pluginTitle	= 'Gallery Categories';
		protected $pluginName	= 'gallery';
		protected $table 		= "core_media_cat";
		protected $pid			= "media_cat_id";
		protected $perPage 		= 10; //no limit
		protected $listOrder = 'media_cat_order';

		protected $listQry = "SELECT * FROM #core_media_cat WHERE media_cat_owner = 'gallery' "; // without any Order or Limit. 
	//	protected $editQry = "SELECT * FROM #faq_info WHERE faq_info_id = {ID}";
	 	 	
		protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
		//	'media_cat_id'			=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'media_cat_image' 		=> array('title'=> LAN_IMAGE,		'type' => 'image', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			
         	'media_cat_owner' 		=> array('title'=> "Owner",			'type' => 'hidden',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'writeParms' =>'value=gallery'),
			'media_cat_category' 	=> array('title'=> LAN_CATEGORY,	'type' => 'hidden',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>TRUE),		
			'media_cat_title' 		=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),
         	'media_cat_diz' 		=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=150&bb=1','readonly'=>FALSE), // Display name
			'media_cat_class' 		=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'width' => 'auto', 'data' => 'int', 'filter'=>TRUE, 'batch'=>TRUE),
			'media_cat_order' 		=> array('title'=> LAN_ORDER,		'type' => 'text',			'width' => '5%', 'thclass' => 'right', 'class'=> 'right' ),								
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
		);
	
		

	public function beforeCreate($new_data)
	{

		$replace = array("_"," ","'",'"',"."); //FIXME Improve
		$new_data['media_cat_category'] = strtolower(str_replace($replace,"",$new_data['media_cat_title']));
		return $new_data;
	}
	
	function galleryPage()
	{
		$mes = e107::getMessage();
		$message = "<b>Gallery</b> is active. Simply import and assign images to the gallery categories using the <a href='".e_ADMIN."image.php'>Media Manager</a>";
		
		$mes->addInfo($message);
	}
		
}

class gallery_cat_admin_form_ui extends e_admin_form_ui
{
	
	// Override the default Options field. 
	
	public function gallery_category_parent($curVal,$mode)
	{
		// TODO - catlist combo without current cat ID in write mode, parents only for batch/filter 
		// Get UI instance
		$controller = $this->getController();
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getDownloadCategoryTree($curVal), false, 'TITLE');
			break;
			
			case 'write':
				return $this->selectbox('gallery_category_parent', $controller->getDownloadCategoryTree(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getDownloadCategoryTree();
			break;
		}
	}
}







class gallery_main_admin_ui extends e_admin_ui
{
		

  	   
}

class gallery_main_admin_form_ui extends e_admin_form_ui
{

	
	
	
}
