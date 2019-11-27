<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Download Plugin Administration UI
 *
*/

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
			'controller' 	=> 'download_main_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'download_main_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array (
			'controller' 	=> 'download_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'download_cat_form_ui',
			'uipath' 		=> null
		),
		'mirror'	=> array(
			'controller' 	=> 'download_mirror_ui',
			'path' 			=> null,
			'ui' 			=> 'download_mirror_form_ui',
			'uipath' 		=> null
		),
		'broken'	=> array(
			'controller' 	=> 'download_broken_ui',
			'path' 			=> null,
			'ui' 			=> 'download_broken_form_ui',
			'uipath' 		=> null
		),	
	);

	/* Both are optional
	protected $defaultMode = null;
	protected $defaultAction = null;
	*/

	/**
	 * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}release/admin_config.php', 'perm' => '0']);
	 * Additionally, any valid e107::getNav()->admin() key-value pair could be added to the above array
	 * @var array
	 */
	protected $adminMenu = array(
		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create' 		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		
		'other0' 		=> array('divider'=> true),
		
		'cat/list'			=> array('caption'=> LAN_CATEGORIES, 'perm'=>'P'),
		'cat/create' 		=> array('caption'=> LAN_CREATE_CATEGORY, 'perm' => 'Q'),
		
		'other1' 		=> array('divider'=> true),
		
		'mirror/list'		=> array('caption'=> DOWLAN_128, 'perm' => 'P'),
		'mirror/create'		=> array('caption'=> DOWLAN_143, 'perm' => 'P'),
		
		'other2' 		=> array('divider'=> true),

		'broken/list' 		=> array('caption'=> LAN_DL_BROKENDOWNLOADSREPORTS, 'perm' => 'P'),

		'other3' 		=> array('divider'=> true),
			
		'main/settings' 	=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
	//	'main/maint' 		=> array('caption'=> DOWLAN_165, 'perm' => 'P'),
		'main/limits'		=> array('caption'=> DOWLAN_112, 'perm' => 'P'),
	
	//	'main/mirror'		=> array('caption'=> DOWLAN_128, 'perm' => 'P')
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
	e107::getNav()->admin(DOWLAN_32, $action, $var);

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
	protected $menuTitle = LAN_PLUGIN_DOWNLOAD_NAME;
}


class download_cat_ui extends e_admin_ui
{ 	 	 
		protected $pluginTitle	    = LAN_PLUGIN_DOWNLOAD_NAME;
		protected $pluginName	    = 'download';
		protected $eventName        = 'download-category';
		protected $table 		    = "download_category";
		protected $pid			    = "download_category_id";
		protected $perPage 		    = 0; //no limit

		protected $batchCopy		= true;

		// initiate as a parent/child tree.
		protected $sortField		= 'download_category_order';
		protected $sortParent       = 'download_category_parent';
		protected $treePrefix       = 'download_category_name';
	//	protected $orderStep		= // automatic
	//	protected $listOrder		= // automatic

		//legacy URL scheme
		protected $url         		= array('route'=>'download/list/category', 'vars' => array('id' => 'download_category_id', 'name' => 'download_category_sef'), 'name' => 'download_category_name', 'description' => ''); // 'link' only needed if profile not provided.

	 	 	
		protected $fields = array(
			'checkboxes'						=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'download_category_icon' 			=> array('title'=> LAN_ICON,		'type' => 'method',			'width' => '5%', 'thclass' => 'center','class'=>'center','writeParms'=>'glyphs=1' ),
			'download_category_id'				=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readParms'=>'link=sef&target=blank'),
         	'download_category_name' 			=> array('title'=> LAN_TITLE,		'type' => 'text',		'data'=>'str',	'inline' => true, 'width' => 'auto', 'thclass' => 'left', 'writeParms'=>'size=xxlarge'),
       		'download_category_sef' 			=> array('title'=> LAN_SEFURL,		'type' => 'text',		'data'=>'str',	'batch'=>true, 'inline' => true,	'width' => 'auto', 'thclass' => 'left', 'writeParms'=>'sef=download_category_name&size=xxlarge'),
         
	     	'download_category_description' 	=> array('title'=> LAN_DESCRIPTION,	'type' => 'bbarea',		'data'=>'str',	'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'download_category_parent' 			=> array('title'=> LAN_PARENT,		'type' => 'method',			'width' => '5%', 'batch' => TRUE, 'filter'=>TRUE),		
			'download_category_class' 			=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',		'inline' => true, 'width' => 'auto', 'data' => 'int', 'batch' => TRUE, 'filter'=>TRUE),
			'download_category_order' 			=> array('title'=> LAN_ORDER,		'type' => 'number',	'nolist'=>true, 'data'=>'int',		'width' => '5%', 'thclass' => 'right', 'class'=> 'right' ),
			'options' 							=> array('title'=> LAN_OPTIONS,		'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center', 'sort'=>1)
		);	

		protected $fieldpref = array('download_category_icon', 'download_category_id', 'download_category_name', 'download_category_sef', 'download_category_class', 'download_category_order');

	protected $downloadCats = array();

	function init()
	{
		if(deftrue('e_DEBUG'))
		{
			$this->fields['download_category_order']['nolist'] = false;
		}

		$this->setDownloadCategoryTree();

	}


	private function setDownloadCategoryTree()
	{


		$sql = e107::getDb();
		$qry = $this->getParentChildQry(true);
		$sql->gen($qry);

		$this->downloadCats[0] = LAN_NONE;

		while($row = $sql->fetch())
		{
			$num = $row['_depth'] - 1;
			$id = $row['download_category_id'];
			$this->downloadCats[$id] = str_repeat("&nbsp;&nbsp;",$num).$row['download_category_name'];
		}

		if($this->getAction() === 'edit') // make sure parent is not the same as ID.
		{
			$r = $this->getId();
			unset($this->downloadCats[$r]);
		}

	}



	function getDownloadCategoryTree($id = false)
	{

		if($id)
		{
			return $this->downloadCats[$id];
		}
		
		return $this->downloadCats;
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
				return $this->select('download_category_parent', $controller->getDownloadCategoryTree(), $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return $controller->getDownloadCategoryTree();
			break;
		}
	}




	public function download_category_icon($curVal,$mode)
	{

		if(!empty($curVal) && strpos($curVal, chr(1)))
		{
			list($curVal,$tmp) = explode(chr(1),$curVal);
		}

		switch($mode)
		{
			case 'read':
				return e107::getParser()->toIcon($curVal, array('legacy'=>'{e_IMAGE}icons/'));
			break;

			case 'write':
				return $this->iconpicker('download_category_icon', $curVal,null,array('glyphs'=>true, 'legacyPath'=>'{e_IMAGE}icons/'));
			break;

			case 'filter':
			case 'batch':
				return null;
			break;
		}
	}
}







class download_main_admin_ui extends e_admin_ui
{
		// required
		protected $pluginTitle = LAN_PLUGIN_DOWNLOAD_NAME;
		protected $pluginName = 'download';
		protected $eventName = 'download';
		protected $table = "download"; // DB Table, table alias is supported. Example: 'r.release'
		protected $listQry = "SELECT m.*, c.download_category_sef, u.user_id,u.user_name FROM #download AS m
			lEFT JOIN #download_category AS c on m.download_category = c.download_category_id LEFT JOIN #user AS u ON m.download_author = u.user_id "; // without any Order or Limit.
		
		//required - default column user prefs
		protected $fieldpref = array('checkboxes', 'download_image', 'download_id', 'download_datestamp', 'download_category', 'download_name', 'download_active', 'download_class', 'fb_order', 'options');
	
		// Security modes
		protected $security_options = array(
			'none' => LAN_DL_SECURITY_MODE_NONE,
			'nginx-secure_link_md5' => LAN_DL_SECURITY_MODE_NGINX_SECURELINKMD5
		);

