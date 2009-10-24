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
|     $Revision: 1.5 $
|     $Date: 2009-10-24 08:45:43 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once('class2.php');

header('Content-type: text/html; charset=utf-8', TRUE);

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

if (!$pref['maintainance_flag'] )
{
	header('location: '.SITEURL);
	exit();
}

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
