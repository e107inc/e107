<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/faqs/admin_config.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-11-09 16:54:28 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) 
{
	header("location:".e_BASE."index.php");
	exit;
}

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
		//	'ui' 			=> 'faq_cat_form_ui',
			'uipath' 		=> null
		)					
	);	

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> 'FAQs', 'perm' => '0'),
		'main/create'	=> array('caption'=> 'Create FAQ', 'perm' => '0'),
		'cat/list' 		=> array('caption'=> 'Categories', 'perm' => '0'),
		'cat/create' 	=> array('caption'=> "Create New Cat.", 'perm' => '0'),
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
		protected $pluginTitle	= 'FAQs';
		protected $pluginName	= 'plugin';
		protected $table 		= "faqs_info";
		protected $pid			= "faq_info_id";
	//	protected $perPage = 10;
	//	protected $listQry = "SELECT * FROM #faq_info"; // without any Order or Limit. 
	//	protected $editQry = "SELECT * FROM #faq_info WHERE faq_info_id = {ID}";
	 	 	
		protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_info_icon' 			=> array('title'=> LAN_ICON,		'type' => 'icon',			'width' => '5%', 'thclass' => 'left' ),	 
			'faq_info_id'				=> array('title'=> LAN_ID,			'type' => 'int',			'width' =>'5%', 'forced'=> TRUE),     		
         	'faq_info_title' 			=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left'), 
         	'faq_info_about' 			=> array('title'=> LAN_DESCRIPTION,	'type' => 'textarea',		'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'faq_info_parent' 			=> array('title'=> LAN_CATEGORY,	'type' => 'text',			'width' => '5%'),		
			'faq_info_class' 			=> array('title'=> LAN_VISIBILE,	'type' => 'userclass',		'data' => 'int',	'width' => 'auto'),
			'faq_info_order' 			=> array('title'=> LAN_ORDER,		'type' => 'text',			'width' => '5%', 'thclass' => 'left' ),					
			'options' 					=> array('title'=> LAN_OPTIONS,		'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);	
}


class faq_main_ui extends e_admin_ui
{
		//TODO Move to Class above. 
		protected $pluginTitle		= 'FAQs';
		protected $pluginName		= 'faqs';
		protected $table			= "faqs";
		
		protected $listQry			= "SELECT  * FROM #faqs"; // without any Order or Limit. 
		
		protected $editQry			= "SELECT * FROM #faqs WHERE faq_id = {ID}";
		
		protected $pid = "faq_id";
		protected $perPage = 10;
		protected $batchDelete = true;
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'faq_id'				=> array('title'=> LAN_ID,			'type' => 'int',			'width' =>'5%', 'forced'=> TRUE),
       		
         	'faq_question' 			=> array('title'=> "Question",		'type' => 'text',			'data'=> 'str',  'width' => 'auto', 'thclass' => 'left first'), // Display name
         	'faq_answer' 			=> array('title'=> "Answer",		'type' => 'bbarea',		'data'=> 'str','width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'faq_parent' 			=> array('title'=> "Category",		'type' => 'method',			'data'=> 'int','width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
			'faq_comment' 			=> array('title'=> "Comment",		'type' => 'userclass',		'data' => 'int',	'width' => 'auto'),	// User id
			'faq_datestamp' 		=> array('title'=> "datestamp",		'type' => 'datestamp',		'data'=> 'int','width' => 'auto'),	// User date
            'faq_author' 			=> array('title'=> LAN_USER,		'type' => 'user',		'data'=> 'int', 'thclass' => 'center', 'class'=>'center', 'filter' => true, 'batch' => true,	'width' => 'auto'),	 	// Photo
			'faq_order' 			=> array('title'=> "Order",			'type' => 'int',			'data'=> 'int','width' => '5%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);
		 
	//	protected $fieldpref = array('checkboxes', 'comment_id', 'comment_item_id', 'comment_author_id', 'comment_author_name', 'comment_subject', 'comment_comment', 'comment_type', 'options');
		
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'add_faq'	   				=> array('title'=> 'Allow submitting of FAQs by:', 'type'=>'userclass'),
			'submit_question'	   		=> array('title'=> 'Allow submitting of Questions by:', 'type'=>'userclass'),		
			'classic_look'				=> array('title'=> 'Use Classic Layout', 'type'=>'boolean')
		);
		
}

//TODO Block and Unblock buttons, moderated comments?
class faq_admin_form_ui extends e_admin_form_ui
{
	var $categories = array('hello','hello2','hello3');
	
	//FIXME Breaks everything!!! 
	/*
	function __construct()
	{
		$sql = e107::getDb();
		$sql->db_Select('faq_info');
		while ($row = $sql->db_Fetch())
		{
			$id = $row['faq_info_id'];
			$this->categories[$id] = $row['faq_info_title'];
			echo "hello";
		}	
	}
	*/
	
	
	function faq_parent($curVal,$mode)
	{ 
		if($mode == 'read')
		{
			return $curVal.' (custom!)';
		}
		
		if($mode == 'filter') // Custom Filter List for release_type
		{
			return array(1=>'Category 1',2=>'Category 2',3=>'Category 3');
		}
		
		if($mode == 'batch')
		{

		}
	}
}

new faq_admin();


require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>