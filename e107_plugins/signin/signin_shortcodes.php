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


if (!defined('e107_INIT'))
{
	exit();
}

class plugin_signin_signin_shortcodes extends e_shortcode
{

	private $use_imagecode = 0;
	private $usernamePlaceholder = '';
	private $allowEmailLogin;
	private $authMethod;
	private $regMode;

	function __construct()
	{

		e107::plugLan('signin', '', true);

		$pref = e107::getPref();

		$this->use_imagecode = e107::getConfig()->get('logcode');
		$this->allowEmailLogin = varset($pref['allowEmailLogin'], 0);
		$this->usernamePlaceholder = defset('LAN_SIGNIN_USERNAME', '');

		if ($this->allowEmailLogin == 1)
		{
			$this->usernamePlaceholder = defset('LAN_SIGNIN_EMAIL', '');
		}

		if ($this->allowEmailLogin == 2)
		{
			$this->usernamePlaceholder = defset('LAN_SIGNIN_USEREMAIL', '');
		}

		$this->regMode = (int) defset('USER_REGISTRATION');

		$this->authMethod = vartrue($pref['auth_method'], 'e107');

	}


	/**
	 *
	 * @param array $parm
	 * @return null|string
	 */
	function sc_signin_active($parm = array())
	{

		//	$request = e_REQUEST_URI;

		$ret = null;

		$mode = varset($parm['mode']);

		if ($mode === 'settings' && defset('e_PAGE') === 'usersettings.php')
		{
			return 'active';
		}
		elseif ($mode === 'profile' && defset('e_PAGE') === 'user.php')
		{
			return 'active';
		}


		return null;
	}


	function sc_signin_input_username($parm = null)
	{

		$pref = e107::getPref();

		// If logging in with email address - ignore pref and increase to 100 chars.
		$maxLength = ($this->allowEmailLogin == 1 || $this->allowEmailLogin) ? 100 : varset($pref['loginname_maxlength'], 30);

		return "
				<label class='sr-only' for='" . vartrue($parm['idprefix']) . "username'>" . $this->usernamePlaceholder . "</label>
				<input class='form-control tbox login user' type='text' name='username' placeholder='" . $this->usernamePlaceholder . "' required='required' id='" . vartrue($parm['idprefix']) . "username' size='15' value='' maxlength='" . $maxLength . "' />\n";
	}



	function sc_signin_username($parm=null)
	{
		return !empty($parm['username']) ? USERNAME : '';
	}


	function sc_signin_input_password($parm = null)
	{

		$pref = e107::getPref();
		$t_password = "
				<label class='sr-only' for='" . vartrue($parm['idprefix']) . "userpass'>" . LAN_PASSWORD . "</label>
				<input class='form-control tbox login pass' type='password' placeholder='" . LAN_PASSWORD . "' required='required' name='userpass' id='" . vartrue($parm['idprefix']) . "userpass' size='15' value='' maxlength='30' />\n";

		if (!USER && e107::getSession()->is('challenge') && varset($pref['password_CHAP'], 0))
		{
			$t_password .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='" . e107::getSession()->get('challenge') . "' />\n\n";
		}

		return $t_password;
	}


	function sc_signin_password_label($parm = '')
	{
		return LAN_PASSWORD;
	}


	function sc_signin_imagecode_number($parm = '')
	{

		if ($this->use_imagecode)
		{
			return e107::getSecureImg()->renderImage(secure_image::FORM_LOGIN);
		}

		return null;
	}

	function sc_signin_form($parm=null)
	{
		return ($parm === 'start') ? '<form method="post" onsubmit="hashLoginPassword(this);return true" action="'.e_REQUEST_HTTP.'" accept-charset="UTF-8">' : '</form>';
	}

	function sc_signin_imagecode_box($parm = '')
	{

		if ($this->use_imagecode)
		{
			return e107::getSecureImg()->renderInput(secure_image::FORM_LOGIN);
		}

		return null;
	}



	function sc_signin_signup_href($parm = '')
	{

		if ($this->regMode !== 1 || $this->authMethod !== 'e107')
		{
			return null;
		}

		return e_SIGNUP;

	}

	function sc_signin_login_href($parm = '')
	{

		if ($this->regMode == 0 )
		{
			return null;
		}

		return e_LOGIN;

	}
	
	function sc_signin_resend_href($parm=null)
	{
		if ($this->regMode !== 1 || $this->authMethod !== 'e107')
		{
			return null;
		}

		return e_SIGNUP . '?resend';
	}



	function sc_signin_fpw_href($parm=null)
	{
		return SITEURL . 'fpw.php';
	}


	function sc_signin_maintenance($parm = '')
	{

		$pref = e107::getPref();

		if (ADMIN && !empty($pref['maintainance_flag']))
		{
			return defset('LAN_SIGNIN_MAINTENANCE', '');
		}

		return '';
	}


	function sc_signin_pm_nav($parm=null)
	{
		if(!e107::isInstalled('pm') )
		{
			return null;
		}

		$sc = e107::getScBatch('pm', true);

		// Being listed in plug_installed is not the same as having the batch
		// loaded: the class this needs lives in the plugin's e_shortcode.php,
		// which is only read when pm is in the e_shortcode addon list. The two
		// disagree after a failed install and after an uninstall in the same
		// request, and the menu is no reason to end the page.
		if(!is_object($sc))
		{
			return null;
		}

		return $sc->sc_pm_nav($parm);
	}

	function sc_signin_admin_href($parm = '')
	{
		if (ADMIN == true)
		{
			return e_ADMIN_ABS;
		}

		return null;
	}


	function sc_signin_usersettings_href($parm = null)
	{
		return e107::getUrl()->create('user/myprofile/edit', array('id' => USERID));
	}


	/**
	 * @todo- to be merged with sc_signin_profile() ?
	 * @param string $parm
	 * @return string
	 */
	function sc_signin_profile_href($parm = '')
	{
		return e107::getUrl()->create('user/profile/view', array('user_id' => USERID, 'user_name' => USERNAME));
	}



	function sc_signin_logout_href($parm = '')
	{
		return e_HTTP . 'index.php?logout';
	}



}


