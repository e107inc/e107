<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

e107::coreLan('signup');

class signup_shortcodes extends e_shortcode
{
	public $template = array();

	function __construct()
	{
		parent::__construct();
		e107::coreLan('user');
		$this->template = e107::getCoreTemplate('signup');
	}

	function sc_signup_coppa_text($parm=null)
	{
		$text = LAN_SIGNUP_77. " <a target='_blank' href='https://www.ftc.gov/business-guidance/resources/complying-coppa-frequently-asked-questions'>". LAN_SIGNUP_14."</a>. ".
		LAN_SIGNUP_15 . " " . e107::getParser()->emailObfuscate(SITEADMINEMAIL, LAN_SIGNUP_14) . " " . LAN_SIGNUP_16;

		return $text;
	}
	
	function sc_signup_coppa_form($parm=null)
	{
		if (strpos(LAN_SIGNUP_77, "stage") !== FALSE)
		{
			return "";
		}
		else
		{
			$class = (!empty($parm['class'])) ? $parm['class'] : 'btn btn-primary button';
			return "
			<form method='post' action='".e_SELF."?stage1' autocomplete='off'>
			<div>
			<br />
			".e107::getForm()->radio_switch("coppa", false, LAN_YES, LAN_NO, array('reverse' => 1))."
			<br />
			<br />
			<input class='".$class."' type='submit' name='newver' value=\"".LAN_CONTINUE."\" />
			</div></form>
		";
		}

		/*
			<input type='radio' name='coppa' value='0' id='coppa_no' checked='checked' /> <label class='control-label' for='coppa_no'>".LAN_NO."</label>
			<input type='radio' name='coppa' value='1' id='coppa_yes' /> <label class='control-label' for='coppa_yes'>".LAN_YES."</label>
		*/
	}


	function sc_signup_xup($param=null) // show it to those who were using xup
	{
		switch ($param) 
		{
			case 'login':
				return $this->sc_signup_xup_login($param);
			break;
			
			case 'signup':
			default:
				return $this->sc_signup_xup_signup($param);
			break;
		}
	}
	
	// TODO - template
	function sc_signup_xup_login($parm=null)
	{
		if (!e107::getUserProvider()->isSocialLoginEnabled()) return '';

		$size = empty($parm['size']) ? '3x' : $parm['size'];
		$class = empty($parm['class']) ?  'btn btn-primary' : $parm['class'] ;

		return $this->generateXupLoginButtons("login", $size, $class);
	}
	
	// TODO - template
	function sc_signup_xup_signup($parm=null)
	{
		if (!e107::getUserProvider()->isSocialLoginEnabled()) return '';

		$size = empty($parm['size']) ? '2x' : $parm['size'];
		$class = empty($parm['class']) ?  'btn btn-primary' : $parm['class'] ;

		if($size == '2x')
		{
			$class .= ' btn-lg';
		}

		return $this->generateXupLoginButtons("signup", $size, $class);
	}

	/**
	 * @param string $type
	 * @param $size
	 * @param $class
	 * @return string
	 */
	private function generateXupLoginButtons($type, $size, $class)
	{
		$text = "";
		$tp = e107::getParser();

		$lan_plugin_social_xup = '';
		switch ($type)
		{
			case "login":
				$lan_plugin_social_xup = LAN_PLUGIN_SOCIAL_XUP_SIGNUP;
				break;
			case "signup":
				$lan_plugin_social_xup = LAN_PLUGIN_SOCIAL_XUP_REG;
				break;
		}


		$manager = new social_login_config(e107::getConfig());
		$providers = $manager->getSupportedConfiguredProviderConfigs();

		foreach ($providers as $p => $v)
		{
			if ($v['enabled'] == 1)
			{
				$ic = strtolower($p);

				switch ($ic)
				{
					case 'windowslive':
						$ic = 'windows';
						break;
				}

				if (defset('FONTAWESOME') && in_array($ic, e107::getMedia()->getGlyphs('fa4')))
					$button = "<span title='" . $tp->lanVars($lan_plugin_social_xup, $p) . "'>" . $tp->toGlyph('fa-' . $ic, array('size' => $size, 'fw' => true)) . "</span>";
				elseif (is_file(e107::getFolder('images') . "xup/{$ic}.png"))
					$button = "<img class='e-tip' title='" . $tp->lanVars($lan_plugin_social_xup, $p) . "' src='" . e_IMAGE_ABS . "xup/{$ic}.png' alt='' />";
				else
					$button = "<span title='" . $tp->lanVars($lan_plugin_social_xup, $p) . "'>$p</span>";

				$callback_url = e107::getUserProvider($p)->generateCallbackUrl(e_REQUEST_URL);
				$text .= " <a title='" . $tp->lanVars($lan_plugin_social_xup, $p) . " ' role='button' class='signup-xup $class' href='$callback_url'>$button</a> ";
			}
			//TODO different icon options. see: http://zocial.smcllns.com/
		}

		return $text;
	}

