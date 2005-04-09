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
|     $Revision: 1.4 $
|     $Date: 2005-04-09 03:57:28 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

global $pref;
global $tp;

if($pref['maintainance_text'])
{
	$SITEDOWN_TABLE_MAINTAINANCETEXT = $tp->toHTML($pref['maintainance_text'], TRUE, 'parse_sc', 'admin');
}
else
{
	$SITEDOWN_TABLE_MAINTAINANCETEXT = "<b>- ".SITENAME." ".LAN_00." -</b><br /><br />".LAN_01 ;
}

$SITEDOWN_TABLE_PAGENAME = PAGE_NAME;
	
if (!$SITEDOWN_TABLE)
{
	if (file_exists(THEME."sitedown_template.php"))
	{
		require_once(THEME."sitedown_template.php");
	}
	else
	{
		require_once(e_THEME."templates/sitedown_template.php");
	}
}

echo preg_replace("/\{(.*?)\}/e", '$\1', $SITEDOWN_TABLE);
	
?>