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




$parm = eHelper::scParams($parm);

$tmpl = e107::getCoreTemplate('chapter','nav',true,true); // always merge and allow override

$template = $tmpl['showPage'];

$pg = new page_shortcodes;
$text = $pg->sc_page_navigation($parm);



/**
 * Expandable menu support. 
 * @see jquery.page.navigation.js . activate with expandable=1 in the page-navigation menu. 
 * For best results include: e107::css('page', 'css/page.navigation.css', 'jquery'); in theme.php 
 *//*
if($expandable) 
{
	e107::js('page','js/jquery.page.navigation.js','jquery');
	$template['caption'] .= "<span class='btn-group pull-right'><a class='btn btn-default btn-secondary btn-xs btn-mini' id='page-nav-expand'>+</a><a class='btn btn-default btn-secondary btn-xs btn-mini' id='page-nav-collapse'>-</a></span>";
}*/


### Render
e107::getRender()->tablerender($template['caption'], $text, 'page-navigation-menu');

