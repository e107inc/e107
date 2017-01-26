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
	<li><a class="e-menu-link newscats{active}" href="{NEWS_CATEGORY_URL}">{NEWS_CATEGORY_TITLE} <span class="pull-right">{NEWS_CATEGORY_NEWS_COUNT}</span></a></li>
';

//$NEWS_MENU_WRAPPER['category']['NEWS_CATEGORY_NEWS_COUNT'] = "({---})"; // Wrap brackets around the news count when value is returned.
//$NEWS_MENU_TEMPLATE['category']['separator']   = '<br />';






// months menu
$NEWS_MENU_TEMPLATE['months']['start']       = '<ul class="nav nav-list news-menu-months">';
$NEWS_MENU_TEMPLATE['months']['end']         = '</ul>';
$NEWS_MENU_TEMPLATE['months']['item']        = '
	<li><a class="e-menu-link newsmonths{active}" href="{url}">{month} <span class="badge pull-right">{count}</span></a></li>
';
//$NEWS_MENU_TEMPLATE['months']['separator']   = '<br />';






// latest menu
$NEWS_MENU_TEMPLATE['latest']['start']       = '<ul class="nav nav-list news-menu-latest">';
$NEWS_MENU_TEMPLATE['latest']['end']         = '</ul>'; // Example: $NEWS_MENU_TEMPLATE['latest']['end']  '<br />{currentTotal} from {total}';
$NEWS_MENU_TEMPLATE['latest']['item']        = '<li><a class="e-menu-link newsmonths" href="{NEWSURL}">{NEWSTITLE} {NEWSCOMMENTCOUNT}</a></li>';

$NEWS_MENU_WRAPPER['latest']['NEWSCOMMENTCOUNT']	= "({---})";





// Other News Menu. 
$NEWS_MENU_TEMPLATE['other']['caption'] 	= '';
$NEWS_MENU_TEMPLATE['other']['start']		= "
												{SETIMAGE: w=700&h=400&crop=1}"; // set the {NEWSIMAGE} dimensions. 								
$NEWS_MENU_TEMPLATE['other']['item']		= '
                    <a href="{NEWSURL}" class="thumbnail">{NEWSIMAGE: type=tag&item=1}</a>
                    <h2 class="home-post-title"><a href="{NEWSURL}">{NEWSTITLE}</a></h2>
                    <p>{NEWSBODY}</p>
                    <div class="row home-post-footer">
                        <div class="col-md-8">
                            <div class="home-post-meta">
                                <i class="fa fa-clock-o"></i> {NEWSDATE=short} 
                                <i class="fa fa-folder-open"></i> {NEWSCATEGORY}
                                <i class="fa fa-tags"></i> {NEWSTAGS}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <a href="{NEWSURL}" class="btn btn-primary btn-block">'.LAN_THEME_NEWS_1.'</a>
                        </div>
                    </div>';									
$NEWS_MENU_TEMPLATE['other']['end']			= "";








// Other News Menu. 2 

$NEWS_MENU_TEMPLATE['other2']['caption'] 	= TD_MENU_L2;
$NEWS_MENU_TEMPLATE['other2']['start'] 	= "<ul class='media-list unstyled list-unstyled othernews2-block'>{SETIMAGE: w=100&h=100&crop=1}"; // set the {NEWSIMAGE} dimensions.
$NEWS_MENU_TEMPLATE['other2']['item'] 	= "<li class='media'>
										<span class='media-object pull-left'>{NEWSTHUMBNAIL=placeholder}</span> 
										<div class='media-body'><h4>{NEWSTITLELINK}</h4>
										<p class='text-right'><a class='btn btn-primary btn-othernews2' href='{NEWSURL}'>".LAN_READ_MORE." &raquo;</a></p>
										</div>
										</li>\n";
										
$NEWS_MENU_TEMPLATE['other2']['end'] 	= "</ul>";







//$NEWS_MENU_TEMPLATE['latest']['separator']   = '<br />'; // Shouldn't be needed. 
