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
 * $Revision: 1.1 $
 * $Date: 2009-09-29 00:03:01 $
 * $Author: e107coders $
 *
*/

require_once("../../class2.php");
if (!getperms("P")) { header("location:".e_BASE."index.php"); exit; }
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$frm = new e_form(true);

$ef = new efeed;


if(varset($_POST['update']) || varset($_POST['create']))
{
	$id = intval($_POST['record_id']);
	$ef->submitPage($id);
}

if(varset($_POST['delete']))
{
	$id = key($_POST['delete']);
	$ef->deleteRecord($id);
	$_GET['mode'] = "list";
}

if(varset($_GET['mode'])=='create')
{
	$id = varset($_POST['edit']) ? key($_POST['edit']) : "";
	$ef->createRecord($id);	
}
else
{
	$ef->listRecords();
}

if(isset($_POST['submit-e-columns']))
{
	$user_pref['admin_release_columns'] = $_POST['e-columns'];
	save_prefs('user');
}


require_once(e_ADMIN."footer.php");



class efeed
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
            'release_type'	   			=> array('title'=> 'type', 'width'=>'auto','method'=>'feed_type'),
			'release_folder' 			=> array('title'=> 'folder', 'type' => 'text', 'width' => 'auto'),	// User name
			'release_name' 				=> array('title'=> 'name', 'type' => 'text', 'width' => 'auto'),
			'release_version' 			=> array('title'=> 'version', 'type' => 'text', 'width' => 'auto'),
			'release_author' 			=> array('title'=> LAN_AUTHOR, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left first'), // Display name
         	'release_authorURL' 		=> array('title'=> LAN_AUTHOR.'URL', 'type' => 'url', 'width' => 'auto', 'thclass' => 'left first'), // Display name

            'release_date' 				=> array('title'=> LAN_DATE, 'type' => 'text', 'width' => 'auto'),	 	// Photo
			'release_compatibility' 	=> array('title'=> 'compatib', 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'release_url' 				=> array('title'=> 'URL', 'type' => 'url', 'width' => '10%', 'thclass' => 'center' ),	 // No real vetting
			'options' 					=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);
		
		$this->fieldpref = (varset($user_pref['admin_release_columns'])) ? $user_pref['admin_release_columns'] : array_keys($this->fields);
		$this->table = "release";
		$this->listQry = "SELECT * FROM #release ORDER BY release_id DESC";
		$this->editQry = "SELECT * FROM #release WHERE release_id = {ID}";
		$this->primary = "release_id";
		$this->pluginTitle = "e107 Release";
		
		$this->listCaption = "Release List";
		$this->createCaption = LAN_CREATE."/".LAN_EDIT;
		
	}


