<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/index.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
if(!$pref['frontpage'] || $pref['frontpage_type'] == "splash"){
	header("location: ".e_BASE."news.php");
	exit;
}else if(is_numeric($pref['frontpage'])){
	header("location: ".e_BASE."article.php?".$pref['frontpage'].".255");
	exit;
}else if(eregi("http", $pref['frontpage'])){
	header("location: ".e_BASE.$pref['frontpage']);
	exit;
}else{
	header("location: ".e_BASE.$pref['frontpage'].".php");
	exit;
}
?>