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
 * $Revision: 1.4 $
 * $Date: 2009-10-22 05:14:11 $
 * $Author: e107coders $
 *
*/

require_once("../../class2.php");
if (!getperms("P")) { header("location:".e_BASE."index.php"); exit; }
require_once(e_ADMIN."auth.php");


class efeed extends e_model_interface
{
	var $fields;
	var $fieldpref;
	var $listQry;
	var $table;
	var $primary;


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
		
		$this->prefs = array(
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'text', 'width' => 'auto'),	// User name
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'width' => 'auto'),
		
		);
		
		$this->fieldpref = (varset($user_pref['admin_release_columns'])) ? $user_pref['admin_release_columns'] : array_keys($this->fields);
		
		$this->listQry = "SELECT * FROM #release ORDER BY release_id DESC";
		$this->editQry = "SELECT * FROM #release WHERE release_id = {ID}";
		
		$this->table = "release";
		$this->primary = "release_id";
		$this->pluginTitle = "e107 Release";
		
		$this->adminMenu = array(
			'list'		=> array('caption'=>'Release List', 'perm'=>'0'),
			'create' 	=> array('caption'=>LAN_CREATE."/".LAN_EDIT, 'perm'=>'0')			
		);
		
	}
	
	// custom method. (matches field/key name)
	function release_type($curVal)
	{
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


	//TODO move this to the model class.. and make generic. 
	function optionsPage()
	{
		global $e107, $pref,$emessage;
		
		$frm = e107::getForm();
		

		if(!isset($pref['pageCookieExpire'])) $pref['pageCookieExpire'] = 84600;

		//XXX Lan - Options
		$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
				<fieldset id='core-cpage-options'>
					<legend class='e-hideme'>".LAN_OPTIONS."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".CUSLAN_29."</td>
								<td class='control'>
									".$frm->radio_switch('listPages', $pref['listPages'])."
								</td>
							</tr>

							<tr>
								<td class='label'>".CUSLAN_30."</td>
								<td class='control'>
									".$frm->text('pageCookieExpire', $pref['pageCookieExpire'], 10)."
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('saveOptions', CUSLAN_40, 'submit')."
					</div>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender(LAN_OPTIONS, $emessage->render().$text);
	}


	function saveSettings()
	{
		global $pref, $admin_log, $emessage;
		$temp['listPages'] = $_POST['listPages'];
		$temp['pageCookieExpire'] = $_POST['pageCookieExpire'];
		if ($admin_log->logArrayDiffs($temp, $pref, 'CPAGE_04'))
		{
			save_prefs();		// Only save if changes
			$emessage->add(CUSLAN_45, E_MESSAGE_SUCCESS);
		}
		else
		{
			$emessage->add(CUSLAN_46);
		}
	}



}


$ef = new efeed;
$ef->init();





require_once(e_ADMIN."footer.php");




function admin_config_adminmenu()
{
	global $ef;
	global $action;
	$ef->show_options($action);
}

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>