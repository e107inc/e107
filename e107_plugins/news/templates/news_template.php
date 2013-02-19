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
	<div class="list-item">
		<h2>{NEWSURLTITLE}</h2>
		<div class="category">in {NEWSCATEGORY}</div>
		<div class="date">{NEWSDATE=short}</div>
		<div class="author">{NEWSAUTHOR}</div>
 		
		<div class="body">
			{NEWSIMAGE}
			{NEWSBODY}
			{EXTENDED}
		</div>
		<div class="options">
			{NEWSCOMMENTS} {EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
		</div>
	</div>
';
//$NEWS_MENU_TEMPLATE['list']['separator']   = '<br />';

###### Default view item (temporary) - TODO rewrite news, template standards ######
//$NEWS_MENU_TEMPLATE['view']['start']       = '<ul class="nav nav-list news-menu-months">';
//$NEWS_MENU_TEMPLATE['view']['end']         = '</ul>';
$NEWS_TEMPLATE['view']['item'] = '
	<div class="view-item">
		<h2>{NEWSTITLE}</h2>
		<div class="category">in {NEWSCATEGORY}</div>
		<div class="date">{NEWSDATE=short}</div>
		<div class="author">{NEWSAUTHOR}</div>

		<div class="body">
			{NEWSIMAGE}
			{NEWSBODY}
			{EXTENDED}
		</div>
		<div class="options">
			{NEWSCOMMENTS} {EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
		</div>
	</div>
';
//$NEWS_MENU_TEMPLATE['view']['separator']   = '<br />';


###### news_categories.sc (temporary) - TODO rewrite news, template standards ######
$NEWS_TEMPLATE['category']['body'] = '
	<div style="padding:5px"><div style="border-bottom:1px inset black; padding-bottom:1px;margin-bottom:5px">
	{NEWSCATICON}&nbsp;{NEWSCATEGORY}
	</div>
	{NEWSCAT_ITEM}
	</div>
';

$NEWS_TEMPLATE['category']['item'] = '
	<div style="width:100%;padding-bottom:2px">
	<table style="width:100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td style="width:2px;vertical-align:top">&#8226;
	</td>
	<td style="text-align:left;vertical-align:top;padding-left:3px">
	{NEWSTITLELINK}
	<br />
	</td></tr>
	</table>
	</div>
';