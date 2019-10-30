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
//define('e_MINIMAL',true);
require_once('../class2.php');

if (!getperms("5|J")) { e107::redirect('admin'); exit; }

e107::css('inline',"

.e-wysiwyg { height: 400px }
td.menu-field { background-color: rgba(0,0,0,0.07); }
");

e107::coreLan('cpage', true);
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_page.php');

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
		'overview'		=> array(
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
		'overview/list'	=> array('caption'=> CUSLAN_1, 'perm' => '5|J'), 
		'page/list'		=> array('caption'=> CUSLAN_48, 'perm' => '5'),
		'menu/list'		=> array('caption'=> CUSLAN_49, 'perm' => 'J', 'tab' => 2),

		'page/create' 	=> array('caption'=> CUSLAN_12, 'perm' => '5'),
		'other' 		=> array('divider'=> true),
		'cat/list' 		=> array('caption'=> CUSLAN_50, 'perm' => '5'), // Create Category.
		'cat/create' 	=> array('caption'=> CUSLAN_51, 'perm' => '5'), // Category List
		'other2' 		=> array('divider'=> true),
	
	//	'menu/create' 	=> array('caption'=> CUSLAN_31, 'perm' => 'J', 'tab' => 2),
		'page/prefs'	=> array('caption'=> LAN_OPTIONS, 'perm' => '0')		
	);

	protected $adminMenuIcon = 'e-custom-24';
	

	protected $adminMenuAliases = array(
		'overview/edit' => 'overview/list',
		'page/edit'		=> 'page/list',
		'menu/edit'		=> 'menu/create',
		'menu/grid'		=> 'menu/list',
		'cat/edit'      => 'cat/list'
	);	
	
	protected $menuTitle = ADLAN_42;


	function init()
	{





	}



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
			return null;
		}
			
		if($mode == 'filter')
		{
			return null;
		}
		if($mode == 'batch')
		{
			return null;
		}		
	}
	

		// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		
		if($attributes['mode'] == 'read')
		{
			parse_str(str_replace('&amp;', '&', e_QUERY), $query); //FIXME - FIX THIS
			$query['action'] = 'edit';
			$query['id'] = $id;
			$query = http_build_query($query,null, '&amp;');
				
			$text = "<a href='".e_SELF."?{$query}' class='btn btn-default' title='".LAN_EDIT."' data-toggle='tooltip' data-placement='left'>
						".ADMIN_EDIT_ICON."</a>";

			if($this->getController()->getMode() === 'overview')
			{
				$text .= $this->submit_image('menu_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'));
			}
			return $text;
		}
	}

}

//FIXME - needs a layout similar to the admin sitelinks page. ie. showing chapters as we would 'sublinks'. 
// BOOKS & CHAPTERS 
class page_chapters_ui extends e_admin_ui
{
		protected $pluginTitle	= CUSLAN_59;
		protected $pluginName	= 'core';
		protected $table 		= "page_chapters";
		protected $pid			= "chapter_id";
		protected $perPage 		= 0; //no limit
		protected $batchDelete 	= false;
		protected $batchCopy	= true;	
        protected $batchLink   	= true;
        protected $batchExport  = true;

		protected $listQry          = "SELECT a. *, CASE WHEN a.chapter_parent = 0 THEN a.chapter_order ELSE b.chapter_order + (( a.chapter_order)/1000) END AS Sort FROM `#page_chapters` AS a LEFT JOIN `#page_chapters` AS b ON a.chapter_parent = b.chapter_id ";
		protected $listOrder		= 'Sort,chapter_order ';
	//	protected $listOrder 	= ' COALESCE(NULLIF(chapter_parent,0), chapter_id), chapter_parent > 0, chapter_order '; //FIXME works with parent/child but doesn't respect parent order.
		protected $url         	= array('route'=>'page/chapter/index', 'vars' => array('id' => 'chapter_id', 'name' => 'chapter_sef'), 'name' => 'chapter_name', 'description' => ''); // 'link' only needed if profile not provided. 
	
