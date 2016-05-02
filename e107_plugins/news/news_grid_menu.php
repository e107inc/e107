<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Latest news menu
 */
if (!defined('e107_INIT')) { exit; }

$cacheString = 'nq_news_grid_menu_'.md5(serialize($parm));
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

	$ntree          = e107::getObject('e_news_tree', null, e_HANDLER.'news_class.php');
	$template       = e107::getTemplate('news', 'news_menu', 'grid');
	$gridSize       = vartrue($parms['layout'],'col-md-4');

	$parmSrch       = array(
						'{NEWSGRID}',
						'_titleLimit_',
						'_summaryLimit_'
					);

	$parmReplace    = array(
						$gridSize,
						vartrue($parms['titleLimit'],0),
						vartrue($parms['summaryLimit'],0)
					);

	$template = str_replace($parmSrch , $parmReplace, $template);

	$render = (empty($parms['caption'])) ? false: true;

	$parms['tmpl']      = 'news_menu';
	$parms['tmpl_key']  = 'grid';

	if(empty($parms['count']))
	{
		$parms['count'] = 3;
	}

	$parms['order']     = 'n.news_datestamp DESC';


	$treeparm = array();
	if(vartrue($parms['count'])) $treeparm['db_limit'] = '0, '.intval($parms['count']);
	if(vartrue($parms['order'])) $treeparm['db_order'] = e107::getParser()->toDb($parms['order']);
	$parms['return'] = true;

	if(varset($parms['source']) == 'template')
	{
		$treeparm['db_where']     = 'FIND_IN_SET(6, n.news_render_type)';
	}

	if(varset($parms['source']) == 'sticky')
	{
		$treeparm['db_where']     = 'n.news_sticky=1';
	}

	$cached = $ntree->loadJoinActive(vartrue($parms['category'], 0), false, $treeparm)->render($template, $parms, $render);
	e107::getCache()->set($cacheString, $cached);
}

echo $cached;
