<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Custom Menus/Pages Administration
 * Admin-related functions for custom page and menu creation
*/
define('e_MINIMAL',true);
require_once('../class2.php');

if (!getperms("5|J")) { header('location:'.e_ADMIN.'admin.php'); exit; }

e107::css('inline',"

.e-wysiwyg { height: 400px }
");

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'custom';


class page_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'page'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'page_chapters_ui',
			'path' 			=> null,
			'ui' 			=> 'page_chapters_form_ui',
			'uipath' 		=> null
		),
		'menu'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		),
		'dialog'		=> array(
			'controller' 	=> 'menu_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'menu_admin_form_ui',
			'uipath' 		=> null
		)
			
	);	
	
	protected $adminMenu = array(
		'page/list'		=> array('caption'=> CUSLAN_48, 'perm' => '5'),
		'menu/list'		=> array('caption'=> CUSLAN_49, 'perm' => 'J', 'tab' => 2),	
		'page/create' 	=> array('caption'=> CUSLAN_12, 'perm' => '5'),
		'other' 		=> array('divider'=> true),
		'cat/list' 		=> array('caption'=> "List Books/Chapters", 'perm' => '5'), // Create Category. 
		'cat/create' 	=> array('caption'=> "Add Book/Chapter", 'perm' => '5'), // Category List
		'other2' 		=> array('divider'=> true),
	
	//	'menu/create' 	=> array('caption'=> CUSLAN_31, 'perm' => 'J', 'tab' => 2),
		'page/prefs'	=> array('caption'=> LAN_OPTIONS, 'perm' => '0')		
	);
	

	protected $adminMenuAliases = array(
		'page/edit'		=> 'page/list',
		'menu/edit'		=> 'menu/create'				
	);	
	
	protected $menuTitle = ADLAN_42;
}

class page_admin_form_ui extends e_admin_form_ui
{
	
	function page_title($curVal,$mode,$parm)
	{
	
		if($mode == 'read') 
		{
			$id = $this->getController()->getListModel()->get('page_id');
			return "<a href='".e_BASE."page.php?".$id."' >".$curVal."</a>";
		}
			
		if($mode == 'write')
		{
			return;	
		}
			
		if($mode == 'filter')
		{
			return;	
		}
		if($mode == 'batch')
		{
			return;
		}		
	}
	

}

