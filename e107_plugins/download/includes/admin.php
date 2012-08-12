<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Download Plugin Administration UI
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_plugins/release/includes/admin.php $
 * $Id: admin.php 12212 2011-05-11 22:25:02Z e107coders $
*/

//require_once(e_HANDLER.'admin_handler.php'); - autoloaded - see class2.php __autoload()
if (!defined('e107_INIT')){ exit; } 

class plugin_download_admin extends e_admin_dispatcher
{
	/**
	 * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'index' => 'list', 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
	 * Note - default mode/action is autodetected in this order:
	 * - $defaultMode/$defaultAction (owned by dispatcher - see below)
	 * - $adminMenu (first key if admin menu array is not empty)
	 * - $modes (first key == mode, corresponding 'index' key == action)
	 * @var array
	 */
	protected $modes = array (
		'main'		=> array (
					'controller' => 'download_main_admin_ui',
					'path' => null,
					'ui' => 'download_main_admin_form_ui',
					'uipath' => null
		),
		'cat'		=> array (
					'controller' 	=> 'download_cat_ui',
					'path' 			=> null,
					'ui' 			=> 'download_cat_form_ui',
					'uipath' 		=> null
		)	
	);

	/* Both are optional
	protected $defaultMode = null;
	protected $defaultAction = null;
	*/

	/**
	 * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}release/admin_config.php', 'perm' => '0']);
	 * Additionally, any valid e_admin_menu() key-value pair could be added to the above array
	 * @var array
	 */
	protected $adminMenu = array(
		'main/list'			=> array('caption'=> 'Manage', 'perm' => 'P'),
		'main/create' 		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		'cat/list'			=> array('caption'=> DOWLAN_31, 'perm'=>'P'),
		'cat/create' 		=> array('caption'=> "Create Category", 'perm' => 'Q'),
		'main/settings' 	=> array('caption'=> 'Settings', 'perm' => 'P'),
		'main/maint' 		=> array('caption'=> DOWLAN_165, 'perm' => 'P'),
		'main/limits'		=> array('caption'=> DOWLAN_112, 'perm' => 'P'),
		'main/mirror'		=> array('caption'=> DOWLAN_128, 'perm' => 'P')
	);
/*
	$var['main']['text'] = DOWLAN_29;
	$var['main']['link'] = e_SELF;
	$var['create']['text'] = DOWLAN_30;
	$var['create']['link'] = e_SELF."?create";
	$var['cat']['text'] = DOWLAN_31;
	$var['cat']['link'] = e_SELF."?cat";
	$var['cat']['perm'] = "Q";
	$var['opt']['text'] = LAN_OPTIONS;
	$var['opt']['link'] = e_SELF."?opt";
	$var['maint']['text'] = DOWLAN_165;
	$var['maint']['link'] = e_SELF."?maint";
	$var['limits']['text'] = DOWLAN_112;
	$var['limits']['link'] = e_SELF."?limits";
	$var['mirror']['text'] = DOWLAN_128;
	$var['mirror']['link'] = e_SELF."?mirror";
	e_admin_menu(DOWLAN_32, $action, $var);

   unset($var);
	$var['ulist']['text'] = DOWLAN_22;
	$var['ulist']['link'] = e_SELF."?ulist";;
	$var['filetypes']['text'] = DOWLAN_23;
	$var['filetypes']['link'] = e_SELF."?filetypes";
	$var['uopt']['text'] = LAN_OPTIONS;
	$var['uopt']['link'] = e_SELF."?uopt";

*/

	/**
	 * Optional, mode/action aliases, related with 'selected' menu CSS class
	 * Format: 'MODE/ACTION' => 'MODE ALIAS/ACTION ALIAS';
	 * This will mark active main/list menu item, when current page is main/edit
	 * @var array
	 */
	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list',
		'cat/edit'	=> 'cat/list'
	);

	/**
	 * Navigation menu title
	 * @var string
	 */
	protected $menuTitle = 'Downloads Menu';
}


class download_cat_ui extends e_admin_ui
{ 	 	 
		protected $pluginTitle	= 'Download Categories';
		protected $pluginName	= 'download';
		protected $table 		= "download_category";
		protected $pid			= "download_category_id";
		protected $perPage 		= 0; //no limit
		protected $listOrder = 'download_category_order';
		// protected $defaultOrderField = 'download_category_parent,download_category_order';
	//	protected $listQry = "SELECT * FROM #faq_info"; // without any Order or Limit. 
	//	protected $editQry = "SELECT * FROM #faq_info WHERE faq_info_id = {ID}";
	 	 	
		protected $fields = array(
			'checkboxes'						=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'download_category_icon' 			=> array('title'=> LAN_ICON,		'type' => 'icon',			'width' => '5%', 'thclass' => 'center','class'=>'center' ),	 
			'download_category_id'				=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE),     		
         	'download_category_name' 			=> array('title'=> LAN_TITLE,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left'), 
         	'download_category_description' 	=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'download_category_parent' 			=> array('title'=> 'Parent',		'type' => 'method',			'width' => '5%', 'batch' => TRUE, 'filter'=>TRUE),		
			'download_category_class' 			=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'width' => 'auto', 'data' => 'int', 'batch' => TRUE, 'filter'=>TRUE),
			'download_category_order' 			=> array('title'=> LAN_ORDER,		'type' => 'text',			'width' => '5%', 'thclass' => 'right', 'class'=> 'right' ),					
			'options' 							=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
		);	
		

	function getDownloadCategoryTree($id = false, $default = 'n/a')
	{
		// TODO get faq category tree
		$sql = e107::getDb();
		$sql -> db_Select_gen('SELECT * FROM #download_category ORDER BY download_category_order');
		$cats = array();
		$cats[0] = $default;
		while($row = $sql->db_Fetch())
		{
			$cats[$row['download_category_id']] = $row['download_category_name'];
		}
		
		if($id)
		{
			return $cats[$id];
		}
		
		return $cats;
	}	
		
}

class download_cat_form_ui extends e_admin_form_ui
{
	public function download_category_parent($curVal,$mode)
	{
		// TODO - catlist combo without current cat ID in write mode, parents only for batch/filter 
		// Get UI instance
		$controller = $this->getController();
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getDownloadCategoryTree($curVal), false, 'TITLE');
			break;
			
			case 'write':
				return $this->selectbox('download_category_parent', $controller->getDownloadCategoryTree(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getDownloadCategoryTree();
			break;
		}
	}
}







class download_main_admin_ui extends e_admin_ui
{
		// required
		protected $pluginTitle = "Downloads";
		protected $pluginName = 'download';
		protected $table = "download"; // DB Table, table alias is supported. Example: 'r.release'
		protected $listQry = "SELECT m.*,u.user_id,u.user_name FROM #download AS m LEFT JOIN #user AS u ON m.download_author = u.user_id "; // without any Order or Limit.
		
		//required - default column user prefs
		protected $fieldpref = array('checkboxes', 'download_id', 'download_category', 'download_name', 'fb_template', 'fb_class', 'fb_order', 'options');
	
		//

		// optional - required only in case of e.g. tables JOIN. This also could be done with custom model (set it in init())
		//protected $editQry = "SELECT * FROM #release WHERE release_id = {ID}";

		// required - if no custom model is set in init() (primary id)
		protected $pid = "download_id";
		
		// optional
		protected $perPage = 10;

