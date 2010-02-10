<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Update Admin
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/updateadmin.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

require_once('../class2.php');

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'admin_pass';

require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'message_handler.php');
// require_once(e_HANDLER.'user_handler.php'); //use e107::getSession() instead. 
require_once(e_HANDLER.'validator_class.php');
$userMethods = e107::getSession();
$emessage = &eMessage::getInstance();

if (isset($_POST['update_settings'])) 
{
	if ($_POST['ac'] == md5(ADMINPWCHANGE)) 
	{
		$userData = array();
		$userData['data'] = array();
		if ($_POST['a_password'] != '' && $_POST['a_password2'] != '' && ($_POST['a_password'] == $_POST['a_password2'])) 
		{
			$userData['data']['user_password'] = $sql->escape($userMethods->HashPassword($_POST['a_password'], $currentUser['user_loginname']), FALSE);
			unset($_POST['a_password']);
			unset($_POST['a_password2']);
			if (varsettrue($pref['allowEmailLogin']))
			{
				$user_prefs = unserialize($currentUser['user_prefs']);
				$user_prefs['email_password'] = $userMethods->HashPassword($new_pass, $email);
				$userData['data']['user_prefs'] = serialize($user_prefs);
			}

			$userData['data']['user_pwchange'] = time();
			$userData['WHERE'] = 'user_id='.USERID;
			validatorClass::addFieldTypes($userMethods->userVettingInfo,$userData, $userMethods->otherFieldTypes);
	
			$check = $sql -> db_Update('user',$userData);
			if ($check) 
			{
				$admin_log->log_event('ADMINPW_01', '', E_LOG_INFORMATIVE, '');
				$userMethods->makeUserCookie(array('user_id' => USERID,'user_password' => $userData['data']['user_password']), FALSE);		// Can't handle autologin ATM
				$emessage->add(UDALAN_3." ".ADMINNAME, E_MESSAGE_SUCCESS);
				$e_event -> trigger('adpword');
				$ns->tablerender(UDALAN_2, $emessage->render());
			}
			else 
			{
				$emessage->add(UDALAN_1.' '.LAN_UPDATED_FAILED, E_MESSAGE_ERROR);
				$ns->tablerender(LAN_UPDATED_FAILED, $emessage->render());
			}
		}
		else 
		{
			$emessage->add(UDALAN_1.' '.LAN_UPDATED_FAILED, E_MESSAGE_ERROR);
			$ns->tablerender(LAN_UPDATED_FAILED, $emessage->render());
		}
	}
} 
else 
{
	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-updateadmin'>
			<legend class='e-hideme'>".UDALAN_8." ".ADMINNAME."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".UDALAN_4.":</td>
						<td class='control'>
							".ADMINNAME."
						</td>
					</tr>
					<tr>
						<td class='label'>".UDALAN_5.":</td>
						<td class='control'>
							<input class='tbox input-text' type='password' name='a_password' size='60' value='' maxlength='20' />
						</td>
					</tr>
					<tr>
						<td class='label'>".UDALAN_6.":</td>
						<td class='control'>
							<input class='tbox input-text' type='password' name='a_password2' size='60' value='' maxlength='20' />
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
				<button class='update' type='submit' name='update_settings' value='no-value'><span>".UDALAN_7."</span></button>
			</div>
		</fieldset>
	</form>
	
	";

	$ns->tablerender(UDALAN_8." ".ADMINNAME, $text);
}

require_once('footer.php');

?>