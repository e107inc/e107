<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("../../class2.php");

e107::lan('faqs', 'admin',true);
//TODO LANS
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
		'main/pending'	=> array('caption'=> LANA_FAQ_UNANSWERED, 'perm' => 'P', 'uri'=>"admin_config.php?mode=main&action=list&filter=pending"),

		'cat/list' 		=> array('caption'=> LAN_CATEGORIES, 'perm' => 'P'),
		'cat/create' 	=> array('caption'=> LAN_CREATE_CATEGORY, 'perm' => 'P'),
		'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);


	protected $perm = array(
	//	'main/prefs'    => '0'
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = LAN_PLUGIN_FAQS_NAME;

	//
	function init()
	{
	    if(!empty($_GET['filter']))
	    {
			$action = $_GET['filter'];
			$this->adminMenu['main/'.$action]['selected'] = true;
	    }

		$pref = e107::pref('faqs');

		$this->access = array(
			'main/create'   => varset($pref['admin_faq_create'],   e_UC_ADMIN),
			'main/edit'     => varset($pref['admin_faq_edit'],     e_UC_ADMIN),
			'main/delete'   => varset($pref['admin_faq_delete'],   e_UC_ADMIN),
			'cat/list'      => check_class($pref['admin_cat_create']) || check_class($pref['admin_cat_edit']) ? e_UC_ADMIN : e_UC_MAINADMIN,
			'cat/create'    => varset($pref['admin_cat_create'],   e_UC_ADMIN),
			'cat/edit'      => varset($pref['admin_cat_edit'],     e_UC_ADMIN),
			'cat/delete'    => varset($pref['admin_cat_delete'],   e_UC_ADMIN),
		);

	}
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
	 	 	
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_info_icon' 			=> array('title'=> LAN_ICON,		'type' => 'icon',			'width' => '5%', 'thclass' => 'left', 'writeParms'=>'glyphs=1' ),	 
			'faq_info_id'				=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE),     		
         	'faq_info_title' 			=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readParms'=>'editable=1'), 
         	'faq_info_about' 			=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'faq_info_parent' 			=> array('title'=> LAN_CATEGORY,	'type' => 'dropdown',		'width' => '5%', 'writeParms'=>''),		
			'faq_info_class' 			=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'width' => 'auto', 'data' => 'int', 'inline'=>true),
			'faq_info_metad' 			=> array('title'=> LANA_FAQ_METAD,	'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readParms'=>'editable=1'), 
			'faq_info_metak' 			=> array('title'=> LANA_FAQ_METAK,	'type' => 'tags',			'width' => 'auto', 'thclass' => 'left', 'readParms'=>'editable=1'), 				
			'faq_info_sef' 				=> array('title'=> LAN_SEFURL,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'inline'=>true, 'writeParms'=>'size=xxlarge'), 				
	
			'faq_info_order' 			=> array('title'=> LAN_ORDER,		'type' => 'number',			'width' => '5%', 'thclass' => 'left' ),	
			'options' 					=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center','readParms'=>array('sort'=>1))
		);	
	
	protected $categories = array();
	
	public function init()
	{
		$sql = e107::getDb();
		
		$this->categories[0] = "(".LAN_ROOT.")";
		
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

		protected $pluginTitle		= LAN_PLUGIN_FAQS_NAME;
		protected $pluginName		= 'faqs';
		protected $eventName	    = 'faqs';
		protected $table			= "faqs";
		// without any Order or Limit. 
		
		//FIXME JOIN should occur automatically. We have all the data necessary to build the query. 
		// ie. faq_author is a 'user' field. 
		
		protected $listQry		= "SELECT  f.*, u.* FROM #faqs AS f LEFT JOIN #user AS u ON f.faq_author = u.user_id "; // Should not be necessary.
		
		protected $editQry		= "SELECT * FROM #faqs WHERE faq_id = {ID}";
		
		protected $pid 			= "faq_id";
		protected $perPage 		= 10;
		protected $batchDelete	= true;
		protected $batchCopy	= true;
		protected $listOrder	= 'faq_order ASC';
		protected $sortField	= 'faq_order';
		protected $tabs			= array(LANA_FAQ_QUESTION, LAN_DETAILS); // Simpler method than 'fieldsets'. Allows for easy moving of fields between tabs and works as required by 'news' and 'custom pages'. 
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	protected $fields = array(
			'checkboxes'			=> array('title'=> '',					            'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_id'				=> array('title'=> LAN_ID,				'tab' => 0, 'type' => null,				'width' =>'5%', 'forced'=> TRUE),
			'faq_question' 			=> array('title'=> LANA_FAQ_QUESTION,	'tab' => 0, 'type' => 'text',			'width' => 'auto', 'thclass' => 'left first', 'required'=>TRUE, 'readParms'=>'editable=1', 'writeParms'=>'maxlength=1000&size=block-level'),
			'faq_answer' 			=> array('title'=> LANA_FAQ_ANSWER,		'tab' => 0, 'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=1&truncate=50&bb=1'), 
		 	'faq_parent' 			=> array('title'=> LAN_CATEGORY,		'tab' => 0, 'type' => 'dropdown',		'data'=> 'int', 'inline'=>true,'width' => '10%', 'filter'=>TRUE, 'batch'=>TRUE),

			'faq_tags' 				=> array('title'=> LANA_FAQ_TAGS,		'tab' => 1, 'type' => 'tags',			'data' => 'str',	'width' => 'auto', 'inline'=> true, 'help' => LANA_FAQ_TAGS_HELP),	// User id
			'faq_comment' 			=> array('title'=> LANA_FAQ_COMMENT,	'tab' => 1, 'type' => 'userclass',		'data' => 'int',	'width' => 'auto', 'inline'=> true),	// user class who can make comments
			
			'faq_datestamp' 		=> array('title'=> LAN_DATE,			'tab' => 1, 'type' => 'datestamp',		'data'=> 'int','width' => 'auto', 'noedit' => false,'writeParms'=>'type=datetime&auto=1'),
			'faq_author'			=> array('title'=> LAN_AUTHOR,			'tab' => 1, 'type' => 'user',			'data'=> 'int', 'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'writeParms' => 'currentInit=1', 'filter' => true, 'batch' => true, 'nolist' => true	),	 	// Photo
			'faq_author_ip' 		=> array('title'=> LAN_IP,				'tab' => 1, 'type' => 'ip',				'readonly'=>2, 'data'=> 'str', 'width' => 'auto', 'thclass' => 'center', 'class'=>'center', 'writeParms' => 'currentInit=1', 'filter' => true, 'batch' => true, 'nolist' => true	),

			'u.user_name'			=> array('title'=> LAN_USER,			'tab' => 1, 'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=faq_author&link=1'),	// User name
			'u.user_loginname'		=> array('title'=> LANA_FAQ_ULOGINNAME,	'tab' => 1, 'type' => 'user',			'width' => 'auto', 'noedit' => true, 'readParms'=>'idField=faq_author&link=1'),	// User login name
			'faq_order' 			=> array('title'=> LAN_ORDER,			'tab' => 1, 'type' => 'number',			'data'=> 'int','width' => '5%', 'thclass' => 'center','nolist' => false, 'noedit'=>false, 'readParms'=>'editable=1'),
			
			'options' 				=> array('title'=> LAN_OPTIONS,			'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>array('sort'=>1)),
			'pending'				=> array('title' => 'internal',			'type' => 'hidden',			'data'=>false, 'writeParms'=>array()),
		);
		 
		protected $fieldpref = array('checkboxes', 'faq_question', 'faq_answer', 'faq_parent', 'faq_datestamp', 'options');

		protected $preftabs				= array(LAN_GENERAL, LAN_FAQS_ASK_A_QUESTION, LANA_FAQ_PREF_22, LANA_FAQ_PREF_23);
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'add_faq'					=> array('title'=> LANA_FAQ_PREF_1, 'tab'=>1, 'type'=>'userclass' ),
			'submit_question'	   		=> array('title'=> LANA_FAQ_PREF_2, 'tab'=>1, 'type'=>'userclass' ),
			'submit_question_limit'		=> array('title'=> LANA_FAQ_PREF_4, 'tab'=>1, 'type'=>'number', 'data'=>'int', 'help'=>LANA_FAQ_PREF_HELP_1),
			'submit_question_char_limit'=> array('title'=> LANA_FAQ_PREF_5, 'tab'=>1, 'type'=>'number', 'data'=>'int', 'help'=>LANA_FAQ_PREF_HELP_1, 'writeParms'=>array('max'=>255, 'default'=>255)),
			'submit_question_language'	=> array('title'=> LANA_FAQ_PREF_6, 'tab'=>1, 'type'=>'dropdown' ),
			'submit_question_acknowledgement'=> array('title'=> LANA_FAQ_PREF_7, 'tab'=>1, 'type'=>'textarea', 'help'=>LANA_FAQ_PREF_HELP_2),
//new display tab
			'classic_look'				=> array('title'=> LANA_FAQ_PREF_3,  'tab'=>0, 'type'=>'boolean' ),
			'list_type'					=> array('title'=> LANA_FAQ_PREF_8,  'tab'=>0, 'type'=>'dropdown', 'writeParms'=>array('ul'=>LANA_FAQ_PREF_9, 'ol'=>LANA_FAQ_PREF_10)),
			'page_title'				=> array('title'=> LANA_FAQ_PREF_11, 'tab'=>0, 'type'=>'text', 'multilan'=>true, 'help'=>LANA_FAQ_PREF_HELP_2),
			'new'						=> array('title'=> LANA_FAQ_PREF_12, 'tab'=>0, 'type'=>'number', 'writeParms'=>'size=mini&default=0&post=days old', 'help'=>LANA_FAQ_PREF_HELP_2),
			'display_total'				=> array('title'=> LANA_FAQ_PREF_13, 'tab'=>0, 'type'=>'boolean', 'data'=>'int' ),
			'display_datestamp'			=> array('title'=> LANA_FAQ_PREF_14, 'tab'=>0, 'type'=>'boolean', 'data'=>'int' ),
			'display_social'			=> array('title'=> LANA_FAQ_PREF_15, 'tab'=>0, 'type'=>'boolean', 'data'=>'int' ),
			'orderby'					=> array('title'=> LAN_ORDER,        'tab'=>0, 'type'=>'dropdown', 'writeParms'=>array('faq_order-ASC'=>LANA_FAQ_PREF_16, 'faq_id-ASC'=>LANA_FAQ_PREF_18, 'faq_id-DESC'=>LANA_FAQ_PREF_19, 'faq_datestamp-ASC'=>LANA_FAQ_PREF_20, 'faq_datestamp-DESC'=>LANA_FAQ_PREF_21)),

			'admin_faq_create'			=> array('title'=> LAN_CREATE_ITEM,  'tab'=>2, 'type'=>'userclass', 'writeParms'=>'default=254&classlist=main,admin,classes,no-excludes' ),
			'admin_faq_edit'			=> array('title'=> LAN_EDIT,         'tab'=>2, 'type'=>'userclass', 'writeParms'=>'default=254&classlist=main,admin,classes,no-excludes' ),
			'admin_faq_delete'			=> array('title'=> LAN_DELETE,       'tab'=>2, 'type'=>'userclass', 'writeParms'=>'default=254&classlist=main,admin,classes,no-excludes' ),

			'admin_cat_create'			=> array('title'=> LAN_CREATE_CATEGORY, 'tab'=>3, 'type'=>'userclass' , 'writeParms'=>'default=254&classlist=main,admin,classes,no-excludes' ),
			'admin_cat_edit'			=> array('title'=> LAN_EDIT,            'tab'=>3, 'type'=>'userclass' , 'writeParms'=>'default=254&classlist=main,admin,classes,no-excludes' ),
			'admin_cat_delete'			=> array('title'=> LAN_DELETE,          'tab'=>3, 'type'=>'userclass' , 'writeParms'=>'default=254&classlist=main,admin,classes,no-excludes' ),
		);

	protected $categories = array();

	
	public function init()
	{
		$this->prefs['submit_question_language']['writeParms'] = array(0=> 'English', 1=>LANA_FAQ_PREF_17);//TODO - Site Language? 


		$sql = e107::getDb();
		if($sql->select('faqs_info'))
		{
			while ($row = $sql->fetch())
			{
				$this->categories[$row['faq_info_id']] = $row['faq_info_title'];
			}
		}

		$faqOrder = e107::pref('faqs','orderby');

		if(!empty($faqOrder))
		{
			list($sortField,$sortASC) = explode("-",$faqOrder);
			$this->listOrder = $sortField." ".$sortASC;

			if($sortField != 'faq_order')
			{
				$this->fields['options']['readParms']['sort'] = 0;
			}

		}


		$this->fields['faq_parent']['writeParms'] = $this->categories;

		//$this->fields['pending']['writeParms']['show'] = 1;
		$this->fields['pending']['writeParms']['value'] = ($_GET['filter'] == 'pending') ? 1 : 0;

		if(!empty($_GET['filter'])) // hide re-ordering when looking at 'unanswered' list and sort by datestamp.
		{
			$this->listQry .= " WHERE f.faq_parent = 0 ";
			$this->listOrder = "faq_datestamp ASC";
			$this->fields['options']['readParms'] = '';
			$this->sortField = false;
		}
		else
		{
			$this->listQry .= " WHERE f.faq_parent != 0 ";
		}

	}
	
	public function beforeCreate($new_data, $old_data)
	{
		// trim spaces
		if(!empty($new_data['faq_tags']))
		{
			$new_data['faq_tags'] = implode(',', array_map('trim', explode(',', $new_data['faq_tags'])));
		}

		$new_data['faq_order'] = 0;

		return $new_data;
	}
	
	
	public function beforeUpdate($new_data, $old_data, $id)
	{
		// trim spaces
		if(!empty($new_data['faq_tags']))
		{
			$new_data['faq_tags'] = implode(',', array_map('trim', explode(',', $new_data['faq_tags'])));
		}

	//	e107::getMessage()->addInfo(print_a($new_data, true));

		if(!empty($new_data['pending']))
		{
			$new_data['faq_datestamp'] = time();
		}

		return $new_data;
	}
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

				return $this->selectbox('faq_parent', $controller->getFaqCategory(), $curVal).$this->hidden('pending', $pending);
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

