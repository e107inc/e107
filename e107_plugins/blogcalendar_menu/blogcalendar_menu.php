<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/blogcalendar_menu/blogcalendar_menu.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
| Based on code by: Thomas Bouve (crahan@gmx.net)
*/
if (!defined('e107_INIT')) { exit; }


$cString = 'nq_news_blogacalendar_menu_'.preg_replace('#[^\w]#', '', $parm);
$cached = e107::getCache()->retrieve($cString);


if(false === $cached)
{

	require_once(e_PLUGIN."blogcalendar_menu/calendar.php");
	require_once(e_PLUGIN."blogcalendar_menu/functions.php");
	
	// ------------------------------
	// initialization + fetch options
	// ------------------------------
	$prefix	 			= e_PLUGIN_ABS."blogcalendar_menu";
	$marray 			= e107::getDate()->terms('month'); 
	
	
	
	
	$pref['blogcal_ws'] = "monday";
		
	// ----------------------------------------------
	// get the requested and current date information
	// ----------------------------------------------
	list($cur_year, $cur_month, $cur_day) = explode(" ", date("Y n j"));
	if (e_PAGE == 'news.php' && strstr(e_QUERY, "day")) 
	{
		$tmp = explode(".", e_QUERY);
		// Core now support legacy queries - use just the old way
		//$tmp = e107::getUrl()->parseRequest('core:news', 'main', urldecode(e_QUERY));
		$item = $tmp[1];
		$req_year = intval(substr($item, 0, 4));
		$req_month = intval(substr($item, 4, 2));
		// decide on the behaviour here, do we highlight
		// the day being viewed? or only 'today'?
		//$req_day = substr($item, 6, 2);
		// if the requested year and month are the current, then add
		// the current day to the mix so the calendar highlights it
		if (($req_year == $cur_year) && ($req_month == $cur_month)) 
		{
			$req_day = $cur_day;
		} 
		else 
		{
			$req_day = "";
		}
	}
	elseif(e_PAGE == 'news.php' && strstr(e_QUERY, "month")) 
	{
		$tmp = explode(".", e_QUERY);
		// Core now support legacy queries - use just the old way
		//$tmp = e107::getUrl()->parseRequest('core:news', 'main', urldecode(e_QUERY));
		$item = $tmp[1];
		$req_year = intval(substr($item, 0, 4));
		$req_month = intval(substr($item, 4, 2));
		// if the requested year and month are the current, then add
		// the current day to the mix so the calendar highlights it
		if (($req_year == $cur_year) && ($req_month == $cur_month)) 
		{
			$req_day = $cur_day;
		} 
		else 
		{
			$req_day = "";
		}
	} 
	else 
	{
		$req_year = $cur_year;
		$req_month = $cur_month;
		$req_day = $cur_day;
	}
		
	// -------------------------------
	// create the month selection item
	// -------------------------------

	
	// get all newsposts since the beginning of the year till now
	// -------------------------------------------
	// get links to all newsitems in current month
	// -------------------------------------------
	$month_start 	= mktime(0, 0, 0, $req_month, 1, $req_year);
	$lastday 		= date("t", $month_start);
	$month_end 		= mktime(23, 59, 59, $req_month, $lastday, $req_year);
	$start 			= mktime(0, 0, 0, 1, 1, 1980);
	$end 			= time();
	
	$year_start 	= mktime(0, 0, 0, 1, 1, $req_year);
	$year_end 		= mktime(23, 59, 59, 12, 31, $req_year);
	
	$sql->select("news", "news_id, news_datestamp", "news_class IN (".USERCLASS_LIST.") AND (FIND_IN_SET('0', news_render_type) OR FIND_IN_SET(1, news_render_type)) AND news_datestamp > ".intval($start)." AND news_datestamp < ".intval($end));
	
	$links = array();
	$months = array();
	
	while ($news = $sql->fetch())
	{
		$xmonth = date("n", $news['news_datestamp']);
		$xyear = date("Y", $news['news_datestamp']);
		if (!isset($month_links[$xmonth]) || empty($month_links[$xmonth]))
		{
			$month_links[$xmonth] = e107::getUrl()->create('news/list/month', 'id='.formatDate($xyear, $xmonth));//e_BASE."news.php?month.".formatDate($req_year, $xmonth);
		}
	//	if(($news['news_datestamp'] >= $month_start && $news['news_datestamp'] <= $month_end) || (deftrue('BOOTSTRAP') && $news['news_datestamp'] >= $year_start && $news['news_datestamp'] <= $year_end))
		{
			$xday = date("j", $news['news_datestamp']);
			if (!isset($links[$xyear][$xmonth][$xday]))
			{
				$links[$xyear][$xmonth][$xday] = e107::getUrl()->create('news/list/day', 'id='.formatDate($xyear, $xmonth, $xday));//e_BASE."news.php?day.".formatDate($req_year, $req_month, $xday);
	
				$day_links[$xday] = e107::getUrl()->create('news/list/day', 'id='.formatDate($xyear, $xmonth, $xday));//e_BASE."news.php?day.".formatDate($req_year, $req_month, $xday);
			}
		}
		
		$months[$xyear][$xmonth] = 1;
	}
	
	// if we're listing the current year, add the current month to the list regardless of posts
	if ($req_year == $cur_year) 
	{
		$month_links[$cur_month] = e107::getUrl()->create('news/list/month', 'id='.formatDate($cur_year, $cur_month));//e_BASE."news.php?month.".formatDate($cur_year, $cur_month);
	}
		
	// go over the link array and create the option fields
	
	if(!isset($months[$cur_year][$cur_month])) // display current month even if no links available. 
	{
		$months[$cur_year][$cur_month] = 1;	
	}

			
	// ------------------------
	// create and show calendar
	// ------------------------
	/*
	$menu = "<div style='text-align: center;'><table border='0' cellspacing='7'>";
	$menu .= "<tr><td>$month_selector";
	$menu .= "<div style='text-align:center'>".calendar($req_day, $req_month, $req_year, $day_links, $pref['blogcal_ws'])."</div>";
	$menu .= "<div class='forumheader' style='text-align: center; margin-top:2px;'><span class='smalltext'><a href='$prefix/archive.php'>".BLOGCAL_L2."</a></span></div></td></tr>";
	$menu .= "</table></div>";
	*/

	
	if(deftrue('BOOTSTRAP')) // v2.x
	{
		$month_selector = '<span class="btn-group pull-right float-right"><a class="btn btn-mini btn-default btn-secondary btn-sm btn-xs " href="#blogCalendar" data-slide="prev">‹</a>  
 		<a class="btn btn-mini btn-default btn-secondary btn-sm btn-xs" href="#blogCalendar" data-slide="next">›</a></span>';
		 
		$caption = "<span class='inline-text'>".BLOGCAL_L1." ".$month_selector."</span>";	
		
		$menu = "<div id='blogCalendar' data-interval='false' class='carousel slide blogcalendar-block text-center'>";
		$menu .= "<div class='blogcalendar-day-selector carousel-inner'>";
		
		foreach($months as $year=>$val)
		{
			foreach($val as $month=>$v)
			{
				$menu .= calendar($req_day, $month, $year, varset($links[$year][$month],array()), $pref['blogcal_ws']);
			}
		}
		
		$menu .= "</div>";
		$menu .= "<div class='blogcalendar-archive-link' >
		<a class='blogcalendar-archive-link btn btn-small btn-s btn-primary' href='$prefix/archive.php'>".BLOGCAL_L2."</a>
		</div>
		</div>";
		
	}
	else // BC 
	{
					
		$month_selector = "<div class='forumheader' style='text-align: center; margin-bottom: 2px;'>";
		$month_selector .= "<select name='activate' onchange='urljump(this.options[selectedIndex].value)' class='tbox'>";
		
		foreach($month_links as $index => $val) 
		{
			$month_selector .= "<option value='".$val."'";
			$month_selector .= ($index == $req_month)?" selected='selected'": "";
			$month_selector .= ">".$marray[$index]."</option>";
		}
		
		$month_selector .= "</select></div>";		
			    
		$menu = "<div class='blogcalendar-block' style='text-align: center; max-width: 100%; overflow: hidden;'>
		<table class='blogcalendar-table' border='0' cellspacing='7' cellpadding='0'>";
		$menu .= "<tr><td class='blogcalendar-month-selector'>".$month_selector;
		$menu .= "<div class='blogcalendar-day-selector' style='text-align:center'>".calendar($req_day, $req_month, $req_year, $day_links, $pref['blogcal_ws'])."</div>";
		$menu .= "<div class='forumheader blogcalendar-archive-link' style='text-align: center; margin-top:2px;'><span class='smalltext'><a class='blogcalendar-archive-link' href='$prefix/archive.php'>".BLOGCAL_L2."</a></span></div></td></tr>";
		$menu .= "</table></div>";	
		
		 $caption = "<span class='form-inline'>".BLOGCAL_L1." ".$req_year."</span>";		
	}
				
	$cached = $ns->tablerender($caption, $menu, 'blog_calendar', true);
//	echo "day= ".$req_day. " month=".$req_month." year=".$req_year." links=".print_a($day_links)." ws=".$pref['blogcal_ws'];
	e107::getCache()->set($cString, $menu);
	
}

echo $cached;
?>