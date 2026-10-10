<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if(!defined('e107_INIT'))
{
	exit;
}



class signin_shortcodes extends e_shortcode
{
	public $override = false; // when set to true, existing core/plugin shortcodes matching methods below will be overridden. 
	private $lsc;


	/**
	 * @example {SIGNIN} shortcode - available site-wide.
	 * @param null $parm
	 * @return string|null
	 */
	function sc_signin($parm = null)  // Naming:  "sc_" + [plugin-directory] + '_uniquename'
	{
		e107::includeLan(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");

		$this->lsc = e107::getScBatch('signin', 'signin');

		if(e107::getUser()->isUser())
		{
			return $this->signOut($parm);
		}

		if(!$this->signinIsAvailable())
		{
			return null;
		}

		return $this->signIn($parm);
	}

	/**
	 * Whether a guest has anywhere to sign in, read as {@see redirection::getMembersOnlyRedirectUrl()} reads it.
	 *
	 * @return bool
	 */
	private function signinIsAvailable()
	{
		return e_LOGIN !== SITEURL.'login.php'
			|| e107::getPref('user_reg')
			|| e107::getUserProvider()->isSocialLoginEnabled();
	}

	/**
	 * Form to show to GUESTS.
	 *
	 * @param null $parm
	 * @return string
	 */
	private function signIn($parm=null)
	{
		$template = e107::getTemplate('signin', 'signin', 'signin');

		$this->lsc->wrapper('signin/signin');

		return e107::getParser()->parseTemplate($template, true, $this->lsc);

	}


	/**
	 * Form to show to MEMBERS.
	 * @param null $parm
	 * @return string
	 */
	private function signOut($parm=null)
	{
		// Logged in.
		$this->lsc->wrapper('signin/signout');

		$template = e107::getTemplate('signin', 'signin', 'signout'); // todo

		return e107::getParser()->parseTemplate($template, true, $this->lsc);


	}


}
