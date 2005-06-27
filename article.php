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
|     $Revision: 1.4 $
|     $Date: 2005-06-27 01:45:02 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
// This file is now depracated and remains in core for backward compatibilty reasons.
	
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