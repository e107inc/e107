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
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:21 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("0")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

ob_start();
phpinfo();
$phpinfo .= ob_get_contents();
$phpinfo = eregi_replace("^.*\<body\>", "", $phpinfo);
ob_end_clean();
$ns -> tablerender("PHPInfo", $phpinfo);
require_once("footer.php");
?>