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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/siteinfo/sitebutton_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2009-09-19 17:36:36 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
echo "parm=".$parm; //FIXME - just for testing only.
$ns->tablerender(SITEBUTTON_MENU_L1, "<div style='text-align:center'>\n<a href='".SITEURL."'><img src='".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : e_IMAGE_ABS.SITEBUTTON)."' alt='".SITEBUTTON_MENU_L1."' style='border: 0px; width: 88px; height: 31px' /></a>\n</div>", 'sitebutton');
?>