<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Latest news menu
 */
if (!defined('e107_INIT')) { exit; }

$cacheString = 'nq_news_latest_menu_'.md5(serialize($parm).USERCLASS_LIST.e_LANGUAGE);
$cached = e107::getCache()->retrieve($cacheString);
if(false === $cached)
{
	e107::plugLan('news');

	if(is_string($parm))
	{
		parse_str($parm, $parms);
	}
	else
	{
		$parms = $parm;
	}

	if(isset($parms['caption'][e_LANGUAGE]))
	{
		$parms['caption'] = $parms['caption'][e_LANGUAGE];
	}

	/** @var e_news_tree $ntree */
	$ntree = e107::getObject('e_news_tree', null, e_HANDLER.'news_class.php');

	if(empty($parms['tmpl']))
	{
		$parms['tmpl'] = 'news_menu';
	}

	if(empty($parms['tmpl_key']))
	{
		$parms['tmpl_key'] = 'latest';
	}

	$template = e107::getTemplate('news', $parms['tmpl'], $parms['tmpl_key'], true, true);

	$treeparm = array();
	if(vartrue($parms['count'])) $treeparm['db_limit'] = '0, '.intval($parms['count']);
	if(vartrue($parms['order'])) $treeparm['db_order'] = e107::getParser()->toDb($parms['order']);
	$parms['return'] = true;
	
	$cached = $ntree->loadJoinActive(vartrue($parms['category'], 0), false, $treeparm)->render($template, $parms, true);
	e107::getCache()->set($cacheString, $cached);
}

echo $cached;
