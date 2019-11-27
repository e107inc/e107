<?php
	/**
	 * Copyright (C) 2008-2016 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
	 *
	 * Carousel Menu
	 */
	if(!defined('e107_INIT'))
	{
		exit;
	}


	if(is_string($parm))
	{
		parse_str($parm, $parms);
	}
	else
	{
		$parms = $parm;
	}

	if(isset($parms['caption'][e_LANGUAGE]))
	{
		$parms['caption'] = $parms['caption'][e_LANGUAGE];
	}

	$limit = vartrue($parms['count'], 5);
	$tp = e107::getParser();
	$template = e107::getTemplate('news', 'news_menu', 'carousel', true, true);

	$nobody_regexp = "'(^|,)(" . str_replace(",", "|", e_UC_NOBODY) . ")(,|$)'";

// e107::getDebug()->log("News Carousel Menu ".print_a($parms,true));
	/*
	$query = "
			SELECT n.*, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
			nc.category_meta_keywords, nc.category_meta_description
			FROM #news AS n
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") AND n.news_start < ".time()."
			AND (n.news_end=0 || n.news_end>".time().") ";

		if(!empty($parms['category']))
		{
				$query .= " AND n.news_category = ".intval($parms['category']);
		}

		if(vartrue($parms['source']) == 'assigned')
		{
			$query .= " AND FIND_IN_SET(5,n.news_render_type) ";
		}

		if(vartrue($parms['source']) == 'sticky')
		{
			$query .= " AND n.news_sticky = 1 ";
		}


		$query .= " AND n.news_thumbnail != '' ";

	$query .= "
			ORDER BY n.news_sticky DESC, n.news_datestamp DESC
			LIMIT ".$limit;
	*/


	$news = e107::getObject('e_news_tree');  // get news class.

	$cat = !empty($parms['category']) ? intval($parms['category']) : null;

	$opts = array(
		'db_order' => 'n.news_sticky DESC, n.news_datestamp DESC',
		'db_limit' => $limit, // default is 10
		'db_where' => " 1 "
	);

	if(vartrue($parms['source']) === 'assigned')
	{
		$opts['db_where'] = "FIND_IN_SET(5,n.news_render_type) ";
	}

	if(vartrue($parms['source']) === 'sticky')
	{
		$opts['db_where'] = "n.news_sticky = 1 ";
	}

	$opts['db_where'] .= " AND n.news_thumbnail != '' ";

	$data = $news->loadJoinActive($cat, false, $opts)->toArray();

//	$data = $sql->retrieve($query,true);


	if(empty($data))
	{
		e107::getMessage()->addDebug("No News items found with  'carousel' as the template ")->render();

		return;
	}

	$count = 0;

// $tp->setThumbSize(800,0);

	$sc = e107::getScBatch('news');
	$text = '';
	$nav = array();

	foreach($data as $row)
	{

		$sc->setScVar('news_item', $row);

		$vars = array(
			'{ACTIVE}' => ($count == 0) ? 'active' : '',
			'{COUNT}'  => $count,
		);

		$parsed = str_replace(array_keys($vars), $vars, $template['item']);

		$navTemplate = str_replace(array_keys($vars), $vars, $template['nav']);
		$nav[] = $tp->parseTemplate($navTemplate, true, $sc);

		$parsed = $tp->parseTemplate($parsed, true, $sc);
		$text .= $parsed;

		$count++;
	}

	
	$header = str_replace("{NAV}", implode("\n", $nav), $template['start']);

	$footer = str_replace("{NAV}", implode("\n", $nav), $template['end']);


	if(!empty($parms['caption']))
	{
		e107::getRender()->tablerender($parms['caption'], ($header . $text . $footer), 'news-carousel'); //TODO Tablerender().
	}
	else
	{
		echo $header . $text . $footer;
	}


	e107::js('footer-inline', "
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


