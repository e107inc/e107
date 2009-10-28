<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/sitedown.php,v $
|     $Revision: 1.12 $
|     $Date: 2009-10-28 14:06:57 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once('class2.php');

if (!varset($pref['maintainance_flag']))
{
	header('location: '.SITEURL);
	exit();
}

header('Content-type: text/html; charset='.CHARSET, TRUE);

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

require_once(e_FILE.'shortcode/batch/sitedown_shortcodes.php');

if (!$SITEDOWN_TABLE)
{
	if (file_exists(THEME.'sitedown_template.php'))
	{
		require_once(THEME.'sitedown_template.php');
	}
	else
	{
		require_once(e_THEME.'templates/sitedown_template.php');
	}
}

echo $tp->parseTemplate($SITEDOWN_TABLE, TRUE, $sitedown_shortcodes);
