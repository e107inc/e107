<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News menus templates
 */

if (!defined('e107_INIT'))  exit;

global $sc_style;

// $sc_style['NEWS_CATEGORY_NEWS_COUNT']['pre']  = '(';
// $sc_style['NEWS_CATEGORY_NEWS_COUNT']['post'] = ')';



// category menu
$NEWS_MENU_TEMPLATE['category']['start']       = '<ul class="nav nav-list news-menu-category">';
$NEWS_MENU_TEMPLATE['category']['end']         = '</ul>';
$NEWS_MENU_TEMPLATE['category']['item']        = '
	<li><a class="e-menu-link newscats{active}" href="{NEWS_CATEGORY_URL}">{NEWS_CATEGORY_TITLE} {NEWS_CATEGORY_NEWS_COUNT}</a></li>
';

$NEWS_MENU_WRAPPER['category']['NEWS_CATEGORY_NEWS_COUNT'] = "({---})"; // Wrap brackets around the news count when value is returned. 
//$NEWS_MENU_TEMPLATE['category']['separator']   = '<br />';






// months menu
$NEWS_MENU_TEMPLATE['months']['start']       = '<ul class="nav nav-list news-menu-months">';
$NEWS_MENU_TEMPLATE['months']['end']         = '</ul>';
$NEWS_MENU_TEMPLATE['months']['item']        = '
	<li><a class="e-menu-link newsmonths{active}" href="{url}">{month} ({count})</a></li>
';
//$NEWS_MENU_TEMPLATE['months']['separator']   = '<br />';






// latest menu
$NEWS_MENU_TEMPLATE['latest']['start']       = '<ul class="nav nav-list news-menu-latest">';
$NEWS_MENU_TEMPLATE['latest']['end']         = '</ul>'; // Example: $NEWS_MENU_TEMPLATE['latest']['end']  '<br />{currentTotal} from {total}';
$NEWS_MENU_TEMPLATE['latest']['item']        = '<li><a class="e-menu-link newsmonths" href="{NEWSURL}">{NEWSTITLE} {NEWSCOMMENTCOUNT}</a></li>';

$NEWS_MENU_WRAPPER['latest']['NEWSCOMMENTCOUNT']	= "({---})";





// Other News Menu. 
$NEWS_MENU_TEMPLATE['other']['caption'] 	= TD_MENU_L1;
$NEWS_MENU_TEMPLATE['other']['start']		= "<div id='otherNews' data-interval='false' class='carousel slide othernews-block'>
												<div class='carousel-inner'>
												{SETIMAGE: w=400&h=200&crop=1}"; // set the {NEWSIMAGE} dimensions. 								
$NEWS_MENU_TEMPLATE['other']['item']		= '<div class="item {ACTIVE}">
												{NEWSTHUMBNAIL=placeholder}
              									<h3>{NEWSTITLE}</h3>
              									<p>{NEWSSUMMARY}</p>
              									<p class="text-right"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">'.LAN_READ_MORE.' &raquo;</a></p>
            									</div>';									
$NEWS_MENU_TEMPLATE['other']['end']			= "</div></div>";








// Other News Menu. 2 

$NEWS_MENU_TEMPLATE['other2']['caption'] 	= TD_MENU_L2;
$NEWS_MENU_TEMPLATE['other2']['start'] 	= "<ul class='media-list unstyled othernews2-block'>{SETIMAGE: w=100&h=100&crop=1}"; // set the {NEWSIMAGE} dimensions. 	
$NEWS_MENU_TEMPLATE['other2']['item'] 	= "<li class='media'>
										<span class='media-object pull-left'>{NEWSTHUMBNAIL=placeholder}</span> 
										<div class='media-body'><h5>{NEWSTITLELINK}</h5>
										</div>
										</li>\n";
										
$NEWS_MENU_TEMPLATE['other2']['end'] 	= "</ul>";







//$NEWS_MENU_TEMPLATE['latest']['separator']   = '<br />'; // Shouldn't be needed. 