		// optional - required only in case of e.g. tables JOIN. This also could be done with custom model (set it in init())
		//protected $editQry = "SELECT * FROM #release WHERE release_id = {ID}";

		// required - if no custom model is set in init() (primary id)
		protected $pid = "download_id";
		
		// optional
		protected $perPage = 10;

		// default - true - TODO - move to displaySettings
		protected $batchDelete = true;

		/** @deprecated see writeParms() on download_id below. */
	//	protected $url         		= array('route'=>'download/view/item', 'vars' => array('id' => 'download_id', 'name' => 'download_sef'), 'name' => 'download_name', 'description' => ''); // 'link' only needed if profile not provided.


	
    	protected  $fields = array(
			'checkboxes'				=> array('title'=> '', 					'type' => null,			'data' => null,			'width'=>'5%', 		'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect'),
			'download_id'				=> array('title'=> LAN_ID, 				'type' => 'text',		'data' => 'int',		'width'=>'5%',		'thclass' => '',	'forced'=> TRUE, 'readParms'=>'url=item&target=blank', 'primary'=>TRUE/*, 'noedit'=>TRUE*/), //Primary ID is not editable
            'download_name' 			=> array('title'=> LAN_TITLE, 			'type' => 'text', 		'data' => 'str',		'inline'=>true, 'width' => 'auto',	'thclass' => ''),		
            'download_url'	   			=> array('title'=> DOWLAN_13, 			'type' => 'url', 	'data' => 'str',		'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
		    'download_sef'	   			=> array('title'=> LAN_SEFURL, 			'type' => 'text', 	'inline'=>true, 'data' => 'str',		'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE, 'writeParms'=>'sef=download_name'),
		  	'download_keywords'	    	=> array('title'=> LAN_KEYWORDS, 		'type' => 'tags', 	'inline'=>true, 'data' => 'str',		'width'=>'auto',	'thclass' => ''),
		
			'download_author' 			=> array('title'=> LAN_AUTHOR,			'type' => 'user', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),
         	'download_author_email' 	=> array('title'=> DOWLAN_16, 			'type' => 'email', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),  
         	'download_author_website' 	=> array('title'=> DOWLAN_17, 			'type' => 'url', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),
            'download_description' 		=> array('title'=> LAN_DESCRIPTION,		'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	
		 	'download_filesize' 		=> array('title'=> DOWLAN_66,			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'right', 'class' => 'right'),			
		 	'download_requested' 		=> array('title'=> DOWLAN_29, 			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'right', 'class' => 'right'),
			'download_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',	'width' => 'auto',	'inline'=>true, 'batch' => TRUE, 'filter'=>TRUE),		
			'download_active'			=> array('title'=> DOWLAN_21,			'type' => 'method', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center', 'class' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			'download_datestamp' 		=> array('title'=> LAN_DATE, 			'type' => 'datestamp', 	'data' => 'int',		'width' => 'auto',	'thclass' => '', 'readParms' => 'long', 'writeParms' => ''),
			
			'download_thumb' 			=> array('title'=> DOWLAN_20,			'type' => 'image', 		'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60&legacyPath={e_FILE}downloadthumbs', 'writeParms' => 'media=download_image', 'readonly'=>TRUE ),
			'download_image' 			=> array('title'=> DOWLAN_19,			'type' => 'image', 		'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60&legacyPath={e_FILE}downloadimages', 'writeParms' => 'media=download_image', 'readonly'=>TRUE,	'batch' => FALSE, 'filter'=>FALSE),
			'download_comment'			=> array('title'=> DOWLAN_102,			'type' => 'boolean', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			
			'download_class' 			=> array('title'=> DOWLAN_113,			'type' => 'userclass',		'width' => 'auto', 'inline'=>true, 'data' => 'int','batch' => TRUE, 'filter'=>TRUE),		
			'download_visible' 			=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',	'inline'=>true,	'width' => 'auto', 'data' => 'int', 'batch' => TRUE, 'filter'=>TRUE),
			
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
         "download_id"              => array("title"=>LAN_ID,  "type"=>"", "width"=>"auto", "thclass"=>"", "forced"=>true),
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


			$this->action 		= $this->getMode(); // vartrue($_GET['mode']);
			$this->subAction 	= $this->getAction(); // vartrue($_GET['action']);
			$this->id			= $this->getId(); // vartrue($_GET['id']);
			
			$this->observe();
			
			
			$categories = array();
			if(e107::getDb()->select('download_category'))
			{
				//$categories[0] = LAN_SELECT;
				while ($row = e107::getDb()->fetch())
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
			if(vartrue($_GET['filter_options']))
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
			
            if ($sql->gen("SELECT * FROM `#download` ORDER BY download_id"))
            {
               while($row = $sql->fetch())
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
                  $filesize = (is_readable(e_DOWNLOAD.$row['download_url']) ? eHelper::parseMemorySize(filesize(e_DOWNLOAD.$file['fname'])) : DOWLAN_181);
                  $filets   = (is_readable(e_DOWNLOAD.$row['download_url']) ? e107::getDate()->convert_date(filectime(e_DOWNLOAD.$file['fname']), "long") : DOWLAN_181);
                  $text .= '<tr>';
                  $text .= '<td>'.$tp->toHTML($file['fname']).'</td>';
                  $text .= '<td>'.$filets.'</td>';
                  $text .= '<td>'.$filesize.'</td>';

               }
            }
		}

        /**
         * @inheritdoc
         */
        public function afterDelete($deleted_data, $id, $deleted_check)
        {
            if($deleted_check)
            {
                $sql = e107::getDb('mmcleanup');
                if(strpos($deleted_data['download_url'], '{e_MEDIA_') === 0 && $sql->delete('core_media', "media_url='{$deleted_data['download_url']}'"))
                {
                    $mediaFile = e107::getParser()->replaceConstants($deleted_data['download_url']);
                    @unlink($mediaFile);
                    e107::getMessage()->addSuccess('Associated media record successfully erased');
                }
                if(strpos($deleted_data['download_image'], '{e_MEDIA_') === 0 && $sql->delete('core_media', "media_url='{$deleted_data['download_image']}'"))
                {
                    $mediaImage = e107::getParser()->replaceConstants($deleted_data['download_image']);
                    e107::getMessage()->addSuccess('Associated media image successfully erased');
                    @unlink($mediaImage);
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
			$this->showLimits();
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
		


		function showLimits()
		{
			$sql = e107::getDb();
			$ns = e107::getRender();
			$tp = e107::getParser();
			$pref = e107::getPref();
			
			//global $pref;
			
			if ($sql->select('userclass_classes','userclass_id, userclass_name'))
			{
				$classList = $sql->db_getList();
			}
			if ($sql->select("generic", "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as limit_bw_num, gen_ip as limit_bw_days, gen_intdata as limit_count_num, gen_chardata as limit_count_days", "gen_type = 'download_limit'"))
			{
				while($row = $sql->fetch())
				{
					$limitList[$row['limit_classnum']] = $row;
				}
			}
			$txt = "
				<form method='post' action='".e_SELF."?".e_QUERY."'>
				<table class='table adminform'>
				<tr>
					<td colspan='4' style='text-align:left'>
				";
				if(vartrue($pref['download_limits']) == 1)
				{
					$chk = " checked = 'checked'";
				}
				else
				{
					$chk = "";
				}
		
				$txt .= "
					<input type='checkbox' name='download_limits' value='on'{$chk}/> ".DOWLAN_125."
					</td>
				</tr>
				<tr>
					<th class='fcaption'>".LAN_ID."</th>
					<th class='fcaption'>".DOWLAN_113."</th>
					<th class='fcaption'>".DOWLAN_107."</th>
					<th class='fcaption'>".DOWLAN_108."</th>
				</tr>
			";
		
			if(is_array(vartrue($limitList)))
			{
				foreach($limitList as $row)
				{
					$txt .= "
					<tr>
					<td>".$row['limit_id']."</td>
					<td>".r_userclass_name($row['limit_classnum'])."</td>
					<td class='form-inline'>
						<input type='text' class='form-control' size='5' name='count_num[{$row['limit_id']}]' value='".($row['limit_count_num'] ? $row['limit_count_num'] : "")."'/> ".DOWLAN_109."
						<input type='text' class='form-control' size='5' name='count_days[{$row['limit_id']}]' value='".($row['limit_count_days'] ? $row['limit_count_days'] : "")."'/> ".DOWLAN_110."
					</td>
					<td class='form-inline'>
						<input type='text' class='form-control' size='5' name='bw_num[{$row['limit_id']}]' value='".($row['limit_bw_num'] ? $row['limit_bw_num'] : "")."'/> ".DOWLAN_111." ".DOWLAN_109."
						<input type='text' class='form-control' size='5' name='bw_days[{$row['limit_id']}]' value='".($row['limit_bw_days'] ? $row['limit_bw_days'] : "")."'/> ".DOWLAN_110."
					</td>
					</tr>
					";
				}
			}
			$txt .= "
			</table>
			<div class='buttons-bar center'>
			<input type='submit' class='btn btn-default btn-secondary button' name='updatelimits' value='".DOWLAN_115."'/>
			</div>
			
			<table class='table adminlist'>
			<tr>
			<td colspan='4'><br/><br/></td>
			</tr>
			<tr>
			<td colspan='2'>".r_userclass("newlimit_class", 0, "off", "guest, member, admin, classes, language")."</td>
			<td class='form-inline'>
				<input type='text' class='form-control' size='5' name='new_count_num' value=''/> ".DOWLAN_109."
				<input type='text' class='form-control' size='5' name='new_count_days' value=''/> ".DOWLAN_110."
			</td>
			<td class='form-inline'>
				<input type='text' class='form-control' size='5' name='new_bw_num' value=''/> ".DOWLAN_111." ".DOWLAN_109."
				<input type='text' class='form-control' size='5' name='new_bw_days' value=''/> ".DOWLAN_110."
			</td>
			</tr>
			<tr>
			
			";
		
			$txt .= "</table>
			<div class='buttons-bar center'>
			<input type='submit' class='btn btn-default btn-secondary button' name='addlimit' value='".DOWLAN_114."'/>
			</div></form>";
			echo $txt;
		
		//	$ns->tablerender(DOWLAN_112, $txt);
			// require_once(e_ADMIN.'footer.php');
			// exit;
		}


		
		function showMaint() //XXX Deprecated. 
		{
			$mes = e107::getMessage();
			$mes->addInfo("Deprecated Area - please use filter instead under 'Manage' ");
			
			global $pref;
			$ns = e107::getRender();
			$sql = e107::getDb();
			$frm = e107::getForm();
			$tp = e107::getParser();
			
		   if (isset($_POST['dl_maint'])) {
		      switch ($_POST['dl_maint'])
		      {
		         case 'duplicates':
		         {
		            $title = DOWLAN_166;
		            $query = 'SELECT GROUP_CONCAT(d.download_id SEPARATOR ",") as gc, d.download_id, d.download_name, d.download_url, dc.download_category_name
		                      FROM #download as d
		                      LEFT JOIN #download_category AS dc ON dc.download_category_id=d.download_category
		                      GROUP BY d.download_url
		                      HAVING COUNT(d.download_id) > 1
		               ';
		            $text = "";
		            $count = $sql->gen($query);
		            $foundSome = false;
		            if ($count) {
		               $currentURL = "";
		               while($row = $sql->fetch()) {
		                  if (!$foundSome) {
		   		          //  $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
		                     $text .= '<form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
		                     			<table class="table adminform">';
		                     $text .= '<tr>';
		                     $text .= '<th>'.DOWLAN_13.'</th>';
		                     $text .= '<th>'.LAN_ID.'</th>';
		                     $text .= '<th>'.DOWLAN_27.'</th>';
		                     $text .= '<th>'.DOWLAN_11.'</th>';
		                     $text .= '<th>'.LAN_OPTIONS.'</th>';
		                     $text .= '</tr>';
		                     $foundSome = true;
		                  }
		                  $query = "SELECT d.*, dc.* FROM `#download` AS d
		                     LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category
		                     WHERE download_id IN (".$row['gc'].")
		                     ORDER BY download_id ASC";
		                  $count = $sql2->gen($query);
		                  while($row = $sql2->fetch()) {
		                     $text .= '<tr>';
		                     if ($currentURL != $row['download_url']) {
		                        $text .= '<td>'.$tp->toHTML($row['download_url']).'</td>';
		                        $currentURL = $row['download_url'];
		                     } else {
		                        $text .= '<td>*</td>';
		                     }
		                     $text .= '<td>'.$row['download_id'].'</td>';
		                     $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
		                     $text .= '<td>'.$tp->toHTML($row['download_category_name']).'</td>';
		                     $text .= '<td>
		                                 <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.duplicates">'.ADMIN_EDIT_ICON.'</a>
		   				                  <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
		   				               </td>';
		                     $text .= '</tr>';
		                  }
		               }
		            }
		            if ($foundSome) {
		               $text .= '</table></form>';
		            }
		            else
		            {
						e107::getMessage()->addInfo(DOWLAN_172);
		            }
		            break;
		         }
		         case 'orphans':
		         {
		            $title = DOWLAN_167;
		            $text = "";
		            require_once(e_HANDLER."file_class.php");
		            $efile = new e_file();
		            $files = $efile->get_files(e_DOWNLOAD);
		            $foundSome = false;
		            foreach($files as $file) {
		               if (0 == $sql->db_Count('download', '(*)', " WHERE download_url='".$file['fname']."'")) {
		                  if (!$foundSome) {
		   		           // $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
		                     $text .= '<form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
		                     <table class="table adminform">';
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
		   //TODO               $text .= '<td>
		   //TODO                           <a href="'.e_SELF.'?create.add.'. urlencode($file["fname"]).'">'.E_16_CREATE.'</a>
		   //TODO					            <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$file["fname"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_173.' [ '.$file["fname"].' ]').'") \'/>
		   //TODO					         </td>';
		                  $text .= '</tr>';
		               }
		            }
		            if ($foundSome) {
		               $text .= '</table></form>';
		            }
		            else
		            {
		            	e107::getMessage()->addInfo(DOWLAN_174);
		  
		            }
		            break;
		         }
		         case 'missing':
		         {
		            $title = DOWLAN_168;
		            $text = "";
		            $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category";
		            $count = $sql->gen($query);
		            $foundSome = false;
		            if ($count) {
		               while($row = $sql->fetch()) {
		                  if (!is_readable(e_DOWNLOAD.$row['download_url'])) {
		                     if (!$foundSome)
							 {
		   		              // $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
								 
		                        $text .= '<form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
		                        		<table class="adminlist">';
		                        $text .= '<tr>';
		                        $text .= '<th>'.LAN_ID.'</th>';
		                        $text .= '<th>'.DOWLAN_27.'</th>';
		                        $text .= '<th>'.DOWLAN_11.'</th>';
		                        $text .= '<th>'.DOWLAN_13.'</th>';
		                        $text .= '<th>'.LAN_OPTIONS.'</th>';
		                        $text .= '</tr>';
		                        $foundSome = true;
		                     }
		                     $text .= '<tr>';
		                     $text .= '<td>'.$row['download_id'].'</td>';
		                     $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$tp->toHTML($row['download_name']).'</a></td>';
		                     $text .= '<td>'.$tp->toHTML($row['download_category_name']).'</td>';
		                     $text .= '<td>'.$tp->toHTML($row['download_url']).'</td>';
		                     $text .= '<td>
		                                 <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.missing">'.ADMIN_EDIT_ICON.'</a>
		   					               <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
		   					            </td>';
		                     $text .= '</tr>';
		                  }
		               }
		            }
		            if ($foundSome) {
		               $text .= '</table></form>';
		            }
		            else
		            {
		            	e107::getMessage()->addInfo(DOWLAN_172);
		              //  $text = DOWLAN_172;
		            }
		            break;
		         }
		         case 'inactive':
		         {
		            $title = DOWLAN_169;
		            $text = "";
		            $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category WHERE download_active=0";
		            $count = $sql->gen($query);
		            $foundSome = false;
		            if ($count) {
		               while($row = $sql->fetch()) {
		                  if (!$foundSome)
		                  {
		   		           // $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
		                     $text .= '<form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
		                     		<table class="table adminform">';
		                     $text .= '<tr>';
		                     $text .= '<th>'.LAN_ID.'</th>';
		                     $text .= '<th>'.DOWLAN_27.'</th>';
		                     $text .= '<th>'.DOWLAN_11.'</th>';
		                     $text .= '<th>'.DOWLAN_13.'</th>';
		                     $text .= '<th>'.LAN_OPTIONS.'</th>';
		                     $text .= '</tr>';
		                     $foundSome = true;
		                  }
		                  
		                  $text .= '<tr>';
		                  $text .= '<td>'.$row['download_id'].'</td>';
		                  $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
		                  $text .= '<td>'.$e107->tp->toHTML($row['download_category_name']).'</td>';
		                  if (strlen($row['download_url']) > 0) {
		                     $text .= '<td>'.$row['download_url'].'</td>';
		                  } else {
		   					   $mirrorArray = download::makeMirrorArray($row['download_mirror'], TRUE);
		                     $text .= '<td>';
		                     foreach($mirrorArray as $mirror) {
		                        $text .= $mirror['url'].'<br/>';
		                     }
		                     $text .= '</td>';
		                  }
		                  $text .= '<td>
		                              <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.inactive">'.ADMIN_EDIT_ICON.'</a>
		   				               <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
		   				            </td>';
		                  $text .= '</tr>';
		               }
		            }
		            if ($foundSome) {
		               $text .= '</table></form>';
		            }
		            else
		            {
		            	e107::getMessage()->addInfo(DOWLAN_172);
		               // $text = DOWLAN_172;
		            }
		            break;
		         }
		         case 'nocategory':
		         {
		            $title = DOWLAN_178;
		            $text = "";
		            $query = "SELECT * FROM `#download` WHERE download_category=0";
		            $count = $sql->gen($query);
		            $foundSome = false;
		            if ($count) {
		               while($row = $sql->fetch()) {
		                  if (!$foundSome) {
		   		          //  $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
		                     $text .= '
		                     <form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
		                     <table class="table adminlist">';
		                     $text .= '<tr>';
		                     $text .= '<th>'.LAN_ID.'</th>';
		                     $text .= '<th>'.DOWLAN_27.'</th>';
		                     $text .= '<th>'.DOWLAN_13.'</th>';
		                     $text .= '<th>'.LAN_OPTIONS.'</th>';
		                     $text .= '</tr>';
		                     $foundSome = true;
		                  }
		                  $text .= '<tr>';
		                  $text .= '<td>'.$row['download_id'].'</td>';
		                  $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
		                  if (strlen($row['download_url']) > 0) {
		                     $text .= '<td>'.$e107->tp->toHTML($row['download_url']).'</td>';
		                  } else {
		   					   $mirrorArray = download::makeMirrorArray($row['download_mirror'], TRUE);
		                     $text .= '<td>';
		                     foreach($mirrorArray as $mirror) {
		                        $text .= $mirror['url'].'<br/>';
		                     }
		                     $text .= '</td>';
		                  }
		                  $text .= '<td>
		                              <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.nocategory">'.ADMIN_EDIT_ICON.'</a>
		   				               <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
		   				            </td>';
		                  $text .= '</tr>';
		               }
		            }
		            if ($foundSome) {
		               $text .= '</table></form>';
		            }
		            else
		            {
		            	e107::getMessage()->addInfo(DOWLAN_172);
		              // $text = DOWLAN_172;
		            }
		            break;
		         }
		         case 'filesize':
		         {
		            $title = DOWLAN_66;
		            $text = "";
		            $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category WHERE d.download_url<>''";
		            $count = $sql->gen($query);
		            $foundSome = false;
		            if ($count) {
		               while($row = $sql->fetch()) {
		                  if (is_readable(e_DOWNLOAD.$row['download_url'])) {
		                     $filesize = filesize(e_DOWNLOAD.$row['download_url']);
		                     if ($filesize <> $row['download_filesize']) {
		                        if (!$foundSome) {
		   		                 // $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
		                           $text .= '<form method="post" action="'.e_SELF.'?'.e_QUERY.'" id="myform">
		                           		<table class="table adminlist">';
		                           $text .= '<tr>';
		                           $text .= '<th>'.LAN_ID.'</th>';
		                           $text .= '<th>'.DOWLAN_27.'</th>';
		                           $text .= '<th>'.DOWLAN_11.'</th>';
		                           $text .= '<th>'.DOWLAN_13.'</th>';
		                           $text .= '<th>'.DOWLAN_180.'</th>';
		                           $text .= '<th>'.LAN_OPTIONS.'</th>';
		                           $text .= '</tr>';
		                           $foundSome = true;
		                        }
		                        $text .= '<tr>';
		                        $text .= '<td>'.$row['download_id'].'</td>';
		                        $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
		                        $text .= '<td>'.$e107->tp->toHTML($row['download_category_name']).'</td>';
		                        $text .= '<td>'.$e107->tp->toHTML($row['download_url']).'</td>';
		                        $text .= '<td>'.$row['download_filesize'].' / ';
		                        $text .= $filesize;
		                        $text .= '</td>';
		                        $text .= '<td>
		                                    <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.filesize">'.ADMIN_EDIT_ICON.'</a>
		   					                  <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
		   					               </td>';
		                        $text .= '</tr>';
		                     }
		                  }
		               }
		            }
		            if ($foundSome) {
		               $text .= '</table></form>';
		            }
		            else
		            {
		            	e107::getMessage()->addInfo(DOWLAN_172);
		              // $text = DOWLAN_172;
		            }
		            break;
		         }
		         case 'log':
		         {
		            $text = "log - view manage download history log";
		            header('location: '.e_ADMIN.'admin_log.php?downlog');
		            exit();
		            break;
		         }
		      }
		   }
		   else {
		      $title = DOWLAN_193;
		      $text = DOWLAN_179;
		      $eform = new e_form();
		      $text = "
		      	<form method='post' action='".e_SELF."?".e_QUERY."' id='core-db-main-form'>
		      		<fieldset id='core-db-plugin-scan'>
		      		<legend class='e-hideme'>".DOWLAN_10."</legend>
		      			<table class='table adminform'>
		      			<colgroup span='2'>
		      				<col style='width: 40%'></col>
		      				<col style='width: 60%'></col>
		      			</colgroup>
		      			<tbody>
		      				<tr>
		      					<td>".DOWLAN_166."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'duplicates').$eform->label(DOWLAN_185, 'dl_maint', 'duplicates')."
		      					</td>
		      				</tr>
		      				<tr>
		      					<td>".DOWLAN_167."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'orphans').$eform->label(DOWLAN_186, 'dl_maint', 'orphans')."
		      					</td>
		      				</tr>
		      				<tr>
		      					<td>".DOWLAN_168."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'missing').$eform->label(DOWLAN_187, 'dl_maint', 'missing')."
		      					</td>
		      				</tr>
		      				<tr>
		      					<td>".DOWLAN_169."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'inactive').$eform->label(DOWLAN_188, 'dl_maint', 'inactive')."
		      					</td>
		      				</tr>
		      				<tr>
		      					<td>".DOWLAN_178."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'nocategory').$eform->label(DOWLAN_189, 'dl_maint', 'nocategory')."
		      					</td>
		      				</tr>
		      				<tr>
		      					<td>".DOWLAN_66."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'filesize').$eform->label(DOWLAN_190, 'dl_maint', 'filesize')."
		      					</td>
		      				</tr>
		      				<tr>
		      					<td>".DOWLAN_171."</td>
		      					<td>
		      						".$eform->radio('dl_maint', 'log').$eform->label(DOWLAN_191, 'dl_maint', 'log')."
		      					</td>
		      				</tr>
		
		      				</tbody>
		      			</table>
		      			<div class='buttons-bar center'>
		      				".$eform->admin_button('trigger_db_execute', DOWLAN_192, 'execute')."
		      			</div>
		      		</fieldset>
		      	</form>
		      	";
		   }
		   
		   echo $text;
		   // 	$ns->tablerender(DOWLAN_165.$title, $text);
		}


		
		
		function saveSettings()
		{
			global $admin_log,$pref;
					
			$tp = e107::getParser();

			$expected_params = array(
				'download_php', 'download_view', 'download_sort', 'download_order',
				'mirror_order', 'recent_download_days', 'agree_flag',
				'agree_text', 'download_denied', 'download_reportbroken',
				'download_security_mode', 'download_security_expression', 'download_security_link_expiry'
			);
			
			$temp = array();
			foreach($expected_params as $expected_param)
			{
				$temp[$expected_param] = $_POST[$expected_param];
			}

			$temp['download_subsub'] = $_POST['download_subsub'] ? '1' : '0';
			$temp['download_incinfo'] = $_POST['download_incinfo'] ? '1' : '0';

			if ($_POST['download_security_mode'] !== 'nginx-secure_link_md5')
			{
				unset($temp['download_security_mode']);
				unset($temp['download_security_expression']);
				unset($temp['download_security_link_expiry']);
				e107::getConfig('core')->removePref('download_security_mode');
				e107::getConfig('core')->removePref('download_security_expression');
				e107::getConfig('core')->removePref('download_security_link_expiry');
			}
			
			e107::getConfig('core')->setPref($temp)->save(false);

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
			$mes = e107::getMessage();
			
		//	print_a($this);
	
	      	global $e107, $cal, $rs, $ns, $file_array, $image_array, $thumb_array;
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
	/*      if ($sql->select("rbinary")) //TODO Remove me.
	      {
	         while ($row = $sql->fetch())
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
	
	      if (!$sql->select("download_category"))
	      {
	         //$ns->tablerender(ADLAN_24, "<div style='text-align:center'>".DOWLAN_5."</div>");
	         $mes->addInfo(DOWLAN_5); 
	         return;
	      }
	      $download_active = 1;
	      if ($_GET['action'] == "edit" && !$_POST['submit'])
	      {
	         if ($sql->select("download", "*", "download_id=".intval($_GET['id'])))
	         {
	            $row = $sql->fetch();
	            extract($row);
	
	            $mirrorArray = $this->makeMirrorArray($row['download_mirror']);
	         }
	      }
	
	      if ($subAction == "dlm" && !$_POST['submit'])
	      {
	         require_once(e_PLUGIN.'download/download_shortcodes.php');
	         if ($sql->select("upload", "*", "upload_id=".$id))
	         {
	            $row = $sql->fetch();
	
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
	     
	         <ul class='nav nav-tabs'>
	            <li class='active'><a data-toggle='tab' href='#download-create'>".DOWLAN_175."</a></li>
	            <li><a data-toggle='tab' href='#download-edit-external'>".DOWLAN_176."</a></li>
	            <li><a data-toggle='tab' href='#download-edit-mirror'>".DOWLAN_128."</a></li>
	         </ul>
	         <form method='post' action='".e_SELF."?".e_QUERY."' id='myform'>
	          <div class='tab-content'>
	          <div class='tab-pane active' id='download-create'>
	            
	               
	                  <table class='table adminform' style='margin:0'>
	                     <tr>
	                        <td>".DOWLAN_13."</td>
	                        <td style='width:80%'>
	                           <div>".DOWLAN_131."&nbsp;&nbsp;";
							  
	                   //       $text .= "<select name='download_url' class='form-control'>
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
	            </div>
	            <div class='tab-pane' id='download-edit-external'>
	               <table class='table adminform' style='margin:0'>
	                  <tr>
	                       <td>".DOWLAN_149."</td>
	                       <td style='width:80%;'>
	                          <input class='form-control input-xxlarge' type='text' name='download_url_external' size='90' value='{$download_url_external}' maxlength='255'/>
	                       </td>
	                    </tr>
	                    <tr>
	                       <td>".DOWLAN_66."</td>
	                       <td class='form-inline'>
	                          <input class='form-control' type='text' name='download_filesize_external' size='8' value='{$download_filesize}' maxlength='10'/>
	                       	 <select class='form-control' name='download_filesize_unit'>
						      <option value='B'{$b_sel}>".CORE_LAN_B."</option>
						      <option value='KB'{$kb_sel}>".CORE_LAN_KB."</option>
						      <option value='MB'>".CORE_LAN_MB."</option>
						      <option value='GB'>".CORE_LAN_GB."</option>
						      <option value='TB'>".CORE_LAN_TB."</option>
							  </select>
	                       </td>
	                  </tr>
	               </table>
	            </div>
	            <div  class='tab-pane' id='download-edit-mirror'>
	               <table class='table adminlist'>
	                  <tr>
	                     <td style='width:20%'><span title='".DOWLAN_129."' style='cursor:help'>".DOWLAN_128."</span></td>
	                     <td style='width:80%'>";
	
	      // See if any mirrors to display
	      if (!$sql -> select("download_mirror"))
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
	                           <select name='download_mirror_name[]' class='form-control'>
	                              <option value=''>&nbsp;</option>";
	
	            foreach ($mirrorList as $mirror)
	            {
	               extract($mirror);
	               $text .= "<option value='{$mirror_id}'".($mirror_id == $mirrorArray[($count-1)]['id'] ? " selected='selected'" : "").">{$mirror_name}</option>\n";
	            }
	
	            $text .= "</select>
	                           <input  class='form-control' type='text' name='download_mirror[]' style='width: 60%;' value=\"".$mirrorArray[($count-1)]['url']."\" maxlength='200'/>
	                           <input  class='form-control' type='text' name='download_mirror_size[]' style='width: 15%;' value=\"".$mirrorArray[($count-1)]['filesize']."\" maxlength='10'/>";
	            if (DOWNLOAD_DEBUG)
	            {
	               if ($id)
	               {
	                  $text .= '('.$mirrorArray[($count-1)]['requests'].')';
	               }
	               else
	               {
	               $text .= "<input  class='form-control' type='text' name='download_mirror_requests[]' style='width: 10%;' value=\"".$mirrorArray[($count-1)]['requests']."\" maxlength='10'/>";
	               }
	            }
	            $text .= "  </div>";
	         }
	         $text .="      </div>
	                        <input class='btn btn-default btn-secondary button' type='button' name='addoption' value='".DOWLAN_130."' onclick=\"duplicateHTML('mirror','mirrorsection')\"/>
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
	            </div>
	           </div>
	            <fieldset id='download-edit-therest'>
	               <table class='table adminform' >
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_11."</td>
	                     <td style='width:80%'>";
	                     
	                     
	      $text .= $download->getCategorySelectList($download_category);
	   // $text .= download::getCategorySelectList($download_category);
		
	      $text .= "     </td>
	                  </tr>
	                  <tr>
	                     <td >".DOWLAN_12."</td>
	                     <td style='width:80%'>
	                        <input class='form-control input-xxlarge' type='text' id='download-name' name='download_name' size='60' value=\"".$tp->toForm($download_name)."\" maxlength='200'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_15."</td>
	                     <td style='width:80%'>
	                        <input class='form-control' type='text' name='download_author' size='60' value='$download_author' maxlength='100'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_16."</td>
	                     <td style='width:80%'>
	                        <input class='form-control' type='text' name='download_author_email' size='60' value='$download_author_email' maxlength='100'/>
	                     </td>
	                  </tr>
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_17."</td>
	                     <td style='width:80%'>
	                        <input class='form-control' type='text' name='download_author_website' size='60' value='$download_author_website' maxlength='100'/>
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
	                     <td style='width:20%'>".DOWLAN_19."</td>
	                     <td style='width:80%'>";
	       /*
															$text = "<select name='download_image' class='form-control'>
										  <option value=''>&nbsp;</option>";
																foreach($image_array as $img)
					 {
						$fpath = str_replace(e_FILE."downloadimages/","",$img['path'].$img['fname']);
						  $sel = ($download_image == $fpath) ? " selected='selected'" : "";
						  $text .= "<option value='".$fpath."' $sel>".$fpath."</option>\n";
					 }
									 $text .= "     </select>";
										   */
		   
		$text .= $frm->imagepicker('download_image', $download_image,'',array('media'=>'download_image', 'legacyPath'=>'{e_FILE}downloadimages'));
		  
	      if ($subAction == "dlm" && $download_image)
	      {
	         $text .= "
	         <input type='hidden' name='move_image' value='1'/>\n";
	      }
	      $text .= "     </td>
	                  </tr>";
	                  
	                  
	                  
	      if(is_dir(e_FILE."downloadthumbs")) // Legacy 
	      {            
	                  
	           $text .= "
	                 <tr>
			            <td style='width:20%'>".DOWLAN_20."</td>
			           <td style='width:80%'>";
			     /*
												   $text .= "
											 <select name='download_thumb' class='form-control'>
												<option value=''>&nbsp;</option>";
						   foreach($thumb_array as $thm){
							  $tpath = str_replace(e_FILE."downloadthumbs/","",$thm['path'].$thm['fname']);
							  $sel = ($download_thumb == $tpath) ? " selected='selected'" : "";
							  $text .= "<option value='".$tpath."' $sel>".$tpath."</option>\n";
						   }
										 $text .= "        </select>";
						   */
				 
				 $text .= $frm->imagepicker('download_thumb', $download_thumb,'',array('media'=>'download_thumb', 'legacyPath'=>'{e_FILE}downloadthumbs'));
				 
				 
			    $text .= "
			                </td>
			              </tr>";
	      }   
	      
		  
		           
			$text .= "
	                  <tr>
	                     <td style='width:20%'>".LAN_DATESTAMP."</td>
	                     <td style='width:80%'>";
	      if (!$download_datestamp){
	           $download_datestamp = time();
	      }
	
			$text .= $frm->datepicker('download_datestamp', $download_datestamp, 'type=datetime');
			
	  //    $update_checked = ($_POST['update_datestamp']) ? "checked='checked'" : "";
	  //    $text .= "        &nbsp;&nbsp;<span><input type='checkbox' value='1' name='update_datestamp' $update_checked/>".DOWLAN_148."</span>";
		  
		  $text .= "
	                     </td>
	                  </tr>
	                  
	     			 <tr>
	                     <td >".LAN_SEFURL."</td>
	                     <td class='input-group' style='width:80%'>
	                        ".$frm->renderElement('download_sef',$download_sef, array('type'=>'text', 'writeParms'=>array('sef'=>'download_name','size'=>'xxlarge')))."
	                     </td>
	                  </tr> 
	                  
	                  
	                 <tr>
	                     <td >".LAN_KEYWORDS."</td>
	                     <td style='width:80%'>".$frm->tags('download_keywords',$download_keywords)."
	                    
	                     </td>
	                  </tr>  
	                  
					                
	                  
	                  <tr>
	                     <td style='width:20%'>".DOWLAN_21."</td>
	                     <td style='width:80%'>
	                        <select name='download_active' class='form-control input-xxlarge'>";
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
	                        <select name='move_file' class='form-control'>
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
	      $text .= $frm->renderHooks($data);
		  
	 
	
	      $text .= "
	      	   
	               </table>
	        <div class='buttonsbar center'>";
	                     
		
						 
	      if ($id && $subAction == "edit")
		  {
	         $text .= "<input class='btn btn-success' type='submit' name='submit_download' value='".DOWLAN_24."'/> ";
	      } else
	      {
	         $text .= "<input class='btn btn-success' type='submit' name='submit_download' value='".DOWLAN_25."'/>";
	      }
	
	      $text .= "
	                     </div>
	             
	            </fieldset>
	         </form>
	    
	        
	        ";
	     // $ns->tablerender(ADLAN_24, $text);
	     echo $text;
	   }

	function calc_filesize($size, $unit)
	{
		switch($unit)
		{
			case 'B' :
				return $size;
				break;
			case 'KB' :
				return $size * 1024;
				break;
			case 'MB' :
				return $size * 1024 * 1024;
				break;
			case 'GB' :
				return $size * 1024 * 1024 * 1024;
				break;
			case 'TB' :
				return $size * 1024 * 1024 * 1024 * 1024;
				break;
		}
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
	
			if (!empty($_POST['download_url_external']) && empty($_POST['download_url']) && !empty($_POST['download_filesize_unit']))
			{
				$dlInfo['download_url'] = $tp->toDB($_POST['download_url_external']);
			//	$filesize = intval($_POST['download_filesize_external']);
				$filesize = $this->calc_filesize($_POST['download_filesize_external'], $_POST['download_filesize_unit']);		
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
					elseif($dlInfo['download_url'][0] == '{')
					{
						$filesize = filesize($tp->replaceConstants($dlInfo['download_url']));
					}
		            else
		            {  	
		               $filesize = filesize(e_BASE.$DOWNLOADS_DIRECTORY.$dlInfo['download_url']);
		            }
				}
			}
	
	      if (!$filesize)
	      {
	         if ($sql->select("upload", "upload_filesize", "upload_file='{$dlInfo['download_url']}'"))
	         {
	            $row = $sql->fetch();
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
			$dlInfo['download_sef'] 				= vartrue($_POST['download_sef']) ? eHelper::secureSef($_POST['download_sef']) : eHelper::title2sef($_POST['download_name']);
			$dlInfo['download_keywords']				= $tp->toDB($_POST['download_keywords']);
			$dlInfo['download_author'] 				= $tp->toDB($_POST['download_author']);
			$dlInfo['download_author_email'] 		= $tp->toDB($_POST['download_author_email']);
			$dlInfo['download_author_website'] 		= $tp->toDB($_POST['download_author_website']);
			$dlInfo['download_category'] 			= intval($_POST['download_category']);
			$dlInfo['download_active']  			= intval($_POST['download_active']);
			$dlInfo['download_thumb']				= $tp->toDB($_POST['download_thumb']);
	      	$dlInfo['download_image']				= $tp->toDB($_POST['download_image']);
	      	$dlInfo['download_comment']				= $tp->toDB($_POST['download_comment']);
	      	$dlInfo['download_class']				= $tp->toDB($_POST['download_class']);
	      	$dlInfo['download_visible']				= $tp->toDB($_POST['download_visible']);
			$dlInfo['download_datestamp']			= intval($_POST['download_datestamp']);
			
	
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
	         if ($id && $sql->select('download','download_mirror', 'download_id = '.$id))      // Get existing download stats
	         {
	            if ($row = $sql->fetch())
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
	        
	   			$mes->add($hooks, E_MESSAGE_SUCCESS);
		
				$updateArray = array_merge($dlInfo,$dlMirrors);
				$updateArray['WHERE'] = 'download_id='.intval($id);
				
				$mes->addAuto($sql->db_Update('download',$updateArray), 'update', DOWLAN_2." (<a href='".e_PLUGIN."download/download.php?view.".$id."'>".$_POST['download_name']."</a>)");
	                
				$dlInfo['download_id'] = $id;
				$this->downloadLog('DOWNL_06',$dlInfo,$dlMirrors);
				$dlInfo['download_datestamp'] = $time;      // This is what 0.7 did, regardless of settings
				unset($dlInfo['download_class']);         // Also replicating 0.7
				$e_event->trigger('dlupdate', $dlInfo); // @deprecated 
				
				e107::getEvent()->trigger('admin_download_update',$dlInfo); 
	      }
	      else // Its a new entry. 
	      {
		         if ($download_id = $sql->insert('download',array_merge($dlInfo,$dlMirrors)))
		         {
		            // Process triggers before calling admin_update so trigger messages can be shown
		            $data = array('method'=>'create', 'table'=>'download', 'id'=>$download_id, 'plugin'=>'download', 'function'=>'create_download');
		            $hooks = $e107->e_event->triggerHook($data);
		       
		            $mes->add($hooks, E_MESSAGE_SUCCESS);
		
		            $mes->addAuto($download_id, 'insert', DOWLAN_1." (<a href='".e_PLUGIN."download/download.php?view.".$download_id."'>".$_POST['download_name']."</a>)");
		
		            $dlInfo['download_id'] = $download_id;
		            $this->downloadLog('DOWNL_05',$dlInfo,$dlMirrors);
		            $dlInfo['download_datestamp'] = $time;      // This is what 0.7 did, regardless of settings
		            unset($dlInfo['download_class']);         // Also replicating 0.7
		            $e_event->trigger("dlpost", $dlInfo); // @deprecated 
					
					e107::getEvent()->trigger('admin_download_create',$dlInfo); 
		
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
	         $mes->addAuto($sql -> db_Delete("download_mirror", "mirror_id=".$del_id), delete, DOWLAN_135);
	         e107::getLog()->add('DOWNL_14','ID: '.$del_id,E_LOG_INFORMATIVE,'');
	      }
	
	
	      if (!$sql -> select("download_mirror"))
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
	            <td style='width: 30%;'>".($mirror_image ? "<img src='".e_FILE."downloadimages/".$mirror_image."' alt=''/>" : LAN_NONE)."</td>
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
	         $sql -> select("download_mirror", "*", "mirror_id='".intval($id)."' ");
	         $mirror = $sql -> fetch();
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
	      <table class='table adminform'>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_12."</td>
	      <td style='width: 70%;'>
	      <input class='form-control input-xxlarge' type='text' name='mirror_name' size='60' value='{$mirror_name}' maxlength='200'/>
	      </td>
	      </tr>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_139."</td>
	      <td style='width: 70%;'>
	      <input class='form-control input-xxlarge' type='text' name='mirror_url' size='70' value='{$mirror_url}' maxlength='255'/>
	      </td>
	      </tr>
	
	      <tr>
	      <td style='width: 30%;'>".DOWLAN_136."</td>
	      <td style='width: 70%;'>
	      <input class='form-control input-xxlarge' type='text' id='mirror_image' name='mirror_image' size='60' value='{$mirror_image}' maxlength='200'/>
	
	
	      <br /><input class='btn btn-default btn-secondary button' type ='button' style='cursor:pointer' size='30' value='".DOWLAN_42."' onclick='expandit(this)'/>
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
	      <input class='form-control' type='text' name='mirror_location' size='60' value='$mirror_location' maxlength='200'/>
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
	      ".($edit ? "<input class='btn btn-default btn-secondary button' type='submit' name='submit_mirror' value='".DOWLAN_142."'/><input type='hidden' name='id' value='{$mirror_id}'/>" : "<input class='btn button' type='submit' name='submit_mirror' value='".DOWLAN_143."'/>")."
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
		            $mes->addAuto($sql -> db_Update("download_mirror", "mirror_name='{$name}', mirror_url='{$url}', mirror_image='".$tp->toDB($_POST['mirror_image'])."', mirror_location='{$location}', mirror_description='{$description}' WHERE mirror_id=".intval($_POST['id'])), 'update', DOWLAN_133);
		            e107::getLog()->add('DOWNL_13','ID: '.intval($_POST['id']).'[!br!]'.$logString,E_LOG_INFORMATIVE,'');
		         }
		         else
		         {
		            $mes->addAuto($sql -> db_Insert("download_mirror", "0, '{$name}', '{$url}', '".$tp->toDB($_POST['mirror_image'])."', '{$location}', '{$description}', 0"), 'insert', DOWLAN_134);
		            e107::getLog()->add('DOWNL_12',$logString,E_LOG_INFORMATIVE,'');
		         }
		      }
	   }

	private function supported_secure_link_variables_html()
	{
		require_once(__DIR__."/../handlers/NginxSecureLinkMd5Decorator.php");
		$supported_secure_link_variables_html = "<ul>";
		foreach(NginxSecureLinkMd5Decorator::supported_variables() as $variable)
		{
			$supported_secure_link_variables_html .= "<li><code>$variable</code></li>";
		}
		$supported_secure_link_variables_html .= "</ul>";
		return $supported_secure_link_variables_html;
	}

	private function mirror_order_options_html($pref)
	{
		return ($pref['mirror_order'] == "0" ? "<option value='0' selected='selected'>".DOWLAN_161."</option>" : "<option value='0'>".DOWLAN_161."</option>").
			($pref['mirror_order'] == "1" ? "<option value='1' selected='selected'>".LAN_ID."</option>" : "<option value='1'>".LAN_ID."</option>").
			($pref['mirror_order'] == "2" ? "<option value='2' selected='selected'>".DOWLAN_12."</option>" : "<option value='2'>".DOWLAN_12."</option>");
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
		         "download_requested" => LAN_PLUGIN_DOWNLOAD_NAME,
		         "download_name"      => DOWLAN_59,
		         "download_author"    => DOWLAN_15
		      );
		      $sort_options = array(
		         "ASC"    => DOWLAN_62,
		         "DESC"   => DOWLAN_63
		      );

		   	$text = "
				   
					   <ul class='nav nav-tabs'>
						   <li class='active'><a data-toggle='tab' href='#core-download-download1'>".LAN_DL_DOWNLOAD_OPT_GENERAL."</a></li>
						   <li><a data-toggle='tab' href='#core-download-download2'>".LAN_DL_DOWNLOAD_OPT_BROKEN."</a></li>
						   <li><a data-toggle='tab' href='#core-download-download3'>".LAN_DL_DOWNLOAD_OPT_AGREE."</a></li>
						   <li><a data-toggle='tab' href='#core-download-download4'>".LAN_DL_DOWNLOAD_OPT_SECURITY."</a></li>
						   <li><a data-toggle='tab' href='#core-download-download5'>".LAN_DL_UPLOAD."</a></li>
					   </ul>
						
		        		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		        		<div class='tab-content'>
		   				<div class='tab-pane active' id='core-download-download1'>
		            	   <div>
		            		   <table class='table adminform'>
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
		            		         <td>".$frm->select('download_order', $order_options, $pref['download_order'])."</td>
		            		      </tr>
		            		      <tr>
		            		         <td>".LAN_ORDER."</td>
		             		         <td>".$frm->select('download_sort', $sort_options, $pref['download_sort'])."</td>
		            		      </tr>
		            		      <tr>
		               		      <td>".DOWLAN_160."</td>
		               		      <td>
		                  		      <select name='mirror_order' class='form-control'>".$this->mirror_order_options_html($pref)."
		            		            </select>
		               		      </td>
		            		      </tr>
		            		      <tr>
		            		         <td>".DOWLAN_164."</td>
		            		         <td><input type='text' name='recent_download_days' class='form-control' value='".$pref['recent_download_days']."' size='3' maxlength='3'/>
		            		         </td>
		            		      </tr>
		            		   </table>
		            		</div>
				   		</div>
		   				<div class='tab-pane' id='core-download-download2'>
		            	   <div>
		            		   <table class='table adminform'>
		            		      <colgroup>
		            		         <col style='width:30%'/>
		            		         <col style='width:70%'/>
		            		      </colgroup>
		            		      <tr>
		               		      <td>".DOWLAN_151."</td>
		               		      <td>". r_userclass("download_reportbroken", $pref['download_reportbroken'])."</td>
		            		      </tr>";

		            		      //moved to e_notify
		            		      /* 
		            		      <tr>
		               		      <td>".DOWLAN_150."</td>
		               		      <td>". ($pref['download_email'] ? "<input type='checkbox' name='download_email' value='1' checked='checked'/>" : "<input type='checkbox' name='download_email' value='1'/>")."</td>
		            		      </tr>*/
		    $text .= " 
		            		   </table>
		            		</div>
				   		</div>
		   				<div class='tab-pane' id='core-download-download3'>
		            	   <div>
		            		   <table class='table adminform'>
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
				   		</div>
		   				<div class='tab-pane' id='core-download-download4'>
		            	   <div>
		            	   		<p style='padding: 8px'>
		            	   			".LAN_DL_SECURITY_DESCRIPTION."
								</p>
		            		   <table class='table adminform'>
		            		      <colgroup>
		            		         <col style='width:30%'/>
		            		         <col style='width:70%'/>
		            		      </colgroup>
		            		      <tr>
		            		         <td>".LAN_DL_SECURITY_MODE."</td>
		            		         <td>".$frm->select('download_security_mode', $this->security_options, $pref['download_security_mode'])."</td>
		            		      </tr>
		            		      <tbody id='nginx-secure_link_md5' ".($pref['download_security_mode'] === 'nginx-secure_link_md5' ? "" : "style='display:none'").">
		            		      	<tr>
		            		     	 	<td>".LAN_DL_SECURITY_NGINX_SECURELINKMD5_EXPRESSION."</td>
		            		     	 	<td>
		            		     	 		".$frm->text('download_security_expression', $pref['download_security_expression'], 1024)."
		            		     	 		<div class='field-help'>".LAN_DL_SECURITY_NGINX_SECURELINKMD5_EXPRESSION_HELP."</div>
		            		     	 		<small><a href='#' onclick='event.preventDefault();$(\"#supported-nginx-variables\").toggle();this.blur()'>
		            		     	 			".LAN_DL_SECURITY_NGINX_SUPPORTED_VARIABLES_TOGGLE."
		            		     	 		</a></small>
		            		     	 		<div id='supported-nginx-variables' style='display:none'>
		            	   						".$this->supported_secure_link_variables_html()."
		            		     	 		</div>
		            		     	 	</td>
		            		      	</tr>
		            		      	<tr>
		            		      		<td>".LAN_DL_SECURITY_LINK_EXPIRY."</td>
		            		      		<td>
		            		     	 		".$frm->text('download_security_link_expiry', $pref['download_security_link_expiry'], 16, array('pattern' => '\d+'))."
		            		      			<div class='field-help'>".LAN_DL_SECURITY_LINK_EXPIRY_HELP."</div>
		            		      		</td>
		            		      	</tr>
								  </tbody>
		            		   </table>
		            		</div>
				   		</div>
				   		<div class='tab-pane' id='core-download-download5'>
		            	   <div>
		            		   <table class='table adminform'>
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
				   		</div>
						   <div class='buttons-bar center'>
		                  <input class='btn btn-default btn-secondary button' type='submit' name='updatedownlaodoptions' value='".DOWLAN_64."'/>
		               </div>
		              
		           </div>
		           </form>
		      ";

		   	  e107::js('footer-inline', "
		   	  $('#download-security-mode').on('change', function() {
		   	    var mode = $(this).val();
		   	    
		   	    if (mode == 'nginx-secure_link_md5') {
		   	        $('#nginx-secure_link_md5').show('slow');
		   	        return;
		   	    }
		   	    
		   	    $('#nginx-secure_link_md5').hide('slow');
		   	  });
		   	  ");

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
			e107::getLog()->add($aText,$logString,E_LOG_INFORMATIVE,'');
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
		$text = "<select class='form-control' name='release_type' >";
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

				
class download_mirror_ui extends e_admin_ui
{
			
		protected $pluginTitle		= LAN_PLUGIN_DOWNLOAD_NAME;
		protected $pluginName		= 'download';
		protected $table			= 'download_mirror';
		protected $pid				= 'mirror_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
	//	protected $batchCopy		= true;		
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs			= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable. 
		
	//	protected $listQry      	= "SELECT * FROM #tableName WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= 'mirror_id DESC';
	
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'mirror_id' 			=>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'mirror_name' 		=>   array ( 'title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'mirror_url' 			=>   array ( 'title' => LAN_URL, 'type' => 'url', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'mirror_image' 		=>   array ( 'title' => LAN_IMAGE, 'type' => 'image', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'mirror_location' 	=>   array ( 'title' => DOWLAN_141, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'mirror_description' 	=>   array ( 'title' => LAN_DESCRIPTION, 'type' => 'bbarea', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'mirror_count' 		=>   array ( 'title' => 'Count', 'type' => 'hidden', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options' 			=>   array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('mirror_name', 'mirror_url', 'mirror_image', 'mirror_location');
		
	
		public function init()
		{
			// Set drop-down values (if any). 
	
		}
	
	/*	
		// optional - override edit page. 
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
	*/
			
}
				


class download_mirror_form_ui extends e_admin_form_ui
{

}

class download_broken_ui extends e_admin_ui
{

	protected $pluginTitle		= LAN_PLUGIN_DOWNLOAD_NAME;
	protected $pluginName		= 'download';
	protected $table			= 'generic';
	protected $pid				= 'gen_id';
	protected $perPage 			= 10;
	protected $listQry			= "SELECT g.*,u.user_name FROM `#generic` AS g LEFT JOIN `#user` AS u ON g.gen_user_id = u.user_id WHERE g.gen_type='Broken Download'";
	protected $listOrder		= 'gen_datestamp ASC';

	protected $fields 		= array (  
		'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
        'gen_id' 				=> array ( 'title' => LAN_ID, 'type' => 'number', 'nolist' => true,	'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
        'gen_datestamp' 		=> array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => '10%', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
        //'gen_intdata' 		=> array ( 'title' =>  LAN_ID, 'type' => 'number', 'batch'=>false, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
        'gen_ip' 				=> array ( 'title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
        'gen_chardata' 		=> array ( 'title' => LAN_DESCRIPTION, 'type' => 'text', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left', ),
        'gen_user_id' 		=> array ( 'title' => DOWLAN_199, 'type' => 'user', 'batch'=>false, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => 'idField=gen_user_id', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left nowrap',  ),
        'options'				=> array ( 'title' => LAN_OPTIONS, 'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'readParms'=>'edit=0'  ),
	);

	protected $fieldpref = array('gen_datestamp', 'gen_ip', 'gen_chardata', 'gen_user_id');

	protected $batchOptions = array();

	// optional
	public function init()
	{
	
	}

	public function afterDelete($deleted_data, $id, $deleted_check)
	{
		
	}

	public function renderHelp()
	{
		$help_text = str_replace("[br]", "<br />", DOWLAN_HELP_11);
		$help_text = str_replace(array("[", "]"), array("<a href='".e_ADMIN_ABS."notify.php'>"), $help_text); 
		
		return array('caption' => LAN_HELP, 'text' => $help_text);
	}

}



class download_broken_form_ui extends e_admin_form_ui
{

	function options($att, $value, $id, $attributes)
	{
		if($attributes['mode'] == 'read')
		{
			$download_id = $this->getController()->getListModel()->get('gen_intdata');
			
			$text = "<div class='btn-group'>";
			$text .= "<a class='btn btn-default' href='".e_SELF."?mode=main&action=edit&id=". $download_id."'>".ADMIN_VIEW_ICON."</a>";
			$text .= $this->renderValue('options', $value, array('readParms' => 'edit=0'), $id);
			$text .= "</div>";

			return $text;
		}
	}
}		