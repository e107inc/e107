<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Related configuration module - News
 *
 *
*/

if (!defined('e107_INIT')) { exit; }



class news_related // include plugin-folder in the name.
{


	function compile($tags,$parm=array())
	{
		$items = array();

		$tag_regexp = "(^|,)(".str_replace(",", "|", $tags).")(,|$)";

		$qb = e107::getDb()->createQueryBuilder();
		$rows = $qb->select('n.*', 'nc.category_id', 'nc.category_name', 'nc.category_sef')
			->from('news', 'n')
			->leftJoin('news_category', 'nc', $qb->expr()->compareColumns('n.news_category', 'nc.category_id'))
			->where('n.news_id', '!=', (int) $parm['current'])
			->where($qb->expr()->regexp('n.news_class', e_CLASS_REGEXP))
			->where($qb->expr()->regexp('n.news_meta_keywords', $tag_regexp))
			->orderBy('n.news_datestamp', 'DESC')
			->setMaxResults((int) $parm['limit'])
			->fetchAll();

		if($rows)
		{
			foreach($rows as $row)
			{
				$thumbs = !empty($row['news_thumbnail']) ?  explode(",",$row['news_thumbnail']) : array();

				$items[] = array(
					'title'			=> varset($row['news_title']),
					'url'			=> e107::getUrl()->create('news/view/item',$row), // '{e_BASE}news.php?extend.'.$row['news_id'],
					'summary'		=> varset($row['news_summary']),
					'image'			=> varset($thumbs[0]),
					'date'			=> e107::getParser()->toDate(varset($row['news_datestamp']), 'short'),
				);
			}

			return $items;
	    }
		//elseif(ADMIN)
		//{
		//	return array(array('title'=>$query,'url'=>''));	
		//}
	}
	
}




