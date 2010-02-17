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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/blogcalendar_menu/functions.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
	
if (!defined('e107_INIT')) { exit; }

// format a date as yyyymmdd
function formatDate($year, $month, $day = "") {
	$date = $year;
	$date .= (strlen($month) < 2)?"0".$month:
	$month;
	$date .= (strlen($day) < 2 && $day != "")?"0".$day:
	$day;
	return $date;
}
?>