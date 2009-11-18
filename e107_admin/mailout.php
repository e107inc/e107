<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Maintenance
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/mailout.php,v $
 * $Revision: 1.27 $
 * $Date: 2009-11-18 20:45:18 $
 * $Author: e107steved $
 *
*/

/*
TODO:
	1. Improve maintenance screen
	2. 'Mail hold' function
*/

/*
Features:
1. Additional sources of email addresses for mailouts can be provided via plugins
2. Both list of email recipients and the email are separately stored in the DB using a documented interface (allows alternative creation/mailout routines)
3. Can specify qmail in the sendmail path

Interface to add extra mailout (source) handlers - these provide email addresses:
1. The handler is called 'e_mailout.php' in the plugin directory.
2. Mailout options has a facility to enable the individual handlers
3. Certain variables may be defined at load time to determine whether this is exclusive or supplementary
4. The class name must be 'mailout_plugin_path'


$pref['mailout_enabled'][plugin_path] - array of flags determining which mailers are active

*/


/*
All information is stored in two db tables (see mail_manager_class.php)

Each mailout task is implemented as a class, which must include a number of mandatory entry points:
	show_select($allow_edit = FALSE) - in edit mode, returns text which facilitates address selection. Otherwise shows the current selection criteria
	function returnSelectors() - returns storable representation of user-entered selection criteria
	select_init() - initialise the selection mechanism
	select_add() - routine pulls out email addresses etc, for caller to add to the list of addressees
	select_close() - selection complete

*/


/*
Valid actions ($_GET['mode']):
	'prefs' - Edit options

	'makemail' - Create an email for use as a template, or to send

	'saved' - email templates saved (was 'list')

	'sent' - list emails where sending process complete (was 'mailouts')
	'pending' - list emails in queue or being sent

	'maildelete' - delete email whose 'handle' is in $mailID - shows confirmation page
	'maildeleteconfirm' - does it

	'edit' - edit email whose 'handle' is in $mailID

	'detail' - show all the target recipients of a specific email

	'resend' - resend failures on a specific list

	'orphans' - data tidy routine

	'debug' - not currently used; may be useful to list other info
 
Valid subparameters (where required):
	$_GET['m'] - id of mail info in db
	$_GET['t'] - id of target info in db
*/

require_once('../class2.php');

