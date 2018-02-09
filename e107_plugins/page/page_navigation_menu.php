<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

if (!defined('e107_INIT')) { exit; }




//FIXME XXX - This menu should call the {PAGE_NAVIGATION} shortcode instead of duplicating its code and automatically display all links.  

$parm = eHelper::scParams($parm);

$tmpl = e107::getCoreTemplate('chapter','nav',true,true); // always merge and allow override

$template = $tmpl['showPage'];
/*
$request = e107::getRegistry('core/page/request');
if($request && is_array($request))
{
	switch ($request['action']) 
	{
		case 'listChapters':
			$parm['cbook'] = $request['id'];
			$template = $tmpl['listChapters'];
		break;
		
		case 'listPages':
			$parm['cchapter'] = $request['id'];
			$template = $tmpl['listPages'];
		break;
		
		case 'showPage':
			$parm['cpage'] = $request['id'];
		break;
	}
}

$expandable = vartrue($parm['expandable']);

if($parm) $parm = http_build_query($parm, null, '&');
else $parm = '';

### Retrieve
$links = e107::getAddon('page', 'e_sitelink');
$data = $links->pageNav($parm);
if(isset($data['title']) && !vartrue($template['noAutoTitle']))
{
	// use chapter title
	$template['caption'] = $data['title'];
	$data = $data['body'];
}

if(empty($data)) return;
$text = e107::getNav()->render($data, $template) ;*/
$pg = new page_shortcodes;
$text = $pg->sc_page_navigation($parm);



/**
 * Expandable menu support. 
 * @see jquery.page.navigation.js . activate with expandable=1 in the page-navigation menu. 
 * For best results include: e107::css('page', 'css/page.navigation.css', 'jquery'); in theme.php 
 */
if($expandable) 
{
	e107::js('page','js/jquery.page.navigation.js','jquery');
	$template['caption'] .= "<span class='btn-group pull-right'><a class='btn btn-default btn-secondary btn-xs btn-mini' id='page-nav-expand'>+</a><a class='btn btn-default btn-secondary btn-xs btn-mini' id='page-nav-collapse'>-</a></span>";
}


### Render
e107::getRender()->tablerender($template['caption'], $text, 'page-navigation-menu');

