<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox administration
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/admin_config.php,v $
* $Revision: 1.16 $
* $Date: 2010-01-12 16:09:56 $
* $Author: e107coders $
*
*/
require_once("../../class2.php");
if (!getperms("P") || !plugInstalled('featurebox')) 
{
	header("location:".e_BASE."index.php");
	 exit;
}

e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_admin_featurebox.php');

class fb_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'fb_main_ui',
			'path' 			=> null,
			'ui' 			=> 'fb_admin_form_ui',
			'uipath' 		=> null
		),
		'category'		=> array(
			'controller' 	=> 'fb_category_ui',
			'path' 			=> null,
			'ui' 			=> 'fb_cat_form_ui',
			'uipath' 		=> null
		)					
	);	

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> 'Featurebox List', 'perm' => 'P'),
		'main/create'	=> array('caption'=> 'Create Featurebox Entry', 'perm' => 'P'),
		'category/list' => array('caption'=> 'Categories', 'perm' => 'P'),
		'category/create'	=> array('caption'=> "Create Category", 'perm' => 'P'),
	//	'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list',
		'category/edit'	=> 'category/list'
	);	
	
	protected $menuTitle = 'featurebox';
}

class fb_category_ui extends e_admin_ui
{ 	 	 
	protected $pluginTitle	= 'Feature Box';
	protected $pluginName	= 'featurebox';
	protected $table 		= "featurebox_category";
	protected $pid			= "fb_category_id";
	protected $perPage 		= 0; //no limit
 	 	 	
	protected $fields = array(
		'checkboxes'			=> array('title'=> '',					'type' => null, 							'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center first'),
		'fb_category_id'		=> array('title'=> LAN_ID,				'type' => 'number',		'data' => 'int',	'width' =>'5%', 'forced'=> TRUE),     		
     	'fb_category_icon' 		=> array('title'=> LAN_ICON,			'type' => 'icon',		'data' => 'str', 	'width' => '5%', 'thclass' => 'center', 'class'=>'center'),
		'fb_category_title' 	=> array('title'=> LAN_TITLE,			'type' => 'text',		'data' => 'str',  	'width' => 'auto', 'validate' => 'str', 'rule' => '1-200', 'error' => 'String between 1-200 characters expected', 'help' => 'up to 200 characters', 'thclass' => 'left'), 
		'fb_category_template' 	=> array('title'=> 'Category template',	'type' => 'layouts',	'data' => 'str', 	'width' => 'auto', 'thclass' => 'left', 'writeParms' => 'plugin=featurebox&id=featurebox_category&merge=1', 'filter' => true),
		'fb_category_random' 	=> array('title'=> 'Random',			'type' => 'boolean',	'data' => 'int', 	'width' => '5%', 'thclass' => 'center', 'class' => 'center', 'batch' => true, 'filter' => true),
		'fb_category_class' 	=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',	'data' => 'int', 	'width' => 'auto', 'filter' => true, 'batch' => true),
		'fb_category_limit' 	=> array('title'=> 'Limit',				'type' => 'number',		'data' => 'int', 	'width' => '5%', 'thclass' => 'left', 'help' => 'number of items to be shown, 0 - show all'),
		'options' 				=> array('title'=> LAN_OPTIONS,			'type' => null,								'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
	);	
	
	/**
	 * Prevent deletion of categories in use
	 */
	public function beforeDelete($data, $id)
	{
		if (e107::getDb()->db_Count('featurebox', '(*)', 'fb_category='.intval($id)))
		{
			$this->getTreeModel()->addMessageWarning("Can't delete <strong>{$data['fb_category_title']}</strong> - category is in use!");
			return false;
		}
		return true;
	}
	
	/**
	 * Some default values
	 * TODO - 'default' fields attribute (default value on init)
	 */
	public function beforeCreate($new_data)
	{
		if(!is_numeric($new_data['fb_category_limit']))
		{
			$new_data['fb_category_limit'] = 1;
		}
		if(!varset($new_data['fb_category_template']))
		{
			$new_data['fb_category_template'] = 'default';
		}
		return $new_data;
	}
	
	public function beforeUpdate($new_data)
	{
		if(!varset($new_data['fb_category_template']))
		{
			$new_data['fb_category_template'] = 'default';
		}
		return $new_data;
	}
	
	/**
	 * Create error callback
	 */
	public function onCreateError($new_data, $old_data)
	{
		return $this->_handleUnique($new_data, 'create');
	}
	
	/**
	 * Create error callback
	 */
	public function onUpdateError($new_data, $old_data, $id)
	{
		return $this->_handleUnique($new_data, 'update');
	}
	
	/**
	 * Provide user friendly message on mysql duplicate entry error #1062 
	 * No need of beforeCreate callback and additional SQL query - mysql error number give us
	 * enough info
	 * @return boolean true - suppress model errors
	 */
	protected function _handleUnique($new_data, $mod)
	{
		if($this->getModel()->getSqlErrorNumber() == 1062)
		{
			$templates = e107::getLayouts('featurebox', 'featurebox_category', 'front', '', true, false);
			$msg = e107::getMessage();
			$msg->error('Layout <strong>'.vartrue($templates[$new_data['fb_category_template']], 'n/a').'</strong> is in use by another category. Layout should be unique per category. ');
			$msg->error($mod == 'create' ? LAN_CREATED_FAILED : LAN_UPDATED_FAILED);
			
			return (!E107_DEBUG_LEVEL); // suppress messages (TRUE) only when not in debug mod
		}
		return false;
	}
}

/*class fb_cat_form_ui extends e_admin_form_ui
{
}*/

class fb_main_ui extends e_admin_ui
{
	//TODO Move to Class above. 
	protected $pluginTitle		= 'Feature Box';
	protected $pluginName		= 'featurebox';
	protected $table			= "featurebox";	
	protected $pid 				= "fb_id";
	protected $perPage 			= 10;
	protected $batchDelete 		= true;
	