//FIXME - needs a layout similar to the admin sitelinks page. ie. showing chapters as we would 'sublinks'. 
// BOOKS & CHAPTERS 
class page_chapters_ui extends e_admin_ui
{
		protected $pluginTitle	= 'Page';
		protected $pluginName	= 'core';
		protected $table 		= "page_chapters";
		protected $pid			= "chapter_id";
		protected $perPage = 0; //no limit
		protected $batchDelete = false;
		protected $listOrder 	= 'chapter_parent,chapter_order asc'; 
		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',						'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'chapter_id'				=> array('title'=> LAN_ID,					'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'chapter_icon' 				=> array('title'=> LAN_ICON,				'type' => 'icon', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			       		
         	'chapter_parent' 			=> array('title'=> "Book",					'type' => 'dropdown',		'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'filter'=>true),                   	
         	'chapter_name' 				=> array('title'=> "Book or Chapter Title",	'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),       
         	'chapter_meta_description'	=> array('title'=> LAN_DESCRIPTION,			'type' => 'textarea',		'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>FALSE),
			'chapter_meta_keywords' 	=> array('title'=> "Meta Keywords",			'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),		
			'chapter_sef' 				=> array('title'=> "SEF Url String",		'type' => 'text',			'width' => 'auto', 'readonly'=>FALSE), // Display name
			'chapter_manager' 			=> array('title'=> "Can be edited by",		'type' => 'userclass',		'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'chapter_order' 			=> array('title'=> LAN_ORDER,				'type' => 'text',			'width' => 'auto', 'thclass' => 'right', 'class'=> 'right' ),										
			'options' 					=> array('title'=> LAN_OPTIONS,				'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
		
		);

		protected $fieldpref = array('checkboxes', 'chapter_icon', 'chapter_id', 'chapter_name', 'chapter_description','chapter_manager', 'chapter_order', 'options');

		protected $books = array();
	
		function init()
		{
			$sql = e107::getDb();
			$sql->gen("SELECT chapter_id,chapter_name FROM #page_chapters WHERE chapter_parent =0");
			$this->books[0] = "(New Book)";
			while($row = $sql->fetch())
			{
				$bk = $row['chapter_id'];
				$this->books[$bk] = $row['chapter_name'];
			}
			asort($this->books);			
			
			$this->fields['chapter_parent']['writeParms'] = $this->books;	
		}
		
		
		public function beforeCreate($new_data)
		{
	
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{
	
		}

}


class page_chapters_form_ui extends e_admin_form_ui
{

}


// Menu Area. 
/*
class menu_admin_ui extends e_admin_ui
{
		protected $pluginTitle = ADLAN_42;
		protected $pluginName = 'core';
		protected $table = "page";
		
		protected $listQry = "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.page_theme != '' "; // without any Order or Limit.
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid 			= "page_id";
		protected $listOrder 	= 'p.page_order asc'; // desc would require changes to ajax sorting. 
		protected $perPage 		= 10;
		protected $batchDelete 	= true;
		protected $batchCopy 	= true;	
	//	protected $sortField	= 'page_order';
		protected $orderStep 	= 10;
		
		protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> 'ID',			'type'=>'text',   'tab' => 0,	'width'=>'5%', 'readParms'=>'','forced'=> TRUE),
         	'page_theme' 		=> array('title'=> "Menu Name", 	'tab' => 0,	'type' => 'text', 		'width' => 'auto','nolist'=>true),
		
		    'page_title'	   	=> array('title'=> LAN_TITLE, 		'tab' => 0,	'type' => 'text', 		'width'=>'25%', 'inline'=>true),
		//	'page_template' 	=> array('title'=> 'Template', 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),     
		// 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&readonly=1'),
        
			'options' 	=> array('title'=> LAN_OPTIONS, 'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center'
		);
	
		protected $fieldpref = array("page_id","page_theme", "page_title", "page_text");	
		
		
		function init()
		{
			$this->fields['page_id']['readParms'] = array('link'=> e_SELF."?mode=dialog&action=preview&id=[id]", 'target'=> 'modal', 'iframe' => true);
			
			
			if(E107_DEBUG_LEVEL > 0 && e_AJAX_REQUEST)
			{
				echo "REQUEST = ".e_REQUEST_SELF; //XXX Why no Query String ?? FIXME
				// $this->getAction()	
			}
			
			
			
			if($this->getMode() == 'dialog')
			{
				
				$this->getRequest()->setAction('preview');
				
			//	$this->setDefaultAction('previewPage');
				
			//	echo "ACTIOn = ".$this->getAction();
				
				define('e_IFRAME', TRUE);
				
				// return;
			};
				
			
		}

		function CreateHeader()
		{
			// e107::css('inline',' body { background-color: green } ');	
		}
		
		// Create Menu in Menu Table
	
		
		
		function previewPage() //XXX FIXME Doesn't work when in Ajax mode.. why???
		{
			print_a($_GET);
			
		//	$id = $this->getListModel()->get('page_id');
			$tp = e107::getParser();			
		}
					
				
			
		
		
}

//TODO XXX FIXME // Hooks! 
	$hooks = array(
					'method'	=>'form', 
					'table'		=>'page', 
					'id'		=> $id, 
					'plugin'	=> 'page', 
					'function'	=> 'createPage'
				);
				
				
	//			$text .= $frm->renderHooks($hooks);



class menu_form_ui extends e_admin_form_ui
{

}
*/




//  MAIN Pages. 
class page_admin_ui extends e_admin_ui
{
		protected $pluginTitle  	= ADLAN_42;
		protected $pluginName   	= 'core';
		protected $table        	= "page";
		
		protected $listQry      	= "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.page_theme = '' "; // without any Order or Limit.
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid 				= "page_id";
		protected $listOrder 		= 'p.page_order asc'; // desc would require changes to ajax sorting. 
		protected $perPage 			= 10;
		protected $batchDelete 		= true;
		protected $batchCopy 		= true;	
        protected $batchLink    	= true;
	  	protected $batchFeaturebox   = true;
		protected $sortField		= 'page_order';
		protected $orderStep 		= 10;
		//protected $url         	= array('profile'=>'page/view', 'name' => 'page_title', 'description' => '', 'link'=>'{e_BASE}page.php?id=[id]'); // 'link' only needed if profile not provided. 
		protected $url         		= array('route'=>'page/view/index', 'vars' => array('id' => 'page_id', 'sef' => 'page_sef'), 'name' => 'page_title', 'description' => ''); // 'link' only needed if profile not provided. 
		protected $tabs		 		= array("Page","Page Options","Menu");
		protected $featurebox		= array('title'=>'page_title', 'text'=>'page_text');
		
		/*
		 * 	'fb_title' 			=> array('title'=> LAN_TITLE,			'type' => 'text',			'inline'=>true,  'width' => 'auto', 'thclass' => 'left'), 
     	'fb_text' 			=> array('title'=> FBLAN_08,			'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1','writeParms'=>'template=admin'), 
		//DEPRECATED 'fb_mode' 			=> array('title'=> FBLAN_12,			'type' => 'dropdown',		'data'=> 'int',	'width' => '5%', 'filter'=>TRUE, 'batch'=>TRUE),		
		//DEPRECATED 'fb_rendertype' 	=> array('title'=> FBLAN_22,			'type' => 'dropdown',		'data'=> 'int',	'width' => 'auto', 'noedit' => TRUE),	
        'fb_template' 		=> array('title'=> LAN_TEMPLATE,			'type' => 'layouts',		'data'=> 'str', 'width' => 'auto', 'writeParms' => 'plugin=featurebox', 'filter' => true, 'batch' => true),	 	// Photo
		'fb_image' 			=> array('title'=> "Image",				'type' => 'image',			'width' => 'auto', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60'),
		'fb_imageurl' 		=> array('title'=> "Image Link",		'type' => 'url',			'width' => 'auto'),
		'fb_class' 	
		 */
		
		
	//		protected $listSorting = true; 
	
		// PAGE LIST/EDIT and MENU EDIT modes. 
		protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> LAN_ID,			'type' => 'text', 'tab' => 0,	'width'=>'5%', 			'forced'=> TRUE, 'readParms'=>'link=sef&target=dialog'),
            'page_chapter' 		=> array('title'=> 'Book/Chapter', 	'tab' => 0,	'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true, 'inline'=>true),
            'page_title'	   	=> array('title'=> LAN_TITLE, 		'tab' => 0,	'type' => 'text', 'inline'=>true,		'width'=>'25%'),
			'page_template' 	=> array('title'=> LAN_TEMPLATE, 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
        
		 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'tab' => 0,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page'), 
		
		
			// Options Tab. 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'tab' => 1,	'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1'),
            'page_class' 		=> array('title'=> LAN_USERCLASS, 	'tab' => 1,	'type' => 'userclass', 	'data'=>'int', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_comment_flag' => array('title'=> ADLAN_114,		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
		//	'page_password' 	=> array('title'=> LXAN_USER_05, 	'type' => 'text', 'width' => 'auto'),								
			'page_sef' 			=> array('title'=> LAN_SEFURL, 		'tab' => 1,	'type' => 'text', 'width' => 'auto'),		
			'page_metakeys' 	=> array('title'=> LAN_KEYWORDS, 		'tab' => 1,	'type' => 'text', 'width' => 'auto'),								
			'page_metadscr' 	=> array('title'=> CUSLAN_11, 		'tab' => 1,	'type' => 'text', 'width' => 'auto'),	
		
			'page_order' 		=> array('title'=> LAN_ORDER, 		'tab' => 1,	'type' => 'number', 'width' => 'auto', 'inline'=>true),
			
			// Menu Tab  XXX 'page_theme' is 'menu_name' - not caption. 
			'page_theme' 		=> array('title'=> "Menu Name", 		'tab' => 2,	'type' => 'text', 		'width' => 'auto','nolist'=>true, "help"=>"Will be listed in the Menu-Manager under this name or may be called using {MENU|name} in your theme."),
		   	'menu_title'	   	=> array('title'=> "Menu Title", 	'nolist'=>true, 'tab' => 2,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Caption displayed on the menu item."),
			'menu_text' 		=> array('title'=> "Menu Body",		'nolist'=>true, 'tab' => 2,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page' ), 
			'menu_template' 	=> array('title'=> "Menu Template", 'nolist'=>true, 'tab' => 2,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
        
			'menu_image'		=> array('title' =>"Menu Image", 	'nolist'=>true, 'tab' => 2,	'type' => 'image', 		'width' => '110px',	'thclass' => 'center', 			'class' => "center", 		'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>false),		  					
			
	
	
	   	//	'page_ip_restrict' 	=> array('title'=> LXXAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS,   'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);
	
		protected $fieldpref = array("page_id","page_title","page_chapter","page_template","page_author","page_class");

		protected $prefs = array( 
			'listPages'	   		=> array('title'=> CUSLAN_29, 'type'=>'boolean'),
			'pageCookieExpire'	=> array('title'=> CUSLAN_30, 'type'=>'number') //TODO Set default value to  84600
		);

		protected $books = array();
		protected $cats = array();
		protected $templates = array();

		function init()
		{
			
			// USED IN Menu LIST/INLINE-EDIT MODE ONLY. 
			if($this->getMode() == 'menu' && ($this->getACtion() == 'list' || $this->getACtion() == 'inline'))
			{
			
				$this->listQry = "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.page_theme != '' "; // without any Order or Limit.
			
				$this->batchDelete 	= false;
				$this->fields = array(
					'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
					'page_id'			=> array('title'=> 'ID',			'type'=>'text',   'tab' => 0,	'width'=>'5%', 'readParms'=>'','forced'=> TRUE),
		       
					'menu_image'		=> array('title' =>"Menu Image", 	 	'type' => 'image', 		'width' => '110px',	'thclass' => 'left', 'class' => "left", 'nosort' => false, 'readParms'=>'thumb=80&thumb_urlraw=0&thumb_aw=80','readonly'=>false),		  					
				
				  	'page_theme' 		=> array('title'=> "Menu Name", 	'type' => 'text', 		'width' => 'auto','nolist'=>true, "help"=>"Will be listed in the Menu-Manager under this name"),
					'menu_template' 	=> array('title'=> "Menu Template",  	'type' => 'dropdown', 	'width' => 'auto', 'filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
       
				// 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
					'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&readonly=1'),
		     
			  		'menu_title'	   	=> array('title'=> "Menu Title", 	'forced'=> TRUE, 	'type' => 'text', 		'inline'=>true,		'width'=>'25%'),
					'menu_text' 		=> array('title'=> "Menu Body",		 	'type' => 'bbarea',		'data'=>'str',	'width' => 'auto', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page'), 
				
					'options' 	=> array('title'=> LAN_OPTIONS, 'type' => null,	'noselector' => true, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'deleteClass=252')
				);
	
				$this->fieldpref = array("page_id","page_theme", "menu_title", "menu_text", 'menu_image', 'menu_template');	
			}
				
							
			
			$this->templates = e107::getLayouts('', 'page', 'front', '', false, false); 
			unset($this->templates['panel'], $this->templates['nav']);
			
			$this->fields['page_template']['writeParms'] = $this->templates;			
			$this->fields['menu_template']['writeParms'] = e107::getLayouts('', 'menu', 'front', '', false, false); 
			
			$sql = e107::getDb();
			$sql->gen("SELECT chapter_id,chapter_name,chapter_parent FROM #page_chapters ORDER BY chapter_parent asc, chapter_order");
			while($row = $sql->fetch())
			{
				$cat = $row['chapter_id'];

				if($row['chapter_parent'] == 0)
				{
					$this->books[$cat] = $row['chapter_name'];	
				}
				else
				{
					$book = $row['chapter_parent'];
					$this->cats[$cat] = $this->books[$book] . " : ".$row['chapter_name'];	
				}			
			}
		//	asort($this->cats);			
			
			$this->fields['page_chapter']['writeParms'] = $this->cats;

		}




		function afterCreate($newdata,$olddata, $id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
			
			$menu_name = $tp->toDB($newdata['page_theme']); // not to be confused with menu-caption.
			$menu_path = intval($id);
				
			if (!$sql->select('menus', 'menu_name', "`menu_path` = ".$menu_path." LIMIT 1")) 	
			{		
				$insert = array('menu_name' => $menu_name, 'menu_path' => $menu_path);
			
				if($sql->insert('menus', $insert) !== false)
				{
					
					$mes->addDebug("Menu Created");
					return true;
				}
			}	
			
			return $newdata;
			
		}
		
		function beforeCreate($newdata,$olddata)
		{
			return $newdata;	
			
		}
		
		
		
		// Update Menu in Menu Table
		function afterUpdate($newdata,$olddata,$id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
			
			$menu_name = $tp->toDB($newdata['menu_title']); // not to be confused with menu-caption.
				
			if ($sql->select('menus', 'menu_name', "`menu_path` = ".$id." LIMIT 1")) 	
			{		
				if($sql->update('menus', "menu_name='{$menu_name}' WHERE menu_path=".$id." ") !== false)
				{
					$mes->addDebug("Menu Updated");
					return true;
				}
			}				
		}






}


	;


new page_admin();
require_once('auth.php');

e107::getAdminUI()->runPage();

require_once(e_ADMIN.'footer.php');






?>