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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/sitebutton_menu/sitebutton_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-13 13:20:45 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
$ns -> tablerender(SITEBUTTON_MENU_L1,  "<div style='text-align:center'>\n<a href='".SITEURL."'><img style='border:0' src='".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : e_IMAGE.SITEBUTTON)."' alt='".SITEBUTTON_MENU_L1."' /></a>\n</div>", 'sitebutton');
?>