		protected $sortField	= 'chapter_order';
		protected $sortParent   = 'chapter_parent';
	//	protected $orderStep 	= 10;
		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',						'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'chapter_id'				=> array('title'=> LAN_ID,					'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'chapter_icon' 				=> array('title'=> LAN_ICON,				'type' => 'icon', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'writeParms'=> 'glyphs=1', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			       		

         	'chapter_parent' 			=> array('title'=> CUSLAN_52,		   		'type' => 'dropdown',		'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'filter'=>true),
         	'chapter_name' 				=> array('title'=> CUSLAN_53,	            'type' => 'method',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'writeParms'=>'size=xxlarge'),
         	'chapter_template' 			=> array('title'=> LAN_TEMPLATE, 			'type' => 'dropdown', 		'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
        
         	'chapter_meta_description'	=> array('title'=> LAN_DESCRIPTION,			'type' => 'textarea',		'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'writeParms'=>'size=xxlarge', 'readonly'=>FALSE),
			'chapter_meta_keywords' 	=> array('title'=> LAN_KEYWORDS,			'type' => 'tags',			'inline'=>true, 'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),
			'chapter_sef' 				=> array('title'=> LAN_SEFURL,	    	    'type' => 'text',			'width' => 'auto', 'readonly'=>FALSE, 'batch'=>true,  'inline'=>true, 'writeParms'=>'size=xxlarge&inline-empty=1&sef=chapter_name',  ), // Display name
			'chapter_manager' 			=> array('title'=> CUSLAN_55,		        'type' => 'userclass',		'inline'=>true, 'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'chapter_order' 			=> array('title'=> LAN_ORDER,				'type' => 'text',			'width' => 'auto', 'thclass' => 'right', 'class'=> 'right' ),										
			'chapter_visibility' 		=> array('title'=> LAN_VISIBILITY,			'type' => 'userclass',		'inline'=>true, 'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'chapter_fields'            => array('title', 'hidden',                 'type'=>'hidden'),
			'chapter_image' 	        => array('title'=> LAN_IMAGE,			    'type' => 'image', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center',  'readParms'=>'thumb=140&thumb_urlraw=0&thumb_aw=140', 'writeParms'=>'', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),

			'options' 					=> array('title'=> LAN_OPTIONS,				'type' => 'method',			'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'left', 'readParms'=>'sort=1')
		
		);

		protected $fieldpref = array('checkboxes', 'chapter_icon', 'chapter_id', 'chapter_name', 'chapter_description','chapter_template', 'chapter_visibility', 'chapter_order', 'options');

		protected $books = array();
	
		function init()
		{
			$this->addTitle(CUSLAN_63);
		//	e107::getMessage()->addWarning("Experimental: Custom Fields");
			$this->tabs = array(LAN_GENERAL,CUSLAN_4);
			$this->fields['chapter_fields'] = array('title'=>"Fields", 'tab'=>1, 'type'=>'method', 'data'=>'json', 'writeParms'=>array('nolabel'=>2));

			if($this->getAction() === 'list')
			{
				$this->fields['chapter_parent']['title'] = CUSLAN_56;
			}
			elseif(deftrue('e_DEBUG'))
			{
				$this->fields['chapter_sef']['title'] = LAN_SEFURL.' / '.LAN_NAME;
				$this->fields['chapter_sef']['help'] = 'May also be used in shortcode {CHAPTER_MENUS: name=x}';
			}

			$sql = e107::getDb();
			$sql->gen("SELECT chapter_id,chapter_name FROM #page_chapters WHERE chapter_parent =0");
			$this->books[0] = CUSLAN_5;
			
			while($row = $sql->fetch())
			{
				$bk = $row['chapter_id'];
				$this->books[$bk] = $row['chapter_name'];
			}
			
			asort($this->books);
			
			$this->fields['chapter_parent']['writeParms'] = $this->books;	
			
			
			$tmp = e107::getLayouts('', 'chapter', 'front', '', true, false);
			$tmpl = array();
			foreach($tmp as $key=>$val)
			{
				if(substr($key,0,3) != 'nav')
				{
					$tmpl[$key] = $val;	
				}	
			}
			
			$this->fields['chapter_template']['writeParms'] = $tmpl; // e107::getLayouts('', 'chapter', 'front', '', true, false); // e107::getLayouts('', 'page', 'books', 'front', true, false); 
			
		}
		
		
		public function beforeCreate($new_data, $old_data)
		{
			if(empty($new_data['chapter_sef']))
			{
				$new_data['chapter_sef'] = eHelper::title2sef($new_data['chapter_name']);
			}
			else 
			{
				$new_data['chapter_sef'] = eHelper::secureSef($new_data['chapter_sef']);
			}
			
			$sef = e107::getParser()->toDB($new_data['chapter_sef']);
			
			if(e107::getDb()->count('page_chapters', '(*)', "chapter_sef='{$sef}'"))
			{
				e107::getMessage()->addError(CUSLAN_57);
				return false;
			}

			$new_data = e107::getCustomFields()->processConfigPost('chapter_fields', $new_data);
			
			return $new_data;	
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{	
			// return $this->beforeCreate($new_data);

			$new_data = e107::getCustomFields()->processConfigPost('chapter_fields', $new_data);

			return $new_data;
		}

/*
		private function processCustomFields($newdata)
		{
			if(empty($newdata))
			{
				return null;
			}

			$new = array();
			foreach($newdata as $fields)
			{
				if(empty($fields['key']) || empty($fields['type']))
				{
					continue;
				}


				$key = $fields['key'];
				unset($fields['key']);
				$new[$key] = $fields;


			}

			return $new;
		}*/

}


class page_chapters_form_ui extends e_admin_form_ui
{
	
	function chapter_name($curVal,$mode,$parm)
	{
	
		$frm = e107::getForm();
	
		if($mode == 'read') 
		{
			$parent 	= $this->getController()->getListModel()->get('chapter_parent');
			$id			= $this->getController()->getListModel()->get('chapter_id');

			$level = 1;

			$linkQ = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";	
			$level_image = $parent ? '<img src="'.e_IMAGE_ABS.'generic/branchbottom.gif" class="icon" alt="" style="margin-left: '.($level * 20).'px" />&nbsp;' : '';

			return ($parent) ?  $level_image."<a href='".$linkQ."' >".$curVal."</a>" : $curVal;
		}
			
		if($mode == 'write')
		{
			return $frm->text('chapter_name',$curVal,255,'size=xxlarge');	
		}
			
		if($mode == 'filter')
		{
			return null;
		}
		if($mode == 'batch')
		{
			return null;
		}		
	}
	
	
	function chapter_fields($curVal,$mode,$parm)
	{
		$fieldAmount = (deftrue('e_DEBUG')) ? 20 :10;


		if($mode == 'read')
		{

		}

		if($mode == 'write')
		{
			return e107::getCustomFields()->loadConfig($curVal)->renderConfigForm('chapter_fields');
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



	
		// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		//$id = $this->getController()->getListModel()->get('page_id');
		//	return "<a href='".e_BASE."page.php?".$id."' >".$curVal."</a>";
		$parent = $this->getController()->getListModel()->get('chapter_parent');
	//	$id = $this->getController()->getListModel()->get('chapter_id');
		$att['readParms'] = 'sort=1';

		
		if($attributes['mode'] == 'read')
		{
			$text = "<div class='btn-group'>";
			$text .= $this->renderValue('options',$value,$att,$id);
			
			if($parent != 0)
			{
				$link = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";	
				$text .= "<a href='".$link."' class='btn btn-default' title='".CUSLAN_58."'>".ADMIN_PAGES_ICON."</a>";  //
			}


			$text .= "</div>";
			return $text;
		}
	}
}


// Menu Area. 
/*
class menu_admin_ui extends e_admin_ui
{
		protected $pluginTitle = ADLAN_42;
		protected $pluginName = 'core';
		protected $table = "page";
		
		protected $listQry = "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.menu_name != '' "; // without any Order or Limit.
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
         	'menu_name' 		=> array('title'=> "Menu Name", 	'tab' => 0,	'type' => 'text', 		'width' => 'auto','nolist'=>true),
		
		    'page_title'	   	=> array('title'=> LAN_TITLE, 		'tab' => 0,	'type' => 'text', 		'width'=>'25%', 'inline'=>true),
		//	'page_template' 	=> array('title'=> 'Template', 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),     
		// 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&readonly=1'),
        
			'options' 	=> array('title'=> LAN_OPTIONS, 'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center'
		);
	
		protected $fieldpref = array("page_id","menu_name", "page_title", "page_text");	
		
		
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
		protected $eventName   		= 'page';
		protected $table        	= "page";
		
		protected $listQry      	= "SELECT SQL_CALC_FOUND_ROWS
		                                    p.*,u.user_id,u.user_name,pch.chapter_sef,pbk.chapter_sef AS book_sef
		                               FROM #page AS p
		                               LEFT JOIN #user AS u ON p.page_author = u.user_id
		                               LEFT JOIN #page_chapters AS pch ON p.page_chapter = pch.chapter_id
		                               LEFT JOIN #page_chapters AS pbk ON pch.chapter_parent = pbk.chapter_id
		                               WHERE (p.page_title != '' OR p.page_text != '')   "; // without any Order or Limit.
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid 				= "page_id";
		protected $listOrder 		= 'p.page_order asc'; // desc would require changes to ajax sorting. 
		protected $perPage 			= 10;
		protected $batchDelete 		= true;
		protected $batchCopy 		= true;	
        protected $batchLink    	= true;
		protected $batchExport      = true;
	  	protected $batchFeaturebox  = true;
		protected $sortField		= 'page_order';
		protected $orderStep 		= 10;
		//protected $url         	= array('profile'=>'page/view', 'name' => 'page_title', 'description' => '', 'link'=>'{e_BASE}page.php?id=[id]'); // 'link' only needed if profile not provided. 
		protected $url         		= array('route'=>'page/view/index', 'vars' => array('id' => 'page_id', 'name' => 'page_sef', 'other' => 'page_sef', 'chapter' => 'chapter_sef', 'book' => 'book_sef'), 'name' => 'page_title', 'description' => ''); // 'link' only needed if profile not provided.
		protected $tabs		 		= array(CUSLAN_59,CUSLAN_60,CUSLAN_61,CUSLAN_62);
		protected $featurebox		= array('name'=>'page_title', 'description'=>'page_text', 'image' => 'menu_image', 'visibility' => 'page_class', 'url' => true);


		protected $grid             = array('title'=>'menu_title', 'image'=>'menu_image', 'body'=>'',  'class'=>'col-md-2', 'perPage'=>12, 'carousel'=>false);




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
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'3%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> LAN_ID,			'type' => 'text', 'tab' => 0,	'width'=>'5%', 			'forced'=> TRUE, 'readParms'=>'link=sef&target=blank'),
            'page_title'	   	=> array('title'=> CUSLAN_2, 		'tab' => 0,	'type' => 'text', 	'data'=>'str', 'inline'=>true,		'width'=>'25%', 'writeParms'=>'size=block-level'),
		    'page_chapter' 		=> array('title'=> CUSLAN_63, 	    'tab' => 0,	'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true, 'inline'=>true),
       
			'page_template' 	=> array('title'=> LAN_TEMPLATE, 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>array()),

		 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 'inline'=>true, 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'tab' => 0,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>array('media'=>'page^', 'template'=>'page')),
		
		
			// Options Tab. 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'tab' => 1,	'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&type=datetime'),
            'page_class' 		=> array('title'=> LAN_VISIBILITY, 	'tab' => 1,	'type' => 'userclass', 	'data'=>'str', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_comment_flag' => array('title'=> LAN_COMMENTS,		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_password' 	=> array('title'=> LAN_PASSWORD, 		'tab' => 1, 'type' => 'text', 	'data'=>'str', 'width' => 'auto', 'writeParms'=>array('password'=>1, 'nomask'=>1, 'size' => 40, 'class' => 'tbox e-password', 'generate' => 1, 'strength' => 1, 'required'=>0)),								
			'page_sef' 			=> array('title'=> LAN_SEFURL, 		'tab' => 1,	'type' => 'text', 'batch'=>true,	'data'=>'str', 'inline'=>true, 'width' => 'auto', 'writeParms'=>'size=xxlarge&sef=page_title'),
			'page_metakeys' 	=> array('title'=> LAN_KEYWORDS, 		'tab' => 1,	'type' => 'tags', 	'data'=>'str', 'width' => 'auto'),
			'page_metadscr' 	=> array('title'=> CUSLAN_11, 		'tab' => 1,	'type' => 'text', 	'data'=>'str', 'width' => 'auto', 'writeParms'=>'size=xxlarge'),
		
			'page_order' 		=> array('title'=> LAN_ORDER, 		'tab' => 1,	'type' => 'number', 'width' => 'auto', 'inline'=>true),
			'page_fields'       => array('title'=>'Custom Fields',  'tab'=>4, 'type'=>'hidden', 'data'=>'json', 'width'=>'auto'),


			// Menu Tab  XXX 'menu_name' is 'menu_name' - not caption. 
			'menu_name' 		=> array('title'=> CUSLAN_64, 		'tab' => 2,	'type' => 'text', 		'width' => 'auto','nolist'=>true, "help"=>"Will be listed in the Menu-Manager under this name or may be called using {CMENU=name} in your theme. Must use ASCII characters only and be all lowercase."),
		   	'menu_title'	   	=> array('title'=> CUSLAN_65, 	    'nolist'=>true, 'tab' => 2,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Caption displayed on the menu item.", 'writeParms'=>'size=xxlarge'),
			'menu_text' 		=> array('title'=> CUSLAN_66,		'nolist'=>true, 'tab' => 2,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page^' ),
			'menu_template' 	=> array('title'=> CUSLAN_67,       'nolist'=>true, 'tab' => 2,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
            'menu_class' 		=> array('title'=> LAN_VISIBILITY, 	'tab' => 3,	'type' => 'userclass', 	'data'=>'int', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
			'menu_button_text'	=> array('title'=> CUSLAN_68, 	    'nolist'=>true, 'tab' => 3,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Leave blank to use the default"),
		
			'menu_button_url'	=> array('title'=> CUSLAN_69, 	    'nolist'=>true, 'tab' => 3,	'type' => 'text', 'inline'=>true,		'width'=>'25%', "help"=>"Leave blank to use the corresponding page", 'writeParms'=>'size=xxlarge'),
		
			'menu_icon'			=> array('title' =>CUSLAN_70,       'nolist'=>true, 'tab' => 2,	'type' => 'icon', 		'width' => '110px',	'thclass' => 'center', 			'class' => "center", 'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParms'=>'media=page^&glyphs=1', 'readonly'=>false),
		
			'menu_image'		=> array('title' =>CUSLAN_71, 	    'nolist'=>true, 'tab' => 2,	'type' => 'image', 		'width' => '110px',	'thclass' => 'center', 			'class' => "center", 'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParms'=>'media=page^&video=1', 'readonly'=>false),
			
	
	
	   	//	'page_ip_restrict' 	=> array('title'=> LXXAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS,   'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last','readParms'=>'sort=1&deleteClass=e_UC_NOBODY')
		);
	
		protected $fieldpref = array("page_id","page_title","page_chapter","page_template","page_author","page_class");

		protected $prefs = array( 
			'listPages'	   			=> array('title'=> CUSLAN_29, 						'type'=>'boolean'),
			'listBooks'	   			=> array('title'=> CUSLAN_50, 			            'type'=>'boolean'),
			'listBooksTemplate'   	=> array('title'=> CUSLAN_72, 	                    'type'=>'dropdown'),
			'pageCookieExpire'		=> array('title'=> CUSLAN_30, 						'type'=>'number'), //TODO Set default value to  84600
			'admin_page_perpage'    => array('title'=> CUSLAN_3, 				'type'=>'number'), //TODO Set default value to  84600
		);

		protected $books = array();
		protected $cats = array(0 => LAN_NONE);
		protected $templates = array();
		protected $chapterFields = array();
		protected $chapters = array();

		function init()
		{

			$mode = $this->getMode();

			$this->perPage = (int) e107::pref('core','admin_page_perpage', 10);

			if($mode !== 'menu')
			{
				$this->grid = array();
			}

			if($mode === 'overview')
			{
				$this->listQry = "SELECT SQL_CALC_FOUND_ROWS p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id  "; // without any Order or Limit.
				$this->fieldpref = array("page_id", "page_title", 'page_chapter', 'page_template', "menu_title", 'menu_image', 'menu_template' );



				$this->sortField = false;

				$this->fields['menu_title']['width'] = 'auto';
				$this->fields['menu_image']['readParms'] = 'thumb=60x55';
				$this->fields['menu_image']['width'] = 'auto';
				$this->fields['menu_button_text']['nolist'] = false; 
				$this->fields['menu_button_url']['nolist'] = false;

				$this->fields['page_title']['width'] = 'auto';

				$this->fields['options']['type'] = 'method';


				foreach($this->fieldpref as $k)
				{
					$this->fields[$k]['nolist'] = false;

					if($k === 'page_id')
					{
						continue;
					}

					if(strpos($k,'menu_') === 0)
					{
						$this->fields[$k]['class'] = 'menu-field ' . varset($this->fields[$k]['class'], '');
					}

					$this->fields[$k]['width'] = '13%';
				}
			}

			
			if(!empty($_POST['menu_delete'])) // Delete a Page/Menu combination (or rather, remove it's data )
			{
				$key = key($_POST['menu_delete']);
				
				if($key)
				{
					//e107::getDb()->update('page',"menu_name = '' WHERE page_id=".intval($key)." LIMIT 1");
					e107::getDb()->delete('page',"page_id=".intval($key));
				}
			}

			// USED IN Menu LIST/INLINE-EDIT MODE ONLY. 
			if($this->getMode() === 'menu' && ($this->getAction() == 'list' || $this->getAction() == 'inline' || $this->getAction() == 'grid'))
			{
			
				$this->listQry = "SELECT SQL_CALC_FOUND_ROWS p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE (p.menu_title != '' OR p.menu_name != '' OR p.menu_image != '' OR p.menu_icon !='') "; // without any Order or Limit.
		//	$this->gridQry = $this->listQry;
				$this->listOrder 		= 'p.page_order asc'; // 'p.page_id desc';
			
				$this->batchDelete 	= false;
				$this->fields = array(
					'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'3%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
					'page_id'			=> array('title'=> 'ID',			'type'=>'text',   'tab' => 0,	'width'=>'5%', 'readParms'=>'','forced'=> TRUE),
		       
					'menu_image'		=> array('title' =>CUSLAN_71, 	 	'type' => 'image', 		'width' => '110px',	'thclass' => 'left', 'class' => "left", 'nosort' => false, 'readParms'=>'thumb=140&thumb_urlraw=0&thumb_aw=140', 'readonly'=>false),
					'menu_icon'			=> array('title'=> LAN_ICON, 	 	'type' => 'icon', 		'width' => '80px',	'thclass' => 'center', 'class' => "center", 'nosort' => false, 'readParms'=>'thumb=80&thumb_urlraw=0&thumb_aw=80', 'readonly'=>false),		  					
				
			  		'menu_title'	   	=> array('title'=> CUSLAN_65, 	    'forced'=> TRUE, 	'type' => 'text', 		'inline'=>true,		'width'=>'20%'),
			
				
				  	'menu_name' 		=> array('title'=> CUSLAN_64, 	    'type' => 'text', 	'inline'=>false,	'width' => '10%','nolist'=>false, "help"=>"Will be listed in the Menu-Manager under this name. Must use ASCII characters only."),
					'menu_template' 	=> array('title'=> CUSLAN_67,  	    'type' => 'dropdown', 	'width' => '15%', 'filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>''),
          			'menu_class' 		=> array('title'=> LAN_USERCLASS,   'type' => 'userclass', 	'data'=>'str', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
		
				// 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
					'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&readonly=1'),
		     	
			   		'page_chapter' 		=> array('title'=> CUSLAN_63, 	    'tab' => 0,	'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true, 'inline'=>true),
      
					'menu_text' 		=> array('title'=> CUSLAN_66,	 	'type' => 'bbarea',		'data'=>'str',	'width' => 'auto', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'media=page'),
				
					'options' 	        => array('title'=> LAN_OPTIONS,     'type' => 'method',	'noselector' => true, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'delete=0&deleteClass='.e_UC_NOBODY)
				);
	
				$this->fieldpref = array("page_id","menu_name", "menu_title", 'menu_image', 'menu_template', 'menu_icon', 'page_chapter', 'menu_class');


				if(deftrue('e_DEBUG'))
				{
					$this->fields['menu_name']['inline'] = true;
				}

				if($this->getAction() == 'grid')
				{
					$this->fields['menu_image']['readParms'] = 'thumb=400x400';

				}




                ### Parse aliases again or all filters shall fail due to the menu hack!
                $this->_alias_parsed = false;
                $this->parseAliases();
			}
				

			if($this->getAction() == 'create' && e_DEBUG === true)
			{

				$tmp = e107::getCoreTemplate('page', 'default');
				if(!empty($tmp['editor']))
				{
					$this->fields['page_text']['writeParms']['default'] = $tmp['editor'];
				}

			}

			
			$this->templates = e107::getLayouts('', 'page', 'front', '', true, false); 
			unset($this->templates['panel'], $this->templates['nav']);
			
			$this->fields['page_template']['writeParms'] = $this->templates;			
			$this->fields['menu_template']['writeParms'] = e107::getLayouts('', 'menu', 'front', '', true, false); 
			$this->fields['menu_name']['writeParms'] 	= array('pattern'=>'^[a-z0-9-]*');
			
			
			$tmp = e107::getLayouts('', 'chapter', 'front', '', true, false);
			$tmpl = array();
			foreach($tmp as $key=>$val)
			{
				if(substr($key,0,3) != 'nav')
				{
					$tmpl[$key] = $val;	
				}	
			}
			
			
			$this->prefs['listBooksTemplate']['writeParms'] = $tmpl; 
			
			$sql = e107::getDb();

			$sql->gen("SELECT chapter_id,chapter_name,chapter_parent, chapter_sef, chapter_fields FROM #page_chapters ORDER BY chapter_parent asc, chapter_order");
			while($row = $sql->fetch())
			{
				$cat = $row['chapter_id'];

				$chrow = $row;
				unset($chrow['chapter_fields']);

				$this->chapters[$cat] = $chrow;

				if($row['chapter_parent'] == 0)
				{
					$this->books[$cat] = $row['chapter_name'];	
				}
				else
				{
					$book = $row['chapter_parent'];
					$this->cats[$cat] = $this->books[$book] . " : ".$row['chapter_name'];	
				}

				if(!empty($row['chapter_fields']))
				{
					$this->chapterFields[$cat] = ($row['chapter_fields']);
				}


			}
		//	asort($this->cats);			
			
			$this->fields['page_chapter']['writeParms']['optArray'] = $this->cats;
			$this->fields['page_chapter']['writeParms']['size'] = 'xxlarge';

			if($this->getAction() === 'create')
			{
				$this->fields['page_chapter']['writeParms']['ajax'] = array('src'=>e_SELF."?mode=page&action=chapter-change",'target'=>'tabadditional');
			}


			if(e_AJAX_REQUEST)
			{
				// @todo insert placeholder examples in params input when 'type' dropdown value is changed
			}




			if(e_AJAX_REQUEST && isset($_POST['page_chapter']) ) //&& $this->getAction() === 'chapter-change'
			{

				$this->initCustomFields($_POST['page_chapter']);

				$elid = 'core-page-create';
				$model = $this->getModel();
				$tabId = e107::getCustomFields()->getTabId();
				$tabLabel = e107::getCustomFields()->getTabLabel();

				$data = array(
					'tabs'   => $this->getTabs(),
					'legend' => '',
					'fields' => $this->getFields(),
				);

				$text = $this->getUI()->renderCreateFieldset($elid, $data, $model, $tabId);

				$ajax = e107::getAjax();
				$commands = array();

				if(empty($text))
				{
					$text = ""; // There are no additional fields for the selected chapter.
					$commands[] = $ajax->commandInvoke('a[href="#tab' . $tabId . '"]', 'fadeOut');
				}
				else
				{
					$commands[] = $ajax->commandInvoke('a[href="#tab' . $tabId . '"]', 'fadeInAdminTab');
				}

				$commands[] = $ajax->commandInvoke('a[href="#tab' . $tabId . '"]', 'html', array($tabLabel));
				$commands[] = $ajax->commandInvoke('#tab' . $tabId, 'html', array($text));

				$ajax->response($commands);
				exit;
			}
		}

		/*
		 * @todo Move to admin-ui ?
		 */
		private function initCustomFields($chap=null)
		{

			if(!empty($this->chapterFields[$chap]))
			{
				e107::getCustomFields()->loadConfig($this->chapterFields[$chap]);
			}
			else
			{
				$tabId = e107::getCustomFields()->getTabId();
				e107::css('inline', '.nav-tabs li a[href="#tab' . $tabId . '"] { display: none; }');
			}

			e107::getCustomFields()->setAdminUIConfig('page_fields',$this);
		}

		private function loadCustomFieldsData()
		{
			$row = e107::getDb()->retrieve('page', 'page_chapter, page_fields', 'page_id='.$this->getId());

			$cf = e107::getCustomFields();

			$cf->loadData($row['page_fields'])->setAdminUIData('page_fields',$this);

		//	e107::getDebug()->log($cf);


		}


		function CreateObserver()
		{
			parent::CreateObserver();
			$this->initCustomFields(0);

		}



		// Override default so we can alter the field db table data after it is loaded. .
		function EditObserver()
		{

			parent::EditObserver();

			$row = e107::getDb()->retrieve('page', 'page_chapter, page_fields', 'page_id='.$this->getId());
			$chap = intval($row['page_chapter']);

			$this->initCustomFields($chap);
			$this->loadCustomFieldsData();

		}

		/**
		 * Filter/Process Posted page_field data;
		 * @param $new_data
		 * @return null
		 *//*
		private function processCustomFieldData($new_data)
		{
			if(empty($new_data))
			{
				return null;
			}

			unset($new_data['page_fields']); // Reset.

			foreach($new_data as $k=>$v)
			{
				if(substr($k,0,11) === "page_fields")
				{
					list($tmp,$newkey) = explode("__",$k);
					$new_data['page_fields'][$newkey] = $v;
					unset($new_data[$k]);


				}

			}



			return $new_data;


		}
*/





        /**
         * Overrid
         */
        public function ListObserver()
        {
            parent::ListObserver();

            // fix current url config limitation
            $tree = $this->getTreeModel();

            /** @var e_admin_model $model */
            foreach ($tree->getTree() as $id => $model)
            {

				if($chap = $model->get('page_chapter'))
				{
                    $model->set('chapter_sef', $this->chapters[$chap]['chapter_sef']);
                    $parent = (int) $this->chapters[$chap]['chapter_parent'];
                    $model->set('book_sef', $this->chapters[$parent]['chapter_sef']);
				}
				else
				{
                    $urlData = $this->url;
                    $urlData['route'] = 'page/view/other';
                    $model->setUrl($urlData);
                }

            }
        }

		function afterCreate($newdata,$olddata, $id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
			
			$menu_name = $tp->toDB($newdata['menu_name']); // not to be confused with menu-caption.
			$menu_path = intval($id);
				
			if (!$sql->select('menus', 'menu_name', "`menu_path` = ".$menu_path." LIMIT 1")) 	
			{		
				$insert = array('menu_name' => $menu_name, 'menu_path' => $menu_path);
			
				if($sql->insert('menus', $insert) !== false)
				{
					$mes->addDebug(CUSLAN_73);
					return true;
				}
			}	
			
			return $newdata;
			
		}
		
		function beforeCreate($newdata,$olddata)
		{

			$newdata = e107::getCustomFields()->processDataPost('page_fields',$newdata);

			$newdata['menu_name'] = preg_replace('/[^\w-*]/','-',$newdata['menu_name']);

			if(empty($newdata['page_sef']))
			{
				if(!empty($newdata['page_title']))
				{
					$newdata['page_sef'] = eHelper::title2sef($newdata['page_title']);
				}
				elseif(!empty($newdata['menu_name']))
				{
					$newdata['page_sef'] = eHelper::title2sef($newdata['menu_name']);
				}
		
			}
			else
			{
				$newdata['page_sef'] = eHelper::secureSef($newdata['page_sef']);
			}


		//	$newdata = $this->processCustomFieldData($newdata);


			$sef = e107::getParser()->toDB($newdata['page_sef']);

			if(isset($newdata['page_title']) && isset($newdata['menu_name']) && empty($newdata['page_title']) && empty($newdata['menu_name']))
			{
				e107::getMessage()->addError(CUSLAN_79);
				return false;
			}

			if(e107::getDb()->count('page', '(*)', "page_sef='{$sef}'"))
			{
				e107::getMessage()->addError(CUSLAN_57);
				return false;
			}


			return $newdata;	
		}
		
		function beforeUpdate($newdata,$olddata, $id)
		{

			$newdata = e107::getCustomFields()->processDataPost('page_fields',$newdata);

			if(isset($newdata['menu_name']))
			{
				$newdata['menu_name'] = preg_replace('/[^\w-*]/','',$newdata['menu_name']);
			}




			return $newdata;	
		}		
		
		// Update Menu in Menu Table
		function afterUpdate($newdata,$olddata,$id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();

			if(!isset($newdata['menu_name']))
			{
				return true;
			}
					
			$menu_name = $tp->toDB($newdata['menu_name']); // not to be confused with menu-caption.
				
			if ($sql->select('menus', 'menu_name', "`menu_path` = ".$id." LIMIT 1")) 	
			{		
				if($sql->update('menus', "menu_name='{$menu_name}' WHERE menu_path=".$id." ") !== false)
				{
					$mes->addDebug(CUSLAN_74);
					return true;
				}
			}
			else // missing menu record so create it.  
			{
				$mes->addDebug(CUSLAN_75." ".$id);
				return $this->afterCreate($newdata,$olddata,$id);	
				
			}				
		}




		public function afterDelete($deleted_data, $id, $deleted_check)
		{
			$sql = e107::getDb();

			if ($sql->select('menus', 'menu_name', "`menu_path` = ".$id." LIMIT 1"))
			{
				if($sql->delete('menus', " menu_path=".intval($id)." ") !== false)
				{
					e107::getMessage()->addDebug(CUSLAN_76."".$id." ".CUSLAN_77);
					return true;
				}
				else
				{
					e107::getMessage()->addDebug(CUSLAN_78."".$id.".");
				}
			}


		}



}


new page_admin();
require_once('auth.php');

e107::getAdminUI()->runPage();

require_once(e_ADMIN.'footer.php');






?>
