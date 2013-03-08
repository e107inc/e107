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

$template = e107::getCoreTemplate('page','nav');

$parm = eHelper::scParams($parm);
$request = e107::getRegistry('core/pages/request');
if($request && is_array($request))
{
	switch ($request['action']) 
	{
		case 'listChapters':
			$parm['cbook'] = $request['id'];
		break;
		
		case 'listPages':
			$parm['cchapter'] = $request['id'];
		break;
		
		case 'showPage':
			$parm['cpage'] = $request['id'];
		break;
	}
}

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
$text = e107::getNav()->render($data, $template) ;

### Render
e107::getRender()->tablerender($template['caption'], $text, 'page-navigation-menu');

?>