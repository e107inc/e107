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
|     $Source: /cvs_backup/e107_0.8/article.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:01 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
// This file is now deprecated and remains in core for backward compatibility reasons.
	
$tmp = explode(".", $_SERVER['QUERY_STRING']);
$action = $tmp[0];
$sub_action = $tmp[1];
$id = $tmp[2];
	
if ($sub_action == 255) {
	// content page
	header("Location: content.php?content.{$action}");
	exit;
}
	
if ($action == 0) {
	// content page
	header("Location: content.php?article");
	exit;
} else {
	header("Location: content.php?review");
	exit;
}
	
?>