<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Latest news menu
 */
if (!defined('e107_INIT')) { exit; }

$cacheString = 'nq_news_latest_menu_'.md5($parm);
$cached = e107::getCache()->retrieve($cacheString);
if(false === $cached)
{
	e107::plugLan('news');

	parse_str($parm, $parms);
	$ntree = e107::getObject('e_news_tree', null, e_HANDLER.'news_class.php');

	$template = e107::getTemplate('news', vartrue($parms['tmpl'], 'news_menu'), vartrue($parms['tmpl_key'], 'latest'));

	$treeparm = array();
	if(vartrue($parms['count'])) $treeparm['db_limit'] = '0, '.intval($parms['count']);
	if(vartrue($parms['order'])) $treeparm['db_order'] = e107::getParser()->toDb($parms['order']);
	$parms['return'] = true;
	
	$cached = $ntree->loadJoinActive(vartrue($parms['category'], 0), false, $treeparm)->render($template, $parms, true);
	e107::getCache()->set($cacheString, $cached);
}

echo $cached;
