<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Admin Log
 *
*/

/*
 * Preferences:
 * 	'sys_log_perpage' - number of events per page
 *
 * 	'user_audit_opts'	- which user-related events to log
 * 	'user_audit_class'	- user class whose actions can be logged
 *
 * 	'roll_log_days' (default 7) - number of days for which entries retained in rolling log
 * 	'roll_log_active' - set to '1' to enable
 *
 */

require_once (__DIR__.'/../class2.php');
if(! getperms('S'))
{
	e107::redirect('admin');
	exit();
}

e107::coreLan('admin_log', true);
e107::coreLan('log_messages', true); 

$logList = e107::pref('core', 'lan_log_list');

if(is_array($logList)) //... and for any plugins which support it
{
	foreach($logList  as $path => $file)
	{
	//	$file = str_replace('--LAN--', e_LANGUAGE, $file);

		//e107::lan($path,'log',true);
	//	e107::includeLan(e_PLUGIN.$path.'/languages/'.$file);
		e107::includeLan(e_PLUGIN.$path.'/languages/'.e_LANGUAGE."_log.php");
		e107::includeLan(e_PLUGIN.$path.'/languages/'.e_LANGUAGE."/".e_LANGUAGE."_log.php");
	}
}

define('AL_DATE_TIME_FORMAT', 'y-m-d  H:i:s');


function loadEventTypes($table)
{

	$sql = e107::getDb();
	$row = $sql->retrieve("SELECT dblog_eventcode,dblog_title FROM #".$table." WHERE dblog_eventcode !='' AND dblog_title !='' GROUP BY dblog_eventcode",true);
	$eventTypes = array();
	foreach($row as $val)
	{
		$id = $val['dblog_eventcode'];
		$def = strpos($val['dblog_title'], "LAN") !== false ? $id : $val['dblog_title'];
		$eventTypes[$id] = str_replace(': [x]', '', deftrue($val['dblog_title'],$def));
	}

	asort($eventTypes);

	return $eventTypes;

}



function time_box($boxname, $this_time, $day_count, $inc_tomorrow = FALSE, $all_mins = FALSE)
{ // Generates boxes for date and time for today and the preceding days
	// Appends 'date', 'hours', 'mins' to the specified boxname


	$all_time = getdate(); // Date/time now
	$sel_time = getdate($this_time); // Currently selected date/time
	$sel_day = mktime(0, 0, 0, $sel_time['mon'], $sel_time['mday'], $sel_time['year']);
	$today = mktime(0, 0, 0, $all_time['mon'], $all_time['mday'] + ($inc_tomorrow ? 1 : 0), $all_time['year']);

	// Start with day
	$ret = "<select name='{$boxname}date' class='tbox'>";
	// Stick an extra day on the end, plus tomorrow if the flag set
	for($i = ($inc_tomorrow ? - 2 : - 1); $i <= $day_count; $i ++)
	{
		$day_string = date("D d M", $today);
		$sel = ($today == $sel_day) ? " selected='selected'" : "";
		$ret .= "<option value='{$today}' {$sel}>{$day_string}</option>";
		$today -= 86400; // Move to previous day
	}
	$ret .= "</select>";

	// Hours
	$ret .= "&nbsp;<select name='{$boxname}hours' class='tbox select time-offset'>";
	for($i = 0; $i < 24; $i ++)
	{
		$sel = ($sel_time['hours'] == $i) ? " selected='selected'" : "";
		$ret .= "<option value='{$i}' {$sel}>{$i}</option>";
	}
	$ret .= "</select>";

	// Minutes
	$ret .= "&nbsp;<select name='{$boxname}mins' class='tbox select time-offset'>";
	for($i = 0; $i < 60; $i += ($all_mins ? 1 : 5))
	{
		$sel = ($sel_time['minutes'] == $i) ? " selected='selected'" : "";
		$ret .= "<option value='{$i}' {$sel}>{$i}</option>";
	}
	$ret .= "</select>";

	return $ret;
}

