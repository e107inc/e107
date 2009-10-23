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
 * $Revision: 1.7 $
 * $Date: 2009-10-23 04:25:19 $
 * $Author: e107coders $
 *
*/

require_once("../../class2.php");
if (!getperms("P")) { header("location:".e_BASE."index.php"); exit; }
require_once(e_ADMIN."auth.php");

class releasePlugin extends e_model_interface
{

	function __construct()
	{	    
    	$this->fields = array(
		
			'release_id'				=> array('title'=> ID, 'width'=>'5%', 'forced'=> TRUE, 'primary'=>TRUE),
            'release_type'	   			=> array('title'=> 'type', 'type' => 'method', 'width'=>'auto'),
			'release_folder' 			=> array('title'=> 'folder', 'type' => 'text', 'width' => 'auto'),	// User name
			'release_name' 				=> array('title'=> 'name', 'type' => 'text', 'width' => 'auto'),
			'release_version' 			=> array('title'=> 'version', 'type' => 'text', 'width' => 'auto'),
			'release_author' 			=> array('title'=> LAN_AUTHOR, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left'), 
         	'release_authorURL' 		=> array('title'=> LAN_AUTHOR.'URL', 'type' => 'url', 'width' => 'auto', 'thclass' => 'left'), 
            'release_date' 				=> array('title'=> LAN_DATE, 'type' => 'text', 'width' => 'auto'),	 
			'release_compatibility' 	=> array('title'=> 'compatib', 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 
			'release_url' 				=> array('title'=> 'URL', 'type' => 'url', 'width' => '10%', 'thclass' => 'center' ),	 
			'options' 					=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);
		
		$this->prefs = array( //TODO add option for core or plugin pref. 
		
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);
				
		$this->listQry = "SELECT * FROM #release"; // without any Order or Limit. 
		$this->editQry = "SELECT * FROM #release WHERE release_id = {ID}";		
		$this->table = "release";
		$this->primary = "release_id";
		$this->pluginTitle = "e107 Release";
		
		$this->adminMenu = array(
			'list'		=> array('caption'=>'Release List', 'perm'=>'0'),
			'create' 	=> array('caption'=>LAN_CREATE."/".LAN_EDIT, 'perm'=>'0'),
			'options' 	=> array('caption'=>LAN_OPTIONS, 'perm'=>'0'),
			'custom'	=> array('caption'=>'Custom Page', 'perm'=>0)				
		);		
	}
	
	// Custom View/Form-Element method. ie. Naming should match field/key with type=method.
	function release_type($curVal)
	{
		if($this->mode == 'list')
		{
			return $curVal;
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

$rp = new releasePlugin;
$rp->init();

require_once(e_ADMIN."footer.php");

function admin_config_adminmenu() //TODO move this into e_model_interface
{
	global $rp;
	$rp->show_options($action);
}

?>