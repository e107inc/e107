<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Release Plugin
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/release/admin_config.php,v $
 * $Revision: 1.10 $
 * $Date: 2009-10-28 17:05:35 $
 * $Author: secretr $
 *
*/

require_once("../../class2.php");
if (!getperms("P")) { header("location:".e_BASE."index.php"); exit; }

require_once(e_HANDLER.'admin_handler.php');

class plugin_release_admin_dispatcher extends e_admin_dispatcher
{
		/**
		 * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
		 * @var array
		 */
		protected $controllerList = array(
			'main'		=> array('controller' => 'plugin_release_admin_controller_main', 'path' => null, 'ui' => 'plugin_release_admin_ui_main', 'uipath' => null)				
		);	
		/**
		 * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}release/admin_config.php', 'perm' => '0']);
		 * Additionally, any valid e_admin_menu() key-value pair could be added to the above array
		 * @var array
		 */
		protected $adminMenu = array(
			'main/list'		=> array('caption'=> 'Release List', 'perm'=>'0'),
			'main/create' 	=> array('caption'=> LAN_CREATE, 'perm'=>'0'),
			'main/options' 	=> array('caption'=> LAN_OPTIONS, 'perm'=>'0'),
			'main/custom'	=> array('caption'=> 'Custom Page', 'perm'=>0)		
		);	
		
		/**
		 * Navigation menu title
		 * @var string
		 */
		protected $menuTitle = 'Release Menu';
}

class plugin_release_admin_controller_main extends e_admin_controller_main
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
	    
		//TODO change the release_url type back to URL before release. 
		// required
    	protected  $fields = array(
			'checkboxes'				=> array('title'=> '', 					'type' => '',		'width'=>'5%', 		'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center'),
			'release_id'				=> array('title'=> ID, 					'type' => '',		'width'=>'5%',		'thclass' => '',	'forced'=> TRUE, 'primary'=>TRUE),
            'release_type'	   			=> array('title'=> 'Type', 				'type' => 'method', 'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
			'release_folder' 			=> array('title'=> 'Folder', 			'type' => 'text', 	'width' => 'auto',	'thclass' => ''),	
			'release_name' 				=> array('title'=> 'Name', 				'type' => 'text', 	'width' => 'auto',	'thclass' => ''),
			'release_version' 			=> array('title'=> 'Version',			'type' => 'text', 	'width' => 'auto',	'thclass' => ''),
			'release_author' 			=> array('title'=> LAN_AUTHOR,			'type' => 'text', 	'width' => 'auto',	'thclass' => 'left'), 
         	'release_authorURL' 		=> array('title'=> LAN_AUTHOR_URL, 		'type' => 'url', 	'width' => 'auto',	'thclass' => 'left'), 
            'release_date' 				=> array('title'=> LAN_DATE, 			'type' => 'text', 	'width' => 'auto',	'thclass' => ''),	 
			'release_compatibility' 	=> array('title'=> 'compatib',			'type' => 'text', 	'width' => '10%',	'thclass' => 'center' ),	 
			'release_url' 				=> array('title'=> 'Userclass',			'type' => 'userclass', 	'width' => '10%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE),	 
			'options' 					=> array('title'=> LAN_OPTIONS, 		'type' => '', 		'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE)
		);
		
		//required - default column user prefs 
		protected $fieldpref = array('checkboxes', 'release_id', 'release_type', 'release_compatibility', 'options');
		
		// required if no custom model is set in init()
		protected $dataFields = array(
			'release_id' => 'int',
			'release_type' => 'str',
			'release_folder' => 'str',
			'release_name' => 'str',
			'release_version' => 'str',
			'release_author' => 'str',
			'release_authorURL' => 'str',
			'release_date' => 'int',
			'release_compatibility' => 'str',
			'release_url' => 'str',
		);
		
		// optional
		protected  $validationRules = array();
		
		// optional
		protected $prefs = array( //TODO add option for core or plugin pref. 
		
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);
		
		// required if no custom model is set in init()
		protected $listQry = "SELECT * FROM #release"; // without any Order or Limit. 
		
		// required if no custom model is set in init()
		protected $editQry = "SELECT * FROM #release WHERE release_id = {ID}";
		
		// optional
		public function init()
		{
		}
}

$dispatcher = new plugin_release_admin_dispatcher();
$dispatcher->runObservers(true);
e107::setRegistry('admin/release_dispatcher', $dispatcher);

require_once(e_ADMIN."auth.php");

e107::getRegistry('admin/release_dispatcher')->runPage();

require_once(e_ADMIN."footer.php");

/*
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


function admin_config_adminmenu() //TODO move this into e_model_interface
{
	//global $rp;
	//$rp->show_options();
	e107::getRegistry('admin/release_dispatcher')->renderMenu();
}


function headerjs() // needed for the checkboxes - how can we remove the need to duplicate this code?
{
	/*require_once (e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";*/
	return e107::getRegistry('admin/release_dispatcher')->getController()->getHeader();

	//return $ret;
}
?>