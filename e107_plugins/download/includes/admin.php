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
		// protected $perPage = 20;

		// default - true - TODO - move to displaySettings
		protected $batchDelete = true;

		// UNDER CONSTRUCTION
		// protected $displaySettings = array();

		// UNDER CONSTRUCTION
		// protected $disallowPages = array('main/create', 'main/prefs');

		//TODO change the release_url type back to URL before release.
		// required
		/**
		 * (use this as starting point for wiki documentation)
		 * $fields format  (string) $field_name => (array) $attributes
		 *
		 * $field_name format:
		 * 	'table_alias_or_name.field_name.field_alias' (if JOIN support is needed) OR just 'field_name'
		 * NOTE: Keep in mind the count of exploded data can be 1 or 3!!! This means if you wanna give alias
		 * on main table field you can't omit the table (first key), alternative is just '.' e.g. '.field_name.field_alias'
		 *
		 * $attributes format:
		 * 	- title (string) Human readable field title, constant name will be accpeted as well (multi-language support
		 *
		 *  - type (string) null (means system), number, text, dropdown, url, image, icon, datestamp, userclass, userclasses, user[_name|_loginname|_login|_customtitle|_email],
		 *    boolean, method, ip
		 *  	full/most recent reference list - e_form::renderTableRow(), e_form::renderElement(), e_admin_form_ui::renderBatchFilter()
		 *  	for list of possible read/writeParms per type see below
		 *
		 *  - data (string) Data type, one of the following: int, integer, string, str, float, bool, boolean, model, null
		 *    Default is 'str'
		 *    Used only if $dataFields is not set
		 *  	full/most recent reference list - e_admin_model::sanitize(), db::_getFieldValue()
		 *  - dataPath (string) - xpath like path to the model/posted value. Example: 'dataPath' => 'prefix/mykey' will result in $_POST['prefix']['mykey']
		 *  - primary (boolean) primary field (obsolete, $pid is now used)
		 *
		 *  - help (string) edit/create table - inline help, constant name will be accpeted as well, optional
		 *  - note (string) edit/create table - text shown below the field title (left column), constant name will be accpeted as well, optional
		 *
		 *  - validate (boolean|string) any of accepted validation types (see e_validator::$_required_rules), true == 'required'
		 *  - rule (string) condition for chosen above validation type (see e_validator::$_required_rules), not required for all types
		 *  - error (string) Human readable error message (validation failure), constant name will be accepted as well, optional
		 *
		 *  - batch (boolean) list table - add current field to batch actions, in use only for boolean, dropdown, datestamp, userclass, method field types
		 *    NOTE: batch may accept string values in the future...
		 *  	full/most recent reference type list - e_admin_form_ui::renderBatchFilter()
		 *
		 *  - filter (boolean) list table - add current field to filter actions, rest is same as batch
		 *
		 *  - forced (boolean) list table - forced fields are always shown in list table
		 *  - nolist (boolean) list table - don't show in column choice list
		 *  - noedit (boolean) edit table - don't show in edit mode
		 *
		 *  - width (string) list table - width e.g '10%', 'auto'
		 *  - thclass (string) list table header - th element class
		 *  - class (string) list table body - td element additional class
		 *
		 *  - readParms (mixed) parameters used by core routine for showing values of current field. Structure on this attribute
		 *    depends on the current field type (see below). readParams are used mainly by list page
		 *
		 *  - writeParms (mixed) parameters used by core routine for showing control element(s) of current field.
		 *    Structure on this attribute depends on the current field type (see below).
		 *    writeParams are used mainly by edit page, filter (list page), batch (list page)
		 *
		 * $attributes['type']->$attributes['read/writeParams'] pairs:
		 *
		 * - null -> read: n/a
		 * 		  -> write: n/a
		 *
		 * - dropdown -> read: 'pre', 'post', array in format posted_html_name => value
		 * 			  -> write: 'pre', 'post', array in format as required by e_form::selectbox()
		 *
		 * - user -> read: [optional] 'link' => true - create link to user profile, 'idField' => 'author_id' - tells to renderValue() where to search for user id (used when 'link' is true and current field is NOT ID field)
		 * 				   'nameField' => 'comment_author_name' - tells to renderValue() where to search for user name (used when 'link' is true and current field is ID field)
		 * 		  -> write: [optional] 'nameField' => 'comment_author_name' the name of a 'user_name' field; 'currentInit' - use currrent user if no data provided; 'current' - use always current user(editor); '__options' e_form::userpickup() options
		 *
		 * - number -> read: (array) [optional] 'point' => '.', [optional] 'sep' => ' ', [optional] 'decimals' => 2, [optional] 'pre' => '&euro; ', [optional] 'post' => 'LAN_CURRENCY'
		 * 			-> write: (array) [optional] 'pre' => '&euro; ', [optional] 'post' => 'LAN_CURRENCY', [optional] 'maxlength' => 50, [optional] '__options' => array(...) see e_form class description for __options format
		 *
		 * - ip		-> read: n/a
		 * 			-> write: [optional] element options array (see e_form class description for __options format)
		 *
		 * - text -> read: (array) [optional] 'htmltruncate' => 100, [optional] 'truncate' => 100, [optional] 'pre' => '', [optional] 'post' => ' px'
		 * 		  -> write: (array) [optional] 'pre' => '', [optional] 'post' => ' px', [optional] 'maxlength' => 50 (default - 255), [optional] '__options' => array(...) see e_form class description for __options format
		 *
		 * - textarea 	-> read: (array) 'noparse' => '1' default 0 (disable toHTML text parsing), [optional] 'bb' => '1' (parse bbcode) default 0,
		 * 								[optional] 'parse' => '' modifiers passed to e_parse::toHTML() e.g. 'BODY', [optional] 'htmltruncate' => 100,
		 * 								[optional] 'truncate' => 100, [optional] 'expand' => '[more]' title for expand link, empty - no expand
		 * 		  		-> write: (array) [optional] 'rows' => '' default 15, [optional] 'cols' => '' default 40, [optional] '__options' => array(...) see e_form class description for __options format
		 * 								[optional] 'counter' => 0 number of max characters - has only visual effect, doesn't truncate the value (default - false)
		 *
		 * - bbarea -> read: same as textarea type
		 * 		  	-> write: (array) [optional] 'pre' => '', [optional] 'post' => ' px', [optional] 'maxlength' => 50 (default - 0),
		 * 				[optional] 'size' => [optional] - medium, small, large - default is medium,
		 * 				[optional] 'counter' => 0 number of max characters - has only visual effect, doesn't truncate the value (default - false)
		 *
		 * - image -> read: [optional] 'title' => 'SOME_LAN' (default - LAN_PREVIEW), [optional] 'pre' => '{e_PLUGIN}myplug/images/',
		 * 				'thumb' => 1 (true) or number width in pixels, 'thumb_urlraw' => 1|0 if true, it's a 'raw' url (no sc path constants),
		 * 				'thumb_aw' => if 'thumb' is 1|true, this is used for Adaptive thumb width
		 * 		   -> write: (array) [optional] 'label' => '', [optional] '__options' => array(...) see e_form::imagepicker() for allowed options
		 *
		 * - icon  -> read: [optional] 'class' => 'S16', [optional] 'pre' => '{e_PLUGIN}myplug/images/'
		 * 		   -> write: (array) [optional] 'label' => '', [optional] 'ajax' => true/false , [optional] '__options' => array(...) see e_form::iconpicker() for allowed options
		 *
		 * - datestamp  -> read: [optional] 'mask' => 'long'|'short'|strftime() string, default is 'short'
		 * 		   		-> write: (array) [optional] 'label' => '', [optional] 'ajax' => true/false , [optional] '__options' => array(...) see e_form::iconpicker() for allowed options
		 *
		 * - url	-> read: [optional] 'pre' => '{ePLUGIN}myplug/'|'http://somedomain.com/', 'truncate' => 50 default - no truncate, NOTE:
		 * 			-> write:
		 *
		 * - method -> read: optional, passed to given method (the field name)
		 * 			-> write: optional, passed to given method (the field name)
		 *
		 * - hidden -> read: 'show' => 1|0 - show hidden value, 'empty' => 'something' - what to be shown if value is empty (only id 'show' is 1)
		 * 			-> write: same as readParms
		 *
		 * - upload -> read: n/a
		 * 			-> write: Under construction
		 *
		 * Special attribute types:
		 * - method (string) field name should be method from the current e_admin_form_ui class (or its extension).
		 * 		Example call: field_name($value, $render_action, $parms) where $value is current value,
		 * 		$render_action is on of the following: read|write|batch|filter, parms are currently used paramateres ( value of read/writeParms attribute).
		 * 		Return type expected (by render action):
		 * 			- read: list table - formatted value only
		 * 			- write: edit table - form element (control)
		 * 			- batch: either array('title1' => 'value1', 'title2' => 'value2', ..) or array('singleOption' => '<option value="somethig">Title</option>') or rendered option group (string '<optgroup><option>...</option></optgroup>'
		 * 			- filter: same as batch
		 * @var array
		 */
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
			'download_category' 		=> array('title'=> LAN_CATEGORY,		'type' => 'dropdown',			'width' => '5%',	'batch' => TRUE, 'filter'=>TRUE),		
			'download_active'			=> array('title'=> DOWLAN_21,			'type' => 'method', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center', 'class' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			'download_datestamp' 		=> array('title'=> LAN_DATE, 			'type' => 'datestamp', 	'data' => 'int',		'width' => 'auto',	'thclass' => '', 'readParms' => 'long', 'writeParms' => ''),
			
			'download_thumb' 			=> array('title'=> DOWLAN_20,			'type' => 'image', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),
			'download_image' 			=> array('title'=> DOWLAN_19,			'type' => 'image', 		'data' => 'str',		'width' => '20%',	'thclass' => 'center','readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>TRUE,	'batch' => FALSE, 'filter'=>FALSE),
			'download_comment'			=> array('title'=> DOWLAN_102,			'type' => 'boolean', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			'download_class' 			=> array('title'=> DOWLAN_113,			'type' => 'userclass',		'width' => 'auto', 'data' => 'int'),		
			'download_mirror' 			=> array('title'=> DOWLAN_128,			'type' => 'text', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),
			'download_mirror_type' 		=> array('title'=> DOWLAN_195,			'type' => 'method', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),
			
			'download_visible' 			=> array('title'=> LAN_VISIBILITY,		'type' => 'userclass',		'width' => 'auto', 'data' => 'int'),
		//	'download_order' 	=> array('title'=> LAN_ORDER,	'type' => 'text',			'width' => '5%', 'thclass' => 'left' ),					
			'issue' 					=> array('title'=> 'Issue', 		'type' => 'method', 		'data' => null,	'nolist'=>TRUE, 'noedit'=>TRUE, 'filter'=>TRUE),
			'options' 					=> array('title'=> LAN_OPTIONS, 		'type' => null, 		'data' => null,			'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE)
		);
		
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

		// optional
		public function init()
		{
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
			$adminDownload->create_download();
		}
			
		function importPage()
		{
			$this->batchImportForm();
		}
	
		function settingsPage()
		{
			global $adminDownload;
			$adminDownload->show_download_options();
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
			$adminDownload->show_existing_mirrors();
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
