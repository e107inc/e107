<?php
/*
* e107 website system
*
* Copyright (c) 2008-2014 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox administration
*
*/
require_once("../../class2.php");
if (!getperms("P") || !e107::isInstalled('featurebox')) 
{
	e107::redirect('admin');
	exit;
}

e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_admin_featurebox.php');
// e107::lan('plugin','featurebox',true);

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
		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		'category/list' 	=> array('caption'=> LAN_CATEGORIES, 'perm' => 'P'),
		'category/create'	=> array('caption'=> LAN_CREATE_CATEGORY, 'perm' => 'P'),
		'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list',
		'category/edit'	=> 'category/list'
	);	
	
	protected $menuTitle = 'Feature Box';
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
		'fb_category_title' 	=> array('title'=> LAN_TITLE,			'type' => 'text',		'data' => 'str',  	'inline'=>true, 'width' => 'auto',  'help' => 'up to 200 characters', 'thclass' => 'left', 'writeParms'=>'size=xlarge'), 
		'fb_category_template' 	=> array('title'=> FBLAN_30,	        'type' => 'layouts',	'inline'=>true, 	'data' => 'str', 	'width' => 'auto', 'thclass' => 'left', 'writeParms' => 'plugin=featurebox&id=featurebox_category&merge=1', 'filter' => true),
		'fb_category_random' 	=> array('title'=> FBLAN_31,			'type' => 'boolean',	'data' => 'int', 	'width' => '5%', 'thclass' => 'center', 'class' => 'center', 'batch' => true, 'filter' => true),
		'fb_category_class' 	=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',	'data' => 'int', 	'inline'=>true, 'width' => 'auto', 'filter' => true, 'batch' => true),
		'fb_category_limit' 	=> array('title'=> LAN_LIMIT,			'type' => 'number',		'data' => 'int', 	'width' => '5%', 'thclass' => 'left', 'help' => 'number of items to be shown, 0 - show all'),
		'fb_category_parms' 	=> array('title'=> FBLAN_32,		    'type' => 'textarea',	'data' => 'str', 	'width' => 'auto', 'thclass' => 'left', 'class' => 'left','writeParms' => array('expand'=>LAN_ADVANCED), 'help'=>FBLAN_33),
		
		'options' 				=> array('title'=> LAN_OPTIONS,			'type' => null,								'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
	);
	
	public function init()
	{
		### Prevent modification of the 'unassigned' system category
		if($this->getAction() == 'edit')
		{
			$this->getModel()->load((int) $this->getId());
			
			// FIXME lan
			if($this->getModel()->get('fb_category_template') === 'unassigned')
			{
				e107::getMessage()->addError("<strong>".FBLAN_INSTALL_03."</strong> is system category and can't be modified.", 'default', true);
				$this->redirect('list');
			}
		}
		elseif($this->getAction() == 'inline')
		{
			$this->getModel()->load((int) $this->getId());
			if($this->getModel()->get('fb_category_template') === 'unassigned')
			{
				$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
				header($protocol.': 403 Forbidden', true, 403);
				echo "'".FBLAN_INSTALL_03."' is system category and can't be modified.";
				exit;
			}
		}
	}
	
	/**
	 * Prevent deletion of categories in use
	 */
	public function beforeDelete($data, $id)
	{
		// FIXME lan
		if($data['fb_category_template'] === 'unassigned')
		{
			$this->getTreeModel()->addMessageError("<strong>".FBLAN_INSTALL_03."</strong> is system category and can't be deleted.");
			return false;
		}
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
	protected $batchCopy 		= true;
	protected $sortField		= 'fb_order';
	protected $orderStep 		= 1;
	protected $listOrder 		= 'fb_order asc';
	
	protected $fields = array(
		'checkboxes'		=> array('title'=> '',					'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center first', 'class'=>'center'),
		'fb_id'				=> array('title'=> LAN_ID,				'type' => 'number',			'data'=> 'int', 'width' =>'5%', 'forced'=> TRUE),
     	'fb_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',		'inline'=>true,  'data'=> 'int',	'width' => '10%',  'filter'=>TRUE, 'batch'=>TRUE),
		'fb_title' 			=> array('title'=> LAN_TITLE,			'type' => 'text',			'inline'=>true,  'width' => 'auto', 'thclass' => 'left'), 
    	'fb_image' 			=> array('title'=> FBLAN_26,		'type' => 'image',			'width' => '100px', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParms'=>'size=xxlarge&media=featurebox&video=1'),
	
	 	'fb_text' 			=> array('title'=> FBLAN_08,			'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1','writeParms'=>'template=admin'), 
		//DEPRECATED 'fb_mode' 			=> array('title'=> FBLAN_12,			'type' => 'dropdown',		'data'=> 'int',	'width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
		//DEPRECATED 'fb_rendertype' 	=> array('title'=> FBLAN_22,			'type' => 'dropdown',		'data'=> 'int',	'width' => 'auto', 'noedit' => TRUE),	
        'fb_template' 		=> array('title'=> LAN_TEMPLATE,			'type' => 'layouts',	'inline'=>true,	'data'=> 'str', 'width' => 'auto', 'writeParms' => 'plugin=featurebox&merge=true', 'filter' => true, 'batch' => true),	 	// Photo
		'fb_imageurl' 		=> array('title'=> FBLAN_27,		'type' => 'url',			'width' => 'auto','writeParms'=>'size=xxlarge'),
		'fb_class' 			=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',		'data' => 'int', 'inline'=>true, 'width' => 'auto', 'filter' => true, 'batch' => true),	// User id
		'fb_order' 			=> array('title'=> LAN_ORDER,			'type' => 'number',			'data'=> 'int','width' => '5%' ),
		'options' 			=> array('title'=> LAN_OPTIONS,			'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center', 'readParms'=>'sort=1')
	);
	 
	protected $fieldpref = array('checkboxes', 'fb_id', 'fb_category', 'fb_title', 'fb_template', 'fb_class', 'fb_order', 'options');
	
	protected $prefs = array( 
		'menu_category'	   	=> array('title'=> FBLAN_28, 'type'=>'dropdown', 'help' => FBLAN_29)
	);

	

	
	function init()
	{
		$categories = array();
		if(e107::getDb()->select('featurebox_category'))
		{
			while ($row = e107::getDb()->fetch())
			{
				$id = $row['fb_category_id'];
				$tmpl = $row['fb_category_template'];
				$categories[$id] = $row['fb_category_title'];
				$menuCat[$tmpl] = $row['fb_category_title'];
			}
		}

		$this->fields['fb_category']['writeParms'] 		= $categories;	
		$this->fields['fb_category']['readParms'] 		= $categories;
		
		unset($menuCat['unassigned']);
		
		$this->prefs['menu_category']['writeParms']['optArray'] 	= $menuCat;
		$this->prefs['menu_category']['readParms']['optArray'] 		= $menuCat;

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
