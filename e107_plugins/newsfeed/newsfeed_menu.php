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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/newsfeed_menu.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-03-03 04:00:18 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if(!defined("e_PLUGIN")){ exit; }
include_once(e_PLUGIN."newsfeed/newsfeed_functions.php");
$info = newsfeed_info('all');
$ns->tablerender($info['title'], $info['text']);
?>