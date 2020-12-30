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
	 * @return string
	 */
	function sc_signin($parm = null)  // Naming:  "sc_" + [plugin-directory] + '_uniquename'
	{
		e107::includeLan(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");

		$this->lsc = e107::getScBatch('signin', 'signin');

		if(USERID) // Logged Out.
		{
			return $this->signOut($parm);
		}

		return $this->signIn($parm);
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
