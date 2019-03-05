<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Ban List Management
 *
 *
*/

require_once('../class2.php');

if (!getperms('4'))
{
	e107::redirect('admin');
	exit();
}

require_once(e_HANDLER.'iphandler_class.php');		// This is probably already loaded in class2.php

e107::coreLan('banlist', true);

e107::js('footer-inline', "

		$('#useip').click(function (event) {

			var id = $(this).attr('data-ip');
			$('#banlist-ip').val(id);
			event.preventDefault();
		});

");


class banlist_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'banlist_ui',
			'path' 			=> null,
			'ui' 			=> 'banlist_form_ui',
			'uipath' 		=> null
		),
		'white'	=> array(
			'controller' 	=> 'banlist_ui',
			'path' 			=> null,
			'ui' 			=> 'banlist_form_ui',
			'uipath' 		=> null
		),
		'failed'	=> array(
			'controller' 	=> 'failed_ui',
			'path' 			=> null,
			'ui' 			=> 'failed_form_ui',
			'uipath' 		=> null
		),

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> BANLAN_122, 'perm' => '4'),
		'main/create'		=> array('caption'=> BANLAN_123, 'perm' => '4'),
		'other'            => array('divider'=>true),
		// Use FILTER to view whitelist instead. 
		'white/list'		=> array('caption'=> BANLAN_52, 'perm' => '4'),
		'white/create'		=> array('caption'=> BANLAN_53, 'perm' => '4'),

		'other1'            => array('divider'=>true),

		'failed/list'       => array('caption'=> ADLAN_146, 'perm'=>'4'),

		'other2'            => array('divider'=>true),
		'main/transfer'		=> array('caption'=> BANLAN_35, 'perm' => '4'),
		'main/times'		=> array('caption'=> BANLAN_15, 'perm' => '0'),
		'main/options'		=> array('caption'=> LAN_OPTIONS, 'perm' => '0'),
	//	'main/banlog'		=> array('caption'=> BANLAN_81, 'perm' => '0'),
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'						
	);	
	
	protected $menuTitle = BANLAN_16;

	protected $adminMenuIcon = 'e-banlist-24';
}




				
class banlist_ui extends e_admin_ui
{
			
		protected $pluginTitle		= BANLAN_16;
		protected $eventName		= 'ban';
		protected $table			= 'banlist';
		protected $pid				= 'banlist_id'; 
		protected $perPage 			= 10;
		protected $listQry          = "SELECT * FROM `#banlist` WHERE banlist_bantype != 100 ";
		protected $listOrder		= 'banlist_datestamp DESC';

