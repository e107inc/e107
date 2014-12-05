<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_shortcodes.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

/**
 *	e107 Login menu plugin
 *
 *	Shortcodes for login menu
 *
 *	@package	e107_plugins
 *	@subpackage	login
 *	@version 	$Id$;
 *
 *	@todo Convert to new format
 */

if (!defined('e107_INIT')) { exit(); }
global $tp;


$login_menu_shortcodes = e107::getScBatch('login_menu',TRUE);

//$login_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

class login_menu_shortcodes extends e_shortcode
{
	
	private $use_imagecode =0;
	private $sec;
	private $usernameLabel = LOGIN_MENU_L1;
	
	function __construct()
	{
		$pref = e107::getPref();
		
		$this->use_imagecode = e107::getConfig()->get('logcode');	
		$this->sec = e107::getSecureImg();
		$this->usernameLabel = '';
		
		if($pref['allowEmailLogin']==1)
		{
			$this->usernameLabel = LOGIN_MENU_L49;
		}
	
		if($pref['allowEmailLogin']==2)
		{
			$this->usernameLabel = LOGIN_MENU_L50;
		}
		
	}
	
	function sc_lm_username_input($parm='')
	{
		$pref = e107::getPref();
		return "<input class='form-control tbox login user' type='text' name='username' placeholder='".$this->usernameLabel."' required='required' id='username' size='15' value='' maxlength='".varset($pref['loginname_maxlength'],30)."' />\n";
	}
	
	
	function sc_lm_username_label($parm='')
	{
		return $this->usernameLabel;	
	}
	
	
	function sc_lm_password_input($parm='')
	{
		$pref = e107::getPref();
		$t_password = "<input class='form-control tbox login pass' type='password' placeholder='Password' required='required' name='userpass' id='userpass' size='15' value='' maxlength='30' />\n";
		if (!USER && e107::getSession()->is('challenge') && varset($pref['password_CHAP'],0)) $t_password .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='".e107::getSession()->get('challenge')."' />\n\n";
		return $t_password;
	}
	
	
	function sc_lm_password_label($parm='')
	{
		return LOGIN_MENU_L2;
	}
	
	
	function sc_lm_imagecode($parm='')
	{
		//DEPRECATED - use LM_IMAGECODE_NUMBER, LM_IMAGECODE_BOX instead
		if($this->use_imagecode) 
		{
		    return '<input type="hidden" name="rand_num" id="rand_num" value="'.$this->sec->random_number.'" />
		    		'.$this->sec->r_image().'
		    		<br /><input class="tbox login verify" type="text" name="code_verify" id="code_verify" size="15" maxlength="20" /><br />'; 
		}
		return '';
	}
	
	
	function sc_lm_imagecode_number($parm='')
	{
		if($this->use_imagecode) 
		{
		    return '<input type="hidden" name="rand_num" id="rand_num" value="'.$this->sec->random_number.'" />
		        '.$this->sec->r_image(); 
		}
		return '';
	}
	
	function sc_lm_imagecode_box($parm='')
	{
		$placeholder = LAN_ENTER_CODE;
		
		if($this->use_imagecode) 
		{
		    return '<input class="form-control tbox login verify" type="text" name="code_verify" id="code_verify" size="15" maxlength="20" placeholder="'.$placeholder.'" />'; 
		}
		return '';
	}
	
	function sc_lm_loginbutton($parm='')
	{
		return "<input class='button btn login' type='submit' name='userlogin' id='userlogin' value='".LOGIN_MENU_L28."' />";
	}
	
	function sc_lm_rememberme($parm='')
	{
		$pref = e107::getPref();
		if($parm == "hidden"){
			return "<input type='hidden' name='autologin' id='autologin' value='1' />";
		}
		if($pref['user_tracking'] != "session")
		{
			return "<input type='checkbox' name='autologin' id='autologin' value='1' checked='checked' />".($parm ? $parm : "<label for='autologin'>".LOGIN_MENU_L6."</label>");
		}
		return '';
	}
	
