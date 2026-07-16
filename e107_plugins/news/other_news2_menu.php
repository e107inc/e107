<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/news/other_news2_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

use e107\Database\SqlFragment;

if (!defined('e107_INIT')) { exit; }


// Load Data
if($cacheData = e107::getCache()->retrieve("nq_othernews2"))
{
	echo $cacheData;
	return;
}


require_once(e_HANDLER."news_class.php");
unset($text);
global $OTHERNEWS2_STYLE;
$ix = new news;

if(!empty($parm))
{
	if(is_string($parm))
	{
		parse_str($parm, $parms);
	}
	else
	{
		$parms = $parm;
	}
}

if(!$OTHERNEWS2_STYLE) 
{
	if(THEME_LEGACY !== true) // v2.x
	{
		if(!defined("OTHERNEWS_COLS"))
		{
			define("OTHERNEWS_COLS",false);
		}

		$template = e107::getTemplate('news', 'news_menu', 'other2', true, true);
		$OTHERNEWS2_STYLE = $template['item'];

		if(!empty($parms['caption']))
		{
			if(isset($parms['caption'][e_LANGUAGE]))
			{
				$template['caption'] =  e107::getParser()->toHTML($parms['caption'][e_LANGUAGE], true,'TITLE');
			}
			else
			{
				$template['caption'] =  e107::getParser()->toHTML($parms['caption'], true,'TITLE');
			}


		}
	}
	else //v1.x
	{
		$template['start'] = '';
		$template['end'] = '';	


		if(!empty($parms['caption']))
		{
			$template['caption'] =  e107::getParser()->toHTML($parms['caption'],true,'TITLE');
		}
		else
		{
			$template['caption'] = TD_MENU_L2;
		}
		
		$OTHERNEWS2_STYLE = "
		<table class='forumheader3' cellpadding='0' cellspacing='0' style='width:100%'>
		<tr><td class='caption2' colspan='2' style='padding:3px;text-decoration:none;'>
		{NEWSCATICON}
		{NEWSCATEGORY}
		</td></tr>
		<tr><td  style='padding:3px;vertical-align:top'>
		{NEWSTITLELINK}
		<br />
		{NEWSSUMMARY}
		</td>
		<td style='padding:3px'>
		{NEWSTHUMBNAIL}
		</td>
		</tr>
		</table>
		";
	}
}


$template['caption'] .= e107::getForm()->instantEditButton(e_ADMIN_ABS."newspost.php?searchquery=&filter_options=news_render_type__3", 'H');








if(!defined("OTHERNEWS2_LIMIT")){
//	define("OTHERNEWS2_LIMIT",5);
}

if(!defined("OTHERNEWS2_ITEMLINK")){
	// define("OTHERNEWS2_ITEMLINK","");
}
if(!defined("OTHERNEWS2_CATLINK")){
//	define("OTHERNEWS2_CATLINK","");
}
if(!defined("OTHERNEWS2_CATICON")){
	define("OTHERNEWS2_CATICON","border:0px");
}
if(!defined("OTHERNEWS2_THUMB")){
	define("OTHERNEWS2_THUMB","border:0px");
}

if(!defined("OTHERNEWS2_COLS")){
	define("OTHERNEWS2_COLS",false);
}

if(!defined("OTHERNEWS2_CELL")){
	// define("OTHERNEWS2_CELL","");
}

if(!defined("OTHERNEWS2_SPACING")){
	// define("OTHERNEWS2_SPACING","0");
}

if(!isset($param))
{
	$param = array();
}

$param['itemlink'] 		= defset('OTHERNEWS2_ITEMLINK','');
$param['thumbnail'] 	= OTHERNEWS2_THUMB;
$param['catlink'] 		= defset('OTHERNEWS2_CATLINK','');
$param['caticon'] 		= OTHERNEWS2_CATICON;
$param['template_key']  = 'news_menu/other2/item';

$style 					= defset('OTHERNEWS2_CELL','padding:0px;vertical-align:top');
$nbr_cols 				= defset('OTHERNEWS2_COLS', 1);

$_t = time();
$qb = e107::getDb()->createQueryBuilder();
$qb->addSelect(SqlFragment::raw('n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon'))
	->from('news', 'n')
	->leftJoin('user', 'u', $qb->expr()->compareColumns('n.news_author', 'u.user_id'))
	->leftJoin('news_category', 'nc', $qb->expr()->compareColumns('n.news_category', 'nc.category_id'))
	->whereIn('n.news_class', array_map('intval', explode(',', USERCLASS_LIST)))
	->where('n.news_start', '<', $_t)
	->where($qb->expr()->anyOf($qb->expr()->eq('n.news_end', 0), $qb->expr()->gt('n.news_end', $_t)))
	->where($qb->expr()->findInSet('n.news_render_type', 3))
	->orderBy('n.news_datestamp', 'DESC')
	->setFirstResult(0)->setMaxResults((int) defset('OTHERNEWS2_LIMIT', 5));
$otherNewsRows = $qb->fetchAll();

if ($otherNewsRows)
{
	$text = $tp->parseTemplate($template['start'],true);
	
	if(OTHERNEWS2_COLS !== false)
	{			
		$text = "<table style='width:100%' cellpadding='0' cellspacing='".defset('OTHERNEWS2_SPACING',0)."'>";
		$t = 0;
		$wid = floor(100/$nbr_cols);

		foreach ($otherNewsRows as $row)
		{
			$text .= ($t % $nbr_cols == 0) ? "<tr>" : "";
			$text .= "\n<td style='$style ; width:$wid%;'>\n";

			$text .= $ix->render_newsitem($row, 'return', '', $OTHERNEWS2_STYLE, $param);
	
			$text .= "\n</td>\n";
			if (($t+1) % $nbr_cols == 0) {
				$text .= "</tr>";
				$t++;
			}
			else {
				$t++;
			}
		}
	
		while ($t % $nbr_cols != 0)
		{
			$text .= "<td style='width:$wid'>&nbsp;</td>\n";
			$text .= (($t+1) % $nbr_cols == 0) ? "</tr>" : "";
			$t++;
	
		}
		$text .= "</table>";
	}
	else // perfect for divs. 
	{
		foreach ($otherNewsRows as $row)
		{
			$text .= $ix->render_newsitem($row, 'return', '', $OTHERNEWS2_STYLE, $param);
		}
	}
		
	$text .= $tp->parseTemplate($template['end'], true);

	// Save Data
	ob_start();

	e107::getRender()->tablerender($template['caption'], $text, 'other_news2');

	$cache_data = ob_get_flush();
	e107::getCache()->set("nq_othernews2", $cache_data);

}

