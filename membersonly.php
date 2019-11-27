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
|     $Source: /cvs_backup/e107_0.8/membersonly.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

	e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

	if(deftrue('BOOTSTRAP')) //v2.x
	{
		$MEMBERSONLY_TEMPLATE = e107::getCoretemplate('membersonly');
	}
	else // Legacy
	{
		if(is_readable(THEME."membersonly_template.php"))
		{
			require_once(THEME."membersonly_template.php");
		}
		else
		{
			require_once(e_CORE."templates/membersonly_template.php");
		}

		$MEMBERSONLY_TEMPLATE['default']['caption']	= $MEMBERSONLY_CAPTION;
		$MEMBERSONLY_TEMPLATE['default']['header']	= $MEMBERSONLY_BEGIN;
		$MEMBERSONLY_TEMPLATE['default']['body']	= $MEMBERSONLY_TABLE;
		$MEMBERSONLY_TEMPLATE['default']['footer'] 	= $MEMBERSONLY_END;
	}

	define('e_IFRAME',true);

class membersonly
{

	function sc_membersonly_signup()
	{
		$pref = e107::pref('core');

		if (intval($pref['user_reg'])===1)
		{
			$srch = array("[","]");
			$repl = array("<a class='alert-link' href='".e_SIGNUP."'>","</a>");
			return str_replace($srch,$repl, LAN_MEMBERS_3);
		}

	}

	function sc_membersonly_returntohome()
	{
		$pref = e107::pref('core');
		if($pref['membersonly_redirect'] == 'login')
		{
			return "<a class='alert-link' href='".e_HTTP."index.php'>".LAN_MEMBERS_4."</a>";
		}
	}

	function sc_membersonly_restricted_area()
	{
		return LAN_MEMBERS_1;
	}

	function sc_membersonly_login()
	{
		$srch = array("[","]");
		$repl = array("<a class='alert-link' href='".e_LOGIN."'>","</a>");
		return str_replace($srch,$repl, LAN_MEMBERS_2);
	}

}

	require_once(HEADERF);

	$mem = new membersonly;

	$BODY = e107::getParser()->parseTemplate( $MEMBERSONLY_TEMPLATE['default']['body'],true,$mem);

	echo $MEMBERSONLY_TEMPLATE['default']['header'];
	e107::getRender()->tablerender($MEMBERSONLY_TEMPLATE['default']['caption'], $BODY, 'membersonly');
	echo $MEMBERSONLY_TEMPLATE['default']['footer'];

	require_once(FOOTERF);
?>