<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


$nw = e107::getObject('e_news_tree');
$tp = e107::getParser();

$nparm = array('db_limit' => 350 );

$tmp = $nw->loadJoinActive(0, false, $nparm)->toArray();

$monthLabels = e107::getDate()->terms();

$arr = array();
foreach($tmp as $id => $val)
{
	$d = date('Y-n',$val['news_datestamp']);
	list($year,$month) = explode('-',$d);
	unset($val['news_body']);
	$arr[$year][$month][] = $val;

	// e107::getDebug()->log($val);
}





$text = "<ul class='news-archive-menu'>";

foreach($arr as $year=>$val)
{
	if($year == date('Y'))
	{
		$displayYear = 'block';
		$expandOpen = 'open';
	}
	else
	{
		$displayYear = 'none';
		$expandOpen = '';
	}

	$id = "news-archive-".$year;
	$text .= "<li>";
	$text .= "<a class='e-expandit {$expandOpen}' href='#".$id."'>".$year."</a>";
	$text .= "<ul id='".$id."' class='news-archive-menu-months' style='display:".$displayYear."'>";

		foreach($val as $month=>$items)
		{
			//$displayMonth = ($mCount === 1) ? 'display:block': 'display:none';

			$idm = "news-archive-".$year.'-'.$month;

			$text .= "<li>";
			$text .= "<a class='e-expandit' href='#".$idm."'>".$monthLabels[$month];

			if(!empty($parm['badges'])) // param only (no menu-manager config. To be replaced by template.
			{
				$num = count($items);
				$text .= "<span class='badge'>".$num."</span>";
			}

			$text .= "</a>";
			$text .= "<ul id='".$idm."' class='news-archive-menu-items' style='display:none'>";

			foreach($items as $row)
			{
				$url = e107::getUrl()->create('news/view/item', $row, array('allow' => 'news_sef,news_title,news_id,category_sef,category_name,category_id'));
				$text .= "<li><a href='".$url."'>".$tp->toHtml($row['news_title'],false,'TITLE')."</a></li>";

			}
			$text .= "</ul>";
			$text .= "</li>";

		}
	$text .= "</ul>";
	$text .= "</li>";

}
$text .= "</ul>";



$caption = !empty($parm['caption'][e_LANGUAGE]) ? $parm['caption'][e_LANGUAGE] : LAN_NEWSARCHIVE_MENU_TITLE;

e107::plugLan('news');

e107::getRender()->tablerender($caption, $text, 'news-archive-menu');

//e107::getDebug()->log($arr);

