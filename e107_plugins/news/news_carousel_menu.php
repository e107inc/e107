<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * News Carousel Menu
 */
if (!defined('e107_INIT')) { exit; }

$tp = e107::getParser();

e107::js('footer-inline',"
	clickEvent = false;
	$('#news-carousel').on('click', '#news-carousel-nav a', function() {
			clickEvent = true;
			$('#news-carousel-nav li').removeClass('active');
			$(this).parent().addClass('active');		
	}).on('slid.bs.carousel', function(e) {
		if(!clickEvent) {	
			var count = $('#news-carousel-nav').children().length -1;
			var current = $('#news-carousel-nav li.active');
			current.removeClass('active').next().addClass('active');
			var id = parseInt(current.data('slide-to'));
			if(count == id) {
				$('#news-carousel-nav li').first().addClass('active');	
			}
		}
		clickEvent = false;
	});"
);


	$NEWS_MENU_TEMPLATE['carousel']['start'] = '
    <div id="news-carousel" class="carousel slide" data-ride="carousel">
    	<div class="row">
      <!-- Wrapper for slides -->
      <div id="news-carousel-images" class="col-md-8">
      <div class="carousel-inner">';

		
	$NEWS_MENU_TEMPLATE['carousel']['end'] = '
                
      </div><!-- End Carousel Inner -->
	</div>
		<div id="news-carousel-titles" class="col-md-4 ">
			<ul id="news-carousel-nav" class="nav nav-inverse nav-stacked pull-right ">{NAV}</ul>
		</div>
	</div><!-- End Carousel -->
	</div>
 ';

 
 	$NEWS_MENU_TEMPLATE['carousel']['item'] = '<!-- Start Item -->
		<div class="item {ACTIVE}">
          {NEWSIMAGE}
           <div class="carousel-caption">
            <small>{NEWSDATE}</small>
            <h1>{NEWSTITLE}</h1>
           
          </div>
        </div><!-- End Item -->';;


	$navTemplate = '<li data-target="#news-carousel" data-slide-to="{COUNT}" class="{ACTIVE}"><a href="#">{NEWSSUMMARY}</a></li>';
 
 
 
 
 
	$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";		
	
	$query = "
		SELECT n.*, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
		nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") AND n.news_start < ".time()."
		AND (n.news_end=0 || n.news_end>".time().") AND FIND_IN_SET(5,n.news_render_type)
		ORDER BY n.news_sticky DESC, n.news_datestamp DESC
		LIMIT 5";


		
$data = $sql->retrieve($query,true);

if(count($data) < 1)
{
	e107::getMessage()->addDebug( "No News items found with  'carousel' as the template ")->render();
	return;
}

$count = 0;

 $tp->setThumbSize(800,0);

foreach($data as $row)
{
	$tmp = explode(",",$row['news_thumbnail']); // fix for multiple
	
	if($video = $tp->toVideo($tmp[0],array('thumb'=>'tag', 'w'=>800)))
	{
		$imgTag = $video;	
	}
	else 
	{
		$img = $tp->thumbUrl($tmp[0]);
		$imgTag = '<img class="img-responsive" src="'.$img.'">';
	}
	
	$vars = array(
		'NEWSTITLE'		=> $tp->toHtml($row['news_title'],false, 'TITLE'),
		'NEWSSUMMARY'	=> vartrue($row['news_summary'],$row['news_title']),
		'NEWSDATE'		=> $tp->toDate($row['news_datestamp'],'dd MM, yyyy'),
		'ACTIVE'		=> ($count == 0) ? 'active' : '',
		'COUNT'			=> $count,
		'NEWSIMAGE'		=> '<a href="'.e107::getUrl()->create('news/view/item',$row).'">'.$imgTag.'</a>'
	);
	
	
	$text .= $tp->simpleParse($NEWS_MENU_TEMPLATE['carousel']['item'], $vars);
	
	$nav[] = $tp->simpleParse($navTemplate, $vars);

	$count++;
}
		
	$header = $NEWS_MENU_TEMPLATE['carousel']['start'];
	
	$footer = str_replace("{NAV}", implode("\n",$nav), $NEWS_MENU_TEMPLATE['carousel']['end']); 

	e107::getRender()->tablerender('',$header.$text.$footer,'news-carousel'); //TODO Tablerender(). 


