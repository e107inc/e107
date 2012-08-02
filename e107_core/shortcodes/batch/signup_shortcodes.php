<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

// Mods to show extended field categories

if (!defined('e107_INIT')) { exit; }


class signup_shortcodes extends e_shortcode
{
	
	function sc_signup_coppa_form()
	{
		if (strpos(LAN_SIGNUP_77, "stage") !== FALSE)
		{
			return "";
		}
		else
		{
			return "
		<form method='post' action='".e_SELF."?stage1' >\n
		<div><br />
		<input type='radio' name='coppa' value='0' checked='checked' /> ".LAN_NO."
		<input type='radio' name='coppa' value='1' /> ".LAN_YES."<br />
		<br />
		<input class='button' type='submit' name='newver' value=\"".LAN_CONTINUE."\" />
		</div></form>
		";
		}
	}
	
	function sc_signup_xup($param) // show it to those who were using xup
	{
		switch ($param) 
		{
			case 'login':
				return $this->sc_signup_social_login();	
			break;
			
			case 'signup':
			default:
				return $this->sc_signup_xup_signup();	
			break;
		}
	}
	
	// TODO - template
	function sc_signup_xup_login()
	{
		$pref = e107::getPref('social_login_active');
			
		if(!empty($pref))
		{
			$text = "";
			$providers = e107::getPref('social_login'); 

			foreach($providers as $p=>$v)
			{
				$p = strtolower($p);
				if($v['enabled'] == 1)
				{
					$text .= "<a href='".e107::getUrl()->create('system/xup/login?provider='.$p)."'><img class='e-tip' title='Register using your {$p} account' src='".e_HANDLER."hybridauth/icons/{$p}.png' alt='' /></a>";		
				}
				//TODO different icon options. see: http://zocial.smcllns.com/
			}	
			
		//	$text .= "<hr />";
			return $text;	
		}	
	}
	
	// TODO - template
	function sc_signup_xup_signup()
	{
		$pref = e107::getPref('social_login_active');
			
		if(!empty($pref))
		{
			$text = "";
			$providers = e107::getPref('social_login'); 

			foreach($providers as $p=>$v)
			{
				$p = strtolower($p);
				if($v['enabled'] == 1)
				{
					$text .= "<a href='".e107::getUrl()->create('system/xup/signup?provider='.$p)."'><img class='e-tip' title='Register using your {$p} account' src='".e_HANDLER."hybridauth/icons/{$p}.png' alt='' /></a>";		
				}
				//TODO different icon options. see: http://zocial.smcllns.com/
			}	
			
		//	$text .= "<hr />";
			return $text;	
		}	
	}
	
	
	function sc_signup_form_open()
	{
		global $rs;
		return $rs->form_open("post", e_SELF, "signupform");
	}
	
	
	function sc_signup_signup_text()
	{
			
		global $pref, $tp, $SIGNUP_TEXT;
		
		if($pref['signup_text'])
		{
			return $tp->toHTML($pref['signup_text'], TRUE, 'parse_sc,defs');
		}
		elseif($pref['user_reg_veri'])
		{
			return $SIGNUP_TEXT;
		}
	}
	
	
	
