<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 *
 * @package     e107
 * @subpackage  faqs
 * @version     $Id$
 * @author      e107inc
 *
 *	FAQ plugin admin UI
 */

require_once("../../class2.php");

class faq_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'faq_main_ui',
			'path' 			=> null,
			'ui' 			=> 'faq_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'faq_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'faq_cat_form_ui',
			'uipath' 		=> null
		)					
	);	

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'	=> array('caption'=> LAN_CREATE_ITEM, 'perm' => 'P'),
		'cat/list' 		=> array('caption'=> LAN_CATEGORIES, 'perm' => 'P'),
		'cat/create' 	=> array('caption'=> LAN_CREATE_CATEGORY, 'perm' => 'P'),
		'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'FAQs';
}

class faq_cat_ui extends e_admin_ui
{ 	 	 
		protected $pluginTitle	= LAN_PLUGIN_FAQS_NAME;
		protected $pluginName	= 'plugin';
		protected $table 		= "faqs_info";
		protected $pid			= "faq_info_id";
		protected $perPage 		= 5; //no limit
		protected $listOrder	= 'faq_info_order ASC';
		protected $sortField	= 'faq_info_order';
	//	protected $listQry = "SELECT * FROM #faq_info"; // without any Order or Limit. 
	//	protected $editQry = "SELECT * FROM #faq_info WHERE faq_info_id = {ID}";
	 	 	
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_info_icon' 			=> array('title'=> LAN_ICON,		'type' => 'icon',			'width' => '5%', 'thclass' => 'left' ),	 
			'faq_info_id'				=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE),     		
         	'faq_info_title' 			=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readParms'=>'editable=1'), 
         	'faq_info_about' 			=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'faq_info_parent' 			=> array('title'=> LAN_CATEGORY,	'type' => 'dropdown',		'width' => '5%', 'writeParms'=>''),		
			'faq_info_class' 			=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'width' => 'auto', 'data' => 'int', 'inline'=>true),
			'faq_info_order' 			=> array('title'=> LAN_ORDER,		'type' => 'number',			'width' => '5%', 'thclass' => 'left' ),					
			'options' 					=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);	
	
	protected $categories = array();
	
	public function init()
	{
		$sql = e107::getDb();
		
		$this->categories[0] = "(Root)";
		
		if($sql->select('faqs_info','*', 'faq_info_parent = 0 ORDER BY faq_info_title ASC'))
		{
			while ($row = $sql->fetch())
			{
				$this->categories[$row['faq_info_id']] = $row['faq_info_title'];
			}
		}
		
		$this->fields['faq_info_parent']['writeParms'] = $this->categories;
		
		/*
		if(e_AJAX_REQUEST) // ajax link sorting. 
		{
			$sql = e107::getDb();
			$c=0;
			if(isset($_POST['all']))
			{
				foreach($_POST['all'] as $id)
				{
					$sql->db_Update("faqs_info","faq_info_order = ".intval($c)." WHERE faq_info_id = ".intval($id));
					$c++;		
				}	
			}
			
		
			exit;
		}		
		*/
		
	}	
	/**
	 * Get FAQ Category data
	 *
	 * @param integer $id [optional] get category title, false - return whole array
	 * @param mixed $default [optional] default value if not found (default 'n/a')
	 * @return 
	 */
	function getFaqCategoryTree($id = false, $default = 'n/a')
	{
		// TODO get faq category tree
	}
		
}

class faq_cat_form_ui extends e_admin_form_ui
{
	public function faq_info_parent($curVal,$mode)
	{
		// TODO - catlist combo without current cat ID in write mode, parents only for batch/filter 
		// Get UI instance
		$controller = $this->getController();
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getFaqCategoryTree($curVal), false, 'TITLE');
			break;
			
			case 'write':
				return $this->selectbox('faq_info_parent', $controller->getFaqCategoryTree(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getFaqCategoryTree();
			break;
		}
	}
}

class faq_main_ui extends e_admin_ui
{
		//TODO Move to Class above. 
		protected $pluginTitle		= 'FAQs';
		protected $pluginName		= 'faqs';
		protected $table			= "faqs";
		// without any Order or Limit. 
		
		//FIXME JOIN should occur automatically. We have all the data necessary to build the query. 
		// ie. faq_author is a 'user' field. 
		
		protected $listQry		= "SELECT  f.*, u.* FROM #faqs AS f LEFT JOIN #user AS u ON f.faq_author = u.user_id "; // Should not be necessary.
		
		protected $editQry		= "SELECT * FROM #faqs WHERE faq_id = {ID}";
		
