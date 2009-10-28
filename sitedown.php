<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/sitedown.php,v $
|     $Revision: 1.7 $
|     $Date: 2009-10-28 15:37:46 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once('class2.php');

if (!varset($pref['maintainance_flag']))
{
	header('location: '.SITEURL);
	exit();
}

header('Content-type: text/html; charset=utf-8');

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
