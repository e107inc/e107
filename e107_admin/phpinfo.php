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
|     $Source: /cvs_backup/e107_0.7/e107_admin/phpinfo.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-08-23 00:44:23 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("0")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'phpinfo';
require_once("auth.php");
	
ob_start();
phpinfo();
$phpinfo .= ob_get_contents();
$phpinfo = preg_replace("#^.*\<body\>#i", "", $phpinfo);
ob_end_clean();
$ns->tablerender("PHPInfo", $phpinfo);
require_once("footer.php");
?>