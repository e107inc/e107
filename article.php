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
|     $Source: /cvs_backup/e107_0.7/article.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-31 17:05:00 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
// This file is now depracated and remains in core for backward compatibilty reasons.
	
require_once("class2.php");

$tmp = explode(".", e_QUERY);
$action = $tmp[0];
$sub_action = $tmp[1];
$id = $tmp[2];
	
if ($sub_action == 255) {
	// content page
	header("location:".e_BASE."content.php?content.".$action);
	exit;
}
	
if ($action == 0) {
	// content page
	header("location:".e_BASE."content.php?article");
	exit;
} else {
	header("location:".e_BASE."content.php?review");
	exit;
}
	
?>