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



class page_related // replace 'e_' with 'plugin-folder_' 
{

	public function compile($tags,$parm=array())
	{
		$sql = e107::getDb();
		$items = array();

		// $tags is admin-set DB content; preserve e107's storage transform (toDB)
		// applied to the original query, then bind the assembled REGEXP pattern.
		$tags = e107::getParser()->toDB($tags);
		$tag_regexp = "(^|,)(".str_replace(",", "|", $tags).")(,|$)";

		$qb = $sql->createQueryBuilder();
		$rows = $qb->select('*')->from('page')
			->where('page_id', '!=', (int) $parm['current'])
			->where($qb->expr()->regexp('page_class', e_CLASS_REGEXP))
			->where($qb->expr()->regexp('page_metakeys', $tag_regexp))
			->orderBy('page_datestamp', 'DESC')
			->setMaxResults((int) $parm['limit'])
			->fetchAll();

		if($rows)
		{
			foreach($rows as $row)
			{
				$row    = pageHelper::addSefFields($row);
				
				$id = $row['page_chapter'];
				$title = (vartrue($this->chapterName[$id])) ? $this->chapterName[$id]." | ".$row['page_title'] : $row['page_title'];
				$route = !empty($row['page_chapter']) ? 'page/view/index' : 'page/view/other';

				$items[] = array(
					'title'			=> $title,
					'url'			=> e107::getUrl()->create($route, $row), // '{e_BASE}news.php?extend.'.$row['news_id'],
					'summary'		=> $row['page_metadscr'],
					'image'			=> $row['menu_image']
				);
			}
			
			return $items;
	    }

	}
	
}



