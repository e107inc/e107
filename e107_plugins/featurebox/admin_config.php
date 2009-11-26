<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/admin_config.php,v $
|     $Revision: 1.10 $
|     $Date: 2009-11-26 17:15:46 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P") || !plugInstalled('featurebox')) 
{
	header("location:".e_BASE."index.php");
	 exit;
}

include_lan(e_PLUGIN."featurebox/languages/".e_LANGUAGE."_admin_featurebox.php");

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
		'checkboxes'			=> array('title'=> '',				'type' => null, 							'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
		'fb_category_id'		=> array('title'=> LAN_ID,			'type' => 'number',		'data' => 'int',	'width' =>'5%', 'forced'=> TRUE),     		
     	'fb_category_title' 	=> array('title'=> LAN_TITLE,		'type' => 'text',		'data' => 'str',  	'width' => 'auto', 'validate' => 'str', 'rule' => '1-200', 'error' => 'String between 1-200 characters expected', 'help' => 'up to 200 characters', 'thclass' => 'left'), 
		'fb_category_layout' 	=> array('title'=> 'Layout',		'type' => 'dropdown',	'data' => 'str', 	'width' => 'auto', 'thclass' => 'left', 'batch' => true, 'filter' => true),
		'fb_category_random' 	=> array('title'=> 'Random',		'type' => 'boolean',	'data' => 'int', 	'width' => '5%', 'thclass' => 'center', 'class' => 'center', 'batch' => true, 'filter' => true),
		'fb_category_class' 	=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',	'data' => 'int', 	'width' => 'auto'),
		'fb_category_limit' 	=> array('title'=> 'Limit',			'type' => 'number',		'data' => 'int', 	'width' => '5%', 'thclass' => 'left', 'help' => 'number of items to be shown'),
		'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,								'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
	);	
	
	function init()
	{
		// build layout dropdown params
		$templates = array();
		$templates['default'] = 'Default';
		
		$tmp = e107::getFile()->get_files(e_PLUGIN.'featurebox/templates/layout');
		foreach($tmp as $files)
		{
			$key = str_replace('_template.php', '', $files['fname']);
			$templates[$key] = implode(' ', array_map('ucfirst', explode('_', $key))); //TODO add LANS?
		}
		
		// TODO we need something like getFrontTheme()/getAdminTheme() - this will fail on user theme!
		$tmp = e107::getFile()->get_files(e_THEME.e107::getPref('sitetheme').'/featurebox/templates/layout');
		foreach($tmp as $files)
		{
			$key = str_replace('_template.php', '', $files['fname']);
			$templates[$key] = implode(' ', array_map('ucfirst', explode('_', $key))); //TODO add LANS?
		}
		
		$this->fields['fb_category_layout']['readParms'] = $templates;
		$this->fields['fb_category_layout']['writeParms'] = $templates;
	}
	
	/**
	 * User defined pre-delete logic
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
		'checkboxes'		=> array('title'=> '',					'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
		'fb_id'				=> array('title'=> LAN_ID,				'type' => 'int',			'width' =>'5%', 'forced'=> TRUE),
     	'fb_title' 			=> array('title'=> LAN_TITLE,			'type' => 'text',			'width' => 'auto', 'thclass' => 'left first'), 
     	'fb_text' 			=> array('title'=> "Message Text",		'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
			'fb_image' 			=> array('title'=> "Image",				'type' => 'image',			'width' => 'auto', 'thclass' => 'left first'), 
			'fb_imageurl' 		=> array('title'=> "Image Link",		'type' => 'url',			'width' => 'auto', 'thclass' => 'left first'), 			
		'fb_mode' 			=> array('title'=> FBLAN_12,			'type' => 'dropdown',		'data'=> 'int',	'width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
		'fb_class' 			=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',		'data' => 'int', 'width' => 'auto'),	// User id
		'fb_rendertype' 	=> array('title'=> FBLAN_22,			'type' => 'dropdown',		'data'=> 'int',	'width' => 'auto', 'noedit' => TRUE),	
        'fb_template' 		=> array('title'=> FBLAN_25,			'type' => 'dropdown',		'data'=> 'str', 'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'writeParms' => '', 'filter' => true, 'batch' => true),	 	// Photo
			'fb_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',		'data'=> 'int',	'width' => '5%',  'filter'=>TRUE, 'batch'=>TRUE),					
		'fb_order' 			=> array('title'=> LAN_ORDER,			'type' => 'number',			'data'=> 'int','width' => '5%', 'thclass' => 'center' ),	

		'options' 			=> array('title'=> LAN_OPTIONS,			'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
	);
	 
//	protected $fieldpref = array('checkboxes', 'comment_id', 'comment_item_id', 'comment_author_id', 'comment_author_name', 'comment_subject', 'comment_comment', 'comment_type', 'options');
	
		protected $prefs = array( 
		'fb_active'	   				=> array('title'=> 'Allow submitting of fbs by:', 'type'=>'userclass'),
		'submit_question'	   		=> array('title'=> 'Allow submitting of Questions by:', 'type'=>'userclass'),		
		'classic_look'				=> array('title'=> 'Use Classic Layout', 'type'=>'boolean')
	);
	

	
	function init()
	{
		
		$templates = array();
		$categories = array();
		
		$tmp = e107::getTemplate('featurebox', 'featurebox');
		
		foreach($tmp as $key=>$val)
		{
			$templates[$key] = $key; //TODO add LANS?
		}
				
		
		if(e107::getDb()->db_Select('featurebox_category'))
		{
			$categories[0] = LAN_SELECT;
			while ($row = e107::getDb()->db_Fetch())
			{
				$id = $row['fb_category_id'];
				$categories[$id] = $row['fb_category_title'];
			}
		}
				
		$this->fields['fb_category']['writeParms'] 		= $categories;
		$this->fields['fb_template']['writeParms'] 		= $templates;
		$this->fields['fb_rendertype']['writeParms'] 	= array(FBLAN_23,FBLAN_24);
		$this->fields['fb_mode']['writeParms'] 			= array(FBLAN_13,FBLAN_14);
		
		$this->fields['fb_category']['readParms'] 		= $categories;
		$this->fields['fb_template']['readParms'] 		= $templates;
		$this->fields['fb_rendertype']['readParms'] 	= array(FBLAN_23,FBLAN_24);
		$this->fields['fb_mode']['readParms'] 			= array(FBLAN_13,FBLAN_14);			

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