<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/article.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).

	Heavily updated by McFly

+---------------------------------------------------------------+
*/
// This file is now depracated and remains in core for backward compatibilty reasons.

require_once("class2.php");

$tmp = explode(".", e_QUERY);
$action = $tmp[0];
$sub_action = $tmp[1];
$id = $tmp[2];


if($sub_action == 255){	// content page
	header("location:".e_BASE."content.php?content.".$action);
	exit;
}

if($action == 0){	// content page
	header("location:".e_BASE."content.php?article");
	exit;
}else{
	header("location:".e_BASE."content.php?review");
	exit;
}

?>