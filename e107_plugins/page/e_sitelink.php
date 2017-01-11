<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/


if (!defined('e107_INIT')) { exit; }

class page_sitelink // include plugin-folder in the name.
{
	private $chapterSef = array();
	private $chapterParent = array();
	
	function __construct()
	{
		$sql = e107::getDb();
		
		$books = $sql->retrieve("SELECT chapter_id,chapter_sef,chapter_parent FROM #page_chapters ORDER BY chapter_id ASC" , true);
				
		foreach($books as $row)
		{
			$id = $row['chapter_id'];
			$this->chapterSef[$id] = $row['chapter_sef'];
			$this->chapterParent[$id] = $row['chapter_parent'];
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
	
	
	function config()
	{	
		$links = array();
		$sql = e107::getDb();
		
		$links[] = array(
			'name'			=> "All Books",
			'function'		=> "bookNav",
			'description' 	=> "A list of all books"
		);
		
		$books = $sql->retrieve("SELECT * FROM #page_chapters WHERE chapter_parent =0 ORDER BY chapter_order ASC" , true);
				
		foreach($books as $row)
		{
			$links[] = array(
				'name'			=> "All Chapters from ".$row['chapter_name'],
				'function'		=> "chapterNav",
				'parm'			=> $row['chapter_id'],
				'description' 	=> "A list of all chapters from the book ".$row['chapter_name']
			);
		}

		$chaps = $sql->retrieve("SELECT * FROM #page_chapters WHERE chapter_parent !=0 ORDER BY chapter_order ASC" , true);

		foreach($chaps as $row)
		{
			$links[] = array(
				'name'			=> "All Pages from ".$row['chapter_name'],
				'function'		=> "pagesFromChapter",
				'parm'			=> $row['chapter_id'],
				'description' 	=> "A list of all pages from the chapter ".$row['chapter_name']
			);
		}
			
		$links[] = array(
			'name'			=> "All Pages",
			'function'		=> "pageList",
			'parm'			=> "",
			'description' 	=> "A list of all pages"
		);	
		
		return $links;
	}
	
	/**
	 * Return a tree of all books and their chapters. 
	 */
	public function bookNav($book)
	{
		return $this->pageNav('book=0');
	}

	public function pagesFromChapter($id)
	{

		return $this->pageList($id);
	}


	private function pageList($parm)
	{
		$sql = e107::getDb();
		$arr = array();


		if(!empty($parm))
		{
			$query = "SELECT * FROM `#page` WHERE page_class IN (".USERCLASS_LIST.") AND page_chapter = ".intval($parm)." ORDER BY page_order ASC" ;
		}
		else
		{
			$query = "SELECT * FROM `#page` WHERE page_class IN (".USERCLASS_LIST.") ORDER BY page_order ASC" ;
		}

		$pages = $sql->retrieve($query, true);

		foreach($pages as $row)
		{
			$chapter_parent = $this->getParent($row['page_chapter']);

			$row['book_sef'] = $this->getSef($chapter_parent);

			$row['chapter_sef'] = $this->getSef($row['page_chapter']);

			if(!vartrue($row['chapter_sef']))
			{
			//	$row['chapter_sef'] = '--sef-not-assigned--';
			}

			$arr[]  = array(
				'link_id'			=> $row['page_id'],
				'link_name'			=> $row['page_title'] ? $row['page_title'] : 'No title', // FIXME lan
				'link_url'			=> e107::getUrl()->create('page/view', $row, array('allow' => 'page_sef,page_title,page_id,chapter_sef,book_sef')),
				'link_description'	=> '',
			//	'link_button'		=> $row['menu_image'],
				'link_category'		=> '',
				'link_order'		=> $row['page_order'],
				'link_parent'		=> $row['page_chapter'],
				'link_open'			=> '',
				'link_class'		=> intval($row['page_class']),
				'link_active'		=> (isset($options['cpage']) && $row['page_id'] == $options['cpage']),
				'link_identifier'	=> 'page-nav-'.intval($row['page_id']) // used for css id.

			);

		}

		return $arr;

	}

	/**
	 * Return a list of all chapters from a sepcific book. 
	 */
	public function chapterNav($book)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		if($sql->select("page_chapters", "*", "chapter_parent = ".intval($book)." AND chapter_visibility IN (".USERCLASS_LIST.")  ORDER BY chapter_order ASC "))
		{
			$sublinks = array();
			
			while($row = $sql->fetch())
			{
				$sef = $row;
				$sef['chapter_sef'] = $this->getSef($row['chapter_id']);	
				$sef['book_sef']	= $this->getSef($row['chapter_parent']);
				
				$sublinks[] = array(
					'link_name'			=> $tp->toHtml($row['chapter_name'],'','TITLE'),
					'link_url'			=> e107::getUrl()->create('page/chapter/index', $sef), // 'page.php?ch='.$row['chapter_id'],
					'link_description'	=> '',
					'link_button'		=> $row['chapter_icon'],
					'link_category'		=> '',
					'link_order'		=> '',
					'link_parent'		=> $row['chapter_parent'],
					'link_open'			=> '',
					'link_class'		=> 0,
					'link_identifier'	=> 'page-nav-'.intval($row['chapter_id']) // used for css id. 
				);

			}
			
			return $sublinks;
			
		}
	}

	function pageNav($parm='') 
	{
		$frm = e107::getForm();
		$options = array();
		if(vartrue($parm))
		{
			parse_str($parm,$options);	
		}
			

			
		$sql 		= e107::getDb();
		$sublinks 	= array();
		$arr 		= array();	
		
		// map current when in auto mode
		if(vartrue($options['auto']))
		{
			// current book found, top book not set
			if(vartrue($options['cbook']) && !vartrue($options['book']))
			{
				$options['book'] = $options['cbook'];
			}
			
			// current chapter found, top chapter not set
			if(vartrue($options['cchapter']) && !vartrue($options['chapter']))
			{
				$options['chapter'] = $options['cchapter'];
			}
			
			// current chapter found, top chapter not set
			if(vartrue($options['cpage']) && !vartrue($options['page']))
			{
				$options['page'] = $options['cpage'];
			}
		}
		
		// find the chapter if required
		if(vartrue($options['page']) && !vartrue($options['chapter']))
		{
			$options['chapter'] = $sql->retrieve('page', 'page_chapter', 'page_id='.intval($options['page']));
		}	

		$query		= "SELECT * FROM #page WHERE ";
		$q = array();
		
		if(vartrue($options['chapter']))
		{
			$q[] = "page_title !='' AND page_chapter = ".intval($options['chapter']);	 		
		}
		elseif(vartrue($options['book']))
		{
			$q[] = "page_title !='' && page_chapter IN (SELECT chapter_id FROM #page_chapters WHERE chapter_parent=".intval($options['book']).")";	 		
		}
		// XXX discuss FIXED remove DB check, use default title - AND page_title !=''
		$q[] 		= "page_class IN (".USERCLASS_LIST.")";
		
		$query 		.= implode(' AND ', $q)." ORDER BY page_order"; 
		
		$data 		= $sql->retrieve($query, true);
		$_pdata 	= array();
				
		foreach($data as $row)
		{
			$pid = $row['page_chapter'];
			
			$row['chapter_sef'] = $this->getSef($row['page_chapter']);
			$book 				= $this->getParent($row['page_chapter']);
			$row['book_sef']	= $this->getSef($book); 
			
			if(!vartrue($row['page_sef']))
			{
				$row['page_sef'] = '--sef-not-assigned--';	
			}
			
			$sublinks[$pid][] = $_pdata[] = array(
				'link_id'			=> $row['page_id'],
				'link_name'			=> $row['page_title'] ? $row['page_title'] : 'No title', // FIXME lan
				'link_url'			=> e107::getUrl()->create('page/view', $row, array('allow' => 'page_sef,page_title,page_id,chapter_sef,book_sef')),
				'link_description'	=> '',
				'link_button'		=> $row['menu_image'],
				'link_category'		=> '',
				'link_order'		=> $row['page_order'],
				'link_parent'		=> $row['page_chapter'],
				'link_open'			=> '',
				'link_class'		=> intval($row['page_class']),
				'link_active'		=> (isset($options['cpage']) && $row['page_id'] == $options['cpage']),
				'link_identifier'	=> 'page-nav-'.intval($row['page_id']) // used for css id. 
	
			);
		}

		$filter = "chapter_visibility IN (".USERCLASS_LIST.") " ;
		
		if(vartrue($options['chapter']))
		{
			//$filter = "chapter_id > ".intval($options['chapter']);
			
			$title = $sql->retrieve('page_chapters', 'chapter_name', 'chapter_id='.intval($options['chapter']).' AND chapter_visibility IN ('.USERCLASS_LIST.')' );
			$outArray 	= array();
			if(!$title) return e107::getNav()->compile($_pdata, $outArray, $options['chapter']);	
			return array('title' => $title, 'body' => e107::getNav()->compile($_pdata, $outArray, $options['chapter']));
		}

		$parent = 0;
		$title = false;
		if(vartrue($options['book']))
		{
			
			// XXX discuss the idea here
			//$filter = "chapter_id > ".intval($options['book']);
			$filter = "chapter_parent = ".intval($options['book']);
			$parent = intval($options['book']);
			$title = $sql->retrieve('page_chapters', 'chapter_name', 'chapter_id='.intval($options['book']));
			
		//	print_a('parent='.$parent);
		}

		
		$books = $sql->retrieve("SELECT * FROM #page_chapters WHERE ".$filter." ORDER BY chapter_order ASC" , true);
		foreach($books as $row)
		{
			$row['book_sef'] = $this->getSef($row['chapter_parent']);
			
			if(!vartrue($row['chapter_sef']))
			{
				$row['chapter_sef'] = '--sef-not-assigned--';		
			}
			
			$arr[] = array(
				'link_id'			=> $row['chapter_id'],
				'link_name'			=> $row['chapter_name'],
				'link_url'			=> ($row['chapter_parent'] == 0) ? e107::getUrl()->create('page/book/index', $row) : e107::getUrl()->create('page/chapter/index', $row), // ,'page.php?bk='.$row['chapter_id'] : 'page.php?ch='.$row['chapter_id'], 
			//	'link_url'			=> vartrue($row['chapter_sef'],'#'),
				
				'link_description'	=> '',
				'link_button'		=> $row['chapter_icon'],
				'link_category'		=> '',
				'link_order'		=> $row['chapter_order'],
				'link_parent'		=> $row['chapter_parent'],
				'link_open'			=> '',
				'link_class'		=> 0, 
				'link_sub'			=> (!vartrue($options['book']) && !vartrue($options['auto'])) ? varset($sublinks[$row['chapter_id']]) : '', //XXX always test with docs template in bootstrap before changing. 
				'link_active'		=> $row['chapter_parent'] == 0 ? isset($options['cbook']) && $options['cbook'] == $row['chapter_id'] : isset($options['cchapter']) && $options['cchapter'] == $row['chapter_id'],
				'link_identifier'	=> 'page-nav-'.intval($row['chapter_id']) // used for css id. 
			);	
			
		}

		
		$outArray 	= array();
		$parent = vartrue($options['book']) ? intval($options['book']) : 0;
		$ret =  e107::getNav()->compile($arr, $outArray, $parent);		

		if(!$title) return $ret;
		return array('title' => $title, 'body' => $ret);
	}
}
