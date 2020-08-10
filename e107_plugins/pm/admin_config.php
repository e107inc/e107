<?php

// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	e107::redirect('admin');
	exit;
}

e107::lan('pm',true);
// e107::css('inline', "div.tab-content { margin-top:10px } ");

class pm_admin extends e_admin_dispatcher
{

	protected $modes = array(	

		'main'	=> array(
			'controller' 	=> 'private_msg_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_form_ui',
			'uipath' 		=> null
		),
		'inbox'	=> array(
			'controller' 	=> 'private_msg_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_form_ui',
			'uipath' 		=> null
		),
		'outbox'	=> array(
			'controller' 	=> 'private_msg_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_form_ui',
			'uipath' 		=> null
		),

    /*
		'block'	=> array(
			'controller' 	=> 'private_msg_block_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_block_form_ui',
			'uipath' 		=> null
		),
    */
	);	
	
	
	protected $adminMenu = array(

		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
		'main/limits'		=> array('caption'=> ADLAN_PM_55, 'perm' => 'P'),
		'main/maint'		=> array('caption'=> ADLAN_PM_59, 'perm' => 'P'),


		'main/null'		    => array('divider'=> true),
		'inbox/list'		=> array('caption'=> LAN_PLUGIN_PM_INBOX, 'perm' => 'P'),
		'outbox/list'		=> array('caption'=> LAN_PLUGIN_PM_OUTBOX, 'perm' => 'P'),
		'outbox/create'		=> array('caption'=> LAN_PLUGIN_PM_NEW, 'perm' => 'P'),

	//	'block/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
	//	'block/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
			




	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = LAN_PLUGIN_PM_NAME;

	function init()
	{

		if(e_DEBUG == true)
		{
			$this->adminMenu['main/null2']	= array('divider'=> true);
			$this->adminMenu['main/list']   = array('caption'=> "Log", 'perm' => 'P');
		}

	}
}




				
class private_msg_ui extends e_admin_ui
{
			