		// default - true - TODO - move to displaySettings
		protected $batchDelete = true;

	
    	protected  $fields = array(
			'checkboxes'				=> array('title'=> '', 					'type' => null,			'data' => null,			'width'=>'5%', 		'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect'),
			'download_id'				=> array('title'=> ID, 					'type' => 'number',		'data' => 'int',		'width'=>'5%',		'thclass' => '',	'forced'=> TRUE, 'primary'=>TRUE/*, 'noedit'=>TRUE*/), //Primary ID is not editable
            'download_name' 			=> array('title'=> LAN_TITLE, 			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),		
            'download_url'	   			=> array('title'=> DOWLAN_13, 			'type' => 'url', 	'data' => 'str',		'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
			'download_author' 			=> array('title'=> LAN_AUTHOR,			'type' => 'user', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),
         	'download_author_email' 	=> array('title'=> DOWLAN_16, 			'type' => 'email', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),  
         	'download_author_website' 	=> array('title'=> DOWLAN_17, 			'type' => 'url', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),
            'download_description' 		=> array('title'=> LAN_DESCRIPTION,		'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	
		 	'download_filesize' 		=> array('title'=> DOWLAN_66,			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'right', 'class' => 'right'),			
		 	'download_requested' 		=> array('title'=> DOWLAN_29, 			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'right', 'class' => 'right'),
			'download_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',	'width' => 'auto',	'batch' => TRUE, 'filter'=>TRUE),		
			'download_active'			=> array('title'=> DOWLAN_21,			'type' => 'method', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center', 'class' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			'download_datestamp' 		=> array('title'=> LAN_DATE, 			'type' => 'datestamp', 	'data' => 'int',		'width' => 'auto',	'thclass' => '', 'readParms' => 'long', 'writeParms' => ''),
			
			'download_thumb' 			=> array('title'=> DOWLAN_20,			'type' => 'image', 		'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>TRUE ),
			'download_image' 			=> array('title'=> DOWLAN_19,			'type' => 'image', 		'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>TRUE,	'batch' => FALSE, 'filter'=>FALSE),
			'download_comment'			=> array('title'=> DOWLAN_102,			'type' => 'boolean', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			
			'download_class' 			=> array('title'=> DOWLAN_113,			'type' => 'userclass',		'width' => 'auto', 'data' => 'int','batch' => TRUE, 'filter'=>TRUE),		
			'download_visible' 			=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',		'width' => 'auto', 'data' => 'int', 'batch' => TRUE, 'filter'=>TRUE),
			
			'download_mirror' 			=> array('title'=> DOWLAN_128,			'type' => 'text', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),
			'download_mirror_type' 		=> array('title'=> DOWLAN_195,			'type' => 'method', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),
		
	
		//	'download_order' 	=> array('title'=> LAN_ORDER,	'type' => 'text',			'width' => '5%', 'thclass' => 'left' ),					
			'issue' 					=> array('title'=> 'Issue', 		'type' => 'method', 		'data' => null,	'nolist'=>TRUE, 'noedit'=>TRUE, 'filter'=>TRUE),
			'options' 					=> array('title'=> LAN_OPTIONS, 		'type' => null, 		'data' => null,			'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE)
		);
		
		
		protected $action 		= array();
		protected $subAction 	= array();
		protected $id			= "";
		
		
		
/*		
$columnInfo = array(
		 "checkboxes"	   			=> array("title" => "", "forced"=> TRUE, "width" => "3%", "thclass" => "center first", "toggle" => "dl_selected"),
         "download_id"              => array("title"=>DOWLAN_67,  "type"=>"", "width"=>"auto", "thclass"=>"", "forced"=>true),
         "download_name"            => array("title"=>DOWLAN_12,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_url"             => array("title"=>DOWLAN_13,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_author"          => array("title"=>DOWLAN_15,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_author_email"    => array("title"=>DOWLAN_16,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_author_website"  => array("title"=>DOWLAN_17,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_description"     => array("title"=>DOWLAN_18,  "type"=>"", "width"=>"auto", "thclass"=>""),
         
 * 		 "download_filesize"        => array("title"=>DOWLAN_66,  "type"=>"", "width"=>"auto", "thclass"=>"right"),
         "download_requested"       => array("title"=>DOWLAN_29,  "type"=>"", "width"=>"auto", "thclass"=>"center"),
         "download_category"        => array("title"=>DOWLAN_11,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_active"          => array("title"=>DOWLAN_21,  "type"=>"", "width"=>"auto", "thclass"=>"center"),
         "download_datestamp"       => array("title"=>DOWLAN_182, "type"=>"", "width"=>"auto", "thclass"=>""),
         
 * 		 "download_thumb"           => array("title"=>DOWLAN_20,  "type"=>"", "width"=>"auto", "thclass"=>"center"),
         "download_image"           => array("title"=>DOWLAN_19,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_comment"         => array("title"=>DOWLAN_102, "type"=>"", "width"=>"auto", "thclass"=>"center"),
         "download_class"           => array("title"=>DOWLAN_113, "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_mirror"          => array("title"=>DOWLAN_128, "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_mirror_type"     => array("title"=>DOWLAN_195, "type"=>"", "width"=>"auto", "thclass"=>""),
         "download_visible"         => array("title"=>DOWLAN_43,  "type"=>"", "width"=>"auto", "thclass"=>""),
		 "options"			        => array("title"=>LAN_OPTIONS, "width"=>"10%", "thclass"=>"center last", "forced"=>true)
		);
*/		
		




		// FORMAT field_name=>type - optional if fields 'data' attribute is set or if custom model is set in init()
		/*protected $dataFields = array();*/

		// optional, could be also set directly from $fields array with attributes 'validate' => true|'rule_name', 'rule' => 'condition_name', 'error' => 'Validation Error message'
		/*protected  $validationRules = array(
			'release_url' => array('required', '', 'Release URL', 'Help text', 'not valid error message')
		);*/

		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array(
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
		);

		
		public function observe()
		{
			if (isset($_POST['submit_download'])) // Create or Update a Download. 
			{
				$this->submit_download();
			}
			
			if (isset($_POST['updatedownlaodoptions'])) // Save Download Options. 
			{
				$this->saveSettings();
			}
			
			if (isset($_POST['submit_mirror']))
			{
				$this->submit_mirror();
			}
					
			if (isset($_POST))
			{
				e107::getCache()->clear("download_cat");
			}
		}
		
		
		
		// optional
		public function init()
		{
			
			$this->action 		= $_GET['mode'];
			$this->subAction 	= $_GET['action'];
			$this->id			= $_GET['id'];
			
			$this->observe();
			
			
			$categories = array();
			if(e107::getDb()->db_Select('download_category'))
			{
				//$categories[0] = LAN_SELECT;
				while ($row = e107::getDb()->db_Fetch())
				{
					$id = $row['download_category_id'];
					$categories[$id] = $row['download_category_name'];
				}
			}
	
			$this->fields['download_category']['writeParms'] 		= $categories;
			// DEPRECATED
			//$this->fields['fb_rendertype']['writeParms'] 	= array(FBLAN_23,FBLAN_24);
			//$this->fields['fb_mode']['writeParms'] 			= array(FBLAN_13,FBLAN_14);
			
			$this->fields['download_category']['readParms'] 		= $categories;
			
			// Custom filter queries 
			if($_GET['filter_options'])
			{
				list($filter,$mode) = explode("__",$_GET['filter_options']);
				
				if($mode == 'missing')
				{
					$this->filterQry = $this->missingFiles();
				}
				
				if($mode == 'nocategory')
				{
					$this->filterQry = "SELECT * FROM `#download` WHERE download_category=0";
				}
      
	  			if($mode == 'duplicates')
				{
					$this->filterQry = "SELECT GROUP_CONCAT(d.download_id SEPARATOR ',') as gc, d.download_id, d.download_name, d.download_url, dc.download_category_name
                      FROM #download as d
                      LEFT JOIN #download_category AS dc ON dc.download_category_id=d.download_category
                      GROUP BY d.download_url
                      HAVING COUNT(d.download_id) > 1";
				}
				
				if($mode == "filesize")
				{
					$this->filterQry = $this->missingFiles('filesize');	
				}

			}	
			
		}


		/*
		 * Return a query for Missing Files and Filesize mismatch 
		 */
		public function missingFiles($mode='missing')
		{
			
			$sql = e107::getDb();
			$count = array();
			
            if ($sql->db_Select_gen("SELECT * FROM `#download` ORDER BY download_id"))
            {
               while($row = $sql->db_Fetch())
			   {
               		if (!is_readable(e_DOWNLOAD.$row['download_url']))
					{		 
					 	$count[] = $row['download_id']; 				 
					}
					elseif($mode == 'filesize')
					{
					 	$filesize = filesize(e_DOWNLOAD.$row['download_url']);
                     	if ($filesize <> $row['download_filesize'])
						{
							$count[] = $row['download_id'];	
						}
					}
					 
               }
            }
            
			if($count > 0)
			{
				return "SELECT * FROM `#download` WHERE download_id IN (".implode(",",$count).")";
			}
			
		}
		
		
		
		function orphanFiles() //TODO
		{
			
			$files = e107::getFile()->get_files(e_DOWNLOAD);
            $foundSome = false;
            foreach($files as $file)
			{
               if (0 == $sql->db_Count('download', '(*)', " WHERE download_url='".$file['fname']."'")) {
                  if (!$foundSome) {
   		           // $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                     $text .= '<form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
                     <table class="adminlist">';
                     $text .= '<tr>';
                     $text .= '<th>'.DOWLAN_13.'</th>';
                     $text .= '<th>'.DOWLAN_182.'</th>';
                     $text .= '<th>'.DOWLAN_66.'</th>';
                     $text .= '<th>'.LAN_OPTIONS.'</th>';
                     $text .= '</tr>';
                     $foundSome = true;
                  }
                  $filesize = (is_readable(e_DOWNLOAD.$row['download_url']) ? $e107->parseMemorySize(filesize(e_DOWNLOAD.$file['fname'])) : DOWLAN_181);
                  $filets   = (is_readable(e_DOWNLOAD.$row['download_url']) ? $gen->convert_date(filectime(e_DOWNLOAD.$file['fname']), "long") : DOWLAN_181);
                  $text .= '<tr>';
                  $text .= '<td>'.$tp->toHTML($file['fname']).'</td>';
                  $text .= '<td>'.$filets.'</td>';
                  $text .= '<td>'.$filesize.'</td>';

               }
            }
		}
		
		

		function createPage()
		{
			global $adminDownload;
			$this->create_download();
		}
			
		function importPage()
		{
			$this->batchImportForm();
		}
	
		function settingsPage()
		{
			// global $adminDownload;
			$this->show_download_options();
		}
		
		function limitsPage()
		{
			showLimits();
		}
		
		function maintPage()
		{
			showMaint();	
		}
	
		function mirrorPage()
		{
			global $adminDownload;
			$this->show_existing_mirrors();
		}
		
		
		
		function saveSettings()
		{
			global $admin_log,$pref;
					
			$tp = e107::getParser();
			
			$temp = array();
			$temp['download_php'] = $_POST['download_php'];
			$temp['download_view'] = $_POST['download_view'];
			$temp['download_sort'] = $_POST['download_sort'];
			$temp['download_order'] = $_POST['download_order'];
			$temp['mirror_order'] = $_POST['mirror_order'];
			$temp['recent_download_days'] = $_POST['recent_download_days'];
			$temp['agree_flag'] = $_POST['agree_flag'];
			$temp['download_email'] = $_POST['download_email'];
			$temp['agree_text'] = $tp->toDB($_POST['agree_text']);
			$temp['download_denied'] = $tp->toDB($_POST['download_denied']);
			$temp['download_reportbroken'] = $_POST['download_reportbroken'];
			if ($_POST['download_subsub']) $temp['download_subsub'] = '1'; else $temp['download_subsub'] = '0';
			if ($_POST['download_incinfo']) $temp['download_incinfo'] = '1'; else $temp['download_incinfo'] = '0';
			if ($admin_log->logArrayDiffs($temp, $pref, 'DOWNL_01'))
			{
				save_prefs();
				// e107::getMessage()->add(DOWLAN_65);
			}
			else
			{
				// e107::getMessage()->add(DOWLAN_8);
			}
		}

			
		// Create Download FORM. 
	   function create_download()
	   {
	   		$action		= $this->action;
			$subAction	= $this->subAction;
			$id			= $this->id;
	   	
			$sql = e107::getDb();
			$tp = e107::getParser();
			$fl = e107::getFile();
			
		//	print_a($this);
	
	      	global $e107, $cal, $rs, $ns, $file_array, $image_array, $thumb_array, $pst;
	      	require_once(e_PLUGIN.'download/download_shortcodes.php');
			require_once(e_PLUGIN.'download/handlers/download_class.php');
	      	require_once(e_HANDLER."form_handler.php");
			
			$download = new download;
	
		    if ($file_array = $fl->get_files(e_DOWNLOAD, "","standard",5))
		    {
		    	sort($file_array);
		    }
		    if ($public_array = $fl->get_files(e_UPLOAD))
		    {
		    	foreach($public_array as $key=>$val)
		    	{
					$file_array[] = str_replace(e_UPLOAD,"",$val);
				}
			}
	/*      if ($sql->db_Select("rbinary")) //TODO Remove me.
	      {
	         while ($row = $sql->db_Fetch())
	         {
	            extract($row);
	            $file_array[] = "Binary ".$binary_id."/".$binary_name;
	         }
	      }
	*/
	      if ($image_array = $fl->get_files(e_FILE.'downloadimages/', '\.gif$|\.jpg$|\.png$|\.GIF$|\.JPG$|\.PNG$','standard',2))
	      {
	         sort($image_array);
	      }
	      if ($thumb_array = $fl->get_files(e_FILE.'downloadthumbs/', '\.gif$|\.jpg$|\.png$|\.GIF$|\.JPG$|\.PNG$','standard',2))
	      {
	         sort($thumb_array);
	      }
	
	      $frm = new e_form();
	      $mirrorArray = array();
	
	      $download_status[0] = DOWLAN_122;
	      $download_status[1] = DOWLAN_123;
	      $download_status[2] = DOWLAN_124;
	
	      if (!$sql->db_Select("download_category"))
	      {
	         $ns->tablerender(ADLAN_24, "<div style='text-align:center'>".DOWLAN_5."</div>");
	         return;
	      }
	      $download_active = 1;
	      if ($_GET['action'] == "edit" && !$_POST['submit'])
	      {
	         if ($sql->db_Select("download", "*", "download_id=".intval($_GET['id'])))
	         {
	            $row = $sql->db_Fetch();
	            extract($row);
	
	            $mirrorArray = $this->makeMirrorArray($row['download_mirror']);
	         }
	      }
	
	      if ($subAction == "dlm" && !$_POST['submit'])
	      {
	         require_once(e_PLUGIN.'download/download_shortcodes.php');
	         if ($sql->db_Select("upload", "*", "upload_id=".$id))
	         {
	            $row = $sql->db_Fetch();
	
	            $download_category = $row['upload_category'];
	            $download_name = $row['upload_name'].($row['upload_version'] ? " v" . $row['upload_version'] : "");
	            $download_url = $row['upload_file'];
	            $download_author_email = $row['upload_email'];
	            $download_author_website = $row['upload_website'];
	            $download_description = $row['upload_description'];
	            $download_image = $row['upload_ss'];
	            $download_filesize = $row['upload_filesize'];
	            $image_array[] = array("path" => "", "fname" => $row['upload_ss']);
	            $download_author = substr($row['upload_poster'], (strpos($row['upload_poster'], ".")+1));
	         }
	      }
	
	
	      $text = "
	      <div class='admintabs' id='tab-container'>
	         <ul class='e-tabs' id='core-download-tabs'>
	            <li id='tab-general'><a href='#download-create'>".DOWLAN_175."</a></li>
	            <li id='tab-external'><a href='#download-edit-external'>".DOWLAN_176."</a></li>
	            <li id='tab-mirror'><a href='#download-edit-mirror'>".DOWLAN_128."</a></li>
	         </ul>
	         <div>
	            <form method='post' action='".e_SELF."?".e_QUERY."' id='myform'>
	               <fieldset id='download-create'>
	                  <table style='".ADMIN_WIDTH."' class='adminlist'>
	                     <tr>
	                        <td style='width:20%;'>".DOWLAN_13."</td>
	                        <td style='width:80%'>
	                           <div>".DOWLAN_131."&nbsp;&nbsp;";
							  
	                   //       $text .= "<select name='download_url' class='tbox'>
	                  //               <option value=''>&nbsp;</option>\n";
	   
	
	      $counter = 0;
	      while (isset($file_array[$counter]))
	      {
	         $fpath = str_replace(e_DOWNLOAD,"",$file_array[$counter]['path']).$file_array[$counter]['fname'];
	         $selected = '';
	         if (stristr($fpath, $download_url) !== FALSE)
	         {
	            $selected = " selected='selected'";
	            $found = 1;
	         }
	
	     //    $text .= "<option value='".$fpath."' $selected>".$fpath."</option>\n";
	         $counter++;
	      }
	
	      if (preg_match("/http:|https:|ftp:/", $download_url))
	      {
	         $download_url_external = $download_url;
	         $download_url = '';
	      }
	
	      $etext = " - (".DOWLAN_68.")";
	      if (file_exists(e_UPLOAD.$download_url))
	      {
	         $etext = "";
	      }
	
	      if (!$found && $download_url)
	      {
	    //     $text .= "<option value='".$download_url."' selected='selected'>".$download_url.$etext."</option>\n";
	      }
	
	  //    $text .= "             </select>";
	  
	  	$text .= e107::getForm()->filepicker("download_url",$download_url,DOWLAN_131,"media=download_file&title=Choose a file");
	  
	      $text .= "
	                        </div>
	                     </td>
	                  </tr>
	               </table>
	            </fieldset>
	            <fieldset id='download-edit-external'>
	               <table style='".ADMIN_WIDTH."' class='adminlist'>
	                  <tr>
	                       <td style='width:20%;'>".DOWLAN_149."</td>
	                       <td style='width:80%;'>
	                          <input class='tbox' type='text' name='download_url_external' size='70' value='{$download_url_external}' maxlength='255'/>
	                       </td>
	                    </tr>
	                    <tr>
	                       <td>".DOWLAN_66."</td>
	                       <td>
	                          <input class='tbox' type='text' name='download_filesize_external' size='8' value='{$download_filesize}' maxlength='10'/>
	                       </td>
	                  </tr>
	               </table>
	            </fieldset>
	            <fieldset id='download-edit-mirror'>
	               <table style='".ADMIN_WIDTH."' class='adminlist'>
	                  <tr>
	                     <td style='width:20%'><span title='".DOWLAN_129."' style='cursor:help'>".DOWLAN_128."</span></td>
	                     <td style='width:80%'>";
	
	      // See if any mirrors to display
	      if (!$sql -> db_Select("download_mirror"))
	      {   // No mirrors defined here
	         $text .= DOWLAN_144."</td></tr>";
	      }
	      else
	      {
	         $text .= DOWLAN_132."<div id='mirrorsection'>";
	         $mirrorList = $sql -> db_getList();         // Get the list of possible mirrors
	         $m_count = (count($mirrorArray) ? count($mirrorArray) : 1);      // Count of mirrors actually in use (or count of 1 if none defined yet)
	         for($count = 1; $count <= $m_count; $count++)
	         {
	            $opt = ($count==1) ? "id='mirror'" : "";
	            $text .="
	                        <div {$opt}>
	                           <select name='download_mirror_name[]' class='tbox'>
	                              <option value=''>&nbsp;</option>";
	
	            foreach ($mirrorList as $mirror)
	            {
	               extract($mirror);
	               $text .= "<option value='{$mirror_id}'".($mirror_id == $mirrorArray[($count-1)]['id'] ? " selected='selected'" : "").">{$mirror_name}</option>\n";
	            }
	
	            $text .= "</select>
	                           <input  class='tbox' type='text' name='download_mirror[]' style='width: 60%;' value=\"".$mirrorArray[($count-1)]['url']."\" maxlength='200'/>
	                           <input  class='tbox' type='text' name='download_mirror_size[]' style='width: 15%;' value=\"".$mirrorArray[($count-1)]['filesize']."\" maxlength='10'/>";
	            if (DOWNLOAD_DEBUG)
	            {
	               if ($id)
	               {
	                  $text .= '('.$mirrorArray[($count-1)]['requests'].')';
	               }
	               else
	               {
	               $text .= "<input  class='tbox' type='text' name='download_mirror_requests[]' style='width: 10%;' value=\"".$mirrorArray[($count-1)]['requests']."\" maxlength='10'/>";
	               }
	            }
	            $text .= "  </div>";
	         }
	         $text .="      </div>
	                        <input class='button' type='button' name='addoption' value='".DOWLAN_130."' onclick=\"duplicateHTML('mirror','mirrorsection')\"/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%' ><span style='cursor:help' title='".DOWLAN_154."'>".DOWLAN_155."</span></td>
	                     <td style='width:80%'>
	                        <input type='radio' name='download_mirror_type' value='1'".($download_mirror_type ? " checked='checked'" : "")."/> ".DOWLAN_156."<br/>
	                        <input type='radio' name='download_mirror_type' value='0'".(!$download_mirror_type ? " checked='checked'" : "")."/> ".DOWLAN_157."
	                     </td>
	                  </tr>";
	      }      // End of mirror-related stuff
	
	      $download_author = $subAction != "edit" && $download_author == "" ? USERNAME : $download_author;//TODO what if editing an no author specified
	      $download_author_email = $subAction != "edit" && $download_author_email == "" ? USEREMAIL : $download_author_email;
	      $text .= "
	               </table>
	            </fieldset>
	            <fieldset id='download-edit-therest'>
	               <table style='".ADMIN_WIDTH."' class='adminlist'>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_11."</td>
	                     <td style='width:80%'>";
	      $text .= $download->getCategorySelectList($download_category);
	   // $text .= download::getCategorySelectList($download_category);
		
	      $text .= "     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%;'>".DOWLAN_12."</td>
	                     <td style='width:80%'>
	                        <input class='tbox' type='text' name='download_name' size='60' value=\"".$tp->toForm($download_name)."\" maxlength='200'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_15."</td>
	                     <td style='width:80%'>
	                        <input class='tbox' type='text' name='download_author' size='60' value='$download_author' maxlength='100'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_16."</td>
	                     <td style='width:80%'>
	                        <input class='tbox' type='text' name='download_author_email' size='60' value='$download_author_email' maxlength='100'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_17."</td>
	                     <td style='width:80%'>
	                        <input class='tbox' type='text' name='download_author_website' size='60' value='$download_author_website' maxlength='100'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_18."</td>
	                     <td style='width:80%'>
	      ";
	      $text .= $frm->bbarea('download_description',$download_description);
		  
	      $text .= "     </td>
	                  </tr>
	                  <tr>
	                     <td>
	                        Activation between
	                     </td>
	                     <td>
	                         // TODO
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_19."</td>
	                     <td style='width:80%'>";
	       /*
															$text = "<select name='download_image' class='tbox'>
										  <option value=''>&nbsp;</option>";
																foreach($image_array as $img)
					 {
						$fpath = str_replace(e_FILE."downloadimages/","",$img['path'].$img['fname']);
						  $sel = ($download_image == $fpath) ? " selected='selected'" : "";
						  $text .= "<option value='".$fpath."' $sel>".$fpath."</option>\n";
					 }
									 $text .= "     </select>";
										   */
		   
		$text .= $frm->imagepicker('download_image', $download_image,'','download_image'); 
		  
	      if ($subAction == "dlm" && $download_image)
	      {
	         $text .= "
	         <input type='hidden' name='move_image' value='1'/>\n";
	      }
	      $text .= "     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_20."</td>
	                     <td style='width:80%'>";
	     /*
										   $text .= "
									 <select name='download_thumb' class='tbox'>
										<option value=''>&nbsp;</option>";
				   foreach($thumb_array as $thm){
					  $tpath = str_replace(e_FILE."downloadthumbs/","",$thm['path'].$thm['fname']);
					  $sel = ($download_thumb == $tpath) ? " selected='selected'" : "";
					  $text .= "<option value='".$tpath."' $sel>".$tpath."</option>\n";
				   }
								 $text .= "        </select>";
				   */
		 
		 $text .= $frm->imagepicker('download_thumb', $download_thumb,'','download_thumb'); 
		 
		 
	      $text .= "
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".LAN_DATESTAMP."</td>
	                     <td style='width:80%'>";
	      if (!$download_datestamp){
	           $download_datestamp = time();
	      }
	
			$text .= $frm->datepicker('download_datestamp',$download_datestamp);
			
	  //    $update_checked = ($_POST['update_datestamp']) ? "checked='checked'" : "";
	  //    $text .= "        &nbsp;&nbsp;<span><input type='checkbox' value='1' name='update_datestamp' $update_checked/>".DOWLAN_148."</span>";
		  
		  $text .= "
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_21."</td>
	                     <td style='width:80%'>
	                        <select name='download_active' class='tbox'>";
	      foreach($download_status as $key => $val){
	         $sel = ($download_active == $key) ? " selected = 'selected' " : "";
	           $text .= "<option value='{$key}' {$sel}>{$val}</option>\n";
	      }
	      $text .= "        </select>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_102."</td>
	                     <td style='width:80%'>";
	      if ($download_comment == "0") {
	         $text .= LAN_YES.": <input type='radio' name='download_comment' value='1'/>
	            ".LAN_NO.": <input type='radio' name='download_comment' value='0' checked='checked'/>";
	      } else {
	         $text .= LAN_YES.": <input type='radio' name='download_comment' value='1' checked='checked'/>
	            ".LAN_NO.": <input type='radio' name='download_comment' value='0'/>";
	      }
	      $text .= "     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_145."</td>
	                     <td style='width:80%'>".r_userclass('download_visible', $download_visible, 'off', 'public, nobody, member, admin, classes, language')."</td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_106."</td>
	                     <td style='width:80%'>".r_userclass('download_class', $download_class, 'off', 'public, nobody, member, admin, classes, language')."</td>
	                  </tr>";
	      if ($subAction == "dlm") {
	         $text .= "
	                  <tr>
	                     <td style='width:30%'>".DOWLAN_153."</td>
	                     <td style='width:70%'>
	                        <select name='move_file' class='tbox'>
	                           <option value=''>".LAN_NO."</option>";
	           $dl_dirlist = $fl->get_dirs(e_DOWNLOAD);
	           if ($dl_dirlist){
	            sort($dl_dirlist);
	            $text .= "<option value='".e_DOWNLOAD."'>/</option>\n";
	            foreach($dl_dirlist as $dirs)
	            {
	                 $text .= "<option value='". e_DOWNLOAD.$dirs."/'>".$dirs."/</option>\n";
	            }
	         }
	         else
	         {
	              $text .= "<option value='".e_DOWNLOAD."'>".LAN_YES."</option>\n";
	         }
	         $text .= "     </select>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:30%'>".DOWLAN_103."</td>
	                     <td style='width:70%'>
	                        <input type='checkbox' name='remove_upload' value='1'/>
	                        <input type='hidden' name='remove_id' value='$id'/>
	                     </td>
	                  </tr>";
	      }
	
	      //triggerHook
	      $data = array('method'=>'form', 'table'=>'download', 'id'=>$id, 'plugin'=>'download', 'function'=>'create_download');
	      $hooks = $e107->e_event->triggerHook($data);
	      if(!empty($hooks))
	      {
	         $text .= "<tr>
	                     <td colspan='2' >".LAN_HOOKS." </td>
	                   </tr>
	         ";
	         foreach($hooks as $hook)
	         {
	            if(!empty($hook))
	            {
	               $text .= "<tr>
	                            <td class='label'>".$hook['caption']."</td>
	                            <td class='control'>".$hook['text']."</td>
	                         </tr>";
	            }
	         }
	      }
	
	      $text .= "
	      	<tr style=''>
	        <td colspan='2' style='text-align:center'>";
	                     
		
						 
	      if ($id && $subAction == "edit")
		  {
	         $text .= "<input class='button' type='submit' name='submit_download' value='".DOWLAN_24."'/> ";
	      } else
	      {
	         $text .= "<input class='button' type='submit' name='submit_download' value='".DOWLAN_25."'/>";
	      }
	
	      $text .= "
	                     </td>
	                  </tr>
	               </table>
	            </fieldset>
	         </form>
	         </div>
	         </div>";
	     // $ns->tablerender(ADLAN_24, $text);
	     echo $text;
	   }



	// Actually save a new or edited download to the DB
	   function submit_download()
	   {
			global $e107, $tp, $sql, $DOWNLOADS_DIRECTORY, $e_event;
		  
			$action		= $this->action;
			$subAction	= $this->subAction;
			$id			= $this->id;
		
	   		$sql = e107::getDb();
			$tp = e107::getParser();
			$fl = e107::getFile();
			$mes = e107::getMessage();
	
			$dlInfo = array();
			$dlMirrors = array();
	
		    if ($subAction == 'edit')
		    {
		    	if ($_POST['download_url_external'] == '')
		        {
		        	$_POST['download_filesize_external'] = FALSE;
		       	}
			}
	
			if ($_POST['download_url_external'] && $_POST['download_url'] == '')
			{
				$dlInfo['download_url'] = $tp->toDB($_POST['download_url_external']);
				$filesize = intval($_POST['download_filesize_external']);
			}
			else
			{
				$dlInfo['download_url'] = $tp->toDB($_POST['download_url']);
				if ($_POST['download_filesize_external'])
				{
	            	$filesize = intval($_POST['download_filesize_external']);
	         	}
	         	else
	         	{
		            if (strpos($DOWNLOADS_DIRECTORY, "/") === 0 || strpos($DOWNLOADS_DIRECTORY, ":") >= 1)
		            {
		               $filesize = filesize($DOWNLOADS_DIRECTORY.$dlInfo['download_url']);
		            }
		            else
		            {
		               $filesize = filesize(e_BASE.$DOWNLOADS_DIRECTORY.$dlInfo['download_url']);
		            }
				}
			}
	
	      if (!$filesize)
	      {
	         if ($sql->db_Select("upload", "upload_filesize", "upload_file='{$dlInfo['download_url']}'"))
	         {
	            $row = $sql->db_Fetch();
	            $filesize = $row['upload_filesize'];
	         }
	      }
	      $dlInfo['download_filesize'] = $filesize;
	
	
	      //  ----   Move Images and Files ------------
	      if ($_POST['move_image'])
	      {
	         if ($_POST['download_thumb'])
	         {
	            $oldname = e_UPLOAD.$_POST['download_thumb'];
	            $newname = e_FILE."downloadthumbs/".$_POST['download_thumb'];
	            if (!$this -> move_file($oldname,$newname))
	            {
	                  return;
	            }
	         }
	         if ($_POST['download_image'])
	         {
	            $oldname = e_UPLOAD.$_POST['download_image'];
	            $newname = e_FILE."downloadimages/".$_POST['download_image'];
	            if (!$this -> move_file($oldname,$newname))
	            {
	                  return;
	            }
	         }
	      }
	
	        if ($_POST['move_file'] && $_POST['download_url'])
	      {
	           $oldname = e_UPLOAD.$_POST['download_url'];
	         $newname = $_POST['move_file'].$_POST['download_url'];
	         if (!$this -> move_file($oldname,$newname))
	         {
	               return;
	         }
	            $dlInfo['download_url'] = str_replace(e_DOWNLOAD,"",$newname);
	      }
	
	
	       // ------------------------------------------
	
	
			$dlInfo['download_description'] 		= $tp->toDB($_POST['download_description']);
			$dlInfo['download_name'] 				= $tp->toDB($_POST['download_name']);
			$dlInfo['download_author'] 				= $tp->toDB($_POST['download_author']);
			$dlInfo['download_author_email'] 		= $tp->toDB($_POST['download_author_email']);
			$dlInfo['download_author_website'] 		= $tp->toDB($_POST['download_author_website']);
			$dlInfo['download_category'] 			= intval($_POST['download_category']);
			$dlInfo['download_active']  			= intval($_POST['download_active']);
			$dlInfo['download_thumb']				= $tp->toDB($_POST['download_thumb']);
	      	$dlInfo['download_image']				= $tp->toDB($_POST['download_image']);
	      	$dlInfo['download_comment']				= $tp->toDB($_POST['download_comment']);
	      	$dlInfo['download_class']				= intval($_POST['download_class']);
	      	$dlInfo['download_visible']				= intval($_POST['download_visible']);
			$dlInfo['download_datestamp']			= e107::getDate()->convert($_POST['download_datestamp'],'inputdate');
			
	
	      if($_POST['update_datestamp'])
	      {
				$dlInfo['download_datestamp'] = time();
	      }
	
	      $mirrorStr = "";
	      $mirrorFlag = FALSE;
	
	      // See if any mirrors defined
	      // Need to check all the possible mirror names - might have deleted the first one if we're in edit mode
	      
	      if(count($_POST['download_mirror_name']))
		  {
				foreach ($_POST['download_mirror_name'] as $mn)
				{
					if ($mn)
		        	{
		        	   $mirrorFlag = TRUE;
		        	   break;
		        	}
		     	}	
		  }
	      
	      if ($mirrorFlag)
	      {
	         $mirrors = count($_POST['download_mirror_name']);
	         $mirrorArray = array();
	         $newMirrorArray = array();
	         if ($id && $sql->db_Select('download','download_mirror', 'download_id = '.$id))      // Get existing download stats
	         {
	            if ($row = $sql->db_Fetch())
	            {
	               $mirrorArray = $this->makeMirrorArray($row['download_mirror'], TRUE);
	            }
	         }
	         for($a=0; $a<$mirrors; $a++)
	         {
	            $mid = trim($_POST['download_mirror_name'][$a]);
	            $murl = trim($_POST['download_mirror'][$a]);
	            $msize = trim($_POST['download_mirror_size'][$a]);
	            if ($mid && $murl)
	            {
	               $newMirrorArray[$mid] = array('id' => $mid, 'url' => $murl, 'requests' => 0, 'filesize' => $msize);
	               if (DOWNLOAD_DEBUG && !$id)
	               {
	                  $newMirrorArray[$mid]['requests'] = intval($_POST['download_mirror_requests'][$a]);
	               }
	            }
	         }
	         // Now copy across any existing usage figures
	         foreach ($newMirrorArray as $k => $m)
	         {
	            if (isset($mirrorArray[$k]))
	            {
	               $newMirrorArray[$k]['requests'] = $mirrorArray[$k]['requests'];
	            }
	         }
	         $mirrorStr = $this->compressMirrorArray($newMirrorArray);
	      }
	
	      $dlMirrors['download_mirror']=$mirrorStr;
	      $dlMirrors['download_mirror_type']=intval($_POST['download_mirror_type']);
	
	      if ($id) // Its an edit
	      {  	
	         	// Process triggers before calling admin_update so trigger messages can be shown
	         	$data = array('method'=>'update', 'table'=>'download', 'id'=>$id, 'plugin'=>'download', 'function'=>'update_download');
	         	$hooks = $e107->e_event->triggerHook($data);
	         	require_once(e_HANDLER."message_handler.php");
	        
	   			$mes->add($hooks, E_MESSAGE_SUCCESS);
		
				$updateArray = array_merge($dlInfo,$dlMirrors);
				$updateArray['WHERE'] = 'download_id='.intval($id);
				
				$mes->autoMessage($sql->db_Update('download',$updateArray), 'update', DOWLAN_2." (<a href='".e_PLUGIN."download/download.php?view.".$id."'>".$_POST['download_name']."</a>)");
	                
				$dlInfo['download_id'] = $id;
				$this->downloadLog('DOWNL_06',$dlInfo,$dlMirrors);
				$dlInfo['download_datestamp'] = $time;      // This is what 0.7 did, regardless of settings
				unset($dlInfo['download_class']);         // Also replicating 0.7
				$e_event->trigger('dlupdate', $dlInfo);
	      }
	      else // Its a new entry. 
	      {
		         if ($download_id = $sql->db_Insert('download',array_merge($dlInfo,$dlMirrors)))
		         {
		            // Process triggers before calling admin_update so trigger messages can be shown
		            $data = array('method'=>'create', 'table'=>'download', 'id'=>$download_id, 'plugin'=>'download', 'function'=>'create_download');
		            $hooks = $e107->e_event->triggerHook($data);
		       
		            $mes->add($hooks, E_MESSAGE_SUCCESS);
		
		            $mes->autoMessage($download_id, 'insert', DOWLAN_1." (<a href='".e_PLUGIN."download/download.php?view.".$download_id."'>".$_POST['download_name']."</a>)");
		
		            $dlInfo['download_id'] = $download_id;
		            $this->downloadLog('DOWNL_05',$dlInfo,$dlMirrors);
		            $dlInfo['download_datestamp'] = $time;      // This is what 0.7 did, regardless of settings
		            unset($dlInfo['download_class']);         // Also replicating 0.7
		            $e_event->trigger("dlpost", $dlInfo);
		
		            if ($_POST['remove_upload'])
		            {
		               $sql->db_Update("upload", "upload_active='1' WHERE upload_id='".$_POST['remove_id']."'");
		               $mess = "<br/>".$_POST['download_name']." ".DOWLAN_104;
		               $mess .= "<br/><br/><a href='".e_ADMIN."upload.php'>".DOWLAN_105."</a>";
		               $this->show_message($mess);
		            }
		         }
	      }
	   }


		// Turn the array into a string which can be stored in the DB
		function compressMirrorArray($source)
	   	{
	      	if (!is_array($source) || !count($source)) return '';
	      	$inter = array();
	      	foreach ($source as $s)
	      	{
	      	   $inter[] = $s['id'].','.$s['url'].','.$s['requests'].','.$s['filesize'];
	      	}
	      	return implode(chr(1),$inter);
	   	}







		function show_existing_mirrors()
		{
			$sql 		= e107::getDb();
			$tp 		= e107::getParser();
			$mes 		= e107::getMessage();
			$fl			= e107::getFile();
			
			$action		= $this->action;
			$subAction	= $this->subAction;
			$id			= $this->id;
			
			global $delete, $del_id, $admin_log;
	
	      require_once(e_HANDLER."form_handler.php");
	      $frm = new e_form();
		  
		  
	      if ($delete == "mirror")
	      {
	         $mes->autoMessage($sql -> db_Delete("download_mirror", "mirror_id=".$del_id), delete, DOWLAN_135);
	         $admin_log->log_event('DOWNL_14','ID: '.$del_id,E_LOG_INFORMATIVE,'');
	      }
	
	
	      if (!$sql -> db_Select("download_mirror"))
	      {
	   			$mes->addInfo(DOWLAN_144);
	         // $text = "<div style='text-align:center;'>".DOWLAN_144."</div>"; // No mirrors defined yet
	      }
	      else
	      {
	
	         $text = "<div>
	         <form method='post' action='".e_SELF."?".e_QUERY."'>
	         <table style='".ADMIN_WIDTH."' class='adminlist'>
	         <tr>
	         <td style='width: 10%; text-align: center;' class='forumheader'>ID</td>
	         <td style='width: 30%;' class='forumheader'>".DOWLAN_12."</td>
	         <td style='width: 30%;' class='forumheader'>".DOWLAN_136."</td>
	         <td style='width: 30%; text-align: center;' class='forumheader'>".LAN_OPTIONS."</td>
	         </tr>
	         ";
	
	         $mirrorList = $sql -> db_getList();
	
	         foreach($mirrorList as $mirror)
	         {
	            extract($mirror);
	            $text .= "
	
	            <tr>
	            <td style='width: 10%; text-align: center;'>$mirror_id</td>
	            <td style='width: 30%;'>".$tp -> toHTML($mirror_name)."</td>
	            <td style='width: 30%;'>".($mirror_image ? "<img src='".e_FILE."downloadimages/".$mirror_image."' alt=''/>" : DOWLAN_28)."</td>
	            <td style='width: 30%; text-align: center;'>
	            <a href='".e_SELF."?mirror.edit.{$mirror_id}'>".ADMIN_EDIT_ICON."</a>
	            <input type='image' title='".LAN_DELETE."' name='delete[mirror_{$mirror_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".DOWLAN_137." [ID: $mirror_id ]')\"/>
	            </td>
	            </tr>
	            ";
	         }
	         $text .= "</table></form></div>";
	
	      }
	
	     // $ns -> tablerender(DOWLAN_138, $text);
		  echo $text;
	
	      $imagelist = $fl->get_files(e_FILE.'downloadimages/');
	
	      if ($subAction == "edit" && !defined("SUBMITTED"))
	      {
	         $sql -> db_Select("download_mirror", "*", "mirror_id='".intval($id)."' ");
	         $mirror = $sql -> db_Fetch();
	         extract($mirror);
	         $edit = TRUE;
	      }
	      else
	      {
	         unset($mirror_name, $mirror_url, $mirror_image, $mirror_location, $mirror_description);
	         $edit = FALSE;
	      }
	
	      $text = "<div>
	      <form method='post' action='".e_SELF."?".e_QUERY."' id='dataform'>\n
	      <table style='".ADMIN_WIDTH."' class='adminlist'>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_12."</td>
	      <td style='width: 70%;'>
	      <input class='tbox' type='text' name='mirror_name' size='60' value='{$mirror_name}' maxlength='200'/>
	      </td>
	      </tr>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_139."</td>
	      <td style='width: 70%;'>
	      <input class='tbox' type='text' name='mirror_url' size='70' value='{$mirror_url}' maxlength='255'/>
	      </td>
	      </tr>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_136."</td>
	      <td style='width: 70%;'>
	      <input class='tbox' type='text' id='mirror_image' name='mirror_image' size='60' value='{$mirror_image}' maxlength='200'/>
	
	
	      <br /><input class='button' type ='button' style='cursor:pointer' size='30' value='".DOWLAN_42."' onclick='expandit(this)'/>
	      <div id='imagefile' style='display:none;{head}'>";
	
	      $text .= DOWLAN_140."<br/>";
	      foreach($imagelist as $file)
	      {
	         $text .= "<a href=\"javascript:insertext('".$file['fname']."','mirror_image','imagefile')\"><img src='".e_FILE."downloadimages/".$file['fname']."' alt=''/></a> ";
	      }
	
	      $text .= "</div>
	      </td>
	      </tr>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_141."</td>
	      <td style='width: 70%;'>
	      <input class='tbox' type='text' name='mirror_location' size='60' value='$mirror_location' maxlength='200'/>
	      </td>
	      </tr>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_18."</td>
	      <td style='width: 70%;'>";
	      $text .= $frm->bbarea('mirror_description',$mirror_description);
	      $text .= "</td>
	      </tr>
	
	      <tr>
	      <td colspan='2' class='forumheader' style='text-align:center;'>
	      ".($edit ? "<input class='button' type='submit' name='submit_mirror' value='".DOWLAN_142."'/><input type='hidden' name='id' value='{$mirror_id}'/>" : "<input class='button' type='submit' name='submit_mirror' value='".DOWLAN_143."'/>")."
	      </td>
	      </tr>
	
	      </table>
	      </form>
	      </div>";
	
	      $caption = ($edit ? DOWLAN_142 : DOWLAN_143);
			echo $text;
	      // $ns -> tablerender($caption, $text);
	   }
	
		function submit_mirror()
	   	{
			global $admin_log;
			
			$tp = e107::getParser();
			$sql = e107::getDb();
			$mes = e107::getMessage();
			
	 		define("SUBMITTED", TRUE);
	 		
		      if (isset($_POST['mirror_name']) && isset($_POST['mirror_url']))
		      {
		         $name = $tp -> toDB($_POST['mirror_name']);
		         $url = $tp -> toDB($_POST['mirror_url']);
		         $location = $tp -> toDB($_POST['mirror_location']);
		         $description = $tp -> toDB($_POST['mirror_description']);
		
		         $logString = $name.'[!br!]'.$url.'[!br!]'.$location.'[!br!]'.$description;
		
		         if (isset($_POST['id']))
		         {
		            $mes->autoMessage($sql -> db_Update("download_mirror", "mirror_name='{$name}', mirror_url='{$url}', mirror_image='".$tp->toDB($_POST['mirror_image'])."', mirror_location='{$location}', mirror_description='{$description}' WHERE mirror_id=".intval($_POST['id'])), 'update', DOWLAN_133);
		            $admin_log->log_event('DOWNL_13','ID: '.intval($_POST['id']).'[!br!]'.$logString,E_LOG_INFORMATIVE,'');
		         }
		         else
		         {
		            $mes->autoMessage($sql -> db_Insert("download_mirror", "0, '{$name}', '{$url}', '".$tp->toDB($_POST['mirror_image'])."', '{$location}', '{$description}', 0"), 'insert', DOWLAN_134);
		            $admin_log->log_event('DOWNL_12',$logString,E_LOG_INFORMATIVE,'');
		         }
		      }
	   }

		function show_download_options()
		{
		   	global $pref, $ns;
		
				require_once(e_HANDLER."form_handler.php");
				$frm = new e_form(true); //enable inner tabindex counter
		
		   	$agree_flag = $pref['agree_flag'];
		   	$agree_text = $pref['agree_text'];
		      $c = $pref['download_php'] ? " checked = 'checked' " : "";
		      $sacc = (varset($pref['download_incinfo'],0) == '1') ? " checked = 'checked' " : "";
		      $order_options = array(
		         "download_id"        => "Id No.",
		         "download_datestamp" => LAN_DATE,
		         "download_requested" => ADLAN_24,
		         "download_name"      => DOWLAN_59,
		         "download_author"    => DOWLAN_15
		      );
		      $sort_options = array(
		         "ASC"    => DOWLAN_62,
		         "DESC"   => DOWLAN_63
		      );
		
		   	$text = "
				   <div class='admintabs' id='tab-container'>
					   <ul class='e-tabs e-hideme' id='download-option-tabs'>
						   <li id='tab-download1'><a href='#core-download-download1'>".LAN_DL_DOWNLOAD_OPT_GENERAL."</a></li>
						   <li id='tab-download2'><a href='#core-download-download2'>".LAN_DL_DOWNLOAD_OPT_BROKEN."</a></li>
						   <li id='tab-download3'><a href='#core-download-download3'>".LAN_DL_DOWNLOAD_OPT_AGREE."</a></li>
						   <li id='tab-download4'><a href='#core-download-download4'>".LAN_DL_UPLOAD."</a></li>
					   </ul>
		
		        		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		   				<fieldset id='core-download-download1'>
		            	   <div>
		            		   <table style='".ADMIN_WIDTH."' class='adminlist'>
		            		      <colgroup>
		            		         <col style='width:30%'/>
		            		         <col style='width:70%'/>
		            		      </colgroup>
		            		      <tr>
		            		         <td>".LAN_DL_USE_PHP."</td>
		            		         <td>"
		            		            .$frm->checkbox('download_php', '1', $pref['download_php'])
		            		            .$frm->label(LAN_DL_USE_PHP_INFO, 'download_php', '1')
		            		         ."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".LAN_DL_SUBSUB_CAT."</td>
		            		         <td>"
		            		            .$frm->checkbox('download_subsub', '1', $pref['download_subsub'])
		            		            .$frm->label(LAN_DL_SUBSUB_CAT_INFO, 'download_subsub', '1')
		            		         ."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".LAN_DL_SUBSUB_COUNT."</td>
		            		         <td>"
		            		            .$frm->checkbox('download_incinfo', '1', $pref['download_incinfo'])
		            		            .$frm->label(LAN_DL_SUBSUB_COUNT_INFO, 'download_incinfo', '1')
		            		         ."</td>
		            		      </tr>
		            		      <tr>
		               		      <td>".DOWLAN_55."</td>
		            		         <td>".$frm->text('download_view', $pref['download_view'], '4', array('size'=>'4'))."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".DOWLAN_56."</td>
		            		         <td>".$frm->selectbox('download_order', $order_options, $pref['download_order'])."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".LAN_ORDER."</td>
		             		         <td>".$frm->selectbox('download_sort', $sort_options, $pref['download_sort'])."</td>
		            		      </tr>
		            		      <tr>
		               		      <td>".DOWLAN_160."</td>
		               		      <td>
		                  		      <select name='mirror_order' class='tbox'>".
		                  		         ($pref['mirror_order'] == "0" ? "<option value='0' selected='selected'>".DOWLAN_161."</option>" : "<option value='0'>".DOWLAN_161."</option>").
		                                 ($pref['mirror_order'] == "1" ? "<option value='1' selected='selected'>".DOWLAN_67."</option>" : "<option value='1'>".DOWLAN_67."</option>").
		                                 ($pref['mirror_order'] == "2" ? "<option value='2' selected='selected'>".DOWLAN_163."</option>" : "<option value='2'>".DOWLAN_12."</option>")."
		            		            </select>
		               		      </td>
		            		      </tr>
		            		      <tr>
		            		         <td>".DOWLAN_164."</td>
		            		         <td><input name='recent_download_days' class='tbox' value='".$pref['recent_download_days']."' size='3' maxlength='3'/>
		            		         </td>
		            		      </tr>
		            		   </table>
		            		</div>
				   		</fieldset>
		   				<fieldset id='core-download-download2'>
		            	   <div>
		            		   <table style='".ADMIN_WIDTH."' class='adminlist'>
		            		      <colgroup>
		            		         <col style='width:30%'/>
		            		         <col style='width:70%'/>
		            		      </colgroup>
		            		      <tr>
		               		      <td>".DOWLAN_151."</td>
		               		      <td>". r_userclass("download_reportbroken", $pref['download_reportbroken'])."</td>
		            		      </tr>
		            		      <tr>
		               		      <td>".DOWLAN_150."</td>
		               		      <td>". ($pref['download_email'] ? "<input type='checkbox' name='download_email' value='1' checked='checked'/>" : "<input type='checkbox' name='download_email' value='1'/>")."</td>
		            		      </tr>
		            		   </table>
		            		</div>
				   		</fieldset>
		   				<fieldset id='core-download-download3'>
		            	   <div>
		            		   <table style='".ADMIN_WIDTH."' class='adminlist'>
		            		      <colgroup>
		            		         <col style='width:30%'/>
		            		         <col style='width:70%'/>
		            		      </colgroup>
		            		      <tr>
		               		      <td>".DOWLAN_100."</td>
		               		      <td>". ($agree_flag ? "<input type='checkbox' name='agree_flag' value='1' checked='checked'/>" : "<input type='checkbox' name='agree_flag' value='1'/>")."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".DOWLAN_101."</td>
		               	   	   <td>".$frm->bbarea('agree_text',$agree_text)."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".DOWLAN_146."</td>
		            		         <td>".$frm->bbarea('download_denied',$pref['download_denied'])."</td>
		            		      </tr>
		            		   </table>
		            		</div>
				   		</fieldset>
		   				<fieldset id='core-download-download4'>
		            	   <div>
		            		   <table style='".ADMIN_WIDTH."' class='adminlist'>
		            		      <colgroup>
		            		         <col style='width:30%'/>
		            		         <col style='width:70%'/>
		            		      </colgroup>
		            		      <tr>
		            		         <td>".DOWLAN_XXX."</td>
		            		         <td>//TODO</td>
		            		      </tr>
		            		   </table>
		            		</div>
				   		</fieldset>
						   <div class='buttons-bar center'>
		                  <input class='button' type='submit' name='updatedownlaodoptions' value='".DOWLAN_64."'/>
		               </div>
		              </form>
		           </div>
		      ";
		     // $ns->tablerender(LAN_DL_OPTIONS, $text);
		      echo $text;
		   }





		function downloadLog($aText, &$dlInfo, &$mirrorInfo=NULL)
		{
			global $admin_log;
			$logString = DOWLAN_9;
			foreach ($dlInfo as $k => $v)
			{
				$logString .= '[!br!]'.$k.'=>'.$v;
			}
			if ($mirrorInfo != NULL)
			{
				foreach ($mirrorInfo as $k => $v)
				{
					$logString .= '[!br!]'.$k.'=>'.$v;
				}
			}
			$admin_log->log_event($aText,$logString,E_LOG_INFORMATIVE,'');
	   }





		function move_file($oldname,$newname)
		{
		
			$mes = e107::getMessage();
			
			if (file_exists($newname))
		    {
		    	return TRUE;
		    }
		
			if (!file_exists($oldname) || is_dir($oldname))
			{
				$mes->addError(DOWLAN_68 . " : ".$oldname);
		        return FALSE;
			}
		
			$directory = dirname($newname);
			if (is_writable($directory))
			{
		         if (!rename($oldname,$newname))
		         {
		            $mes->addError(DOWLAN_152." ".$oldname ." -> ".$newname);
		            return FALSE;
		         }
		         else
		         {
		            return TRUE;
		         }
			}
			else
			{
				$mes->addError($directory ." ".LAN_NOTWRITABLE);
				return FALSE;
			}
	   }









		// Given the string which is stored in the DB, turns it into an array of mirror entries
	   // If $byID is true, the array index is the mirror ID. Otherwise its a simple array
	   function makeMirrorArray($source, $byID = FALSE)
	   {
	      $ret = array();
	      if ($source)
	      {
	         $mirrorTArray = explode(chr(1), $source);
	
	         $count = 0;
	         foreach($mirrorTArray as $mirror)
	         {
	            if ($mirror)
	            {
	               list($mid, $murl, $mreq, $msize) = explode(",", $mirror);
	               $ret[$byID ? $mid : $count] = array('id' => $mid, 'url' => $murl, 'requests' => $mreq, 'filesize' => $msize);
	               $count++;
	            }
	         }
	      }
	      return $ret;
	   }
	   
	   
	   
}

class download_main_admin_form_ui extends e_admin_form_ui
{
	
	function download_category($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function.
	{
		if($mode == 'read')
		{
			return $curVal.' (custom!)';
		}

		if($mode == 'batch') // Custom Batch List for release_type
		{
			return array('theme'=>"Theme","plugin"=>'Plugin');
		}

		if($mode == 'filter') // Custom Filter List for release_type
		{
			return array('theme'=>"Theme","plugin"=>'Plugin');
		}

		$types = array("theme","plugin");
		$text = "<select class='tbox' name='release_type' >";
		foreach($types as $val)
		{
			$selected = ($curVal == $val) ? "selected='selected'" : "";
			$text .= "<option value='{$val}' {$selected}>".$val."</option>\n";
		}
		$text .= "</select>";
		return $text;
	}
	
	
	function download_active($curVal,$mode)
	{
		$download_status[0] = DOWLAN_122; // Inactive; 
      	$download_status[1] = DOWLAN_123; // Active
      	$download_status[2] = DOWLAN_124;
		
		if($mode == 'read')
		{
			$status = array(ADMIN_FALSE_ICON,ADMIN_TRUE_ICON,ADMIN_WARNING_ICON);		
			return $status[$curVal];
		}

		if($mode == 'batch' || $mode == 'filter') // Custom Batch List for download_active
		{
			return $download_status;
		}
		 
		return "&nbsp;";
	}
	
	
	// Filter List for 'Issues' 
	function issue($curVal,$mode)
	{	
		if($mode == 'filter') 
		{
			return array(
				'duplicates'	=> DOWLAN_166,
				'orphans'		=> DOWLAN_167, // TODO
				'missing'		=> DOWLAN_168,
				'nocategory' 	=> DOWLAN_178,
				'filesize'		=> DOWLAN_66,
				'log'			=> DOWLAN_171
			);
			
		}
		 
		return "&nbsp;";
	}
	
	
	function download_mirror_type($curVal,$mode)
	{
		switch ($curVal)
		{
       		case 1:
         	return DOWLAN_196;
        	break;
         	default:
  			// return DOWLAN_197;
  		}
	}
}
