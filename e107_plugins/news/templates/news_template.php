<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News default templates
 */

if (!defined('e107_INIT'))  exit;

global $sc_style;

###### Default list item (temporary) - TODO rewrite news, template standards ######
//$NEWS_MENU_TEMPLATE['list']['start']       = '<ul class="nav nav-list news-menu-months">';
//$NEWS_MENU_TEMPLATE['list']['end']         = '</ul>';
$NEWS_TEMPLATE['list']['item'] = '
	<div class="news-list-item">
		<h2>{NEWSTITLE}</h2>
		<div class="item-date">{NEWSDATE=short}</div>
		<div class="item-author">{NEWSAUTHOR}</div>
 
		<div class="item-body">
			{NEWSIMAGE}
			{NEWSBODY}
			{EXTENDED}
		</div>
		<div class="item-options">
			{NEWSCOMMENTS} {EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
		</div>
	</div>
';
//$NEWS_MENU_TEMPLATE['list']['separator']   = '<br />';

###### Default view item (temporary) - TODO rewrite news, template standards ######
//$NEWS_MENU_TEMPLATE['view']['start']       = '<ul class="nav nav-list news-menu-months">';
//$NEWS_MENU_TEMPLATE['view']['end']         = '</ul>';
$NEWS_TEMPLATE['view']['item'] = '
	<div class="news-view-item">
		<h2>{NEWSTITLE}</h2>
		<div class="item-date">{NEWSDATE=short}</div>
		<div class="item-author">{NEWSAUTHOR}</div>
 
		<div class="item-body">
			{NEWSIMAGE}
			{NEWSBODY}
			{EXTENDED}
		</div>
		<div class="item-options">
			{NEWSCOMMENTS} {EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
		</div>
	</div>
';
//$NEWS_MENU_TEMPLATE['view']['separator']   = '<br />';