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
|     $Revision: 1.2 $
|     $Date: 2009-07-14 05:31:57 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

global $pref;
global $tp;

e107_include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitedown.php");
e107_include_once(e_LANGUAGEDIR."English/lan_sitedown.php");

if (!$pref['maintainance_flag'] )
{
	header("location: ".SITEURL);
}

require_once(e_FILE."shortcode/batch/sitedown_shortcodes.php");

if (!$SITEDOWN_TABLE) {
	if (file_exists(THEME."sitedown_template.php"))
	{
		require_once(THEME."sitedown_template.php");
	}
	else
	{
		require_once(e_THEME."templates/sitedown_template.php");
	}
}

	echo $tp->parseTemplate($SITEDOWN_TABLE, TRUE, $sitedown_shortcodes);

?>