	function sc_signup_form_open()
	{
		return "<form action='".e_SELF."' method='post' id='signupform' class='signup-form form-horizontal' autocomplete='off'><div>".e107::getForm()->token()."</div>";
	}
	
	/* example: {SIGNUP_SIGNUP_TEXT}
	/* example: {SIGNUP_SIGNUP_TEXT: class=custom} */
	function sc_signup_signup_text($parm=null)
	{		
		$pref = e107::getPref();
		$tp = e107::getParser();
			
		if(!empty($pref['signup_text']))
		{
			$class = (!empty($parm['class'])) ? "class='".$parm['class']."'" : " class='alert alert-block alert-warning' ";
			
			return "<div id='signup-custom-text'  ".$class." >".$tp->toHTML($pref['signup_text'], TRUE, 'parse_sc,defs')."</div>";
		}
		
		/*
		
		elseif($pref['user_reg_veri'])
		{
			//	$SIGNUP_TEXT =	LAN_SIGNUP_80." <b>".LAN_SIGNUP_29."</b><br /><br />".
			// LAN_SIGNUP_30."<br />".
			// LAN_SIGNUP_85;
			//	return $SIGNUP_TEXT." ";
		}
		 */
		return null;
	}
	
	
	
	function sc_signup_displayname()
	{
		$pref = e107::getPref();

		if (check_class($pref['displayname_class']))
		{
			$dis_name_len = varset($pref['displayname_maxlength'],15);
			$val = !empty($_POST['username']) ? e107::getParser()->filter($_POST['username'], 'str') : '';
			return e107::getForm()->text('username', $val,  $dis_name_len);

		}

		return null;
	}
	
	/* example {SIGNUP_LOGINNAME} */
	/* example {SIGNUP_LOGINNAME: class=btn input-lg} */
	/* example {SIGNUP_LOGINNAME: placeholder=LAN_LOGINNAME} */
	/* example {SIGNUP_LOGINNAME: class=input-lg&placeholder=LAN_LOGINNAME} */

	function sc_signup_loginname($parm=null)
	{

		$pref = e107::getPref();
		if (vartrue($pref['predefinedLoginName']))
		{
		  return LAN_SIGNUP_67;
		}
 
	//	if ($pref['signup_option_loginname'])
		{
			$log_name_length = varset($pref['loginname_maxlength'],30);
			$options = array('size'=>30,'required'=>1);
			$options['title'] = str_replace("[x]",$log_name_length,LAN_SIGNUP_109); // Password must be at least
			$options['pattern'] = '[\S]*';
			$options['class'] = vartrue($parm['class'],'');
			$options['placeholder'] = vartrue($parm['placeholder']) ? $parm['placeholder']  : '';

			$val = !empty($_POST['loginname']) ? e107::getParser()->filter($_POST['loginname'], 'str') : '';

			return e107::getForm()->text('loginname', $val, $log_name_length, $options);
		}
	}
	
	/* example {SIGNUP_REALNAME} */
	/* example {SIGNUP_REALNAME: class=btn input-lg} */
	/* example {SIGNUP_REALNAME: placeholder=LAN_SIGNUP_91} */
	/* example {SIGNUP_REALNAME: class=input-lg&placeholder=LAN_SIGNUP_91} */
	
