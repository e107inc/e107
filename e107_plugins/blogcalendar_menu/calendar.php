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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/blogcalendar_menu/calendar.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-02-08 13:33:45 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
| Based on code by: Thomas Bouve (crahan@gmx.net) and
| and Based on: PHP Calendar by Keith Devens http://www.keithdevens.com/software/php_calendar/
*/
	
function calendar($req_day, $req_month, $req_year, $links = NULL, $ws = "sunday") {
	// get access to the preferences
	global $pref;
	 
	// prepare the day array
	$darray = array(BLOGCAL_D1, BLOGCAL_D2, BLOGCAL_D3, BLOGCAL_D4,
		BLOGCAL_D5, BLOGCAL_D6, BLOGCAL_D7);
	 
	// what day does the week start on?
	switch($ws) {
		case "monday":
		$ws = "1";
		 break;
		case "sunday":
		 array_unshift($darray, array_pop($darray));
		$ws = "0";
	}
	 
	// what's the padding we should use for the cells?
	$padding = $pref['blogcal_padding']?$pref['blogcal_padding']:
	"2";
	 
	$date = mktime(0, 0, 0, $req_month, 1, $req_year);
	$last_day = date('t', $date);
	$date_info = getdate($date);
	$day_of_week = $date_info['wday'];
	if ($ws && $day_of_week == 0) $day_of_week = 7;
	 
	// print the daynames
	$calendar = "<table class='fborder'>";
	$calendar .= '<tr>';
	foreach($darray as $dheader) {
		$calendar .= "<td class='forumheader' style='padding: ".$padding."px;'><span class='smalltext'>$dheader</span></td>";
	}
	$calendar .= "</tr>";
	$calendar .= '<tr>';
	 
	$day_of_month = 1;
	$tablerow = 1;
	 
	// take care of the first "empty" days of the month
	if ($day_of_week-$ws > 0) {
		$calendar .= "<td colspan='";
		$calendar .= $day_of_week-$ws;
		$calendar .= "'>&nbsp;</td>";
	}
	 
	// print the days of the month (take the $ws into account)
	while ($day_of_month <= $last_day) {
		if ($day_of_week-$ws == 7) {
			#start a new week
			$calendar .= "</tr><tr>";
			$day_of_week = 0+$ws;
			$tablerow++;
		}
		if ($day_of_month == $req_day) {
			$day_style = isset($links[$day_of_month]) ? "indent" : "forumheader3";
		} else {
			$day_style = isset($links[$day_of_month]) ? "indent" : "forumheader3";
		}
		$calendar .= "<td class='$day_style' style='padding: ".$padding."px;'><span class='smalltext'>";
		$calendar .= isset($links[$day_of_month]) ? "<a href='".$links[$day_of_month]."'>":"";
		$calendar .= $day_of_month;
		$calendar .= isset($links[$day_of_month]) ? "</a>" : "";
		$calendar .= "</span></td>";
		$day_of_month++;
		$day_of_week++;
	}
	if ($day_of_week-$ws != 7) {
		$calendar .= '<td colspan="' . (7 - $day_of_week+$ws) . '">&nbsp;</td>';
	}
	$calendar .= "</tr>";
	if ($tablerow != 6) {
		$calendar .= "<tr><td style='padding: ".$padding."px;' colspan='6'>&nbsp;</td></tr>";
	}
	 
	$calendar .= "</table>";
	return $calendar;
}
?>