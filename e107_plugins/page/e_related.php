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

		$tag_regexp = "'(^|,)(".str_replace(",", "|", $tags).")(,|$)'";
		
		$query = "SELECT * FROM #page WHERE page_id != ".$parm['current']." AND page_class REGEXP '".e_CLASS_REGEXP."'  AND page_metakeys REGEXP ".$tag_regexp."  ORDER BY page_datestamp DESC LIMIT ".$parm['limit'];
				
		if($sql->gen($query))
		{		
			while($row = $sql->fetch())
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



