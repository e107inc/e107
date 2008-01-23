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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_shortcodes.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-01-23 01:12:15 $
|     $Author: secretr $
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
if(ADMIN == TRUE){
	return ($pref['maintainance_flag'] == 1 ? '<div style="text-align:center"><strong>'.LOGIN_MENU_L10.'</strong></div><br />' : '' );
}
SC_END

SC_BEGIN LM_ADMINLINK_BULLET
$data = getcachedvars('login_menu_data'); 
if(ADMIN==TRUE && $data['link_bullet'] != 'bullet'){
	return $data['link_bullet'];
}
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
$data = getcachedvars('login_menu_data'); 
return $data['link_bullet'];
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

SC_BEGIN LM_EXTERNAL_LINKS
global $tp, $menu_pref, $login_menu_shortcodes, $LOGIN_MENU_EXTERNAL_LINK;
require_once(e_PLUGIN."login_menu/login_menu_class.php");
if(!varsettrue($menu_pref['login_menu']['external_links'])) return '';
$tmp = explode(',', $menu_pref['login_menu']['external_links']);
$lbox_infos = login_menu_class::parse_external_list($tmp);
if(!varsettrue($lbox_infos['links'])) return '';
$ret = '';
foreach ($lbox_infos['links'] as $id => $items) {
$lbox_items = login_menu_class::clean_links($items);
if(!$lbox_items) continue;
    foreach ($lbox_items as $lbox_item) {
    	cachevars('login_menu_linkdata', $lbox_item);
    	$ret .= $tp -> parseTemplate($LOGIN_MENU_EXTERNAL_LINK, false, $login_menu_shortcodes);
    }
}
return $ret;
SC_END

SC_BEGIN LM_EXTERNAL_LINK
$lbox_item = getcachedvars('login_menu_linkdata');
return $parm == 'href' ? $lbox_item['link_url'] : '<a href="'.$lbox_item['link_url'].'">'.varsettrue($lbox_item['link_label'], '['.LOGIN_MENU_L44.']').'</a>';
SC_END

SC_BEGIN LM_EXTERNAL_LINK_LABEL
$lbox_item = getcachedvars('login_menu_linkdata');
return varsettrue($lbox_item['link_label'], '['.LOGIN_MENU_L44.']');
SC_END

SC_BEGIN LM_STATS
global $LOGIN_MENU_STATS, $tp, $login_menu_shortcodes;
$data = getcachedvars('login_menu_data');
if(!$data['enable_stats']) return '';
return $tp -> parseTemplate($LOGIN_MENU_STATS, true, $login_menu_shortcodes);
SC_END

SC_BEGIN LM_NEW_NEWS
$data = getcachedvars('login_menu_data'); 
if(!isset($data['new_news'])) return ''; 
if(!$data['new_news'])
    return LOGIN_MENU_L26.' '.LOGIN_MENU_L15;
return $data['new_news'].' '.($data['new_news'] == 1 ? LOGIN_MENU_L14 : LOGIN_MENU_L15);
SC_END

SC_BEGIN LM_NEW_COMMENTS
$data = getcachedvars('login_menu_data');
if(!isset($data['new_comments'])) return '';
if(!$data['new_comments'])
    return LOGIN_MENU_L26.' '.LOGIN_MENU_L19;
return $data['new_comments'].' '.($data['new_comments'] == 1 ? LOGIN_MENU_L18 : LOGIN_MENU_L19);
SC_END

SC_BEGIN LM_NEW_CHAT
$data = getcachedvars('login_menu_data');
if(!isset($data['new_chat'])) return '';
if(!$data['new_chat'])
    return LOGIN_MENU_L26.' '.LOGIN_MENU_L17;
return $data['new_chat'].' '.($data['new_chat'] == 1 ? LOGIN_MENU_L16 : LOGIN_MENU_L17);
SC_END

SC_BEGIN LM_NEW_FORUM
$data = getcachedvars('login_menu_data');
if(!isset($data['new_forum'])) return '';
if(!$data['new_forum'])
    return LOGIN_MENU_L26.' '.LOGIN_MENU_L21;
return $data['new_forum'].' '.($data['new_forum'] == 1 ? LOGIN_MENU_L20 : LOGIN_MENU_L17);
SC_END

SC_BEGIN LM_NEW_USERS
$data = getcachedvars('login_menu_data');
if(!isset($data['new_users'])) return '';
if(!$data['new_users'])
    return LOGIN_MENU_L26.' '.LOGIN_MENU_L23;
return $data['new_users'].' '.($data['new_users'] == 1 ? LOGIN_MENU_L22 : LOGIN_MENU_L23);
SC_END

SC_BEGIN LM_LISTNEW_LINK
$data = getcachedvars('login_menu_data');
if($parm == 'href') return $data['listnew_link'];
return $data['listnew_link'] ? '<a href="'.$data['listnew_link'].'">'.LOGIN_MENU_L24.'</a>' : '';
SC_END

SC_BEGIN LM_MESSAGE
global $tp, $LOGIN_MENU_MESSAGE;
if(!defsettrue('LOGINMESSAGE')) return '';
if($parm == "popup"){
	$srch = array("<br />","'");
	$rep = array("\\n","\'");
	return "<script type='text/javascript'>
		alert('".$tp->toJS(LOGINMESSAGE)."');
		</script>";
}else{
    return $tp->parseTemplate($LOGIN_MENU_MESSAGE, true, $login_menu_shortcodes);
}
SC_END

SC_BEGIN LM_MESSAGE_TEXT
return defsettrue('LOGINMESSAGE', '');
SC_END
*/
?>