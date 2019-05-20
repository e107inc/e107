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
+----------------------------------------------------------------------------+
*/


if (!defined('e107_INIT')) { exit(); }
global $tp;


// BC LAN Fix.

$bcDefs = array(
'LOGIN_MENU_L1'     => 'LAN_LOGINMENU_1',
'LOGIN_MENU_L2'     => 'LAN_LOGINMENU_2',
'LOGIN_MENU_L3'     => 'LAN_LOGINMENU_3',
'LOGIN_MENU_L4'     => 'LAN_LOGINMENU_4',
'LOGIN_MENU_L6'     => 'LAN_LOGINMENU_6',
'LOGIN_MENU_L40'    => 'LAN_LOGINMENU_40',
'LOGIN_MENU_L51'    => 'LAN_LOGINMENU_51'
);

e107::getLanguage()->bcDefs($bcDefs);


//$login_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
	if(!class_exists('login_menu_shortcodes'))
	{
		class login_menu_shortcodes extends e_shortcode
		{

			private $use_imagecode =0;
			private $sec;
			private $usernameLabel = LAN_LOGINMENU_1;
			private $allowEmailLogin;

			function __construct()
			{
				$pref = e107::getPref();

				$this->use_imagecode = e107::getConfig()->get('logcode');
				$this->sec = e107::getSecureImg();
				$this->usernameLabel = '';
				$this->allowEmailLogin = $pref['allowEmailLogin'];

				if($pref['allowEmailLogin']==1)
				{
					$this->usernameLabel = LAN_LOGINMENU_49;
				}

				if($pref['allowEmailLogin']==2)
				{
					$this->usernameLabel = LAN_LOGINMENU_50;
				}

			}


			/**
			 *
			 * @param array $parm
			 * @return null|string
			 */
			function sc_lm_active($parm=array())
			{
			//	$request = e_REQUEST_URI;

				$ret = null;

				$mode = varset($parm['mode']);

				if($mode === 'usersettings' && defset('e_PAGE') === 'usersettings.php')
				{
					 return 'active';
				}
				elseif($mode === 'profile' && defset('e_PAGE') === 'user.php')
				{
					return 'active';
				}


				return null;
			}



			function sc_lm_username_input($parm=null)
			{
				$pref = e107::getPref();

				// If logging in with email address - ignore pref and increase to 100 chars.
				$maxLength  = ($this->allowEmailLogin == 1 || $this->allowEmailLogin) ? 100 : varset($pref['loginname_maxlength'],30);

				return "
				<label class='sr-only' for='".vartrue( $parm['idprefix'] )."username'>".$this->usernameLabel."</label>
				<input class='form-control tbox login user' type='text' name='username' placeholder='".$this->usernameLabel."' required='required' id='".vartrue( $parm['idprefix'] )."username' size='15' value='' maxlength='".$maxLength."' />\n";
			}


			function sc_lm_username_label($parm='')
			{
				return $this->usernameLabel;
			}


			function sc_lm_password_input($parm=null)
			{
				$pref = e107::getPref();
				$t_password = "
				<label class='sr-only' for='".vartrue( $parm['idprefix'] )."userpass'>".LAN_PASSWORD."</label>
				<input class='form-control tbox login pass' type='password' placeholder='".LAN_PASSWORD."' required='required' name='userpass' id='".vartrue( $parm['idprefix'] )."userpass' size='15' value='' maxlength='30' />\n";

				if (!USER && e107::getSession()->is('challenge') && varset($pref['password_CHAP'],0))
					 $t_password .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='".e107::getSession()->get('challenge')."' />\n\n";

				return $t_password;
			}


			function sc_lm_password_label($parm='')
			{
				return LAN_LOGINMENU_2;
			}


			/**
			 * @deprecated use {LM_IMAGECODE_NUMBER}, {LM_IMAGECODE_BOX} instead
			 * @param string $parm
			 * @return string
			 */
			function sc_lm_imagecode($parm='')
			{
				//DEPRECATED - use LM_IMAGECODE_NUMBER, LM_IMAGECODE_BOX instead
				if($this->use_imagecode)
				{
					return $this->sc_lm_imagecode_number()."<br />".$this->sc_lm_imagecode_box();
				    /*return '<input type="hidden" name="rand_num" id="rand_num" value="'.$this->sec->random_number.'" />
				            '.$this->sec->r_image().'
				            <br /><input class="tbox login verify" type="text" name="code_verify" id="code_verify" size="15" maxlength="20" /><br />';
					*/
				}
				return '';
			}


			function sc_lm_imagecode_number($parm='')
			{
				if($this->use_imagecode)
				{
					return e107::getSecureImg()->renderImage();
				 /*   return '<input type="hidden" name="rand_num" id="rand_num" value="'.$this->sec->random_number.'" />
				        '.$this->sec->r_image();*/
				}

				return '';
			}

			function sc_lm_imagecode_box($parm='')
			{
				if($this->use_imagecode)
				{
					return e107::getSecureImg()->renderInput();
					// $placeholder = LAN_ENTER_CODE;
				  //  return '<input class="form-control tbox login verify" type="text" name="code_verify" id="code_verify" size="15" maxlength="20" placeholder="'.$placeholder.'" />';
				}

				return '';
			}

			function sc_lm_loginbutton($parm='')
			{
				return "<input class='button btn btn-default btn-secondary login' type='submit' name='userlogin' id='userlogin' value='".LAN_LOGIN."' />";
			}

			function sc_lm_rememberme($parm='')
			{
				$pref = e107::getPref();
				if($parm == "hidden"){
					return "<input type='hidden' name='autologin' id='autologin' value='1' />";
				}
				if($pref['user_tracking'] != "session")
				{
					return "<label for='autologin'><input type='checkbox' name='autologin' id='autologin' value='1' checked='checked' />".($parm ? $parm : "".LAN_LOGINMENU_6."</label>");
				}
				return '';
			}

			function sc_lm_signup_link($parm='')
			{
				$pref = e107::getPref();
				if (intval($pref['user_reg'])===1)
				{
					if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
					{
						return $parm == 'href' ? e_SIGNUP : "<a class='login_menu_link signup' id='login_menu_link_signup' href='".e_SIGNUP."' title=\"".LAN_LOGINMENU_3."\">".LAN_LOGINMENU_3."</a>";
					}
				}
				return '';
			}

			function sc_lm_fpw_link($parm='')
			{
				$pref = e107::getPref();
				if (!$pref['auth_method'] || $pref['auth_method'] == 'e107')
				{
					return $parm == 'href' ? SITEURL.'fpw.php' : "<a class='login_menu_link fpw' id='login_menu_link_fpw' href='".SITEURL."fpw.php' title=\"".LAN_LOGINMENU_4."\">".LAN_LOGINMENU_4."</a>";
				}
				return '';
			}

			function sc_lm_resend_link($parm='')
			{
				$pref = e107::getPref();

				if (intval($pref['user_reg'])===1)
				{
					if(isset($pref['user_reg_veri']) && $pref['user_reg_veri'] == 1)
					{
						if (!$pref['auth_method'] || $pref['auth_method'] == 'e107' )
						{
							return $parm == 'href' ? e_SIGNUP.'?resend' : "<a class='login_menu_link resend' id='login_menu_link_resend' href='".e_SIGNUP."?resend' title=\"".LAN_LOGINMENU_40."\">".LAN_LOGINMENU_40."</a>";
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
					return LAN_LOGINMENU_10;
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
					return $parm == 'href' ? e_ADMIN_ABS.'admin.php' : '<a class="login_menu_link admin" id="login_menu_link_admin" href="'.e_ADMIN_ABS.'admin.php">'.LAN_LOGINMENU_11.'</a>';
				}
				return '';
			}

			function sc_lm_admin_configure($parm='')
			{
			if(ADMIN == TRUE) {
				return $parm == 'href' ? e_PLUGIN_ABS.'login_menu/config.php' : '<a class="login_menu_link config" id="login_menu_link_config" href="'.e_PLUGIN_ABS.'login_menu/config.php">'.LAN_LOGINMENU_48.'</a>';
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
				$text = ($parm) ? $parm : LAN_SETTINGS;
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
				$text = ($parm) ? $parm : LAN_LOGINMENU_13;
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
			$text = ($parm) ? $parm : LAN_LOGOUT;
			return '<a class="login_menu_link logout" id="login_menu_link_logout" href="'.e_HTTP.'index.php?logout">'.$text.'</a>';
			}

			function sc_lm_logout_href($parm='')
			{
			return e_HTTP.'index.php?logout';
			}

			function sc_lm_external_links($parm='')
			{
				global $menu_pref, $login_menu_shortcodes, $LOGIN_MENU_EXTERNAL_LINK;

				$tp = e107::getParser();

				if(!vartrue($menu_pref['login_menu']['external_links'])) return '';
				$lbox_infos = login_menu_class::parse_external_list(true, false);
				$lbox_active = $menu_pref['login_menu']['external_links'] ? explode(',', $menu_pref['login_menu']['external_links']) : array();
				if(!vartrue($lbox_infos['links'])) return '';
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
				return $parm == 'href' ? $lbox_item['link_url'] : '<a href="'.$lbox_item['link_url'].'" class="login_menu_link external" id="login_menu_link_external_'.$lbox_item['link_id'].'">'.vartrue($lbox_item['link_label'], '['.LAN_LOGINMENU_44.']').'</a>';
			}

			function sc_lm_external_link_label($parm='')
			{
				$lbox_item = getcachedvars('login_menu_linkdata');
				return vartrue($lbox_item['link_label'], '['.LAN_LOGINMENU_44.']');
			}

			function sc_lm_stats($parm='')
			{
				$tp = e107::getParser();
				global $LOGIN_MENU_STATS;
				$data = getcachedvars('login_menu_data');
				if(!$data['enable_stats']) return '';
				return $tp -> parseTemplate($LOGIN_MENU_STATS, true, $this);
			}

			function sc_lm_new_news($parm='')
			{
				$tp = e107::getParser();
				global $LOGIN_MENU_STATITEM;
				$data = getcachedvars('login_menu_data');
				if(!isset($data['new_news'])) return '';
				$tmp = array();
				if($data['new_news']){
					$tmp['LM_STAT_NEW']   = $data['new_news'];
					$tmp['LM_STAT_LABEL'] = $data['new_news'] == 1 ? LAN_LOGINMENU_14 : LAN_LOGINMENU_15;
					$tmp['LM_STAT_EMPTY'] = '';
				} else {
					$tmp['LM_STAT_NEW'] = '';
					$tmp['LM_STAT_LABEL'] = '';
					$tmp['LM_STAT_EMPTY'] = LAN_LOGINMENU_26." ".LAN_LOGINMENU_15;
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
					$tmp['LM_STAT_LABEL'] = $data['new_comments'] == 1 ? LAN_LOGINMENU_18 : LAN_LOGINMENU_19;
					$tmp['LM_STAT_EMPTY'] = '';
				} else {
					$tmp['LM_STAT_NEW']   = '';
					$tmp['LM_STAT_LABEL'] = '';
					$tmp['LM_STAT_EMPTY'] = LAN_LOGINMENU_26." ".LAN_LOGINMENU_19;
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
					$tmp['LM_STAT_LABEL'] = $data['new_users'] == 1 ? LAN_LOGINMENU_22 : LAN_LOGINMENU_23;
					$tmp['LM_STAT_EMPTY'] = '';
				} else {
					$tmp['LM_STAT_NEW']   = '';
					$tmp['LM_STAT_LABEL'] = '';
					$tmp['LM_STAT_EMPTY'] = LAN_LOGINMENU_26." ".LAN_LOGINMENU_23;
				}
				return $tp -> parseTemplate($LOGIN_MENU_STATITEM, false, $tmp);
			}

			function sc_lm_plugin_stats($parm='')
			{
				global $tp, $menu_pref, $new_total, $LOGIN_MENU_STATITEM, $LM_STATITEM_SEPARATOR;

				if(!vartrue($menu_pref['login_menu']['external_stats'])) return '';

				$lbox_infos = login_menu_class::parse_external_list(true, false);

				if(!vartrue($lbox_infos['stats'])) return '';

				$lbox_active_sorted = $menu_pref['login_menu']['external_stats'] ? explode(',', $menu_pref['login_menu']['external_stats']) : array();

				$ret = array();

				$sep = varset($LM_STATITEM_SEPARATOR, '<br />');

				foreach ($lbox_active_sorted as $stackid)
				{
				    if(!varset($lbox_infos['stats'][$stackid])) continue;

				    foreach ($lbox_infos['stats'][$stackid] as $lbox_item)
				    {
				        $tmp = array();
				        if($lbox_item['stat_new'])
				        {
				            $tmp['LM_STAT_NEW'] = $lbox_item['stat_new'];
				            $tmp['LM_STAT_LABEL'] = $lbox_item["stat_new"] == 1 ? $lbox_item['stat_item'] : $lbox_item['stat_items'];
				            $tmp['LM_STAT_EMPTY'] = '';
				            $new_total += $lbox_item['stat_new'];
				        }
				        else
				        {
				            //if(empty($lbox_item['stat_nonew'])) continue;
				            $tmp['LM_STAT_NEW'] = '';
				            $tmp['LM_STAT_LABEL'] = '';
				            $tmp['LM_STAT_EMPTY'] = $lbox_item['stat_nonew'];
				        }

				        $ret[] = $tp->parseTemplate($LOGIN_MENU_STATITEM, false, $tmp);
				    }
				}

				return $ret ? implode($sep, $ret) : '';

			}


			function sc_lm_listnew_link($parm='')
			{
				$data = getcachedvars('login_menu_data');
				if($parm == 'href') return $data['listnew_link'];
				return $data['listnew_link'] ? '<a href="'.$data['listnew_link'].'" class="login_menu_link listnew" id="login_menu_link_listnew">'.LAN_LOGINMENU_24.'</a>' : '';
			}


			function sc_lm_message($parm='')
			{
				global $tp, $LOGIN_MENU_MESSAGE;
				if(!deftrue('LOGINMESSAGE')) return '';
				if($parm == "popup"){
					$srch = array("<br />","'");
					$rep = array("\\n","\'");
					return "<script type='text/javascript'>
						alert('".$tp->toJS(LOGINMESSAGE)."');
						</script>";
				}
				else
				{
				    return e107::getParser()->parseTemplate($LOGIN_MENU_MESSAGE, true, $this);
				}
			}


			function sc_lm_message_text($parm='')
			{
				return deftrue('LOGINMESSAGE', '');
			}


		}
	}

	$login_menu_shortcodes = e107::getScBatch('login_menu',TRUE);

