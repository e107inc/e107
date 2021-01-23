<?php



class membersonly_shortcodes extends e_shortcode
{

	/**
	 * @example {MEMBERSONLY_SIGNUP}
	 * @return string
	 */
	function sc_membersonly_signup()
	{
		$pref = e107::pref('core');

		if(intval($pref['user_reg']) === 1)
		{
			$srch = array("[", "]");
			$repl = array("<a class='alert-link' href='" . e_SIGNUP . "'>", "</a>");

			return str_replace($srch, $repl, LAN_MEMBERS_3);
		}

	}


	/**
	 * @example {MEMBERSONLY_RETURNTOHOME}
	 * @return string
	 */
	function sc_membersonly_returntohome()
	{

		$pref = e107::pref('core');
		if($pref['membersonly_redirect'] == 'login')
		{
			return "<a class='alert-link' href='" . e_HTTP . "index.php'>" . LAN_MEMBERS_4 . "</a>";
		}
	}


	/**
	 * @example {MEMBERSONLY_RESTRICTED_AREA}
	 * @return string
	 */
	function sc_membersonly_restricted_area()
	{
		return LAN_MEMBERS_1;
	}


	/**
	 * @example {MEMBERSONLY_LOGIN}
	 * @return string|
	 */
	function sc_membersonly_login()
	{

		$srch = array("[", "]");
		$repl = array("<a class='alert-link' href='" . e_LOGIN . "'>", "</a>");

		return str_replace($srch, $repl, LAN_MEMBERS_2);
	}

}
