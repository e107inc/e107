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
$NEWS_MENU_TEMPLATE['category']['start']       = '';
$NEWS_MENU_TEMPLATE['category']['end']         = '';
$NEWS_MENU_TEMPLATE['category']['item']        = '
	<img src="{bullet}" alt="bullet" class="icon" /> <a class="e-menu-link newscats{active}" href="{NEWS_CATEGORY_URL}">{NEWS_CATEGORY_TITLE} {NEWS_CATEGORY_NEWS_COUNT}</a>
';
$NEWS_MENU_TEMPLATE['category']['separator']   = '<br />';

// months menu
$NEWS_MENU_TEMPLATE['months']['start']       = '';
$NEWS_MENU_TEMPLATE['months']['end']         = '';
$NEWS_MENU_TEMPLATE['months']['item']        = '
	<img src="{bullet}" alt="bullet" class="icon" /> <a class="e-menu-link newsmonths{active}" href="{url}">{month} ({count})</a>
';
$NEWS_MENU_TEMPLATE['months']['separator']   = '<br />';

// latest menu
$NEWS_MENU_TEMPLATE['latest']['start']       = '';
// Example
//$NEWS_MENU_TEMPLATE['latest']['end']         = '<br />{currentTotal} from {total}';
$NEWS_MENU_TEMPLATE['latest']['end']         = '';
$NEWS_MENU_TEMPLATE['latest']['item']        = '
	<img src="{bullet}" alt="bullet" class="icon" /> <a class="e-menu-link newsmonths" href="{NEWSURL}">{NEWSTITLE} ({NEWSCOMMENTCOUNT})</a>
';
$NEWS_MENU_TEMPLATE['latest']['separator']   = '<br />';