		protected $pid 			= "faq_id";
		protected $perPage 		= 5;
		protected $batchDelete	= true;
		protected $batchCopy	= true;
		protected $listOrder	= 'faq_order ASC';
		protected $sortField	= 'faq_order';
		protected $tabs			= array('FAQs',"Details"); // Simpler method than 'fieldsets'. Allows for easy moving of fields between tabs and works as required by 'news' and 'custom pages'. 
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_id'				=> array('title'=> LAN_ID,			'tab' => 0, 'type' => 'int',			'width' =>'5%', 'forced'=> TRUE),
         	'faq_question' 			=> array('title'=> LANA_FAQ_QUESTION,		'tab' => 0, 'type' => 'text',			'width' => 'auto', 'thclass' => 'left first', 'required'=>TRUE, 'readParms'=>'editable=1'), 
         	'faq_answer' 			=> array('title'=> LANA_FAQ_ANSWER,		'tab' => 0,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=1&truncate=50&bb=1'), 
		 	'faq_parent' 			=> array('title'=> LAN_CATEGORY,	'tab' => 0,	'type' => 'dropdown',		'data'=> 'int', 'inline'=>true,'width' => '10%', 'filter'=>TRUE, 'batch'=>TRUE),		
			'faq_comment' 			=> array('title'=> LANA_FAQ_COMMENT,		'tab' => 1, 'type' => 'userclass',		'data' => 'int',	'width' => 'auto', 'inline'=> true),	// User id
			'faq_datestamp' 		=> array('title'=> LAN_DATE,		'tab' => 1, 'type' => 'datestamp',		'data'=> 'int','width' => 'auto', 'noedit' => false,'writeParms'=>'auto=1'),	
            'faq_author' 			=> array('title'=> LAN_USER,		'tab' => 1, 'type' => 'user',			'data'=> 'int', 'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'writeParms' => 'currentInit=1', 'filter' => true, 'batch' => true, 'nolist' => true	),	 	// Photo
       		'u.user_name' 			=> array('title'=> LANA_FAQ_UNAME,		'tab' => 1, 'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=faq_author&link=1'),	// User name
       		'u.user_loginname' 		=> array('title'=> LANA_FAQ_ULOGINNAME,	'tab' => 1, 'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=faq_author&link=1'),	// User login name
			'faq_order' 			=> array('title'=> LAN_ORDER,		'tab' => 1, 'type' => 'number',			'data'=> 'int','width' => '5%', 'thclass' => 'center','nolist' => false, 'noedit'=>false, 'readParms'=>'editable=1'),	
			'options' 				=> array('title'=> LAN_OPTIONS,				 	'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);
		 
		protected $fieldpref = array('checkboxes', 'faq_question', 'faq_answer', 'faq_parent', 'faq_datestamp', 'options');
		
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'add_faq'	   				=> array('title'=> LANA_FAQ_PREF_1, 'type'=>'userclass', 'help' => 'Under construction'),
			'submit_question'	   		=> array('title'=> LANA_FAQ_PREF_2, 'type'=>'userclass', 'help' => 'Under construction'),		
			'classic_look'				=> array('title'=> LANA_FAQ_PREF_3, 'type'=>'boolean', 'help' => 'Under construction')
		);

	protected $categories = array();

	
	public function init()
	{
			
		$sql = e107::getDb();
		if($sql->select('faqs_info'))
		{
			while ($row = $sql->fetch())
			{
				$this->categories[$row['faq_info_id']] = $row['faq_info_title'];
			}
		}
		
		$this->fields['faq_parent']['writeParms'] = $this->categories;
	}
	
	
	
		
	/**
	 * FAQ categories
	 * @var array
	 */
	

	/**
	 * Get FAQ Category data
	 *
	 * @param integer $id [optional] get category title, false - return whole array
	 * @param mixed $default [optional] default value if not found (default 'n/a')
	 * @return array
	 */
	 /*
	function getFaqCategory($id = false, $default = 'n/a')
	{
		
		if(null === $this->categories) //auto-retrieve on first call
		{
			$sql = e107::getDb();
			if($sql->db_Select('faqs_info'))
			{
				while ($row = $sql->db_Fetch())
				{
					$this->categories[$row['faq_info_id']] = $row['faq_info_title'];
				}
			}
			else
			{
				$this->categories = array(); //prevent PHP warnings
			}
		}
		if(false === $id)
		{
			return $this->categories;
		}
		return vartrue($this->categories[$id], $default);
	}
	  */
		
}

class faq_admin_form_ui extends e_admin_form_ui
{
	/**
	 * faq_parent field method
	 * 
	 * @param integer $curVal
	 * @param string $mode
	 * @return mixed
	 */
	function faq_parent($curVal,$mode)
	{ 
		// Get UI instance
		$controller = $this->getController();
		
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getFaqCategory($curVal), false, 'TITLE');
			break;
			
			case 'write':
				return $this->selectbox('faq_parent', $controller->getFaqCategory(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getFaqCategory();
			break;
		}
	}
}

new faq_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