		protected $fields 	= array (  
		  'checkboxes' 			=>   array ( 'title' => '', 				'type' => null, 		'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'banlist_id'			 =>  array ( 'title' => LAN_ID, 			'data' => 'int',        'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banlist_ip' 			=>   array ( 'title' => BANLAN_126, 			'type' => 'method', 		'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banlist_bantype' 	=>   array ( 'title' => LAN_TYPE, 			'type' => 'method', 	'data' => 'str', 'width' => 'auto', 'filter'=>true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banlist_datestamp' 	=>   array ( 'title' => LAN_DATESTAMP, 		'type' => 'datestamp', 	'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => 'auto=1&hidden=1&readonly=1', 'class' => 'left', 'thclass' => 'left',  ),
		  'banlist_banexpires' 	=>   array ( 'title' => BANLAN_124,	 		'type' => 'method', 	'data' => 'int', 'inline'=>true, 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banlist_admin' 		=>   array ( 'title' => 'Admin', 			'type' => 'text', 	    'data' => 'int', 'noedit'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'banlist_reason' 		=>   array ( 'title' => BANLAN_7, 			'type' => 'text', 		'data' => 'str', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => 'constant=1', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		  'banlist_notes' 		=>   array ( 'title' => BANLAN_19, 			'type' => 'text', 		'data' => 'str', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		  'options' 			=>   array ( 'title' => LAN_OPTIONS, 		'type' => '', 			'data' => '', 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('checkboxes', 'banlist_ip', 'banlist_bantype', 'banlist_datestamp', 'banlist_banexpires', 'banlist_reason', 'banlist_notes', 'options');

		
	//	protected $pref = array(
	//		'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
	//		'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
	//		'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
	//	);


		function CreateObserver()
		{
			parent::CreateObserver();
			$this->fields['banlist_ip']['title']= BANLAN_5;
		}

		function EditObserver()
		{
			parent::EditObserver();
			$this->fields['banlist_ip']['title']= BANLAN_5;
		}

		
		// optional
		public function init()
		{

			if($this->getMode() == 'white')
			{
				if($this->getAction() == 'list')
				{
					$this->listQry = "SELECT * FROM `#banlist` WHERE banlist_bantype = 100 ";
				}


				if($this->getAction() == 'create')
				{
					$myip = e107::getIPHandler()->getIP(true);
					$this->fields['banlist_ip']['writeParms']['tdClassRight']  = 'form-inline';
					$this->fields['banlist_ip']['writeParms']['pre'] = "<div class='input-append'>";
					$this->fields['banlist_ip']['writeParms']['post'] = "<button class='btn btn-primary' id='useip' data-ip='{$myip}'>".BANLAN_125."</button></div>"; // USERIP;
				}

			}


			if (isset($_POST['update_ban_prefs']))		// Update ban messages
			{
				$this->timesPageSave(); 	
			}
		}


		public function beforeCreate($new_data, $old_data, $id)
		{
			$new_data['banlist_admin'] = ADMINID;

			if(filter_var($new_data['banlist_ip'], FILTER_VALIDATE_IP)) // check it's an IP
			{
				$new_data['banlist_ip'] = e107::getIPHandler()->ipEncode($new_data['banlist_ip']);
			}

			return $new_data;
		}

		public function beforeUpdate($new_data, $old_data, $id)
		{
			$new_data['banlist_admin'] = ADMINID;

			if(filter_var($new_data['banlist_ip'], FILTER_VALIDATE_IP)) // check it's an IP
			{
				$new_data['banlist_ip'] = e107::getIPHandler()->ipEncode($new_data['banlist_ip']);
			}

			return $new_data;
		}





		public function afterCreate($new_data, $old_data, $id)
		{
			e107::getIPHandler()->regenerateFiles();
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			e107::getIPHandler()->regenerateFiles();
		}

		public function afterDelete($deleted_data, $id, $deleted_check)
		{

			e107::getIPHandler()->regenerateFiles();
		}


		public function addPage()
		{
			//$ns = e107::getRender();
			//$text = 'Hello World!';
			//$ns->tablerender('Hello',$text);				
		}

		
		protected function transferPage()
		{

			$ipAdministrator = new banlistManager;

			// Character options for import & export
			$separator_char = array(1 => ',', 2 => '|');
			$quote_char = array(1 => '(none)', 2 => "'", 3 => '"');

			$frm = e107::getForm();
			$mes = e107::getMessage();

			$error = false;

			if(isset($_POST['ban_import']))  // Got a file to import
			{
				require_once(e_HANDLER . 'upload_handler.php');

				if(($files = process_uploaded_files(e_UPLOAD, false, array('overwrite' => true, 'max_file_count' => 1, 'file_mask' => 'csv'))) === false)
				{ // Invalid file
					$error = true;
					$mes->addError(BANLAN_47);
				}

				if(empty($files) || vartrue($files[0]['error']))
				{
					$error = true;
					if(varset($files[0]['message']))
					{
						$mes->addError($files[0]['message']);
					}
				}

				if(!$error) // Got a file of some sort
				{
					$message = process_csv(e_UPLOAD . $files[0]['name'],
						intval(varset($_POST['ban_over_import'], 0)),
						intval(varset($_POST['ban_over_expiry'], 0)),
						$separator_char[intval(varset($_POST['ban_separator'], 1))],
						$quote_char[intval(varset($_POST['ban_quote'], 3))]);
					banlist_adminlog('07', 'File: ' . e_UPLOAD . $files[0]['name'] . '<br />' . $message);
				}

			}

			$text = "
				<form method='post' action='" . e_ADMIN_ABS . "banlist_export.php' id='core-banlist-transfer-form' >
					<fieldset id='core-banlist-transfer-export'>
						<legend>" . BANLAN_40 . "</legend>
						<table class='table adminlist'>
							<colgroup>
								<col style='width:30%' />
								<col style='width:30%' />
								<col style='width:40%' />
							</colgroup>
							<tbody>
								<tr>
									<th colspan='2'>" . BANLAN_36 . "</th>
									<th>&nbsp;</th>
								</tr>
				";


			foreach($ipAdministrator->getValidReasonList() as $i) //FIXME $frm->label()
			{
				$text .= "
								<tr>
								<td colspan='3'>
									" . $frm->checkbox("ban_types[{$i}]", $i) . $frm->label($ipAdministrator->getBanTypeString($i, false), "ban_types[{$i}]", $i) . "
									<span class='field-help'>(" . $ipAdministrator->getBanTypeString($i, true) . ")</span>
								</td></tr>
				";
			}

			$text .= "<tr>
				<td>" . BANLAN_79 . "</td>
				<td>" . $frm->select('ban_separator', $separator_char) . ' ' . BANLAN_37 . "</td>
			<td>" . $frm->select('ban_quote', $quote_char) . ' ' . BANLAN_38 . "</td></tr>";

			$text .= "
	
							</tbody>
						</table>
						<div class='buttons-bar center'>" . $frm->admin_button('ban_export', BANLAN_39, 'export', BANLAN_39) . "</div>
							<input type='hidden' name='e-token' value='" . e_TOKEN . "' />
					</fieldset>
				</form>
			";

			// Now do the import options
			$text .= "
				<form enctype='multipart/form-data' method='post' action='" . e_SELF . "?transfer' id='ban_import_form' >
					<fieldset id='core-banlist-transfer-import'>
						<legend>" . BANLAN_41 . "</legend>
						<table class='table adminlist'>
							<colgroup>
								<col style='width:30%' />
								<col style='width:30%' />
								<col style='width:40%' />
							</colgroup>
							<tbody>
								<tr>
									<th colspan='2'>" . BANLAN_42 . "</th>
									<th>&nbsp;</th>
								</tr>
								<tr>
									<td colspan='3'>" . $frm->checkbox('ban_over_import', 1, '', array('label' => BANLAN_43)) . "</td>
								</tr>
								<tr>
									<td colspan='3'>" . $frm->checkbox('ban_over_expiry', 1, '', array('label' => BANLAN_44)) . "</td>
								</tr>
								<tr>
									<td>" . BANLAN_46 . "</td>
									<td colspan='2'>
										" . $frm->file('file_userfile[]', array('size' => '40')) . "
									</td>
								</tr>
								<tr>
				<td>" . BANLAN_80 . "</td>
				<td>" . $frm->select('ban_separator', $separator_char) . ' ' . BANLAN_37 . "</td>
				<td>" . $frm->select('ban_quote', $quote_char) . ' ' . BANLAN_38 . "</td></tr>
					</tbody>
						</table>
						<div class='buttons-bar center'>
						" . $frm->admin_button('ban_import', BANLAN_45, 'import') . $frm->token(). "
						</div>
	
	
					</fieldset>
				</form>
			";

			return $mes->render() . $text;
		}
		
	
		
		private function timesPageSave()
		{


			$ipAdministrator = new banlistManager;
			$tp = e107::getParser();
			$changed = false;

			$pref = array();

			$reasonList = $ipAdministrator->getValidReasonList();
			foreach ($ipAdministrator->getValidReasonList() as $bt)
			{
				$i = abs($bt) + 1;		// Forces a single-digit positive number for part of field name
				$t1 = $tp->toDB(varset($_POST['ban_text_'.($i)],''));
				$t2 = intval(varset($_POST['ban_time_'.($i)],0));
				if (!isset($pref['ban_messages'][$bt]) || ($pref['ban_messages'][$bt] != $t1))
				{
					$pref['ban_messages'][$bt] = $t1;
					$changed = TRUE;
				}
				if (!isset($pref['ban_durations'][$bt]) || ($pref['ban_durations'][$bt] != $t2))
				{
					$pref['ban_durations'][$bt] = $t2;
					$changed = TRUE;
				}
			}

			if ($changed && !empty($pref))
			{
			// @todo write actual prefs changes to log file (different methods for prefs?)
				e107::getConfig()->setPref($pref)->save(); 
			// 	save_prefs();
				/*****************************************
					Write messages and times to disc file
				 *****************************************/
				$ipAdministrator->writeBanMessageFile();
				banlist_adminlog('08','');
				//$ns->tablerender(BANLAN_9, "<div style='text-align:center'>".BANLAN_33.'</div>');
			//	$mes->addSuccess(BANLAN_33);
			}	
			
			
		}



		
		protected function timesPage()
		{
			if (!getperms('0'))
			{
				return;
			}
			
			$pref = e107::getPref();
			$tp = e107::getParser();		
			$frm = e107::getForm();
			$mes = e107::getMessage();
			
			$ipAdministrator = new banlistManager;
						
			$text = '';
			if ((!isset($pref['ban_messages'])) || !is_array($pref['ban_messages']))
			{
				foreach ($ipAdministrator->getValidReasonList() as $bt)
				{
					$pref['ban_messages'][$bt] = '';
				}
			}
			if ((!isset($pref['ban_durations'])) || !is_array($pref['ban_durations']))
			{
				foreach ($ipAdministrator->getValidReasonList() as $bt)
				{
					$pref['ban_durations'][$bt] = 0;
				}
			}
	
			$text .= "
				<form method='post' action='".e_SELF.'?'.e_QUERY."' id='ban_options'>
					<fieldset id='core-banlist-times'>
						<legend class='e-hideme'>".BANLAN_77."</legend>
						<table class='table adminlist'>
							<colgroup>
								<col style='width: 20%' />
								<col style='width: 65%' />
								<col style='width: 15%' />
							</colgroup>
							<thead>
								<tr>
									<th>".BANLAN_28."</th>
									<th>".BANLAN_29."<br />".BANLAN_31."</th>
									<th class='center last'>".BANLAN_30."</th>
								</tr>
							</thead>
							<tbody>
			";
			foreach ($ipAdministrator->getValidReasonList() as $bt)
			{
				$i = abs($bt) + 1;		// Forces a single-digit positive number
				$text .= "
						<tr>
							<td>
								<strong>".$ipAdministrator->getBanTypeString($bt, FALSE)."</strong>
								<div class='field-help'>".$ipAdministrator->getBanTypeString($bt, TRUE)."</div>
							</td>
							<td class='left'>
								".$frm->textarea('ban_text_'.($i), $pref['ban_messages'][$bt], 4, 120, array('size'=>'xxlarge'))."
							</td>
							<td class='center'>".ban_time_dropdown('', BANLAN_32, $pref['ban_durations'][$bt], 'ban_time_'.($i))."</td>
						</tr>
					";
			}
			$text .= "
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".$frm->admin_button('update_ban_prefs', LAN_UPDATE, 'update')."
							<input type='hidden' name='e-token' value='".e_TOKEN."' />
						</div>
					</fieldset>
				</form>
				";
	
			echo $mes->render().$text; 
		}		


		private function drop_box($box_name, $curval)
		{
			$frm = e107::getForm();

			$opts = array(50, 100, 150, 200, 250, 300, 400, 500);
			$ret = $frm->select_open($box_name, array('class' => 'tbox'));
			foreach ($opts as $o)
			{
				$ret .= $frm->option($o, $o, ($curval == $o));
			}
			$ret .= "</select>\n";
			return $ret;
		}

		
		protected function optionsPage()
		{
			if (!getperms('0'))
			{
				exit();
			}

			$mes = e107::getMessage();
			$tp = e107::getParser();
			$sql = e107::getDb();
			$frm = e107::getForm();
			$pref = e107::getPref();


			if (isset($_POST['update_ban_options']))
			{
				$pref['enable_rdns']            = intval($_POST['ban_rdns_on_access']);
				$pref['enable_rdns_on_ban']     = intval($_POST['ban_rdns_on_ban']);
				$pref['ban_max_online_access']  = intval($_POST['ban_access_guest']).','.intval($_POST['ban_access_member']);
				$pref['ban_retrigger']          = intval($_POST['ban_retrigger']);
				$pref['ban_date_format']        = $tp->toDB($_POST['ban_date_format']);

				e107::getConfig()->setPref($pref)->save(true,true,true);
			}

			if (isset($_POST['remove_expired_bans']))
			{
				$result = $sql->delete('banlist',"`banlist_bantype` < ".eIPHandler::BAN_TYPE_WHITELIST." AND `banlist_banexpires` > 0 AND `banlist_banexpires` < ".time());
				banlist_adminlog('12', $result);
				$mes->addSuccess(str_replace('[y]', $result, BANLAN_48));
			}

			list($ban_access_guest, $ban_access_member) = explode(',', varset($pref['ban_max_online_access'], '100,200'));
			$ban_access_member = max($ban_access_guest, $ban_access_member);


			$text = "
				<form method='post' action='".e_SELF."?mode=main&action=options'>
					<fieldset id='core-banlist-options'>
						<legend>".BANLAN_72."</legend>
						<table class='table adminform'>
							<colgroup>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>
							<tbody>
								<tr>
									<td>".BANLAN_63."</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('ban_rdns_on_access', 1, $pref['enable_rdns'] == 1)."
											<div class='field-help'>".BANLAN_65."</div>
										</div>
									</td>
								</tr>
								<tr>
									<td>".BANLAN_64."</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('ban_rdns_on_ban', 1, $pref['enable_rdns_on_ban'] == 1)."
											<div class='field-help'>".BANLAN_66."</div>
										</div>
									</td>
								</tr>
								<tr>
									<td>".BANLAN_67."</td>
									<td>
										<div class='field-spacer'>".$this->drop_box('ban_access_guest', $ban_access_guest).BANLAN_70."</div>
										<div class='field-spacer'>".$this->drop_box('ban_access_member', $ban_access_member).BANLAN_69."</div>
										<div class='field-help'>".BANLAN_68."</div>
									</td>
								</tr>
								<tr>
									<td>".BANLAN_71."</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('ban_retrigger', 1, $pref['ban_retrigger'] == 1)."
											<div class='field-help'>".BANLAN_73."</div>
										</div>
									</td>
								</tr>
	
								<tr>
								  <td>".BANLAN_91."</td>
								  <td>
								  ".$frm->text('ban_date_format', varset($pref['ban_date_format'], '%H:%M %d-%m-%y'), 40)."
								  <div class='field-help'>".BANLAN_92."</div>
								  </td>
								</tr>
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".$frm->admin_button('update_ban_options', LAN_UPDATE, 'update')."
							<input type='hidden' name='e-token' value='".e_TOKEN."' />
						</div>
					</fieldset>
					<fieldset id='core-banlist-options-ban'>
						<legend>".BANLAN_74."</legend>
						<table class='table adminform'>
							<colgroup>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>
							<tbody>
								<tr>
									<td>".BANLAN_75."</td>
									<td>
										".$frm->admin_button('remove_expired_bans', BANLAN_76, 'delete')."
										<input type='hidden' name='e-token' value='".e_TOKEN."' />
									</td>
								</tr>
							</tbody>
						</table>
					</fieldset>
				</form>
			";

			return $text;
		}				

		protected function banlogPage()
		{
			//FIXME Put LogPage code in here. 
		}	

}
				


class banlist_form_ui extends e_admin_form_ui
{

	// Custom Method/Function
	function banlist_reason($curVal,$mode) //TODO
	{

		switch($mode)
		{
			case 'read': // List Page

				break;

			case 'write': // Edit Page
				return $this->renderElement('banlist_reason', $curVal, array());
				break;

			case 'filter':
			case 'batch':

				break;
		}
	}

	// Custom Method/Function
	function banlist_ip($curVal,$mode) //TODO
	{

		if(!empty($curVal))
		{
			$tmp = explode(":",$curVal);

			if(count($tmp) === 8)
			{
				$curVal = e107::getIPHandler()->ipDecode($curVal);
			}
		}

		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
				break;

			case 'write': // Edit Page

				return $this->text('banlist_ip', $curVal, array());
				break;

			case 'filter':
			case 'batch':

				break;
		}
	}

	
	// Custom Method/Function 
	function banlist_bantype($curVal,$mode)
	{
	
		$ipAdministrator = new banlistManager;
		
		// print_a($ipAdministrator->banTypes);
		 		
		switch($mode)
		{
			case 'read': // List Page
				return "<div class='nowrap' title='".$ipAdministrator->getBanTypeString($curVal, TRUE)."'>".$ipAdministrator->getBanTypeString($curVal, FALSE)."</div>";					
			break;
			
			case 'write': // Edit Page

				if ($this->getController()->getMode() == 'white')
				{
					return $this->hidden('banlist_bantype',eIPHandler::BAN_TYPE_WHITELIST)."<span class='label label-success'>".BANLAN_120."</span>";
				}
				elseif($this->getController()->getAction() == 'create')
				{
					return $this->hidden('banlist_bantype',eIPHandler::BAN_TYPE_MANUAL)."<span class='label label-important label-danger'>".BANLAN_121."</span>";
				}



				return $this->selectbox('banlist_bantype',$ipAdministrator->banTypes, $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return  $ipAdministrator->banTypes; 
			break;
		}
	}

	
	// Custom Method/Function 
	function banlist_banexpires($curVal,$mode)
	{
		
		$pref = e107::getPref();
		$date = e107::getDate();
		$opts = $this->banexpires();
			 		
		switch($mode)
		{
			case 'read': // List Page
				$id = $this->getController()->getListModel()->get('banlist_ip');
			//	$val =  ($curVal ? strftime($pref['ban_date_format'], $curVal).(($curVal < time()) ? ' ('.BANLAN_34.')' : '') : LAN_NEVER); // ."<br />".$this->banexpires();
			//	$val .= " (".$curVal.")";
				// $mod = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
				$val = vartrue($curVal) ? $date->computeLapse(time(), $curVal) : LAN_NEVER;

				$val = str_replace("ago", "", $val); // quick fix for the 'ago'.
				
				if(vartrue($curVal) && $curVal < time())
				{
					$val = 	BANLAN_34;
				}
									
				$source = str_replace('"',"'",json_encode($opts));
				return "<a class='e-tip e-editable' data-name='banlist_banexpires' data-source=\"".$source."\"  data-type='select' data-pk='".$id."' data-url='".e_SELF."?mode=&amp;action=inline&amp;id={$id}&amp;ajax_used=1' href='#'>".$val."</a>";
				
			break;
			
			case 'write': // Edit Page
				if(!empty($curVal))
				{
					$opts[$curVal] = e107::getParser()->toDate($curVal, 'short');
				}

				return $this->selectbox('banlist_banexpires',$opts, $curVal);
				// return $frm->text('banlist_banexpires',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  false;
			break;
		}
	}


	function banexpires()
	{
		$intervals = array(0, 1, 2, 3, 6, 8, 12, 24, 36, 48, 72, 96, 120, 168, 336, 672);

		$opts = array();

		foreach ($intervals as $i)
		{
			$words = "";
			
			if ($i == 0)
			{
				$opts[$i]  = LAN_NEVER;
				continue;
			}
			elseif (($i % 24) == 0 && $i !== 24)
			{
				$words = floor($i / 24).' '.BANLAN_23;
			}
			else
			{
				$words = $i.' '.BANLAN_24;
			}
			
			$calc = time() + ($i * 60 * 60);
			
			$opts[$calc] = $words; 
		}
	//	print_a($opts);
		return $opts;		
	}

}




	class failed_ui extends e_admin_ui
	{

		protected $pluginTitle		= BANLAN_16;
		protected $pluginName		= 'failed_login';
		protected $table			= 'generic';
		protected $pid				= 'gen_id';
		protected $perPage 			= 10;
		protected $listQry			= "SELECT * FROM `#generic` WHERE gen_type='failed_login' ";
		protected $listOrder        = "gen_datestamp DESC";

		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		                                    'gen_id' 				=> array ( 'title' => LAN_ID,	 'nolist'=>true,	'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			//	  'gen_type' 			=> array ( 'title' => LAN_BAN, 	'type' => 'method', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		                                    'gen_datestamp' 		=> array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		                                    'gen_chardata' 		=> array ( 'title' => LAN_DESCRIPTION, 'type' => 'method', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left', 'filter'=>true ),

			//	  'gen_user_id' 		=> array ( 'title' => LAN_BAN, 'type' => 'method', 'batch'=>true, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		                                    'gen_ip' 				=> array ( 'title' => LAN_IP, 'type' => 'ip', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			//	  'gen_intdata' 		=> array ( 'title' =>  LAN_BAN, 'type' => 'method', 'batch'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		                                    'options'				=> array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'readParms'=>'edit=0'  ),
		);

		protected $fieldpref = array('gen_datestamp', 'gen_ip', 'gen_chardata');

		protected $batchOptions = array();

		// optional
		public function init()
		{
			if($_POST['etrigger_batch'] == 'gen_intdata__1' && count($_POST['e-multiselect'])) // Do we need BAN here?
			{
				$dels = implode(',',$_POST['e-multiselect']);
				//$e107::getDb()->insert('banlist',
			}

			$allFailedTotal = e107::getDB()->count('generic', '(*)', "gen_type='failed_login'");

			$this->batchOptions = array('delete-all'=>str_replace('[x]', $allFailedTotal, BANLAN_127));

			if(!empty($_POST['etrigger_batch']) && $_POST['etrigger_batch'] == "delete-all")
			{
				$this->deleteAllFailed();
			}

		
		}

		private function deleteAllFailed()
		{

			if(e107::getDB()->delete('generic', "gen_type='failed_login'"))
			{
				e107::getMessage()->addSuccess(LAN_DELETED);
			}
		}

		public function afterDelete($data)
		{
			//	$sql2->db_Delete('banlist', "banlist_ip='{$banIP}'");
		}

	}



	class failed_form_ui extends e_admin_form_ui
	{


		// Custom Method/Function
		function gen_intdata($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return $curVal;
					break;

				case 'write': // Edit Page
					return $frm->text('gen_type',$curVal);
					break;

				case 'filter':
				case 'batch':
					return  array(1=>LAN_BAN);
					break;
			}
		}


		// Custom Method/Function
		function gen_chardata($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return str_replace(":::","<br />",$curVal);
					break;

				case 'write': // Edit Page
					return $frm->text('gen_chardata',$curVal);
					break;

				case 'filter':
				case 'batch':
					//	return  $array;
					break;
			}
		}

	}


