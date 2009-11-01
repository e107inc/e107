<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Release Plugin Administration UI
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/release/includes/admin.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-11-01 19:05:26 $
 * $Author: secretr $
*/

//require_once(e_HANDLER.'admin_handler.php'); - autoloaded - see class2.php __autoload()
class plugin_release_admin extends e_admin_dispatcher
{
	/**
	 * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
	 * @var array
	 */
	protected $modes = array(
		'main'		=> array('controller' => 'plugin_release_admin_ui', 'path' => null, 'ui' => 'plugin_release_admin_form_ui', 'uipath' => null)				
	);	

	/**
	 * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}release/admin_config.php', 'perm' => '0']);
	 * Additionally, any valid e_admin_menu() key-value pair could be added to the above array
	 * @var array
	 */
	protected $adminMenu = array(
		'main/list'		=> array('caption'=> 'Manage', 'perm' => '0'),
		'main/create' 	=> array('caption'=> LAN_CREATE, 'perm' => '0'),
		'main/options' 	=> array('caption'=> 'Settings', 'perm' => '0'),
		'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	/**
	 * Optional, map mode/action t
	 * Format: 'MODE/ACTION' => 'MODE ALIAS/ACTION ALIAS';
	 * @var array
	 */
	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	/**
	 * Navigation menu title
	 * @var string
	 */
	protected $menuTitle = 'Release Menu';
}

class plugin_release_admin_ui extends e_admin_ui
{
		// required
		protected $pluginTitle = "e107 Release";
		
		// required
		protected $pluginName = 'release';
		
		// required - if no custom model is set in init()
		protected $table = "release";
		
		// required - if no custom model is set in init() (primary id)
		protected $pid = "release_id";
		
		// optional 
		protected $perPage = 20;
		
		// default - true
		protected $batchDelete = true;
	    
		//TODO change the release_url type back to URL before release. 
		// required
    	protected  $fields = array(
			'checkboxes'				=> array('title'=> '', 					'type' => null,			'data' => null,			'width'=>'5%', 		'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect'),
			'release_id'				=> array('title'=> ID, 					'type' => 'int',		'data' => 'int',		'width'=>'5%',		'thclass' => '',	'forced'=> TRUE, 'primary'=>TRUE/*, 'noedit'=>TRUE*/), //Primary ID is note editable
            'release_type'	   			=> array('title'=> 'Type', 				'type' => 'method', 	'data' => 'str',		'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
			'release_folder' 			=> array('title'=> 'Folder', 			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),	
			'release_name' 				=> array('title'=> 'Name', 				'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),
			'release_version' 			=> array('title'=> 'Version',			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),
			'release_author' 			=> array('title'=> LAN_AUTHOR,			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'), 
         	'release_authorURL' 		=> array('title'=> LAN_AUTHOR_URL, 		'type' => 'url', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'), 
            'release_date' 				=> array('title'=> LAN_DATE, 			'type' => 'datestamp', 	'data' => 'int',		'width' => 'auto',	'thclass' => ''),	 
			'release_compatibility' 	=> array('title'=> 'compatib',			'type' => 'text', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),	 
			'release_url' 				=> array('title'=> 'release_url',		'type' => 'url', 		'data' => 'str',		'width' => '20%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'parms' => 'truncate=30', 'validate' => true, 'help' => 'Enter release URL here', 'error' => 'please, ener valid URL'),	 
			'options' 					=> array('title'=> LAN_OPTIONS, 		'type' => null, 		'data' => null,			'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE)
		);
		
		//required - default column user prefs 
		protected $fieldpref = array('checkboxes', 'release_id', 'release_type', 'release_url', 'release_compatibility', 'options');
		
		// FORMAT field_name=>type - optional if fields 'data' attribute is set or if custom model is set in init()
		/*protected $dataFields = array();*/
		
		// optional, could be also set directly from $fields array with attributes 'validate' => true|'rule_name', 'rule' => 'condition_name', 'error' => 'Validation Error message'
		/*protected  $validationRules = array(
			'release_url' => array('required', '', 'Release URL', 'Help text', 'not valid error message')
		);*/
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);
		
		// required if no custom tree model is set in init()
		protected $listQry = "SELECT * FROM #release"; // without any Order or Limit. 
		
		// optional - required only in case of e.g. tables JOIN. This also could be done with custom model (set it in init())
		protected $editQry = "SELECT * FROM #release WHERE release_id = {ID}";
		
		// optional
		public function init()
		{
		}
}

class plugin_release_admin_form_ui extends e_admin_form_ui
{
	function release_type($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function. 
	{
		if($mode == 'list')
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
}

/* OBSOLETE - will be removed soon
class releasePlugin extends e_model_interface
{

	function __construct()
	{	
	
		$this->pluginTitle = "e107 Release";
	
		$this->table = "release";
	    
		//TODO change the release_url type back to URL before release. 
		
    	$this->fields = array(
			'checkboxes'				=> array('title'=> '', 					'type' => '',		'width'=>'5%', 		'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center'),
			'release_id'				=> array('title'=> ID, 					'type' => '',		'width'=>'5%',		'thclass' => '',	'forced'=> TRUE, 'primary'=>TRUE),
            'release_type'	   			=> array('title'=> 'Type', 				'type' => 'method', 'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
			'release_folder' 			=> array('title'=> 'Folder', 			'type' => 'text', 	'width' => 'auto',	'thclass' => ''),	
			'release_name' 				=> array('title'=> 'Name', 				'type' => 'text', 	'width' => 'auto',	'thclass' => ''),
			'release_version' 			=> array('title'=> 'Version',			'type' => 'text', 	'width' => 'auto',	'thclass' => ''),
			'release_author' 			=> array('title'=> LAN_AUTHOR,			'type' => 'text', 	'width' => 'auto',	'thclass' => 'left'), 
         	'release_authorURL' 		=> array('title'=> LAN_AUTHOR.'URL', 	'type' => 'url', 	'width' => 'auto',	'thclass' => 'left'), 
            'release_date' 				=> array('title'=> LAN_DATE, 			'type' => 'text', 	'width' => 'auto',	'thclass' => ''),	 
			'release_compatibility' 	=> array('title'=> 'compatib',			'type' => 'text', 	'width' => '10%',	'thclass' => 'center' ),	 
			'release_url' 				=> array('title'=> 'Userclass',				'type' => 'userclass', 	'width' => '10%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE),	 
			'options' 					=> array('title'=> LAN_OPTIONS, 		'type' => '', 		'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE)
		);
		
		$this->prefs = array( //TODO add option for core or plugin pref. 
		
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);
		
		$this->listQry = "SELECT * FROM #release"; // without any Order or Limit. 
		$this->editQry = "SELECT * FROM #release WHERE release_id = {ID}";		
	
		$this->adminMenu = array(
			'list'		=> array('caption'=>'Release List', 'perm'=>'0'),
			'create' 	=> array('caption'=>LAN_CREATE."/".LAN_EDIT, 'perm'=>'0'),
			'options' 	=> array('caption'=>LAN_OPTIONS, 'perm'=>'0'),
			'custom'	=> array('caption'=>'Custom Page', 'perm'=>0)				
		);		
	}
	
	// Custom View/Form-Element method. ie. Naming should match field/key with type=method.
	
	
	function release_type($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function. 
	{
		if($mode == 'list')
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
	
	//custom Page = Naming should match $this->adminMenu key + 'Page'. 
	function customPage()
	{
		$ns = e107::getRender();
		$ns->tablerender("Custom","This is a custom Page");
	}

}
*/
//$rp = new releasePlugin;
//$rp->init();