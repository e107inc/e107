<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/blogcalendar_menu/calendar.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */
/*
| Based on code by: Thomas Bouve (crahan@gmx.net) and
| and Based on: PHP Calendar by Keith Devens http://www.keithdevens.com/software/php_calendar/
*/






	
if (!defined('e107_INIT')) { exit; }

function calendar($req_day, $req_month, $req_year, $links = NULL, $ws = "sunday") 
{
	
	$pref = e107::getPref();
	 
	// prepare the day array
	$darray = e107::getDate()->terms('day-shortest');
	$marray = e107::getDate()->terms('month');
	 
	// what day does the week start on?
	switch($ws) 
	{
		case "monday":
			$ws = "1";
		break;
		 
		case "sunday":
			array_unshift($darray, array_pop($darray));
		$ws = "0";
	}
	 
	// what's the padding we should use for the cells?
	$padding 		= (isset($pref['blogcal_padding']) && $pref['blogcal_padding']) ? $pref['blogcal_padding']: "2";
	 
	$date 			= mktime(0, 0, 0, $req_month, 1, $req_year);
	$last_day 		= date('t', $date);
	$date_info 		= getdate($date);
	$day_of_week 	= $date_info['wday'];
	
	if ($ws && $day_of_week == 0) 
	{
		$day_of_week = 7;
	}
	 
	// print the daynames
	$calendar = "<table class='table blogcalendar fborder'>";
	$calendar .= '<thead><tr>';
	
	foreach($darray as $dheader) 
	{
		$calendar .= "<th class='forumheader blogcalendar-day-name'><span class='smalltext'>$dheader</span></th>";
	}
	
	$calendar .= "</tr>
					</thead>
					<tbody>";
	$calendar .= '<tr>';
	 
	$day_of_month = 1;
	$tablerow = 1;
	 
	// take care of the first "empty" days of the month
	if ($day_of_week-$ws > 0) 
	{
		$calendar .= "<td class='muted blogcalendar-day-empty' colspan='";
		$calendar .= $day_of_week-$ws;
		$calendar .= "'>&nbsp;</td>";
	}
	 
	// print the days of the month (take the $ws into account)
	while ($day_of_month <= $last_day) 
	{
		if ($day_of_week-$ws == 7) 
		{
			#start a new week
			$calendar .= "</tr><tr>";
			$day_of_week = 0+$ws;
			$tablerow++;
		}
		
		if ($day_of_month == $req_day) 
		{
			$day_style = isset($links[$day_of_month]) ? "indent blogcalendar-day-active" : "forumheader3 blogcalendar-day";
		}
		else 
		{
			$day_style = isset($links[$day_of_month]) ? "indent blogcalendar-day-active " : "forumheader3 blogcalendar-day";
		}
		
		$label_style = isset($links[$day_of_month]) ? 'label label-info' : ''; //TODO A pref in admin to choose between info, danger, etc. 
		
		$calendar .= "<td class='$day_style' >";
	
		$calendar .= isset($links[$day_of_month]) ? "<a class='blogcalendar-day-link' href='".$links[$day_of_month]."'>":"";
		$calendar .= "<span class='smalltext blogcalendar-day-link {$label_style}'>";
		$calendar .= $day_of_month;
		$calendar .= "</span>";
		$calendar .= isset($links[$day_of_month]) ? "</a>" : "";
		
		$calendar .= "</td>";
		$day_of_month++;
		$day_of_week++;
	}
		  
	
	
	if ($day_of_week-$ws != 7) 
	{
		$calendar .= '<td class="blogcalendar-day-empty" colspan="' . (7 - $day_of_week+$ws) . '">&nbsp;</td>';
	}
	
	$calendar .= "</tr>";
	
	if ($tablerow != 6) 
	{
		$calendar .= "<tr><td class='blogcalendar-day-empty' colspan='7'>&nbsp;</td></tr>";
	}
	 
	$calendar .= "</tbody></table>";
	
//	$calendar .= "tablerow = ".$tablerow;

	if(deftrue('BOOTSTRAP'))
	{
		$active = date("n-Y") == ($req_month."-".$req_year)  ? 'active' : '';
		$text = "<div class='item carousel-item {$active}'>";
		$text .= "<h5>".$marray[$req_month]." ".$req_year."</h5>";
		$text .= $calendar;
		$text .= "</div>";
	}
	else // BC
	{
		$text = $calendar;
	}	
	
	return $text;
}
?>