	protected $fields = array(
		'checkboxes'		=> array('title'=> '',					'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center first', 'class'=>'center'),
		'fb_id'				=> array('title'=> LAN_ID,				'type' => 'number',			'data'=> 'int', 'width' =>'5%', 'forced'=> TRUE),
     	'fb_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',		'data'=> 'int',	'width' => '5%',  'filter'=>TRUE, 'batch'=>TRUE),
		'fb_title' 			=> array('title'=> LAN_TITLE,			'type' => 'text',			'width' => 'auto', 'thclass' => 'left'), 
     	'fb_text' 			=> array('title'=> "Message Text",		'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), 
		//DEPRECATED 'fb_mode' 			=> array('title'=> FBLAN_12,			'type' => 'dropdown',		'data'=> 'int',	'width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
		//DEPRECATED 'fb_rendertype' 	=> array('title'=> FBLAN_22,			'type' => 'dropdown',		'data'=> 'int',	'width' => 'auto', 'noedit' => TRUE),	
        'fb_template' 		=> array('title'=> FBLAN_25,			'type' => 'layouts',		'data'=> 'str', 'width' => 'auto', 'writeParms' => 'plugin=featurebox', 'filter' => true, 'batch' => true),	 	// Photo
		'fb_image' 			=> array('title'=> "Image",				'type' => 'image',			'width' => 'auto'),
		'fb_imageurl' 		=> array('title'=> "Image Link",		'type' => 'url',			'width' => 'auto'),
		'fb_class' 			=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',		'data' => 'int', 'width' => 'auto', 'filter' => true, 'batch' => true),	// User id
		'fb_order' 			=> array('title'=> LAN_ORDER,			'type' => 'number',			'data'=> 'int','width' => '5%' ),
		'options' 			=> array('title'=> LAN_OPTIONS,			'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
	);
	 
	protected $fieldpref = array('checkboxes', 'fb_id', 'fb_category', 'fb_title', 'fb_template', 'fb_class', 'fb_order', 'options');
	
	protected $prefs = array();
	

	
	function init()
	{
		$categories = array();
		if(e107::getDb()->db_Select('featurebox_category'))
		{
			//$categories[0] = LAN_SELECT;
			while ($row = e107::getDb()->db_Fetch())
			{
				$id = $row['fb_category_id'];
				$categories[$id] = $row['fb_category_title'];
			}
		}

		$this->fields['fb_category']['writeParms'] 		= $categories;
		// DEPRECATED
		//$this->fields['fb_rendertype']['writeParms'] 	= array(FBLAN_23,FBLAN_24);
		//$this->fields['fb_mode']['writeParms'] 			= array(FBLAN_13,FBLAN_14);
		
		$this->fields['fb_category']['readParms'] 		= $categories;
		// DEPRECATED
		//$this->fields['fb_rendertype']['readParms'] 	= array(FBLAN_23,FBLAN_24);
		//$this->fields['fb_mode']['readParms'] 			= array(FBLAN_13,FBLAN_14);			

	}
		
}

class fb_admin_form_ui extends e_admin_form_ui
{
	

}

new fb_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>