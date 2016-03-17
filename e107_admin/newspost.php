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

require_once('../class2.php');

if (!getperms('H|N|H0|H1|H2|H3|H4|H5'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('newspost', true);






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
		'main/settings' => '0',
		'sub/list'      => 'N'
	);



	protected $adminMenu = array(
		'main/list'			=> array('caption'=> LAN_LIST, 'perm' => 'H'),
		'main/create' 		=> array('caption'=> NWSLAN_45, 'perm' => 'H'),  // Create/Edit News Item
	//	'cat/list' 			=> array('caption'=> NWSLAN_46, 'perm' => '7'), // Category List
		'cat/list' 			=> array('caption'=> LAN_CATEGORIES, 'perm' => 'H'), // Create Category. 
		'cat/create' 		=> array('caption'=> LAN_NEWS_63, 'perm' => 'H'), // Category List
		'main/settings' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'), // Preferences
	//	'main/submitted'	=> array('caption'=> LAN_NEWS_64, 'perm' => 'N'), // Submitted News
		'sub/list'			=> array('caption'=> NWSLAN_47, 'perm' => 'N'), // Submitted News
	//	'main/maint'		=> array('caption'=> LAN_NEWS_55, 'perm' => '0') // Maintenance
	);



	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list',
		'cat/edit'	=> 'cat/list'
	);

	protected $menuTitle = "News";

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
		protected $sortField = 'category_order';
		protected $listOrder	= "category_order ASC";
		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'category_id'				=> array('title'=> LAN_ID,				'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'category_icon' 			=> array('title'=> LAN_ICON,			'type' => 'icon', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60', 'writeParms' => 'glyphs=1', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			       	
         	'category_name' 			=> array('title'=> LAN_TITLE,			'type' => 'text',			'inline'=>true, 'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'validate' => true, 'writeParms'=>array('size'=>'xxlarge')),
         
         	'category_meta_description' => array('title'=> LAN_DESCRIPTION,		'type' => 'textarea',		'inline'=>true, 'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>FALSE, 'writeParms'=>array('size'=>'xxlarge')),
			'category_meta_keywords' 	=> array('title'=> LAN_KEYWORDS,		'type' => 'tags',			'inline'=>true, 'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),		
			'category_sef' 				=> array('title'=> LAN_SEFURL,	'type' => 'text',	'inline'=>true,	'width' => 'auto', 'readonly'=>FALSE, 'writeParms'=>array('size'=>'xxlarge', 'sef'=>'category_name')), // Display name
			'category_manager' 			=> array('title'=> LAN_MANAGER,'type' => 'userclass',		'inline'=>true, 'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'category_order' 			=> array('title'=> LAN_ORDER,			'type' => 'text',			'width' => 'auto', 'thclass' => 'right', 'class'=> 'right' ),										
			'options' 					=> array('title'=> LAN_OPTIONS,			'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center', 'sort' => true)
		);

		protected $fieldpref = array('checkboxes', 'category_icon', 'category_id', 'category_name', 'category_description', 'category_sef', 'category_manager', 'category_order', 'options');
		
	//	protected $newspost;
	
		function init()
		{
			// $this->newspost = new admin_newspost;
		}
		
	//	function createPage()
	//	{
		//	$this->newspost->show_categories();
	//	}
		
		public function beforeCreate($new_data)
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
			
			if(e107::getDb()->count('news_category', '(*)', "category_sef='{$sef}'"))
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
			if(empty($new_data['category_sef']))
			{
				$new_data['category_sef'] = eHelper::title2sef($new_data['category_name']);
			}
			$sef = e107::getParser()->toDB($new_data['category_sef']);
			if(e107::getDb()->count('news_category', '(*)', "category_sef='{$sef}' AND category_id!=".intval($id)))
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
			'submitnews_datestamp'		=> array('title' => LAN_NEWS_32, 		'type' => 'datestamp', 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),

			'submitnews_title' 			=> array('title'=> LAN_TITLE,			'type' => 'method',			'width' => '35%', 'thclass' => 'left', 'readonly'=>TRUE),

			'submitnews_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),		
		//	'submitnews_item' 			=> array('title'=> LAN_DESCRIPTION,		'type' => 'method',			'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>TRUE),
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
		
		public function beforeCreate($new_data)
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
		
		
		$text   = "<a data-toggle='modal' href='#submitted_".$submitnews_id."' data-cache='false' data-target='#submitted_".$submitnews_id."' class='e-tip' title='".LAN_PREVIEW."'>";
		$text .= $tp->toHTML($submitnews_title,FALSE,'emotes_off, no_make_clickable');	
		$text .= '</a>';
		
		$text .= '

		 <div id="submitted_'.$submitnews_id.'" class="modal fade" tabindex="-1" role="dialog"  aria-hidden="true">
		 <div class="modal-dialog modal-lg" >
             <div class="modal-content">
			    <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			   <h4>'.$tp->toHtml($submitnews_title,false,'TITLE').'</h4>
			    </div>
			    <div class="modal-body">
			    <p>';
		
		$text .= $tp->toHTML($submitnews_item,TRUE);
				
		if($submitnews_file)
		{
			$tmp = explode(',',$submitnews_file);
			
			$text .= "<br />";
			
			
			foreach($tmp as $imgfile)
			{				
				$url = $tp->thumbUrl(e_UPLOAD.$imgfile,array('aw'=>400),true);
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


			$text   = "<a class='btn btn-default  btn-large' data-toggle='modal' href='#submitted_".$submitnews_id."' data-cache='false' data-target='#submitted_".$submitnews_id."'  title='".LAN_PREVIEW."'>".ADMIN_VIEW_ICON."</a>";




			if($approved == 0)
			{
				//$text = $this->submit_image('submitnews['.$id.']', 1, 'execute', NWSLAN_58);
				$text .= "<a class='btn btn-default btn-large' href='".e_SELF."?mode=main&action=create&sub={$id}'>".ADMIN_EXECUTE_ICON."</a>";
				// NWSLAN_103;	
			} 
			else // Already submitted; 
			{
				
			}
					
			$text .= $this->submit_image('etrigger_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'btn btn-default btn-large action delete'));
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
	protected $batchCopy 	= true;
    protected $batchLink    = true;
	protected $listQry      = "SELECT n.*,u.user_id,u.user_name FROM `#news` AS n LEFT JOIN `#user` AS u ON n.news_author = u.user_id "; // without any Order or Limit.

	protected $listOrder	= "news_id desc";
	// true for 'vars' value means use same var

	protected $tabs         = array(LAN_NEWS_52, 'SEO', LAN_NEWS_53);


    protected $url          = array(
    	'route'=>'news/view/item', 
    	'name' => 'news_title', 
    	'description' => 'news_summary', 
    	'vars'=> array('news_id' => true, 'news_sef' => true, 'category_id' => 'news_category', 'category_sef' => true) // FIXME category_sef missing, we have to retrieve category data on the list view
	); // 'link' only needed if profile not provided. 
    
		

		
	protected $fields = array(
		'checkboxes'	   		=> array('title' => '', 			'type' => null, 		'width' => '3%', 	'thclass' => 'center first', 	'class' => 'center', 	'nosort' => true, 'toggle' => 'news_selected', 'forced' => TRUE),
		'news_id'				=> array('title' => LAN_ID, 	    'type' => 'text', 	    'width' => '5%', 	'thclass' => 'center', 			'class' => 'center',  	'nosort' => false, 'readParms'=>'link=sef&target=blank'),
 		'news_thumbnail'		=> array('title' => NWSLAN_67, 		'type' => 'method', 	'width' => '110px',	'thclass' => 'center', 			'class' => "center", 		'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>false),
 		'news_title'			=> array('title' => LAN_TITLE, 		'type' => 'text',       'tab'=>0, 'writeParms'=> array('required'=> 1, 'size'=>'block-level'), 'inline'=>true,		'width' => 'auto', 'thclass' => '', 				'class' => null, 		'nosort' => false),
		'news_summary'			=> array('title' => LAN_SUMMARY, 	'type' => 'text', 	    'tab'=>0, 'writeParms'=>'size=block-level',	'width' => 'auto', 	'thclass' => 'left', 				'class' => 'left', 		'nosort' => false),
		'news_body'			    => array('title' => "", 	        'type' => 'method',     'tab'=>0,  'nolist'=>true, 'writeParms'=>'nolabel=1','data'=>'str',		'width' => 'auto', 	'thclass' => '',  'class' => null, 		'nosort' => false),
		'news_extended'			=> array('title' => "", 	        'type' => null,     'tab'=>0,  'nolist'=>true, 'writeParms'=>'nolabel=1','data'=>'str',		'width' => 'auto', 	'thclass' => '',  'class' => null, 		'nosort' => false),

		'news_meta_keywords'	=> array('title' => LAN_KEYWORDS, 	'type' => 'tags', 	    'tab'=>1,	'inline'=>true, 'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
		'news_meta_description'	=> array('title' => LAN_DESCRIPTION,'type' => 'textarea', 	'tab'=>1,	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'writeParms'=>array('size'=>'xxlarge')),
		'news_sef'				=> array('title' => LAN_SEFURL, 	'type' => 'text',       'tab'=>1,  'inline'=>true, 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'writeParms'=>array('size'=>'xxlarge', 'show'=>1, 'sef'=>'news_title')),
		'news_ping'				=> array('title' => LAN_PING, 	    'type' => 'checkbox',   'tab'=>1, 'data'=>false, 'writeParms'=>'value=0',	'inline'=>true, 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),

		'news_author'			=> array('title' => LAN_AUTHOR, 	'type' => 'method', 	'tab'=>0, 	'readParms'=>'idField=user_id&nameField=user_name', 'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
		'news_datestamp'		=> array('title' => LAN_NEWS_32, 	'type' => 'datestamp',  'tab'=>2,   'writeParms'=>'type=datetime', 'data' => 'int',   'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y', 'filter'=>true),
        'news_category'			=> array('title' => NWSLAN_6, 		'type' => 'dropdown',   'tab'=>0,	'data' => 'int', 'inline'=>true,	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'batch'=>true, 'filter'=>true),
		'news_start'			=> array('title' => LAN_START, 	    'type' => 'datestamp',  'tab'=>2,   'writeParms'=>'type=datetime',	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),
       	'news_end'				=> array('title' => LAN_END, 		'type' => 'datestamp',  'tab'=>2,  'writeParms'=>'type=datetime',	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),
        'news_class'			=> array('title' => LAN_VISIBILITY, 'type' => 'userclass',  'tab'=>2,   'inline'=>true, 'width' => 'auto', 	'thclass' => '', 				'class' => null,  'batch'=>true, 'filter'=>true),
		'news_render_type'		=> array('title' => LAN_TEMPLATE, 	'type' => 'dropdown',   'tab'=>0,   'data'=> 'str',		'inline'=>false, 'width' => 'auto', 	'thclass' => 'left', 			'class' => 'left', 		'nosort' => false, 'batch'=>true, 'filter'=>true),
		'news_sticky'			=> array('title' => LAN_NEWS_28, 	'type' => 'boolean',    'tab'=>2,	'data' => 'int' , 'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false, 'batch'=>true, 'filter'=>true),
        'news_allow_comments' 	=> array('title' => LAN_COMMENTS, 		'type' => 'boolean',    'tab'=>2,	'writeParms'=>'inverse=1', 'data' => 'int', 'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false,'batch'=>true, 'filter'=>true,'readParms'=>'reverse=1'),
        'news_comment_total' 	=> array('title' => LAN_NEWS_60, 	'type' => 'number',     'tab'=>2,	'noedit'=>true, 'width' => '10%', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
	//	admin_news_notify
		'news_email_notify'     => array('title' => "Email notification", 'type' => 'checkbox',   'tab'=>2,  'data'=>false, 'writeParms'=>array('show'=>1, 'tdClassRight'=>'form-inline'), 'help'=>'Trigger an email notification when you submit this form.'),
		'submitted_id'          => array('title' => LAN_NEWS_68, 'type' => 'hidden',  'tab'=>2,  'data'=>false, 'writeParms'=>'show=0'),
		'options'				=> array('title' => LAN_OPTIONS, 	'type' => null, 		'width' => '10%', 	'thclass' => 'center last', 	'class' => 'center', 	'nosort' => true, 'forced' => TRUE)

	);
	
	protected $fieldpref = array('checkboxes','news_id', 'news_thumbnail', 'news_title', 'news_datestamp', 'news_category', 'news_class', 'options');

	/* //TODO
	protected $prefs = array(

		'news_category'			=> array('title' => NWSLAN_127, 		'type' => 'dropdown', 'help'=> "Determines how the default news page should appear."),
		'news_ping_services'			=> array('title' => "Ping Services", 	'type' => 'textarea', 'data'=> 'help'=> ">Notify these services when you create/update news items. <br />One per line."),



	);
	*/
		
	protected $cats = array();
	protected $newspost;
	
	protected $news_renderTypes = array( // TODO Placement location and template should be separate. 
	
		'0' =>	LAN_NEWS_69,
		'1' =>	LAN_NEWS_70,
		'4' =>	LAN_NEWS_71,
		'2' =>	LAN_NEWS_72,
		'3' =>	LAN_NEWS_73,
		'5' =>	LAN_NEWS_74,
		//'5' =>  LAN_NEWS_75
	);

	public function beforeCreate($new_data)
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



		$new_data['news_sef'] =  empty($new_data['news_sef']) ?  eHelper::title2sef($new_data['news_title']) : eHelper::secureSef($new_data['news_sef']);

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
			$tmp = urldecode($v);
			if(strpos($tmp,'{e_UPLOAD}')!==false)
			{
				list($root,$qry) = explode("?",$tmp);
				parse_str($qry,$opt);
				if(!empty($opt['src']))
				{

					$f = str_replace('{e_UPLOAD}','',$opt['src']);
				//	e107::getMessage()->addInfo("<h3>Importing File</h3>".print_a($f,true));
					if($bbpath = e107::getMedia()->importFile($f,'news', e_UPLOAD.$f))
					{
						$new[] = $bbpath;
					}
				}

			}
			elseif(!empty($v))
			{
				$new[] = $v;
			}
		}

	//		e107::getMessage()->addInfo("<h3>Process SubNews Images</h3>".print_a($new,true));

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


		$this->processPings();
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

		$this->processPings();

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
			e107::getMessage()->addSuccess("Email notification triggered");
		}
		else
		{
			e107::getMessage()->addWarning("News item visibility must include 'everyone' for email notifications to work.");
		}


	}



	public function afterDelete()
	{
		$this->clearCache();
	}

	function clearCache()
	{
		$ecache = e107::getCache();
		$ecache->clear("news.php"); //TODO change it to 'news_*' everywhere
		$ecache->clear("news_", false, true); //NEW global news cache prefix
		$ecache->clear("othernews"); //TODO change it to 'news_othernews' everywhere
		$ecache->clear("othernews2"); //TODO change it to 'news_othernews2' everywhere

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
			return implode(",",array_filter($postedImage));
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
		'news_render_type',
		'news_author' ,
		'news_body',
		'news_extended',
		'news_thumbnail',

		'news_sef' ,
		'news_meta_keywords',
		'news_meta_description' ,
		'news_ping',

		'news_email_notify',
		'news_allow_comments' ,
		'news_start' ,
		'news_end' ,
		'news_datestamp' ,
		'news_class',
		'news_sticky',

		'news_comment_total' ,
		'submitted_id',
		'options' );


		$addons = e107::getAddonConfig('e_admin',null, 'config',$this);
		foreach($addons as $plug=>$config)
		{
			foreach($config['fields'] as $field=>$tmp)
			{
				$newOrder[] = "x_".$plug."_".$field;
			//	echo $field;
			}
		}



		$order = array_flip($newOrder);

		if($order[$a] == $order[$b])
		{
			return 0;
		}

		return ($order[$a] < $order[$b]) ? -1 : 1;

	}


	function init()
	{
		if(!empty($_POST['save_prefs']))
		{
			$this->saveSettings();
		}

		if(e_DEBUG == true) // allowing manual fixing of comment total in DEBUG mode.
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
	//	$this->newspost = new admin_newspost;
	//	$this->newspost->news_renderTypes = $this->news_renderTypes;
	//	$this->newspost->observer();
 
	}


	function saveSettings()
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
		$temp['news_ping_services']		= explode("\n",$_POST['news_ping_services']);
		$temp['news_default_template']	= preg_replace('#[^\w\pL\-]#u', '', $_POST['news_default_template']);
		$temp['news_list_limit']		= intval($_POST['news_list_limit']);
		$temp['news_list_templates']     = e107::getParser()->toDB($_POST['news_list_templates']);

		e107::getConfig()->updatePref($temp);

		if(e107::getConfig()->save(false))
		{
			e107::getAdminLog()->logArrayDiffs($temp, e107::getPref(), 'NEWS_06');
			$this->clearCache();
		}
	}




	function processPings()
	{

		// Ping Changes to Services.
		$pingServices = e107::getPref('news_ping_services');
		//TODO Use Ajax with progress-bar.

		$mes = e107::getMessage();

		$mes->addDebug('Checking for Ping Status','default',true);

		if(!empty($_POST['news_ping']) && (count($pingServices)>0) && (in_array(e_UC_PUBLIC, $_POST['news_class'])))
		{
			$mes->addDebug("Initiating ping",'default',true);

			include (e_HANDLER.'xmlrpc/xmlrpc.inc.php');
			include (e_HANDLER.'xmlrpc/xmlrpcs.inc.php');
			include (e_HANDLER.'xmlrpc/xmlrpc_wrappers.inc.php');

			$extendedServices = array('blogsearch.google.com');

			$port = 80;

			foreach($pingServices as $fullUrl)
			{
				$fullUrl = str_replace("http://","", trim($fullUrl));
				list($server,$path) = explode("/",$fullUrl, 2);

				$path 			= "/".$path;

				$weblog_name	= SITENAME;
				$weblog_url		= $_SERVER['HTTP_HOST'].e_HTTP;
				$changes_url	= $_SERVER['HTTP_HOST'].e107::getUrl()->create('news/view/item', $_POST); //  $_SERVER['HTTP_HOST'].e_HTTP."news.php?extend.".$_POST['news_id'];
				$cat_or_rss		= $_SERVER['HTTP_HOST'].e_PLUGIN_ABS."rss_menu/rss.php?1.2";
				$extended		= (in_array($server, $extendedServices)) ? true : false;

				if($this->ping($server, $port, $path, $weblog_name, $weblog_url, $changes_url, $cat_or_rss, $extended))
				{
					e107::getMessage()->addInfo("Successfully Pinged: ".$server .' with:<br />url: '.$changes_url .'<br />rss: '.$cat_or_rss , 'default', true);
				}
				else
				{
					e107::getMessage()->addDebug("Ping failed!: ".$server .' with: '.$changes_url , 'default', true);
				}

			}

		}
		else
		{
		//	$mes->addDebug('Ping not triggerred','default',true);
		//	$mes->addDebug("Services: ".print_a($pingServices, true),'default', true);
		//	$mes->addDebug("Userclass: ".print_a($_POST['news_class'],true),'default', true);

		}

	}


	   /* Multi-purpose ping for any XML-RPC server that supports the Weblogs.Com interface. */
    function ping($xml_rpc_server, $xml_rpc_port, $xml_rpc_path, $weblog_name, $weblog_url, $changes_url, $cat_or_rss='', $extended = false)
	{
		$mes = e107::getMessage();
		$log = e107::getAdminLog();
		
		$mes->addDebug("Attempting to ping: ".$xml_rpc_server, 'default', true);

		
        $name_param 		= new xmlrpcval($weblog_name, 'string');
        $url_param 			= new xmlrpcval($weblog_url, 'string');
        $changes_param 		= new xmlrpcval($changes_url, 'string');
        $cat_or_rss_param 	= new xmlrpcval($cat_or_rss, 'string');
        $method_name 		= ($extended) ? "weblogUpdates.extendedPing" : "weblogUpdates.ping";
		
        if ($cat_or_rss != "") 
        {
            $params = array($name_param, $url_param, $changes_param, $cat_or_rss_param);
			$call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\", \"$cat_or_rss\")";
		} 
        else 
        {
            if ($changes_url != "") 
            {
              	$params = array($name_param, $url_param, $changes_param);
				$call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\")";
			}
			 else 
			 {
				$params = array($name_param, $url_param);
				$call_text = "$method_name(\"$weblog_name\", \"$weblog_url\")";
			}
        }

        // create the message
        $message 	= new xmlrpcmsg($method_name, $params);
        $client 	= new xmlrpc_client($xml_rpc_path, $xml_rpc_server, $xml_rpc_port);
        $response 	= $client->send($message);
       
        $this->log_ping("Request: " . $call_text);
        $this->log_ping($message->serialize(), true);
		
        if ($response == 0) 
        {
            $error_text = "Error: " . $xml_rpc_server . ": " . $client->errno . " " . $client->errstring;
            $this->report_error($error_text);
            $this->log_ping($error_text);
			$log->addArray(array('status'=>LAN_ERROR, 'service'=>$xml_rpc_server, 'url'=> $changes_url, 'response'=>$client->errstring))->save('PING_01');
	
            return false;
        }
		
        if ($response->faultCode() != 0)  
        {
            $error_text = "Error: " . $xml_rpc_server . ": " . $response->faultCode() . " " . $response->faultString();
            $this->report_error($error_text);
			$log->addArray(array('status'=>LAN_ERROR, 'service'=>$xml_rpc_server, 'url'=> $changes_url, 'response'=>$response->faultString()))->save('PING_01');
	
            return false;
        }
		
        $response_value = $response->value();
        if ($this->debug)
		{
			 $this->report_error($response_value->serialize());
		}
		
        $this->log_ping($response_value->serialize(), true);
		
		$fl_error 	= $response_value->structmem('flerror');
		$message 	= $response_value->structmem('message');

        // read the response
        if ($fl_error->scalarval() != false) 
        {
            $error_text = "Error: " . $xml_rpc_server . ": " . $message->scalarval();
			$this->report_error($error_text);
			$log->addArray(array('status'=>LAN_ERROR, 'service'=>$xml_rpc_server, 'url'=> $changes_url, 'response'=>$message->scalarval()))->save('PING_01');
	
		//	$this->log_ping($error_text);
			return false;
		}

		$log->addArray(array('status'=>LAN_OK, 'service'=>$xml_rpc_server, 'url'=> $changes_url, 'response'=>$message->scalarval()))->save('PING_01');
		
        return true;
	}



    // save ping data to a log file
    function log_ping($message, $xml_data = false) 
    {
       	$message = $xml_data." ".$message;
		file_put_contents(e_LOG."news_ping.log", $message, FILE_APPEND);
    }

	  // sDisplay Ping errors. 
	function report_error($message)
	{
		e107::getMessage()->addError($message, 'default', true);	
	}



	/*
		function createPage()
		{

			// print_a($_POST);
			if(isset($_GET['sub']))
			{
				$id = intval($_GET['sub']);

				$this->loadSubmitted($id);
			}
			else
			{
				$this->preCreate();
			}

			$this->newspost->show_create_item();
		}

			function categoryPage()
			{
				if(!getperms('0|7'))
				{
					$this->noPermissions();
				}
				$this->newspost->show_categories();
				// $newspost->show_create_item();
			}
			*/
	function submittedPage()
	{
		$this->newspost->show_submitted_news();	
	}
	
	function maintPage()
	{
		
	}

	private function _optrange($num, $zero = true)
	{
		$tmp = range(0, $num < 0 ? 0 : $num);
		if(!$zero) unset($tmp[0]);
			return $tmp;
	}
		
	function settingsPage()
	{
	//	return $this->newspost->show_news_prefs();

			$pref = e107::getPref();
			$frm = e107::getForm();

			$sefbaseDiz = str_replace(array("[br]","[","]"), array("<br />","<a href='".e_ADMIN_ABS."eurl.php'>","</a>"), NWSLAN_128 );
			$pingOpt = array('placeholder'=>LAN_NEWS_87);
			$pingVal = (!empty($pref['news_ping_services'])) ? implode("\n",$pref['news_ping_services']) : '';
			$newsTemplates = array('default'=>'Default', 'list'=>'List'); //TODO  'category'=>'Categories'? research 'Use non-standard template for news layout' and integrate here.



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
								<td>".NWSLAN_127."</td>
								<td>
									".$frm->select('news_default_template', $newsTemplates, $pref['news_default_template'])."
									<div class='field-help'>".LAN_NEWS_88."</div>
								</td>
							</tr>

							<tr>
								<td>".NWSLAN_88."</td>
								<td>
									".$frm->select('newsposts', $this->_optrange(50, false), $pref['newsposts'], 'class=tbox')."
								</td>
							</tr>

							<tr>
								<td>".LAN_NEWS_91."</td>
								<td>
									".$frm->select('news_list_limit', $this->_optrange(50, false), $pref['news_list_limit'], 'class=tbox')."
									<div class='field-help'>".LAN_NEWS_92."</div>
								</td>
							</tr>

							<tr>
								<td>".LAN_NEWS_93."</td>
								<td>
									".$frm->checkboxes('news_list_templates', $this->news_renderTypes, varset($pref['news_list_templates'],0), array('useKeyValues' => 1))."
									<div class='field-help'>".LAN_NEWS_94."</div>
								</td>
							</tr>
							<tr>
								<td>Ping Services</td>
								<td>
									".$frm->textarea('news_ping_services', $pingVal, 4, 100, $pingOpt)."
									<div class='field-help'>".LAN_NEWS_89."<br />".LAN_NEWS_90."</div>
								</td>
							</tr>

							<tr>
							<td>".NWSLAN_86."</td>
								<td>
									".$frm->radio_switch('news_cats', $pref['news_cats'])."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_87."</td>
								<td>
									".$frm->select('nbr_cols', $this->_optrange(6, false), $pref['nbr_cols'], 'class=tbox')."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_115."</td>
								<td id='newsposts-archive-cont'>
									".$frm->select('newsposts_archive', $this->_optrange(intval($pref['newsposts']) - 1), intval($pref['newsposts_archive']), 'class=tbox')."
									<div class='field-help'>".NWSLAN_116."</div>
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
				'1600x1200' => '2 MP (1600×1200)',
				'2272x1704' => '4 MP (2272×1704)',
				'2816x2112' => '6 MP (2816×2112)',
				'3264x2448' => '8 MP (3264×2448)',
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
								<td>Only accept images larger than</td>
								<td>
									".$frm->select('subnews_attach_minsize', $imageSizes, $pref['subnews_attach_minsize'], null, 'Any Size')."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_101."</td>
								<td>
									".$frm->number('subnews_resize', $pref['subnews_resize'], 5, 'size=6&class=tbox')."
									<div class='field-help'>".NWSLAN_102."</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_120."</td>
								<td>
									".$frm->bbarea('news_subheader', stripcslashes(vartrue($pref['news_subheader'])), 2, 'helpb')."
								</td>
							</tr>
							</tbody>
						</table>
					";

			$tab1 .= "
							<tr>
								<td>".NWSLAN_111."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->radio_switch('news_newdateheader', $pref['news_newdateheader'])."
										<div class='field-help'>".NWSLAN_112."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_113."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->radio_switch('news_unstemplate', vartrue($pref['news_unstemplate']))."
										<div class='field-help'>".NWSLAN_114."</div>
									</div>
								</td>
							</tr>

						</tbody>
					</table>";

			$text .= $frm->tabs(array(
				'general'	=> array('caption'=>'General', 'text'=>$tab1),
				'subnews'	=> array('caption'=>'Submit News', 'text'=>$tab2)
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

	}


	function noPermissions($qry = '')
	{
		$url = e_SELF.($qry ? '?'.$qry : '');
		if($qry !== e_QUERY)
		{
			$mes = e107::getMessage();
			$this->show_message('Insufficient permissions!', E_MESSAGE_ERROR, true);
			session_write_close();
			header('Location: '.$url);
		}
		exit;
	}
	
	
	function loadSubmitted($id)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		if ($sql->select("submitnews", "*", "submitnews_id=".intval($id)))
		{
			$row = $sql->fetch();
			$data['news_title'] = $tp->dataFilter($row['submitnews_title']);
			$data['news_body'] = $row['submitnews_item'];
			$data['news_category'] = intval( $row['submitnews_category']);
			$data['news_body'] .= "\n[[b]".NWSLAN_49." {$row['submitnews_name']}[/b]]";

			$data['news_thumbnail'] = $row['submitnews_file']; // implode(",",$thumbs);
			$data['news_sef']    = eHelper::dasherize($data['news_title']);
			$data['submitted_id']   = $id;

			foreach($data as $k=>$v)
			{
				$this->getModel()->setData($k, $v); // Override Table data.
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
					$_POST['comment_total'] = e107::getDb()->db_Count("comments", "(*)", " WHERE comment_item_id={$row['news_id']} AND comment_type='0'");
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


	function news_author($curVal, $mode)
	{




		$pref = e107::pref('core');
		$sql = e107::getDb();
		$frm = e107::getForm();


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
			$sql->select("user", "user_name", "user_id={$auth} LIMIT 1");
			$row = $sql->fetch();
			$text .= "<input type='hidden' name='news_author' value='".$auth.chr(35).$row['user_name']."' />";
			$text .= "<a href='".e107::getUrl()->create('user/profile/view', 'name='.$row['user_name'].'&id='.$curVal."'>".$row['user_name'])."</a>";
		}
		else // allow master admin to
		{
			$text .= $frm->select_open('news_author');
			$qry = "SELECT user_id,user_name FROM #user WHERE user_perms = '0' OR user_perms = '0.' OR user_perms REGEXP('(^|,)(H)(,|$)') ";
			if($pref['subnews_class'] && $pref['subnews_class']!= e_UC_GUEST && $pref['subnews_class']!= e_UC_NOBODY)
			{
				if($pref['subnews_class']== e_UC_MEMBER)
				{
					$qry .= " OR user_ban != 1";
				}
				elseif($pref['subnews_class']== e_UC_ADMIN)
				{
					$qry .= " OR user_admin = 1";
				}
				else
				{
					$qry .= " OR FIND_IN_SET(".intval($pref['subnews_class']).", user_class) ";
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
				$text .= $frm->option($row['user_name'], $row['user_id'].chr(35).$row['user_name'], $sel);
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
		    <li class="active"><a href="#news-body-container" data-toggle="tab">'.NWSLAN_13.'</a></li>
		    <li><a href="#news-extended-container" data-toggle="tab">'.NWSLAN_14.'</a></li>
		  </ul>
		  <div class="tab-content">';


		$val = strstr($curVal, "[img]http") ? $curVal : str_replace("[img]../", "[img]", $curVal);
		$text .= "<div id='news-body-container' class='tab-pane active'>";
		$text .= $frm->bbarea('news_body', $val, 'news', 'news', 'large');
		$text .= "</div>";
		$text .= "<div id='news-extended-container' class='tab-pane'>";

		$val = (strstr($curValExt, "[img]http") ? $curValExt : str_replace("[img]../", "[img]",$curValExt));
		$text .= $frm->bbarea('news_extended', $val, 'extended', 'news','large');

		$text .= "</div>
			</div>";

		return $text;

	}





	function news_thumbnail($curval,$mode)
	{

		if($mode == 'read')
		{
			if(!vartrue($curval)) return;

			if(strpos($curval, ",")!==false)
			{
				$tmp = explode(",",$curval);
				$curval = $tmp[0];
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

			return "<a class='e-dialog' href='{$link}'><img src='{$url}' alt='{$curval}' /></a>";
		}


		if($mode == 'write')
		{
			if(!empty($_GET['sub']))
			{
				$thumbTmp = explode(",",$curval);
				$paths = array();
				foreach($thumbTmp as $key=>$path)
				{
					$paths[] = e107::getParser()->thumbUrl(e_TEMP.$path,'aw=800'); ;
				}

				$curval = implode(",", $paths);

			}


			$tp = e107::getParser();
			$frm = e107::getForm();

			//	$text .= $frm->imagepicker('news_thumbnail[0]', $curval ,'','media=news&video=1');
			$thumbTmp = explode(",",$curval);

			foreach($thumbTmp as $key=>$path)
			{
				if(!empty($path) && (strpos($path, ",") == false) && $path[0] != "{" && $tp->isVideo($path) === false )//BC compat
				{
				//	$thumbTmp[$key] = "{e_IMAGE}newspost_images/".$path;
				}
			}

			$text = $frm->imagepicker('news_thumbnail[0]', varset($thumbTmp[0]),'','media=news&video=1');
			$text .= $frm->imagepicker('news_thumbnail[1]', varset($thumbTmp[1]),'','media=news&video=1');
			$text .= $frm->imagepicker('news_thumbnail[2]', varset($thumbTmp[2]),'','media=news&video=1');
			$text .= $frm->imagepicker('news_thumbnail[3]', varset($thumbTmp[3]),'','media=news&video=1');
			$text .= $frm->imagepicker('news_thumbnail[4]', varset($thumbTmp[4]),'','media=news&video=1');

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
			return "<a class='e-tip' href='{$url}' title='Open in new tab' rel='external'>".$value."</a>";
		}
		return $value;
	}
}

	new news_admin();
	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage();



	if(!e_AJAX_REQUEST) require_once("footer.php");
	exit;











$e_sub_cat = 'news';

require_once('auth.php');

/*
 * Observe for delete action
 */
$newspost->observer();

/*
 * Show requested page
 */
$newspost->show_page();



/* OLD JS? Can't find references to this func
echo "
<script type=\"text/javascript\">
function fclear() {
	document.getElementById('core-newspost-create-form').data.value = \"\";
	document.getElementById('core-newspost-create-form').news_extended.value = \"\";
}
</script>\n";
*/

require_once("footer.php");
exit;

/*

class admin_newspost
{
	var $_request = array();
	var $_cal = array();
	var $_pst;
	var $_fields;
	var $_sort_order;
	var $_sort_link;
	var $fieldpref;
	var $news_categories;
	public $news_renderTypes = array();


	public $error = false;

	function __construct($qry='')
	{
		global $user_pref;
		
		
		$qry = "";
		$this->parseRequest($qry);

		require_once(e_HANDLER."cache_handler.php");
		require_once(e_HANDLER."news_class.php");


		$this->fieldpref = varset($user_pref['admin_news_columns'], array('news_id', 'news_title', 'news_author', 'news_render_type', 'options'));

		$this->fields = array(
				'checkboxes'	   		=> array('title' => '', 			'type' => null, 		'data'=> false, 'width' => '3%', 	'thclass' => 'center first', 	'class' => 'center', 	'nosort' => true, 'toggle' => 'news_selected', 'forced' => TRUE),
				'news_id'				=> array('title' => LAN_ID, 	'type' => 'number', 	'data'=> 'int', 'width' => '5%', 	'thclass' => 'center', 			'class' => 'center',  	'nosort' => false),
 				'news_thumbnail'		=> array('title' => NWSLAN_67, 	'type' => 'image', 			'data'=> 'str', 'width' => '110px',	'thclass' => 'center', 			'class' => "center", 		'nosort' => false, 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','writeParams' => 'path={e_MEDIA}','readonly'=>false),		  		
 				'news_title'			=> array('title' => LAN_TITLE, 		'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_summary'			=> array('title' => LAN_SUMMARY, 	'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),			
				
				'news_meta_keywords'	=> array('title' => LAN_KEYWORDS, 	'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_meta_description'	=> array('title' => LAN_DESCRIPTION,'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_sef'				=> array('title' => LAN_SEFURL, 		'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
    			'user_name'				=> array('title' => LAN_AUTHOR, 	'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_datestamp'		=> array('title' => LAN_NEWS_32, 	'type' => 'datestamp', 	'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),
                'category_name'			=> array('title' => NWSLAN_6, 		'type' => 'text', 		'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
  				
  				'news_start'			=> array('title' => "Start", 		'type' => 'datestamp', 	'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),
       			'news_end'				=> array('title' => "End", 			'type' => 'datestamp', 	'data'=> 'str','width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),
                       
  				
  				'news_class'			=> array('title' => LAN_VISIBILITY, 		'type' => 'userclass', 	'data'=> 'str', 'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_render_type'		=> array('title' => LAN_NEWS_49, 	'type' => 'dropdown', 	'data'=> 'comma', 'width' => 'auto', 	'thclass' => 'center', 			'class' => null, 		'nosort' => false),
			   	'news_sticky'			=> array('title' => LAN_NEWS_28, 	'type' => 'boolean', 	'data'=> 'int', 'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false),
                'news_allow_comments' 	=> array('title' => NWSLAN_15, 		'type' => 'boolean', 	'data'=> 'int', 'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false),
                'news_comment_total' 	=> array('title' => LAN_NEWS_60, 	'type' => 'number', 	'data'=> 'int', 'width' => '10%', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'options'				=> array('title' => LAN_OPTIONS, 	'type' => null, 		'data'=> false, 'width' => '10%', 	'thclass' => 'center last', 	'class' => 'center', 	'nosort' => true, 'forced' => TRUE)

		);


	}*/
/*
	function parseRequest($qry)
	{
		$tmp = explode(".", $qry);
		$action = vartrue($tmp[0], 'main');
		$sub_action = varset($tmp[1], '');
		$id = isset($tmp[2]) && is_numeric($tmp[2]) ? intval($tmp[2]) : 0;
		$this->_sort_order = isset($tmp[2]) && !is_numeric($tmp[2]) ? $tmp[2] : 'desc';
		$from = intval(varset($tmp[3],0));
		unset($tmp);
		
		$action = vartrue($_GET['action'],'main');
		$sub_action = varset($_GET['sub'],'');
		$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
		$this->_sort_order = isset($_GET['id']) && !is_numeric($_GET['id']) ? $_GET['id'] : 'desc';
		$from = intval(varset($_GET['frm'],0));


        if ($this->_sort_order != 'asc') $this->_sort_order = 'desc';
		$this->_sort_link = ($this->_sort_order) == 'asc' ? 'desc' : 'asc';
		$sort_order = 'desc';
		$this->_request = array($action, $sub_action, $id, $sort_order, $from);
	}

	function getAction()
	{
		return $this->_request[0];
	}*/

	/**
	 * @param string $action
	 * @return admin_newspost
	 *//*
	function setAction($action)
	{
		$this->_request[0] = $action;
		return $this;
	}

	function getSubAction()
	{
		return $this->_request[1];
	}*/

	/**
	 * @param string $action
	 * @return admin_newspost
	 *//*
	function setSubAction($action)
	{
		$this->_request[1] = $action;
		return $this;
	}*/
/*
	function getId()
	{
		return $this->_request[2];
	}*/

	/**
	 * @param integer $id
	 * @return admin_newspost
	 *//*
	function setId($id)
	{
		$this->_request[2] = intval($id);
		return $this;
	}

	function getSortOrder()
	{
		return $this->_request[3];
	}

	function getFrom()
	{
		return $this->_request[4];
	}

	function clear_cache()
	{
		$ecache = e107::getCache();
		$ecache->clear("news.php"); //TODO change it to 'news_*' everywhere

		$ecache->clear("news_", false, true); //NEW global news cache prefix
		//$ecache->clear("nq_news_"); - supported by cache::clear() now
		//$ecache->clear("nomd5_news_"); supported by cache::clear() now

		$ecache->clear("othernews"); //TODO change it to 'news_othernews' everywhere
		$ecache->clear("othernews2"); //TODO change it to 'news_othernews2' everywhere
		return $this;
	}

	function clear_rwcache($sefstr = '')
	{
		// obsolete
	}

	function set_rwcache($sefstr, $data)
	{
		// obsolete
	}

	function ajax_observer()
	{
		$method = 'ajax_exec_'.$this->getAction();

		if(e_AJAX_REQUEST && method_exists($this, $method))
		{
			$this->$method();
			return true;
		}
		return false;
	}*/


/*
	function observer()
	{
		e107::getDb()->db_Mark_Time('News Administration');
		$this->news_categories = array();
		if(e107::getDb()->select('news_category', '*', (getperms('0') ? '' : 'category_manager IN ('.USERCLASS_LIST.')')))
		{
			$this->news_categories = e107::getDb()->db_getList('ALL', FALSE, FALSE, 'category_id');
		}

		//Required on create & savepreset action triggers
		if(isset($_POST['news_userclass']) && is_array($_POST['news_userclass']))
		{
			$_POST['news_class'] = implode(",", $_POST['news_userclass']);
			unset($_POST['news_userclass']);
		}
		$main = getperms('0');
		if(isset($_POST['delete']) && is_array($_POST['delete']))
		{
			$this->_observe_delete();
		}
		elseif(isset($_POST['execute_batch']))
		{
			$this->process_batch($_POST['news_selected']);
		}
		elseif(isset($_POST['submit_news']))
		{
			$this->_observe_submit_item($this->getSubAction(), $this->getId());
		}
		elseif($main && isset($_POST['create_category']))
		{
			$this->_observe_create_category();
		}
		elseif($main && isset($_POST['update_category']))
		{
			$this->_observe_update_category();
		}
		elseif($main && isset($_POST['multi_update_category']))
		{
			$this->_observe_multi_create_category();
		}
		elseif($main && isset($_POST['save_prefs']))
		{
			$this->_observe_save_prefs();
		}
		elseif(isset($_POST['submitupload']))
		{
			$this->_observe_upload();
		}
		elseif(isset($_POST['news_comments_recalc']))
		{
			$this->_observe_newsCommentsRecalc();
		}

		if(isset($_POST['etrigger_ecolumns'])) //elseif fails.
		{
        //	$this->_observe_saveColumns();
		}
	}*/
/*
	function show_page()
	{
		
	//	print_a($POST);
		
		switch ($this->getAction()) {
			case 'create':
				$this->_pst->read_preset('admin_newspost');  //only works here because $_POST is used.
				$this->show_create_item();
			break;

			case 'cat':
				if(!getperms('0|7'))
				{
					$this->noPermissions();
				}
				$this->show_categories();
			break;

			case 'sn':
				$this->show_submitted_news();
			break;

			case 'pref':
				if(!getperms('0'))
				{
					$this->noPermissions();
				}
				$this->show_news_prefs();
			break;

			case 'maint' :
				if(!getperms('0'))
				{
					$this->noPermissions();
				}
				$this->showMaintenance();
				break;

			default:
				$this->show_existing_items();
			break;
		}
	}*/
/*
	function _observe_delete()
	{
		$admin_log = e107::getAdminLog();
		//FIXME - SEF URL cache
		$tmp = array_keys($_POST['delete']);
		list($delete, $del_id) = explode("_", $tmp[0]);
		$del_id = intval($del_id);

		if(!$del_id) return false;

		$e107 = e107::getInstance();


		switch ($delete) {
			case 'main':
							
				if ($sql->count('news','(*)',"news_id={$del_id}"))
				{
					e107::getEvent()->trigger("newsdel", $del_id);
					
					if(e107::getEvent()->trigger("admin_news_delete", $del_id)) // Allow trigger to halt process if it returns true.  
					{
						return; 
					}
					
					if($sql->delete("news", "news_id={$del_id}"))
					{
						e107::getEvent()->trigger("admin_news_deleted", $del_id);
						e107::getLog()->add('NEWS_01',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_31." #".$del_id." ".NWSLAN_32, E_MESSAGE_SUCCESS);
						$this->clear_cache();

						$data = array('method'=>'delete', 'table'=>'news', 'id'=>$del_id, 'plugin'=>'news', 'function'=>'delete');
						$this->show_message(e107::getEvent()->triggerHook($data), E_MESSAGE_WARNING);

						admin_purge_related("news", $del_id);
					}
				}
			break;

			case 'category':
				
				if(!getperms('0|7')) $this->noPermissions();

				if (($count = $sql->count('news','(news_id)',"news_category={$del_id}")) === false || $count > 0)
				{
					$this->show_message('Category is in used in <strong>'.$count.'</strong> news items and cannot be deleted.', E_MESSAGE_ERROR);
					return false;
				}
				
				if ($sql->count('news_category','(*)',"category_id={$del_id}"))
				{
					e107::getEvent()->trigger("newscatdel", $del_id);
					if ($sql->delete("news_category", "category_id={$del_id}"))
					{
						e107::getLog()->add('NEWS_02',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_33." #".$del_id." ".NWSLAN_32, E_MESSAGE_SUCCESS);
						$this->clear_cache();
					}
				}
			break;

			case 'sn':
				if ($sql->delete("submitnews", "submitnews_id={$del_id}"))
				{
					e107::getLog()->add('NEWS_03',$del_id,E_LOG_INFORMATIVE,'');
					$this->show_message(NWSLAN_34." #".$del_id." ".NWSLAN_32);
					$this->clear_cache();
				}
			break;

			default:
				return  false;
		}

		return true;
	}

*/



	/**
	 * For future use: multiple-images. 
	 *//*
	private function processThumbs($postedImage)
	{
		if(is_array($postedImage))
		{	
			return implode(",",array_filter($postedImage));
		}
		else 
		{
			return $postedImage;
		}
		
	}*/







// In USE.
 /*


	function _observe_submit_item($sub_action, $id)
	{
		// ##### Format and submit item to DB
		
		$ix = new news;
		// jQuery UI temporary date-time fix - inputdatetime -> inputdate
		$_POST['news_start'] = vartrue(e107::getDate()->convert($_POST['news_start'],'inputdatetime'), 0);

		if($_POST['news_start'])
		{
		//	$_POST['news_start'] = e107::getDate()->convert($_POST['news_start']);
		}
		else
		{
	//		$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$_POST['news_end'] = e107::getDate()->convert($_POST['news_end'],'inputdatetime');
		}
		else
		{
			$_POST['news_end'] = 0;
		}
		
		if($_POST['news_datestamp'])
		{
			$_POST['news_datestamp'] = e107::getDate()->convert($_POST['news_datestamp'],'inputdatetime');
		}
		else
		{
			$_POST['news_datestamp'] = time();	
		}
		
		
		$_POST['news_thumbnail'] = $this->processThumbs($_POST['news_thumbnail']); 
		
		
		
		*/
				
		/*
		$matches = array();
		if(preg_match('#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#', $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}
		
		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}
		*/
		
	/*
		if ($id && $sub_action != "sn" && $sub_action != "upload")
		{
			$_POST['news_id'] = $id;
		}
		else
		{
			e107::getDb()->db_Update('submitnews', "submitnews_auth=1 WHERE submitnews_id ={$id}");
			e107::getAdminLog()->log_event('NEWS_07', $id, E_LOG_INFORMATIVE,'');
		}
		if (!isset($_POST['cat_id']))
		{
			$_POST['cat_id'] = 0;
		}
		$_POST['news_category'] = $_POST['cat_id'];
		if(!isset($this->news_categories[$_POST['news_category']]))
		{
			 $this->noPermissions();
		}*/

		/*if(isset($_POST['news_thumbnail']))
		{
			$_POST['news_thumbnail'] = urldecode(basename($_POST['news_thumbnail']));
		}*/
/*
		$_POST['news_render_type'] = implode(",",$_POST['news_render_type']);
//		print_a($_POST);
//	 exit;
        $tmp = explode(chr(35), $_POST['news_author']);
        $_POST['news_author'] = $tmp[0];
		
        $ret = $ix->submit_item($_POST, !vartrue($_POST['create_edit_stay']));
		if($ret['error'])
		{
			e107::getMessage()->mergeWithSession() //merge with session messages
				->add(($id ? LAN_UPDATED_FAILED : LAN_CREATED_FAILED), E_MESSAGE_ERROR);
				
			$_POST['news_sef'] = $ret['data']['news_sef']; 
			return false;
		}
        $this->clear_cache();
		
        if(isset($_POST['create_edit_stay']) && !empty($_POST['create_edit_stay']))
        {
			if($this->getAction() != 'edit')
			{
	        	session_write_close();
				$rurl = e_SELF.(vartrue($ret['news_id']) ? '?mode='.$_GET['mode'].'&action=edit&id='.$ret['news_id'] : '');
				header('Location: '.$rurl);
				exit;
			}
        }
        else
        {
			session_write_close();
			header('Location:'.e_SELF);
			exit;
        }
	}*/


/*


	function _observe_create_category()
	{
		if(!getperms('0|7'))
		{
			 $this->noPermissions();
		}
		//FIXME - lan, e_model based news administration model
		$this->error = false;
		if(empty($_POST['category_name']))
		{
			$this->show_message('Validation Error: Missing Category name', E_MESSAGE_ERROR);
			$this->error = true;
			if(!empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		else
		{
			// first format sef...
			if(empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::title2sef($_POST['category_name']);
			}
			else 
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		
		// ...then check it
		if(empty($_POST['category_sef']))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL value is required field and can\'t be empty!', E_MESSAGE_ERROR);
		}
		elseif(e107::getDb()->db_Count('news_category', '(category_id)', "category_sef='".e107::getParser()->toDB($_POST['category_sef'])."'"))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL is unique field - current value already in use! Please choose another SEF URL value.', E_MESSAGE_ERROR);
		}

		if (!$this->error)
		{
			$inserta = array();

			$inserta['data']['category_icon'] = $_POST['category_icon'];
			$inserta['_FIELD_TYPES']['category_icon'] = 'todb';

			$inserta['data']['category_name'] = $_POST['category_name'];
			$inserta['_FIELD_TYPES']['category_name'] = 'todb';
			
			$inserta['data']['category_sef'] = $_POST['category_sef'];
			$inserta['_FIELD_TYPES']['category_sef'] = 'todb';

			$inserta['data']['category_meta_description'] = eHelper::formatMetaDescription($_POST['category_meta_description']);
			$inserta['_FIELD_TYPES']['category_meta_description'] = 'todb';

			$inserta['data']['category_meta_keywords'] = eHelper::formatMetaKeys($_POST['category_meta_keywords']);
			$inserta['_FIELD_TYPES']['category_meta_keywords'] = 'todb';

			$inserta['data']['category_manager'] = $_POST['category_manager'];
			$inserta['_FIELD_TYPES']['category_manager'] = 'int';

			$inserta['data']['category_order'] = $_POST['category_order'];
			$inserta['_FIELD_TYPES']['category_order'] = 'int';

			$id = e107::getDb()->db_Insert('news_category', $inserta);
			if($id)
			{
				$inserta['data']['category_id'] = $id;
				
				//admin log now supports DB array and method chaining
				e107::getAdminLog()->log_event('NEWS_04', $inserta, E_LOG_INFORMATIVE, '');

				$this->show_message(NWSLAN_35, E_MESSAGE_SUCCESS);
				$this->clear_cache();

				e107::getEvent()->trigger("newscatpost", array_merge($inserta['data'], $rwinserta['data'])); // @deprecated
				e107::getEvent()->trigger("admin_news_category_created", array_merge($inserta['data'], $rwinserta['data']));
			}
			else
			{
				//debug + error message
				if(e107::getDb()->getLastErrorNumber())
				{
					$this->error = true;
					$this->show_message('mySQL Error detected!', E_MESSAGE_ERROR);
					e107::getMessage()->addDebug('mySQL error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText());
				}
			}
		}
	}

*/


/*
	function _observe_update_category()
	{
		if(!getperms('0|7'))
		{
			 $this->noPermissions();
		}
		$this->setId(intval($_POST['category_id']));

		if(!$this->getId())
		{
			return;
		}

		//FIXME - lan, e_model based news administration model
		$this->error = false;
		if(empty($_POST['category_name']))
		{
			$this->show_message('Validation Error: Missing Category name', E_MESSAGE_ERROR);
			$this->error = true;
			if(!empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		else
		{
			// first format sef...
			if(empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::title2sef($_POST['category_name']);
			}
			else 
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		
		// ...then check it
		if(empty($_POST['category_sef']))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL value is required field and can\'t be empty!', E_MESSAGE_ERROR);
		}
		elseif(e107::getDb()->db_Count('news_category', '(category_id)', "category_id<>".$this->getId()." AND category_sef='".(e107::getParser()->toDB($_POST['category_sef'])."'")))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL is unique field - current value already in use! Please choose another SEF URL value.', E_MESSAGE_ERROR);
		}

		if (!$this->error)
		{
			$updatea = array();
			$updatea['data']['category_icon'] = $_POST['category_icon'];
			$updatea['_FIELD_TYPES']['category_icon'] = 'todb';

			$updatea['data']['category_name'] = $_POST['category_name'];
			$updatea['_FIELD_TYPES']['category_name'] = 'todb';
			
			$updatea['data']['category_sef'] = $_POST['category_sef'];
			$updatea['_FIELD_TYPES']['category_sef'] = 'todb';
			
			$updatea['data']['category_meta_description'] = strip_tags($_POST['category_meta_description']);
			$updatea['_FIELD_TYPES']['category_meta_description'] = 'str';

			$updatea['data']['category_meta_keywords'] = $_POST['category_meta_keywords'];
			$updatea['_FIELD_TYPES']['category_meta_keywords'] = 'str';

			$updatea['data']['category_manager'] = $_POST['category_manager'];
			$updatea['_FIELD_TYPES']['category_manager'] = 'int';

			$updatea['data']['category_order'] = $_POST['category_order'];
			$updatea['_FIELD_TYPES']['category_order'] = 'int';

			$updatea['WHERE'] = 'category_id='.$this->getId();

			$inserta = array();
			$rid = 0;

			$upcheck = e107::getDb()->db_Update("news_category", $updatea);
			$rwupcheck = false;
			if($upcheck || !e107::getDb()->getLastErrorNumber())
			{
		
				if ($upcheck || $rwupcheck)
				{
					//admin log now supports DB array and method chaining
					$updatea['data']['category_id'] = $this->getId();
					if($upcheck) e107::getAdminLog()->log_event('NEWS_05', $updatea['data'], E_LOG_INFORMATIVE, '');
					if($rwupcheck && $inserta['data']) e107::getAdminLog()->log_event('NEWS_10', $inserta['data'], E_LOG_INFORMATIVE, '');

					$this->show_message(NWSLAN_36, E_MESSAGE_SUCCESS);
					$this->clear_cache();

					
					e107::getEvent()->trigger("newscatupd", array_merge($updatea['data'], $inserta['data'])); // @deprecated
					e107::getEvent()->trigger("admin_news_category_updated", array_merge($updatea['data'], $inserta['data']));
				}
				else
				{
					$this->show_message(LAN_NO_CHANGE);
				}

				
				$this->setId(0);
			}
			else
			{
				$this->error = true;
				$this->setSubAction('edit');
				$this->show_message('mySQL Error detected!', E_MESSAGE_ERROR);
				$this->show_message('#'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), E_MESSAGE_DEBUG);
				return;
			}
		}
	}

	function _observe_multi_create_category()
	{
		if(!getperms('0|7'))
		{
			 $this->noPermissions();
		}
		$cnt = 0;
		foreach ($_POST['multi_category_manager'] as $cid => $val)
		{
			$order = $_POST['multi_category_order'][$cid];
			$cnt += (int) e107::getDb()->db_Update('news_category', 'category_manager='.intval($val).', category_order='.intval($order).' WHERE category_id='.intval($cid));
		}
		if($cnt) e107::getMessage()->addSuccess(LAN_UPDATED);
	}

	function _observe_save_prefs()
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
		$temp['subnews_class'] 			= intval($_POST['subnews_class']);
		$temp['subnews_htmlarea'] 		= intval($_POST['subnews_htmlarea']);
		$temp['news_subheader'] 		= e107::getParser()->toDB($_POST['news_subheader']);
		$temp['news_newdateheader'] 	= intval($_POST['news_newdateheader']);
		$temp['news_unstemplate'] 		= intval($_POST['news_unstemplate']);
		$temp['news_editauthor']		= intval($_POST['news_editauthor']);
		$temp['news_ping_services']		= explode("\n",$_POST['news_ping_services']);
		$temp['news_default_template']	= preg_replace('#[^\w\pL\-]#u', '', $_POST['news_default_template']);
		$temp['news_list_limit']		= intval($_POST['news_list_limit']);


		e107::getConfig()->updatePref($temp);

		if(e107::getConfig()->save(false))
		{
			e107::getAdminLog()->logArrayDiffs($temp, e107::getPref(), 'NEWS_06');
			$this->clear_cache();
			
		}
	}

	function _observe_upload()
	{
		//$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");

		$uploaded = file_upload(e_NEWSIMAGE);

		foreach($_POST['uploadtype'] as $key=>$uploadtype)
		{
			if($uploadtype == "thumb")
			{
				rename(e_NEWSIMAGE.$uploaded[$key]['name'],e_NEWSIMAGE."thumb_".$uploaded[$key]['name']);
			}

			if($uploadtype == "file")
			{
				rename(e_NEWSIMAGE.$uploaded[$key]['name'],e_DOWNLOAD.$uploaded[$key]['name']);
			}

			if ($uploadtype == "resize" && $_POST['resize_value'])
			{
				require_once(e_HANDLER."resize_handler.php");
				resize_image(e_NEWSIMAGE.$uploaded[$key]['name'], e_NEWSIMAGE.$uploaded[$key]['name'], $_POST['resize_value'], "copy");
			}
		}
	}*/

/*
	function _observe_saveColumns()
	{
		global $user_pref,$admin_log;
		$user_pref['admin_news_columns'] = $_POST['e-columns'];
		save_prefs('user');
		$this->fieldpref = $user_pref['admin_news_columns'];
	}

	function show_existing_items()
	{
		$user_pref = e107::getUser()->getPref(); 
		$sql = e107::getDb();
		
		if(!getperms('H'))
		{
        	return;
		}

		//require_once(e_HANDLER."form_handler.php");
		$frm = e107::getForm(true); //enable inner tabindex counter

	   	// Effectively toggle setting for headings


		$amount = 10;//TODO - pref

		if(!is_array($user_pref['admin_news_columns']))
		{
        	$user_pref['admin_news_columns'] = array("news_id","news_title","news_author","news_render_type");
		}


		$field_columns = $this->fields;

		$e107 = e107::getInstance();

        // ------ Search Filter ------

        $text .= "
			<form method='get' action='".e_SELF."'>
			<div class='left' style='padding:20px'>
			<input type='text' name='srch' value='".$_GET['srch']."' />\n";
			
		$text .= "<select class='tbox' name='filter' onchange='this.form.submit()' >
		<option value=''>All Categories</option>\n"; // TODO LAN

		foreach($this->news_categories as $arr)
		{
			$key = $arr['category_id'];
			$val = $arr['category_name'];
			$sel = ($_GET['filter'] == $key) ? "selected='selected'" : "";
        	$text .= "<option value='$key' {$sel}>".$val."</option>\n";
		}

		$text .= "</select>";
			$text .= $frm->admin_button('searchsubmit', NWSLAN_63, 'search');
			$text .= "
			</div></form>
		";

        // --------------------------------------------

		$query = "
			SELECT n.*, nc.*, u.user_name, u.user_id FROM #news AS n
			LEFT JOIN #news_category AS nc ON n.news_category=nc.category_id
			LEFT JOIN #user AS u ON n.news_author=u.user_id
		";

		$check_perms = !getperms('0') ? " nc.category_manager IN (".USERCLASS_LIST.") " : '';
		
		// Quick qry fix. 
		$check_perms .= (vartrue($_GET['filter'])) ? " n.news_category = ".intval($_GET['filter'])." " : "";
		
		if (vartrue($_GET['srch']))
		{
			$query .= "WHERE {$check_perms}n.news_title REGEXP('".$_GET['srch']."') OR n.news_body REGEXP('".$_GET['srch']."') OR n.news_extended REGEXP('".$_GET['srch']."') ORDER BY n.news_datestamp DESC";
		}
		else
		{
			$ordfield = 'n.news_datestamp';

			if($this->getSubAction() == 'user_name')
			{
				$ordfield = "u.user_name";
			}
			elseif(strpos($this->getSubAction(), 'category_'))
			{
				$ordfield = 'nc.'.$this->getSubAction();
			}
			elseif($this->getSubAction())
			{
				$ordfield = 'n.'.$this->getSubAction();
			}

			$query .= ($check_perms ? "WHERE {$check_perms}" : '')."ORDER BY {$ordfield} ".strtoupper($this->_sort_order);
		}

		$newsposts = $sql->gen($query);
		
		//echo "sql=".$query;
		
		if ($sql->gen($query." LIMIT ".$this->getFrom().", {$amount}"))
		{
			$newsarray = $e107->sql->db_getList();

			$text .= "
				<form action='".e_SELF."' id='newsform' method='post'>
					<fieldset id='core-newspost-list'>
						<legend class='e-hideme'>".NWSLAN_4."</legend>
						<table class='table adminlist'>
							".$frm->colGroup($this->fields, $this->fieldpref)."
							".$frm->thead($this->fields, $this->fieldpref, 'action=main&amp;sub=[FIELD]&amp;id=[ASC]&amp;filter='.intval($_GET['filter']).'&amp;srch='.$_GET['srch'].'&amp;frm=[FROM]')."
							<tbody>";

			$ren_type = array("default","title","other-news","other-news 2"); // Shortened
			
			foreach($newsarray as $row)
			{
				// PREPARE SOME DATA
				// safe to pass $row as it contains username and id only (no sensitive data), user_id and user_name will be internal converted to 'id', 'name' vars 
				$row['user_name'] 			= "<a href='".e107::getUrl()->create('user/profile/view', $row)."' title='{$row['user_name']}'>{$row['user_name']}</a>";		
				$row['news_title'] 			= "<a href='".e107::getUrl()->create('news/view/item', $row)."'>".$tp->toHTML($row['news_title'], false, 'TITLE')."</a>";
				$row['category_name'] 		= "<a href='".e107::getUrl()->create('news/list/items', $row)."'>".$row['category_name']."</a>";
				$row['news_render_type'] 	= $ren_type[$row['news_render_type']];
	
				$row['news_allow_comments'] = !$row['news_allow_comments'] ? true : false; // old reverse logic
				$row['options'] 			= "
												<a class='action' href='".e_SELF."?action=create&amp;sub=edit&amp;id={$row['news_id']}' tabindex='".$frm->getNext()."'>".ADMIN_EDIT_ICON."</a>
												".$frm->submit_image("delete[main_{$row['news_id']}]", LAN_DELETE, 'delete', NWSLAN_39." [ID: {$row['news_id']}]")."
											";
				$row['checkboxes'] 			= $row['news_id'];
				
				

				// AUTO RENDER
				$text .= $frm->renderTableRow($this->fields, $this->fieldpref, $row, 'news_id');
			}

			$text .= "
							</tbody>
						</table>";
			$text .= "<div class='buttons-bar center'>".$this->show_batch_options()."</div>";
			$text .= "
					</fieldset>
				</form>
			";

		}
		else
		{
			$tmp = NWSLAN_43;
			if(vartrue($_GET['srch']))
			{
				$tmp = sprintXXXf(NWSLAN_121, '<em>&quot;'.$_GET['srch'])."&quot;</em> <a href='".e_SELF."'>&laquo; ".LAN_BACK."</a>";
			}
			$text = "<div class='center warning'>{$tmp}</div>";
		}



	//	$newsposts = $sql->count('news');

		if ($newsposts > $amount)
		{
		//	$parms = $newsposts.",".$amount.",".$this->getFrom().",".e_SELF."?".$this->getAction().'.'.($this->getSubAction() ? $this->getSubAction() : 0).'.'.$this->_sort_order.".[FROM]";
			$parms = $newsposts.",".$amount.",".$this->getFrom().",".e_SELF."?action=".$this->getAction().'&amp;sub='.($this->getSubAction() ? $this->getSubAction() : 0).'&amp;id='.$this->_sort_order.'&amp;filter='.intval($_GET['filter']).'&amp;srch='.$_GET['srch']."&amp;frm=[FROM]";
			
			$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
			if ($nextprev) $text .= "<div class='nextprev-bar'>".$nextprev."</div>";

		}

		e107::getRender()->tablerender(NWSLAN_4, e107::getMessage()->render().$text);
	}



	function show_batch_options()
	{
		$classes = e107::getUserClass()->uc_get_classlist();

		// Grab news Category Names;
		e107::getDb()->select('news_category', '*');
        $newscatarray = e107::getDb()->db_getList();
		$news_category = $news_manage = array();
        foreach($newscatarray as $val)
		{
        	$news_category[$val['category_id']] = $val['category_name'];
			$news_manage[$val['category_id']] = $val['category_manager'];
		}

		$comments_array = array('Allow Comments', 'Disable Comments', 'Reverse Allow/Disalow');
		$sticky_array = array(1 => 'Sticky', 0 => 'Not Sticky', 2 => 'Reverse Them'); // more proper controls order

		return e107::getForm()->batchoptions(
			array(
					'delete_selected'		=> LAN_DELETE,
					'category' 				=> array('Modify Category', $news_category),
					'sticky_selected'		=> array('Modify Sticky', $sticky_array),
					'rendertype'			=> array('Modify Render-type', $this->news_renderTypes),
					'comments'				=> array('Modify Comments', $comments_array),
					'__check_class' 		=> array('category' => $news_manage)
			),
		    array(
		         	'userclass'    			=> array('Assign Visibility...',$classes),
			)
	   );
	}

	function batch_category($ids, $value)
	{
		if(!isset($this->news_categories[$value]))
		{
			 $this->noPermissions();
		}
		$sql = e107::getDb();
		$count = $sql->db_Update("news","news_category = ".$value." WHERE news_id IN (".implode(",",$ids).") ");
	}

	function batch_comments($ids, $value)
	{
		$sql = e107::getDb();
		$value = intval($value);
		if(2 === $value) //reverse it
		{
			$count = $sql->db_Update("news","news_allow_comments=1-news_allow_comments WHERE news_id IN (".implode(",",$ids).") ");
		}
		else //set it
		{
			$count = $sql->db_Update("news","news_allow_comments=".$value." WHERE news_id IN (".implode(",",$ids).") ");
		}
	}

	function batch_rendertype($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("news","news_render_type = ".$value." WHERE news_id IN (".implode(",",$ids).") ");
	}

	function batch_userclass($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("news","news_class = ".$value." WHERE news_id IN (".implode(",",$ids).") ");
	}

	function batch_delete($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Delete("news","news_id IN (".implode(",",$ids).") ");
	}
	
	function batch_subdelete($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Delete("submitnews","submitnews_id IN (".implode(",",$ids).") ");
	}
	
	function batch_subcategory($ids, $value)
	{
		if(!isset($this->news_categories[$value]))
		{
			 $this->noPermissions();
		}
		$sql = e107::getDb();
		$count = $sql->db_Update("submitnews","submitnews_category = ".$value." WHERE submitnews_id IN (".implode(",",$ids).") ");
	}

	function batch_sticky($ids, $value)
	{
		$sql = e107::getDb();
		$value = intval($value);
		if(2 === $value) //reverse it
		{
			$count = $sql->db_Update("news","news_sticky=1-news_sticky WHERE news_id IN (".implode(",",$ids).") ");
		}
		else //set it
		{
			$count = $sql->db_Update("news","news_sticky=".$value." WHERE news_id IN (".implode(",",$ids).") ");
		}
	}


	function process_batch($id_array)
	{
		list($type, $tmp, $value) = explode("_",$_POST['execute_batch']);
		$method = "batch_".$type;
		if (method_exists($this,$method) && isset($id_array) )
		{
			$this->$method($id_array,$value);
		}
	}

*/

	// In Use.
	 /*
	function _pre_create()
	{
	
		
		if($this->getSubAction() == "edit" && !$_POST['preview'])
		{
			if(!isset($_POST['submit_news']))
			{
				if(e107::getDb()->select('news', '*', 'news_id='.intval($this->getId())))
				{
					$row = e107::getDb()->fetch();

					if(!isset($this->news_categories[$row['news_category']]))
					{
						$this->noPermissions();
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
					$_POST['comment_total'] = e107::getDb()->db_Count("comments", "(*)", " WHERE comment_item_id={$row['news_id']} AND comment_type='0'");
					$_POST['news_render_type'] = $row['news_render_type'];
					$_POST['news_thumbnail'] = $row['news_thumbnail'];
					$_POST['news_meta_keywords'] = $row['news_meta_keywords'];
					$_POST['news_meta_description'] = $row['news_meta_description'];
				}
			}
		}
	}*/




/*

	function show_create_item()
	{
		$pref = e107::getPref();
		$this->_pre_create();

		require_once(e_HANDLER."userclass_class.php");
		$frm = e107::getForm();

		$text = '';
	///	if (isset($_POST['preview'])) // Deprecated
	//	{
		//	$text = $this->preview_item($this->getId());
	//	}


		$sub_action = $this->getSubAction();
		$id = $this->getSubAction() != 'sn' && $this->getSubAction() != 'upload' ? $this->getId() : 0;

		$e107 = e107::getInstance();
		$tp = e107::getParser();
		$sql = e107::getDb();

		if ($sub_action == "sn" && !varset($_POST['preview']))
		{
			if ($sql->select("submitnews", "*", "submitnews_id=".$this->getId(), TRUE))
			{
				//list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['news_body'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql->fetch();
				$row = $sql->fetch();
				$_POST['news_title'] = $row['submitnews_title'];
				$_POST['news_body'] = $row['submitnews_item'];
				$_POST['cat_id'] = $row['submitnews_category'];

				if (deftrue('e_WYSIWYG'))
				{
				  if (substr($_POST['news_body'],-7,7) == '[/html]') $_POST['news_body'] = substr($_POST['news_body'],0,-7);
				  if (substr($_POST['news_body'],0,6) == '[html]') $_POST['news_body'] = substr($_POST['news_body'],6);
					$_POST['news_body'] .= "<br /><b>".NWSLAN_49." {$row['submitnews_name']}</b>";
					$_POST['news_body'] .= ($row['submitnews_file'])? "<br /><br /><img src='{e_NEWSIMAGE}{$row['submitnews_file']}' class='f-right' />": '';
				}
				else
				{
					$_POST['news_body'] .= "\n[[b]".NWSLAN_49." {$row['submitnews_name']}[/b]]";
					$_POST['news_body'] .= ($row['submitnews_file'])?"\n\n[img]{e_NEWSIMAGE}{$row['submitnews_file']}[/img]": "";
				}
				$_POST['data'] = $tp->dataFilter($_POST['data']);		// Filter any nasties
				$_POST['news_title'] = $tp->dataFilter($_POST['news_title']);
			}
		}*/
/*

		if ($sub_action == "upload" && !varset($_POST['preview']))
		{
			if ($sql->select('upload', '*', "upload_id=".$this->getId())) {
				$row = $sql->fetch();
				$post_author_id = substr($row['upload_poster'], 0, strpos($row['upload_poster'], "."));
				$post_author_name = substr($row['upload_poster'], (strpos($row['upload_poster'], ".")+1));
				$match = array();
				//XXX DB UPLOADS STILL SUPPORTED?
				$upload_file = "pub_" . (preg_match('#Binary\s(.*?)\/#', $row['upload_file'], $match) ? $match[1] : $row['upload_file']);
				$_POST['news_title'] = LAN_UPLOAD.": ".$row['upload_name'];
				$_POST['news_body'] = $row['upload_description']."\n[b]".NWSLAN_49." [link=".$e107->url->create('user/profile/view', 'id='.$post_author_id.'&name='.$post_author_name)."]".$post_author_name."[/link][/b]\n\n[file=request.php?".$upload_file."]{$row['upload_name']}[/file]\n";
			}
		}
*/
/*
		$text .= "
			<ul class='nav nav-tabs'>
		    <li class='active'><a href='#core-newspost-create' data-toggle='tab'>".LAN_NEWS_52."</a></li>
		    <li><a href='#core-newspost-seo' data-toggle='tab'>SEO</a></li>
		    <li><a href='#core-newspost-edit-options' data-toggle='tab'>".LAN_NEWS_53."</a></li>
		  </ul>
		  <form method='post' action='".e_SELF."?".e_QUERY."' id='core-newspost-create-form' ".(FILE_UPLOADS ? "enctype='multipart/form-data'" : "")." >
			
		  <div class='tab-content'>
				
				<div class='tab-pane active' id='core-newspost-create'>
				<fieldset>
					<legend>".LAN_NEWS_52."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".NWSLAN_6.": </td>
								<td>
		";

		if (!$this->news_categories)
		{
			$text .= NWSLAN_10;
		}
		else
		{
			// $text .= $frm->select("cat_id",$this->news_category,$_POST['cat_id']);
			$catopt = array();
			foreach ($this->news_categories as $row)
			{
				$catopt[$row['category_id']] = $tp->toHTML($row['category_name'], FALSE, "LINKTEXT");
			}
			
			$text .= $frm->select("cat_id", $catopt, $_POST['cat_id']);
			/*
			$text .= $frm->select_open('cat_id');

			foreach ($this->news_categories as $row)
			{
					$text .= $frm->option($tp->toHTML($row['category_name'], FALSE, "LINKTEXT"), $row['category_id'], varset($_POST['cat_id']) == $row['category_id']);
			}
			$text .= "</select>";
			*/
	/*
		}
		$text .= "
								</td>
							</tr>
							<tr>
								<td>".LAN_TITLE.":</td>
								<td>
								<input type='text' name='news_title' value=\"". $tp->post_toForm(vartrue($_POST['news_title']))."\" class='tbox' style='width:90%' required='required' />
									".
								// TOO short ->	$frm->text('news_title', $tp->post_toForm($_POST['news_title']),200,array('size'=>300)).
									"
								</td>
							</tr>

							<tr>
								<td>".LAN_SUMMARY.":</td>
								<td>
								<input type='text' name='news_summary' value=\"". $tp->post_toForm(vartrue($_POST['news_summary']))."\" class='tbox' style='width:90%' />
									".
								//	$frm->text('news_summary', $tp->post_toForm($_POST['news_summary']), 250).
									"
								</td>
							</tr>
							
							
		<tr>
								<td>".LAN_TEMPLATE.":</td>
								<td>
		";

		//XXX multiple -selections at once. (comma separated) - working
		$text .= $frm->select('news_render_type', $this->news_renderTypes, vartrue($_POST['news_render_type']), "multiple=1")."
										<div class='field-help'>
											".NWSLAN_74."
										</div>
									</td>
								</tr>
		";



		// -------- News Author ---------------------
        $text .="
							<tr>
								<td>".LAN_AUTHOR.":</td>
								<td>
		";

		if(!getperms('0') && !check_class($pref['news_editauthor']))
		{
			$auth = ($_POST['news_author']) ? intval($_POST['news_author']) : USERID;
			$sql->select("user", "user_name", "user_id={$auth} LIMIT 1");
           	$row = $sql->fetch();
			$text .= "<input type='hidden' name='news_author' value='".$auth.chr(35).$row['user_name']."' />";
			$text .= "<a href='".$e107->url->create('user/profile/view', 'name='.$row['user_name'].'&id='.$_POST['news_author'])."'>".$row['user_name']."</a>";
		}
        else // allow master admin to
		{
			$text .= $frm->select_open('news_author');
			$qry = "SELECT user_id,user_name FROM #user WHERE user_perms = '0' OR user_perms = '0.' OR user_perms REGEXP('(^|,)(H)(,|$)') ";
			if($pref['subnews_class'] && $pref['subnews_class']!= e_UC_GUEST && $pref['subnews_class']!= e_UC_NOBODY)
			{
				if($pref['subnews_class']== e_UC_MEMBER)
				{
					$qry .= " OR user_ban != 1";
				}
				elseif($pref['subnews_class']== e_UC_ADMIN)
				{
	            	$qry .= " OR user_admin = 1";
				}
				else
				{
	            	$qry .= " OR FIND_IN_SET(".intval($pref['subnews_class']).", user_class) ";
				}
			}

	        $sql->gen($qry);
	        while($row = $sql->fetch())
	        {
	        	if(vartrue($_POST['news_author']))
				{
		        	$sel = ($_POST['news_author'] == $row['user_id']);
		        }
				else
				{
		        	$sel = (USERID == $row['user_id']);
				}
				$text .= $frm->option($row['user_name'], $row['user_id'].chr(35).$row['user_name'], $sel);
			}

			$text .= "</select>
			";
		}


		$text .= "</td></tr>\n";
		
		
		// -----
		
		$text .= "<tr>
					
					<td colspan='2'>\n";
		
		$text .= '<ul class="nav nav-tabs">
		    <li class="active"><a href="#news-body-container" data-toggle="tab">'.NWSLAN_13.'</a></li>
		    <li><a href="#news-extended-container" data-toggle="tab">'.NWSLAN_14.'</a></li>
		  </ul>
		  <div class="tab-content">';
		
		
		$val = (strstr($tp->post_toForm(vartrue($_POST['news_body'])), "[img]http") ? $tp->post_toForm(vartrue($_POST['news_body'])) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_body'])));
        $text .= "<div id='news-body-container' class='tab-pane active'>";
        $text .= $frm->bbarea('news_body', $val, 'news', 'news');
		$text .= "</div>";		
		$text .= "<div id='news-extended-container' class='tab-pane'>";
			
		$val = (strstr($tp->post_toForm(vartrue($_POST['news_extended'])), "[img]http") ? $tp->post_toForm($_POST['news_extended']) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_extended'])));
		$text .= $frm->bbarea('news_extended', $val, 'extended', 'news','large');
		
		$text .= "</div>
			</div></td></tr>";
			
			*/
			
			
		//-----------
		
		/*		
				$text .= "
							<tr>
								<td>".NWSLAN_13.":<br /></td>
								<td>
								
								";

		$val = (strstr($tp->post_toForm($_POST['news_body']), "[img]http") ? $tp->post_toForm($_POST['news_body']) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_body'])));
        $text .= $frm->bbarea('news_body', $val, 'news', 'helpb');

		// Extended news form textarea
		// Fixes Firefox issue with hidden wysiwyg textarea.
		// XXX - WYSIWYG is already plugin, this should go
  //		if(defsettrue('e_WYSIWYG')) $ff_expand = "tinyMCE.execCommand('mceResetDesignMode')";
		$val = (strstr($tp->post_toForm($_POST['news_extended']), "[img]http") ? $tp->post_toForm($_POST['news_extended']) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_extended'])));
		$text .= "
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_14.":</td>
								<td>
									".$frm->bbarea('news_extended', $val, 'extended', 'helpc')."
									<!-- <div class='field-help'>".NWSLAN_83."</div> -->
								</td>
							</tr>";
						/*
		/*
							
				$text .= "
									<tr>
										<td>".NWSLAN_66.":</td>
										<td>";
		
				$text .= $frm->mediaUrl('news', NWSLAN_69);
		
				$text .= "
										</td>
									</tr>";
	*/
	/*	 $text .= "
	
									<tr>
										<td>".NWSLAN_67."s:<br />
										".$frm->help(LAN_NEWS_23)."</td>
										<td>
				";
		if(vartrue($_POST['news_thumbnail']) && (strpos($_POST['news_thumbnail'], ",") == false) && $_POST['news_thumbnail'][0] != "{" && $tp->isVideo($_POST['news_thumbnail']) === false )//BC compat
		{
			$_POST['news_thumbnail'] = "{e_IMAGE}newspost_images/".$_POST['news_thumbnail'];	
		}
		
	//	$text .= $frm->imagepicker('news_thumbnail[0]', $_POST['news_thumbnail'] ,'','media=news&video=1');
	
	
	// * XXX Experimental
		$thumbTmp = explode(",",$_POST['news_thumbnail']);
		$text .= $frm->imagepicker('news_thumbnail[0]', varset($thumbTmp[0]),'','media=news&video=1');
		$text .= $frm->imagepicker('news_thumbnail[1]', varset($thumbTmp[1]),'','media=news&video=1');
		$text .= $frm->imagepicker('news_thumbnail[2]', varset($thumbTmp[2]),'','media=news&video=1');
		$text .= $frm->imagepicker('news_thumbnail[3]', varset($thumbTmp[3]),'','media=news&video=1');
		$text .= $frm->imagepicker('news_thumbnail[4]', varset($thumbTmp[4]),'','media=news&video=1');
	

		$text .= "
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset></div>
		";

		//BEGIN SEO block
		$text .= "<div class='tab-pane' id='core-newspost-seo'>
				<fieldset>
					<legend>SEO</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
						
							<tr>
								<td>Friendly URL string: </td>
								<td>
									".$frm->text('news_sef', $tp->post_toForm(vartrue($_POST['news_sef'])), 255, 'size=xxlarge')."
									<div class='field-help'>If left empty will be automatically created from current News Title based on your current <a href='".e_ADMIN_ABS."eurl.php?mode=main&amp;action=settings' title='To URL settings area' rel='external'>URL settings</a></div>
								</td>
							</tr>

							<tr>
								<td>".LAN_KEYWORDS.": </td>
								<td>".$frm->tags('news_meta_keywords', $tp->post_toForm(vartrue($_POST['news_meta_keywords'])), 255)."
								<div class='field-help'>Keywords/tags associated to associate with this news item</div>
								</td>
								
							</tr>
							
		
							<tr>
								<td>Meta description: </td>
								<td>".$frm->textarea('news_meta_description', $tp->post_toForm(vartrue($_POST['news_meta_description'])), 7)."</td>
							</tr>
							
							<tr>
								<td>Notify Ping Services: </td>
								<td>".$frm->checkbox('news_ping',1, false)."</td>
							</tr>
							
						</tbody>
					</table>
				</fieldset></div>
		";

		//BEGIN Options block
		$text .= "<div class='tab-pane' id='core-newspost-edit-options'>
				<fieldset>
					<legend>".LAN_NEWS_53."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".NWSLAN_15.":</td>
								<td>
									".$frm->radio_switch('news_allow_comments', vartrue($_POST['news_allow_comments']),null,null,'inverse=1')."
									<div class='field-help'>
										".NWSLAN_18."
									</div>
								</td>
							</tr>
							
								<tr>
									<td>".NWSLAN_19.":</td>
									<td>
										<div class='field-spacer'>".NWSLAN_21.":</div>
										<div class='field-spacer'>
		";

		
		$text .= $frm->datepicker("news_start",vartrue($_POST['news_start']),"type=datetime");
		$text .= " - ";
		$text .= $frm->datepicker("news_end",vartrue($_POST['news_end']),"type=datetime");

		$text .= "</div>
										<div class='field-help'>
											".NWSLAN_72."
										</div>
									</td>
								</tr>
								<tr>
									<td>".LAN_NEWS_32.":</td>
									<td>
										<div class='field-spacer'>
		";

		$text .= $frm->datepicker("news_datestamp",vartrue($_POST['news_datestamp']),"type=datetime"); //XXX should be 'datetime' when working correctly. 

		$text .= "</div>";
		*/

		/*
		
				$text .= "<div class='field-spacer'>
						".$frm->checkbox('update_datestamp', '1', $_POST['update_datestamp']).$frm->label(NWSLAN_105, 'update_datestamp', '1')."
						</div>
						<div class='field-help'>
						".LAN_NEWS_33."
						</div>";
				*/
	/*
			$text .= "
			</td>
								</tr>
		";




        // --------------------- News Userclass ---------------------------

		$text .= "
								<tr>
									<td>".LAN_VISIBILITY.":</td>
									<td>
										".$frm->uc_select('news_userclass[]', vartrue($_POST['news_class'],0), 'nobody,public,guest,member,admin,classes,language', 'description=1&multiple=1')."
										<div class='field-help'>
											".NWSLAN_84."
										</div>
									</td>
								</tr>
								<tr>
									<td>".LAN_NEWS_28.":</td>
									<td>
										".$frm->checkbox('news_sticky', '1', vartrue($_POST['news_sticky']), array('label' => LAN_NEWS_29))."
										<div class='field-help'>
											".LAN_NEWS_30."
										</div>
									</td>
								</tr>
		";

		if($pref['trackbackEnabled']){ // FIXME onclick expandit not working
			$text .= "
								<tr>
									<td>".LAN_NEWS_34.":</td>
									<td>
										<a class='e-pointer' onclick='expandit(this);'>".LAN_NEWS_35."</a>
										<div class='e-hideme'>
											<div class='field-spacer'>
												<span class='field-help>".LAN_NEWS_37."</span>
											</div>
											<div class='field-spacer'>
												<textarea class='tbox textarea' name='trackback_urls' style='width:95%' cols='80' rows='5'>".$_POST['trackback_urls']."</textarea>
											</div>
										</div>
									</td>
								</tr>
			";
		}
		
		
		//triggerHook
		
		$data = array('method'=>'form', 'table'=>'news', 'id'=>$id, 'plugin'=>'news', 'function'=>'create_item');	
		$text .= $frm->renderHooks($data);
		
		$text .= "</tbody>
					</table>
				</fieldset>
				</div>
				<div class='buttons-bar center'>
				<div class=' btn-group'>";
				
				//	".$frm->admin_button('preview', isset($_POST['preview']) ? NWSLAN_24 : NWSLAN_27 , 'other')."
				
				$text .= $frm->admin_button('submit_news', ($id && $sub_action != "sn" && $sub_action != "upload") ? NWSLAN_25 : NWSLAN_26 , 'update');

				$text .= '<button class="btn btn-success dropdown-toggle left" data-toggle="dropdown">
									<span class="caret"></span>
									</button>
									<ul class="dropdown-menu col-selection">
									<li class="nav-header">After submit:</li>
							';
				$text .= "<li><a href='#' class='e-noclick'>".$frm->checkbox('create_edit_stay', 1, isset($_POST['create_edit_stay']), array('label' => LAN_NEWS_54))."</a></li>";	

													
									
				$text .= "</ul>
					</div>
					<input type='hidden' name='news_id' value='{$id}' />
				</div>
			</form>
		</div>

		";

		$mes = e107::getMessage();
		echo $mes->render().$text;
		// $e107->ns->tablerender($this->getSubAction() == 'edit' ? NWSLAN_29a : NWSLAN_29, $emessage->render().$text);
	}*/

/*
	function preview_item($id)
	{
		$ix = new news;

		$e107 = &e107::getInstance();

		$_POST['news_title'] = $tp->toDB($_POST['news_title']);
		$_POST['news_summary'] = $tp->toDB($_POST['news_summary']);

		$_POST['news_id'] = $id;

		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		$matches = array();
		if(preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}

		$sql->select("news_category", "*", "category_id='".intval($_POST['cat_id'])."'");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql->fetch();

	   	list($_POST['user_id'],$_POST['user_name']) = explode(chr(35), $_POST['news_author']);
		$_POST['news_author'] = $_POST['user_id'];
		$_POST['comment_total'] = $id ? $sql->count("comments", "(*)", " WHERE comment_item_id={$id} AND comment_type='0'") : 0;
		$_PR = $_POST;

		$_PR['news_body'] = $tp->post_toHTML($_PR['news_body'],FALSE);
		$_PR['news_title'] = $tp->post_toHTML($_PR['news_title'],FALSE,"emotes_off, no_make_clickable");
		$_PR['news_summary'] = $tp->post_toHTML($_PR['news_summary']);
		$_PR['news_extended'] = $tp->post_toHTML($_PR['news_extended']);
		$_PR['news_file'] = $_POST['news_file'];
		$_PR['news_thumbnail'] = basename($_POST['news_thumbnail']);

		//$ix->render_newsitem($_PR);

		return "
				<fieldset id='core-newspost-preview'>
					<legend>".NWSLAN_27."</legend>
					<table class='admininfo'>
					<tbody>
						<tr>
							<td colspan='2'>
								".$tp->parseTemplate('{NEWSINFO}').$ix->render_newsitem($_PR, 'return')."
							</td>
						</tr>
					</tbody>
					</table>
				</fieldset>
		";
	}

	function ajax_exec_cat()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		//require_once (e_HANDLER.'js_helper.php');
		$e107 = e107::getInstance();

		$category = array();
		if ($sql->select("news_category", "*", "category_id=".$this->getId()))
		{
			$category = $sql->fetch();
		}

		if(empty($category))
		{
			e_jshelper::sendAjaxError(404, 'Page not found!', 'Requested news category was not found in the DB.', true);
		}
		$jshelper = new e_jshelper();

		$jshelper->addResponseAction('fill-form', $category);

		//show cancel and update, hide create buttons; disable create button (just in case)
		$jshelper->addResponseAction('element-invoke-by-id', array(
			'show' => 		'category-clear,update-category',
			'disabled,1' => 'create-category',
			'hide' => 		'create-category',
			'newsScrollToMe' => 'core-newspost-cat-create'
		));


		//Send the prefered response type
		$jshelper->sendResponse('XML');
	}

	function ajax_exec_cat_list_refresh()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		echo $this->show_categoriy_list();
	}

	function ajax_exec_catorder()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		//interactive category order
		$check = e107::getDb()->db_Update('news_category', 'category_order='.intval($this->getId()).' WHERE category_id='.intval($this->getSubAction()));
		if(e107::getDb()->getLastErrorNumber())
		{
			echo 'mySQL Error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText();
			return;
		}
		if($check)
		{
			e107::getAdminLog()->log_event('NEWS_05', 'category_id='.intval($this->getSubAction()).', category_order='.intval($this->getId()), E_LOG_INFORMATIVE, '');
		}
	}

	function ajax_exec_catmanager()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		//interactive category manage permissions
		$check = e107::getDb()->db_Update('news_category', 'category_manager='.intval($this->getId()).' WHERE category_id='.intval($this->getSubAction()));
		if(e107::getDb()->getLastErrorNumber())
		{
			echo 'mySQL Error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText();
			retrun;
		}
		if($check)
		{
			$class_name = e107::getUserClass()->uc_get_classname($this->getId());
			e107::getAdminLog()->log_event('NEWS_05', 'category_id='.intval($this->getSubAction()).', category_manager='.intval($this->getId()).' ('.$class_name.')', E_LOG_INFORMATIVE, '');
		}
	}

	function show_categories()
	{

		$frm = e107::getForm(false, true);

		$category = array();
		
		if ($this->getSubAction() == "edit" && !isset($_POST['update_category']))
		{
			if (e107::getDb()->select("news_category", "*", "category_id=".$this->getId()))
			{
				$category = e107::getDb()->fetch();
			}
			
		}

		if($this->error && (isset($_POST['update_category']) || isset($_POST['create_category'])))
		{
			foreach ($_POST as $k=>$v)
			{
				if(strpos($k, 'category_') === 0)
				{
					$category[$k] = e107::getParser()->post_toForm($v);
					continue;
				}

				if(strpos($k, 'news_rewrite_') === 0)
				{
					$category_rewrite[$k] = e107::getParser()->post_toForm($v);
					continue;
				}
			}
		}

		//FIXME - lan
		$text = "
			<form method='post' action='".e_SELF."?cat' id='core-newspost-cat-create-form'>
				<fieldset id='core-newspost-cat-create'>
					<legend>".NWSLAN_56."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".NWSLAN_52."</td>
								<td>
									".$frm->text('category_name', $category['category_name'], 200)."
									<div class='field-help'>Required field</div>
								</td>
							</tr>
							<tr>
								<td>Category friendly URL string</td>
								<td>
									".$frm->text('category_sef', $category['category_sef'], 200)."
									<div class='field-help'>If left empty will be automatically created from current Category Title based on your current <a href='".e_ADMIN_ABS."eurl.php?mode=main&amp;action=settings' title='To URL settings area' rel='external'>URL settings</a></div>
								</td>
							</tr>
							<tr>
								<td>Category meta keywords</td>
								<td>
									".$frm->text('category_meta_keywords', $category['category_meta_keywords'], 255)."
									<div class='field-help'>Used on news categoty list page</div>
								</td>
							</tr>
							<tr>
								<td>Category meta description</td>
								<td>
									".$frm->textarea('category_meta_description', $category['category_meta_description'], 5)."
									<div class='field-help'>Used on news categoty list page</div>
								</td>
							</tr>
							<tr>
								<td>Category management permissions</td>
								<td>
									".$frm->uc_select('category_manager',  vartrue($category['category_manager'], e_UC_ADMIN), 'main,admin,classes')."
									<div class='field-help'>Which group of site administrators are able to manage this category related news</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_53."</td>
								<td>
									".$frm->iconpicker('category_icon', $category['category_icon'], NWSLAN_54)."
									".$frm->hidden('category_order', $category['category_order'])."
									
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
		";

		if($this->getId())
		{
			$text .= "
				".$frm->admin_button('update_category', NWSLAN_55, 'update')."
				".$frm->admin_button('category_clear', LAN_CANCEL, 'cancel')."
				".$frm->hidden("category_id", $this->getId())."
			";
		}
		else
		{
			$text .= "
				".$frm->admin_button('create_category', NWSLAN_56, 'create')."
				".$frm->admin_button('update_category', NWSLAN_55, 'update', '', 'other=style="display:none"')."
				".$frm->admin_button('category_clear', LAN_CANCEL, 'cancel', '', 'other=style="display:none"')."
				".$frm->hidden("category_id", 0)."
			";
		}

		$text .= "
					</div>
				</fieldset>
			</form>
			<div id='core-newspost-cat-list-cont'>
				".$this->show_categoriy_list()."
			</div>
		";


		echo e107::getMessage()->render().$text;
		
		// e107::getRender()->tablerender(NWSLAN_46a, e107::getMessage()->render().$text);
	}
*/
/*
	function show_categoriy_list()
	{
		$frm = e107::getForm();

		//FIXME - lan
		$text = "

			<form action='".e_SELF."?cat' id='core-newspost-cat-list-form' method='post'>
				<fieldset id='core-newspost-cat-list'>
					<legend>".NWSLAN_51."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col style='width: 	5%'  />
							<col style='width:  10%' />
							<col style='width:  40%' />
							<col style='width:  20%' />
							<col style='width:  15%' />
							<col style='width:  10%' />
						</colgroup>
						<thead>
							<tr>
								<th class='center'>".LAN_ID."</th>
								<th class='center'>".LAN_ICON."</th>
								<th>".NWSLAN_6."</th>
								<th>Manage Permissions</th>
								<th class='center last'>".LAN_OPTIONS."</th>
								<th class='center'>Order</th>
							</tr>
						</thead>
						<tbody>
		";
		if ($category_total = e107::getDb()->gen("SELECT ncat.* FROM #news_category AS ncat  ORDER BY ncat.category_order ASC"))
		{
			$tindex = 100;
			while ($category = e107::getDb()->fetch()) {

				$icon = '';
				if ($category['category_icon'])
				{
					$icon = (strstr($category['category_icon'], "images/") ? THEME_ABS.$category['category_icon'] : (strpos($category['category_icon'], '{') === 0 ? e107::getParser()->replaceConstants($category['category_icon'], 'abs') : e_IMAGE_ABS."icons/".$category['category_icon']));
					$icon = "<img class='icon action' src='{$icon}' alt='' />";
				}

				$url = '<a href="'.e107::getUrl()->create('news/list/category', $category).'" title="'.$category['category_name'].'" rel="external">'.$category['category_name'].'</a>';
				$text .= "
							<tr>
								<td class='center middle'>{$category['category_id']}</td>
								<td class='center middle'>{$icon}</td>
								<td class='middle'>{$url}</td>
								<td class='middle'>".$frm->uc_select('multi_category_manager['.$category['category_id'].']',  vartrue($category['category_manager'], e_UC_ADMIN), 'main,admin,classes')."</td>
								<td class='center middle'>";
				
		
				$text .= "<a class='action' id='core-news-catedit-{$category['category_id']}' href='".e_SELF."?mode=cat&amp;action=edit&amp;id={$category['category_id']}' tabindex='".$frm->getNext()."'>".defset('ADMIN_EDIT_ICON', '<img src="'.e_IMAGE_ABS.'admin_images/edit_16.png" alt="Edit" />')."</a>";
				
				// $text .= "<a class='action' id='core-news-catedit-{$category['category_id']}' href='".e_SELF."?cat.edit.{$category['category_id']}' tabindex='".$frm->getNext()."'>".defset('ADMIN_EDIT_ICON', '<img src="'.e_IMAGE_ABS.'admin_images/edit_16.png" alt="Edit" />')."</a>";				
								
				$text .= "
									
									".$frm->submit_image("delete[category_{$category['category_id']}]", $category['category_id'], 'delete', e107::getParser()->toJS(NWSLAN_37." [ID: {$category['category_id']} ]"))."
								</td>
								<td class='middle center'>".$frm->text('multi_category_order['.$category['category_id'].']', $category['category_order'], 3, 'size=2&tabindex='.$tindex)."</td>
							</tr>
				";
				$tindex++;
			}

			$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('multi_update_category', LAN_UPDATE, 'update e-hide-if-js')."
						".$frm->admin_button('trigger_list_refresh', 'Refresh List', 'refresh')."
					</div>
			";
			}
			else
			{
				$text .= "<div class='center'>".NWSLAN_10."</div>";
			}

		$text .= "
				</fieldset>
			</form>
		";

		return $text;
	}*/
/*
	function _optrange($num, $zero = true)
	{
		$tmp = range(0, $num < 0 ? 0 : $num);
		if(!$zero) unset($tmp[0]);

		return $tmp;
	}*/
/*
	function ajax_exec_pref_archnum()
	{
		$frm = e107::getForm();
		echo $frm->select('newsposts_archive', $this->_optrange(intval($this->getSubAction()) - 1), intval(e107::getPref('newsposts_archive')), 'class=tbox&tabindex='.intval($this->getId()));
	}*/

/*
    function ajax_exec_searchValue()
	{
		$frm = e107::getForm();
		echo $frm->filterValue($_POST['filtertype'], $this->fields);
	}
*/
/*
	function show_news_prefs()
	{
		$pref = e107::getPref();
		$frm = e107::getForm();

		$sefbaseDiz = str_replace(array("[br]","[","]"), array("<br />","<a href='".e_ADMIN_ABS."eurl.php'>","</a>"), NWSLAN_128 );
		$pingOpt = array('placeholder'=>'eg. blogsearch.google.com/ping/RPC2');
		$pingVal = (!empty($pref['news_ping_services'])) ? implode("\n",$pref['news_ping_services']) : '';
		$newsTemplates = array('default'=>'Default', 'list'=>'List'); //TODO  'category'=>'Categories'? research 'Use non-standard template for news layout' and integrate here. 

		$text = "
			<form method='post' action='".e_SELF."?pref' id='core-newspost-settings-form'>";
			
		$tab1 = "
				
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".NWSLAN_127."</td>
								<td>
									".$frm->select('news_default_template', $newsTemplates, $pref['news_default_template'])."
									<div class='field-help'>Determines how the default news page should appear.</div>
								</td>
							</tr>
							<tr>
								<td>Ping Services</td>
								<td>
									".$frm->textarea('news_ping_services', $pingVal, 4, 100, $pingOpt)."
									<div class='field-help'>Notify these services when you create/update news items. <br />One per line.</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_86."</td>
								<td>
									".$frm->radio_switch('news_cats', $pref['news_cats'])."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_87."</td>
								<td>
									".$frm->select('nbr_cols', $this->_optrange(6, false), $pref['nbr_cols'], 'class=tbox')."
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_88."</td>
								<td>
									".$frm->select('newsposts', $this->_optrange(50, false), $pref['newsposts'], 'class=tbox')."
								</td>
							</tr>
							
							<tr>
								<td>Limit for News-Listing Pages</td>
								<td>
									".$frm->select('news_list_limit', $this->_optrange(50, false), $pref['news_list_limit'], 'class=tbox')."
									<div class='field-help'>eg. news.php?all or news.php?cat.1 or news.php?tag=xxx</div>
								</td>
							</tr>
		
							<tr>
								<td>".NWSLAN_115."</td>
								<td id='newsposts-archive-cont'>
									".$frm->select('newsposts_archive', $this->_optrange(intval($pref['newsposts']) - 1), intval($pref['newsposts_archive']), 'class=tbox')."
									<div class='field-help'>".NWSLAN_116."</div>
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
								<td>".NWSLAN_101."</td>
								<td>
									".$frm->number('subnews_resize', $pref['subnews_resize'], 5, 'size=6&class=tbox')."
									<div class='field-help'>".NWSLAN_102."</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_120."</td>
								<td>
									".$frm->bbarea('news_subheader', stripcslashes(vartrue($pref['news_subheader'])), 2, 'helpb')."
								</td>
							</tr>
							</tbody>
						</table>
					";
							
							$tab1 .= "
							<tr>
								<td>".NWSLAN_111."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->radio_switch('news_newdateheader', $pref['news_newdateheader'])."
										<div class='field-help'>".NWSLAN_112."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_113."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->radio_switch('news_unstemplate', vartrue($pref['news_unstemplate']))."
										<div class='field-help'>".NWSLAN_114."</div>
									</div>
								</td>
							</tr>
						
						</tbody>
					</table>";
			
			$text .= $frm->tabs(array(
				'general'	=> array('caption'=>'General', 'text'=>$tab1),
				'subnews'	=> array('caption'=>'Submit News', 'text'=>$tab2)
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
	}


	function show_submitted_news()
	{	
	
	//TODO - image upload path should be e_MEDIA and using generic upload handler on submitnews.php. 
	
		$e107 = e107::getInstance();
		$frm = e107::getForm();
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		$newsCat = array();
		$sql->select('news_category');
		while($row = $sql->fetch())
		{
			$newsCat[$row['category_id']] = $tp->toHTML($row['category_name'],FALSE,'TITLE');
		}
		
		
		if ($sql->select("submitnews", "*", "submitnews_id !='' ORDER BY submitnews_id DESC"))
		{
			$text .= "
			<form action='".e_SELF."?sn' method='post'>
				<fieldset id='core-newspost-sn-list'>
					<legend class='e-hideme'>".NWSLAN_47."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col style='width: 2%' />
							<col style='width: 5%' />
							<col style='width: 60%' />
							<col style='width: auto' />
							<col style='width: auto' />
							<col style='width: auto' />
							<col style='width: auto' />
							<col style='width: 20%' />
						</colgroup>
						<thead>
							<tr>
								<th class='center'>&nbsp;</th>
								<th class='center'>ID</th>
								<th>".NWSLAN_57."</th>
								<th>".LAN_DATE."</th>
								<th>".LAN_AUTHOR."</th>
								<th>".NWSLAN_6."</th>
								<th>".NWSLAN_123."</th>
								<th class='center last'>".LAN_OPTIONS."</th>							
							</tr>
						</thead>
						<tbody>
			";
			while ($row = $sql->fetch())
			{
				$buttext = ($row['submitnews_auth'] == 0)? NWSLAN_58 :	NWSLAN_103;

				if (substr($row['submitnews_item'], -7, 7) == '[/html]') $row['submitnews_item'] = substr($row['submitnews_item'], 0, -7);
				if (substr($row['submitnews_item'],0 , 6) == '[html]') $row['submitnews_item'] = substr($row['submitnews_item'], 6);

				$text .= "
					<tr>
						<td class='center'><input type='checkbox' name='news_selected[".$row['submitnews_id']."]' value='".$row['submitnews_id']."' /></td>
						<td class='center'>{$row['submitnews_id']}</td>
						<td>
					
						<a href=\"javascript:expandit('submitted_".$row['submitnews_id']."')\">";
				$text .= $tp->toHTML($row['submitnews_title'],FALSE,'TITLE');
				$text .= '</a>';
			//	$text .=  [ '.NWSLAN_104.' '.$submitnews_name.' '.NWSLAN_108.' '.date('D dS M y, g:ia', $submitnews_datestamp).']<br />';
				$text .= "<div id='submitted_".$row['submitnews_id']."' style='display:none'>".$tp->toHTML($row['submitnews_item'],TRUE);
				$text .= ($row['submitnews_file']) ? "<br /><img src='".e_IMAGE_ABS."newspost_images/".$row['submitnews_file']."' alt=\"".$row['submitnews_file']."\" />" : "";
				$text .= "
				</div>
						
						</td>";
						
				$text .= "<td class='nowrap'>".date('D jS M, Y, g:ia', $row['submitnews_datestamp'])."</td>
				<td><a href=\"mailto:".$row['submitnews_email']."?subject=[".SITENAME."] ".trim($row['submitnews_title'])."\" title='".$row['submitnews_email']." - ".e107::getIPHandler()->ipDecode($row['submitnews_ip'])."'>".$row['submitnews_name']."</a></td>
				<td>".$newsCat[$row['submitnews_category']]."</td>
				<td class='center'>".($row['submitnews_auth'] == 0 ?  "-" : ADMIN_TRUE_ICON)."</td>		
						
				
						<td>
							<div class='field-spacer center nowrap'>
								".$frm->admin_button("category_view_{$row['submitnews_id']}", NWSLAN_27, 'action', '', array('id'=>false, 'other'=>"onclick=\"expandit('submitted_".$row['submitnews_id']."')\""))."
								".$frm->admin_button("category_edit_{$row['submitnews_id']}", $buttext, 'action', '', array('id'=>false, 'other'=>"onclick=\"document.location='".e_SELF."?create.sn.{$row['submitnews_id']}'\""))."
								".$frm->admin_button("delete[sn_{$row['submitnews_id']}]", LAN_DELETE, 'delete', '', array('id'=>false, 'title'=>$tp->toJS(NWSLAN_38." [".LAN_ID.": {$row['submitnews_id']} ]")))."
							</div>
						</td>
					</tr>
				";
			}
			
			
			$text .= "
						</tbody>
					</table>";
			$text .= "<div class='buttons-bar center'>";
			$text .= e107::getForm()->batchoptions(array(
				'subdelete_selected'		=> LAN_DELETE,
				'subcategory' 				=> array('Modify Category', $newsCat)
				));
				
			
			$text .= "</div>
			
			
				</fieldset>
				
			</form>
			";
		}
		else
		{
			$text .= "<div class='center'>".NWSLAN_59."</div>";
		}
		e107::getRender()->tablerender(NWSLAN_47, e107::getMessage()->render().$text);
	}



	function showMaintenance()
	{
		$frm = e107::getForm();

		$text = "
			<form method='post' action='".e_SELF."?maint' id='core-newspost-maintenance-form'>
				<fieldset id='core-newspost-maintenance'>
					<legend class='e-hideme'>".LAN_NEWS_59."</legend>
					<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td>".LAN_NEWS_56."</td>
							<td>
								".$frm->checkbox('newsdeletecomments', '1', '0').LAN_NEWS_61."
							</td>
							<td>
								".$frm->admin_button('news_comments_recalc', LAN_NEWS_57, 'update')."
							</td>
						</tr>
					</tbody>
					</table>
				</fieldset>
			</form>
		";

		e107::getRender()->tablerender(LAN_NEWS_59, e107::getMessage()->render().$text);
	}


	function _observe_newsCommentsRecalc()
	{
		if(!getperms('0'))
		{
			$this->noPermissions();
		}

		$qry = "SELECT 
			COUNT(`comment_id`) AS c_count,
			`news_id`, `news_comment_total`, `news_allow_comments`
			FROM `#news` LEFT JOIN `#comments` ON `news_id`=`comment_item_id` 
			WHERE (`comment_type`='0') OR (`comment_type`='news')
			GROUP BY `comment_item_id`";

		$deleteCount = 0;
		$updateCount = 0;
		$canDelete = isset($_POST['newsdeletecomments']);
		if ($result = e107::getDb()->gen($qry))
		{
			while ($row = e107::getDb()->fetch())
			{
				if ($canDelete && ($row['news_allow_comments'] != 0) && ($row['c_count'] > 0))	// N.B. sense of 'news_allow_comments' is 0 = allow!!!
				{		// Delete comments
					e107::getDb('sql2')->db_Delete('comments', 'comment_item_id='.$row['news_id']);
					$deleteCount = $deleteCount + $row['c_count'];
					$row['c_count'] = 0;		// Forces update of news table if necessary
				}
				if ($row['news_comment_total'] != $row['c_count'])
				{
					e107::getDb('sql2')->db_Update('news', 'news_comment_total = '.$row['c_count'].' WHERE news_id='.$row['news_id']);
					$updateCount++;
				}
			}
			$this->show_message(str_replace(array('--UPDATE--', '--DELETED--'), array($updateCount, $deleteCount), LAN_NEWS_58), E_MESSAGE_SUCCESS);
		}
		else
		{
			$this->show_message(LAN_NEWS_62, E_MESSAGE_WARNING);
		}
	}



	function show_message($message, $type = E_MESSAGE_INFO, $session = false)
	{
		// ##### Display comfort ---------
		e107::getMessage()->add($message, $type, $session);
	}

	function noPermissions($qry = '')
	{
		$url = e_SELF.($qry ? '?'.$qry : '');
		if($qry !== e_QUERY)
		{
			$this->show_message('Insufficient permissions!', E_MESSAGE_ERROR, true);
			session_write_close();
			header('Location: '.$url);
		}
		exit;
	}*/
/*

	function show_options()
	{
		$e107 = e107::getInstance();

		$var['main']['text'] = NWSLAN_44;
		$var['main']['link'] = e_SELF;
		$var['main']['perm'] = "H";

		$var['create']['text'] = NWSLAN_45;
		$var['create']['link'] = e_SELF."?action=create";
		$var['create']['perm'] = "H";

		$var['cat']['text'] = NWSLAN_46;
		$var['cat']['link'] = e_SELF."?action=cat";
		$var['cat']['perm'] = "7";

		$var['pref']['text'] = NWSLAN_90;
		$var['pref']['link'] = e_SELF."?action=pref";
		$var['pref']['perm'] = "0";

//TODO remove commented code before release.
	//	$c = $sql->count('submitnews');
	//	if ($c) {
			$var['sn']['text'] = NWSLAN_47." ({$c})";
			$var['sn']['link'] = e_SELF."?action=sn";
			$var['sn']['perm'] = "N";
	//	}

		if (getperms('0'))
		{
			$var['maint']['text'] = LAN_NEWS_55;
			$var['maint']['link'] = e_SELF."?action=maint";
			$var['maint']['perm'] = "N";
		}

		e107::getNav()->admin(NWSLAN_48, $this->getAction(), $var);
	}
*/

// }
