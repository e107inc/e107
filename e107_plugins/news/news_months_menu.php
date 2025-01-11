<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News by month menu
 */

if (!defined('e107_INIT')) { exit; }

$cString = 'nq_news_months_menu_'.md5(serialize($parm).USERCLASS_LIST.e_LANGUAGE);
$cached = e107::getCache()->retrieve($cString);

if(!empty($parm))
{
	if(is_string($parm))
	{
		parse_str($parm, $parms);
	}
	else
	{
		$parms = $parm;
	}

}


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
	
	if(!isset($parms['showarchive']))
	{
		$parms['showarchive'] = 0;
	}

		
//	e107::lan('blogcalendar_menu', e_LANGUAGE); // FIXME decide on language file structure (#743)
	e107::includeLan(e_PLUGIN.'blogcalendar_menu/languages/'.e_LANGUAGE.'.php');

	$tp = e107::getParser();
	$sql = e107::getDb();
	
	$marray  = e107::getDate()->terms('month');
	

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
	if(e_PAGE == 'news.php' && strpos(e_QUERY, "month") !== false)
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
	
	e107::getDebug()->logTime('News months menu');
	if(!$sql->select("news", "news_id, news_datestamp", "news_class IN (".USERCLASS_LIST.") AND (FIND_IN_SET('0', news_render_type) OR FIND_IN_SET(1, news_render_type)) AND news_datestamp > ".intval($start)." AND news_datestamp < ".intval($end)." ORDER BY news_datestamp DESC"))
	{
		e107::getCache()->set($cString, '');
		return '';
	}
	while ($news = $sql->fetch())
	{	
		$xmonth = date("n", $news['news_datestamp']);
		if ((!isset($month_links[$xmonth]) || !$month_links[$xmonth]))
		{
			$xmonth_cnt[$xmonth] = 0;
			$month_links[$xmonth] = e107::getUrl()->create('news/list/month', 'id='.newsFormatDate($req_year, $xmonth));
		}
		$xmonth_cnt[$xmonth]++;
	}


	e107::getDebug()->log($month_links);

	// go over the link array and create the option fields
	$menu_text = array();
	$template = e107::getTemplate('news', 'news_menu', 'months', true, true);
	$bullet = defined('BULLET') ? THEME_ABS.'images/'.BULLET : THEME_ABS.'images/bullet2.gif';
	$vars = new e_vars(array('bullet' => $bullet));
	foreach($month_links as $index => $val) 
	{
		$vars->addData(array(
			'active' => $index == $req_month ? " active" : '',
			'url' => $val,
			'month' => $marray[$index],
			'count' => $xmonth_cnt[$index],
		));
		$menu_text[] = $tp->simpleParse($template['item'], $vars);
	}
	$cached = $template['start'].implode(varset($template['separator'],''), $menu_text).$template['end'];

	$ns->setContent('text', $cached);

	if($cached) 
	{
		if(!$parms['showarchive'])
		{
			if(isset($template['footer']))
			{
				$footer = $tp->replaceConstants($template['footer'],'abs');
				$footer = $tp->parseTemplate($footer,true);
				$ns->setUniqueId('news-months-menu')->setContent('footer', $footer);
			}
			else
			{
				$footer = '<div class="e-menu-link news-menu-archive"><a class="btn btn-default btn-secondary btn-sm" href="'.e_PLUGIN_ABS.'blogcalendar_menu/archive.php">'.BLOGCAL_L2.'</a></div>';
				$cached .= $footer;
			}

		}

		$cached = $ns->tablerender(BLOGCAL_L1." ".$req_year, $cached, 'news_months_menu', true);
		$ns->setUniqueId(null);


	}
	e107::getCache()->set($cString, $cached);
}

echo $cached;