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
|     $Revision: 1.3 $
|     $Date: 2005-01-23 10:08:41 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
global $FILES_DIRECTORY;
$text = "<div style='text-align:center' class='smalltext'>
".BACKEND_MENU_L1."<br />

<div class='spacer'><a href='".e_BASE."rss.php?1.1'><img src='".e_PLUGIN."backend_menu/images/rss1.png' alt='rss1.0' style='border:0' /></a></div>
<div class='spacer'><a href='".e_BASE."rss.php?1.2'><img src='".e_PLUGIN."backend_menu/images/rss2.png' alt='rss2.0' style='border:0' /></a></div>
<div class='spacer'><a href='".e_BASE."rss.php?1.3'><img src='".e_PLUGIN."backend_menu/images/rss3.png' alt='rdf' style='border:0' /></a><br /></div>



</div>";

$caption = (file_exists(THEME."images/backend_menu.png") ? "<img src='".THEME."images/backend_menu.png' alt='' style='vertical-align:middle' /> ".BACKEND_MENU_L2 : BACKEND_MENU_L2);

$ns -> tablerender($caption, $text, 'backend');
?>