class adminlog_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'admin_log_ui',
			'path' 			=> null,
			'ui' 			=> 'admin_log_form_ui',
			'uipath' 		=> null
		),
		

		'audit'	=> array(
			'controller' 	=> 'audit_log_ui',
			'path' 			=> null,
			'ui' 			=> 'admin_log_form_ui',
			'uipath' 		=> null
		),
		

		'rolling'	=> array(
			'controller' 	=> 'dblog_ui',
			'path' 			=> null,
			'ui' 			=> 'admin_log_form_ui',
			'uipath' 		=> null
		),
		

	);	
	
	//$page_title = array('adminlog' => RL_LAN_030, 'auditlog' => RL_LAN_062, 'rolllog' => RL_LAN_002, 'downlog' => RL_LAN_067, 'detailed' => RL_LAN_094, 'online' => RL_LAN_120);
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> RL_LAN_030, 'perm' => '5'),
		'audit/list'		=> array('caption'=> RL_LAN_062, 'perm' => '5'),
		'rolling/list'		=> array('caption'=> RL_LAN_002, 'perm' => '5'),
		'divider/01'        => array('divider'=>true),
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => '5'),	
		'main/maintenance'	=> array('caption'=> LAN_OPTIONS, 'perm' => '5')

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);

	protected $adminMenuIcon = 'e-adminlogs-24';
	
	protected $menuTitle = ADLAN_155;
	

}




				
class admin_log_ui extends e_admin_ui
{
			
		protected $pluginTitle		= ADLAN_155;
		protected $pluginName		= 'core';
		protected $table			= 'admin_log';
		protected $pid				= 'dblog_id';
		protected $perPage 			= 10;
	// protected $listQry			= "SELECT  f.*, u.* FROM #admin_log AS f LEFT JOIN #user AS u ON f.dblog_user_id = u.user_id "; // Should not be necessary.

		protected $listQry			= "SELECT SQL_CALC_FOUND_ROWS  f.*, u.user_id, u.user_name FROM #admin_log AS f LEFT JOIN #user AS u ON f.dblog_user_id = u.user_id "; // Should not be required but be auto-calculated.
		protected $listOrder		= 'f.dblog_id DESC';
		
		protected $batchDelete		= false;
		protected $batchDeleteLog	= false; //TODO - AdminUI option to disable logging of changes.  
			
