<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News Administration
 *
*/

require_once(__DIR__.'/../class2.php');

if (!getperms('H|N|H0|H1|H2|H3|H4|H5'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('newspost', true);


e107::css('inline', "

.submitnews.modal-body {    height: 500px;  overflow-y: scroll; }

");




class news_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'news_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'news_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		),
		'cat'		=> array(
			'controller' 	=> 'news_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'news_cat_form_ui',
			'uipath' 		=> null
		),
		'sub'		=> array(
			'controller' 	=> 'news_sub_ui',
			'path' 			=> null,
			'ui' 			=> 'news_sub_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		)
	);


	protected $access = array(); // as below, but uses userclasses instead of admin perms eg. e_UC_* or numeric userclass value.


	//Route access. (equivalent of getperms() for each mode/action )
	protected $perm = array(
		'main/list'     => 'H|H0|H1|H2',
		'main/create'   => 'H|H0',
		'main/edit'     => 'H|H1', // edit button and inline editing in list mode.
		'main/delete'   => 'H|H2', // delete button in list mode.
		'cat/list'      => 'H',
		'cat/create'    => 'H|H3|H4|H5',
		'cat/edit'      => 'H|H4', // edit button and inline editing in list mode.
		'cat/delete'    => 'H|H5', // delete button in list mode.
		'main/prefs'    => '0',
		'sub/list'      => 'N'
	);



	protected $adminMenu = array(
		'main/list'			=> array('caption'=> LAN_LIST, 'icon'=>'fa-list'),
		'main/create' 		=> array('caption'=> NWSLAN_45, 'icon'=> 'fa-plus'),  // Create/Edit News Item
	//	'cat/list' 			=> array('caption'=> NWSLAN_46, 'perm' => '7'), // Category List
		'other' 		=> array('divider'=> true),
		'cat/list' 			=> array('caption'=> LAN_CATEGORIES, 'icon'=> 'folder'), // Create Category.
		'cat/create' 		=> array('caption'=> LAN_NEWS_63, 'icon'=> 'fas-folder-plus'), // Category List
		'other2' 		=> array('divider'=> true),
		'main/prefs' 	=> array('caption'=> LAN_PREFS, 'icon'=>'fa-cog'), // Preferences
	//	'main/submitted'	=> array('caption'=> LAN_NEWS_64, 'perm' => 'N'), // Submitted News
		'sub/list'			=> array('caption'=> NWSLAN_47, 'icon'=>'fa-user-edit'), // Submitted News
	//	'main/maint'		=> array('caption'=> LAN_NEWS_55, 'perm' => '0') // Maintenance
	);

	protected $adminMenuIcon = 'e-news-24';


	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list',
		'cat/edit'	=> 'cat/list'
	);

	protected $menuTitle = ADLAN_0;

	function init()
	{


		if(!empty($_GET['sub']) && $_GET['action'] == 'create')
		{
			$this->adminMenu['sub/list']['selected'] = true;
			$this->getResponse()->setTitle(NWSLAN_47);
		}



	}


}


class news_cat_ui extends e_admin_ui
{
		protected $pluginTitle	= ADLAN_0; // "News"
		protected $pluginName	= 'core';
		protected $eventName	= 'news-category';
		protected $table 		= "news_category";
		protected $pid			= "category_id";
		protected $perPage = 0; //no limit
		protected $batchDelete = false;
		protected $batchExport = true;
		protected $sortField = 'category_order';
		protected $listOrder	= "category_order ASC";