// --------------------------------------------------------------------------
	/**
	 * Generic DB Record Listing Function. 
	 * @param object $mode [optional]
	 * @return 
	 */
	function listRecords($mode=FALSE)
	{
		$ns = e107::getRender();
		$sql = e107::getDb();
		
		global $frm, $pref;
		
		$emessage = eMessage::getInstance();

        $text = "<form method='post' action='".e_SELF."?mode=create'>
                        <fieldset id='core-release-list'>
						<legend class='e-hideme'>".$this->pluginTitle."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>".
							$frm->colGroup($this->fields,$this->fieldpref).
							$frm->thead($this->fields,$this->fieldpref).

							"<tbody>";


		if(!$sql->db_Select_gen($this->listQry))
		{
			$text .= "\n<tr><td colspan='".count($this->fields)."' class='center middle'>".CUSLAN_42."</td></tr>\n";
		}
		else
		{
			$row = $sql->db_getList('ALL', FALSE, FALSE);

			foreach($row as $field)
			{
				$text .= "<tr>\n";
				foreach($this->fields as $key=>$att)
				{	
					$class = vartrue($this->fields[$key]['thclass']) ? "class='".$this->fields[$key]['thclass']."'" : "";		
					$text .= (in_array($key,$this->fieldpref) || $att['forced']==TRUE) ? "\t<td ".$class.">".$this->renderValue($key,$field)."</td>\n" : "";						
				}
				$text .= "</tr>\n";				
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
		";

		$ns->tablerender($this->pluginTitle." :: ".$this->listCaption, $emessage->render().$text);
	}

	/**
	 * Render Field value (listing page)
	 * @param object $key
	 * @param object $row
	 * @return 
	 */
	function renderValue($key,$row)
	{
		$att = $this->fields[$key];	
		
		if($key == "options")
		{
			$id = $this->primary;
			$text = "<input type='image' class='action edit' name='edit[{$row[$id]}]' src='".ADMIN_EDIT_ICON_PATH."' title='".LAN_EDIT."' />";
			$text .= "<input type='image' class='action delete' name='delete[{$row[$id]}]' src='".ADMIN_DELETE_ICON_PATH."' title='".LAN_DELETE." [ ID: {$row[$id]} ]' />";
			return $text;
		}
		
		switch($att['type'])
		{
			case 'url':
				return "<a href='".$row[$key]."'>".$row[$key]."</a>";
			break;
		
			default:
				return $row[$key];
			break;
		}	
		return $row[$key] .$att['type'];	
	}
	
	/**
	 * Render Form Element (edit page)
	 * @param object $key
	 * @param object $row
	 * @return 
	 */
	function renderElement($key,$row)
	{
		global $frm;
		$att = $this->fields[$key];
		$value = $row[$key];	
		
		if($att['method'])
		{
			$meth = $att['method'];
			return $this->$meth($value);
		}
		
		return $frm->text($key, $row[$key], 50);
			
	}

	function feed_type($curVal)
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


	function createRecord($id=FALSE)
	{
		global $frm, $e_userclass, $e_event;

		$tp = e107::getParser();
		$ns = e107::getRender();
		$sql = e107::getDb();

		if($id)
		{
			$query = str_replace("{ID}",$id,$this->editQry);
			$sql->db_Select_gen($query);
			$row = $sql->db_Fetch(MYSQL_ASSOC);			
		}
		else
		{
			$row = array();
		}

		$text = "
			<form method='post' action='".e_SELF."?mode=list' id='dataform' enctype='multipart/form-data'>
				<fieldset id='core-cpage-create-general'>
					<legend class='e-hideme'>".$this->pluginTitle."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>";
			
		foreach($this->fields as $key=>$att)
		{
			if($att['forced']!==TRUE)
			{
				$text .= "
					<tr>
						<td class='label'>".$att['title']."</td>
						<td class='control'>".$this->renderElement($key,$row)."</td>
					</tr>";
			}
							
		}

		$text .= "
			</tbody>
			</table>	
		<div class='buttons-bar center'>";
					
					if($id)
					{
						$text .= $frm->admin_button('update', LAN_UPDATE, 'update');
						$text .= "<input type='hidden' name='record_id' value='".$id."' />";						
					}	
					else
					{
						$text .= $frm->admin_button('create', LAN_CREATE, 'create');	
					}
					
		$text .= "
			</div>
			</fieldset>
		</form>";	
		
		$ns->tablerender($this->pluginTitle." :: ".$this->createCaption, $text);
	}

	/**
	 * Generic Save DB Record Function. 
	 * @param object $id [optional]
	 * @return 
	 */
	function submitPage($id=FALSE)
	{
		global $sql, $tp, $e107cache, $admin_log, $e_event;
		$emessage = eMessage::getInstance();
		
		$insert_array = array();
		
		foreach($this->fields as $key=>$att)
		{
			if($att['forced']!=TRUE)
			{
				$insert_array[$key] = $_POST[$key];
			}
		}
			
		if($id)
		{
			$insert_array['WHERE'] = $this->primary." = ".$id;
			$status = $sql->db_Update($this->table,$insert_array) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
			$message = LAN_UPDATED;	

		}
		else
		{
			$status = $sql->db_Insert($this->table,$insert_array) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
			$message = LAN_CREATED;	
		}
		

		$emessage->add($message, $status);		
	}

	function deleteRecord($id)
	{
		if(!$id || !$this->primary || !$this->table)
		{
			return;
		}
		
		$emessage = eMessage::getInstance();
		$sql = e107::getDb();
		
		$query = $this->primary." = ".$id;
		$status = $sql->db_Delete($this->table,$query) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
		$message = LAN_DELETED;
		$emessage->add($message, $status);		
	}

	function optionsPage()
	{
		global $e107, $pref, $frm, $emessage;

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


	function show_options($action)
	{
		$action = varset($_GET['mode'],'list');

		$var['list']['text'] = $this->listCaption;
		$var['list']['link'] = e_SELF."?mode=list";
		$var['list']['perm'] = "0";

		$var['create']['text'] = $this->createCaption;
		$var['create']['link'] = e_SELF."?mode=create";
		$var['create']['perm'] = 0;

/*
		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";
		$var['options']['perm'] = "0";*/

		e_admin_menu($this->pluginTitle, $action, $var);
	}
}

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