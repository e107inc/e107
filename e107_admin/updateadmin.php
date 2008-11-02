<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/updateadmin.php,v $
|     $Revision: 1.2 $
|     $Date: 2008-11-02 14:03:04 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
$e_sub_cat = 'admin_pass';
require_once('auth.php');
require_once(e_HANDLER."user_handler.php");
$user_info = new UserHandler;


if (isset($_POST['update_settings'])) 
{
	if ($_POST['ac'] == md5(ADMINPWCHANGE)) 
	{
		if ($_POST['a_password'] != "" && $_POST['a_password2'] != "" && ($_POST['a_password'] == $_POST['a_password2'])) 
		{
			$newPassword = $sql->escape($user_info->HashPassword($_POST['a_password'], $currentUser['user_loginname']), FALSE);
			$newPrefs = '';
			unset($_POST['a_password']);
			unset($_POST['a_password2']);
			if (varsettrue($pref['allowEmailLogin']))
			{
				$user_prefs = unserialize($currentUser['user_prefs']);
				$user_prefs['email_password'] = $user_info->HashPassword($new_pass, $email);
				$newPrefs = "user_prefs='".serialize($user_prefs)."', ";
			}
			if (admin_update($sql -> db_Update("user", "user_password='".$newPassword."', ".$newPrefs."user_pwchange='".time()."' WHERE user_id=".USERID), 'update', UDALAN_3." ".ADMINNAME)) 
			{
				$admin_log->log_event('ADMINPW_01','',E_LOG_INFORMATIVE,'');
				$e_event -> trigger('adpword');
			}
		} 
		else 
		{
			$ns->tablerender(LAN_UPDATED_FAILED, "<div style='text-align:center'><b>".UDALAN_1."</b></div>");
		}
	}
} else {
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>\n
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:30%' class='forumheader3'>".UDALAN_4.": </td>
	<td style='width:70%' class='forumheader3'>
	".ADMINNAME."
	</td>
	</tr>
	<tr>
	<td style='width:30%' class='forumheader3'>".UDALAN_5.": </td>
	<td style='width:70%' class='forumheader3'>
	<input class='tbox' type='password' name='a_password' size='60' value='' maxlength='20' />
	</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".UDALAN_6.": </td>
	<td style='width:70%' class='forumheader3'>
	<input class='tbox' type='password' name='a_password2' size='60' value='' maxlength='20' />
	</td>
	</tr>

	<tr>
	<td colspan='2' style ='text-align:center'  class='forumheader'>
	<input class='button' type='submit' name='update_settings' value='".UDALAN_7."' />
	<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
	</td>
	</tr>
	</table>

	</form>
	</div>";

	$ns->tablerender(UDALAN_8." ".ADMINNAME, $text);
}

require_once('footer.php');

?>