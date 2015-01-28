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
		
		$books = $sql->retrieve("SELECT chapter_id,chapter_sef,chapter_parent, chapter_name FROM #page_chapters WHERE chapter_visibility IN (".USERCLASS_LIST.") ORDER BY chapter_parent, chapter_order ASC" , true);
				
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
		return vartrue($this->catList[$chapter]['chapter_sef'],'--sef-not-assigned--');		
	}
	
	private function getParent($chapter)
	{
		return varset($this->catList[$chapter]['chapter_parent'], false);			
	}	



		
	function config()
	{
		$sql = e107::getDb();
		
		$catList = array();
		
		$catList[] = array('id' => 'all', 'title' => LAN_SEARCH_51);
		
		foreach($this->catList as $key=>$row)
		{
			if(!empty($row['chapter_parent']))
			{
				$catList[] = array('id' => $key, 'title' => $this->getName($row['chapter_parent'])." - ".$row['chapter_name']);
			}
		}
		

			
		$search = array(
			'name'			=> "Pages",
			'table'			=> 'page',

			'advanced' 		=> array(
								'cat'	=> array('type'	=> 'dropdown', 		'text' => "Search in Book/Chapter", 'list'=>$catList),
						//		'date'=> array('type'	=> 'date',			'text' => LAN_SEARCH_50),
					//			'match'=> array('type'	=> 'dropdown',		'text' =>  LAN_SEARCH_52, 'list'=>$matchList)
							),
							
			'return_fields'	=> array('page_id', 'page_title', 'page_sef', 'page_text', 'page_chapter', 'page_datestamp', 'menu_image'), 
			'search_fields'	=> array('page_title' => '1.2', 'page_text' => '0.6', 'page_metakeys'=> '1.0'), // fields and their weights. 
	
			'order'			=> array('page_datestamp' => DESC),
			'refpage'		=> 'page.php'
		);


		return $search;
	}




	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();
		
		$book 				= $this->getParent($row['page_chapter']);
		$row['chapter_sef'] = $this->getSef($row['page_chapter']);
		$row['book_sef']	= $this->getSef($book); 
			
		if(!vartrue($row['page_sef']))
		{
			$row['page_sef'] = '--sef-not-assigned--';	
		}
				
		$res = array();
				
		$res['link'] 		= e107::getUrl()->create('page/view', $row, array('allow' => 'page_sef,page_title,page_id,chapter_sef,book_sef'));
		$res['pre_title'] 	= $tp->toHtml($this->getName($book),false,'TITLE').' - '. $tp->toHtml($this->getName($row['page_chapter']),false,'TITLE'). " | ";
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
	function where($parm='')
	{
		$tp = e107::getParser();
	
		$time = time();

		$qry = " page_class IN (".USERCLASS_LIST.") AND `menu_name` = '' AND ";
		
		if(!empty($parm['cat']) && $parm['cat'] != 'all')
		{
			$qry .= " page_chapter='".intval($parm['cat'])."' AND";	
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

?>