	function sc_signup_displayname()
	{
		global $pref, $rs;
		if (check_class($pref['displayname_class']))
		{
		  $dis_name_len = varset($pref['displayname_maxlength'],15);
		  return $rs->form_text('username', 30, ($_POST['username'] ? $_POST['username'] : $username), $dis_name_len);
		}
	}
	
	
	function sc_signup_loginname()
	{
		global $rs, $pref;
		if (varsettrue($pref['predefinedLoginName']))
		{
		  return LAN_SIGNUP_67;
		}
	//	if ($pref['signup_option_loginname'])
		{
			$log_name_length = varset($pref['loginname_maxlength'],30);
			$options = array('size'=>30,'required'=>1);
			$options['title'] = str_replace("[x]",$log_name_length,LAN_SIGNUP_109); // Password must be at least 
	
			return e107::getForm()->text('loginname', ($_POST['loginname'] ? $_POST['loginname'] : $loginname), $log_name_length, $options);
			// return $rs->form_text("loginname", 30,  , $log_name_length);
		}
	}
	
	
	function sc_signup_realname()
	{
		$pref = e107::getPref('signup_option_realname');
		if($pref < 1){ return; }
			
		$options 				= array('size'=>30);
		$options['required'] 	= ($pref==2) ? 1 : 0;
		$options['title']		= LAN_SIGNUP_110;
		return e107::getForm()->text('realname', ($_POST['realname'] ? $_POST['realname'] : $realname), 100, $options);
			
		//return $rs->form_text("realname", 30,  ($_POST['realname'] ? $_POST['realname'] : $realname), 100);
	}
	
	
	function sc_signup_password1()
	{
		$options = array('size'=>30,'class'=>'e-password tbox','required'=>1);
	//	$options['title'] = 'Password must contain at least 6 characters, including UPPER/lowercase and numbers';
		$len = vartrue(e107::getPref('signup_pass_len'),6);
		$options['title'] = str_replace("[x]",$len,LAN_SIGNUP_107); // Password must be at least 
		$options['pattern'] = '(?=.*\d)(?=.*[a-z])(?=.*[A-Z])\w{'.$len.',}'; // at least one number, one lowercase and uppercase. 
	//	$options['pattern'] = '\w{'.$len.',}'; // word of minimum length 
		
		return e107::getForm()->password('password1', '', 20, $options);
	}
	
	
	function sc_signup_password2()
	{
		return e107::getForm()->password('password2', '', 20, array('size'=>30,'class'=>'tbox','required'=>1));
	}
	
	
	function sc_signup_password_len()
	{
		global $pref, $SIGNUP_PASSWORD_LEN;
		if($pref['signup_pass_len'])
		{
			return $SIGNUP_PASSWORD_LEN;
		}
	}
	
	
	function sc_signup_email()
	{	
		$options = array('size'=>30,'required'=>1,'class'=>'tbox input-text e-email');
		$options['title'] = LAN_SIGNUP_108; // Must be a valid email address. 
		$text = e107::getForm()->email('email',($_POST['email'] ? $_POST['email'] : $email),100,$options);
		$text .= "<div class='e-email-hint' style='display:none' data-hint='Did you mean <b>[x]</b>?'><!-- --></div>";
		return $text;
	}
	
	
	function sc_signup_email_confirm()
	{
		$pref = e107::getPref('signup_option_email_confirm');
		if($pref < 1){ return; }
			
		$options 				= array('size'=>30);
		$options['required'] 	= ($pref==2) ? 1 : 0;
		$options['class'] 		= 'tbox input-text e-email';
		
		return e107::getForm()->email('email_confirm',($_POST['email_confirm'] ? $_POST['email_confirm'] : $email_confirm),100,$options);

	}
	
	
	function sc_signup_hide_email()
	{
		global $rs,$pref;
		$default_email_setting = 1;   // Gives option of turning into a pref later if wanted
		if ($pref['signup_option_realname'])
		{
			return $rs->form_radio("hideemail", 1, $default_email_setting==1)." ".LAN_YES."&nbsp;&nbsp;".$rs->form_radio("hideemail",  0,$default_email_setting==0)." ".LAN_NO;
		}
	}


	function sc_signup_userclass_subscribe()
	{
		global $pref, $e_userclass, $USERCLASS_SUBSCRIBE_START, $USERCLASS_SUBSCRIBE_END, $signupData;
		$ret = "";
		if($pref['signup_option_class'])
		{
		  if (!is_object($e_userclass))
		  {
			require_once(e_HANDLER.'userclass_class.php');
			$e_userclass = new user_class;
		  }
		  $ucList = $e_userclass->get_editable_classes();			// List of classes which this user can edit
		  $ret = '';
		  if(!$ucList) return;
		
/*
		  function show_signup_class($treename, $classnum, $current_value, $nest_level)
		  {
			global $USERCLASS_SUBSCRIBE_ROW, $e_userclass, $tp;
			$tmp = explode(',',$current_value);
			$search = array('{USERCLASS_ID}', '{USERCLASS_NAME}', '{USERCLASS_DESCRIPTION}', '{USERCLASS_INDENT}', '{USERCLASS_CHECKED}');
			$replace = array($classnum, $tp->toHTML($e_userclass->uc_get_classname($classnum), FALSE, 'defs'), 
							$tp->toHTML($e_userclass->uc_get_classdescription($classnum), FALSE, 'defs'), " style='text-indent:".(1.2*$nest_level)."em'",
							( in_array($classnum, $tmp) ? " checked='checked'" : ''));
			return str_replace($search, $replace, $USERCLASS_SUBSCRIBE_ROW);
		  }*/

		  $ret = $USERCLASS_SUBSCRIBE_START;
		  $ret .= $e_userclass->vetted_tree('class',array($this,show_signup_class),varset($signupData['user_class'],''),'editable');
			$ret .= $USERCLASS_SUBSCRIBE_END;
			return $ret;
		}
	}

	function show_signup_class($treename, $classnum, $current_value, $nest_level)
	{
		global $USERCLASS_SUBSCRIBE_ROW, $e_userclass, $tp;
		$tmp = explode(',',$current_value);
		$search = array('{USERCLASS_ID}', '{USERCLASS_NAME}', '{USERCLASS_DESCRIPTION}', '{USERCLASS_INDENT}', '{USERCLASS_CHECKED}');
		$replace = array($classnum, $tp->toHTML($e_userclass->uc_get_classname($classnum), FALSE, 'defs'), 
						$tp->toHTML($e_userclass->uc_get_classdescription($classnum), FALSE, 'defs'), " style='text-indent:".(1.2*$nest_level)."em'",
						( in_array($classnum, $tmp) ? " checked='checked'" : ''));
		return str_replace($search, $replace, $USERCLASS_SUBSCRIBE_ROW);
	}
	
	
	
