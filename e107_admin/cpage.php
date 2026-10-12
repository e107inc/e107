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
require_once(__DIR__.'/../class2.php');

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

	protected $perm = array(
		'page/list'     => '5',
		'page/create'   => '5',
		'page/edit'     => '5',
		'page/delete'   => 'J1',
		'page/prefs'    => '0',
		'page/grid'     => '5',
		'page/filter'   => '5',
		'page/inline'   => '5',
		'page/sort'     => '5',
		'page/chapter-change' => '5',
		'cat/list'      => '5',
		'cat/create'    => '5',
		'cat/edit'      => '5',
		'cat/delete'    => 'J1',
		'cat/grid'      => '5',
		'cat/filter'    => '5',
		'cat/inline'    => '5',
		'cat/sort'      => '5',
		'menu/list'     => 'J',
		'menu/grid'     => 'J',
		'menu/create'   => 'J',
		'menu/edit'     => 'J',
		'menu/delete'   => 'J1',
		'menu/prefs'    => '0',
		'menu/filter'   => 'J',
		'menu/inline'   => 'J',
		'menu/sort'     => 'J',
		'overview/list' => '5|J',
		'overview/create' => '5|J',
		'overview/edit' => '5|J',
		'overview/delete' => 'J1',
		'overview/prefs' => '0',
		'overview/filter' => '5|J',
		'overview/inline' => '5|J',
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

	/**
	 * Refuse a route that names an action the permission map does not, which the dispatcher would open to everyone the file admits.
	 *
	 * @param string $route
	 * @return bool
	 */
	public function hasRouteAccess($route)
	{
		if(varset(explode('/', (string) $route, 2)[1]) !== '' && !array_key_exists($this->canonicalizeRoute($route), $this->canonicalizeKeys($this->perm, array($this, 'canonicalizeRoute'))))
		{
			return false;
		}

		return parent::hasRouteAccess($route);
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
			$query = http_build_query($query, '', '&amp;');
				
			$text = "<a href='".e_SELF."?{$query}' class='btn btn-default' title='".LAN_EDIT."' data-toggle='tooltip' data-bs-toggle='tooltip' data-placement='left'>
						".defset('ADMIN_EDIT_ICON')."</a>";

			$controller = $this->getController();

			if($controller->getMode() === 'overview' && $controller->getDispatcher()->hasRouteAccess('overview/delete'))
			{
				$text .= $this->submit_image('menu_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'));
			}
			return $text;
		}
	}

	/**
	 * Draw the chapter of a listed row as plain text where this route does not save it.
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param array $params
	 * @param int $id
	 * @return void
	 */
	public function renderValueTrigger(&$field, &$value, &$params, $id)
	{
		if($field === 'page_chapter' && !$this->getController()->savesListedChapter($id))
		{
			unset($params['editable']);
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
				$this->fields['chapter_parent']['title'] = LAN_PARENT;
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

			$new_data = e107::getCustomFields()->loadConfig(varset($old_data['chapter_fields']))->processConfigPost('chapter_fields', $new_data);
			
			return $new_data;	
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{	
			// return $this->beforeCreate($new_data);

			$new_data = e107::getCustomFields()->loadConfig(varset($old_data['chapter_fields']))->processConfigPost('chapter_fields', $new_data);

			return $new_data;
		}


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

			$curVal = $this->storedTextAsHtml($curVal);

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

/*
		if($mode == 'read')
		{

		}
*/

		if($mode == 'write')
		{
			return e107::getCustomFields()->loadConfig($curVal)->renderConfigForm('chapter_fields');
		}
/*
		if($mode == 'filter')
		{
			return;
		}
		if($mode == 'batch')
		{
			return;
		}
*/

	}



	
		// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		//$id = $this->getController()->getListModel()->get('page_id');
		//	return "<a href='".e_BASE."page.php?".$id."' >".$curVal."</a>";

		if($attributes['mode'] !== 'read')
		{
			return;
		}

		$parent = $this->getController()->getListModel()->get('chapter_parent');
	//	$id = $this->getController()->getListModel()->get('chapter_id');
		$att = [];
		$att['readParms']['sort'] = 1;

		if(!getperms('J1')) // Page/Menu Delete Perms.
		{
			$att['readParms']['deleteClass'] = e_UC_NOBODY;
		}

		$text = "<div class='btn-group'>";

		$text .= $this->renderValue('options',$value,$att,$id);
			
		if($parent != 0)
		{
			$link = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";
			$text .= "<a href='".$link."' class='btn btn-default' title='".CUSLAN_58."'>".defset('ADMIN_PAGES_ICON')."</a>";  //
		}

		$text .= "</div>";
		return $text;

	}
}


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
		                               LEFT JOIN #page_chapters AS pbk ON pch.chapter_parent = pbk.chapter_id"; // without any Order or Limit.
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




		protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'3%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> LAN_ID,			'type' => 'text', 'tab' => 0,	'width'=>'5%', 			'forced'=> TRUE, 'readParms'=>'link=sef&target=blank'),
            'page_title'	   	=> array('title'=> CUSLAN_2, 		'tab' => 0,	'type' => 'text', 	'data'=>'str', 'inline'=>true,		'width'=>'25%', 'writeParms'=>'size=block-level'),
			'page_subtitle'	   	=> array('title'=> CUSLAN_80, 	'tab' => 0,	'type' => 'text', 	'data'=>'str', 'inline'=>true,		'width'=>'25%', 'writeParms'=>'size=block-level'),

		    'page_chapter' 		=> array('title'=> CUSLAN_63, 	    'tab' => 0,	'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true, 'inline'=>true),
       
			'page_template' 	=> array('title'=> LAN_TEMPLATE, 		'tab' => 0,	'type' => 'dropdown', 	'width' => 'auto','filter' => true, 'batch'=>true, 'inline'=>true, 'writeParms'=>array()),

		 	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'tab' => 0,	'type' => 'user', 'inline'=>true, 		'data'=>'int','width' => 'auto', 'thclass' => 'left'),
			'page_text' 		=> array('title'=> CUSLAN_9,		'tab' => 0,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>array('media'=>'page^', 'template'=>'page')),
		
		
			// Options Tab. 
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'tab' => 1,	'type' => 'datestamp', 	'data'=>'int',	'width' => 'auto','writeParms'=>'auto=1&type=datetime', 'batch'=>true),
            'page_class' 		=> array('title'=> LAN_VISIBILITY, 	'tab' => 1,	'type' => 'userclass', 	'data'=>'str', 'inline'=>true, 'width' => 'auto',  'filter' => true, 'batch' => true),
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_comment_flag' => array('title'=> LAN_COMMENTS,		'tab' => 1,	'type' => 'boolean', 	'data'=>'int', 'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_password' 	=> array('title'=> LAN_PASSWORD, 		'tab' => 1, 'type' => 'text', 	'data'=>'str', 'width' => 'auto', 'writeParms'=>array('password'=>1, 'nomask'=>1, 'size' => 40, 'class' => 'tbox e-password', 'generate' => 1, 'strength' => 1, 'required'=>0)),								
			'page_sef' 			=> array('title'=> LAN_SEFURL, 		'tab' => 1,	'type' => 'text', 'batch'=>true,	'data'=>'str', 'inline'=>true, 'width' => 'auto', 'writeParms'=>'size=xxlarge&sef=page_title'),
			'page_metatitle' 	=> array('title'=> LAN_META_TITLE, 	'tab' => 1,	'type' => 'text', 	'data'=>'str', 'width' => 'auto', 'inline'=>true, 'writeParms'=>['size'=>'xxlarge']),
			'page_metadscr' 	=> array('title'=> LAN_META_DESCRIPTION, 		'tab' => 1,	'type' => 'textarea', 	'data'=>'str', 'help'=>CUSLAN_82, 'width' => 'auto', 'writeParms'=>array('size'=>'xxlarge', 'rows'=>2, 'maxlength'=>155)),
			'page_metakeys' 	=> array('title'=> LAN_KEYWORDS, 		'tab' => 1,	'type' => 'tags', 	'data'=>'str', 'width' => 'auto', 'inline'=>true),

			'page_metaimage' 	=> array('title'=> CUSLAN_81, 		 'nolist'=>false, 'tab' => 1,	'type' => 'image', 'help'=> CUSLAN_82, 		'width' => '110px',	'thclass' => 'center', 			'class' => "center", 'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParms'=>'media=page^&video=1', 'readonly'=>false),

			'page_metarobots'		=> array('title' => LAN_ROBOTS, 'tab'=>1,	'type' => 'dropdown',  'data'=>'safestr', 'batch'=>true,   'inline'=>true, 'readParms'=>array('type'=>'checkboxes'), 'width' => 'auto', 	'thclass' => 'left', 			'class' => 'left', 		'nosort' => false, 'filter'=>true),

			'page_order' 		=> array('title'=> LAN_ORDER, 		'tab' => 1,	'type' => 'number', 'width' => 'auto', 'inline'=>true),
			'page_fields'       => array('title'=>'Custom Fields',  'tab'=>4, 'type'=>'hidden', 'data'=>'json', 'width'=>'auto'),


			// Menu Tab  XXX 'menu_name' is 'menu_name' - not caption. 
			'menu_name' 		=> array('title'=> CUSLAN_64, 		'tab' => 2,	'type' => 'text', 'inline'=>false, 'width' => 'auto','nolist'=>false, "help"=>"Will be listed in the Menu-Manager under this name or may be called using {CMENU=name} in your theme. Must use ASCII characters only and be all lowercase."),
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

		/**
		 * The columns that make a row a custom page or a custom menu when any of them is filled in, keyed by half.
		 *
		 * @var array
		 */
		private static $filledBy = array(
			'page_' => array('page_title', 'page_text'),
			'menu_' => array('menu_title', 'menu_name', 'menu_image', 'menu_icon'),
		);

		function init()
		{

			$this->fields['page_metarobots']['writeParms']['optArray'] = e107::getSingleton('eResponse')->getRobotTypes();
			$this->fields['page_metarobots']['writeParms']['title'] = e107::getSingleton('eResponse')->getRobotDescriptions();
			$this->fields['page_metarobots']['writeParms']['multiple'] = 1;



			$mode = $this->getMode();

			$this->listScope = ($mode === 'overview') ? '' : $this->filledScope($mode === 'menu' ? 'menu_' : 'page_');

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

			
			if($mode === 'overview' && $this->getAction() === 'list' && !empty($_POST['menu_delete']) && $this->hasModeRouteAccess('delete')) // Delete a Page/Menu combination (or rather, remove it's data )
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
			
				$this->listQry = "SELECT SQL_CALC_FOUND_ROWS p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id"; // without any Order or Limit.
		//	$this->gridQry = $this->listQry;
				$this->listOrder 		= 'p.page_order asc'; // 'p.page_id desc';
				$this->fieldPrefName = 'cPageMenu';

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
				

			if($this->getAction() == 'create' && deftrue('e_DEBUG'))
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

			if($this->getAction() === 'create' && $this->savedHalves()['page_'])
			{
				$this->fields['page_chapter']['writeParms']['ajax'] = array('src'=>e_SELF."?mode=page&action=chapter-change",'target'=>'tabadditional');
			}

			$this->holdFieldsToSavedHalves();
/*

			if(e_AJAX_REQUEST)
			{
				// @todo insert placeholder examples in params input when 'type' dropdown value is changed
			}
*/



			if(e_AJAX_REQUEST && isset($_POST['page_chapter']) && $this->getRequest()->canonicalizeAction($this->getAction()) === $this->getRequest()->canonicalizeAction('chapter-change'))
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
			$chap = empty($chap) ? (int) $this->getPosted('page_chapter') : $chap;

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

		/**
		 * Whether this administrator holds each half of a row, keyed by the prefix of that half's fields.
		 *
		 * @return array
		 */
		private function heldHalves()
		{
			return array('page_' => getperms('5'), 'menu_' => getperms('J'));
		}

		/**
		 * Whether this route saves each half of a row, keyed by the prefix of that half's fields.
		 *
		 * @return array
		 */
		private function savedHalves()
		{
			$mode = $this->getMode();

			if($mode === 'overview' || $this->getRequest()->canonicalizeAction($this->getAction()) === 'create')
			{
				return $this->heldHalves();
			}

			return array('page_' => ($mode === 'page'), 'menu_' => ($mode === 'menu'));
		}

		/**
		 * The SQL condition that selects the rows with the named half filled in, as its list shows them.
		 *
		 * @param string $half
		 * @return string
		 */
		private function filledScope($half)
		{
			return implode(" != '' OR ", self::$filledBy[$half])." != ''";
		}

		/**
		 * Whether a row has each half filled in, as {@see page_admin_ui::filledScope()} selects it.
		 *
		 * @param array $row
		 * @return array
		 */
		private function filledHalves($row)
		{
			$filled = array();

			foreach(self::$filledBy as $half => $columns)
			{
				$filled[$half] = false;

				foreach($columns as $column)
				{
					$filled[$half] = $filled[$half] || (string) varset($row[$column]) !== '';
				}
			}

			return $filled;
		}

		/**
		 * The half a column belongs to, null for the row's id, the order both lists sort by, and the chapter, which {@see page_admin_ui::savesChapterOf()} places row by row.
		 *
		 * @param string $column
		 * @return string|null
		 */
		private function columnHalf($column)
		{
			$half = substr($column, 0, 5);

			if(in_array($column, array($this->pid, 'page_order', 'page_chapter'), true) || ($half !== 'page_' && $half !== 'menu_'))
			{
				return null;
			}

			return $half;
		}

		/**
		 * Whether this administrator holds the half a column belongs to, as it holds every column of neither half.
		 *
		 * @param string $column
		 * @return bool
		 */
		private function holdsColumn($column)
		{
			$half = $this->columnHalf($column);

			return $half === null || $this->heldHalves()[$half];
		}

		/**
		 * Whether this route saves the chapter of a row: the page half's on a row that is a custom page, the menu half's on a menu-only row.
		 *
		 * @param array $row
		 * @return bool
		 */
		private function savesChapterOf($row)
		{
			$filled = $this->filledHalves($row);

			return $this->savedHalves()[$filled['page_'] ? 'page_' : 'menu_'];
		}

		/**
		 * Whether this route saves the chapter of the listed row with the given id, for the list to draw its editor.
		 *
		 * @param int $id
		 * @return bool
		 */
		public function savesListedChapter($id)
		{
			$node = $this->getTreeModel()->getNode($id);

			return $node !== null && $this->savesChapterOf($node->getData());
		}

		/**
		 * The page columns a create that does not save the page half fills in itself, as a custom page form fills them for its author.
		 *
		 * @param array $posted
		 * @return array
		 */
		private function pageColumnsFilledOnCreate($posted = array())
		{
			return array(
				'page_author'    => e107::getUser()->getId(),
				'page_datestamp' => time(),
				'page_sef'       => eHelper::title2sef((string) varset($posted['menu_name'])),
			);
		}

		/**
		 * Keep the fields of a half this route does not save out of the form, the model, the inline editor and the batch, and mark those of a half this administrator does not hold as neither listed, sorted, filtered nor searched.
		 *
		 * @return void
		 */
		private function holdFieldsToSavedHalves()
		{
			$saved = $this->savedHalves();
			$filled = ($this->getRequest()->canonicalizeAction($this->getAction()) === 'create' && !$saved['page_']) ? $this->pageColumnsFilledOnCreate() : array();
			$drawn = array();

			if(!$saved['page_'] && isset($this->fields['page_chapter'], $this->fields['menu_name']['tab']))
			{
				$this->fields['page_chapter']['tab'] = $this->fields['menu_name']['tab'];
			}

			foreach($this->fields as $key => $att)
			{
				$half = $this->columnHalf($key);

				if($half !== null && !$saved[$half])
				{
					$att = array_merge($att, array('noedit' => true, 'data' => isset($filled[$key]) ? $att['data'] : false, 'inline' => false, 'batch' => false));
				}

				if(!$this->holdsColumn($key))
				{
					$att = array_merge($att, array('nolist' => true, 'filter' => false, 'search' => false));

					if($this->getQuery('field') === $key)
					{
						$this->getRequest()->setQuery('field', null);
					}
				}

				$this->fields[$key] = $att;

				if(isset($att['tab']) && !empty($att['type']) && empty($att['noedit']) && $key !== $this->pid)
				{
					$drawn[$att['tab']] = true;
				}
			}

			$this->tabs = array_intersect_key($this->tabs, $drawn);
		}

		/**
		 * Keep the chapter of the row this request edits out of the form, the model and the inline editor when the route does not save the half the chapter belongs to on that row.
		 *
		 * @return void
		 */
		private function holdChapterToRow()
		{
			$model = $this->getModel()->load($this->getId());

			if(!$model->getId() || $this->savesChapterOf($model->getData()))
			{
				return;
			}

			$this->fields['page_chapter'] = array_merge($this->fields['page_chapter'], array('noedit' => true, 'data' => false, 'inline' => false, 'batch' => false));
			$model->setDataFields(array_diff_key($model->getDataFields(), array('page_chapter' => true)));
		}

		/**
		 * Whether this administrator holds every half each selected row has filled in, adding the refusal when not.
		 *
		 * @param array $selected
		 * @return bool
		 */
		private function holdsFilledHalvesOf($selected)
		{
			$held = $this->heldHalves();
			$tree = $this->getTreeModel();

			foreach($selected as $id)
			{
				if(in_array(false, array_intersect_key($held, array_filter($this->filledHalves($tree->getNode($id)->getData()))), true))
				{
					e107::getMessage()->addError(LAN_NO_PERMISSIONS);

					return false;
				}
			}

			return true;
		}

		private function loadCustomFieldsData()
		{
			$cf = e107::getCustomFields();

			$cf->loadData($this->getModel()->get('page_fields'))->setAdminUIData('page_fields',$this);

		//	e107::getDebug()->log($cf);


		}


		function CreateObserver()
		{
			parent::CreateObserver();

			if($this->savedHalves()['page_'])
			{
				$this->initCustomFields(0);
			}

			$this->initSEOFields();
		}



		// Override default so we can alter the field db table data after it is loaded. .
		function EditObserver()
		{

			parent::EditObserver();

			$this->holdChapterToRow();

			if($this->getModel()->getId() && $this->savedHalves()['page_'])
			{
				$this->initCustomFields((int) $this->getModel()->get('page_chapter'));
				$this->loadCustomFieldsData();
			}

			$this->initSEOFields();

		}

		/**
		 * Load the row the inline editor writes before it checks the field, so the chapter is held to that row.
		 *
		 * @return void
		 */
		protected function InlineAjaxObserver()
		{
			$this->holdChapterToRow();
		}

		private function initSEOFields()
		{
			eHelper::syncSEOTitle('page-title', 'page-metatitle');

			$seoTitleLimit = (int) e107::pref('core', 'seo_title_limit', 100);
			$seoDescriptionLimit = (int) e107::pref('core', 'seo_description_limit', 180);

			$this->fields['page_metatitle']['writeParms']['counter'] = $seoTitleLimit;
			$this->fields['page_metatitle']['help'] = e107::getParser()->lanVars(LAN_SEARCH_ENGINES_X_LIMIT, $seoTitleLimit);
			$this->fields['page_metadscr']['writeParms']['counter'] = $seoDescriptionLimit;
			$this->fields['page_metadscr']['help'] = e107::getParser()->lanVars(LAN_SEARCH_ENGINES_X_LIMIT, $seoDescriptionLimit);

		}


		/**
		 * Filter/Process Posted page_field data;
		 * @param $new_data
		 * @return null
		 *//*





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
					if(isset($this->chapters[$chap]))
					{
                        $model->set('chapter_sef', $this->chapters[$chap]['chapter_sef']);
                        $parent = (int) $this->chapters[$chap]['chapter_parent'];
                        $model->set('book_sef', $this->chapters[$parent]['chapter_sef']);
					}
				}
				else
				{
                    $urlData = $this->url;
                    $urlData['route'] = 'page/view/other';
                    $model->setUrl($urlData);
                }

            }
        }

		/**
		 * The selected rows a batch write of the field reaches: for the chapter, only those whose chapter this route saves, saying how many it leaves out.
		 *
		 * @param array $selected
		 * @param string $field
		 * @return array
		 */
		private function rowsSavingField($selected, $field)
		{
			if($field !== 'page_chapter')
			{
				return $selected;
			}

			$kept = array_values(array_filter($selected, array($this, 'savesListedChapter')));

			if(count($kept) < count($selected))
			{
				e107::getMessage()->addWarning(e107::getParser()->lanVars(defset('CUSLAN_CHAPTER_ROWS_LEFT_OUT', '[x] selected record(s) were left in their chapter, which belongs to a custom page or menu this list does not edit.'), count($selected) - count($kept), true));
			}

			return $kept;
		}

		/**
		 * A batch write of a field reaches only the rows {@see page_admin_ui::rowsSavingField()} keeps.
		 *
		 * @param array $selected
		 * @param string $field
		 * @param mixed $value
		 * @return int|null
		 */
		protected function handleListBatch($selected, $field, $value)
		{
			return parent::handleListBatch($this->rowsSavingField($selected, $field), $field, $value);
		}

		/**
		 * A boolean batch on a field reaches only the rows {@see page_admin_ui::rowsSavingField()} keeps.
		 *
		 * @param array $selected
		 * @param string $field
		 * @param mixed $value
		 * @return void
		 */
		protected function handleListBoolBatch($selected, $field, $value)
		{
			parent::handleListBoolBatch($this->rowsSavingField($selected, $field), $field, $value);
		}

		/**
		 * A reversing boolean batch on a field reaches only the rows {@see page_admin_ui::rowsSavingField()} keeps.
		 *
		 * @param array $selected
		 * @param string $field
		 * @return void
		 */
		protected function handleListBoolreverseBatch($selected, $field)
		{
			parent::handleListBoolreverseBatch($this->rowsSavingField($selected, $field), $field);
		}

		/**
		 * An attach, detach or userclass batch on a field reaches only the rows {@see page_admin_ui::rowsSavingField()} keeps.
		 *
		 * @param array $selected
		 * @param string $field
		 * @param mixed $value
		 * @param string $type
		 * @return void
		 */
		public function handleCommaBatch($selected, $field, $value, $type)
		{
			parent::handleCommaBatch($this->rowsSavingField($selected, $field), $field, $value, $type);
		}

		/**
		 * Leave the columns of a half this administrator does not hold out of the export, which otherwise streams every column of the row.
		 *
		 * @param array $selected
		 * @return void
		 */
		protected function handleListExportBatch($selected)
		{
			$exclude = array();

			foreach(array_keys($this->getTreeModel()->getNode(reset($selected))->getData()) as $column)
			{
				if(!$this->holdsColumn($column))
				{
					$exclude[] = $column;
				}
			}

			$this->getTreeModel()->setParam('export_exclude', $exclude);

			parent::handleListExportBatch($selected);
		}

		/**
		 * Search no column of a half this administrator does not hold, which would tell it whether the column holds a word.
		 *
		 * @param string $selected
		 * @return string
		 */
		protected function handleListSearchfieldFilter($selected)
		{
			return $this->holdsColumn($selected) ? parent::handleListSearchfieldFilter($selected) : '';
		}

		/**
		 * Filter the list by no column of a half this administrator does not hold, which would tell it whether the column holds a value.
		 *
		 * @param string $filter_value
		 * @return array|string
		 */
		protected function _parseFilterRequest($filter_value)
		{
			$filter = parent::_parseFilterRequest($filter_value);

			return (is_array($filter) && isset($filter[0]) && !$this->holdsColumn($filter[0])) ? array() : $filter;
		}

		/**
		 * A copy creates rows with every half the selected rows have, so it answers to holding each of them.
		 *
		 * @param array $selected
		 * @return void
		 */
		protected function handleListCopyBatch($selected)
		{
			if($this->holdsFilledHalvesOf($selected))
			{
				parent::handleListCopyBatch($selected);
			}
		}

		/**
		 * A feature box item carries a selected row's page and menu columns into the feature box, so it answers to holding each half the row has.
		 *
		 * @param array $selected
		 * @return void
		 */
		protected function handleListFeatureboxBatch($selected)
		{
			if($this->holdsFilledHalvesOf($selected))
			{
				parent::handleListFeatureboxBatch($selected);
			}
		}

		function afterCreate($new_data, $old_data, $id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
			
			$menu_name = $tp->toDB(varset($new_data['menu_name'], '')); // not to be confused with menu-caption.
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
			
			return $new_data;
			
		}
		
		function beforeCreate($new_data, $old_data)
		{

			if(!$this->savedHalves()['page_'])
			{
				$new_data = array_merge($new_data, $this->pageColumnsFilledOnCreate($new_data));
			}

			$new_data = e107::getCustomFields()->processDataPost('page_fields',$new_data);

			if(isset($new_data['menu_name']))
			{
				$new_data['menu_name'] = preg_replace('/[^\w\-*]/','-',$new_data['menu_name']);
			}

			if(empty($new_data['page_sef']))
			{
				if(!empty($new_data['page_title']))
				{
					$new_data['page_sef'] = eHelper::title2sef($new_data['page_title']);
				}
				elseif(!empty($new_data['menu_name']))
				{
					$new_data['page_sef'] = eHelper::title2sef($new_data['menu_name']);
				}
		
			}
			else
			{
				$new_data['page_sef'] = eHelper::secureSef($new_data['page_sef']);
			}


		//	$new_data = $this->processCustomFieldData($new_data);


			$sef = e107::getParser()->toDB($new_data['page_sef']);

			if(isset($new_data['page_title']) && isset($new_data['menu_name']) && empty($new_data['page_title']) && empty($new_data['menu_name']))
			{
				e107::getMessage()->addError(CUSLAN_79);
				return false;
			}

			if(e107::getDb()->count('page', '(*)', "page_sef='{$sef}'"))
			{
				e107::getMessage()->addError(CUSLAN_57);
				return false;
			}


			return $new_data;	
		}
		
		function beforeUpdate($new_data,$old_data, $id)
		{

			if(isset($new_data['page_title']) && isset($new_data['menu_name']) && empty($new_data['page_title']) && empty($new_data['menu_name']))
			{
				e107::getMessage()->addError(CUSLAN_79);
				return false;
			}
			$new_data = e107::getCustomFields()->processDataPost('page_fields',$new_data);

			if(isset($new_data['menu_name']))
			{
				$new_data['menu_name'] = preg_replace('/[^\w\-*]/','',$new_data['menu_name']);
			}


			return $new_data;	
		}		
		
		// Update Menu in Menu Table
		function afterUpdate($new_data, $old_data, $id)
		{
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();

			if(!isset($new_data['menu_name']))
			{
				return true;
			}
					
			$menu_name = $tp->toDB($new_data['menu_name']); // not to be confused with menu-caption.
				
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
				return $this->afterCreate($new_data,$old_data,$id);
				
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