		protected $pluginTitle		= LAN_PLUGIN_PM_NAME;
		protected $pluginName		= 'pm';
		protected $table			= 'private_msg';
		protected $pid				= 'pm_id';
		protected $perPage 			= 7;
        protected $listQry          = '';
        protected $listOrder        = "p.pm_id DESC";
			
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'pm_id'             => array ( 'title' => LAN_ID,       'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_from'           => array ( 'title' => LAN_PLUGIN_PM_FROM,       'type' => 'method', 'noedit'=>true, 'data' => 'int', 'filter'=>true, 'width' => '5%%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_to'             => array ( 'title' => LAN_PLUGIN_PM_TO,         'type' => 'user', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_sent'           => array ( 'title' => LAN_DATE,     'type' => 'datestamp', 'data' => 'int', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => 'auto=1&readonly=1', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_subject'        => array ( 'title' => LAN_PLUGIN_PM_SUB,    'type' => 'text', 'data' => 'str', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_text'           => array ( 'title' => LAN_PLUGIN_PM_MESS,    'type' => 'bbarea', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => 'expand=1&truncate=50', 'writeParms' => 'rows=5&size=block&cols=80', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_read'           => array ( 'title' => LAN_PLUGIN_PM_READ,       'type' => 'boolean', 'noedit'=>1, 'data' => 'int', 'batch'=>true, 'filter'=>true, 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
        
          'pm_sent_del'       => array ( 'title' => LAN_PLUGIN_PM_DEL,        'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_read_del'       => array ( 'title' => LAN_PLUGIN_PM_DEL,        'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_attachments'    => array ( 'title' => LAN_PLUGIN_PM_ATTACHMENT, 'type' => 'text', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_option'         => array ( 'title' => 'Option',     'type' => 'text', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_size'           => array ( 'title' => LAN_PLUGIN_PM_SIZE,       'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options'           => array ( 'title' => LAN_OPTIONS,    'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('pm_id', 'pm_from', 'pm_to', 'pm_sent', 'pm_read', 'pm_subject', 'pm_text');

		protected $preftabs = array(LAN_BASIC, LAN_ADVANCED);

		protected $prefs = array(
			'title'	        => array('title'=> ADLAN_PM_16,         'tab'=>0, 'type' => 'text', 'data' => 'str', 'help'=>''),
			'pm_class'	    => array('title'=> ADLAN_PM_23,      'tab'=>0, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),
			'sendall_class'	=> array('title'=> ADLAN_PM_29,  'tab'=>1, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),
			'send_to_class'	=> array('title'=> ADLAN_PM_83,  'tab'=>0, 'type' => 'method', 'data' => 'str', 'help'=>''),
			'vip_class'     =>  array('title'=> ADLAN_PM_86,  'tab'=>0, 'type' => 'userclass', 'data' => 'int', 'help'=>ADLAN_PM_87, 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,admin,classes')),
			'multi_class'   => array('title'=> ADLAN_PM_30,  'tab'=>0, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),
			'opt_userclass' => array('title'=> ADLAN_PM_31,  'tab'=>0, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),

			'animate'	    => array('title'=> ADLAN_PM_17,     'tab'=>1, 'type' => 'boolean', 'data' => 'str', 'help'=>''),
		//	'dropdown'	    => array('title'=> ADLAN_PM_18,     'tab'=>0, 'type' => 'boolean', 'data' => 'str', 'help'=>''),
			'read_timeout'	=> array('title'=> ADLAN_PM_19, 'tab'=>1, 'type' => 'number', 'data' => 'int', 'help'=>''),
			'unread_timeout'=> array('title'=> ADLAN_PM_20,         'tab'=>1, 'type' => 'number', 'data' => 'int', 'help'=>''),
			'popup'	        => array('title'=> ADLAN_PM_21,  'tab'=>1, 'type' => 'boolean', 'data' => 'int', 'help'=>''),
			'popup_delay'	=> array('title'=> ADLAN_PM_22,  'tab'=>1, 'type' => 'number', 'data' => 'int', 'help'=>'', 'writeParms'=>array('post'=>ADLAN_PM_44, 'tdClassRight'=>'form-inline')),
			'notify_class'  => array('title'=> ADLAN_PM_25,  'tab'=>1, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),
			'receipt_class' => array('title'=> ADLAN_PM_26,  'tab'=>1, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),
			'attach_class'  => array('title'=> ADLAN_PM_27,  'tab'=>0, 'type' => 'userclass', 'data' => 'int', 'help'=>'', 'writeParms'=>array('size'=>'xlarge', 'classlist'=>'nobody,main,member,admin,classes')),
			'attach_size'   => array('title'=> ADLAN_PM_28,  'tab'=>1, 'type' => 'number', 'data' => 'int', 'help'=>'', 'writeParms'=>'tdClassRight=form-inline&post=Kb'),
			'pm_max_send'   => array('title'=> ADLAN_PM_81,  'tab'=>1, 'type' => 'number', 'data' => 'int', 'help'=>''),
			'perpage'	    => array('title'=> ADLAN_PM_24,  'tab'=>0, 'type' => 'number', 'data' => 'int', 'help'=>''),
			'maxlength'     => array('title'=> ADLAN_PM_84,  'tab'=>1, 'type' => 'number', 'data' => 'int', 'help'=>ADLAN_PM_85, 'writeParms'=>array('post'=>'chars.')),


		);







		private function limitsPageAdd()
		{
			$sql = e107::getDb();
			$mes = e107::getMessage();

			$id = intval($_POST['newlimit_class']);
			if($sql->select('generic','gen_id',"gen_type = 'pm_limit' AND gen_datestamp = ".$id))
			{
				$mes->addInfo(ADLAN_PM_5); // 'Limit for selected user class already exists'
			}
			else
			{
				$limArray = array(			// Strange field names because we use the 'generic' table. But at least it documents the correlation
					'gen_type' => 'pm_limit',
					'gen_datestamp' => intval($_POST['newlimit_class']),
					'gen_user_id' => intval($_POST['new_inbox_count']),
					'gen_ip' => intval($_POST['new_outbox_count']),
					'gen_intdata' => intval($_POST['new_inbox_size']),
					'gen_chardata' => intval($_POST['new_outbox_size'])
					);

				if($sql->insert('generic', $limArray))
				{
					e107::getLog()->logArrayAll('PM_ADM_05', $limArray);
					$mes->addSuccess(ADLAN_PM_6);
				}
				else
				{
					e107::getLog()->log_event('PM_ADM_08', '');
					$mes->addError(ADLAN_PM_7);
				}
			}


		}


		private function limitsPageUpdate()
		{
			$sql = e107::getDb();
			$mes = e107::getMessage();
			$pm_prefs = e107::pref('pm');

			$limitVal = intval($_POST['pm_limits']);


			if($pm_prefs['pm_limits'] != $limitVal)
			{
				$pm_prefs['pm_limits'] = $limitVal;

				$mes->addSuccess(ADLAN_PM_8);
			}
			foreach(array_keys($_POST['inbox_count']) as $id)
			{
				$id = intval($id);
				if($_POST['inbox_count'][$id] == '' && $_POST['outbox_count'][$id] == '' && $_POST['inbox_size'][$id] == '' && $_POST['outbox_size'][$id] == '')
				{
					//All entries empty - Remove record
					if($sql->delete('generic','gen_id = '.$id))
					{
						e107::getLog()->log_event('PM_ADM_07', 'ID: '.$id);
						$mes->addSuccess($id.ADLAN_PM_9);
					}
					else
					{
						e107::getLog()->log_event('PM_ADM_10', '');
						$mes->addError($id.ADLAN_PM_10);
					}
				}
				else
				{
					$limArray = array(			// Strange field names because we use the 'generic' table. But at least it documents the correlation
						'gen_user_id' => intval($_POST['inbox_count'][$id]),
						'gen_ip' => intval($_POST['outbox_count'][$id]),
						'gen_intdata' => intval($_POST['inbox_size'][$id]),
						'gen_chardata' => intval($_POST['outbox_size'][$id])
						);


					if ($sql->update('generic',array('data' => $limArray, 'WHERE' => 'gen_id = '.$id)))
					{
						e107::getLog()->logArrayAll('PM_ADM_06', $limArray);
						$mes->addSuccess($id.ADLAN_PM_11);
					}
					else
					{
						e107::getLog()->log_event('PM_ADM_09', '');
						$mes->addError($id.ADLAN_PM_7);
					}
				}
			}


		}




		public function limitsPage()
		{

			if(isset($_POST['addlimit']))
			{
				$this->limitsPageAdd();
			}

			if(isset($_POST['updatelimits']))
			{
				$this->limitsPageUpdate();
			}

			// ---------------------


			$sql = e107::getDb();
			$frm = e107::getForm();
			$pm_prefs = e107::pref('pm');

			if (!isset($pm_prefs['pm_limits'])) { $pm_prefs['pm_limits'] = 0; }

			if($sql->select('generic', "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as inbox_count, gen_ip as outbox_count, gen_intdata as inbox_size, gen_chardata as outbox_size", "gen_type = 'pm_limit'"))
			{
				while($row = $sql->fetch())
				{
					$limitList[$row['limit_classnum']] = $row;
				}
			}

			$txt = "
				<fieldset id='plugin-pm-showlimits'>
				<form method='post' action='".e_SELF.'?'.e_QUERY."'>
				<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
					<col class='col-control' />
				</colgroup>
				<thead>
					<tr>
					<th>".LAN_USERCLASS."</th>
					<th>".ADLAN_PM_37."</th>
					<th>".ADLAN_PM_38."</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td colspan='3' style='text-align:left'>".ADLAN_PM_45."
					<select name='pm_limits' class='tbox'>
				";


				$sel = ($pm_prefs['pm_limits'] == 0 ? "selected='selected'" : "");
				$txt .= "<option value='0' {$sel}>".ADLAN_PM_33."</option>\n";

				$sel = ($pm_prefs['pm_limits'] == 1 ? "selected='selected'" : "");
				$txt .= "<option value='1' {$sel}>".ADLAN_PM_34."</option>\n";

				$sel = ($pm_prefs['pm_limits'] == 2 ? "selected='selected'" : "");
				$txt .= "<option value='2' {$sel}>".ADLAN_PM_35."</option>\n";

				$txt .= "</select>\n";

				$txt .= '&nbsp;&nbsp;'.ADLAN_PM_77."
					</td>
				</tr>

			";

			if (isset($limitList))
			{
				foreach($limitList as $row)
				{
					$txt .= "
					<tr>
					<td>".e107::getUserClass()->uc_get_classname($row['limit_classnum'])."</td>
					<td>
						<div class='row'>
							<div class='col-md-2'>".LAN_PLUGIN_PM_INBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='inbox_count[{$row['limit_id']}]' value='{$row['inbox_count']}' /></div>
							<div class='col-md-2'>".LAN_PLUGIN_PM_OUTBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='outbox_count[{$row['limit_id']}]' value='{$row['outbox_count']}' /></div>
						</div>
					</td>
					<td>
						<div class='row'>
							<div class='col-md-2'>".LAN_PLUGIN_PM_INBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='inbox_size[{$row['limit_id']}]' value='{$row['inbox_size']}' /></div>
							<div class='col-md-2'>".LAN_PLUGIN_PM_OUTBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='outbox_size[{$row['limit_id']}]' value='{$row['outbox_size']}' /></div>
						</div>
					</td>
					</tr>
					";
				}
			}
			else
			{
				$txt .= "
				<tr>
				<td colspan='3' style='text-align: center'>".ADLAN_PM_41."</td>
				</tr>
				";
			}

			$txt .= '
			</tbody>
			</table>
			<div class="buttons-bar center">
			'.$frm->admin_button('updatelimits','no-value','update', LAN_UPDATE).'
			</div>
			</form>
			</fieldset>';

			$tabs = array();
			$tabs[] = array('caption'=>ADLAN_PM_14, 'text'=>$txt);
			$tabs[] = array('caption'=>ADLAN_PM_15, 'text'=>$this->addLimitPage());

			return e107::getForm()->tabs($tabs);
		}



		function addLimitPage()
		{
			$sql = e107::getDb();
			$frm = e107::getForm();
			$pm_prefs = e107::pref('pm');

			if($sql->select('generic', "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as inbox_count, gen_ip as outbox_count, gen_intdata as inbox_size, gen_chardata as outbox_size", "gen_type = 'pm_limit'"))
			{
				while($row = $sql->fetch())
				{
					$limitList[$row['limit_classnum']] = $row;
				}
			}

			$txt = "
				<fieldset id='plugin-pm-addlimit'>
				<form method='post' action='".e_SELF.'?'.e_QUERY."'>
				<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
					<col class='col-control' />
				</colgroup>
				<thead>
				<tr>
					<th>".LAN_USERCLASS."</th>
					<th>".ADLAN_PM_37."</th>
					<th>".ADLAN_PM_38."</th>
					</tr>
				</thead>
				<tbody>
			";

			$txt .= "
			<tr>
			<td>".e107::getUserClass()->uc_dropdown('newlimit_class', 0, 'guest,member,admin,classes')."</td>
			<td>
				<div class='row'>
				<div class='col-md-2'>".LAN_PLUGIN_PM_INBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='new_inbox_count' value='' /></div>
				<div class='col-md-2'>".LAN_PLUGIN_PM_OUTBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='new_outbox_count' value='' /></div>
				</div>
			</td>
			<td>
				<div class='row'>
				<div class='col-md-2'>".LAN_PLUGIN_PM_INBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='new_inbox_size' value='' /></div>
				<div class='col-md-2'>".LAN_PLUGIN_PM_OUTBOX.":</div><div class='col-md-10'><input type='text' class='tbox' size='5' name='new_outbox_size' value='' /></div>
				</div>
			</td>
			</tr>

			";

			$txt .= '
			</tbody>
			</table>
			<div class="buttons-bar center">
			'.$frm->admin_button('addlimit','no-value','update', LAN_ADD).'
			</div>
			</form>
			</fieldset>';
			return $txt;
		}





		public function mainPageProcess()
		{
			$pm_prefs = e107::pref('pm');
			$mes = e107::getMessage();

			$maintOpts = array();

			if (vartrue($_POST['pm_maint_sent']))
			{
				$maintOpts['sent'] = 1;
			}

			if (vartrue($_POST['pm_maint_rec']))
			{
				$maintOpts['rec'] = 1;
			}

			if (vartrue($_POST['pm_maint_blocked']))
			{
				$maintOpts['blocked'] = 1;
			}

			if (vartrue($_POST['pm_maint_expired']))
			{
				$maintOpts['expired'] = 1;
			}

			if (vartrue($_POST['pm_maint_attach']))
			{
				$maintOpts['attach'] = 1;
			}

			$result = $this->doMaint($maintOpts, $pm_prefs);

			if (is_array($result))
			{
				foreach ($result as $k => $ma)
				{
					foreach ($ma as $m)
					{
						$mes->add($m, $k);
					}
				}
			}


		}




		public function maintPage()
		{
			if(isset($_POST['pm_maint_execute']))
			{
				$this->mainPageProcess();
			}



			$frm = e107::getForm();
			$pmPrefs = e107::pref('pm');

			$txt = "
			<fieldset id='plugin-pm-maint'>
			<legend>".ADLAN_PM_62."</legend>
			<form method='post' action='".e_SELF."?maint'>
			<table class='table adminform'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			<tbody>
			<tr>
				<td>".ADLAN_PM_63."</td>
				<td>".$frm->radio_switch('pm_maint_sent', '', LAN_YES, LAN_NO)."</td>
			</tr>
			<tr>
				<td>".ADLAN_PM_64."</td>
				<td>".$frm->radio_switch('pm_maint_rec', '', LAN_YES, LAN_NO)."</td>
			</tr>
			<tr>
				<td>".ADLAN_PM_65."</td>
				<td>".$frm->radio_switch('pm_maint_blocked', '', LAN_YES, LAN_NO)."</td>
			</tr>
			";

			if ($pmPrefs['read_timeout'] || $pmPrefs['unread_timeout'])
			{
				$txt .= "
				<tr>
					<td>".ADLAN_PM_71."</td>
					<td>".$frm->radio_switch('pm_maint_expired', '', LAN_YES, LAN_NO)."</td>
				</tr>";
			}

			$txt .= "
			<tr>
				<td>".ADLAN_PM_78."</td>
				<td>".$frm->radio_switch('pm_maint_attach', '', LAN_YES, LAN_NO)."</td>
			</tr>
			</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('pm_maint_execute','no-value','delete', LAN_GO)."
			</div>
			</form>
			</fieldset>
			";


			return $txt;


		}




		/**
		 * 	Do PM DB maintenance
		 *	@param array $opts of tasks key = sent|rec|blocked|expired  (one or more present). ATM value not used
		 *	@return array where key is message type (E_MESSAGE_SUCCESS|E_MESSAGE_ERROR|E_MESSAGE_INFO etc), data is array of messages of that type (key = timestamp)
		 */
		private function doMaint($opts, $pmPrefs)
		{
			if (!count($opts))
			{
				return array(E_MESSAGE_ERROR => array(ADLAN_PM_66));
			}



			$results = array(E_MESSAGE_INFO => array(ADLAN_PM_67));		// 'Maintenance started' - primarily for a log entry to mark start time
			$logResults = array();
			$e107 = e107::getInstance();
			e107::getLog()->log_event('PM_ADM_04', implode(', ',array_keys($opts)));
			$pmHandler = new private_message($pmPrefs);
			$db2 =e107::getDb('sql2');							// Will usually need a second DB object to avoid over load
			$start = 0;						// Use to ensure we get different log times


			if (isset($opts['sent']))		// Want pm_from = deleted user and pm_read_del = 1
			{
				$cnt = 0;
				if ($res = $db2->gen("SELECT pm.pm_id FROM `#private_msg` AS pm LEFT JOIN `#user` AS u ON pm.`pm_from` = `#user`.`user_id`
							WHERE (pm.`pm_read_del = 1) AND `#user`.`user_id` IS NULL"))
				{
					while ($row = $db2->fetch())
					{
						if ($pmHandler->del($row['pm_id']) !== FALSE)
						{
							$cnt++;
						}
					}
				}
				$start = time();
				$results[E_MESSAGE_SUCCESS][$start] = str_replace('[x]', $cnt, ADLAN_PM_74);
			}
			if (isset($opts['rec']))		// Want pm_to = deleted user and pm_sent_del = 1
			{
				$cnt = 0;
				if ($res = $db2->gen("SELECT pm.pm_id FROM `#private_msg` AS pm LEFT JOIN `#user` AS u ON pm.`pm_to` = `#user`.`user_id`
							WHERE (pm.`pm_sent_del = 1) AND `#user`.`user_id` IS NULL"))
				{
					while ($row = $db2->fetch())
					{
						if ($pmHandler->del($row['pm_id']) !== FALSE)
						{
							$cnt++;
						}
					}
				}
				$start = max($start + 1, time());
				$results[E_MESSAGE_SUCCESS][$start] = str_replace('[x]', $cnt, ADLAN_PM_75);
			}


			if (isset($opts['blocked']))
			{
				if ($res = $db2->gen("DELETE `#private_msg_block` FROM `#private_msg_block` LEFT JOIN `#user` ON `#private_msg_block`.`pm_block_from` = `#user`.`user_id`
							WHERE `#user`.`user_id` IS NULL"))
				{
					$start = max($start + 1, time());
					$results[E_MESSAGE_ERROR][$start] = str_replace(array('[y]', '[z]'), array($this->sql->getLastErrorNum, $this->sql->getLastErrorText), ADLAN_PM_70);
				}
				else
				{
					$start = max($start + 1, time());
					$results[E_MESSAGE_SUCCESS][$start] = str_replace('[x]', $res, ADLAN_PM_69);
				}
				if ($res = $db2->gen("DELETE `#private_msg_block` FROM `#private_msg_block` LEFT JOIN `#user` ON `#private_msg_block`.`pm_block_to` = `#user`.`user_id`
							WHERE `#user`.`user_id` IS NULL"))
				{
					$start = max($start + 1, time());
					$results[E_MESSAGE_ERROR][$start] = str_replace(array('[y]', '[z]'), array($this->sql->getLastErrorNum, $this->sql->getLastErrorText), ADLAN_PM_70);
				}
				else
				{
					$start = max($start + 1, time());
					$results[E_MESSAGE_SUCCESS][$start] = str_replace('[x]', $res, ADLAN_PM_68);
				}
			}


			if (isset($opts['expired']))
			{
				$del_qry = array();
				$read_timeout = intval($pmPrefs['read_timeout']);
				$unread_timeout = intval($pmPrefs['unread_timeout']);
				if($read_timeout > 0)
				{
					$timeout = time()-($read_timeout * 86400);
					$del_qry[] = "(pm_sent < {$timeout} AND pm_read > 0)";
				}
				if($unread_timeout > 0)
				{
					$timeout = time()-($unread_timeout * 86400);
					$del_qry[] = "(pm_sent < {$timeout} AND pm_read = 0)";
				}
				if(count($del_qry) > 0)
				{
					$qry = implode(' OR ', $del_qry);
					$cnt = 0;
					if($db2->db_Select('private_msg', 'pm_id', $qry))
					{
						while ($row = $db2->db_Fetch())
						{
							if ($pmHandler->del($row['pm_id']) !== FALSE)
							{
								$cnt++;
							}
						}
					}
					$start = max($start + 1, time());
					$results[E_MESSAGE_SUCCESS][$start] = str_replace('[x]', $cnt, ADLAN_PM_73);
				}
				else
				{
					$start = max($start + 1, time());
					$results[E_MESSAGE_ERROR][$start] = ADLAN_PM_72;
				}
			}


			if (isset($opts['attach']))
			{	// Check for orphaned and missing attachments

				$fl = e107::getFile();
				$missing = array();
				$orphans = array();
				$fileArray = $fl->get_files(e_PLUGIN.'pm/attachments'); //FIXME wrong path.
				if ($db2->select('private_msg', 'pm_id, pm_attachments', "pm_attachments != ''"))
				{
					while ($row = $db2->fetch())
					{
						$attachList = explode(chr(0), $row['pm_attachments']);
						foreach ($attachList as $a)
						{
							$found = FALSE;
							foreach ($fileArray as $k => $fd)
							{
								if ($fd['fname'] == $a)
								{
									$found = TRUE;
									unset($fileArray[$k]);
									break;
								}
							}
							if (!$found)
							{
								$missing[] = $row['pm_id'].':'.$a;
							}
						}
					}
				}
				// Any files left in $fileArray now are unused
				if (count($fileArray))
				{
					foreach ($fileArray as $k => $fd)
					{
						unlink($fd['path'].$fd['fname']);
						$orphans[] = $fd['fname'];
					}
				}
				$attachMessage = str_replace(array('[x]', '[y]'), array(count($orphans), count($missing)), ADLAN_PM_79);
				if (TRUE)
				{	// Mostly for testing - probably disable this
					if (count($orphans))
					{
						$attachMessage .= '[!br!]Orphans:[!br!]'.implode('[!br!]', $orphans);
					}
					if (count($missing))
					{
						$attachMessage .= '[!br!]Missing:[!br!]'.implode('[!br!]', $missing);
					}
				}
				$start = max($start + 1, time());
				$results[E_MESSAGE_SUCCESS][$start] = $attachMessage;
			}


			e107::getLog()->logArrayAll('PM_ADM_03', $this->makeLogEntry($results));

			foreach ($results as $k => $r)
			{
				foreach ($r as $sk => $s)
				{
					$results[$k][$sk] = str_replace('[!br!]','<br />',$s);
				}
			}
			return $results;
		}



		/**
		 *	Turn the array produced by doMaint for message display into an array of log strings.
		 *	Data is sorted into time stamp order
		 *
		 *	@param array $results - array of arrays as returned from doMaint()
		 *	@param array|boolean $extra - optional additional information which is sorted into the main result according to keys - so use low numbers
		 *	to make the entry appear at the beginning, and text strings to add to the end.
		 */
		function makeLogEntry($results, $extra = FALSE)
		{
			$logPrefixes = array(E_MESSAGE_SUCCESS => 'Pass - ', E_MESSAGE_ERROR => 'Fail - ', E_MESSAGE_INFO => 'Info - ', E_MESSAGE_DEBUG => 'Debug - ');

			$res = array();

			foreach ($results as $k => $ma)
			{
				foreach ($ma as $ts => $m)
				{
					$res[$ts] = $logPrefixes[$k].$m;
				}
			}

			if (is_array($extra))
			{
				$res = array_merge($res, $extra);
			}

			ksort($res);		// Sort in ascending order of timestamp

			return $res;
		}


		function sendTestNotify()
		{
			e107::includeLan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');
			require_once(e_PLUGIN."pm/pm_class.php");

			$pmInfo = array ( 'numsent' => '1', 'pm_to' => USERID, 'pm_sent'=>time(), 'pm_userclass' => false, 'pm_subject' => 'Test Subject Random:'.md5(time()), 'pm_message' => 'Test Message '.md5(time()), 'postpm' => 'Send Private Message', 'keyword' => NULL,
			'to_info' => array (
				'user_id'       => USERID,
				'user_name'     => USERNAME,
				'user_class'    => USERCLASS,
				'user_email'    => USEREMAIL,
			),
			'uploaded' => array ( ), 'from_id' => 1, 'options' => '', );

			$pm = new private_message;

			if($pm->pm_send_notify(null,$pmInfo, 1) === true)
			{
				e107::getMessage()->addSuccess(ADLAN_PM_92);
			}
			else
			{
				e107::getMessage()->addError(ADLAN_PM_93);
			}


		}


        public function init()
        {
          //  $this->listQry = "SELECT p.*,u.user_name FROM #private_msg AS p LEFT JOIN #user AS u ON p.pm_from = u.user_id  ";

			if(deftrue('e_DEVELOPER') || deftrue('e_DEBUG'))
			{
	            $this->prefs['notify_class']['writeParms']['post']= e107::getForm()->button('testNotify', 1, 'primary', ADLAN_PM_91);

				if(!empty($_POST['testNotify']))
				{
					$this->sendTestNotify();
				}
			}

			if($this->getMode() == 'inbox')
			{
                 $this->listQry = 'SELECT  p.*, u.user_name, f.user_name AS fromuser FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_to
					LEFT JOIN #user as f on f.user_id = p.pm_from WHERE p.pm_to = '.USERID;
				$this->fields['pm_to']['nolist'] = true;
				$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
			}

			if($this->getMode() == 'outbox')
			{
				$this->listQry = 'SELECT  p.*, u.user_name, f.user_name AS fromuser FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_to
					LEFT JOIN #user as f on f.user_id = p.pm_from WHERE p.pm_from = '.USERID;
				$this->fields['pm_from']['nolist'] = true;
				$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
			}

	        if($this->getMode() == 'main')
			{
				$this->listQry = 'SELECT  p.*, u.user_name, f.user_name AS fromuser FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_to
					LEFT JOIN #user as f on f.user_id = p.pm_from WHERE 1 ';
			//	$this->fields['pm_from']['nolist'] = true;
				$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
				$this->perPage = 20;
			}

			if($this->getAction() == 'create')
			{
				$this->fields['pm_to']['writeParms']['default'] = 99999999;
				$this->fields['pm_to']['writeParms']['required'] = 1;
				$this->fields['pm_subject']['writeParms']['required'] = 1;

	            if(!empty($_GET['to']))
	            {
	                $this->fields['pm_to']['writeParms']['default'] = intval($_GET['to']);
	                $this->addTitle('Reply');
	            }

				if(!empty($_GET['subject']))
				{
					$this->fields['pm_subject']['writeParms']['default'] = "Re: ". base64_decode($_GET['subject']);
				}


			}



        
        }

		public function beforeCreate($new_data)
		{

			if(empty($new_data['pm_to']))
			{
				e107::getMessage()->addError(ADLAN_PM_90);
				return false;
			}

			$new_data['pm_size'] = strlen($new_data['pm_text']);
			$new_data['pm_from'] = USERID;
			return $new_data;
		}


		/*
		protected  = array(
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
		);

		

	
		
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
		*/
			
}
				


class private_msg_form_ui extends e_admin_form_ui
{

	function send_to_class($value, $mode, $id)
	{
		$list = e107::getUserClass()->getClassList('main,admin,member,classes');
		$list['matchclass'] = ADLAN_PM_89; 

		return $this->select('send_to_class', $list, vartrue($value, e_UC_MEMBER), array('size'=>'xlarge'));

	}




	function options($parms, $value, $id, $attributes)
	{

	//	return $this->renderValue('options',$value,$att,$id);;
		$tp = e107::getParser();
		$mode = $this->getController()->getMode();

		if($mode == 'inbox')
		{
			$text = "";
			$pmData = $this->getController()->getListModel()->getData();

			if($pmData['pm_from'] != USERID)
			{
				$link = e_SELF."?";
				$link .= (!empty($_GET['iframe'])) ? 'mode=inbox&iframe=1' : 'mode=outbox';


				$link .= "&action=create&to=".intval($pmData['pm_from'])."&subject=".base64_encode($pmData['pm_subject']);



				$text .= "<a href='".$link."' class='btn' title='Reply'>".$tp->toGlyph('fa-reply', array('size'=>'1x'))."</a>";
			}

		//	$text .= $this->renderValue('options',$value,$attr,$id);

			return $text;
		}
	}

	function pm_from($curVal, $mode)
	{

		if($mode == 'read')
		{
			$pmData = $this->getController()->getListModel()->getData();
		}

		return $pmData['fromuser'];
	}
}		
		
/*

				
class private_msg_block_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'Private Messaging';
		protected $pluginName		= 'pm';
		protected $table			= 'private_msg_block';
		protected $pid				= 'pm_block_id';
		protected $perPage 			= 10; 
			
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'pm_block_id' =>   array ( 'title' => 'LAN_ID', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_block_from' =>   array ( 'title' => 'From', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_block_to' =>   array ( 'title' => 'To', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_block_datestamp' =>   array ( 'title' => 'LAN_DATESTAMP', 'type' => 'datestamp', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_block_count' =>   array ( 'title' => 'Count', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options' =>   array ( 'title' => 'Options', 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('pm_block_datestamp');
		
		
		

	//	protected  = array(
	//		'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
	//		'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
	//		'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
	//	);

		
		// optional
		public function init()
		{
			
		}
	
		
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
		
			
}
				


class private_msg_block_form_ui extends e_admin_form_ui
{

}		
	*/
	
		
new pm_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;


