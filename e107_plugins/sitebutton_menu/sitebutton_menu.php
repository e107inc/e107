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
|     $Revision: 1.4 $
|     $Date: 2005-01-27 19:53:18 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
$ns->tablerender(SITEBUTTON_MENU_L1, "<div style='text-align:center'>\n<a href='".SITEURL."'><img src='".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : e_IMAGE.SITEBUTTON)."' alt='".SITEBUTTON_MENU_L1."' style='border: 0px; width: 88px; height: 31px' /></a>\n</div>", 'sitebutton');
?>