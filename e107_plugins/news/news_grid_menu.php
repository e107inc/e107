<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Latest news menu
 */
if (!defined('e107_INIT')) { exit; }
/**
 * News Grid Menu
 *
 * @param string    $parm['caption']        text or constant
 * @param integer   $parm['titleLimit']     number of chars fo news title
 * @param integer   $parm['summaryLimit']   number of chars for new summary
 * @param string    $parm['source']         latest (latest news items) | sticky (news items) | template (assigned to news-grid layout)
 * @param integer   $parm['order']          n.news_datestamp DESC
 * @param integer   $parm['limit']          10
 * @param string   $parm['template']        default | or any key as defined in news_grid_template.php
 *
 * @example hard-coded {MENU: path=news/news_grid&limit=6&source=latest&featured=2&template=other}
 * @example admin assigned - Add via Media-Manager and then configure.
 */
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

		e107::getDebug()->log($parms);
	}

	if(isset($parms['caption'][e_LANGUAGE]))
	{
		$parms['caption'] = $parms['caption'][e_LANGUAGE];
	}

	if(!empty($parms['caption']) && defined($parms['caption']))
	{
		$parms['caption'] = constant($parms['caption']);
	}


	$ntree          = e107::getObject('e_news_tree');

	if($legacyTemplate  = e107::getTemplate('news', 'news_menu', 'grid')) // BC
	{
		$template = $legacyTemplate;
		$parms['tmpl']      = 'news_menu';
		$parms['tmpl_key']  = 'grid';
	}
	else // New in v2.1.5
	{
		$tmpl = !empty($parms['template']) ? $parms['template'] : 'default';
		$template = e107::getTemplate('news', 'news_grid', $tmpl);
		$parms['tmpl']      = 'news_grid';
		$parms['tmpl_key']  = $tmpl;

	}

	$gridSize       = vartrue($parms['layout'],'col-md-4');

	$parmSrch       = array(
						'{NEWSGRID}',
						'_titleLimit_',
						'_summaryLimit_'
					);

	$parmReplace    = array(
						$gridSize,
						vartrue($parms['titleLimit'], 0),
						vartrue($parms['summaryLimit'], 0)
					);

	$template = str_replace($parmSrch , $parmReplace, $template);

	$render = (empty($parms['caption'])) ? false: true;



	if(empty($parms['count']))
	{
		$parms['count'] = 3;
	}

	$parms['order']     = 'n.news_datestamp DESC';


	$treeparm = array();

	if(!empty($parms['count']))
	{
		 $treeparm['db_limit'] = '0, '.intval($parms['count']);
	}

	if(!empty($parms['limit']))
	{
		$treeparm['db_limit'] = '0, '.intval($parms['limit']);
	}

	if(!empty($parms['order']))
	{
		$treeparm['db_order'] = e107::getParser()->toDb($parms['order']);
	}

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
