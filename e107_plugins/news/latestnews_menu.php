<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Latest news menu
 */
if (!defined('e107_INIT')) { exit; }


if($current = e107::getRegistry('current_news_item'))
{
	$currentID = (int) $current['news_id'];
}
else
{
	$currentID = 0;
}

$cacheString = 'nq_news_latest_menu_'.md5(serialize($parm).USERCLASS_LIST.e_LANGUAGE.$currentID);
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

	if(!empty($currentID))
	{
		$treeparm['db_where'] = 'news_id != '.$currentID;
	}

	/* Prevent data-overwrite if menu is called within news template and more news shortcodes are called after */
	$origParam = e107::getScBatch('news', TRUE)->getScVar('param');
	$origData = e107::getScBatch('news', TRUE)->getScVar('news_item');

	$cached = $ntree->loadJoinActive(vartrue($parms['category'], 0), false, $treeparm)->render($template, $parms, true);
	e107::getCache()->set($cacheString, $cached);

	e107::getScBatch('news', TRUE)->setScVar('param', $origParam);
	e107::getScBatch('news', TRUE)->setScVar('news_item', $origData);

}

echo $cached;
