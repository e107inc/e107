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
 *
*/

require_once('../class2.php');

// include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
e107::lan('core','updateadmin',true);

$e_sub_cat = 'admin_pass';

require_once(e_ADMIN.'auth.php');
// require_once(e_HANDLER.'user_handler.php'); //use e107::getUserSession() instead. 
require_once(e_HANDLER.'validator_class.php');
$userMethods = e107::getUserSession();
$mes = e107::getMessage();
$frm = e107::getForm();

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

			if (vartrue($pref['allowEmailLogin']))
			{
				$new_pass = e107::getParser()->filter($_POST['a_password']);

				$user_prefs = e107::getArrayStorage()->unserialize($currentUser['user_prefs']);
				$user_prefs['email_password'] = $userMethods->HashPassword($new_pass, USEREMAIL);
				$userData['data']['user_prefs'] = e107::getArrayStorage()->serialize($user_prefs);
			}

			$userData['data']['user_pwchange'] = time();
			$userData['WHERE'] = 'user_id='.USERID;
			validatorClass::addFieldTypes($userMethods->userVettingInfo,$userData, $userMethods->otherFieldTypes);
	
			$check = $sql->update('user',$userData);
			if ($check) 
			{
				e107::getLog()->add('ADMINPW_01', '', E_LOG_INFORMATIVE, '');
				$userMethods->makeUserCookie(array('user_id' => USERID,'user_password' => $userData['data']['user_password']), FALSE);		// Can't handle autologin ATM
				$mes->addSuccess(UDALAN_3." ".ADMINNAME);
				
				e107::getEvent()->trigger('adpword'); //@deprecated
				
				$eventData = array('user_id'=> USERID, 'user_pwchange'=> $userData['data']['user_pwchange']); 
				e107::getEvent()->trigger('admin_password_update',$eventData ); 
				 
				$ns->tablerender(UDALAN_2, $mes->render());
			}
			else 
			{
				$mes->addError(UDALAN_1.' '.LAN_UPDATED_FAILED);
				$ns->tablerender(LAN_UPDATED_FAILED, $mes->render());
			}
		}
		else 
		{
			$mes->addError(UDALAN_1.' '.LAN_UPDATED_FAILED);
			$ns->tablerender(LAN_UPDATED_FAILED, $mes->render());
		}
	}
} 
else 
{
	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-updateadmin'>
			<legend class='e-hideme'>".UDALAN_8." ".ADMINNAME."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".UDALAN_4.":</td>
						<td>
							".ADMINNAME."
						</td>
					</tr>
					<tr>
						<td>".LAN_PASSWORD.":</td>
						<td>".$frm->password('a_password','',20,'generate=1&strength=1')."
							
						</td>
					</tr>
					<tr>
						<td>".UDALAN_6.":</td>
						<td>
							<input class='tbox form-control input-text' type='password' name='a_password2' size='60' value='' maxlength='20' />
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />".
				$frm->admin_button('update_settings','no-value','update',UDALAN_7)."
				
			</div>
		</fieldset>
	</form>
	
	";

	$ns->tablerender(UDALAN_8." ".ADMINNAME, $text);
}

require_once(e_ADMIN.'footer.php');

?>