	function sc_lm_signup_link($parm='')
	{
		$pref = e107::getPref();
		if ($pref['user_reg'])
		{
			if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
			{
				return $parm == 'href' ? e_SIGNUP : "<a class='login_menu_link signup' id='login_menu_link_signup' href='".e_SIGNUP."' title=\"".LOGIN_MENU_L3."\">".LOGIN_MENU_L3."</a>";
			}
		}
		return '';
	}
	
	function sc_lm_fpw_link($parm='')
	{
		$pref = e107::getPref();
		if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
		{
			return $parm == 'href' ? SITEURL.'fpw.php' : "<a class='login_menu_link fpw' id='login_menu_link_fpw' href='".SITEURL."fpw.php' title=\"".LOGIN_MENU_L4."\">".LOGIN_MENU_L4."</a>";
		}
		return '';
	}
	
	function sc_lm_resend_link($parm='')
	{
		$pref = e107::getPref();
		
		if ($pref['user_reg'])
		{
			if(isset($pref['user_reg_veri']) && $pref['user_reg_veri'] == 1)
			{
				if (!$pref['auth_method'] || $pref['auth_method'] == 'e107' )
				{
					return $parm == 'href' ? e_SIGNUP.'?resend' : "<a class='login_menu_link resend' id='login_menu_link_resend' href='".e_SIGNUP."?resend' title=\"".LOGIN_MENU_L40."\">".LOGIN_MENU_L40."</a>";
				}
			}
		}
		return '';
	}
	
	function sc_lm_maintenance($parm='')
	{
		$pref = e107::getPref();
		
		if(ADMIN && varset($pref['maintainance_flag']))
		{
			return LOGIN_MENU_L10;
		}
		return '';
	}
	
	function sc_lm_adminlink_bullet($parm='')
	{
		if(ADMIN)
		{
			$data = getcachedvars('login_menu_data');
			return $parm == 'src' ? $data['link_bullet_src'] : $data['link_bullet'];
		}
		return '';
	}
	
	function sc_lm_adminlink($parm='')
	{
		if(ADMIN == TRUE) {
			return $parm == 'href' ? e_ADMIN_ABS.'admin.php' : '<a class="login_menu_link admin" id="login_menu_link_admin" href="'.e_ADMIN_ABS.'admin.php">'.LOGIN_MENU_L11.'</a>';
		}
		return '';
	}
	
	function sc_lm_admin_configure($parm='')
	{
	if(ADMIN == TRUE) {
		return $parm == 'href' ? e_PLUGIN_ABS.'login_menu/config.php' : '<a class="login_menu_link config" id="login_menu_link_config" href="'.e_PLUGIN_ABS.'login_menu/config.php">'.LOGIN_MENU_L48.'</a>';
	}
	return '';
	}
	
	function sc_lm_bullet($parm='')
	{
	$data = getcachedvars('login_menu_data'); 
	return $parm == 'src' ? $data['link_bullet_src'] : $data['link_bullet'];
	}
	
	function sc_lm_usersettings($parm='')
	{
		$text = ($parm) ? $parm : LOGIN_MENU_L12;
		$url = $this->sc_lm_usersettings_href();
		return '<a class="login_menu_link usersettings" id="login_menu_link_usersettings" href="'.$url.'">'.$text.'</a>';
	}
	
	function sc_lm_usersettings_href($parm='')
	{
		return e107::getUrl()->create('user/myprofile/edit',array('id'=>USERID));
	// return e_HTTP.'usersettings.php';
	}
	
	function sc_lm_profile($parm='')
	{
		$text = ($parm) ? $parm : LOGIN_MENU_L13;
		$url = $this->sc_lm_profile_href();
		return '<a class="login_menu_link profile" id="login_menu_link_profile" href="'.$url.'">'.$text.'</a>';
	}
	
	function sc_lm_profile_href($parm='')
	{
		return e107::getUrl()->create('user/profile/view',array('user_id'=>USERID, 'user_name'=>USERNAME));
		// return e_HTTP.'user.php?id.'.USERID;
	}
	
