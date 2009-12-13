<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * PM Plugin - administration
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_conf.php,v $
 * $Revision: 1.9 $
 * $Date: 2009-12-13 12:32:28 $
 * $Author: e107steved $
 */


/*
TODO:
1. Limits page needs some lines round the table
2. Limits page - test
3. Maintenance page - to be tested
4. Check operation of user dropdown bit
5. User option to enable/disable email notification of PMs
6. Cron-triggered bulk send.
7. Is it right that limits are saved in $pref, not $pm_prefs? If not, import routine/change
8. Put prefs into plugin.xml?
*/


$retrieve_prefs[] = 'pm_prefs';
$eplug_admin = TRUE;
require_once('../../class2.php');

if (!e107::isInstalled('pm') || !getperms('P'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

require_once(e_PLUGIN.'pm/pm_class.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');
require_once (e_HANDLER.'message_handler.php');
$emessage = &eMessage :: getInstance();

$action = e_QUERY;

require_once(e_ADMIN.'auth.php');

if($action == '')
{
	$action = 'main';
}

$pm_prefs = $sysprefs->getArray('pm_prefs');

//pm_prefs record not found in core table, set to defaults and create record
if(!is_array($pm_prefs))
{
	require_once(e_PLUGIN.'pm/pm_default.php');
	$pm_prefs = pm_set_default_prefs();			// Use the default settings
	$sysprefs->setArray('pm_prefs');
	$emessage->add(ADLAN_PM_3, E_MESSAGE_INFO);
	$e107->admin_log->log_event('PM_ADM_01', '');
}



//$lan_file = e_PLUGIN.'pm/languages/admin/'.e_LANGUAGE.'.php';
// include_once(is_readable($lan_file) ? $lan_file : e_PLUGIN."pm/languages/admin/English.php");
	
if (isset($_POST['update_prefs'])) 
{
	$temp = array();
	foreach($_POST as $k => $v)
	{
		if (strpos($k, 'pm_option-') === 0)
		{
			$k = str_replace('pm_option-','',$k);
			$temp[$k] = $v;
		}
	}
	if ($e107->admin_log->logArrayDiffs($temp, $pm_prefs, 'PM_ADM_02'))
	{
		$sysprefs->setArray('pm_prefs');
		$emessage->add(ADLAN_PM_4, E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(ADLAN_PM_76, E_MESSAGE_INFO);
	}
}



// Mantenance options
if (isset($_POST['pm_maint_execute']))
{
	$maintOpts = array();
	$emessage->add('Starting maintenance', E_MESSAGE_INFO);
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
	$result = doMaint($maintOpts, $pm_prefs);
	if (is_array($result))
	{
		foreach ($result as $k => $ma)
		{
			foreach ($ma as $m)
			{
				$emessage->add($m, $k);
			}
		}
	}
}



if(isset($_POST['addlimit']))
{
	$id = intval($_POST['newlimit_class']);
	if($sql->db_Select('generic','gen_id',"gen_type = 'pm_limit' AND gen_datestamp = ".$id))
	{
		$emessage->add(ADLAN_PM_5, E_MESSAGE_INFO);		// 'Limit for selected user class already exists'
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
		if($sql->db_Insert('generic', $limArray))
		{
			$e107->admin_log->logArrayAll('PM_ADM_05', $limArray);
			$emessage->add(ADLAN_PM_6, E_MESSAGE_SUCCESS);
		}
		else
		{
			$e107->admin_log->log_event('PM_ADM_08', '');
			$emessage->add(ADLAN_PM_7, E_MESSAGE_ERROR);
		}
	}
}

if(isset($_POST['updatelimits']))
{
	$limitVal = intval($_POST['pm_limits']);
	if($pref['pm_limits'] != $limitVal)
	{
		$pref['pm_limits'] = $limitVal;
		save_prefs();
		$emessage->add(ADLAN_PM_8, E_MESSAGE_SUCCESS);
	}
	foreach(array_keys($_POST['inbox_count']) as $id)
	{
		$id = intval($id);
		if($_POST['inbox_count'][$id] == '' && $_POST['outbox_count'][$id] == '' && $_POST['inbox_size'][$id] == '' && $_POST['outbox_size'][$id] == '')
		{
			//All entries empty - Remove record
			if($sql->db_Delete('generic','gen_id = '.$id))
			{
				$e107->admin_log->log_event('PM_ADM_07', 'ID: '.$id);
				$emessage->add($id.ADLAN_PM_9, E_MESSAGE_SUCCESS);
			}
			else
			{
				$e107->admin_log->log_event('PM_ADM_10', '');
				$emessage->add($id.ADLAN_PM_10, E_MESSAGE_ERROR);
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
			if ($sql->db_Update('generic',array('data' => $limArray, 'WHERE' => 'gen_id = '.$id)))
			{
				$e107->admin_log->logArrayAll('PM_ADM_06', $limArray);
				$emessage->add($id.ADLAN_PM_11, E_MESSAGE_SUCCESS);
			}
			else
			{
				$e107->admin_log->log_event('PM_ADM_09', '');
				$emessage->add($id.ADLAN_PM_7, E_MESSAGE_ERROR);
			}
		}
	}
}



if ($emessage->hasMessage())
{
	$e107->ns->tablerender(ADLAN_PM_58, $emessage->render());
}


switch ($action)
{
	case 'main' :
		$ns->tablerender(ADLAN_PM_12, show_options($pm_prefs));
		break;
	case 'limits' :
		$ns->tablerender(ADLAN_PM_14, show_limits());
		$ns->tablerender(ADLAN_PM_15, add_limit());
		break;
	case 'maint' :
		$ns->tablerender(ADLAN_PM_60, show_maint($pm_prefs));
		break;
}

require_once(e_ADMIN.'footer.php');




function yes_no($fname, $curval = '')
{
		$ret = 
		form::form_radio($fname, '1', ($curval ? '1' : '0'), '', '').LAN_YES.' '.
		form::form_radio($fname, '0', ($curval ? '0' : '1'), '', '').LAN_NO;
		return $ret;
}



function show_options($pm_prefs)
{
	$txt = "
	<fieldset id='plugin-pm-prefs'>
	<form method='post' action='".e_SELF."'>
	<table cellpadding='0' cellspacing='0' class='adminedit'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
	<tbody>
	<tr>
		<td>".ADLAN_PM_16."</td>
		<td>".form::form_text('pm_option-title', 20, $pm_prefs['title'], 50)."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_17."</td>
		<td>".yes_no('pm_option-animate', $pm_prefs['animate'])."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_18."</td>
		<td>".yes_no('pm_option-dropdown', $pm_prefs['dropdown'])."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_19."</td>
		<td>".form::form_text('pm_option-read_timeout', 5, $pm_prefs['read_timeout'], 5)."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_20."</td>
		<td>".form::form_text('pm_option-unread_timeout', 5, $pm_prefs['unread_timeout'], 5)."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_21."</td>
		<td>".yes_no('pm_option-popup', $pm_prefs['popup'])."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_22."</td>
		<td>".form::form_text('pm_option-popup_delay', 5, $pm_prefs['popup_delay'], 5)." ".ADLAN_PM_44."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_23."</td>
		<td>".r_userclass('pm_option-pm_class', $pm_prefs['pm_class'], 'off', 'member,admin,classes')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_24."</td>
		<td>".form::form_text('pm_option-perpage', 5, $pm_prefs['perpage'], 5)."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_25."</td>
		<td>".r_userclass('pm_option-notify_class', $pm_prefs['notify_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_26."</td>
		<td>".r_userclass('pm_option-receipt_class', $pm_prefs['receipt_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_27."</td>
		<td>".r_userclass('pm_option-attach_class', $pm_prefs['attach_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_28."</td>
		<td>".form::form_text('pm_option-attach_size-', 8, $pm_prefs['attach_size'], 8)." kB</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_29."</td>
		<td>".r_userclass('pm_option-sendall_class', $pm_prefs['sendall_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_30."</td>
		<td>".r_userclass('pm_option-multi_class', $pm_prefs['multi_class'], 'off', 'nobody,member,admin,classes')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_31."</td>
		<td>".yes_no('pm_option-allow_userclass', $pm_prefs['allow_userclass'])."</td>
	</tr>
	<tr>
		<td colspan='2' style='text-align:center'><input type='submit' class='button' name='update_prefs' value='".ADLAN_PM_32."' /></td>
	</tr>
	</tbody>
	</table>
	</form>
	</fieldset>
	";
	return $txt;
}





function show_limits()
{
	global $sql, $pref;
	
	if (!isset($pref['pm_limits'])) { $pref['pm_limits'] = 0; }

	if($sql->db_Select('generic', "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as inbox_count, gen_ip as outbox_count, gen_intdata as inbox_size, gen_chardata as outbox_size", "gen_type = 'pm_limit'"))
	{
		while($row = $sql->db_Fetch())
		{
			$limitList[$row['limit_classnum']] = $row;
		}
	}
	$txt = "
		<fieldset id='plugin-pm-showlimits'>
		<form method='post' action='".e_SELF."'>
		<table cellpadding='0' cellspacing='0' class='adminedit'>
		<colgroup span='3'>
			<col class='col-label' />
			<col class='col-control' />
			<col class='col-control' />
		</colgroup>
		<tbody>
		<tr>
			<td colspan='3' style='text-align:left'>".ADLAN_PM_45." 
			<select name='pm_limits' class='tbox'>
		";
		$sel = ($pref['pm_limits'] == 0 ? "selected='selected'" : "");
		$txt .= "<option value='0' {$sel}>".ADLAN_PM_33."</option>\n";

		$sel = ($pref['pm_limits'] == 1 ? "selected='selected'" : "");
		$txt .= "<option value='1' {$sel}>".ADLAN_PM_34."</option>\n";

		$sel = ($pref['pm_limits'] == 2 ? "selected='selected'" : "");
		$txt .= "<option value='2' {$sel}>".ADLAN_PM_35."</option>\n";

		$txt .= "</select>\n";
		
		$txt .= '&nbsp;&nbsp;'.ADLAN_PM_77."
			</td>
		</tr>
		<tr>
			<th>".ADLAN_PM_36."</th>
			<th>".ADLAN_PM_37."</th>
			<th>".ADLAN_PM_38."</th>
		</tr>
	";

	if (isset($limitList)) 
	{
		foreach($limitList as $row)
		{
			$txt .= "
			<tr>
			<td>".r_userclass_name($row['limit_classnum'])."</td>
			<td>
			".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='inbox_count[{$row['limit_id']}]' value='{$row['inbox_count']}' /> 
			".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='outbox_count[{$row['limit_id']}]' value='{$row['outbox_count']}' /> 
			</td>
			<td>
			".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='inbox_size[{$row['limit_id']}]' value='{$row['inbox_size']}' /> 
			".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='outbox_size[{$row['limit_id']}]' value='{$row['outbox_size']}' /> 
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

	$txt .= "
	<tr>
	<td colspan='3' style='text-align:center'>
	<input type='submit' class='button' name='updatelimits' value='".ADLAN_PM_42."' />
	</td>
	</tr>
	";

	$txt .= '
	</tbody>
	</table>
	</form>
	</fieldset>';
	return $txt;
}




function add_limit()
{
	global $sql, $pref;
	if($sql->db_Select("generic", "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as inbox_count, gen_ip as outbox_count, gen_intdata as inbox_size, gen_chardata as outbox_size", "gen_type = 'pm_limit'"))
	{
		while($row = $sql->db_Fetch())
		{
			$limitList[$row['limit_classnum']] = $row;
		}
	}
	$txt = "
		<fieldset id='plugin-pm-addlimit'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table cellpadding='0' cellspacing='0' class='adminedit'>
		<colgroup span='3'>
			<col class='col-label' />
			<col class='col-control' />
			<col class='col-control' />
		</colgroup>
		<tbody>
		<tr>
			<th>".ADLAN_PM_36."</th>
			<th>".ADLAN_PM_37."</th>
			<th>".ADLAN_PM_38."</th>
		</tr>
	";

	$txt .= "
	<tr>
	<td>".r_userclass("newlimit_class", 0, "off", "guest,member,admin,classes,language")."</td>
	<td>
		".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='new_inbox_count' value='' /> 
		".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='new_outbox_count' value='' /> 
	</td>
	<td>
		".ADLAN_PM_39."<input type='text' class='tbox' size='5' name='new_inbox_size' value='' /> 
		".ADLAN_PM_40."<input type='text' class='tbox' size='5' name='new_outbox_size' value='' /> 
	</td>
	</tr>
	<tr>
	<td colspan='3' style='text-align:center'>
	<input type='submit' class='button' name='addlimit' value='".ADLAN_PM_43."' />
	</td>
	</tr>
	";

	$txt .= '
	</tbody>
	</table>
	</form>
	</fieldset>';
	return $txt;
}




function show_maint($pmPrefs)
{
	$txt = "
	<fieldset id='plugin-pm-maint'>
	<legend>".ADLAN_PM_62."</legend>
	<form method='post' action='".e_SELF."?maint'>
	<table cellpadding='0' cellspacing='0' class='adminedit'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
	<tbody>
	<tr>
		<td>".ADLAN_PM_63."</td>
		<td>".yes_no('pm_maint_sent', '0')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_64."</td>
		<td>".yes_no('pm_maint_rec', '0')."</td>
	</tr>
	<tr>
		<td>".ADLAN_PM_65."</td>
		<td>".yes_no('pm_maint_blocked', '0')."</td>
	</tr>
	";

	if ($pmPrefs['read_timeout'] || $pmPrefs['unread_timeout'])
	{
		$txt .= "
		<tr>
			<td>".ADLAN_PM_71."</td>
			<td>".yes_no('pm_maint_expired', '0')."</td>
		</tr>";
	}

	$txt .= "
	<tr>
		<td>".ADLAN_PM_78."</td>
		<td>".yes_no('pm_maint_attach', '0')."</td>
	</tr>
	<tr>
		<td colspan='2' style='text-align:center'><input type='submit' class='button' name='pm_maint_execute' value='".ADLAN_PM_61."' /></td>
	</tr>
	</tbody>
	</table>
	</form>
	</fieldset>
	";
	return $txt;
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


/**
 * 	Do PM DB maintenance
 *	@param array $opts of tasks key = sent|rec|blocked|expired  (one or more present). ATM value not used
 *	@return array where key is message type (E_MESSAGE_SUCCESS|E_MESSAGE_ERROR|E_MESSAGE_INFO etc), data is array of messages of that type (key = timestamp)
 */
function doMaint($opts, $pmPrefs)
{
	if (!count($opts))
	{
		return array(E_MESSAGE_ERROR => array(ADLAN_PM_66));
	}

	$results = array(E_MESSAGE_INFO => array(ADLAN_PM_67));
	$logResults = array();
	$e107 = e107::getInstance();
	$e107->admin_log->log_event('PM_ADM_04', implode(', ',array_keys($opts)));
	$pmHandler = new private_message();
	$db2 = new db();							// Will usually need a second DB object to avoid over load
	$start = 0;						// Use to ensure we get different log times


	if (isset($opts['sent']))		// Want pm_from = deleted user and pm_read_del = 1
	{
		$cnt = 0;
		if ($res = $db2->db_Select_gen("SELECT pm.pm_id FROM `#private_msg` AS pm LEFT JOIN `#user` AS u ON pm.`pm_from` = `#user`.`user_id`
					WHERE (pm.`pm_read_del = 1) AND `#user`.`user_id` IS NULL"))
		{
			while ($row = $db2->db_Fetch(MYSQL_ASSOC))
			{
				if ($pmHandler->del($row['pm_id']) !== FALSE)
				{
					$cnt++;
				}
			}
		}
		$start = time();
		$results[E_MESSAGE_SUCCESS][$start] = str_replace('--COUNT--', $cnt, ADLAN_PM_74);
	}
	if (isset($opts['rec']))		// Want pm_to = deleted user and pm_sent_del = 1
	{
		$cnt = 0;
		if ($res = $db2->db_Select_gen("SELECT pm.pm_id FROM `#private_msg` AS pm LEFT JOIN `#user` AS u ON pm.`pm_to` = `#user`.`user_id`
					WHERE (pm.`pm_sent_del = 1) AND `#user`.`user_id` IS NULL"))
		{
			while ($row = $db2->db_Fetch(MYSQL_ASSOC))
			{
				if ($pmHandler->del($row['pm_id']) !== FALSE)
				{
					$cnt++;
				}
			}
		}
		$start = max($start + 1, time());
		$results[E_MESSAGE_SUCCESS][$start] = str_replace('--COUNT--', $cnt, ADLAN_PM_75);
	}


	if (isset($opts['blocked']))
	{
		if ($res = $e107->sql->db_Select_gen("DELETE `#private_msg_block` FROM `#private_msg_block` LEFT JOIN `#user` ON `#private_msg_block`.`pm_block_from` = `#user`.`user_id`
					WHERE `#user`.`user_id` IS NULL"))
		{
			$start = max($start + 1, time());
			$results[E_MESSAGE_ERROR][$start] = str_replace(array('--NUM--', '--TEXT--'), array($this->sql->getLastErrorNum, $this->sql->getLastErrorText), ADLAN_PM_70);
		}
		else
		{
			$start = max($start + 1, time());
			$results[E_MESSAGE_SUCCESS][$start] = str_replace('--COUNT--', $res, ADLAN_PM_69);
		}
		if ($res = $e107->sql->db_Select_gen("DELETE `#private_msg_block` FROM `#private_msg_block` LEFT JOIN `#user` ON `#private_msg_block`.`pm_block_to` = `#user`.`user_id`
					WHERE `#user`.`user_id` IS NULL"))
		{
			$start = max($start + 1, time());
			$results[E_MESSAGE_ERROR][$start] = str_replace(array('--NUM--', '--TEXT--'), array($this->sql->getLastErrorNum, $this->sql->getLastErrorText), ADLAN_PM_70);
		}
		else
		{
			$start = max($start + 1, time());
			$results[E_MESSAGE_SUCCESS][$start] = str_replace('--COUNT--', $res, ADLAN_PM_68);
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
				while ($row = $db2->db_Fetch(MYSQL_ASSOC))
				{
					if ($pmHandler->del($row['pm_id']) !== FALSE)
					{
						$cnt++;
					}
				}
			}
			$start = max($start + 1, time());
			$results[E_MESSAGE_SUCCESS][$start] = str_replace('--COUNT--', $cnt, ADLAN_PM_73);
		}
		else
		{
			$start = max($start + 1, time());
			$results[E_MESSAGE_ERROR][$start] = ADLAN_PM_72;
		}
	}


	if (isset($opts['attach']))
	{	// Check for orphaned and missing attachments
		require_once(e_HANDLER.'file_class.php');
		$fl = new e_file();
		$missing = array();
		$orphans = array();
		$fileArray = $fl->get_files(e_PLUGIN.'pm/attachments');
		if ($db2->db_Select('private_msg', 'pm_id, pm_attachments', "pm_attachments != ''"))
		{
			while ($row = $db2->db_Fetch(MYSQL_ASSOC))
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
		$attachMessage = str_replace(array('--ORPHANS--', '--MISSING--'), array(count($orphans), count($missing)), ADLAN_PM_79);
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


	$e107->admin_log->logArrayAll('PM_ADM_03',makeLogEntry($results));
	return $results;
}



function show_menu($action)
{
	if ($action == '') { $action = 'main'; }

	$var['main']['text'] = ADLAN_PM_54;
	$var['main']['link'] = e_SELF;

	$var['limits']['text'] = ADLAN_PM_55;
	$var['limits']['link'] = e_SELF.'?limits';

	$var['maint']['text'] = ADLAN_PM_59;
	$var['maint']['link'] = e_SELF.'?maint';

	show_admin_menu(ADLAN_PM_12, $action, $var);
}


function pm_conf_adminmenu() 
{
	global $action;
	show_menu($action);
}

?>