	function sc_signup_realname($parm=null)
	{
		$pref = e107::getPref('signup_option_realname');
		if($pref < 1){ return null; }
			
		$options 				= array('size'=>30);
		$options['required'] 	= ($pref==2) ? 1 : 0;
		$options['title']		= LAN_SIGNUP_110;
		$options['class']   = vartrue($parm['class'],'');
		$options['placeholder'] = vartrue($parm['placeholder'],'');

		$val = ($_POST['realname']) ? e107::getParser()->filter($_POST['realname'], 'str') : '';

		return e107::getForm()->text('realname', $val, 100, $options);

	}
	
	/* example {SIGNUP_PASSWORD1} */
	/* example {SIGNUP_PASSWORD1: class=btn input-lg} */
	/* example {SIGNUP_PASSWORD1: placeholder=LAN_PASSWORD} */
	/* example {SIGNUP_PASSWORD1: class=input-lg&placeholder=LAN_PASSWORD} */
		
	function sc_signup_password1($parm=null)
	{

		$pref = e107::getPref('signup_option_password', 2);

		if($pref != 2)
		{
			return false;
		}

		$options = array('size'=>30,'class'=>'e-password tbox');
	//	$options['title'] = 'Password must contain at least 6 characters, including UPPER/lowercase and numbers';
	    $preLen = e107::getPref('signup_pass_len');
		$len = vartrue($preLen,6);
		$options['title'] = str_replace("[x]", $len, LAN_SIGNUP_107); // Password must contain  at least
	//	$options['pattern'] = '(?=.*\d)(?=.*[a-z])(?=.*[A-Z])\w{'.$len.',}'; // at least one number, one lowercase and uppercase. 
		$options['required'] = true;
		$options['pattern'] = '(?=^.{'.$len.',}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$';
		$options['autocomplete'] = 'off';

		if(!empty($parm['class']))
		{
			$options['class'] = $parm['class'];
		}

		if(!empty($parm['placeholder']))
		{
			$options['placeholder'] = $parm['placeholder'];
		}
		elseif(!empty($preLen))
		{
			$text = defset('LAN_SIGNUP_125', "Min. [x] chars.");
			$options['placeholder'] = e107::getParser()->lanVars($text, $preLen);
		}

	//	$options['pattern'] = '\w{'.$len.',}'; // word of minimum length 
	
		return e107::getForm()->password('password1', '', 20, $options);
	}
	
	/* example {SIGNUP_PASSWORD2} */
	/* example {SIGNUP_PASSWORD2: class=btn input-lg} */
	/* example {SIGNUP_PASSWORD2: placeholder=LAN_SIGNUP_84} */
	/* example {SIGNUP_PASSWORD2: class=input-lg&placeholder=LAN_SIGNUP_84} */	
	
	function sc_signup_password2($parm=null)
	{

		$pref = e107::getPref('signup_option_password', 2);

		if($pref != 2)
		{
			return false;
		}

		$options = array('size'=>30,'class'=>'e-password tbox','required'=>1); //defaults

		if(!empty($parm['class']))
		{
			$options['class'] = $parm['class'];
		}

		if(!empty($parm['placeholder']))
		{
			$options['placeholder'] = $parm['placeholder'];
		}
				
		return e107::getForm()->password('password2', '', 20, $options);
	}


	/**
	 * @deprecated - placeholder in PASSWORD1 includes this.
	 * @return null
	 */
	function sc_signup_password_len()
	{
		global $pref, $SIGNUP_PASSWORD_LEN;
		if($pref['signup_pass_len'] && !empty($SIGNUP_PASSWORD_LEN))
		{
			return $SIGNUP_PASSWORD_LEN;
		}

		return null;
	}
	
	/* example {SIGNUP_EMAIL} */
	/* example {SIGNUP_EMAIL: class=btn input-lg} */
	/* example {SIGNUP_EMAIL: placeholder=LAN_USER_60} */
	/* example {SIGNUP_EMAIL: class=input-lg&placeholder=LAN_USER_60} */
		
