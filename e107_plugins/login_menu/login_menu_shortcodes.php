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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
global $tp;
$login_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN LM_USERNAME_INPUT
global $pref;
return "<input class='tbox login user' type='text' name='username' id='username' size='15' value='' maxlength='".varset($pref['loginname_maxlength'],30)."' />\n";
SC_END

SC_BEGIN LM_PASSWORD_INPUT
global $pref;
$t_password = "<input class='tbox login pass' type='password' name='userpass' id='userpass' size='15' value='' maxlength='30' />\n";
return $t_password;
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
if($parm == "hidden"){
	return "<input type='hidden' name='autologin' value='1' />";
}
if($pref['user_tracking'] != "session")
{
	return "<input type='checkbox' name='autologin' value='1' checked='checked' />".LOGIN_MENU_L6;
}
SC_END

SC_BEGIN LM_SIGNUP_LINK
global $pref;
if ($pref['user_reg'])
{
	if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
	{
		return "<a class='login_menu_link signup' href='".e_SIGNUP."' title=\"".LOGIN_MENU_L3."\">".LOGIN_MENU_L3."</a>";
	}
}
return "";
SC_END

SC_BEGIN LM_FPW_LINK
global $pref;
if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
{
	return "<a class='login_menu_link fpw' href='".e_BASE."fpw.php' title=\"".LOGIN_MENU_L4."\">".LOGIN_MENU_L4."</a>";
}
return "";
SC_END

SC_BEGIN LM_RESEND_LINK
global $pref;
if ($pref['user_reg'])
{
	if(isset($pref['user_reg_veri']) && $pref['user_reg_veri'] == 1){
		if (!$pref['auth_method'] || $pref['auth_method'] == 'e107' )
		{
			return "<a class='login_menu_link resend' href='".e_SIGNUP."?resend' title=\"".LOGIN_MENU_L40."\">".LOGIN_MENU_L40."</a>";
		}
	}
}
return "";
SC_END

SC_BEGIN LM_MAINTENANCE
global $pref;
if(ADMIN && varset($pref['maintainance_flag']))
{
	return '<div style="text-align:center"><strong>'.LOGIN_MENU_L10.'</strong></div><br />';
}
return '';
SC_END

SC_BEGIN LM_ADMINLINK_BULLET
$bullet = '';
if(ADMIN)
{
	if(defined('BULLET'))
	{
		$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
	}
}
return $bullet;
SC_END

SC_BEGIN LM_ADMINLINK
global $ADMIN_DIRECTORY, $eplug_admin;

//die(e_PAGE);

if(ADMIN == TRUE) {
		if (strpos(e_SELF, $ADMIN_DIRECTORY) !== FALSE || $eplug_admin == true || substr(e_PAGE, 0, 6) == 'admin_')
		{
			return '<a class="login_menu_link" href="'.e_BASE.'index.php">'.LOGIN_MENU_L39.'</a>';
		}
		else
		{
			return '<a class="login_menu_link" href="'.e_ADMIN_ABS.'admin.php">'.LOGIN_MENU_L11.'</a>';
		}
}
SC_END



SC_BEGIN LM_BULLET
$bullet = '';
if(defined('BULLET'))
{
	$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
}
elseif(file_exists(THEME.'images/bullet2.gif'))
{
	$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
}
return $bullet;
SC_END

SC_BEGIN LM_USERSETTINGS
$text = ($parm) ? $parm : LOGIN_MENU_L12;
return '<a class="login_menu_link" href="'.e_HTTP.'usersettings.php">'.$text.'</a>';
SC_END

SC_BEGIN LM_PROFILE
$text = ($parm) ? $parm : LOGIN_MENU_L13;
return '<a class="login_menu_link" href="'.e_HTTP.'user.php?id.'.USERID.'">'.$text.'</a>';
SC_END

SC_BEGIN LM_LOGOUT
$text = ($parm) ? $parm : LOGIN_MENU_L8;
return '<a class="login_menu_link" href="'.e_HTTP.'index.php?logout">'.$text.'</a>';
SC_END

SC_BEGIN LM_MESSAGE
global $tp;
if($parm == "popup"){
	$srch = array("<br />","'");
	$rep = array("\\n","\'");
	return "<script type='text/javascript'>
		alert('".$tp->toJS(LOGINMESSAGE)."');
		</script>";
}else{
	return LOGINMESSAGE;
}
SC_END

*/
?>
