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
if(!empty($_POST) && !isset($_POST['e-token']))
{
	$_POST['e-token'] = '';
}
require_once(__DIR__.'/../class2.php');

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
		  'banlist_ip' 			=>   array ( 'title' => BANLAN_126, 			'type' => 'method', 		'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array(), 'class' => 'left', 'thclass' => 'left',  ),
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

		//

		/**
		 * Custom search field handling for banlist_ip.
		 * @param string $srch
		 */
		function handleListBanlistIpSearch($srch)
		{
			$ret = array(
				"banlist_ip = '".$srch."'"
			);

			if($ip6 = e107::getIPHandler()->ipEncode($srch,true))
			{
				$ip = str_replace('x', '', $ip6);
				$ret[] = "banlist_ip LIKE '%".$ip."%'";
			}

			return implode(" OR ",$ret);
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


		public function beforeCreate($new_data, $old_data)
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

	/**
	 *	Create dropdown with options for ban time - uses internal fixed list of reasonable values
	 */
	private static function ban_time_dropdown($click_js = '', $zero_text = LAN_NEVER, $curval = -1, $drop_name = 'ban_time')
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
				$words = floor($i / 24) . ' ' . BANLAN_23;
			}
			else
			{
				$words = $i . ' ' . BANLAN_24;
			}
			$ret .= $frm->option($words, $i, ($curval == $i));
		}
		$ret .= '</select>';

		return $ret;
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
						<div class='buttons-bar center'>" . $frm->admin_button('ban_export', LAN_UI_BATCH_EXPORT, 'export', LAN_UI_BATCH_EXPORT) . "</div>
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
						" . $frm->admin_button('ban_import', LAN_IMPORT , 'import') . $frm->token(). "
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

				$helpTip = $ipAdministrator->getBanTypeString($bt, TRUE);
				$text .= "
						<tr>
							<td>
								<strong>".$ipAdministrator->getBanTypeString($bt, FALSE)."</strong>
								".$frm->help($helpTip)."
							</td>
							<td class='left'>
								".$frm->textarea('ban_text_'.($i), $pref['ban_messages'][$bt], 4, 120, array('size'=>'xxlarge'))."
							</td>
							<td class='center'>". self::ban_time_dropdown('', BANLAN_32, $pref['ban_durations'][$bt], 'ban_time_' . ($i)) ."</td>
						</tr>
					";
			}
			$text .= "
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".$frm->admin_button('update_ban_prefs', LAN_UPDATE, 'update')."
							<input type='hidden' name='e-token' value='".defset('e_TOKEN')."' />
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
									<td>".BANLAN_63."".$frm->help(BANLAN_65)."</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('ban_rdns_on_access', 1, $pref['enable_rdns'] == 1)."
							
										</div>
									</td>
								</tr>
								<tr>
									<td>".BANLAN_64.$frm->help(BANLAN_66)."</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('ban_rdns_on_ban', 1, $pref['enable_rdns_on_ban'] == 1)."
										</div>
									</td>
								</tr>
								<tr>
									<td>".BANLAN_67.$frm->help(BANLAN_68)."</td>
									<td>
										<div class='field-spacer'>".$this->drop_box('ban_access_guest', $ban_access_guest).BANLAN_70."</div>
										<div class='field-spacer'>".$this->drop_box('ban_access_member', $ban_access_member).BANLAN_69."</div>
									
									</td>
								</tr>
								<tr>
									<td>".BANLAN_71.$frm->help(BANLAN_73)."</td>
									<td>
										<div class='auto-toggle-area autocheck'>
											".$frm->checkbox('ban_retrigger', 1, $pref['ban_retrigger'] == 1)."
										
										</div>
									</td>
								</tr>
	
								<tr>
								  <td>".BANLAN_91.$frm->help(BANLAN_92)."</td>
								  <td>
								  ".$frm->text('ban_date_format', varset($pref['ban_date_format'], '%H:%M %d-%m-%y'), 40)."
								  ".$frm->help(BANLAN_92, 'after')."
								  </td>
								</tr>
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".$frm->admin_button('update_ban_options', LAN_UPDATE, 'update')."
							<input type='hidden' name='e-token' value='".defset('e_TOKEN')."' />
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
										<input type='hidden' name='e-token' value='".defset('e_TOKEN')."' />
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



				return $this->select('banlist_bantype',$ipAdministrator->banTypes, $curVal);
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

				return $this->select('banlist_banexpires',$opts, $curVal);
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
			if(varset($_POST['etrigger_batch']) == 'gen_intdata__1' && count($_POST['e-multiselect'])) // Do we need BAN here?
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

		public function afterDelete($deleted_data, $id, $deleted_check)
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