	new banlist_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();


require_once(e_ADMIN.'footer.php');
exit;



// Unused code below, but left here for reference.



$e_sub_cat = 'banlist';
require_once('auth.php');
$frm = e107::getForm();
// $frm = new e_form(true);

$mes = e107::getMessage();

$pref = e107::getPref();

// Set a default to avoid issues with legacy systems
if (!isset($pref['ban_date_format'])) $pref['ban_date_format'] = '%H:%M %d-%m-%y';

$ipAdministrator = new banlistManager;

// Character options for import & export
$separator_char = array(1 => ',', 2 => '|');
$quote_char = array(1 => '(none)', 2 => "'", 3 => '"');


$action = 'list';
if (e_QUERY)
{
	$tmp = explode('-', e_QUERY);		// Use '-' instead of '.' to avoid confusion with IP addresses
	$action = $tmp[0];
	$sub_action = varset($tmp[1], '');
	if ($sub_action) $sub_action = preg_replace('/[^\w*@\.:]*/', '', urldecode($sub_action));
	$id = intval(varset($tmp[2], 0));
	unset($tmp);
}

if($_GET['action']) // Temporary Fix. 
{
	$action = $_GET['action'];	
}





$writeBanFile = FALSE;
if (isset($_POST['ban_ip']))
{
	$_POST['ban_ip'] = trim($_POST['ban_ip']);
	$new_ban_ip = preg_replace('/[^\w*@\.:]*/', '', urldecode($_POST['ban_ip']));
	if ($new_ban_ip != $_POST['ban_ip'])
	{
		$message = BANLAN_27.' '.$new_ban_ip;
		//$ns->tablerender(BANLAN_9, $message);
		$mes->add(BANLAN_33, $message);
		$_POST['ban_ip'] = $new_ban_ip;
	}

	if (isset($_POST['entry_intent']) && (isset($_POST['add_ban']) || isset($_POST['update_ban'])) && $_POST['ban_ip'] != "" && strpos($_POST['ban_ip'], ' ') === false)
	{
		/*	$_POST['entry_intent'] says why we're here:
				'edit' 	- Editing blacklist
				'add'	- Adding to blacklist
				'whedit' - Editing whitelist
				'whadd'	- Adding to whitelist
		*/
		if(e107::getIPHandler()->whatIsThis($new_ban_ip) == 'ip')
		{
			$new_ban_ip = e107::getIPHandler()->IPencode($new_ban_ip, TRUE); // Normalise numeric IP addresses (allow wildcards)
		}
		$new_vals = array('banlist_ip' => $new_ban_ip);
		if (isset($_POST['add_ban']))
		{
			$new_vals['banlist_datestamp'] = time();
			if ($_POST['entry_intent'] == 'add') $new_vals['banlist_bantype'] = eIPHandler::BAN_TYPE_MANUAL;		// Manual ban
			if ($_POST['entry_intent'] == 'whadd') $new_vals['banlist_bantype'] = eIPHandler::BAN_TYPE_WHITELIST;
		}
		$new_vals['banlist_admin'] = ADMINID;
		$new_vals['banlist_reason'] = $tp->toDB(varset($_POST['ban_reason'], ''));
		$new_vals['banlist_notes'] = $tp->toDB($_POST['ban_notes']);
		if (isset($_POST['ban_time']) && is_numeric($_POST['ban_time']) && (($_POST['entry_intent']== 'edit') || ($_POST['entry_intent'] == 'add')))
		{
			$bt = intval($_POST['ban_time']);
			$new_vals['banlist_banexpires'] = $bt ? time() + ($bt*60*60) : 0;
		}
		if (isset($_POST['add_ban']))
		{  // Insert new value - can just pass an array
			e107::getMessage()->addAuto($sql->db_Insert('banlist', $new_vals), 'insert');
			if ($_POST['entry_intent'] == 'add')
			{
				banlist_adminlog('01', $new_vals['banlist_ip']);		// Write to banlist
			}
			else
			{
				banlist_adminlog('04', $new_vals['banlist_ip']);		// Write to whitelist
			}
		}
		else
		{  // Update existing value
			$qry = '';
			$spacer = '';
			foreach ($new_vals as $k => $v)
			{
				$qry .= $spacer."`{$k}`='$v'";
				$spacer = ', ';
			}
			e107::getMessage()->addAuto($sql->db_Update('banlist', $qry." WHERE banlist_ip='".$_POST['old_ip']."'"));
			if ($_POST['entry_intent'] == 'edit')
			{
				banlist_adminlog('09',$new_vals['banlist_ip']);
			}
			else
			{
				banlist_adminlog('10',$new_vals['banlist_ip']);
			}
		}
		unset($ban_ip);
		$writeBanFile = TRUE;
	}
}



// Remove a ban
if (($action == 'remove' || $action == 'whremove') && isset($_POST['ban_secure'])) 
{
	$sql->db_Delete('generic', "gen_type='failed_login' AND gen_ip='{$sub_action}'");
	e107::getMessage()->addAuto($sql->db_Delete('banlist', "banlist_ip='{$sub_action}'"), 'delete');
	if ($action == "remove")
	{
		$action = 'list';
		banlist_adminlog('02', $sub_action);
	}
	else
	{
		$action = 'white';
		banlist_adminlog('05', $sub_action);
	}
	$writeBanFile = TRUE;
}



// Update the ban expiry time/date - timed from now (only done on banlist)
if ($action == 'newtime')
{
	$end_time = $id ? time() + ($id*60*60) : 0;
	e107::getMessage()->addAuto($sql->db_Update('banlist', 'banlist_banexpires='.intval($end_time)." WHERE banlist_ip='".$sub_action."'"));
	banlist_adminlog('03', $sub_action);
	$action = 'list';
	$writeBanFile = TRUE;
}


if ($writeBanFile)
{
/************************************************
		update list of banned IPs
*************************************************/
	$ipAdministrator->writeBanListFiles('ip,htaccess');
	if (!$ipAdministrator->doesMessageFileExist())
	{
		$ipAdministrator->writeBanMessageFile();		// Message file must exist - may not on fresh site
		banlist_adminlog('08','');
		$mes->addSuccess(LAN_UPDATED);
	}
}



/**
 *	@todo - eliminate extract();
 */
// Edit modes - get existing entry
if ($action == 'edit' || $action == 'whedit')
{
	$sql->db_Select('banlist', '*', "banlist_ip='{$sub_action}'");
	$row = $sql->db_Fetch();
	extract($row);				//FIXME - kill extract()
}
else
{
	unset($banlist_ip, $banlist_reason);
	if (e_QUERY && ($action == 'add' || $action == 'whadd') && strpos($_SERVER["HTTP_REFERER"], "userinfo"))
	{
		$banlist_ip = $sub_action;
	}
}



/**
 *	Create dropdown with options for ban time - uses internal fixed list of reasonable values
 */
function ban_time_dropdown($click_js = '', $zero_text = LAN_NEVER, $curval = -1, $drop_name = 'ban_time')
{
	$frm = e107::getForm();
	$intervals = array(0, 1, 2, 3, 6, 8, 12, 24, 36, 48, 72, 96, 120, 168, 336, 672);

	$ret = $frm->select_open($drop_name, array('other' => $click_js, 'id' => false));
	$ret .= $frm->option('&nbsp;', '');
	foreach ($intervals as $i)
	{
		if ($i == 0)
		{
			$words = $zero_text ? $zero_text : LAN_NEVER;
		}
		elseif (($i % 24) == 0)
		{
			$words = floor($i / 24).' '.BANLAN_23;
		}
		else
		{
			$words = $i.' '.BANLAN_24;
		}
		$ret .= $frm->option($words, $i, ($curval == $i));
	}
	$ret .= '</select>';
	return $ret;
}




/**
 *	Create generic dropdown from array of data
 */
function select_box($name, $data, $curval = FALSE)
{
	global $frm;

	$ret = $frm->select_open($name, array('class' => 'tbox', 'id' => false));
	foreach ($data as $k => $v)
	{
		$ret .= $frm->option($v, $k, ($curval !== FALSE) && ($curval == $k));
	}
	$ret .= "</select>\n";
	return $ret;
}



/**
 *	Create dropdown with options for access counts before ban - uses internal fixed list of reasonable values
 */
function drop_box($box_name, $curval)
{
	global $frm;

	$opts = array(50, 100, 150, 200, 250, 300, 400, 500);
	$ret = $frm->select_open($box_name, array('class' => 'tbox'));
	foreach ($opts as $o)
	{
		$ret .= $frm->option($o, $o, ($curval == $o));
	}
	$ret .= "</select>\n";
	return $ret;
}



$text = '';


switch ($action)
{
	case 'banlog' :
		if(!getperms('0')) exit;
		if (isset($_POST['delete_ban_log']))
		{
			$message = ($ipAdministrator->deleteLogFile() ? BANLAN_89 : BANLAN_90);
			e107::getRender()->tablerender(BANLAN_88, "<div style='text-align:center; font-weight:bold'>".$message."</div>"); // FIXME
		}
		$from = 0;
		$amount = 20;		// Number per page - could make configurable later if required
		if ($sub_action) $from = intval($sub_action);

// @todo format form the 0.8 way
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?banlog-".$from."'>
		<table class='table adminform'>
		<colgroup>
			<col style='width:20%; vertical-align:top;' />
			<col style='width:30%; vertical-align:top;' />
			<col style='width:30%; vertical-align:top;' />
			<col style='width:30%; vertical-align:top;' />
		</colgroup>";

		// Get entries
		$banLogEntries = $ipAdministrator->getLogEntries($from, $amount, $num_entry);
		if (count($banLogEntries) == 0)
		{
		  $text .= "<tbody><tr><td colspan='4'>".BANLAN_82."</td></tr>";
		  $num_entry = 0;
		}
		else
		{
			$text .= "<thead><tr><td class='fcaption'>".BANLAN_83."</td><td class='fcaption'>".BANLAN_84."</td>
				<td class='fcaption'>".BANLAN_7."</td><td class='fcaption'>".BANLAN_85."</td></tr></thead><tbody>";
			foreach ($banLogEntries as $ban)
			{
				$row = $ipAdministrator->splitLogEntry($ban);
				$text .= "
					<tr>
					<td class='forumheader3'>".strftime($pref['ban_date_format'], $row['banDate'])."</td>
					<td class='forumheader3'>".e107::getIPHandler()->ipDecode($row['banIP'])."</td>
					<td class='forumheader3'>".$ipAdministrator->getBanTypeString($row['banReason'], FALSE)."</td>
					<td class='forumheader3'>".$row['banNotes']."</td>
					</tr>";
			}  // End while

			// Next-Previous. ==========================
			if ($num_entry > $amount) 
			{
				$parms = "{$num_entry},{$amount},{$from},".e_SELF."?".$action.'-[FROM]';
				$text .= "<tr><td colspan='5' style='text-align:center'><br />".$tp->parseTemplate("{NEXTPREV={$parms}}".'<br /><br /></td></tr>');
			}
			$text .= "<tr><td colspan='4' style='text-align:center'>
						<input class='btn btn-default btn-secondary button' type='submit' name='delete_ban_log' value='".BANLAN_88."' />
						<input type='hidden' name='e-token' value='".e_TOKEN."' />
					</td>
					  </tr>";
		}
		$text .= "</tbody></table></form></div>";

		if (count($banLogEntries))
		{
			$text .= "&nbsp;&nbsp;&nbsp;".str_replace('[y]', $num_entry, BANLAN_87);
		}
		
		echo $text; 
	//	e107::getRender()->tablerender(BANLAN_16.SEP.BANLAN_86, $text);
		break;


	case 'options' :
		if (!getperms('0'))
			exit();
		if (isset($_POST['update_ban_options']))
		{
			$pref['enable_rdns'] = intval($_POST['ban_rdns_on_access']);
			$pref['enable_rdns_on_ban'] = intval($_POST['ban_rdns_on_ban']);
			$pref['ban_max_online_access'] = intval($_POST['ban_access_guest']).','.intval($_POST['ban_access_member']);
			$pref['ban_retrigger'] = intval($_POST['ban_retrigger']);
			$pref['ban_date_format'] = $tp->toDB($_POST['ban_date_format']);
			save_prefs();						// @todo FIXME log detail of changes. Right prefs to use?
			$mes->addSuccess(LAN_SETSAVED);
		}

		if (isset($_POST['remove_expired_bans']))
		{
			$result = $sql->db_Delete('banlist',"`banlist_bantype` < ".eIPHandler::BAN_TYPE_WHITELIST." AND `banlist_banexpires` > 0 AND `banlist_banexpires` < ".time());
			banlist_adminlog('12', $result);
			$mes->addSuccess(str_replace('[y]', $result, BANLAN_48));
		}

		list($ban_access_guest, $ban_access_member) = explode(',', varset($pref['ban_max_online_access'], '100,200'));
		$ban_access_member = max($ban_access_guest, $ban_access_member);
		$text = "
			<form method='post' action='".e_SELF."?options'>
				<fieldset id='core-banlist-options'>
					<legend>".BANLAN_72."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".BANLAN_63."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox('ban_rdns_on_access', 1, $pref['enable_rdns'] == 1)."
										<div class='field-help'>".BANLAN_65."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>".BANLAN_64."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox('ban_rdns_on_ban', 1, $pref['enable_rdns_on_ban'] == 1)."
										<div class='field-help'>".BANLAN_66."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>".BANLAN_67."</td>
								<td>
									<div class='field-spacer'>".drop_box('ban_access_guest', $ban_access_guest).BANLAN_70."</div>
									<div class='field-spacer'>".drop_box('ban_access_member', $ban_access_member).BANLAN_69."</div>
									<div class='field-help'>".BANLAN_68."</div>
								</td>
							</tr>
							<tr>
								<td>".BANLAN_71."</td>
								<td>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox('ban_retrigger', 1, $pref['ban_retrigger'] == 1)."
										<div class='field-help'>".BANLAN_73."</div>
									</div>
								</td>
							</tr>

							<tr>
							  <td>".BANLAN_91."</td>
							  <td>
							  ".$frm->text('ban_date_format', varset($pref['ban_date_format'], '%H:%M %d-%m-%y'), 40)."
							  <div class='field-help'>".BANLAN_92."</div>
							  </td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('update_ban_options', LAN_UPDATE, 'update')."
						<input type='hidden' name='e-token' value='".e_TOKEN."' />
					</div>
				</fieldset>
				<fieldset id='core-banlist-options-ban'>
					<legend>".BANLAN_74."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".BANLAN_75."</td>
								<td>
									".$frm->admin_button('remove_expired_bans', BANLAN_76, 'delete')."
									<input type='hidden' name='e-token' value='".e_TOKEN."' />
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</form>
		";
		
		echo $mes->render().$text; 
		
	//	e107::getRender()->tablerender(BANLAN_16.SEP.LAN_OPTIONS, $mes->render().$text);
		break;

	case 'times' :

			
	//	e107::getRender()->tablerender(BANLAN_16.SEP.BANLAN_77, $mes->render().$text);
		break;

	case 'edit' :		// Edit an existing ban
	case 'add' :		// Add a new ban
	case 'whedit' :		// Edit existing whitelist entry
	case 'whadd' :		// Add a new whitelist entry
	
		if(!E107_DEBUG_LEVEL)
		{
			break;	
		}
		RETURN;
	
		if (!isset($banlist_reason)) $banlist_reason = '';
		if (!isset($banlist_ip)) $banlist_ip = '';
		if (!isset($banlist_notes)) $banlist_notes = '';

		$page_title = array('edit' => BANLAN_60, 'add' => BANLAN_9, 'whedit' => BANLAN_59, 'whadd' => BANLAN_58);
		$rdns_warn = vartrue($pref['enable_rdns']) ? '' : '<div class="field-help error">'.BANLAN_12.'</div>';
		$next = ($action == 'whedit' || $action == 'whadd') ? '?white' : '?list';
		// Edit/add form first
		$text .= "
			<form method='post' action='".e_SELF.$next."'>
				<fieldset id='core-banlist-edit'>
					<legend class='e-hideme'>".$page_title[$action]."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>
									".BANLAN_5.":
									<div class='label-note'>
										".BANLAN_13."<a href='".e_ADMIN_ABS."users.php'>".E_16_CAT_USER."</a>
									</div>
								</td>
								<td>
									<input type='hidden' name='entry_intent' value='{$action}' />
									".$frm->text('ban_ip', e107::getIPHandler()->ipDecode($banlist_ip), 200)."
									{$rdns_warn}
								</td>
							</tr>
		";

		if (($action == 'add') || ($action == 'whadd') || ($banlist_bantype <= 1) || ($banlist_bantype >= eIPHandler::BAN_TYPE_WHITELIST))
		{ // Its a manual or unknown entry - only allow edit of reason on those
			$text .= "
							<tr>
								<td>".BANLAN_7.": </td>
								<td>
									".$frm->textarea('ban_reason', $banlist_reason, 4, 50)."
								</td>
							</tr>
			";
		}
		elseif ($action == 'edit')
		{
			$text .= "
							<tr>
								<td>".BANLAN_7.": </td>
								<td>{$banlist_reason}</td>
							</tr>
			";
		}

		if ($action == 'edit')
		{
			$text .= "
							<tr>
								<td>".BANLAN_28.": </td>
								<td>".$ipAdministrator->getBanTypeString($banlist_bantype, FALSE)." - ".$ipAdministrator->getBanTypeString($banlist_bantype, TRUE)."</td>
							</tr>
			";
		}

		$text .= "
							<tr>
								<td>".BANLAN_19.": </td>
								<td>
									".$frm->textarea('ban_notes', $banlist_notes, 4, 50)."
								</td>
							</tr>
		";

		if ($action == 'edit' || $action == 'add')
		{
			$inhelp = (($action == 'edit') ? '<div class="field-help">'.BANLAN_26.($banlist_banexpires ? strftime($pref['ban_date_format'], $banlist_banexpires) : LAN_NEVER).'</div>' : '');

			$text .= "
							<tr>
								<td>".BANLAN_18.": </td>
								<td>".ban_time_dropdown().$inhelp."</td>
							</tr>
			";
		}

		$text .= "
						</tbody>
					</table>
					<input type='hidden' name='e-token' value='".e_TOKEN."' />
					<div class='buttons-bar center'>

		";

		/* FORM NOTE EXAMPLE - not needed here as this note is added as label-note (see below)
		$text .= "
			<div class='form-note'>
				".BANLAN_13."<a href='".e_ADMIN_ABS."users.php'><img src='".e_IMAGE_ABS.'admin_imaXXXges/'."users_16.png' alt='' /></a>
			</div>

		";
		*/

		if ($action == 'edit' || $action == 'whedit')
		{
			$text .= "<input type='hidden' name='old_ip' value='{$banlist_ip}' />
				".$frm->admin_button('update_ban', LAN_UPDATE, 'update');
		}
		else
		{
			$text .= $frm->admin_button('add_ban', ($action == 'add' ? BANLAN_8 : BANLAN_53), 'create');
		}

		$text .= "</div>
				</fieldset>
			</form>
		";

		echo  $mes->render().$text; 
	//	e107::getRender()->tablerender($page_title[$action], $mes->render().$text);
		break;		// End of 'Add' and 'Edit'


	case 'transfer' :
		$message = '';
		$error = false;
		if (isset($_POST['ban_import']))
		{ // Got a file to import
			require_once(e_HANDLER.'upload_handler.php');
			if (($files = process_uploaded_files(e_UPLOAD, FALSE, array('overwrite' => TRUE, 'max_file_count' => 1, 'file_mask' => 'csv'))) === FALSE)
			{ // Invalid file
				$error = true;
				$mes->addError(BANLAN_47);
			}
			if(empty($files) || vartrue($files[0]['error']))
			{
				$error = true;
				if(varset($files[0]['message']))
					$mes->addError($files[0]['message']);
			}
			if(!$error)
			{ // Got a file of some sort
				$message = process_csv(e_UPLOAD.$files[0]['name'],
										intval(varset($_POST['ban_over_import'], 0)),
										intval(varset($_POST['ban_over_expiry'], 0)),
										$separator_char[intval(varset($_POST['ban_separator'], 1))],
										$quote_char[intval(varset($_POST['ban_quote'], 3))]);
				banlist_adminlog('07', 'File: '.e_UPLOAD.$files[0]['name'].'<br />'.$message);
			}

		}

		$text = "
			<form method='post' action='".e_ADMIN_ABS."banlist_export.php' id='core-banlist-transfer-form' >
				<fieldset id='core-banlist-transfer-export'>
					<legend>".BANLAN_40."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col style='width:30%' />
							<col style='width:30%' />
							<col style='width:40%' />
						</colgroup>
						<tbody>
							<tr>
								<th colspan='2'>".BANLAN_36."</th>
								<th>&nbsp;</th>
							</tr>
			";


		foreach ($ipAdministrator->getValidReasonList() as $i) //FIXME $frm->label()
		{
			$text .= "
							<tr>
							<td colspan='3'>
								".$frm->checkbox("ban_types[{$i}]", $i).$frm->label($ipAdministrator->getBanTypeString($i, FALSE), "ban_types[{$i}]", $i)."
								<span class='field-help'>(".$ipAdministrator->getBanTypeString($i, TRUE).")</span>
							</td></tr>
			";
		}

		$text .= "<tr>
			<td>".BANLAN_79."</td>
			<td>".select_box('ban_separator', $separator_char).' '.BANLAN_37."</td>
		<td>".select_box('ban_quote', $quote_char).' '.BANLAN_38."</td></tr>";
		$text .= "

						</tbody>
					</table>
					<div class='buttons-bar center'>".$frm->admin_button('ban_export', BANLAN_39, 'export', BANLAN_39)."</div>
						<input type='hidden' name='e-token' value='".e_TOKEN."' />
				</fieldset>
			</form>
		";

		// Now do the import options
		$text .= "
			<form enctype='multipart/form-data' method='post' action='".e_SELF."?transfer' id='ban_import_form' >
				<fieldset id='core-banlist-transfer-import'>
					<legend>".BANLAN_41."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col style='width:30%' />
							<col style='width:30%' />
							<col style='width:40%' />
						</colgroup>
						<tbody>
							<tr>
								<th colspan='2'>".BANLAN_42."</th>
								<th>&nbsp;</th>
							</tr>
							<tr>
								<td colspan='3'>".$frm->checkbox('ban_over_import', 1, '', array('label' => BANLAN_43))."</td>
							</tr>
							<tr>
								<td colspan='3'>".$frm->checkbox('ban_over_expiry', 1, '', array('label' => BANLAN_44))."</td>
							</tr>
							<tr>
								<td>".BANLAN_46."</td>
								<td colspan='2'>
									".$frm->file('file_userfile[]', array('size' => '40'))."
								</td>
							</tr>
							<tr>
			<td>".BANLAN_80."</td>
			<td>".select_box('ban_separator', $separator_char).' '.BANLAN_37."</td>
		<td>".select_box('ban_quote', $quote_char).' '.BANLAN_38."</td></tr>
						</tbody>
					</table>
								<div class='buttons-bar center'>
								".$frm->admin_button('ban_import', BANLAN_45, 'import')."
								<input type='hidden' name='e-token' value='".e_TOKEN."' />
								</div>


				</fieldset>
			</form>
		";

		echo $mes->render().$text; 
	//	e107::getRender()->tablerender(BANLAN_16.SEP.BANLAN_35, $mes->render().$text);
		break;		// End case 'transfer'

	case 'list' :
	case 'white' :
	default :
		
		if(!E107_DEBUG_LEVEL)
		{
			break;	
		}
		
		if (($action != 'list') && ($action != 'white'))
			$action = 'list';

		$edit_action = ($action == 'list' ? 'edit' : 'whedit');
		$del_action = ($action == 'list' ? 'remove' : 'whremove');
		$col_widths = array('list' => array(10, 5, 35, 30, 10, 10), 'white' => array(15, 40, 35, 10));
		$col_titles = array('list' => array(BANLAN_17, BANLAN_20, BANLAN_10, BANLAN_19, BANLAN_18, LAN_OPTIONS),
							'white' => array(BANLAN_55, BANLAN_56, BANLAN_19, LAN_OPTIONS));
		$no_values = array('list' => BANLAN_2, 'white' => BANLAN_54);
		$col_defs = array('list' => array('banlist_datestamp' => 0, 'banlist_bantype' => 0, 'ip_reason' => BANLAN_7, 'banlist_notes' => 0, 'banlist_banexpires' => 0, 'ban_options' => 0),
						'white' => array('banlist_datestamp' => 0, 'ip_reason' => BANLAN_57, 'banlist_notes' => 0, 'ban_options' => 0));

		$text = "
			<form method='post' action='".e_SELF.'?'.$action."' id='core-banlist-form'>
				<fieldset id='core-banlist'>
					<legend class='e-hideme'>".($action == 'list' ? BANLAN_3 : BANLAN_61)."</legend>
					".$frm->hidden("ban_secure", "1")."
		";

		$filter = ($action == 'white') ? 'banlist_bantype='.eIPHandler::BAN_TYPE_WHITELIST : 'banlist_bantype!='.eIPHandler::BAN_TYPE_WHITELIST;


		if(!$ban_total = $sql->db_Select("banlist", "*", $filter." ORDER BY banlist_ip"))
		{
			//$text .= "<div class='center'>".$no_values[$action]."</div>";
			$mes->addInfo($no_values[$action]);
		}
		else
		{
			$text .= "
					<table class='table adminlist'>
						<colgroup>
			";
			foreach($col_widths[$action] as $fw)
			{
				$text .= "
								<col style='width:{$fw}%' />
				";
			}
			$text .= "
						</colgroup>
						<thead>
							<tr>
			";
			$cnt = 0;
			foreach($col_titles[$action] as $ct)
			{
				$cnt ++;
				$text .= "<th".(($cnt == count($col_widths[$action])) ? " class='center last'" : "").">{$ct}</th>";
			}
			$text .= "</tr>
						</thead>
						<tbody>";
			while($row = $sql->db_Fetch())
			{
				//extract($row);//FIXME - kill extract()
				$row['banlist_reason'] = str_replace('LAN_LOGIN_18', BANLAN_11, $row['banlist_reason']);
				$text .= "<tr>";
				foreach($col_defs[$action] as $cd => $fv)
				{
					$row_class = '';
					switch($cd)
					{
						case 'banlist_datestamp':
							$val = ($row['banlist_datestamp'] ? strftime($pref['ban_date_format'], $row['banlist_datestamp']) : BANLAN_22);
							break;
						case 'banlist_bantype':
							$val = "<div class='nowrap' title='".$ipAdministrator->getBanTypeString($row['banlist_bantype'], TRUE)."'>".$ipAdministrator->getBanTypeString($row['banlist_bantype'], FALSE)." <a href='#' title='".$ipAdministrator->getBanTypeString($row['banlist_bantype'], TRUE)."' onclick='return false;'>".E_16_USER."</a></div>";
							break;
						case 'ip_reason':
							$val = e107::getIPHandler()->ipDecode($row['banlist_ip'])."<br />".$fv.": ".$row['banlist_reason'];
							break;
						case 'banlist_banexpires':
							$val = ($row['banlist_banexpires'] ? strftime($pref['ban_date_format'], $row['banlist_banexpires']).(($row['banlist_banexpires'] < time()) ? ' ('.BANLAN_34.')' : '') : LAN_NEVER)."<br />".ban_time_dropdown("onchange=\"e107Helper.urlJump('".e_SELF."?newtime-{$row['banlist_ip']}-'+this.value)\"");
							break;
						case 'ban_options':
							$row_class = ' class="center"';
							$val = "
							<a class='action edit' href='".e_SELF."?{$edit_action}-{$row['banlist_ip']}'>".ADMIN_EDIT_ICON."</a>
<input class='action delete no-confirm' name='delete_ban_entry' value='".e_SELF."?{$del_action}-{$row['banlist_ip']}' type='image' src='".ADMIN_DELETE_ICON_PATH."' alt='".LAN_DELETE."' title='".$tp->toJS(LAN_CONFIRMDEL." [".e107::getIPHandler()->ipDecode($row['banlist_ip'])."]")."' />";
							break;
						case 'banlist_notes':
						default:
							$val = $row[$cd];
					}

					$text .= "<td{$row_class}>{$val}</td>";
				}
				$text .= '</tr>';
			}
			$text .= "</tbody>
					</table>
					<script type='text/javascript'>
					(function () {
						var del_sel = \$\$('input[name=delete_ban_entry]');
						del_sel.each(function (element) {
							var msg = element.readAttribute('title');
							element.writeAttribute('title', '".LAN_DELETE."').writeAttribute('confirm-msg', msg);
						});
						del_sel.invoke('observe', 'click', function (event) {

							var element = event.element(), msg = element.readAttribute('confirm-msg');
							if(!e107Helper.confirm(msg)) { event.stop(); return; }
							\$('core-banlist-form').writeAttribute('action', element.value).submit();
						});
					}())
					</script>
			";
		}
		$text .= "
				</fieldset>
			</form>
		";

		echo $mes->render().$text; 
	//	e107::getRender()->tablerender(($action == 'list' ? BANLAN_3 : BANLAN_61), $mes->render().$text);
		// End of case 'list' and the default case
}		// End switch ($action)


require_once(e_ADMIN.'footer.php');


/**
 *	Admin menu options
 */
function banlist_adminmenu()
{
	$action = (e_QUERY) ? e_QUERY : 'list';

	$var['list']['text'] = BANLAN_14;		// List existing bans
	$var['list']['link'] = e_SELF.'?list';
	$var['list']['perm'] = '4';

	$var['add']['text'] = BANLAN_25;		// Add a new ban
	$var['add']['link'] = e_SELF.'?add';
	$var['add']['perm'] = '4';

	$var['white']['text'] = BANLAN_52;		// List existing whitelist entries
	$var['white']['link'] = e_SELF.'?white';
	$var['white']['perm'] = '4';

	$var['whadd']['text'] = BANLAN_53;		// Add a new whitelist entry
	$var['whadd']['link'] = e_SELF.'?whadd';
	$var['whadd']['perm'] = '4';

	$var['transfer']['text'] = BANLAN_35;
	$var['transfer']['link'] = e_SELF.'?transfer';
	$var['transfer']['perm'] = '4';

	if (getperms('0'))
	{
		$var['times']['text'] = BANLAN_15;
		$var['times']['link'] = e_SELF.'?times';
		$var['times']['perm'] = '0';

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF.'?options';
		$var['options']['perm'] = '0';

		$var['banlog']['text'] = BANLAN_81;
		$var['banlog']['link'] = e_SELF.'?banlog';
		$var['banlog']['perm'] = '0';
	}
	e107::getNav()->admin(BANLAN_16, $action, $var);
}



// Parse the date string used by the import/export - YYYYMMDD_HHMMSS
function parse_date($instr)
{
	if (strlen($instr) != 15)
		return 0;
	return mktime(substr($instr, 9, 2), substr($instr, 11, 2), substr($instr, 13, 2), substr($instr, 4, 2), substr($instr, 6, 2), substr($instr, 0, 4));
}



// Process the imported CSV file, update the database, delete the file.
// Return a message
function process_csv($filename, $override_imports, $override_expiry, $separator = ',', $quote = '"')
{
	$sql = e107::getDb();
	$pref['ban_durations'] = e107::getPref('ban_durations');
	$mes = e107::getMessage();
	
	//  echo "Read CSV: {$filename} separator: {$separator}, quote: {$quote}  override imports: {$override_imports}  override expiry: {$override_expiry}<br />";
	// Renumber imported bans
	if ($override_imports)
		$sql->db_Update('banlist', "`banlist_bantype`=".eIPHandler::BAN_TYPE_TEMPORARY." WHERE `banlist_bantype` = ".eIPHandler::BAN_TYPE_IMPORTED);
	$temp = file($filename);
	$line_num = 0;
	foreach ($temp as $line)
	{ // Process one entry
		$line = trim($line);
		$line_num++;
		if ($line)
		{
			$fields = explode($separator, $line);
			$field_num = 0;
			$field_list = array('banlist_bantype' => eIPHandler::BAN_TYPE_IMPORTED);
			foreach ($fields as $f)
			{
				$f = trim($f);
				if (substr($f, 0, 1) == $quote)
				{
					if (substr($f, -1, 1) == $quote)
					{ // Strip quotes
						$f = substr($f, 1, -1);		// Strip off the quotes
					}
					else
					{
						$mes->addError(BANLAN_49.$line_num);
						return BANLAN_49.$line_num;
					}
				}
				// Now handle the field
				$field_num++;
				switch ($field_num)
				{
					case 1 :	// IP address
						$field_list['banlist_ip'] = e107::getIPHandler()->ipEncode($f);
						break;
					case 2 :	// Original date of ban
						$field_list['banlist_datestamp'] = parse_date($f);
						break;
					case 3 :	// Expiry of ban - depends on $override_expiry
						if ($override_expiry)
						{
							$field_list['banlist_banexpires'] = parse_date($f);
						}
						else
						{ // Use default ban time from now
							$field_list['banlist_banexpires'] = $pref['ban_durations'][eIPHandler::BAN_TYPE_IMPORTED] ? time() + (60*60*$pref['ban_durations'][eIPHandler::BAN_TYPE_IMPORTED]) : 0;
						}
						break;
					case 4 :	// Original ban type - we always ignore this and force to 'imported'
						break;
					case 5 :	// Ban reason originally generated by E107
						$field_list['banlist_reason'] = $f;
						break;
					case 6 :	// Any user notes added
						$field_list['banlist_notes'] = $f;
						break;
					default :	// Just ignore any others
				}
			}
			$qry = "REPLACE INTO `#banlist` (".implode(',', array_keys($field_list)).") values ('".implode("', '", $field_list)."')";
			//	  echo count($field_list)." elements, query: ".$qry."<br />";
			if (!$sql->gen($qry))
			{
				$mes->addError(BANLAN_50.$line_num);
				return BANLAN_50.$line_num;
			}
		}
	}
	// Success here - may need to delete old imported bans
	if ($override_imports)
		$sql->db_Delete('banlist', "`banlist_bantype` = ".eIPHandler::BAN_TYPE_TEMPORARY);
	@unlink($filename);		// Delete file once done
	$mes->addSuccess(str_replace('[y]', $line_num, BANLAN_51).$filename);
	return str_replace('[y]', $line_num, BANLAN_51).$filename;
}



/**
 *	Log event to admin log
 *
 *	@param string $msg_num - exactly two numeric characters corresponding to a log message
 *	@param string $woffle - information for the body of the log entre
 *
 *	@return none
 */
function banlist_adminlog($msg_num = '00', $woffle = '')
{
	e107::getAdminLog()->log_event('BANLIST_'.$msg_num, $woffle, E_LOG_INFORMATIVE, '');
}


/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
/*
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_JS."core/admin.js'></script>
	";

	return $ret;

*/
}

require_once(e_ADMIN."footer.php");
exit;
?>
