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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/blogcalendar_menu/blogcalendar_menu.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-02-08 13:33:45 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
| Based on code by: Thomas Bouve (crahan@gmx.net)
*/
require_once(e_PLUGIN."blogcalendar_menu/calendar.php");
require_once(e_PLUGIN."blogcalendar_menu/functions.php");
	
// ------------------------------
// initialization + fetch options
// ------------------------------
$prefix = e_PLUGIN."blogcalendar_menu";
$marray = array(BLOGCAL_M1, BLOGCAL_M2, BLOGCAL_M3, BLOGCAL_M4,
	BLOGCAL_M5, BLOGCAL_M6, BLOGCAL_M7, BLOGCAL_M8,
	BLOGCAL_M9, BLOGCAL_M10, BLOGCAL_M11, BLOGCAL_M12);
$pref['blogcal_ws'] = "monday";
	
// ----------------------------------------------
// get the requested and current date information
// ----------------------------------------------
list($cur_year, $cur_month, $cur_day) = explode(" ", date("Y n j"));
if (strstr(e_QUERY, "day")) {
	$tmp = explode(".", e_QUERY);
	$item = $tmp[1];
	$req_year = substr($item, 0, 4);
	$req_month = substr($item, 4, 2);
	// decide on the behaviour here, do we highlight
	// the day being viewed? or only 'today'?
	//$req_day = substr($item, 6, 2);
	// if the requested year and month are the current, then add
	// the current day to the mix so the calendar highlights it
	if (($req_year == $cur_year) && ($req_month == $cur_month)) {
		$req_day = $cur_day;
	} else {
		$req_day = "";
	}
}
else if(strstr(e_QUERY, "month")) {
	$tmp = explode(".", e_QUERY);
	$item = $tmp[1];
	$req_year = substr($item, 0, 4);
	$req_month = substr($item, 4, 2);
	// if the requested year and month are the current, then add
	// the current day to the mix so the calendar highlights it
	if (($req_year == $cur_year) && ($req_month == $cur_month)) {
		$req_day = $cur_day;
	} else {
		$req_day = "";
	}
} else {
	$req_year = $cur_year;
	$req_month = $cur_month;
	$req_day = $cur_day;
}
	
// -------------------------------
// create the month selection item
// -------------------------------
$month_selector = "<div class='forumheader' style='text-align: center; margin-bottom: 2px;'>";
$month_selector .= "<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>";

// get all newsposts since the beginning of the year till now
// -------------------------------------------
// get links to all newsitems in current month
// -------------------------------------------
$month_start = mktime(0, 0, 0, $req_month, 1, $req_year);
$lastday = date("t", $month_start);
$month_end = mktime(23, 59, 59, $req_month, $lastday, $req_year);
$start = mktime(0, 0, 0, 1, 1, $req_year);
$end = time();
$sql->db_Select("news", "news_id, news_datestamp", "news_class IN (".USERCLASS_LIST.") AND news_datestamp > $start AND news_datestamp < $end");
while ($news = $sql->db_Fetch())
{
	$xmonth = date("n", $news['news_datestamp']);
	if (!$month_links[$xmonth])
	{
		$month_links[$xmonth] = e_BASE."news.php?month.".formatDate($req_year, $xmonth);
	}
	if($news['news_datestamp'] >= $month_start AND $news['news_datestamp'] <= $month_end)
	{
		$xday = date("j", $news['news_datestamp']);
		if (!$day_links[$xday])
		{
			$day_links[$xday] = e_BASE."news.php?day.".formatDate($req_year, $req_month, $xday);
		}
	}
}

// if we're listing the current year, add the current month to the list regardless of posts
if ($req_year == $cur_year) {
	$month_links[$cur_month] = e_BASE."news.php?month.".formatDate($cur_year, $cur_month);
}
	
// go over the link array and create the option fields
foreach($month_links as $index => $val) {
	$month_selector .= "<option value='".$val."'";
	$month_selector .= ($index == $req_month)?" selected":
	"";
	$month_selector .= ">".$marray[$index-1]."</option>";
}
	
// close the select item
$month_selector .= "</select></div>";
	
	
// ------------------------
// create and show calendar
// ------------------------
$menu = "<div style='text-align: center;'><table border='0' cellspacing='7'>";
$menu .= "<tr><td>$month_selector";
$menu .= "<div style='text-align:center'>".calendar($req_day, $req_month, $req_year, $day_links, $pref['blogcal_ws'])."</div>";
$menu .= "<div class='forumheader' style='text-align: center; margin-top:2px;'><span class='smalltext'><a href='$prefix/archive.php'>".BLOGCAL_L2."</a></span></div></td></tr>";
$menu .= "</table></div>";
$ns->tablerender(BLOGCAL_L1." ".$req_year, $menu, 'blog_calender');
?>