	function sc_signup_extended_user_fields()
	{ 
		global $usere, $tp, $SIGNUP_EXTENDED_USER_FIELDS, $EXTENDED_USER_FIELD_REQUIRED, $SIGNUP_EXTENDED_CAT;
		$text = "";
		
		$search = array(
		'{EXTENDED_USER_FIELD_TEXT}',
		'{EXTENDED_USER_FIELD_REQUIRED}',
		'{EXTENDED_USER_FIELD_EDIT}'
		);
		
		
		// What we need is a list of fields, ordered first by parent, and then by display order?
		// category entries are `user_extended_struct_type` = 0
		// 'unallocated' entries are `user_extended_struct_parent` = 0
		
		// Get a list of defined categories
		$catList = $usere->user_extended_get_categories(FALSE);
		// Add in category zero - the 'no category' category
		array_unshift($catList,array('user_extended_struct_parent' => 0, 'user_extended_struct_id' => '0'));
		
		
		
		foreach($catList as $cat)
		{
		  $extList = $usere->user_extended_get_fieldList($cat['user_extended_struct_id']);
		
		  $done_heading = FALSE;
		  
		  foreach($extList as $ext)
		  {
		  	if($ext['user_extended_struct_required'] == 1 || $ext['user_extended_struct_required'] == 2)
		   	{
		      if(!$done_heading  && ($cat['user_extended_struct_id'] > 0))
		      {	// Add in a heading
				$catName = $cat['user_extended_struct_text'] ? $cat['user_extended_struct_text'] : $cat['user_extended_struct_name'];
				if(defined($catName)) $catName = constant($catName);
				$text .= str_replace('{EXTENDED_CAT_TEXT}', $tp->toHTML($catName, FALSE, 'emotes_off,defs'), $SIGNUP_EXTENDED_CAT);
				$done_heading = TRUE;
			  }
		  	  $replace = array(
		    			$tp->toHTML(deftrue($ext['user_extended_struct_text'], $ext['user_extended_struct_text']), FALSE, 'emotes_off,defs'),
		    			($ext['user_extended_struct_required'] == 1 ? $EXTENDED_USER_FIELD_REQUIRED : ''),
		    			$usere->user_extended_edit($ext, $_POST['ue']['user_'.$ext['user_extended_struct_name']])
		        );
		      $text .= str_replace($search, $replace, $SIGNUP_EXTENDED_USER_FIELDS);
		    }
		  }
		}
		return $text;
	}
	
	
	function sc_signup_signature()
	{
		global $pref, $SIGNUP_SIGNATURE_START, $SIGNUP_SIGNATURE_END;
		if($pref['signup_option_signature'])
		{
			$frm = e107::getForm();
			return $frm->bbarea('signature', $sig, 'signature','helpb','small');
		//	require_once(e_HANDLER."ren_help.php");
			$SIGNUP_SIGNATURE_START = str_replace("{REN_HELP}", $area, $SIGNUP_SIGNATURE_START);
			$SIGNUP_SIGNATURE_END = str_replace("{REN_HELP}", $area, $SIGNUP_SIGNATURE_END);
			$sig = ($_POST['signature'] ? $_POST['signature'] : $signature);
			return $SIGNUP_SIGNATURE_START.$sig.$SIGNUP_SIGNATURE_END;
		}
	}
	
	
	function sc_signup_images() // AVATARS
	{
		$pref 	= e107::getPref();
		
		if($pref['signup_option_image'])
		{
			return e107::getForm()->avatarpicker('avatar');
		}
	}
	
	
	function sc_signup_imagecode()
	{
		global $signup_imagecode, $rs, $sec_img;
		if($signup_imagecode)
		{
			return e107::getSecureImg()->r_image()."<div>".e107::getSecureImg()->renderInput()."</div>"; 
			// return $rs->form_hidden("rand_num", $sec_img->random_number). $sec_img->r_image()."<br />".$rs->form_text("code_verify", 20, "", 20);
		}
	}
	
	function sc_signup_imagecode_label()
	{
		global $signup_imagecode,$sec_img;
		if($signup_imagecode)
		{
			return $sec_img->renderLabel(); 
		}			
	}
	
	
	function sc_signup_form_close()
	{
		return "</form>";
	}
	
	
	function sc_signup_is_mandatory($parm='')
	{
		global $pref;
		if (isset($parm))
		{
		  switch ($parm)
		  {
		    case 'email' : if (varset($pref['disable_emailcheck'],FALSE)) return '';
		  }
		}
		return " *";
	}

}

?>