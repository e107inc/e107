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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/config.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-03-18 02:13:57 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
// *BK* This relocates to admin_config.php.  This is because to recognise an admin theme the script has to contain the 
// *BK* word admin in the filename.  However if you do that then the menu manager doesn't recognise the config file 
// *BK* which has to be called config.php otherwise there is no config option in the menu's combo
require_once("../../class2.php");
header("location:".e_PLUGIN."calendar_menu/admin_config.php");

?>