		protected $fields 		= array (  
	//	'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'nolist'=>true, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'dblog_id' 			=>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_datestamp' 	=>   array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => '12%', 'filter' => true, 'help' => '', 'readParms' => array('mask'=>'dd MM yyyy hh:ii:ss'), 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		//  'dblog_microtime'		=>   array ( 'title' => 'Microtime', 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_type' 			=>   array ( 'title' => RL_LAN_032, 'type' => 'method', 'data' => 'int', 'width' => '5%', 'filter' => true,  'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_ip' 			=>   array ( 'title' => LAN_IP, 'type' => 'ip', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		
		  'dblog_user_id' 		=>   array ( 'title' => LAN_USER, 'type' => 'user', 'data' => 'int', 'width' => 'auto', 'filter' => true,  'help' => '', 'readParms'=>'link=1', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_eventcode' 	=>   array ( 'title' => RL_LAN_023, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		
		  'dblog_title' 		=>   array ( 'title' => LAN_TITLE, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_remarks'		=>   array ( 'title' => RL_LAN_033, 'type' => 'method', 'data' => 'str', 'width' => '35%', 'filter'=>true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'options' 			=>   array ( 'title' => LAN_OPTIONS, 'type' => null, 'nolist'=>true, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array( 'dblog_datestamp',  'dblog_type', 'dblog_eventcode', 'dblog_user_id', 'dblog_ip', 'dblog_title', 'dblog_remarks');
		
		//'adminlog' => array(RL_LAN_019, RL_LAN_032, RL_LAN_020, RL_LAN_104, LAN_USER, RL_LAN_023, LAN_TITLE, RL_LAN_033), 
		
	
		protected $prefs = array(	
			'sys_log_perpage'			=> array('title'=> RL_LAN_044, 'type'=>'dropdown', 'data' => 'int','help'=> RL_LAN_064,'writeParms'=>''),
			'user_audit_class'			=> array('title'=> RL_LAN_123, 'type'=>'userclass', 'data' => 'int','help'=>''),
			'user_audit_opts'			=> array('title'=> RL_LAN_031, 'type'=>'method', 'data' => 'array','help'=>''),
			'roll_log_active'			=> array('title'=> RL_LAN_008, 'type'=>'boolean', 'data' => 'int','help'=>''),
			'roll_log_days'				=> array('title'=> RL_LAN_009, 'type'=>'number', 'data' => 'string','help'=>''),
		//	'Delete admin log events older than '		=> array('title'=> RL_LAN_045, 'type'=>'method', 'data' => 'string','help'=>'Help Text goes here'),
		//	'Delete user audit trail log events older'		=> array('title'=> 'Delete user audit trail log events older', 'type'=>'method', 'data' => 'string','help'=>'Help Text goes here'),
		); 
















		public $eventTypes = array();
		
		// optional
		public function init()
		{
			$perPage = e107::getConfig()->get('sys_log_perpage');
			$this->perPage = vartrue($perPage,10);
			
			$this->prefs['sys_log_perpage']['writeParms'] = array(10=>10, 15=>15, 20=>20, 30=>30, 40=>40, 50=>50);

			$this->eventTypes = loadEventTypes('admin_log');
			
			if(getperms('0'))
			{
				
				$arr = array_reverse($this->fields, true);
				$arr['checkboxes'] =  array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect'  );
				
				$this->fields = array_reverse($arr, true); 
				$this->batchDelete = true;
			}
		}


		function gen_log_delete($selectname)
		{
			$values = array(360, 180, 90, 60, 30, 21, 20, 14, 10, 7, 6, 5, 4, 3, 2, 1);
			return e107::getForm()->select($selectname, $values, '', 'useValues=1&size=small');
		}
	
		
		function maintenancePage()
		{

			if(!empty($_POST['deleteoldadmin']) || !empty($_POST['deleteoldaudit']))
			{
				$this->maintenanceProcess();

			}

			$frm = e107::getForm();



			// Admin log maintenance
			//==================
			$text = "
			<form method='post' action='".e_SELF."?mode=main&action=maintenance'>
				<fieldset id='core-admin-log-maintenance'>
					<legend>".RL_LAN_125."</legend>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".RL_LAN_045." </td>
								<td class='form-inline'>".$this->gen_log_delete('rolllog_clearadmin')." ".RL_LAN_046." ".$frm->admin_button('deleteoldadmin', 'no-value', 'delete', RL_LAN_049)."</td>
							</tr>
			";
		
			// User log maintenance
			//====================
			$text .= "
							<tr>
								<td>".RL_LAN_066." </td>
								<td class='form-inline'>".$this->gen_log_delete('rolllog_clearaudit')." ".RL_LAN_046." ".$frm->admin_button('deleteoldaudit', 'no-value', 'delete', RL_LAN_049)."</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</form>
		
			</fieldset>
			";
	 		return $text;
			
		}		
		
		
		function maintenanceProcess()
		{
			$mes = e107::getMessage();
			$ns = e107::getRender();
			$log = e107::getLog();
			$frm = e107::getForm();
			$sql = e107::getDb();
			$tp = e107::getParser();

			$back_count = 0;
			$action = '';

		//	print_a($_POST);
			
			if(!empty($_POST['deleteoldadmin']) && isset($_POST['rolllog_clearadmin']))
			{
				$back_count = intval($_POST['rolllog_clearadmin']);
				$_POST['backdeltype'] = 'confdel';
				$action = 'backdel';
			}
			elseif(!empty($_POST['deleteoldaudit']) && isset($_POST['rolllog_clearaudit']))
			{
				$back_count = intval($_POST['rolllog_clearaudit']);
				$action = 'backdel';
				$_POST['backdeltype'] = 'auditdel';
			}

			/*
			if(isset($back_count))
			{
				if(($back_count >= 1) && ($back_count <= 90))
				{
					$temp_date = getdate();
					$old_date = intval(mktime(0, 0, 0, $temp_date['mon'], $temp_date['mday'] - $back_count, $temp_date['year']));
					$old_string = strftime("%d %B %Y", $old_date);
					//	$message = "Back delete ".$back_count." days. Oldest date = ".$old_string;
					$action = $next_action;
					$qs[1] = $old_date;
					$qs[2] = $back_count;
				}
				else 
				{
					$mes->addWarning(RL_LAN_050);
				}
			}
			*/

			$old_date = strtotime($back_count.' days ago');
			
			
			// Actually delete back events - admin or user audit log
			if(($action == "backdel") && isset($_POST['backdeltype']))
			{
			//	$old_date = intval($qs[1]);
				$old_string = $tp->toDate($old_date, "%d %B %Y");
				$qry = "dblog_datestamp < ".$old_date; // Same field for both logs

				switch($_POST['backdeltype'])
				{
					case 'confdel':
						$db_table = 'admin_log';
						$db_name = RL_LAN_052;
						$db_msg = "ADLOG_02";
						break;
					case 'auditdel':
						$db_table = 'audit_log';
						$db_name = RL_LAN_053;
						$db_msg = "ADLOG_03";
						break;
					default:
						exit(); // Someone fooling around!
				}


				e107::getMessage()->addDebug("Back delete, <br />oldest date = {$old_string} <br />Query = {$qry}");

				if($del_count = $sql->delete($db_table, $qry))
				{
					// Add in a log event
					$message = $db_name.str_replace(array('[x]', '[y]'), array($old_string, $del_count), RL_LAN_057);
					$mes->addSuccess($message);
					$log->add($db_msg, "db_Delete - earlier than {$old_string} (past {$back_count} days)[!br!]".$message.'[!br!]'.$db_table.' '.$qry, E_LOG_INFORMATIVE, '');
				}
				else
				{
					$message = RL_LAN_054;
					$lastErrorText = $sql->getLastErrorText();
					if ($lastErrorText)
					{
						$message .= "<br />$lastErrorText";
					}
					$mes->addWarning($message);
				}

			}
			
			// Prompt to delete back events
			/*
			if(($action == "confdel") || ($action == "auditdel"))
			{
				$old_string = strftime("%d %B %Y", $qs[1]);
				$text = "
					<form method='post' action='".e_SELF."?backdel.{$qs[1]}.{$qs[2]}'>
						<fieldset id='core-admin-log-confirm-delete'>
							<legend class='e-hideme'>".LAN_CONFDELETE."</legend>
							<table class='table adminform'>
								<tr>
									<td class='center'>
										<strong>".(($action == "confdel") ? RL_LAN_047 : RL_LAN_065).$old_string."</strong>
									</td>
								</tr>
							</table>
							<div class='buttons-bar center'>
								<input type='hidden' name='backdeltype' value='{$action}' />
								".$frm->admin_button('confirmdeleteold', 'no-value', 'delete', RL_LAN_049)."
								".$frm->admin_button('confirmcancelold', 'no-value', 'delete', LAN_CANCEL)."				
							</div>
						</fieldset>
					</form>
			
				";
			
				$ns->tablerender(LAN_CONFDELETE, $text);
			}	
			
			*/
			
		}
		

			
}	


class admin_log_form_ui extends e_admin_form_ui
{

	function sys_log_perpage($curVal,$mode)
	{
		$frm = e107::getForm();
		switch($mode)
		{
			case 'write': // Edit Page
				return $frm->text('sys_log_perpage',$curVal);		
			break;	
		}
	}
	
	function user_audit_opts($curVal,$mode)
	{
		
		$frm = e107::getForm();
		
		// User Audit log options (for info)
		//=======================
		//	define('USER_AUDIT_SIGNUP',11);				// User signed up
		//	define('USER_AUDIT_EMAILACK',12);			// User responded to registration email
		//	define('USER_AUDIT_LOGIN',13);				// User logged in
		//	define('USER_AUDIT_LOGOUT',14);				// User logged out
		//	define('USER_AUDIT_NEW_DN',15);				// User changed display name
		//	define('USER_AUDIT_NEW_PW',16);				// User changed password
		//	define('USER_AUDIT_NEW_EML',17);			// User changed email
		//	define('USER_AUDIT_NEW_SET',19);			// User changed other settings (intentional gap in numbering)
		//	define('USER_AUDIT_ADD_ADMIN', 20); 		// User added by admin
		//	define('USER_AUDIT_MAIL_BOUNCE', 21); 		// User mail bounce
		//	define('USER_AUDIT_BANNED', 22); 			// User banned
		//	define('USER_AUDIT_BOUNCE_RESET', 23); 		// User bounce reset
		//	define('USER_AUDIT_TEMP_ACCOUNT', 24); 		// User temporary account
		
		
		$audit_checkboxes = array(USER_AUDIT_SIGNUP => RL_LAN_071, USER_AUDIT_EMAILACK => RL_LAN_072,
		 USER_AUDIT_LOGIN => LAN_AUDIT_LOG_013, 	USER_AUDIT_LOGOUT 	=> LAN_AUDIT_LOG_014,			// Logout is lumped in with login
		USER_AUDIT_NEW_DN => RL_LAN_075, USER_AUDIT_NEW_PW => RL_LAN_076, USER_AUDIT_PW_RES => RL_LAN_078, USER_AUDIT_NEW_EML => RL_LAN_077, USER_AUDIT_NEW_SET => RL_LAN_079, 
		USER_AUDIT_ADD_ADMIN => RL_LAN_080, USER_AUDIT_MAIL_BOUNCE => RL_LAN_081, USER_AUDIT_BANNED => RL_LAN_082, USER_AUDIT_BOUNCE_RESET => RL_LAN_083,
		USER_AUDIT_TEMP_ACCOUNT => RL_LAN_084);
	
		$userAuditOpts = e107::getConfig()->get('user_audit_opts');

		$text = "";
		
		foreach($audit_checkboxes as $k => $t)
		{
			$checked = isset($userAuditOpts[$k]) ? true : false;
			$text .= $frm->checkbox('user_audit_opts['.$k.']',$k, $checked, array('label'=>$t));	
		}
		
		$text .= $frm->admin_button('check_all', 'jstarget:user_audit_opts', 'checkall', LAN_CHECKALL).$frm->admin_button('uncheck_all', 'jstarget:user_audit_opts', 'checkall', LAN_UNCHECKALL);
	
	
		return $text;
	}
	
	
	
	
	// Custom Method/Function 
	/*
	function dblog_datestamp($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return date(AL_DATE_TIME_FORMAT, $curVal);
			break;
			
			case 'write': // Edit Page
				return $frm->text('dblog_datestamp',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  $array; 
			break;
		}
	}
	*/
	
	// Custom Method/Function 
	function dblog_microtime($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return date("H:i:s", intval($curVal) % 86400).'.'.str_pad(100000 * round($curVal - floor($curVal), 6), 6, '0');
			break;
			
			case 'write': // Edit Page
				return $frm->text('dblog_microtime',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
			//	return  $array;
			break;
		}
	}

	
	// Custom Method/Function 
	function dblog_type($curVal,$mode)
	{
		$tp = e107::getParser();
		/*
		define("E_LOG_INFORMATIVE", 0); // Minimal Log Level, including really minor stuff
		define("E_LOG_NOTICE", 1); // More important than informative, but less important than notice
		define("E_LOG_WARNING", 2); // Not anything serious, but important information
		define("E_LOG_FATAL", 3); //  An event so bad your site ceased execution.
		define("E_LOG_PLUGIN", 4); // Plugin information
		*/
		
		$array = array(
			' ',  // Minimal Log Level, including really minor stuff
			'<i class="S16 e-info-16 e-tip" title="Notice"></i>', //FIXME - should be the blue icon.  // More important than informative, but less important than notice
			'<i class="S16 e-false-16 e-tip" title="Important"></i>', // Not anything serious, but important information
		 	'<i class="S16 e-warning-16 e-tip" title="Warning"></i>', //  An event so bad your site ceased execution.
			' '  // Plugin information - Deprecated - Leave empty. 
		 );
		
		$array[1] = $tp->toGlyph('fa-question-circle'); 
		$array[2] = $tp->toGlyph('fa-exclamation-circle'); 
		$array[3] = $tp->toGlyph('fa-warning'); 
		
		switch($mode)
		{
			case 'read': // List Page
				return varset($array[$curVal], $curVal);
			break;
			
			case 'filter':
			case 'batch':
				return array(RL_LAN_132,RL_LAN_133,RL_LAN_134,RL_LAN_135);
			break;
		}
	}


	
	// Custom Method/Function 
	function dblog_title($curVal,$mode)
	{
		$tp = e107::getParser();
		
		switch($mode)
		{
			case 'read': // List Page



				$val = trim($curVal);
				if(defined($val))
				{
					$val = constant($val);
				}

				if(strpos($val,'[x]') !== false)
				{
					$remark = $this->getController()->getListModel()->get('dblog_remarks');
					preg_match("/\[table\]\s=&gt;\s([\w]*)/i",$remark, $match);

					if(!empty($match[1]))
					{
						$val = $tp->lanVars($val, '<b>'.$match[1].'</b>');
					}
					else
					{
						preg_match("/\[!br!\]TABLE: ([\w]*)/i", $remark, $m);
						if(!empty($m[1]))
						{
							$val = $tp->lanVars($val, '<b>'.$m[1].'</b>');
						}
					}


				}

				return $val;
			break;
			
			case 'filter':
			case 'batch':
				return  null;
			break;
		}
	}

	
	function dblog_eventcode($curVal,$mode)
	{
		$array = $this->getController()->eventTypes;
		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'filter':
			case 'batch':
				return  $array; 
			break;
		}
	}


	// Custom Method/Function 
	function dblog_remarks($curVal,$mode)
	{
		$frm = e107::getForm();		
		$tp = e107::getParser();
				 		
		switch($mode)
		{
			case 'read': // List Page
			
				$text = preg_replace_callback("#\[!(\w+?)(=.+?)?!]#", 'log_process', $curVal);
				$text = $tp->toHTML($text,false,'E_BODY');
				
				if(strpos($text,'Array')!==false || strlen($text)>300)
				{
					$id = $this->getController()->getListModel()->get('dblog_id');
					$ret ="<a class='e-expandit' href='#rem-".$id."'>".RL_LAN_087."</a>";
					$ret .= "<div style='display:none' id='rem-".$id."'>";
					$text = str_replace("<br />","\n",$text);
					$text = str_replace("&#092;","/",$text);
					
					if(strpos($text,'\n') === 0) // cleanup (not sure of the cause)
					{
						$text = substr($text,2);	
					}
					
					if(substr($text,-2) == '\n') // cleanup (not sure of the cause)
					{
						$text = substr($text,0,-2);	
					}
					
					$text = print_a($text,true);	
					$ret .= $text;
					$ret .= "</div>";
					return $ret;
				}		
		 	
				return $text;
			break;

			
			case 'filter':
			case 'batch':
			//	return  $array;
			break;
		}
	}

// Custom Method/Function 
	function dblog_caller($curVal,$mode)
	{
		 		
		switch($mode)
		{
			case 'read': // List Page
				$val =$curVal;
				if((strpos($val, '|') !== FALSE) && (strpos($val, '@') !== FALSE))
				{
					list($file, $rest) = explode('|', $val);
					list($routine, $rest) = explode('@', $rest);
					$val = $file.'<br />Function: '.$routine.'<br />Line: '.$rest;
				}
				return $val;
			break;

			
			case 'filter':
			case 'batch':
			//	return  $array;
			break;
		}
	}

}		
		

				
class audit_log_ui extends e_admin_ui
{
			
		protected $pluginTitle		=  ADLAN_155;
		protected $pluginName		= 'core';
		protected $table			= 'audit_log';
		protected $pid				= 'dblog_id';
		protected $perPage 			= 10;
		protected $listOrder        = 'dblog_id DESC';
		protected $batchDelete		= true;
			
		protected $fields 		= array (  
		'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'dblog_id' 			=>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_datestamp' 	=>   array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => array('mask'=>'dd MM yyyy hh:ii:ss'), 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_microtime' 	=>   array ( 'title' => 'Microtime', 'type' => 'text', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_eventcode' 	=>   array ( 'title' => 'Eventcode', 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_user_id' 		=>   array ( 'title' => LAN_USER, 'type' => 'user', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms'=>'link=1', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	//	  'dblog_user_name' 	=>   array ( 'title' => LAN_USER, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_ip' 			=>   array ( 'title' => LAN_IP, 'type' => 'ip', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_title' 		=>   array ( 'title' => LAN_TITLE, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_remarks' 		=>   array ( 'title' => 'Remarks', 'type' => 'method', 'data' => 'str', 'width' => '30%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'options' 			=>   array ( 'title' => LAN_OPTIONS, 'type' => null,  'nolist'=>true, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('dblog_id', 'dblog_datestamp', 'dblog_microtime', 'dblog_eventcode', 'dblog_user_id', 'dblog_user_name', 'dblog_ip', 'dblog_title','dblog_remarks');
		
		public $eventTypes = array();

		// optional
		public function init()
		{
			$perPage = e107::getConfig()->get('sys_log_perpage');
			$this->perPage = vartrue($perPage,10);
			$this->eventTypes = loadEventTypes('audit_log');
		}

	/*
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
	*/
			
}
				


	
		

				
class dblog_ui extends e_admin_ui
{
			
		protected $pluginTitle		=  ADLAN_155;
		protected $pluginName		= 'core';
		protected $table			= 'dblog';
		protected $pid				= 'dblog_id';
		protected $perPage 			= 15; 
		protected $listOrder		= 'dblog_id desc';
			
		protected $fields 		= array (  
		  'checkboxes' 			=>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
	//	  'dblog_id' 			=>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_datestamp' 	=>   array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => array('mask'=>'dd MM yyyy hh:ii:ss'), 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_microtime' 	=>   array ( 'title' => 'Microtime', 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_type' 			=>   array ( 'title' => LAN_TYPE, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_eventcode' 	=>   array ( 'title' => 'Eventcode', 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_user_id' 		=>   array ( 'title' => LAN_ID, 'type' => 'user', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_user_name' 	=>   array ( 'title' => LAN_USER, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_ip' 			=>   array ( 'title' => LAN_IP, 'type' => 'ip', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_caller' 		=>   array ( 'title' => 'Caller', 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'dblog_title' 		=>   array ( 'title' => LAN_TITLE, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'dblog_remarks' 		=>   array ( 'title' => 'Remarks', 'type' => 'method', 'data' => 'str', 'width' => '30%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'options' 			=>   array ( 'title' => LAN_OPTIONS, 'type' => null,  'nolist'=>true,  'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);

		protected $fieldpref = array('dblog_id', 'dblog_datestamp', 'dblog_microtime', 'dblog_type', 'dblog_eventcode', 'dblog_user_id', 'dblog_user_name', 'dblog_ip', 'dblog_caller', 'dblog_title', 'dblog_remarks');

	public $eventTypes = array();

	// optional
	public function init()
	{
		$perPage            = e107::getConfig()->get('sys_log_perpage');
		$this->perPage      = vartrue($perPage,10);
		$this->eventTypes   = loadEventTypes('dblog');
	}

}
				

	

// Routine to handle the simple bbcode-like codes for log body text

		function log_process($matches)
		{
			switch($matches[1])
			{
				case 'br':
					return '<br />';
				case 'link':
					$temp = substr($matches[2], 1);
					return "<a href='{$temp}'>{$temp}</a>";
				case 'test':
					return '----TEST----';
				default:
					return $matches[0]; // No change
			}
		}		





		
new adminlog_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");




