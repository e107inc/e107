<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * Pages  e_search addon 
 */
 

if (!defined('e107_INIT')) { exit; }

// v2 e_search addon. 
// Removes the need for search_parser.php, search_advanced.php and in most cases search language files. 

class page_search extends e_search // include plugin-folder in the name.
{
	private $catList = array(); 
	
	function __construct()
	{
		$sql = e107::getDb();
		
		$books = $sql->retrieve("SELECT chapter_id,chapter_sef,chapter_parent, chapter_name, chapter_visibility FROM #page_chapters ORDER BY chapter_parent, chapter_order ASC" , true);
				
		foreach($books as $row)
		{
			$id 						= $row['chapter_id'];		
			$this->catList[$id] 		= $row;
		}	
	}



	private function getName($chapter)
	{
		return varset($this->catList[$chapter]['chapter_name'], false);			
	}	
	
	private function getSef($chapter)
	{
		return vartrue($this->catList[$chapter]['chapter_sef'],false);		
	}
	
	private function getParent($chapter)
	{
		return varset($this->catList[$chapter]['chapter_parent'], false);			
	}	

	private function isVisible($chapter)
	{
		return check_class($this->catList[$chapter]['chapter_visibility']);
		// return varset($this->catList[$chapter]['chapter_visibility'], 0);				
	}

		
	function config()
	{
		$sql = e107::getDb();
		
		$catList = array();
		
		$catList[] = array('id' => 'all', 'title' => LAN_SEARCH_51);
		
		foreach($this->catList as $key=>$row)
		{
			if(!empty($row['chapter_parent']) && $this->isVisible($key))
			{
				$catList[] = array('id' => $key, 'title' => $this->getName($row['chapter_parent'])." - ".$row['chapter_name']);
			}
		}
			
		$search = array(
			'name'			=> LAN_PLUGIN_PAGE_NAME ,
			'table'			=> 'page AS p LEFT JOIN #page_chapters AS c ON p.page_chapter = c.chapter_id ',
			'return_fields'	=> array('p.page_id', 'p.page_title', 'p.page_sef', 'p.page_text', 'p.page_chapter', 'p.page_datestamp', 'p.menu_image'), 
			'search_fields'	=> array('p.page_title' => '1.2', 'p.page_text' => '0.6', 'p.page_metakeys'=> '1.0', 'p.page_fields' => '0.5'), // fields and their weights.
	
			'order'			=> array('page_datestamp' => DESC),
			'refpage'		=> 'page.php'
		);
		
		if(!empty($catList))
		{
			$search['advanced'] = array(
								'cat'	=> array('type'	=> 'dropdown', 		'text' => LAN_PLUGIN_PAGE_BOCHAP, 'list'=>$catList),
						//		'date'=> array('type'	=> 'date',			'text' => LAN_DATE_POSTED),
					//			'match'=> array('type'	=> 'dropdown',		'text' =>  LAN_SEARCH_52, 'list'=>$matchList)
							);
		}

		return $search;
	}




	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();
		
		$book 				= $this->getParent($row['page_chapter']);
		$row['chapter_sef'] = $this->getSef($row['page_chapter']);
		$row['book_sef']	= $this->getSef($book); 
			
		if(empty($row['page_sef']))
		{
			$row['page_sef'] = '--sef-not-assigned--';	
		}


		if($row['page_chapter'] == 0) // Page without category. 
		{
			$route = 'page/view/other';
			$pre = ''; 	
		}
		else // Page with book/chapter 
		{
			$route = 'page/view/index'; 	
			$pre = $tp->toHtml($this->getName($book),false,'TITLE').' &raquo; '. $tp->toHtml($this->getName($row['page_chapter']),false,'TITLE'). " | ";
		}
		
				
		$res = array();
				
		$res['link'] 		= e107::getUrl()->create($route, $row, array('allow' => 'page_sef,page_title,page_id,chapter_sef,book_sef'));
		$res['pre_title'] 	= $pre; 
		$res['title'] 		= $tp->toHtml($row['page_title'], false, 'TITLE');
		$res['summary'] 	= (!empty($row['page_metadscr'])) ? $row['page_metadscr'] : $row['page_text'];
		$res['detail'] 		= LAN_SEARCH_3.$tp->toDate($row['page_datestamp'], "long");
		$res['image']		= $row['menu_image'];
		
		
		
		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $parm (ie. advanced fields included. in this case 'cat'  )
	 */
	function where($parm=array())
	{
		$tp = e107::getParser();
	
		$time = time();

		$qry = " (c.chapter_visibility IN (".USERCLASS_LIST.") OR p.page_chapter = 0) AND p.page_class IN (".USERCLASS_LIST.") AND p.page_text != '' AND ";
		
		if(!empty($parm['cat']) && $parm['cat'] != 'all')
		{
			$qry .= " p.page_chapter='".intval($parm['cat'])."' AND";	
		}
		
		return $qry;
		
		/*
		
		if (isset($parm['time']) && is_numeric($parm['time'])) {
			$qry .= " n.news_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}
				
		return $qry;
		*/
	
	}

}

