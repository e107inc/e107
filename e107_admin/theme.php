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
|     $Source: /cvs_backup/e107_0.7/e107_admin/theme.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-15 12:08:01 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("1")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'theme_manage';

require_once("auth.php");

require_once(e_HANDLER."theme_handler.php");
$themec = new themeHandler;



$themec -> showThemes();
require_once("footer.php");



?>