if (!getperms('W'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'mail';

// TODO: Possibly next two lines not needed any more?
//set_time_limit(180);
//session_write_close();



require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'ren_help.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_users.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_mailout.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'mailout_class.php');		// Class handler for core mailout functions
require_once(e_HANDLER.'mailout_admin_class.php');			// Admin tasks handler
require_once(e_HANDLER.'mail_manager_class.php');	// Mail DB API
require_once (e_HANDLER.'message_handler.php');
$emessage = &eMessage :: getInstance();


$action = $e107->tp->toDB(varset($_GET['mode'],'makemail'));
$pageMode = varset($_GET['savepage'], $action);			// Sometimes we need to know what brought us here - $action gets changed
$mailId = intval(varset($_GET['m'],0));
$targetId = intval(varset($_GET['t'],0));


// Create mail admin object, load all mail handlers
$mailAdmin = new mailoutAdminClass($action);			// This decodes parts of the query using $_GET syntax
if ($mailAdmin->loadMailHandlers() == 0)
{	// No mail handlers loaded
	echo 'No mail handlers loaded!!';
	exit;
}


$errors = array();

$subAction = '';
$midAction = '';


if (isset($_POST['mailaction']))
{
	if (is_array($_POST['mailaction']))
	{
		foreach ($_POST['mailaction'] as $k => $v)
		{
			if ($v)		// Look for non-empty action
			{
				$mailId = $k;
				$action = $v;
				break;
			}
		}
	}
}


if (isset($_POST['targetaction']))
{
	if (is_array($_POST['targetaction']))
	{
		foreach ($_POST['targetaction'] as $k => $v)
		{
			if ($v)		// Look for non-empty action
			{
				$targetId = $k;
				$action = $v;
				break;
			}
		}
	}
}

//echo "Action: {$action}  MailId: {$mailId}  Target: {$targetId}<br />";
// ----------------- Actions ----------------------------------------------->
switch ($action)
{
	case 'prefs' :
		if (getperms('0'))
		{
			if (isset($_POST['testemail'])) 
			{		//		Send test email - uses standard 'single email' handler
				if(trim($_POST['testaddress']) == '')
				{
					$emessage->add(LAN_MAILOUT_19, E_MESSAGE_ERROR);
					$subAction = 'error';
				}
				else
				{
					$mailheader_e107id = USERID;
					require_once(e_HANDLER.'mail.php');
					$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).")" : ' (PHP)';
					$sendto = trim($_POST['testaddress']);
					if (!sendemail($sendto, LAN_MAILOUT_113." ".SITENAME.$add, LAN_MAILOUT_114,LAN_MAILOUT_189)) 
					{
						$emessage->add(($pref['mailer'] == 'smtp')  ? LAN_MAILOUT_67 : LAN_MAILOUT_106, E_MESSAGE_ERROR);
					} 
					else 
					{
						$emessage->add(LAN_MAILOUT_81. ' ('.$sendto.')', E_MESSAGE_SUCCESS);
						$admin_log->log_event('MAIL_01',$sendto,E_LOG_INFORMATIVE,'');
					}
				}
			}
			elseif (isset($_POST['updateprefs']))
			{
				saveMailPrefs($emessage);
			}
		}
		break;

	case 'mailedit' :		// Edit existing mail
		if (isset($_POST['mailaction']))
		{
			$action = 'makemail';
			$mailData = $mailAdmin->retrieveEmail($mailId);
			if ($mailData === FALSE)
			{
				$emessage->add(LAN_MAILOUT_164.':'.$mailId, E_MESSAGE_ERROR);
				break;
			}
		}
		break;

	case 'makemail' :
		$newMail = TRUE;
		if (isset($_POST['save_email']))
		{
			$subAction = 'new';
		}
		elseif (isset($_POST['update_email']))
		{
			$subAction = 'update';
			$newMail = FALSE;
		}
		elseif (isset($_POST['send_email'])) 
		{	// Send bulk email
			$subAction = 'send';
		}
		if ($subAction != '')
		{
			$mailData = $mailAdmin->parseEmailPost($newMail);
			$errors = $mailAdmin->checkEmailPost($mailData, $subAction == 'send');		// Full check if sending email
			if ($errors !== TRUE)
			{
				$subAction = 'error';
				break;
			}
			$mailData['mail_selectors'] = $mailAdmin->getAllSelectors();	// Add in the selection criteria
		}

		// That's the checking over - now do something useful!
		switch ($subAction)
		{
			case 'send' :					// This actually creates the list of recipients in the display routine
				$action = 'marksend';
				break;
			case 'new' :
				// TODO: Check all fields created - maybe 
				$mailData['mail_content_status'] = MAIL_STATUS_SAVED;
				$mailData['mail_create_app'] = 'core';
				$result = $mailAdmin->saveEmail($mailData, TRUE);
				if (is_numeric($result))
				{
					$mailData['mail_source_id'] = $result;
					$emessage->add(LAN_MAILOUT_145, E_MESSAGE_SUCCESS);
				}
				else
				{
					$emessage->add(LAN_MAILOUT_146, E_MESSAGE_ERROR);
				}
				break;
			case 'update' :
				$mailData['mail_content_status'] = MAIL_STATUS_SAVED;
				$result = $mailAdmin->saveEmail($mailData, FALSE);
				if (is_numeric($result))
				{
					$mailData['mail_source_id'] = $result;
					$emessage->add(LAN_MAILOUT_147, E_MESSAGE_SUCCESS);
				}
				else
				{
					$emessage->add(LAN_MAILOUT_146, E_MESSAGE_ERROR);
				}
				break;
		}
		break;

	case 'mailhold' :
		$emessage->add('Mail hold - not implemented yet: '.$mailId, E_MESSAGE_ERROR);
		break;

	case 'mailcancel' :
		$action = $pageMode;		// Want to return to some other page
		if ($mailAdmin->cancelEmail($mailId))
		{
			$emessage->add(str_replace('--ID--', $mailId, LAN_MAILOUT_220), E_MESSAGE_SUCCESS);
		}
		else
		{
			$errors[] = str_replace('--ID--', $mailId, LAN_MAILOUT_221);
		}
		break;

	case 'maildelete' :
		break;

	case 'mailtargets' :
		$action = 'recipients';
		// Intentional fall-through
	case 'recipients' :
		if (isset($_POST['etrigger_ecolumns']))
		{
			$mailAdmin->mailbodySaveColumnPref($action);
		}
		break;

	case 'marksend' :			// Actually do something with an email and list of recipients - entry from email confirm page
		$action = 'saved';
		if (isset($_POST['email_cancel']))		// 'Cancel' in this context means 'delete' - don't want it any more
		{
			$midAction = 'midDeleteEmail';
		}
		elseif (isset($_POST['email_hold']))
		{
			if ($mailAdmin->activateEmail($mailId, TRUE))
			{
				$emessage->add(str_replace('--ID--', $mailId, LAN_MAILOUT_187), E_MESSAGE_SUCCESS);
			}
			else
			{
				$errors[] = str_replace('--ID--', $mailId, LAN_MAILOUT_166);
			}
			$action = 'held';
		}
		elseif (isset($_POST['email_send']))
		{
			$midAction = 'midMoveToSend';
			$action = 'pending';
		}
		break;

	case 'mailsendnow' :			// Send mail previously on 'held' list. 
		$midAction = 'midMoveToSend';
		$action = 'pending';
		break;

	case 'maildeleteconfirm' :
		$action = $pageMode;		// Want to return to some other page
		$midAction = 'midDeleteEmail';
		if (!isset($_POST['mailIDConf']) || (intval($_POST['mailIDConf']) != $mailId))
		{
			$errors[] = str_replace(array('--ID--', '--CHECK--'), array($mailId, intval($_POST['mailIDConf'])), LAN_MAILOUT_174);
			break;
		}
		break;

	case 'mailonedelete' :
	case 'debug' :
		$emessage->add('Not implemented yet', E_MESSAGE_ERROR);
		break;

	case 'saved' :		// Show template emails - probably no actions
		if (isset($_POST['etrigger_ecolumns']))
		{
			$mailAdmin->mailbodySaveColumnPref($action);
		}
		break;

	case 'sent' :
	case 'pending' :
	case 'held' :
		if (isset($_POST['etrigger_ecolumns']))
		{
			$mailAdmin->mailbodySaveColumnPref($action);
		}
		break;

	case 'maint' :		// Perform any maintenance actions required
		if (isset($_POST['email_dross']))
		if ($mailAdmin->dbTidy())
		{
			$emessage->add(LAN_MAILOUT_184, E_MESSAGE_SUCCESS);
		}
		else
		{
			$errors[] = LAN_MAILOUT_183;
		}
		break;

	default :
		$emessage->add('Code malfunction 23! ('.$action.')', E_MESSAGE_ERROR);
		$e107->ns->tablerender(LAN_MAILOUT_97, $emessage->render());
		exit;			// Could be a hack attempt
}	// switch($action) - end of 'executive' tasks



// ------------------------ Intermediate actions ---------------------------
// (These have more than one route to trigger them)
switch ($midAction)
{
	case 'midDeleteEmail' :
//		$emessage->add($pageMode.': Would delete here: '.$mailId, E_MESSAGE_SUCCESS);
//		break;														// Delete this
		$result = $mailAdmin->deleteEmail($mailId, 'all');
		if (($result === FALSE) || !is_array($result))
		{
			$errors[] = str_replace('--ID--', $mailId, LAN_MAILOUT_166);
		}
		else
		{
			if (isset($result['content']))
			{
				if ($result['content'] === FALSE)
				{
					$errors[] = str_replace('--ID--', $mailId, LAN_MAILOUT_167);
				}
				else
				{
					$emessage->add(str_replace('--ID--', $mailId, LAN_MAILOUT_167), E_MESSAGE_SUCCESS);
				}
			}
			if (isset($result['recipients']))
			{
				if ($result['recipients'] === FALSE)
				{
					$errors[] = str_replace('--ID--', $mailId, LAN_MAILOUT_169);
				}
				else
				{
					$emessage->add(str_replace(array('--ID--', '--NUM--'), array($mailId, $result['recipients']), LAN_MAILOUT_170), E_MESSAGE_SUCCESS);
				}
			}
		}
		break;
	case 'midMoveToSend' :
		if ($mailAdmin->activateEmail($mailId, FALSE))
		{
			$emessage->add(LAN_MAILOUT_185, E_MESSAGE_SUCCESS);
		}
		else
		{
			$errors[] = str_replace('--ID--', $mailId, LAN_MAILOUT_188);
		}
		break;
}



// --------------------- Display errors and results ------------------------
if (is_array($errors) && (count($errors) > 0))
{
	foreach ($errors as $e)
	{
		$emessage->add($e, E_MESSAGE_ERROR);
	}
	unset($errors);
}
if ($emessage->hasMessage())
{
	$e107->ns->tablerender(LAN_MAILOUT_97, $emessage->render());
}


// ---------------------- Display required page ----------------------------
// At this point $action determines which page display is required - one of a 
// fairly limited number of choices
$mailAdmin->newMode($action);
//echo "Action: {$action}  MailId: {$mailId}  Target: {$targetId}<br />";

switch ($action)
{
	case 'prefs' :
		if (getperms('0'))
		{
			show_prefs($mailAdmin);
		}
		break;

	case 'maint' :
		if (getperms('0'))
		{
			show_maint(FALSE);
		}
		break;
	
	case 'debug' :
		if (getperms('0'))
		{
			show_maint(TRUE);
		}
		break;

	case 'saved' :				// Show template emails
	case 'sent' :
	case 'pending' :
	case 'held' :
		$mailAdmin->showEmailList($action, -1, -1);
		break;

	case 'maildelete' :			// NOTE:: need to set previous page in form
		$mailAdmin->showDeleteConfirm($mailId, $pageMode);
		break;

	case 'marksend' :			// Show the send confirmation page
		$mailAdmin->sendEmailCircular($mailData);
		break;

	case 'recipients' :
		$mailAdmin->showmailRecipients($mailId, $action);
		break;

	case 'makemail' :
	default :
		if (!is_array($mailData))
		{
			$mailData = array();			// Empty array just in case
		}
		$mailAdmin->show_mailform($mailData);
		break;
}



require_once(e_ADMIN."footer.php");




// Update Preferences. (security handled elsewhere)
function saveMailPrefs(&$emessage)
{
	global $pref;
	$e107 = e107::getInstance();
	unset($temp);
	if (!in_array($_POST['mailer'], array('smtp', 'sendmail', 'php'))) $_POST['mailer'] = 'php';
	$temp['mailer'] = $_POST['mailer'];
	// Allow qmail as an option as well - works much as sendmail
	if ((strpos($_POST['sendmail'],'sendmail') !== FALSE) || (strpos($_POST['sendmail'],'qmail') !== FALSE)) $temp['sendmail'] = $e107->tp->toDB($_POST['sendmail']);
	$temp['smtp_server'] 	= $e107->tp->toDB($_POST['smtp_server']);
	$temp['smtp_username'] 	= $e107->tp->toDB($_POST['smtp_username']);
	$temp['smtp_password'] 	= $e107->tp->toDB($_POST['smtp_password']);

	$smtp_opts = array();
	switch (trim($_POST['smtp_options']))
	{
	  case 'smtp_ssl' :
	    $smtp_opts[] = 'secure=SSL';
		break;
	  case 'smtp_tls' :
	    $smtp_opts[] = 'secure=TLS';
		break;
	  case 'smtp_pop3auth' :
	    $smtp_opts[] = 'pop3auth';
		break;
	}
	if (varsettrue($_POST['smtp_keepalive'])) $smtp_opts[] = 'keepalive';
	if (varsettrue($_POST['smtp_useVERP'])) $smtp_opts[] = 'useVERP';

	$temp['smtp_options'] = implode(',',$smtp_opts);

	$temp['mail_sendstyle'] = $e107->tp->toDB($_POST['mail_sendstyle']);
	$temp['mail_pause'] 	= intval($_POST['mail_pause']);
	$temp['mail_pausetime'] = intval($_POST['mail_pausetime']);
	$temp['mail_workpertick'] = intval($_POST['mail_workpertick']);
	$temp['mail_workpertick'] = min($temp['mail_workpertick'],1000);
	$temp['mail_bounce_email'] = $e107->tp->toDB($_POST['mail_bounce_email']);
	$temp['mail_bounce_pop3'] = $e107->tp->toDB($_POST['mail_bounce_pop3']);
	$temp['mail_bounce_user'] =	$e107->tp->toDB($_POST['mail_bounce_user']);
	$temp['mail_bounce_pass'] = $e107->tp->toDB($_POST['mail_bounce_pass']);
	$temp['mail_bounce_type'] = $e107->tp->toDB($_POST['mail_bounce_type']);
	$temp['mail_bounce_delete'] = intval($_POST['mail_bounce_delete']);

	$temp['mailout_enabled'] = implode(',',$_POST['mail_mailer_enabled']);
	$temp['mail_log_options'] = intval($_POST['mail_log_option']).','.intval($_POST['mail_log_email']);

	if ($e107->admin_log->logArrayDiffs($temp, $pref, 'MAIL_03'))
	{
		save_prefs();		// Only save if changes - generates its own message
//		$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(LAN_NO_CHANGE, E_MESSAGE_INFO);
	}
}



//----------------------------------------------------
//		MAILER OPTIONS
//----------------------------------------------------

function show_prefs($mailAdmin)
{
	global $pref;
	$e107 = e107::getInstance();
	$frm = e107::getForm();
	$mes = e107::getMessage();


	e107::getCache()->CachePageMD5 = '_';
	$lastload = e107::getCache()->retrieve('emailLastBounce',FALSE,TRUE,TRUE);
	$lastBounce = round((time() - $lastload) / 60);
	
	$lastBounceText = ($lastBounce > 1256474) ? "<b>Never</b>" : "<b>".$lastBounce . " minutes </b>ago."; 

	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."' id='mailsettingsform'>
		<div id='mail'>
		<table cellpadding='0' cellspacing='0' class='adminform'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tr>
			<td>".LAN_MAILOUT_110."<br /></td>
			<td>".$frm->admin_button(testemail, LAN_MAILOUT_112)."&nbsp;
			<input name='testaddress' class='tbox' type='text' size='40' maxlength='80' value=\"".(varset($_POST['testaddress']) ? $_POST['testaddress'] : USEREMAIL)."\" />
			</td>
		</tr>

		<tr>
		<td style='vertical-align:top'>".LAN_MAILOUT_115."<br /></td>
		<td>
		<select class='tbox' name='mailer' onchange='disp(this.value)'>\n";
	$mailers = array('php','smtp','sendmail');
	foreach($mailers as $opt)
	{
		$sel = ($pref['mailer'] == $opt) ? "selected='selected'" : "";
		$text .= "<option value='{$opt}' {$sel}>{$opt}</option>\n";
	}
	$text .="</select> <span class='field-help'>".LAN_MAILOUT_116."</span><br />";



// SMTP. -------------->
	$smtp_opts = explode(',',varset($pref['smtp_options'],''));
	$smtpdisp = ($pref['mailer'] != 'smtp') ? "style='display:none;'" : '';
	$text .= "<div id='smtp' {$smtpdisp}>
		<table style='margin-right:auto;margin-left:0px;border:0px'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		";
	$text .= "
		<tr>
		<td>".LAN_MAILOUT_87.":&nbsp;&nbsp;</td>
		<td>
		<input class='tbox' type='text' name='smtp_server' size='40' value='".$pref['smtp_server']."' maxlength='50' />
		</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_88.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
		<td style='width:50%;' >
		<input class='tbox' type='text' name='smtp_username' size='40' value=\"".$pref['smtp_username']."\" maxlength='50' />
		</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_89.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
		<td>
		<input class='tbox' type='password' name='smtp_password' size='40' value='".$pref['smtp_password']."' maxlength='50' />
		</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_90."</td><td>
		<select class='tbox' name='smtp_options'>\n
		<option value=''>".LAN_MAILOUT_96."</option>\n";
	$selected = (in_array('secure=SSL',$smtp_opts) ? " selected='selected'" : '');
	$text .= "<option value='smtp_ssl'{$selected}>".LAN_MAILOUT_92."</option>\n";
	$selected = (in_array('secure=TLS',$smtp_opts) ? " selected='selected'" : '');
	$text .= "<option value='smtp_tls'{$selected}>".LAN_MAILOUT_93."</option>\n";
	$selected = (in_array('pop3auth',$smtp_opts) ? " selected='selected'" : '');
	$text .= "<option value='smtp_pop3auth'{$selected}>".LAN_MAILOUT_91."</option>\n";
	$text .= "</select>\n<br />".LAN_MAILOUT_94."</td></tr>";

	$text .= "<tr>
		<td>".LAN_MAILOUT_57."</td><td>
		";
	$checked = (varsettrue($pref['smtp_keepalive']) ) ? "checked='checked'" : "";
	$text .= "<input type='checkbox' name='smtp_keepalive' value='1' {$checked} />
		</td>
		</tr>";

	$checked = (in_array('useVERP',$smtp_opts) ? "checked='checked'" : "");
	$text .= "<tr>
		<td>".LAN_MAILOUT_95."</td><td>
		<input type='checkbox' name='smtp_useVERP' value='1' {$checked} />
		</td>
		</tr>
		</table></div>";


// Sendmail. -------------->
	$senddisp = ($pref['mailer'] != 'sendmail') ? "style='display:none;'" : "";
	$text .= "<div id='sendmail' {$senddisp}><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "

	<tr>
	<td>".LAN_MAILOUT_20.":&nbsp;&nbsp;</td>
	<td>
	<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
	</td>
	</tr>

	</table></div>";


	$text .="</td>
	</tr>


	<tr>
		<td>".LAN_MAILOUT_222."</td>
		<td>";
	$text .= $mailAdmin->sendStyleSelect(varset($pref['mail_sendstyle'], 'textonly'), 'mail_sendstyle');
	$text .= 
		"<span class='field-help'>".LAN_MAILOUT_223."</span>
		</td>
	</tr>\n

	
	<tr>
		<td>".LAN_MAILOUT_25."</td>
		<td> ".LAN_MAILOUT_26."
		<input class='tbox' size='3' type='text' name='mail_pause' value='".$pref['mail_pause']."' /> ".LAN_MAILOUT_27.
		"<input class='tbox' size='3' type='text' name='mail_pausetime' value='".$pref['mail_pausetime']."' /> ".LAN_MAILOUT_29.".<br />
		<span class='field-help'>".LAN_MAILOUT_30."</span>
		</td>
	</tr>\n
	
	<tr>
		<td>".LAN_MAILOUT_156."</td>
		<td><input class='tbox' size='3' type='text' name='mail_workpertick' value='".varset($pref['mail_workpertick'],5)."' />
		<span class='field-help'>".LAN_MAILOUT_157."</span>
		</td>
	</tr>\n";

	if (isset($pref['e_mailout_list']))
	{  // Allow selection of email address sources
		$text .= "<tr>
		<td>".LAN_MAILOUT_77."</td>
		<td> 
	  ";
	  $mail_enable = explode(',',$pref['mailout_enabled']);
	  foreach ($pref['e_mailout_list'] as $mailer => $v)	  {
		$check = (in_array($mailer,$mail_enable)) ? "checked='checked'" : "";
		$text .= "&nbsp;<input type='checkbox' name='mail_mailer_enabled[]' value='{$mailer}' {$check} /> {$mailer}<br />";
	  }
	  $text .= "</td></tr>\n";
	}

	list($mail_log_option,$mail_log_email) = explode(',',varset($pref['mail_log_options'],'0,0'));
	$check = ($mail_log_email == 1) ? " checked='checked'" : "";
	$text .= "<tr>
		<td>".LAN_MAILOUT_72."</td>
		<td> 
		<select class='tbox' name='mail_log_option'>\n
		<option value='0'".(($mail_log_option==0) ? " selected='selected'" : "").">".LAN_MAILOUT_73."</option>\n
		<option value='1'".(($mail_log_option==1) ? " selected='selected'" : "").">".LAN_MAILOUT_74."</option>\n
		<option value='2'".(($mail_log_option==2) ? " selected='selected'" : "").">".LAN_MAILOUT_75."</option>\n
		<option value='3'".(($mail_log_option==3) ? " selected='selected'" : "").">".LAN_MAILOUT_119."</option>\n
		</select>\n
		<input type='checkbox' name='mail_log_email' value='1' {$check} />".LAN_MAILOUT_76.
		"</td>
	</tr>\n";

	$text .= "</table>
	<fieldset id='core-mail-prefs-bounce'>
		<legend".($mode ? " class='e-hideme'" : "").">".LAN_MAILOUT_31."</legend>
		<table cellpadding='0' cellspacing='0' class='adminedit'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
	<tr>
		<td>Last Bounce Processed</td><td>".$lastBounceText."</td>
	</tr>
	
	<tr>
		<td>Auto-process script</td><td><b>".(e_DOCROOT ? substr(e_DOCROOT, 0, -1) : '/').e_HANDLER_ABS."bounce_handler.php</b>";
		
	if(!is_readable(e_HANDLER.'bounce_handler.php'))
	{
		$text .= "<br /><span class='required'>".LAN_MAILOUT_161."</span>";
	}
	elseif(!is_executable(e_HANDLER.'bounce_handler.php'))		// Seems to give wrong answers on Windoze
	{
		$text .= "<br /><span class='required'>".LAN_MAILOUT_162."</span>";
	}

	// TODO: Determine type of bounce processing
	$text .= "</td>
		</tr>		
		<tr><td>".LAN_MAILOUT_32."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_email' value=\"".$pref['mail_bounce_email']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_33."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_pop3' value=\"".$pref['mail_bounce_pop3']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_34."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_user' value=\"".$pref['mail_bounce_user']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_35."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_pass' value=\"".$pref['mail_bounce_pass']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_120."</td><td><select class='tbox' name='mail_bounce_type'>\n
			<option value=''>&nbsp;</option>\n
			<option value='pop3'".(($pref['mail_bounce_type']=='pop3') ? " selected='selected'" : "").">".LAN_MAILOUT_121."</option>\n
			<option value='pop3/notls'".(($pref['mail_bounce_type']=='pop3/notls') ? " selected='selected'" : "").">".LAN_MAILOUT_122."</option>\n
			<option value='pop3/tls'".(($pref['mail_bounce_type']=='pop3/tls') ? " selected='selected'" : "").">".LAN_MAILOUT_123."</option>\n
			<option value='imap'".(($pref['mail_bounce_type']=='imap') ? " selected='selected'" : "").">".LAN_MAILOUT_124."</option>\n
		</select></td></tr>\n
		";

	$check = ($pref['mail_bounce_delete']==1) ? " checked='checked'" : "";
	$text .= "<tr><td>".LAN_MAILOUT_36."</td><td><input type='checkbox' name='mail_bounce_delete' value='1' {$check} /></td></tr>

	</tbody>
	</table></fieldset>

	<div class='buttons-bar center'>".$frm->admin_button('updateprefs',LAN_MAILOUT_28)."</div>

	</div></form>";

	$caption = ADLAN_136." :: ".LAN_PREFS;
	$e107->ns->tablerender($caption,$mes->render(). $text);
}



//-----------------------------------------------------------
//			MAINTENANCE OPTIONS
//-----------------------------------------------------------
function show_maint($debug = FALSE)
{
	$e107 = e107::getInstance();
	$text = "<div style='text-align:center'>";

	$text .= "
			<form action='".e_SELF."?mode=maint' id='email_maint' method='post'>
			<fieldset id='email-maint'>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
			<colgroup span='2'>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			
			<tbody>";

		$text .= "<tr><td colspan='2'>".LAN_MAILOUT_182." <input class='button' type='submit' name='email_dross' value=\"".LAN_SUBMIT."\" /></td></tr>";
		$text .= "</tbody></table>\n</fieldset>";

		$e107->ns->tablerender("<div style='text-align:center'>".ADLAN_136." :: ".ADLAN_40."</div>", $text);

//	$text .= "</table></div>";
}






function mailout_adminmenu() 
{
	$e107 = e107::getInstance();
	$action = $e107->tp->toDB(varset($_GET['mode'],'makemail'));
	if($action == 'mailedit')
	{
    	$action = 'makemail';
	}
    $var['post']['text'] = LAN_MAILOUT_190;
	$var['post']['link'] = e_SELF;
	$var['post']['perm'] = 'W';

    $var['saved']['text'] = LAN_MAILOUT_191;		// Saved emails
	$var['saved']['link'] = e_SELF.'?mode=saved';
	$var['saved']['perm'] = 'W';

    $var['pending']['text'] = LAN_MAILOUT_193;		// Pending email runs
	$var['pending']['link'] = e_SELF.'?mode=pending';
	$var['pending']['perm'] = 'W';

    $var['held']['text'] = LAN_MAILOUT_194;			// Held email runs
	$var['held']['link'] = e_SELF.'?mode=held';
	$var['held']['perm'] = 'W';

    $var['sent']['text'] = LAN_MAILOUT_192;			// Completed email runs
	$var['sent']['link'] = e_SELF.'?mode=sent';
	$var['sent']['perm'] = 'W';

	if(getperms("0"))
	{
		$var['prefs']['text'] = LAN_PREFS;
		$var['prefs']['link'] = e_SELF.'?mode=prefs';
   		$var['prefs']['perm'] = '0';

		$var['maint']['text'] = ADLAN_40;
		$var['maint']['link'] = e_SELF.'?mode=maint';
   		$var['maint']['perm'] = '0';
    }
	show_admin_menu(LAN_MAILOUT_15, $action, $var);
}




function headerjs()
{

	$text = "
	<script type='text/javascript'>
	function disp(type) 
	{
		if(type == 'smtp')
		{
			document.getElementById('smtp').style.display = '';
			document.getElementById('sendmail').style.display = 'none';
			return;
		}

		if(type =='sendmail')
		{
            document.getElementById('smtp').style.display = 'none';
			document.getElementById('sendmail').style.display = '';
			return;
		}

		document.getElementById('smtp').style.display = 'none';
		document.getElementById('sendmail').style.display = 'none';
	}
	</script>";

	return $text;
}


?>