	function sc_signup_email($parm=null)
	{	
		$options = array('size'=>30,'required'=>1);
		$options['title'] = LAN_SIGNUP_108; // Must be a valid email address.
		$options['class']   = vartrue($parm['class'], 'tbox form-control e-email');
		$options['placeholder'] = vartrue($parm['placeholder'],'');

		$val = !empty($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';

		$text = e107::getForm()->email('email', $val,100,$options);
		$text .= "<div class='e-email-hint alert-warning' style='display:none; padding:10px' data-hint='Did you mean <b>[x]</b>?'><!-- --></div>";
		$text .= "<input type='text' name='email2' value='' style='display:none' />"; // spam-trap. 
		return $text;
	}
	
	/* example {SIGNUP_EMAIL_CONFIRM} */
	/* example {SIGNUP_EMAIL_CONFIRM: class=btn input-lg} */
	/* example {SIGNUP_EMAIL_CONFIRM: placeholder=LAN_SIGNUP_39} */
	/* example {SIGNUP_EMAIL_CONFIRM: class=input-lg&placeholder=LAN_SIGNUP_39} */
		
	function sc_signup_email_confirm($parm=null)
	{
		$pref = e107::getPref('signup_option_email_confirm');
		if($pref < 1){ return null; }
			
		$options 				= array('size'=>30);
		$options['required'] 	= ($pref==2) ? 1 : 0;
		$options['class']       = vartrue($parm['class'],'tbox input-text e-email');
		$options['placeholder'] = vartrue($parm['placeholder'],'');

		$val = !empty($_POST['email_confirm']) ? filter_var($_POST['email_confirm'], FILTER_SANITIZE_EMAIL) : '';

		return e107::getForm()->email('email_confirm', $val, 100, $options);

	}
	
	
	function sc_signup_hide_email()
	{
		global $rs;
		$default_email_setting = 1;   // Gives option of turning into a pref later if wanted
		$pref = e107::getPref('signup_option_hideemail');

		if ($pref)
		{
			return $rs->form_radio("hideemail", 1, $default_email_setting==1)." <label for='hideemail1'>".LAN_YES."</label> &nbsp;&nbsp;".$rs->form_radio("hideemail",  0,$default_email_setting==0)." <label for='hideemail0'>".LAN_NO."</label>";
		}

		return null;
	}


	public function sc_signup_userclass_subscribe()
	{
		$pref = e107::pref('core');

		if(empty($pref['signup_option_class']))
		{
			return false;
		}

		$uc = e107::getUserClass();

		$ucList = $uc->get_editable_classes(e_UC_PUBLIC, true);	// This is signup so display only 'editable by public' classes.

		if(empty($ucList)) return false;

		$text = '';
		foreach($ucList as $classNum)
		{
			$text .= $this->show_signup_class($classNum, 1); // checked by default.
		}

		return $text;
	}



	private function show_signup_class( $classnum, $current_value='')
	{
		$tp = e107::getParser();
		$uc = e107::getUserClass();
		$frm = e107::getForm();

	//	if(deftrue('BOOTSTRAP'))
	//	{

			$text   = "<div class='checkboxes'>";
			$label  = $tp->toHTML($uc->getName($classnum),false, 'defs');
			$diz    = $tp->toHTML($uc->getDescription($classnum),false,'defs');
			$text   .= $frm->checkbox('class[]', $classnum, $current_value, array('label'=>$label,'title'=> $diz, 'class'=>'e-tip'));

			$text .= "</div>";

			return $text;
	//	}

		// code below is too unpredictable for reliable BC.
/*
		global $USERCLASS_SUBSCRIBE_ROW;

		e107::getDebug()->log($USERCLASS_SUBSCRIBE_ROW);

		$tmp = explode(',',$current_value);

		$shortcodes = array(
			'USERCLASS_ID'          => $classnum,
			'USERCLASS_NAME'        => $tp->toHTML($uc->getName($classnum),false, 'defs'),
			'USERCLASS_DESCRIPTION' => $tp->toHTML($uc->getDescription($classnum),false,'defs'),
			'USERCLASS_INDENT'      => " style='text-indent:".(1.2 * $nest_level)."em'",
			'USERCLASS_CHECKED'     => (in_array($classnum, $tmp) ? " checked='checked'" : '')
		);

		return $tp->simpleParse($USERCLASS_SUBSCRIBE_ROW, $shortcodes);*/

	}


	function sc_signup_extended_user_fields($parm = null)
	{
		if(empty($this->template['extended-user-fields']))
		{
			$msg = "SIGNUP 'extended-user-fields' template is not defined or empty";
			trigger_error($msg);
			return (ADMIN) ? $msg : '';
		}

		if(empty($this->template['extended-category']))
		{
			$msg = "SIGNUP 'extended-user-fields' template is not defined or empty";
			trigger_error($msg);
			return (ADMIN) ? $msg : '';
		}


		$text = "";

		$search = array(
			'{EXTENDED_USER_FIELD_TEXT}',
			'{EXTENDED_USER_FIELD_REQUIRED}',
			'{EXTENDED_USER_FIELD_EDIT}'
		);

		/** @var e107_user_extended $ue */
		$ue = e107::getUserExt();
		$ue->init();
		$tp = e107::getParser();

		$catList = $ue->getCategories();

		// Add in category zero - the 'no category' category
		array_unshift($catList, array('user_extended_struct_parent' => 0, 'user_extended_struct_id' => '0'));

		foreach($catList as $cat)
		{
			$extList = $ue->getFieldList($cat['user_extended_struct_id']);

			$done_heading = false;

			if(empty($extList))
			{
				continue;
			}

			foreach($extList as $ext)
			{
				$opts = $parm;
				$required = (int) $ext['user_extended_struct_required'];
				$edit = isset($_POST['ue']['user_' . $ext['user_extended_struct_name']]) ? $_POST['ue']['user_' . $ext['user_extended_struct_name']] : '';

				if($required === 0) // "No - Will not show on Signup page".
				{
					continue;
				}

				if(!$done_heading && !empty($cat['user_extended_struct_id']))  // Add in a heading
				{
					$catName = $cat['user_extended_struct_text'] ? $cat['user_extended_struct_text'] : $cat['user_extended_struct_name'];
					$catName = defset($catName, $catName);

					$text .= str_replace('{EXTENDED_CAT_TEXT}', $tp->toHTML($catName, false, 'TITLE'), $this->template['extended-category']);
					$done_heading = true;
				}

				$label = $tp->toHTML(deftrue($ext['user_extended_struct_text'], $ext['user_extended_struct_text']), false, 'emotes_off,defs');

				if(isset($opts['placeholder']))
				{
					$opts['placeholder'] = str_replace('[label]', $label, $opts['placeholder']);
				}

				$replace = array(
					$label,
					($required === 1 ? $this->sc_signup_is_mandatory('true') : ''),
					$ue->renderElement($ext, $edit, $opts)
				);

				$text .= str_replace($search, $replace, $this->template['extended-user-fields']);

			}
		}

		return $text;
	}
	
	
	function sc_signup_signature()
	{
		$sigActive = e107::pref('core','signup_option_signature'); 
		
		if($sigActive)
		{
			$frm = e107::getForm();
			return $frm->bbarea('signature', '', 'signature','helpb', 'tiny');
		}

		return null;
	}
	
	/* {SIGNUP_IMAGES}  */
	/* {SIGNUP_IMAGES: class=img-circle} */ 
	function sc_signup_images($parm = '') // AVATARS
	{
		$pref 	= e107::getPref();
		
		if($pref['signup_option_image'])
		{
			return e107::getForm()->avatarpicker('avatar', '', $parm);
		}

		return null;
	}
	
	
	function sc_signup_imagecode()
	{
		global $signup_imagecode, $rs, $sec_img;
		if($signup_imagecode)
		{
			return e107::getSecureImg()->r_image()."<div>".e107::getSecureImg()->renderInput()."</div>"; 
			// return $rs->form_hidden("rand_num", $sec_img->random_number). $sec_img->r_image()."<br />".$rs->form_text("code_verify", 20, "", 20);
		}
		unset($rs, $sec_img);
		return null;
	}
	
	function sc_signup_imagecode_label()
	{
		global $signup_imagecode,$sec_img;
		if($signup_imagecode)
		{
			return $sec_img->renderLabel(); 
		}

		return null;
	}
	
	
	function sc_signup_form_close()
	{
		return "</form>";
	}
	
	
	function sc_signup_is_mandatory($parm=null)
	{
		$pref = e107::pref('core');

		$mandatory = array(
			'realname'  => 'signup_option_realname',
			'subscribe' => 'signup_option_class',
			'avatar'    => 'signup_option_image',
			'signature' => 'signup_option_signature',
		);

		if((!empty($parm) && !empty($mandatory[$parm]) && (int) $pref[$mandatory[$parm]] === 2) || $parm === 'true' || ($parm === 'email' && empty($pref['disable_emailcheck'])))
		{
			return "<span class='required'><!-- empty --></span>";
		}

		if(!empty($parm))
		{
			switch($parm)
			{


				case 'email' :
					if(varset($pref['disable_emailcheck'], false))
					{
						return '';
					}
				break;

			}
		}

        return null;

	//	if((int) $val === 2)
		{
		//	return "<span class='required'><!-- empty --><</span>";
		}

		//return "<span class='required'></span>";
	}

	function sc_signup_button($parm=null)
	{
		$class = isset($parm['class']) ? $parm['class'] : "button btn btn-primary signup-button";

		return "<input class='".$class."' type='submit' name='register' value=\"".LAN_SIGNUP_79."\" />\n";
	}


	// allow main admin to view signup page for design/testing.
	function sc_signup_adminoptions()
	{
	
		
		if(getperms('0'))
		{
			$pref = e107::getPref();
			$frm = e107::getForm();
			$adminMsg = "<div class='form-group'>".LAN_SIGNUP_112."</div>";

			if(intval($pref['user_reg']) !== 1)
			{
				$adminMsg .= "<div class='form-group'><b>".LAN_SIGNUP_114."</b></div>";
			}

			$adminMsg .= "<div class='form-group form-inline'>
			<a class='btn btn-warning btn-danger btn-sm' href='".e_SELF."?preview'>".LAN_SIGNUP_115."</a>
			<a class='btn btn-error btn-danger btn-sm' href='".e_SELF."?preview.aftersignup'>".LAN_SIGNUP_116."</a>
			<a class='btn btn-error btn-danger btn-sm e-tip' href='".e_SELF."?test' title=\"".e107::getParser()->lanVars(LAN_SIGNUP_118,USEREMAIL)."\">".LAN_SIGNUP_117."</a>
			</div>
			";

			$adminMsg .= $frm->checkbox('simulation',1, false, LAN_SIGNUP_119);

			return "<div class='alert alert-block alert-error alert-danger text-center'>".$adminMsg."</div>";

		}


		return false;

	}

    /**
     * Create Privacy policy link
     * @param null $parm
     * @return string
     * @example {SIGNUP_GDPR_PRIVACYPOLICY_LINK}
     * @example {SIGNUP_GDPR_PRIVACYPOLICY_LINK: class=label label-info}
     */
	function sc_signup_gdpr_privacypolicy_link($parm=null)
	{
		$pp = e107::getPref('gdpr_privacypolicy', '');
		if (!$pp)
		{
			return '';
		}
		$pp = e107::getParser()->replaceConstants($pp, 'full');
		$class = (!empty($parm['class'])) ? $parm['class'] : '';
		return sprintf('<span class="%s"><a href="%s" target="_blank">%s</a></span>', $class, $pp, LAN_SIGNUP_122);

	}

    /**
     * Create Terms and conditions link
     * @param null $parm
     * @return string
     * @example {SIGNUP_GDPR_TERMSANDCONDITIONS_LINK}
     * @example {SIGNUP_GDPR_TERMSANDCONDITIONS_LINK: class=label label-info}
     */
	function sc_signup_gdpr_termsandconditions_link($parm=null)
	{
		$pp = e107::getPref('gdpr_termsandconditions', '');
		if (!$pp)
		{
			return '';
		}
		$pp = e107::getParser()->replaceConstants($pp, 'full');
		$class = (!empty($parm['class'])) ? $parm['class'] : '';
		return sprintf('<span class="%s"><a href="%s" target="_blank">%s</a></span>', $class, $pp, LAN_SIGNUP_123);

	}

	/**
	 * Print message "By signing up you agree to our Privacy policy and our Terms and conditions."
	 * @example {SIGNUP_GDPR_INFO}
	 */
	function sc_signup_gdpr_info()
	{
		if (!e107::getPref('gdpr_termsandconditions', '') || !e107::getPref('gdpr_privacypolicy', ''))
		{
			return '';
		}

		return e107::getParser()->lanVars(LAN_SIGNUP_124,
			array($this->sc_signup_gdpr_privacypolicy_link(), $this->sc_signup_gdpr_termsandconditions_link()));

	}

}