		protected $tabs = array(LAN_GENERAL, LAN_ADVANCED);
		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'category_id'				=> array('title'=> LAN_ID,				'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'category_icon' 			=> array('title'=> LAN_ICON,			'type' => 'icon', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>array('legacy'=>'{e_IMAGE}icons/'), 'writeParms' => 'glyphs=1', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),		 // thumb=60&thumb_urlraw=0&thumb_aw=60
         	'category_name' 			=> array('title'=> LAN_TITLE,			'type' => 'text',	'data'=>'str',		'inline'=>true, 'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'validate' => true, 'writeParms'=>array('size'=>'xxlarge')),
         
         	'category_meta_description' => array('title'=> LAN_DESCRIPTION,		'type' => 'textarea',	'data'=>'str',	'inline'=>true, 'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>FALSE, 'writeParms'=>array('size'=>'xxlarge')),
			'category_meta_keywords' 	=> array('title'=> LAN_KEYWORDS,		'type' => 'tags',		'data'=>'str',	'inline'=>true, 'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),
			'category_sef' 				=> array('title'=> LAN_SEFURL,	'type' => 'text', 'data'=>'str',	'inline'=>true,	'width' => 'auto', 'readonly'=>FALSE, 'writeParms'=>array('size'=>'xxlarge', 'sef'=>'category_name')), // Display name
			'category_manager' 			=> array('title'=> LAN_MANAGER,'type' => 'userclass',	'tab'=>1,	'inline'=>true, 'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'category_template'         => array('title'=> LAN_TEMPLATE,        'type' => 'layouts', 'tab'=>1, 'width'=>'auto', 'thclass' => 'left', 'class'=> 'left', 'writeParms' => array(),'help'=>'Template to use as the default view' ),
	
					'category_order' 			=> array('title'=> LAN_ORDER,			'type' => 'text',	'tab'=>1,		'width' => 'auto', 'thclass' => 'right', 'class'=> 'right' ),
		'options' 					=> array('title'=> LAN_OPTIONS,			'type' => null,		'batch'=>true, 'filter'=>true,		'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center', 'sort' => true)
		);

		protected $fieldpref = array('checkboxes', 'category_icon', 'category_id', 'category_name', 'category_description', 'category_sef', 'category_manager', 'category_order', 'options');
		
	//	protected $newspost;
	
		function init()
		{
			$this->fields['category_template']['writeParms'] = array('plugin' => 'news', 'id' => 'news', 'merge' => false, 'default' => '('.LAN_OPTIONAL.')');
			// $this->newspost = new admin_newspost;
		}




		
	//	function createPage()
	//	{
		//	$this->newspost->show_categories();
	//	}
		
		public function beforeCreate($new_data, $old_data)
		{
			if(empty($new_data['category_sef']))
			{
				$new_data['category_sef'] = eHelper::title2sef($new_data['category_name']);
			}
			else 
			{
				$new_data['category_sef'] = eHelper::secureSef($new_data['category_sef']);
			}

			$sef = e107::getParser()->toDB($new_data['category_sef']);
			
			if(e107::getDb()->count('news_category', '(*)', "category_sef='$sef'"))
			{
				e107::getMessage()->addError(LAN_NEWS_65);
				return false;
			}
			
			if(empty($new_data['category_order']))
			{
				$c = e107::getDb()->count('news_category');
				$new_data['category_order'] = $c ? $c : 0;
			}
			
			return $new_data;
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{
			if(isset($new_data['category_sef']) && empty($new_data['category_sef']))
			{
				$new_data['category_sef'] = eHelper::title2sef($new_data['category_name']);
			}

			$sef = e107::getParser()->toDB($new_data['category_sef']);

		/*	$message = "Error: sef: ".$sef."   id: ".$id."\n";
			$message .= print_r($new_data,true);
			file_put_contents(e_LOG.'uiAjaxResponseInline.log', $message."\n\n", FILE_APPEND);*/

			if(e107::getDb()->count('news_category', '(*)', "category_sef='$sef' AND category_id !=".intval($id)))
			{
				e107::getMessage()->addError(LAN_NEWS_65);
				return false;
			}

			return $new_data;
		}

}

class news_cat_form_ui extends e_admin_form_ui
{

}





// Submitted News Area. 


class news_sub_ui extends e_admin_ui
{
		protected $pluginTitle	= ADLAN_0; // "News"
		protected $pluginName	= 'core';
		protected $table 		= "submitnews";
		protected $pid			= "submitnews_id";
		protected $perPage 		= 10; //no limit
		protected $batchDelete 	= true;
		protected $formQuery	= "mode=main&amp;action=create";
		protected $listOrder	= "submitnews_id desc";
		
		

// submitnews_id 	submitnews_name 	submitnews_email 	submitnews_title 	submitnews_category 	submitnews_item 	submitnews_datestamp 	submitnews_ip 	submitnews_auth 	submitnews_file		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'submitnews_id'				=> array('title'=> LAN_ID,				'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
			'submitnews_datestamp'		=> array('title' => LAN_NEWS_32, 		'type' => 'datestamp', 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),

			'submitnews_title' 			=> array('title'=> LAN_TITLE,			'type' => 'method',			'width' => '35%', 'thclass' => 'left', 'readonly'=>TRUE),

			'submitnews_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),		
			'submitnews_description' 	=> array('title'=> LAN_DESCRIPTION,		'type' => 'textarea',			'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>TRUE),
			'submitnews_name' 			=> array('title'=> LAN_AUTHOR,			'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>TRUE),
       		'submitnews_ip' 			=> array('title'=> LAN_IP,			'type' => 'ip',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>TRUE),
			'submitnews_auth' 			=> array('title'=> " ",			'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'class'=> 'left', 'readParms'=>"link=1" ),
			'options' 					=> array('title'=> LAN_OPTIONS,			'type' => "method",				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'right')
		);

		protected $fieldpref = array('checkboxes', 'submitnews_id', 'submitnews_datestamp', 'submitnews_title', 'submitnews_category', 'submitnews_name', 'options');
		
		protected $newspost;
		
		protected $cats;
	
		function init()
		{
			$sql = e107::getDb();
			$sql->gen("SELECT category_id,category_name FROM #news_category");
			while($row = $sql->fetch())
			{
				$cat = $row['category_id'];
				$this->cats[$cat] = $row['category_name'];
			}
			asort($this->cats);
			$this->fields['submitnews_category']['writeParms'] = $this->cats;
	//		$this->newspost = new admin_newspost;
		}
		
	//	function createPage()
	//	{
		//	$this->newspost->show_categories();
	//	}
		
		public function beforeCreate($new_data, $old_data)
		{
	
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{
	
		}

}

class news_sub_form_ui extends e_admin_form_ui
{
	
	
	
	function submitnews_title($cur,$val)
	{
		$tp = e107::getParser();
		$row = $this->getController()->getListModel();
		
		$submitnews_id 		= $row->get('submitnews_id');
		$submitnews_title 	= $row->get('submitnews_title');
		$submitnews_file 	= $row->get('submitnews_file');
		$submitnews_item 	= $row->get('submitnews_item');

	//	$text .= "<a href='#submitted_".$submitnews_id."' class='e-modal'  >";
		
		
		$text   = "<a data-toggle='modal' data-bs-toggle='modal' href='#submitted_".$submitnews_id."' data-cache='false' data-target='#submitted_".$submitnews_id."' class='e-tip' title='".LAN_PREVIEW."'>";
		$text .= $tp->toHTML($submitnews_title,FALSE,'emotes_off, no_make_clickable');	
		$text .= '</a>';
		
		$text .= '

		 <div id="submitted_'.$submitnews_id.'" class="modal fade" tabindex="-1" role="dialog"  aria-hidden="true">
		 <div class="modal-dialog modal-lg" >
             <div class="modal-content">
			    <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			   <h4>'.$tp->toHTML($submitnews_title,false,'TITLE').'</h4>
			    </div>
			    <div class="submitnews modal-body">
			    <p>';
		
		$text .= $tp->toHTML($submitnews_item,TRUE);
				
		if($submitnews_file)
		{
			$tmp = explode(',',$submitnews_file);
			
			$text .= "<br />";
			
			
			foreach($tmp as $imgfile)
			{
				if(strpos("{e_UPLOAD}",$imgfile) === false)
				{
					$imgfile = e_UPLOAD.$imgfile;
				}

				$url = $tp->thumbUrl($imgfile,array('aw'=>400),true);
				$text .= "<br /><img src='".$url."' alt='".$imgfile."' />";					
			}
		}
		
			    
		$text .= '</p>
			    </div>
				    <div class="modal-footer">
				    <a href="#" data-dismiss="modal" class="btn btn-primary">'.LAN_NEWS_67.'</a>
				    </div>
			    </div>
			    </div></div>';
			
		return $text;	
			


	}
	
	// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		
		if($attributes['mode'] == 'read')
		{
			$text = "<div class='btn-group'>";
			$approved = $this->getController()->getListModel()->get('submitnews_auth'); // approved;


				$row = $this->getController()->getListModel();

		$submitnews_id 		= $row->get('submitnews_id');
		$submitnews_title 	= $row->get('submitnews_title');
		$submitnews_file 	= $row->get('submitnews_file');
		$submitnews_item 	= $row->get('submitnews_item');

	//	$text .= "<a href='#submitted_".$submitnews_id."' class='e-modal'  >";


			$text   = "<a class='btn btn-default btn-secondary  btn-large' data-toggle='modal' data-bs-toggle='modal' href='#submitted_".$submitnews_id."' data-cache='false' data-target='#submitted_".$submitnews_id."'  title='".LAN_PREVIEW."'>".defset('ADMIN_VIEW_ICON')."</a>";




			if($approved == 0)
			{
				//$text = $this->submit_image('submitnews['.$id.']', 1, 'execute', NWSLAN_58);
				$text .= "<a class='btn btn-default btn-secondary btn-large' title=\"".LAN_NEWS_96."\" href='".e_SELF."?mode=main&action=create&sub=$id'>".defset('ADMIN_EXECUTE_ICON')."</a>";
				// NWSLAN_103;	
			} 

					
			$text .= $this->submit_image('etrigger_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'btn btn-default btn-secondary btn-large action delete'));
			$text .= "</div>";
			return $text;
		}
	}
		
}

// Main News Area. 

class news_admin_ui extends e_admin_ui
{
	protected $pluginTitle	= ADLAN_0; // "News"
	protected $pluginName	= 'core';
	protected $eventName    = 'news';
	protected $table 		= "news";
	protected $pid			= "news_id";
	protected $perPage 		= 10; //no limit
	protected $batchDelete 	= true;
	protected $batchExport  = true;
	protected $batchCopy 	= true;
    protected $batchLink    = true;
	protected $listQry      = "SELECT n.*,  nc.category_name, nc.category_sef, u.user_id,u.user_name FROM `#news` AS n 
                               LEFT JOIN `#news_category` AS nc ON n.news_category = nc.category_id 
                               LEFT JOIN `#user` AS u ON n.news_author = u.user_id "; // without any Order or Limit.

	protected $listOrder	= "news_id desc";

	protected $tabs         = array(LAN_NEWS_52, 'SEO', LAN_NEWS_53);

    protected $url          = array(
                             'route'        => 'news/view/item',
                             'name'         => 'news_title',
                             'description'  => 'news_summary',
                             'vars'=> array('news_id' => true, 'news_sef' => true, 'category_id' => 'news_category', 'category_sef' => true)
                             ); // 'link' only needed if profile not provided.
    

		
	protected $fields = array(
		'checkboxes'	   		=> array('title' => '', 			'type' => null, 		'width' => '3%', 	'thclass' => 'center first', 	'class' => 'center', 	'nosort' => true, 'toggle' => 'news_selected', 'forced' => TRUE),
		'news_id'				=> array('title' => LAN_ID, 	    'type' => 'text', 	    'width' => '5%', 	'thclass' => 'center', 			'class' => 'center',  	'nosort' => false, 'readParms'=>'link=sef&target=blank'),
 		'news_thumbnail'		=> array('title' => NWSLAN_67, 		'type' => 'method', 'data'=>'str',	'width' => '110px',	'thclass' => 'center', 			'class' => "center", 		'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60',  'readonly'=>false),
 		'news_title'			=> array('title' => LAN_TITLE, 		'type' => 'text',   'data'=>'safestr',  'filter'=>true,  'tab'=>0, 'writeParms'=> array('required'=> 1, 'size'=>'block-level'), 'inline'=>true,		'width' => 'auto', 'thclass' => '', 				'class' => null, 		'nosort' => false),
		'news_summary'			=> array('title' => LAN_SUMMARY, 	'type' => 'text', 	'data'=>'safestr',  'filter'=>true, 'tab'=>0, 'inline'=>true, 'writeParms'=>'size=block-level',	'width' => 'auto', 	'thclass' => 'left', 				'class' => 'left', 		'nosort' => false),
		'news_body'			    => array('title' => "", 	        'type' => 'method', 'data'=>'str',    'tab'=>0,  'nolist'=>true, 'writeParms'=>'nolabel=1',		'width' => 'auto', 	'thclass' => '',  'class' => null, 		'nosort' => false),
		'news_extended'			=> array('title' => "", 	        'type' => null,     'data'=>'str', 'tab'=>0,  'nolist'=>true, 'noedit'=>true, 'writeParms'=>'nolabel=1',		'width' => 'auto', 	'thclass' => '',  'class' => null, 		'nosort' => false),

		'news_meta_title'	    => array('title' => LAN_META_TITLE, 	    'type' => 'text', 	  'data'=>'safestr', 'filter'=>true, 'tab'=>1,	'inline'=>true, 'width' => 'auto', 'help'=>'', 'writeParms'=>['size'=>'xxlarge', 'placeholder'=>'', 'counter'=>0,  'maxlength'=> 255], 	'nosort' => false),
		'news_meta_keywords'	=> array('title' => LAN_KEYWORDS, 	'type' => 'tags', 	  'data'=>'safestr', 'filter'=>true, 'tab'=>1,	'inline'=>true, 'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'readParms'=>['maxlength'=>255,'maxItems'=>30], 'writeParms' => ['maxItems'=>30, 'maxlength'=>255]),
		'news_meta_description'	=> array('title' => LAN_META_DESCRIPTION,'type' => 'textarea', 'data'=>'safestr','filter'=>true,	'tab'=>1,	'width' => 'auto', 	'thclass' => '', 'help'=>'',		'class' => null, 		'nosort' => false, 'writeParms'=>array('size'=>'xxlarge', 'counter'=>0, 'maxlength'=>255, 'rows'=>2)),
		'news_meta_robots'		=> array('title' => LAN_ROBOTS, 	'type' => 'dropdown',  'data'=>'safestr',  'tab'=>1, 'inline'=>true, 'readParms'=>array('type'=>'checkboxes'), 'writeParms'=>array('multiple'=>1), 'width' => 'auto', 	'thclass' => 'left', 			'class' => 'left', 		'nosort' => false, 'batch'=>true, 'filter'=>true),

		'news_sef'				=> array('title' => LAN_SEFURL, 	'type' => 'text',    'batch'=>1,  'data'=>'str', 'tab'=>1,  'inline'=>true, 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'writeParms'=>array('size'=>'xxlarge', 'show'=>1, 'sef'=>'news_title')),
	//	'news_ping'				=> array('title' => LAN_PING, 	    'type' => 'checkbox',   'tab'=>1, 'data'=>false, 'writeParms'=>'value=0',	'inline'=>true, 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),

		'news_author'			=> array('title' => LAN_AUTHOR, 	'type' => 'method', 	'tab'=>2, 	'readParms'=>'idField=user_id&nameField=user_name', 'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
		'news_datestamp'		=> array('title' => LAN_NEWS_32, 	'type' => 'datestamp', 'data'=>'int', 'tab'=>2,   'writeParms'=>'type=datetime',   'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false,  'filter'=>true, 'batch'=>true),
        'news_category'			=> array('title' => NWSLAN_6, 		'type' => 'dropdown',   'data'=>'int', 'tab'=>0, 'inline'=>true,	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'batch'=>true, 'filter'=>true),
		'news_start'			=> array('title' => LAN_START, 	    'type' => 'datestamp', 'data'=>'int', 'tab'=>2,   'writeParms'=>'type=datetime',	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
       	'news_end'				=> array('title' => LAN_END, 		'type' => 'datestamp',  'data'=>'int', 'tab'=>2,  'writeParms'=>'type=datetime',	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
        'news_class'			=> array('title' => LAN_VISIBILITY, 'type' => 'userclass',  'tab'=>2,   'inline'=>true, 'width' => 'auto', 	'thclass' => '', 				'class' => null,  'batch'=>true, 'filter'=>true),

		'news_template'		    => array('title' => LAN_TEMPLATE, 	'type' => 'method',  'data'=>'safestr',  'tab'=>2,  'inline'=>true, 'writeParms'=>array('plugin'=>'news', 'id'=>'news_view', 'area'=> 'front', 'merge'=>false), 'width' => 'auto', 	'thclass' => 'left', 			'class' => 'left', 		'nosort' => false, 'batch'=>true, 'filter'=>true),

		'news_render_type'		=> array('title' => LAN_LOCATION, 	'type' => 'dropdown',  'data'=>'safestr',  'tab'=>2,  'inline'=>true, 'readParms'=>array('type'=>'checkboxes'), 'width' => 'auto', 	'thclass' => 'left', 			'class' => 'left', 		'nosort' => false, 'batch'=>true, 'filter'=>true),

		'news_sticky'			=> array('title' => LAN_NEWS_28, 	'type' => 'boolean',   'data'=>'int', 'tab'=>2, 'width' => 'auto', 	'thclass' => 'center', 	'inline'=>true,		'class' => 'center', 	'nosort' => false, 'batch'=>true, 'filter'=>true),
    	'news_modified'			=> array('title' => LAN_LAST_UPDATED, 	'type' => 'datestamp', 'readonly'=>true, 'noedit'=>true,  'data'=>'int', 'tab'=>2, 'width' => 'auto', 	'thclass' => 'center', 	'inline'=>false,		'class' => 'center', 	'nosort' => false, 'batch'=>false, 'filter'=>true),

        'news_allow_comments' 	=> array('title' => LAN_COMMENTS, 		'type' => 'boolean',  'data'=>'int',  'tab'=>2,	'writeParms'=>'inverse=1',  'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false,'batch'=>true, 'filter'=>true,'readParms'=>'reverse=1'),
        'news_comment_total' 	=> array('title' => LAN_NEWS_60, 	'type' => 'number',    'data'=>'int', 'tab'=>2,	'noedit'=>true, 'width' => '10%', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
	//	admin_news_notify
		'news_email_notify'     => array('title' => LAN_NEWS_103, 'type' => 'checkbox',   'tab'=>2,  'data'=>false, 'writeParms'=>array('show'=>1, 'tdClassRight'=>'form-inline'), 'help'=>LAN_NEWS_109),
		'submitted_id'          => array('title' => LAN_NEWS_68, 'type' => 'hidden',  'tab'=>2,  'data'=>false, 'writeParms'=>'show=0'),
		'options'				=> array('title' => LAN_OPTIONS, 	'type' => null, 		'width' => '10%', 	'thclass' => 'center last', 	'class' => 'center', 	'nosort' => true, 'forced' => TRUE)

	);
	
	protected $fieldpref = array('checkboxes','news_id', 'news_thumbnail', 'news_title', 'news_datestamp', 'news_category', 'news_class', 'options');

	protected $preftabs = ['general'=>LAN_GENERAL,'admin'=>LAN_HEADER_04, 'subnews'=>LAN_NEWS_101];

	protected $prefs = [
		'news_default_template'   => ['title' => NWSLAN_127, 'type'     => 'dropdown', 'data'=>'safestr', 'help'   => LAN_NEWS_88, 'tab'  => 'general'],
		'newsposts'               => ['title' => NWSLAN_88, 'type'      => 'dropdown', 'data'=>'int', 'tab'    => 'general'],
		'news_list_limit'         => ['title' => LAN_NEWS_91, 'type'    => 'dropdown',  'data'=>'int', 'help'   => LAN_NEWS_92, 'tab'  => 'general'],
		'news_list_templates'     => ['title' => LAN_NEWS_93, 'type'    => 'checkboxes', 'help' => LAN_NEWS_94, 'tab'  => 'general'],
		'news_pagination'         => ['title' => LAN_PAGINATION, 'type' => 'dropdown', 'data'=>'safestr', 'help'   => LAN_NEWS_112, 'tab' => 'general'],
		'news_cache_timeout'      => ['title' => LAN_NEWS_110, 'type'   => 'number', 'data'=>'int', 'help'     => LAN_NEWS_111, 'tab' => 'general'],
		'news_cats'               => ['title' => NWSLAN_86, 'type'      => 'boolean', 'data'=>'int', 'tab'     => 'general'],
		'nbr_cols'                => ['title' => NWSLAN_87, 'type'      => 'dropdown', 'data'=>'int', 'tab'    => 'general'],
		'newsposts_archive'       => ['title' => NWSLAN_115, 'type'     => 'dropdown', 'data'=>'int', 'help'   => NWSLAN_116, 'tab'   => 'general'],
		'newsposts_archive_title' => ['title' => NWSLAN_117, 'type'     => 'text', 'data'=>'safestr', 'tab'        => 'general'],
		'news_newdateheader'      => ['title' => NWSLAN_111, 'type'     => 'boolean', 'data'=>'int', 'help'    => NWSLAN_112, 'tab'   => 'general'],
		'news_unstemplate'        => ['title' => NWSLAN_113, 'type'     => 'boolean', 'data'=>'int', 'help'    => NWSLAN_114, 'tab'   =>'general'],

		'news_editauthor'         => ['title' => LAN_NEWS_51, 'type'    => 'userclass', 'data'=>'int', 'tab'   => 'admin', 'writeParms'=>['classlist'=>'nobody,main,admin,classes']],
		'news_limit_to_self'      => ['title' => LAN_NEWS_113, 'type'   => 'userclass', 'data'=>'int', 'help'=>LAN_NEWS_114, 'tab'   => 'admin', 'writeParms'=>['classlist'=>'nobody,classes,no-excludes']],

		'subnews_class'           => ['title' => NWSLAN_106, 'type'     => 'userclass','data'=>'int', 'tab'   => 'subnews', 'writeParms'=>['classlist'=>'nobody,public,guest,member,admin,classes'] ],
		'subnews_htmlarea'        => ['title' => NWSLAN_107, 'type'     => 'boolean', 'data'=>'int', 'tab'     => 'subnews'],
		'subnews_attach'          => ['title' => NWSLAN_100, 'type'     => 'boolean', 'data'=>'int', 'tab'     => 'subnews'],
		'subnews_attach_minsize'  => ['title' => LAN_NEWS_99, 'type'    => 'dropdown', 'tab'    => 'subnews'],
		'subnews_resize'          => ['title' => NWSLAN_101, 'type'     => 'number', 'data'=>'int', 'tab'      => 'subnews', 'writeParms' => ['maxlength'=>5] ],
		'news_subheader'          => ['title' => NWSLAN_120, 'type'     => 'bbarea', 'tab'      => 'subnews']

	];

	protected $cats = array();
	protected $newspost;
	protected $addons = array();
	
	protected $news_renderTypes = array( // TODO Placement location and template should be separate. 
	
		'0' =>	LAN_NEWS_69,
		'1' =>	LAN_NEWS_70,
		'4' =>	LAN_NEWS_71,
		'2' =>	LAN_NEWS_72,
		'3' =>	LAN_NEWS_73,
		'5' =>	LAN_NEWS_74,
		'6' =>	LAN_NEWS_97,
		//'5' =>  LAN_NEWS_75
	);

	public function beforeCreate($new_data, $old_data)
	{
		if(!empty($new_data['news_thumbnail']) && !empty($_GET['sub'])) // From SubmitNews.
		{
			$new_data['news_thumbnail'] = $this->processSubNewsImages($new_data['news_thumbnail']);
		}


		$new_data['news_thumbnail'] = $this->processThumbs($new_data['news_thumbnail']);

		if(empty($new_data['news_datestamp']))
		{
			$new_data['news_datestamp'] = time();
		}

		$new_data['news_modified'] = time();

		$new_data['news_sef'] =  empty($new_data['news_sef']) ?  eHelper::title2sef($new_data['news_title']) : eHelper::secureSef($new_data['news_sef']);

		$this->checkSEFSimilarity($new_data);


		$tmp = explode(chr(35), $new_data['news_author']);
		$new_data['news_author'] = intval($tmp[0]);

		if(E107_DBG_SQLQUERIES)
		{
			e107::getMessage()->addInfo("<h3>Raw _POST data</h3>".print_a($_POST,true));
		}

		return $new_data;
	}

	private function processSubNewsImages($row)
	{
		$new = array();
		foreach($row as $k=>$v)
		{
			if(empty($v))
			{
				continue;
			}

			$f = str_replace('{e_UPLOAD}','',$v);

			if($bbpath = e107::getMedia()->importFile($f,'news', e_UPLOAD.$f))
			{
				$new[] = $bbpath;
			}
		}


		e107::getMessage()->addDebug("<h3>Processing/importing SubNews Images</h3>".print_a($new,true));

		return implode(",",$new);




	}


	public function beforeUpdate($new_data, $old_data, $id)
	{
		if(!empty($new_data['news_thumbnail']))
		{
			$new_data['news_thumbnail'] = $this->processThumbs($new_data['news_thumbnail']);
		}

		if(isset($new_data['news_datestamp']) && empty($new_data['news_datestamp']))
		{
			$new_data['news_datestamp'] = time();
		}

		if(isset($new_data['news_sef']) && empty($new_data['news_sef']) && !empty($new_data['news_title']))
		{
			$new_data['news_sef'] = eHelper::title2sef($new_data['news_title']);
		}

		$new_data['news_modified'] = time();

		$this->checkSEFSimilarity($new_data);

		if(!empty($new_data['news_author']))
		{
			$tmp = explode(chr(35), $new_data['news_author']);
			$new_data['news_author'] = intval($tmp[0]);
		}

		if(E107_DBG_SQLQUERIES)
		{
			e107::getMessage()->addInfo("<h3>Raw _POST data</h3>".print_a($_POST,true));
		}

		return $new_data;
	}


	/**
	 * Display a warning if there is a mismatch with the SEF Url.
	 * @param $new_data
	 * @return void|null
	 */
	private function checkSEFSimilarity($new_data)
	{
		if(e_LANGUAGE === "Japanese" || e_LANGUAGE === "Korean")
		{
			return;
		}


		$expectedSEF = eHelper::title2sef($new_data['news_title']);
		similar_text($expectedSEF,$new_data['news_sef'],$percSimilar);

		if($percSimilar < 60)
		{
			e107::getMessage()->addWarning(LAN_NEWS_108); // The SEF URL is unlike the title of your news item.
		}


	}


	public function afterCreate($new_data, $old_data, $id)
	{

		if(!empty($_POST['news_email_notify']))
		{
			$this->triggerNotify($new_data);
		}

		if(!empty($new_data['submitted_id']))
		{
			e107::getDb()->update('submitnews', "submitnews_auth = 1 WHERE submitnews_id = ".intval($new_data['submitted_id'])." LIMIT 1");
		}

		if(!empty($new_data['news_sef']) && ($existingSef = e107::getDb()->retrieve('news', 'news_sef', "news_sef = '".$new_data['news_sef']."' AND news_id != ".$id)))
		{
			$existingLAN = e107::getParser()->lanVars(LAN_NEWS_95,$existingSef,true );
			e107::getMessage()->addWarning($existingLAN);
		}



		e107::getEvent()->trigger('newspost',$new_data);
	//	e107::getEvent()->trigger('admin_news_created',$new_data);
		$evdata = array('method'=>'create', 'table'=>'news', 'id'=>$id, 'plugin'=>'news', 'function'=>'submit_item');
		e107::getMessage()->addInfo(e107::getEvent()->triggerHook($evdata));
		$this->clearCache();
	}



	public function afterUpdate($new_data, $old_data, $id)
	{

	//	e107::getMessage()->addInfo(print_a($new_data,true));

		if(!empty($_POST['news_email_notify']))
		{
			$this->triggerNotify($new_data);
		}



		e107::getEvent()->trigger('newsupd', $new_data);
	//	e107::getEvent()->trigger('admin_news_updated',$new_data);

		$this->clearCache();

		if(!empty($new_data['news_sef']) && ($existingSef = e107::getDb()->retrieve('news', 'news_sef', "news_sef = '".$new_data['news_sef']."' AND news_id != ".$id)))
		{
			$existingLAN = e107::getParser()->lanVars(LAN_NEWS_95,$existingSef,true );
			e107::getMessage()->addWarning($existingLAN);
		}

		//$ecache->clear("nq_news_"); - supported by cache::clear() now
		//$ecache->clear("nomd5_news_"); supported by cache::clear() now


		$evdata = array('method'=>'update', 'table'=>'news', 'id'=>$id, 'plugin'=>'news', 'function'=>'submit_item');
		e107::getMessage()->addInfo(e107::getEvent()->triggerHook($evdata));
	}




	// Trigger the news email notification trigger. (@see admin->notify )
	private function triggerNotify($new_data)
	{
		$visibility = explode(",", $new_data['news_class']);

		if(in_array(e_UC_PUBLIC, $visibility))
		{
			e107::getEvent()->trigger('admin_news_notify',$new_data);
			e107::getMessage()->addSuccess(LAN_NEWS_105);
		}
		else
		{
			e107::getMessage()->addWarning(LAN_NEWS_106);
		}


	}



	public function afterDelete($deleted_data, $id, $deleted_check)
	{
		$this->clearCache();
	}

	function clearCache()
	{
		$ecache = e107::getCache();
		$ecache->clear("news.php"); //TODO change it to 'news_*' everywhere
		$ecache->clear("news_", false, true); //NEW global news cache prefix
		$ecache->clear("othernews"); //TODO change it to 'news_other' everywhere
		$ecache->clear("othernews2"); //TODO change it to 'news_other2' everywhere


		//$ecache->clear("nq_news_"); - supported by cache::clear() now
		//$ecache->clear("nomd5_news_"); supported by cache::clear() now
		return $this;
	}

	/**
	 * For future use: multiple-images.
	 */
	private function processThumbs($postedImage)
	{
		if(is_array($postedImage))
		{
			return implode(",", $postedImage);
		}
		else
		{
			return $postedImage;
		}

	}


//


	function ukfield($a, $b) // custom sort order on create/edit pags.
	{

		$newOrder = array (
		'checkboxes',
		'news_id',
		'news_category',
		'news_title' ,
		'news_summary',
		'news_template',
		'news_render_type',

		'news_body',
		'news_extended',
		'news_thumbnail',

		'news_sef' ,
		'news_meta_title',
		'news_meta_description' ,
		'news_meta_keywords',
		'news_meta_robots' ,


		'news_email_notify',
		'news_allow_comments' ,
		'news_start' ,
		'news_end' ,
		'news_author' ,
		'news_datestamp' ,
		'news_class',
		'news_sticky',

		'news_comment_total' ,
		'news_modified',
		'submitted_id',
		'options' );



		foreach($this->addons as $plug=>$config)
		{
			if(!empty($config['fields']))
			{
				foreach($config['fields'] as $field=>$tmp)
				{
					$newOrder[] = "x_".$plug."_".$field;
				//	echo $field;
				}
			}
		}



		$order = array_flip($newOrder);

		if($order[$a] == $order[$b])
		{
			return 0;
		}

		return ($order[$a] < $order[$b]) ? -1 : 1;

	}

	function handleListImageBbcodeBatch($selected, $field, $value)
	{
		$sql = e107::getDb();

		$status = array();

		$ids = implode(",", e107::getParser()->filter($selected,'int'));

		if($data = $sql->retrieve("news","news_id,news_body","news_id IN (".$ids.") ",true))
		{
			foreach($data as $row)
			{
				$id = $row['news_id'];
				$update = array(
					'news_body' => e107::getBB()->imgToBBcode($row['news_body'], true),
					'WHERE' => 'news_id = '.$row['news_id']
				);

				$status[$id] = $sql->update('news',$update) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			}

		}

		$mes = e107::getMessage();

		foreach($status as $k=>$v)
		{
			$mes->add(LAN_UPDATED.": ".$k, $v);
		}

		$this->clearCache();

		return true;
	}


	/**
	 * Returns true when limit news post view/edit to self-authored content is active for the currently logged in administrator.
	 * @return bool
	 */
	private function limitToSelfEnabled()
	{
		if((int) $limit = e107::getPref('news_limit_to_self', false))
		{
			return (check_class($limit) && !getperms('0'));
		}

		return false;
	}


	function init()
	{
		$this->addons = e107::getAddonConfig('e_admin',null, 'config', $this);
/*
		if(!empty($_POST['save_prefs']))
		{
			$this->saveSettings();
		}*/

		if(deftrue('e_DEBUG')) // allowing manual fixing of comment total in DEBUG mode.
		{
			$this->fields['news_comment_total']['noedit'] = false;
			$this->fields['news_comment_total']['inline'] = true;
		}


		$this->fields['news_email_notify']['writeParms']['post'] = "<span class='radio-inline radio inline'><a class='e-modal btn btn-xs btn-mini btn-primary' data-modal-caption='".ADLAN_149."' href='notify.php?iframe=1&type=admin_news_notify#/tab-news-events'>".LAN_CONFIGURE."</a></span>";

		//	e107::getMessage()->addDebug(print_a($_POST,true));

		if($this->getAction() == 'create' ||  $this->getAction() == 'edit')
		{
			uksort($this->fields, array($this, 'ukfield'));

		//	$fieldKeys = array_keys($this->fields);
		//	print_a($fieldKeys);

			if(!empty($_GET['sub']))
			{
				$this->loadSubmitted($_GET['sub']);
			}

		}


		if(deftrue('e_DEBUG'))
		{
			$this->batchOptions['Modify News body'] = array('image_bbcode'=>"Convert all images in news-body to [img] bbcodes.");
		}


		if(deftrue("ADMINUI_NEWS_VISIBILITY_MULTIPLE")) // bc workaround for those who need it. Add to e107_config.php .
		{
			$this->fields['news_class']['type'] = 'userclasses';
		}

	//	$mod = $this->getModel();
	//	$info = print_a($mod, true);

	//	e107::getMessage()->addInfo($info);


		
		
		$sql = e107::getDb();
		$sql->gen("SELECT category_id,category_name FROM #news_category");
		while($row = $sql->fetch())
		{
			$cat = $row['category_id'];
			$this->cats[$cat] = $row['category_name'];
		}
		asort($this->cats);
		$this->fields['news_category']['writeParms']['optArray'] = $this->cats;
		$this->fields['news_category']['writeParms']['size'] = 'xlarge';
		$this->fields['news_render_type']['writeParms']['optArray'] = $this->news_renderTypes; // array(NWSLAN_75,NWSLAN_76,NWSLAN_77,NWSLAN_77." 2","Featurebox");
		$this->fields['news_render_type']['writeParms']['multiple'] = 1;
		$this->fields['news_render_type']['writeParms']['empty'] = '0';

		$this->fields['news_meta_robots']['writeParms']['optArray'] = e107::getSingleton('eResponse')->getRobotTypes();
		$this->fields['news_meta_robots']['writeParms']['title'] = e107::getSingleton('eResponse')->getRobotDescriptions();
		$this->fields['news_meta_robots']['writeParms']['multiple'] = 1;
	//	$this->fields['news_meta_robots']['writeParms']['default'] = 'blank';
	//	$this->newspost = new admin_newspost;
	//	$this->newspost->news_renderTypes = $this->news_renderTypes;
	//	$this->newspost->observer();
 
	}

	private function initSEOFields()
	{
		eHelper::syncSEOTitle('news-title', 'news-meta-title');

		$seoTitleLimit = (int) e107::pref('core', 'seo_title_limit', 100);
		$seoDescriptionLimit = (int) e107::pref('core', 'seo_description_limit', 180);

		$this->fields['news_meta_title']['writeParms']['counter'] = $seoTitleLimit;
		$this->fields['news_meta_title']['help'] = e107::getParser()->lanVars(LAN_SEARCH_ENGINES_X_LIMIT, $seoTitleLimit);
		$this->fields['news_meta_description']['writeParms']['counter'] = $seoDescriptionLimit;
		$this->fields['news_meta_description']['help'] = e107::getParser()->lanVars(LAN_SEARCH_ENGINES_X_LIMIT, $seoDescriptionLimit);

	}

	function EditObserver()
	{

		parent::EditObserver();

		$author = (int) $this->getModel()->get('news_author');

		if($this->limitToSelfEnabled() && $author !== USERID)
		{
			$this->getRequest()->setAction('e403');
			return false;
		}

		$this->initSEOFields();

	}

	function ListObserver()
	{
		if($this->limitToSelfEnabled())
		{
			$this->listQry .= " WHERE n.news_author = ".USERID;
		}

		parent::ListObserver();
	}

	function ListAjaxObserver()
	{
		if($this->limitToSelfEnabled())
		{
			$this->listQry .= " WHERE n.news_author = ".USERID;
		}

		parent::ListAjaxObserver();
	}


	function CreateObserver()
	{
		parent::CreateObserver();
		$this->initSEOFields();
	}

	function beforePrefsSave($new_data, $old_data)
	{

		$new_data['news_default_template']	= preg_replace('#[^\w\pL\-]#u', '', $new_data['news_default_template']);

		return $new_data;
	}

	function afterPrefsSave()
	{
		$this->clearCache();
	}
	function PrefsObserver()
	{
		parent::PrefsObserver();

			$newsTemplates = array();
			$pref = e107::getPref();

			if($newInfo = e107::getTemplateInfo('news', 'news', null, 'front', true))  //TODO  'category'=>'Categories'? research 'Use non-standard template for news layout' and integrate here.
			{
				foreach($newInfo as $k=>$val)
				{
					$newsTemplates[$k] = $val['title'];
				}

			}
			else
			{
				$newsTemplates = array('default'=>LAN_DEFAULT, 'list'=>LAN_LIST);
			}

			$imageSizes = array(

				'400×300'   => '400x300',
				'640×480'   => '640x480',
				'800×600'   => '800x600',
				'1024×768'  => '1024x768',
				'1600×1200' => '2 MP (1600×1200)',
				'2272×1704' => '4 MP (2272×1704)',
				'2816×2112' => '6 MP (2816×2112)',
				'3264×2448' => '8 MP (3264×2448)',
				// 10 MP (3648×2736)
				// 12 MP (4096×3072)

			);

			$this->prefs['news_default_template']['writeParms']['optArray'] = $newsTemplates;
			$this->prefs['newsposts']['writeParms']['optArray'] = $this->_optrange(50, false);
			$this->prefs['news_list_limit']['writeParms']['optArray'] = $this->_optrange(50, false);
			$this->prefs['news_list_templates']['writeParms'] = ['optArray' => $this->news_renderTypes, 'useKeyValues' => 1];
			$this->prefs['news_pagination']['writeParms'] = ['optArray' => ['record'=> "Record", 'page'=>'Page'], 'value'=>varset($pref['news_pagination'],'record')];
			$this->prefs['nbr_cols']['writeParms']['optArray'] = $this->_optrange(6, false);
            $this->prefs['newsposts_archive']['writeParms']['optArray'] = $this->_optrange(intval($pref['newsposts']) - 1);

			$this->prefs['subnews_attach_minsize']['writeParms']['optArray'] = $imageSizes;


	}

	/*function saveSettings()
	{
		if(!getperms('0'))
		{
			$this->noPermissions();
		}


		$temp = array();
		$temp['newsposts'] 				= intval($_POST['newsposts']);
		$temp['newsposts_archive'] 		= intval($_POST['newsposts_archive']);
		$temp['newsposts_archive_title'] = e107::getParser()->toDB($_POST['newsposts_archive_title']);
		$temp['news_cats'] 				= intval($_POST['news_cats']);
		$temp['nbr_cols'] 				= intval($_POST['nbr_cols']);
		$temp['subnews_attach'] 		= intval($_POST['subnews_attach']);
		$temp['subnews_resize'] 		= intval($_POST['subnews_resize']);
		$temp['subnews_attach_minsize'] = e107::getParser()->filter($_POST['subnews_attach_minsize']);
		$temp['subnews_class'] 			= intval($_POST['subnews_class']);
		$temp['subnews_htmlarea'] 		= intval($_POST['subnews_htmlarea']);
		$temp['news_subheader'] 		= e107::getParser()->toDB($_POST['news_subheader']);
		$temp['news_newdateheader'] 	= intval($_POST['news_newdateheader']);
		$temp['news_unstemplate'] 		= intval($_POST['news_unstemplate']);
		$temp['news_editauthor']		= intval($_POST['news_editauthor']);
		$temp['news_pagination']        = filter_var($_POST['news_pagination']);

		$temp['news_default_template']	= preg_replace('#[^\w\pL\-]#u', '', $_POST['news_default_template']);
		$temp['news_list_limit']		= intval($_POST['news_list_limit']);
		$temp['news_list_templates']     = e107::getParser()->toDB($_POST['news_list_templates']);
		$temp['news_cache_timeout']     = intval($_POST['news_cache_timeout']);

		e107::getConfig()->updatePref($temp);

		if(e107::getConfig()->save(false))
		{
			e107::getLog()->logArrayDiffs($temp, e107::getPref(), 'NEWS_06');
			$this->clearCache();
		}
	}*/



	function submittedPage()
	{
		$this->newspost->show_submitted_news();	
	}
	

	private function _optrange($num, $zero = true)
	{
		$tmp = range(0, $num < 0 ? 0 : $num);
		if(!$zero) unset($tmp[0]);
			return $tmp;
	}
		
/*	function settingsPage()
	{
	//	return $this->newspost->show_news_prefs();

			$pref = e107::getPref();
			$frm = e107::getForm();

			$sefbaseDiz = str_replace(array("[br]","[","]"), array("<br />","<a href='".e_ADMIN_ABS."eurl.php'>","</a>"), NWSLAN_128 );


			$newsTemplates = array();

			if($newInfo = e107::getTemplateInfo('news', 'news', null, 'front', true))  //TODO  'category'=>'Categories'? research 'Use non-standard template for news layout' and integrate here.
			{
				foreach($newInfo as $k=>$val)
				{
					$newsTemplates[$k] = $val['title'];
				}

			}
			else
			{
				$newsTemplates = array('default'=>LAN_DEFAULT, 'list'=>LAN_LIST);
			}

			$text = "
			<form method='post' action='".e_REQUEST_URI."' id='core-newspost-settings-form'>";

			$tab1 = "

					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".NWSLAN_127.$frm->help(LAN_NEWS_88)."</td>
								<td>
									".$frm->select('news_default_template', $newsTemplates, $pref['news_default_template'])."
								</td>
							</tr>

							<tr>
								<td>".NWSLAN_88."</td>
								<td>
									".$frm->select('newsposts', $this->_optrange(50, false), $pref['newsposts'])."
								</td>
							</tr>

							<tr>
								<td>".LAN_NEWS_91.$frm->help(LAN_NEWS_92)."</td>
								<td>
									".$frm->select('news_list_limit', $this->_optrange(50, false), $pref['news_list_limit'])."
								</td>
							</tr>

							<tr>
								<td>".LAN_NEWS_93.$frm->help(LAN_NEWS_94)."</td>
								<td>
									".$frm->checkboxes('news_list_templates', $this->news_renderTypes, varset($pref['news_list_templates'],0), array('useKeyValues' => 1))."
								</td>
							</tr>
							";

						$paginationOpts = ['record'=> "Record", 'page'=>'Page'];

						$tab1 .= "
							<tr>
								<td>".LAN_PAGINATION.$frm->help(LAN_NEWS_112)."</td>
								<td>
									".$frm->select('news_pagination',$paginationOpts, varset($pref['news_pagination'],'record'))."
								</td>
							</tr>";

								
						$tab1 .= "
							<tr>
								<td>".LAN_NEWS_110.$frm->help(LAN_NEWS_111)."</td>
								<td>
									".$frm->number('news_cache_timeout',varset($pref['news_cache_timeout'],0), 6)."
								</td>
							</tr>";


						$tab1 .= "

							<tr>
							<td>".NWSLAN_86."</td>
								<td>
									".$frm->radio_switch('news_cats', $pref['news_cats'])."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_87."</td>
								<td>
									".$frm->select('nbr_cols', $this->_optrange(6, false), $pref['nbr_cols'])."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_115.$frm->help(NWSLAN_116)."</td>
								<td id='newsposts-archive-cont'>
									".$frm->select('newsposts_archive', $this->_optrange(intval($pref['newsposts']) - 1), intval($pref['newsposts_archive']))."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_117."</td>
								<td>
									".$frm->text('newsposts_archive_title', $pref['newsposts_archive_title'])."
								</td>
							</tr>
								<tr>
								<td>".LAN_NEWS_51."</td>
								<td>
									".$frm->uc_select('news_editauthor', vartrue($pref['news_editauthor']), 'nobody,main,admin,classes')."
								</td>
							</tr>
							";




			$imageSizes = array(

				'400×300'   => '400x300',
				'640×480'   => '640x480',
				'800×600'   => '800x600',
				'1024×768'  => '1024x768',
				'1600×1200' => '2 MP (1600×1200)',
				'2272×1704' => '4 MP (2272×1704)',
				'2816×2112' => '6 MP (2816×2112)',
				'3264×2448' => '8 MP (3264×2448)',
				// 10 MP (3648×2736)
				// 12 MP (4096×3072)

			);





			$tab2  = "<table class='table adminform'>
								<colgroup>
									<col class='col-label' />
									<col class='col-control' />
								</colgroup>
								<tbody>

							<tr>
								<td>".NWSLAN_106."</td>
								<td>
									".$frm->uc_select('subnews_class', $pref['subnews_class'], 'nobody,public,guest,member,admin,classes')."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_107."</td>
								<td>
									".$frm->radio_switch('subnews_htmlarea', $pref['subnews_htmlarea'])."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_100."</td>
								<td>
									".$frm->radio_switch('subnews_attach', $pref['subnews_attach'])."
								</td>
							</tr>
								<tr>
								<td>".LAN_NEWS_99."</td>
								<td>
									".$frm->select('subnews_attach_minsize', $imageSizes, varset($pref['subnews_attach_minsize'], null), null, LAN_NEWS_100)."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_101."</td>
								<td>
									".$frm->number('subnews_resize', $pref['subnews_resize'], 5, 'size=6&class=tbox')."
									".$frm->help(NWSLAN_102, 'after')."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_120."</td>
								<td>
									".$frm->bbarea('news_subheader', stripcslashes(vartrue($pref['news_subheader'])), null, 'helpb')."
								</td>
							</tr>
							</tbody>
						</table>
					";

			$tab1 .= "
							<tr>
								<td>".NWSLAN_111.$frm->help(NWSLAN_112)."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->radio_switch('news_newdateheader', $pref['news_newdateheader'])."
									</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_113."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->radio_switch('news_unstemplate', vartrue($pref['news_unstemplate']))."
										".$frm->help(NWSLAN_114, 'after')."
									</div>
								</td>
							</tr>

						</tbody>
					</table>";

			$text .= $frm->tabs(array(
				'general'	=> array('caption'=>LAN_GENERAL, 'text'=>$tab1),
				'subnews'	=> array('caption'=>LAN_NEWS_101, 'text'=>$tab2)
			));


			$text .= "

					<div class='buttons-bar center'>
						".$frm->admin_button('save_prefs', LAN_UPDATE, 'update')."
					</div>
				</fieldset>
			</form>
		";
			return e107::getMessage()->render().$text;
			// e107::getRender()->tablerender(NWSLAN_90, e107::getMessage()->render().$text);

	}*/



	private function processSubmittedMedia($data)
	{
		if(empty($data))
		{
			return false;
		}

		$row = json_decode($data,true);
		$text = '';
		foreach($row as $k)
		{
			if(!empty($k))
			{
				$text .= $k."\n\n";
			}
		}

		return $text;

	}


	function loadSubmitted($id)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		if ($sql->select("submitnews", "*", "submitnews_id=".intval($id)))
		{
			$row = $sql->fetch();
			$data['news_title'] = $tp->filter($row['submitnews_title']);
			$data['news_body'] = $row['submitnews_item'];
			$data['news_category'] = intval( $row['submitnews_category']);
			$data['news_body'] .= "\n[[b]".NWSLAN_49." {$row['submitnews_name']}[/b]]";

			if($mediaData = $this->processSubmittedMedia($row['submitnews_media']))
			{
				$data['news_body'] .= "\n\n---\n\n".$mediaData;
			}

			if(e107::getPref('wysiwyg',false)!==false)
			{
				$data['news_body'] = nl2br($data['news_body']);
			}

			$data['news_author'] = $row['submitnews_user'];

			$data['news_thumbnail'] = $row['submitnews_file']; // implode(",",$thumbs);
			$data['news_sef']    = eHelper::dasherize($data['news_title']);

			$data['news_meta_keywords'] = $row['submitnews_keywords'];
			$data['news_summary'] = $row['submitnews_summary'];
			$data['news_meta_description'] = $row['submitnews_description'];

			$data['submitted_id']   = $id;

			foreach($data as $k=>$v)
			{
				$this->getModel()->setData($k, $v); // Override Table data.
			}

			if(e_DEBUG)
			{
				e107::getMessage()->addDebug(print_a($data,true));
			}
		}
			


	}
	
	
	
	
	
	function preCreate()
	{
		if($_GET['action'] == "edit" && !$_POST['preview'])
		{		
			if(!isset($_POST['submit_news']))
			{
				if(e107::getDb()->select('news', '*', 'news_id='.intval($_GET['id'])))
				{
					$row = e107::getDb()->fetch();

				//	if(!isset($this->news_categories[$row['news_category']]))
					{
				//		$this->noPermissions();
					}

					$_POST['news_title'] = $row['news_title'];
					$_POST['news_sef'] = $row['news_sef'];
					$_POST['news_body'] = $row['news_body'];
					$_POST['news_author'] = $row['news_author'];
					$_POST['news_extended'] = $row['news_extended'];
					$_POST['news_allow_comments'] = $row['news_allow_comments'];
					$_POST['news_class'] = $row['news_class'];
					$_POST['news_summary'] = $row['news_summary'];
					$_POST['news_sticky'] = $row['news_sticky'];
					$_POST['news_datestamp'] = ($_POST['news_datestamp']) ? $_POST['news_datestamp'] : $row['news_datestamp'];

					$_POST['cat_id'] = $row['news_category'];
					$_POST['news_start'] = $row['news_start'];
					$_POST['news_end'] = $row['news_end'];
					$_POST['comment_total'] = e107::getDb()->count("comments", "(*)", " WHERE comment_item_id={$row['news_id']} AND comment_type='0'");
					$_POST['news_render_type'] = $row['news_render_type'];
					$_POST['news_thumbnail'] = $row['news_thumbnail'];
					$_POST['news_meta_keywords'] = $row['news_meta_keywords'];
					$_POST['news_meta_description'] = $row['news_meta_description'];
				}
			}
			else // on submit
			{
				if(!empty($_POST['news_meta_keywords'])) $_POST['news_meta_keywords'] = eHelper::formatMetaKeys($_POST['news_meta_keywords']);
			}
			
		}	
	}
}


class news_form_ui extends e_admin_form_ui
{

	function news_template($curVal,$mode)
	{
		if($mode === 'read')
		{
			return $curVal;
		}


		if($mode === 'write')
		{

			if($tmp = e107::getTemplate('news', 'news', 'view'))
			{
				return LAN_DEFAULT;
			}

			if($tmp = e107::getLayouts('news', 'news_view', 'front', null, false, false))
			{
				return $this->select('news_template', $tmp, $curVal, array('size'=>'xlarge'));
			}


			return LAN_DEFAULT;
		}

	}


	function news_author($curVal, $mode)
	{




		$pref = e107::pref();
		$sql = e107::getDb();


		if($mode == 'read')
		{
			$row = $this->getController()->getListModel()->getData();
			// $att = $this->getController()->getFieldAttr('news_author');
		//	$att = array('readParms'=> array(['__idval']=>$row['user_id'idField=user_id&nameField=user_name');
			return $row['user_name'];
		}



		$text = "";

		if(!getperms('0') && !check_class($pref['news_editauthor']))
		{

			$auth = ($curVal) ? intval($curVal) : USERID;
			$sql->select("user", "user_name", "user_id=$auth LIMIT 1");
			$row = $sql->fetch();
			$text .= "<input type='hidden' name='news_author' value='".$auth.chr(35).$row['user_name']."' />";
			$text .= "<a target='_blank' href='".e107::getUrl()->create('user/profile/view', 'name='.$row['user_name'].'&id='.$auth)."'>".$row['user_name']."</a>";
		}
		else // allow master admin to
		{
			$text .= $this->select_open('news_author', array('size'=>'xlarge'));
			$qry = "SELECT user_id,user_name,user_admin FROM #user WHERE user_perms = '0' OR user_perms = '0.' OR user_perms REGEXP('(^|,)(H)(,|$)') ";

			if(!empty($curVal))
			{
				$qry .= " OR user_id = ".intval($curVal); // make sure existing author is included.
			}

			if($pref['subnews_class'] && $pref['subnews_class']!= e_UC_GUEST && $pref['subnews_class']!= e_UC_NOBODY)
			{
				if($pref['subnews_class']== e_UC_MEMBER)
				{
					$qry .= " OR user_ban != 1 ORDER BY user_class DESC, user_name";// limit to avoid long page loads.
				}
				elseif($pref['subnews_class']== e_UC_ADMIN)
				{
					$qry .= " OR user_admin = 1 ORDER BY user_name";
				}
				else
				{
					$qry .= " OR FIND_IN_SET(".intval($pref['subnews_class']).", user_class) ORDER BY user_name";
				}
			}

	//		print_a($pref['subnews_class']);


			$sql->gen($qry);
			while($row = $sql->fetch())
			{
				if(vartrue($curVal))
				{
					$sel = ($curVal == $row['user_id']);
				}
				else
				{
					$sel = (USERID == $row['user_id']);
				}

				$username = $row['user_name'];

				if(!empty($row['user_admin']))
				{
					$username .= " *";
				}


				$text .= $this->option($username, $row['user_id'].chr(35).$row['user_name'], $sel);
			}

			$text .= "</select>
			";


		}

		return $text;

	}







	function news_body($curVal,$mode)
	{
		$frm = e107::getForm();
		$tp = e107::getParser();

		if($mode == 'read')
		{
			return '...';
		}


		$curValExt = $this->getController()->getModel()->get('news_extended');


		$text = '<ul class="nav nav-tabs">
		    <li class="active"><a href="#news-body-container" data-toggle="tab" data-bs-toggle="tab">'.NWSLAN_13.'</a></li>
		    <li><a href="#news-extended-container" data-toggle="tab" data-bs-toggle="tab">'.NWSLAN_14.'</a></li>
		  </ul>
		  <div class="tab-content">';


		$val = strpos($curVal, "[img]http") !== false ? $curVal : str_replace("[img]../", "[img]", $curVal);
		$text .= "<div id='news-body-container' class='tab-pane active'>";
		$text .= $frm->bbarea('news_body', $val, 'news', 'news');
		$text .= "</div>";
		$text .= "<div id='news-extended-container' class='tab-pane'>";

		$val = (strpos($curValExt, "[img]http") !== false ? $curValExt : str_replace("[img]../", "[img]",$curValExt));
		$text .= $frm->bbarea('news_extended', $val, 'extended', 'news');

		$text .= "</div>
			</div>";

		return $text;

	}





	function news_thumbnail($curval,$mode)
	{

		if($mode == 'read')
		{
			if(strpos($curval, ",")!==false)
			{
				$tmp = explode(",",$curval);
				$curval = $tmp[0];
			}

			if(empty($curval))
			{
				return '';
			}

			$vparm = array('thumb'=>'tag','w'=> 80);

			if($thumb = e107::getParser()->toVideo($curval,$vparm))
			{
				return $thumb;
			}

			if($curval[0] != "{")
			{
				$curval = "{e_IMAGE}newspost_images/".$curval;
			}

			$url = e107::getParser()->thumbUrl($curval,'aw=80');
			$link = e107::getParser()->replaceConstants($curval);

			return "<a class='e-modal' href='$link'><img src='$url' alt='".basename($curval)."' /></a>";
		}


		if($mode == 'write')
		{
			$paths = array();

			if(!empty($_GET['sub']))
			{
				$thumbTmp = explode(",",$curval);
				foreach($thumbTmp as $key=>$path)
				{
					$url = ($path[0] == '{') ? $path : e_TEMP.$path;
					$paths[] = e107::getParser()->thumbUrl($url,'aw=800');
				}

			}


			$tp = e107::getParser();
			$frm = e107::getForm();

			//	$text .= $frm->imagepicker('news_thumbnail[0]', $curval ,'','media=news&video=1');
			$thumbTmp = explode(",",$curval);


			$text = "<div class='mediaselector-multi'>";
			$text .= $frm->imagepicker('news_thumbnail[0]', varset($thumbTmp[0]), varset($paths[0]), array('media' => 'news+', 'video' => 1, 'legacyPath' => '{e_IMAGE}newspost_images'));
			$text .= $frm->imagepicker('news_thumbnail[1]', varset($thumbTmp[1]), varset($paths[1]), array('media' => 'news+', 'video' => 1, 'legacyPath' => '{e_IMAGE}newspost_images'));
			$text .= $frm->imagepicker('news_thumbnail[2]', varset($thumbTmp[2]), varset($paths[2]), array('media' => 'news+', 'video' => 1, 'legacyPath' => '{e_IMAGE}newspost_images'));
			$text .= $frm->imagepicker('news_thumbnail[3]', varset($thumbTmp[3]), varset($paths[3]), array('media' => 'news+', 'video' => 1, 'legacyPath' => '{e_IMAGE}newspost_images'));
			$text .= $frm->imagepicker('news_thumbnail[4]', varset($thumbTmp[4]), varset($paths[4]), array('media' => 'news+', 'video' => 1, 'legacyPath' => '{e_IMAGE}newspost_images'));
			$text .= "</div>";
		//	$text .= "<div class='field-help'>Insert image/video into designated area of template.</div>";
			return $text;
		}



	}




	function news_title($value, $mode)
	{
		if($mode == 'read')
		{
			$news_item = $this->getController()->getListModel()->toArray();
			$url = e107::getUrl()->create('news/view/item', $news_item);
			return "<a class='e-tip' href='$url' title='".LAN_NEWS_102."' rel='external'>".$value."</a>";
		}
		return $value;
	}
}



new news_admin();
require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();



if(!e_AJAX_REQUEST)
{
	 require_once("footer.php");
}


