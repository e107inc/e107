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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/backend_menu/backend_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:11:59 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
global $FILES_DIRECTORY;
$text = "<div style='text-align:center' class='smalltext'>
".BACKEND_MENU_L1."<br />
<a href='".SITEURL.$FILES_DIRECTORY."backend/news.xml'>news.xml</a> - <a href='".SITEURL.$FILES_DIRECTORY."backend/news.txt'>news.txt</a>
</div>";

$caption = (file_exists(THEME."images/backend_menu.png") ? "<img src='".THEME."images/backend_menu.png' alt='' style='vertical-align:middle' /> ".BACKEND_MENU_L2 : BACKEND_MENU_L2);

$ns -> tablerender($caption, $text);
?>