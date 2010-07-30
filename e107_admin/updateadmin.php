<?php
/*
* e107 website system
*
* Copyright (C) 2008-2010 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Admin password changing
*
* $URL$
* $Id$
*
*/
require_once('../class2.php');
$e_sub_cat = 'admin_pass';
require_once('auth.php');

if (isset($_POST['update_settings']) && varset($_POST['e-token']))
{
	if ($_POST['ac'] == md5(ADMINPWCHANGE)) {
		if ($_POST['a_password'] != "" && $_POST['a_password2'] != "" && ($_POST['a_password'] == $_POST['a_password2'])) {
			if (admin_update($sql -> db_Update("user", "user_password='".md5($_POST['a_password'])."', user_pwchange='".time()."' WHERE user_name='".ADMINNAME."'"), 'update', UDALAN_3." ".ADMINNAME)) {
				$e_event -> trigger('adpword');
			}
		} else {
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
	<input type='hidden' name='e-token' value='".e_TOKEN."' />
	<input class='button' type='submit' name='update_settings' value='".UDALAN_7."' />
	<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
	</td>
	</tr>
	</table>

	</form>
	</div>";

	$ns->tablerender(UDALAN_8." ".ADMINNAME, $text);
}

require_once(e_ADMIN.'footer.php');

?>