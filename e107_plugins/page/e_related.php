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
	private $chapterSef = array();
	private $chapterParent = array();
	private $chapterName = array();
	
	function __construct()
	{
		$sql = e107::getDb();
		
		$books = $sql->retrieve("SELECT chapter_id,chapter_sef,chapter_parent,chapter_name FROM #page_chapters ORDER BY chapter_id ASC" , true);
				
		foreach($books as $row)
		{
			$id = $row['chapter_id'];
			$this->chapterSef[$id] = $row['chapter_sef'];
			$this->chapterParent[$id] = $row['chapter_parent'];
			$this->chapterName[$id] = $row['chapter_name'];
		}	

	}
	
	private function getSef($chapter)
	{
		return vartrue($this->chapterSef[$chapter],'--sef-not-assigned--');		
	}
	
	private function getParent($chapter)
	{
		return varset($this->chapterParent[$chapter], false);			
	}
	

	function compile($tags,$parm=array()) 
	{
		$sql = e107::getDb();
		$items = array();
		
		
		$tag_regexp = "'(^|,)(".str_replace(",", "|", $tags).")(,|$)'";
		
		$query = "SELECT * FROM #page WHERE page_id != ".$parm['current']." AND page_class REGEXP '".e_CLASS_REGEXP."'  AND page_metakeys REGEXP ".$tag_regexp."  ORDER BY page_datestamp DESC LIMIT ".$parm['limit'];
				
		if($sql->gen($query))
		{		
			while($row = $sql->fetch())
			{
				$row['chapter_sef'] = $this->getSef($row['page_chapter']);
				$book 				= $this->getParent($row['page_chapter']);
				$row['book_sef']	= $this->getSef($book); 
				
				$id = $row['page_chapter'];
				$title = (vartrue($this->chapterName[$id])) ? $this->chapterName[$id]." | ".$row['page_title'] : $row['page_title'];
				
				$items[] = array(
					'title'			=> $title,
					'url'			=> e107::getUrl()->create('page/view/index',$row), // '{e_BASE}news.php?extend.'.$row['news_id'],
					'summary'		=> $row['page_metadscr'],
					'image'			=> $row['menu_image']
				);
			}
			
			return $items;
	    }
		else
		{
			// return array(array('title'=>$query,'url'=>''));	
		}
	}
	
}



?>