	function sc_lm_logout($parm='')
	{
	$text = ($parm) ? $parm : LOGIN_MENU_L8;
	return '<a class="login_menu_link logout" id="login_menu_link_logout" href="'.e_HTTP.'index.php?logout">'.$text.'</a>';
	}
	
	function sc_lm_logout_href($parm='')
	{
	return e_HTTP.'index.php?logout';
	}
	
	function sc_lm_external_links($parm='')
	{
		global $tp, $menu_pref, $login_menu_shortcodes, $LOGIN_MENU_EXTERNAL_LINK;
		if(!varsettrue($menu_pref['login_menu']['external_links'])) return '';
		$lbox_infos = login_menu_class::parse_external_list(true, false); 
		$lbox_active = $menu_pref['login_menu']['external_links'] ? explode(',', $menu_pref['login_menu']['external_links']) : array();
		if(!varsettrue($lbox_infos['links'])) return '';
		$ret = '';
		foreach ($lbox_active as $stackid) {
		    $lbox_items = login_menu_class::clean_links(varset($lbox_infos['links'][$stackid]));
		    if(!$lbox_items) continue;
		    foreach ($lbox_items as $num=>$lbox_item) {
		        $lbox_item['link_id'] = $stackid.'_'.$num;
		    	cachevars('login_menu_linkdata', $lbox_item);
		    	$ret .= $tp -> parseTemplate($LOGIN_MENU_EXTERNAL_LINK, false, $login_menu_shortcodes);
		    }
		}
		return $ret;
	}
	
	function sc_lm_external_link($parm='')
	{
		$lbox_item = getcachedvars('login_menu_linkdata');
		return $parm == 'href' ? $lbox_item['link_url'] : '<a href="'.$lbox_item['link_url'].'" class="login_menu_link external" id="login_menu_link_external_'.$lbox_item['link_id'].'">'.varsettrue($lbox_item['link_label'], '['.LOGIN_MENU_L44.']').'</a>';
	}
	
	function sc_lm_external_link_label($parm='')
	{
		$lbox_item = getcachedvars('login_menu_linkdata');
		return varsettrue($lbox_item['link_label'], '['.LOGIN_MENU_L44.']');
	}
	
	function sc_lm_stats($parm='')
	{
		global $LOGIN_MENU_STATS, $tp, $login_menu_shortcodes;
		$data = getcachedvars('login_menu_data');
		if(!$data['enable_stats']) return '';
		return $tp -> parseTemplate($LOGIN_MENU_STATS, true, $login_menu_shortcodes);
	}
	
	function sc_lm_new_news($parm='')
	{
		global $LOGIN_MENU_STATITEM, $tp;
		$data = getcachedvars('login_menu_data'); 
		if(!isset($data['new_news'])) return ''; 
		$tmp = array();
		if($data['new_news']){
			$tmp['LM_STAT_NEW']   = $data['new_news'];
			$tmp['LM_STAT_LABEL'] = $data['new_news'] == 1 ? LOGIN_MENU_L14 : LOGIN_MENU_L15;
			$tmp['LM_STAT_EMPTY'] = '';
		} else {
			$tmp['LM_STAT_NEW'] = '';
			$tmp['LM_STAT_LABEL'] = '';
			$tmp['LM_STAT_EMPTY'] = LOGIN_MENU_L26." ".LOGIN_MENU_L15;
		}
		return $tp -> parseTemplate($LOGIN_MENU_STATITEM, false, $tmp);
	}
	
