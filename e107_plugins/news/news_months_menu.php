<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News by month menu
 */

if (!defined('e107_INIT')) { exit; }

$cString = 'nq_news_months_menu_'.md5($parm);
$cached = e107::getCache()->retrieve($cString);

if(false === $cached)
{
	if(!function_exists('newsFormatDate'))
	{
		function newsFormatDate($year, $month, $day = "") {
			$date = $year;
			$date .= (strlen($month) < 2)?"0".$month:
			$month;
			$date .= (strlen($day) < 2 && $day != "")?"0".$day:
			$day;
			return $date;
		}
	}
	

	//parse_str($parm, $parms); // FIXME - menu settings...
	$parms['showarchive'] = 0;
		
	e107::plugLan('blogcalendar_menu');
	$tp = e107::getParser();
	$sql = e107::getDb();
	
	$marray = array(BLOGCAL_M1, BLOGCAL_M2, BLOGCAL_M3, BLOGCAL_M4,
		BLOGCAL_M5, BLOGCAL_M6, BLOGCAL_M7, BLOGCAL_M8,
		BLOGCAL_M9, BLOGCAL_M10, BLOGCAL_M11, BLOGCAL_M12);
		
	
	// TODO parms
	//$parms['year'] = "2011 0";
	if(vartrue($parms['year']))
	{
		$date = $parms['year'];
		list($cur_year, $cur_month) = explode(" ", date($date));
		$start = mktime(0, 0, 0, 1, 1, $cur_year);
		$end = mktime(23, 59, 59, 12, 31, $cur_year);
	}
	else 
	{
		$date = "Y n";
		list($cur_year, $cur_month) = explode(" ", date($date));
		$start = mktime(0, 0, 0, 1, 1, $cur_year);
		$end = time();
	}
	
	$req_year = $cur_year;
	if(e_PAGE == 'news.php' && strstr(e_QUERY, "month")) 
	{
		$tmp = explode('.', e_QUERY);
		$item = $tmp[1];
		$req_month = intval(substr($item, 4, 2));
		$req = 'month';
	} 
	else 
	{
		$req_month = $cur_month;
	}

	$xmonth_cnt = array();
	$month_links = array();
	
	$sql->db_Mark_Time('News months menu');
	if(!$sql->db_Select("news", "news_id, news_datestamp", "news_class IN (".USERCLASS_LIST.") AND news_datestamp > ".intval($start)." AND news_datestamp < ".intval($end)." ORDER BY news_datestamp DESC"))
	{
		e107::getCache()->set($cString, '');
		return '';
	}
	while ($news = $sql->db_Fetch())
	{	
		$xmonth = date("n", $news['news_datestamp']);
		if ((!isset($month_links[$xmonth]) || !$month_links[$xmonth]))
		{
			$xmonth_cnt[$xmonth] = 0;
			$month_links[$xmonth] = e107::getUrl()->create('news/list/month', 'id='.newsFormatDate($req_year, $xmonth));
		}
		$xmonth_cnt[$xmonth]++;
	}
	
	// go over the link array and create the option fields
	$menu_text = array();
	$template = e107::getTemplate('news', 'news_menu', 'months');
	$bullet = defined('BULLET') ? THEME_ABS.'images/'.BULLET : THEME_ABS.'images/bullet2.gif';
	$vars = new e_vars(array('bullet' => $bullet));
	foreach($month_links as $index => $val) 
	{
		$vars->addData(array(
			'active' => $index == $req_month ? " active" : '',
			'url' => $val,
			'month' => $marray[$index-1],
			'count' => $xmonth_cnt[$index],
		));
		$menu_text[] = $tp->simpleParse($template['item'], $vars);
	}
	$cached = $template['start'].implode($template['separator'], $menu_text).$template['end']; 
	if($cached) 
	{
		if(!$parms['showarchive']) $cached .= '<div class="e-menu-link archive"><a href="'.e_PLUGIN_ABS.'blogcalendar_menu/archive.php">'.BLOGCAL_L2.'</a></div>';
		$cached = $ns->tablerender(BLOGCAL_L1.$req_year, $cached, 'news_months_menu', true);
	}
	e107::getCache()->set($cString, $cached);
}

echo $cached;
