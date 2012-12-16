<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News menus templates
 */

if (!defined('e107_INIT'))  exit;

global $sc_style;

$sc_style['NEWS_CATEGORY_NEWS_COUNT']['pre']  = '(';
$sc_style['NEWS_CATEGORY_NEWS_COUNT']['post'] = ')';

// category menu
$NEWS_MENU_TEMPLATE['category']['start']       = '<ul class="nav nav-list news-menu-category">';
$NEWS_MENU_TEMPLATE['category']['end']         = '</ul>';
$NEWS_MENU_TEMPLATE['category']['item']        = '
	<li><a class="e-menu-link newscats{active}" href="{NEWS_CATEGORY_URL}">{NEWS_CATEGORY_TITLE} {NEWS_CATEGORY_NEWS_COUNT}</a></li>
';
$NEWS_MENU_TEMPLATE['category']['separator']   = '<br />';

// months menu
$NEWS_MENU_TEMPLATE['months']['start']       = '<ul class="nav nav-list news-menu-months">';
$NEWS_MENU_TEMPLATE['months']['end']         = '</ul>';
$NEWS_MENU_TEMPLATE['months']['item']        = '
	<li><a class="e-menu-link newsmonths{active}" href="{url}">{month} ({count})</a></li>
';
$NEWS_MENU_TEMPLATE['months']['separator']   = '<br />';

// latest menu
$NEWS_MENU_TEMPLATE['latest']['start']       = '<ul class="nav nav-list news-menu-latest">';
// Example
//$NEWS_MENU_TEMPLATE['latest']['end']         = '<br />{currentTotal} from {total}';
$NEWS_MENU_TEMPLATE['latest']['end']         = '</ul>';
$NEWS_MENU_TEMPLATE['latest']['item']        = '
	<li><a class="e-menu-link newsmonths" href="{NEWSURL}">{NEWSTITLE} ({NEWSCOMMENTCOUNT})</a></li>
';
$NEWS_MENU_TEMPLATE['latest']['separator']   = '<br />'; // Shouldn't be needed. 