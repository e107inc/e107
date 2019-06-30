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
		$sql = e107::getDb();
		$items = array();
			
		$tag_regexp = "'(^|,)(".str_replace(",", "|", $tags).")(,|$)'";
		
		$query = "SELECT * FROM #news WHERE news_id != ".$parm['current']." AND news_class REGEXP '".e_CLASS_REGEXP."'  AND news_meta_keywords REGEXP ".$tag_regexp."  ORDER BY news_datestamp DESC LIMIT ".$parm['limit'];
			
		if($sql->gen($query))
		{		
			while($row = $sql->fetch())
			{
				$thumbs = !empty($row['news_thumbnail']) ?  explode(",",$row['news_thumbnail']) : array();

				$items[] = array(
					'title'			=> varset($row['news_title']),
					'url'			=> e107::getUrl()->create('news/view/item',$row), // '{e_BASE}news.php?extend.'.$row['news_id'],
					'summary'		=> varset($row['news_summary']),
					'image'			=> $thumbs[0],
					'date'			=> e107::getParser()->toDate(varset($row['news_datestamp']), 'short'),
				);
			}
			
			return $items;
	    }
		elseif(ADMIN)
		{
		//	return array(array('title'=>$query,'url'=>''));	
		}
	}
	
}




