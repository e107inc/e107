<?php
/*
+ ----------------------------------------------------------------------------+
e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/index.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-01-31 03:47:04 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
	
require_once("class2.php");
if ($pref['membersonly_enabled'] && !USER)
{
	header("location: ".e_LOGIN);
	exit;
}
	
if (!$pref['frontpage'] || $pref['frontpage_type'] == "splash")
{
	header("location: ".e_BASE."news.php");
	exit;
}
elseif(is_numeric($pref['frontpage'])) 
{
	header("location: ".e_BASE."content.php?content.".$pref['frontpage']."");
	exit;
}
elseif(eregi("http", $pref['frontpage']))
{
	header("location: ".e_BASE.$pref['frontpage']);
	exit;
}
elseif ($pref['frontpage'] == 'forum')
{
	header("location: ".e_PLUGIN."forum/forum.php");
	exit;
}
else
{
	header("location: ".e_BASE.$pref['frontpage'].".php");
	exit;
}
?>