	function sc_lm_new_comments($parm='')
	{
		global $LOGIN_MENU_STATITEM, $tp;
		$data = getcachedvars('login_menu_data');
		if(!isset($data['new_comments'])) return '';
		$tmp = array();
		if($data['new_comments']){
			$tmp['LM_STAT_NEW']   = $data['new_comments'];
			$tmp['LM_STAT_LABEL'] = $data['new_comments'] == 1 ? LOGIN_MENU_L18 : LOGIN_MENU_L19;
			$tmp['LM_STAT_EMPTY'] = '';
		} else {
			$tmp['LM_STAT_NEW']   = '';
			$tmp['LM_STAT_LABEL'] = '';
			$tmp['LM_STAT_EMPTY'] = LOGIN_MENU_L26." ".LOGIN_MENU_L19;
		}
		return $tp -> parseTemplate($LOGIN_MENU_STATITEM, false, $tmp);
	}
	
	function sc_lm_new_users($parm='')
	{
		global $LOGIN_MENU_STATITEM, $tp;
		$data = getcachedvars('login_menu_data');
		if(!isset($data['new_users'])) return '';
		$tmp = array();
		if($data['new_users']){
			$tmp['LM_STAT_NEW']   = $data['new_users'];
			$tmp['LM_STAT_LABEL'] = $data['new_users'] == 1 ? LOGIN_MENU_L22 : LOGIN_MENU_L23;
			$tmp['LM_STAT_EMPTY'] = '';
		} else {
			$tmp['LM_STAT_NEW']   = '';
			$tmp['LM_STAT_LABEL'] = '';
			$tmp['LM_STAT_EMPTY'] = LOGIN_MENU_L26." ".LOGIN_MENU_L23;
		}
		return $tp -> parseTemplate($LOGIN_MENU_STATITEM, false, $tmp);
	}
	
	function sc_lm_plugin_stats($parm='')
	{
		global $tp, $menu_pref, $new_total, $LOGIN_MENU_STATITEM, $LM_STATITEM_SEPARATOR;
		if(!varsettrue($menu_pref['login_menu']['external_stats'])) return ''; 
		$lbox_infos = login_menu_class::parse_external_list(true, false);
		if(!varsettrue($lbox_infos['stats'])) return '';
		$lbox_active_sorted = $menu_pref['login_menu']['external_stats'] ? explode(',', $menu_pref['login_menu']['external_stats']) : array();
		$ret = array(); 
		$sep = varset($LM_STATITEM_SEPARATOR, '<br />');
		foreach ($lbox_active_sorted as $stackid) { 
		    if(!varset($lbox_infos['stats'][$stackid])) continue;
		    foreach ($lbox_infos['stats'][$stackid] as $lbox_item) {
		    	$tmp = array();
		    	if($lbox_item['stat_new']){ 
		        	$tmp['LM_STAT_NEW'] = "return '{$lbox_item['stat_new']}';";
		        	$tmp['LM_STAT_LABEL'] = $lbox_item["stat_new"] == 1 ? "return '{$lbox_item['stat_item']}';" : "return '{$lbox_item['stat_items']}';";
		        	$tmp['LM_STAT_EMPTY'] = '';
		        	$new_total += $lbox_item['stat_new'];
		    	} else {
		    	    //if(empty($lbox_item['stat_nonew'])) continue;
		        	$tmp['LM_STAT_NEW'] = '';
		        	$tmp['LM_STAT_LABEL'] = '';
		        	$tmp['LM_STAT_EMPTY'] = "return '{$lbox_item['stat_nonew']}';";
		        }
		    	$ret[] = $tp -> parseTemplate($LOGIN_MENU_STATITEM, false, $tmp);
		    }
		}
		return $ret ? implode($sep, $ret) : '';
	}
	
	
	function sc_lm_listnew_link($parm='')
	{
		$data = getcachedvars('login_menu_data');
		if($parm == 'href') return $data['listnew_link'];
		return $data['listnew_link'] ? '<a href="'.$data['listnew_link'].'" class="login_menu_link listnew" id="login_menu_link_listnew">'.LOGIN_MENU_L24.'</a>' : '';
	}
	
	
	function sc_lm_message($parm='')
	{
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
	}
	
	
	function sc_lm_message_text($parm='')
	{
		return defsettrue('LOGINMESSAGE', '');
	}




}
?>
