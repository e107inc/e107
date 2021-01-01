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

$template = e107::getTemplate('news', 'news_menu', 'archive',true, true);
$text = '';

if(ADMIN && empty($template))
{
	$text = "Missing Template. Check that your theme's news_menu_template.php file contains an 'archive' template. ";
}
  
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
 

  $var = array('EXPANDOPEN' => $expandOpen,
               'YEAR_ID' => $id,
               'YEAR_NAME' => $year,
               'YEAR_DISPLAY' => $displayYear
 
   );
 
  $text .=  $tp->simpleParse($template['year_start'], $var);
 
		foreach($val as $month=>$items)
		{
			//$displayMonth = ($mCount === 1) ? 'display:block': 'display:none';

			$idm = "news-archive-".$year.'-'.$month;

      $var = array('MONTH_ID'   => $idm,
                   'MONTH_NAME' => $monthLabels[$month],
                   'MONTH_COUNT'=> count($items),
      );
         
			$text .=  $tp->simpleParse($template['month_start'], $var);
 
      /*
			if(!empty($parm['badges'])) // param only (no menu-manager config. To be replaced by template.
			{
				$num = count($items);
				$text .= "<span class='badge'>".$num."</span>";
			} */
 

			foreach($items as $row)
			{
				$url = e107::getUrl()->create('news/view/item', $row, array('allow' => 'news_sef,news_title,news_id,category_sef,category_name,category_id'));
		        $var = array('ITEM_URL'   => $url,
		                     'ITEM_TITLE' => $tp->toHTML($row['news_title'],false,'TITLE'),
		        );
		        $text .=  $tp->simpleParse($template['item'], $var);
			}
			$text .= $template['month_end'];
		}
 
	$text .= $template['year_end'];

}

$start =  $template['start'];   
$end = $template['end']; ;

e107::plugLan('news');

$caption = !empty($parm['caption'][e_LANGUAGE]) ? $parm['caption'][e_LANGUAGE] : LAN_NEWSARCHIVE_MENU_TITLE;

e107::getRender()->tablerender($caption, $start.$text.$end, 'news-archive-menu');


//e107::getDebug()->log($arr);
