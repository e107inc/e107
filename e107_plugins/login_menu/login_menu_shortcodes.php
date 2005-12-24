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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/login_menu/login_menu_shortcodes.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-12-24 14:20:30 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
global $tp;
$login_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN LM_USERNAME_INPUT
return "<input class='tbox login user' type='text' name='username' size='15' value='' maxlength='30' />\n";
SC_END

SC_BEGIN LM_PASSWORD_INPUT
return "<input class='tbox login pass' type='password' name='userpass' size='15' value='' maxlength='20' />\n\n";
SC_END

SC_BEGIN LM_IMAGECODE
global $use_imagecode, $sec_img;
if($use_imagecode)
{
	return '<input type="hidden" name="rand_num" value="'.$sec_img->random_number.'" />
		'.$sec_img->r_image().'
		<br /><input class="tbox login verify" type="text" name="code_verify" size="15" maxlength="20" /><br />';
}
SC_END

SC_BEGIN LM_LOGINBUTTON
return "<input class='button' type='submit' name='userlogin' value='".LOGIN_MENU_L28."' />";
SC_END

SC_BEGIN LM_REMEMBERME
global $pref;
if($pref['user_tracking'] != "session")
{
	return "<br /><input type='checkbox' name='autologin' value='1' />".LOGIN_MENU_L6;
}
SC_END

SC_BEGIN LM_SIGNUP_LINK
global $pref;
if ($pref['user_reg']) {
	if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
	{
		return "<a href='".e_SIGNUP."'>".LOGIN_MENU_L3."</a>";
	}
}
SC_END

SC_BEGIN LM_FPW_LINK
return "<a href='".e_BASE."fpw.php'>".LOGIN_MENU_L4."</a>";
SC_END

*/
?>