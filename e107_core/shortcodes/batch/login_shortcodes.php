<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }


class login_shortcodes extends e_shortcode
{
	var $secImg = false;
	protected $userReg = false; // user registration pref.


	function __construct()
	{
		$pref = e107::getPref();
		$this->userReg = intval($pref['user_reg']);
		$this->secImg = ($pref['logcode'] && extension_loaded("gd")) ? true : false;	
	}
	
	function sc_login_username_label($parm='')
	{
		$pref = e107::getPref();
		$allowEmailLogin = varset($pref['allowEmailLogin'],0);
		$ulabel = array(LAN_LOGIN_1,LAN_LOGIN_28,LAN_LOGIN_29);
		return  $ulabel[$allowEmailLogin];	
	}
	
	function sc_login_table_loginmessage($parm='')
	{
		if(empty($this->userReg))
		{
			return null;
		}

		return LOGINMESSAGE;
	}
	
	function sc_login_table_username($parm='') //FIXME use $frm
	{

		if(empty($this->userReg))
		{
			return null;
		}

		$pref = e107::getPref();
		$allowEmailLogin = varset($pref['allowEmailLogin'],0);
		$ulabel = array(LAN_LOGIN_1,LAN_LOGIN_28,LAN_LOGIN_29);
		$placeholder =  $ulabel[$allowEmailLogin];	
		
		
		return "<input class='tbox form-control input-block-level' type='text' name='username' id='username' size='40' maxlength='100' placeholder=\"".$placeholder."\"  />";
	}
	
	function sc_login_table_password($parm='') //FIXME use $frm
	{
		if(empty($this->userReg))
		{
			return null;
		}

		$pref = e107::getPref();
		$text = "<input class='tbox form-control input-block-level' type='password' name='userpass' id='userpass' size='40' maxlength='100' placeholder=\"".LAN_LOGIN_2."\" />";
		
		if (!USER && e107::getSession()->is('challenge') && varset($pref['password_CHAP'],0)) 
		{
		  $text .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='".e107::getSession()->get('challenge')."' />\n\n";
		}
		return $text;	
	}
	
	function sc_login_table_secimg_lan($parm='')
	{
		if(empty($this->userReg))
		{
			return null;
		}

		if(!$this->secImg){ return; }
		return e107::getSecureImg()->renderLabel();
		// return LAN_LOGIN_13;	
	}
	
	
	function sc_login_table_secimg_hidden($parm='')
	{
		if(!$this->secImg){ return; }
		// return "<input type='hidden' name='rand_num' value='".$sec_img->random_number."' />";	// Not required. 
	}
	
	function sc_login_table_secimg_secimg($parm='')
	{
		if(empty($this->userReg))
		{
			return null;
		}

		if(!$this->secImg){ return; }
		return e107::getSecureImg()->renderImage();
		// return e107::getSecureImg()->r_image();	
	}
	
	function sc_login_table_secimg_textboc($parm='')
	{
		if(empty($this->userReg))
		{
			return null;
		}

		if(!$this->secImg){ return; }
		return 	e107::getSecureImg()->renderInput();
		// return "<input class='tbox' type='text' name='code_verify' size='15' maxlength='20' />";	
	}

	function sc_login_table_autologin($parm='')//FIXME use $frm
	{
		if(empty($this->userReg))
		{
			return null;
		}

		return "<input type='checkbox' name='autologin' value='1' />";	
	}
	

	function sc_login_table_autologin_lan($parm='')
	{
		if(empty($this->userReg))
		{
			return null;
		}

		return LAN_LOGIN_8;	
	}

	function sc_login_table_rememberme($parm=null)
	{
		if(empty($this->userReg))
		{
			return null;
		}

		return e107::getForm()->checkbox('autologin',1,false,LAN_LOGIN_8);

	}
	
	/* example: {LOGIN_TABLE_SUBMIT=large} */
	/* example: {LOGIN_TABLE_SUBMIT: class=btn submit_but}  */
	
	function sc_login_table_submit($parm="") //FIXME use $frm
	{
 
		if(empty($this->userReg))
		{
			return null;
		}

		$oldclass = ($parm == 'large') ? "btn-large btn-lg" : "";     
		
		$class = (!empty($parm['class'])) ? $parm['class'] : "btn btn-primary ".$oldclass." button";
 
		return "<input class='".$class."' type='submit' name='userlogin' value=\"".LAN_LOGIN_9."\" />";
	}
	
	
	function sc_login_table_footer_userreg($parm='')
	{
		//$pref = e107::getPref();

		if(empty($this->userReg))
		{
			return null;
		}
		
		$text = "<a href='".e_SIGNUP."'>".LAN_LOGIN_11."</a>";
		$text .= "&nbsp;&nbsp;&nbsp;<a href='".e_BASE."fpw.php'>".LAN_LOGIN_12."</a>";
		return $text;

	}

	/* example {LOGIN_TABLE_SIGNUP_LINK} */
	/* example {LOGIN_TABLE_SIGNUP_LINK: class=hover-black dg-btn-2 radius-3px btn-white hover-accent size-lg} */
	function sc_login_table_signup_link($parm='')
	{
	
		if($this->userReg === 1)
		{
		  $class = (!empty($parm['class'])) ? "class='".$parm['class']."'" : "";
		  
			return "<a href='".e_SIGNUP."' ".$class.">".LAN_LOGIN_11."</a>";
		}

		return null;
	}


	/* example {LOGIN_TABLE_FPW_LINK} */
	/* example {LOGIN_TABLE_FPW_LINK: class=dg-btn-2 btn-white radius-3px hover-white size-xl} */
	function sc_login_table_fpw_link($parm='')
	{
		if(empty($this->userReg))
		{
			return null;
		}
    $class = (!empty($parm['class'])) ? "class='".$parm['class']."'" : "";
		return "<a href='".e_HTTP."fpw.php' ".$class.">".LAN_LOGIN_12."</a>";
	}
	

}

?>
