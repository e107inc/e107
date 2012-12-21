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
$NEWS_TEMPLATE['list']['item']        = '
<div class="newsbox">
	<div class="leftbox">
		<div class="leftbox_title_bg">
			<div class="leftbox_title">
				{NEWSTITLE}
			</div>
		</div>
		<div class="meta">
			<div class="author">
				{NEWSDATE=short}&nbsp;&nbsp;&nbsp;{NEWSAUTHOR}
			</div>
		</div>
	  <div class="newsbbody">
			{NEWSIMAGE}
			{NEWSBODY} {EXTENDED}
		</div>
		<div class="clear"></div>
		<div class="metabottom">
			<div class="metaicons">
    		{EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
			</div>
			{NEWSCOMMENTS}
	  </div>
	</div>
</div>
';
//$NEWS_MENU_TEMPLATE['list']['separator']   = '<br />';

###### Default view item (temporary) - TODO rewrite news, template standards ######
//$NEWS_MENU_TEMPLATE['view']['start']       = '<ul class="nav nav-list news-menu-months">';
//$NEWS_MENU_TEMPLATE['view']['end']         = '</ul>';
$NEWS_TEMPLATE['view']['item']        = '
<div class="newsbox">
	<div class="leftbox">
		<div class="leftbox_title_bg">
			<div class="leftbox_title">
				{NEWSTITLE}
			</div>
		</div>
		<div class="meta">
			<div class="author mediumtext">
				{NEWSDATE=short}&nbsp;&nbsp;&nbsp;{NEWSAUTHOR}
			</div>
		</div>
	  <div class="newsbbody">
			{NEWSIMAGE}
			{NEWSBODY} {EXTENDED}
		</div>
		<div class="clear"></div>
		<div class="metabottom">
			<div class="metaicons">
    		{EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
			</div>
   		{NEWSCOMMENTS}
	  </div>
	</div>
</div>
';
//$NEWS_MENU_TEMPLATE['view']['separator']   = '<br />';