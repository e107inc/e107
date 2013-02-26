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
	function config()
	{	
		$links = array();
			
		$links[] = array(
			'name'			=> "All Pages",
			'function'		=> "pageNav",
			'description' 	=> ""
		);	
		
		return $links;
	}
	
	

	function pageNav($parm='') 
	{
		$options = array();
		if(vartrue($parm))
		{
			parse_str($parm,$options);	
		}
			
		$sql 		= e107::getDb();
		$sublinks 	= array();
		$arr 		= array();	
		
		// find the chapter if required
		if(vartrue($options['page']) && !vartrue($options['chapter']))
		{
			$options['chapter'] = $sql->retrieve('page', 'page_chapter', 'page_id='.intval($options['page']));
		}	

		$query		= "SELECT * FROM #page WHERE ";
		if(vartrue($options['chapter']))
		{
			$query .= "page_chapter = ".intval($options['chapter']);	 		
		}
		elseif(vartrue($options['book']))
		{
			$query .= "page_chapter IN (SELECT chapter_id FROM #page_chapters WHERE chapter_parent=".intval($options['book']).")";	 		
		}
		else
		{
			$query .= 1;
		}
		$query 		.= " ORDER BY page_order"; 
		
		$data 		= $sql->retrieve($query, true);
		$_pdata 	= array();
		
		foreach($data as $row)
		{
			$pid = $row['page_chapter'];
			$sublinks[$pid][] = $_pdata[] = array(
				'link_id'			=> $row['page_id'],
				'link_name'			=> $row['page_title'],
				'link_url'			=> e107::getUrl()->create('page/view', $row, array('allow' => 'page_sef,page_title,page_id')),
				'link_description'	=> '',
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> $row['page_order'],
				'link_parent'		=> $row['page_chapter'],
				'link_open'			=> '',
				'link_class'		=> intval($row['page_class']),
				'link_active'		=> ($options['page'] && $row['page_id'] == $options['page']),
			);
		}

		$filter = 1;
		
		if(vartrue($options['chapter']))
		{
			//$filter = "chapter_id > ".intval($options['chapter']);
			$title = $sql->retrieve('page_chapters', 'chapter_name', 'chapter_id='.intval($options['chapter']));
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
		}


		$books = $sql->retrieve("SELECT * FROM #page_chapters WHERE ".$filter." ORDER BY chapter_order ASC" , true);
		foreach($books as $row)
		{
			
			$arr[] = array(
				'link_id'			=> $row['chapter_id'],
				'link_name'			=> $row['chapter_name'],
				//TODO SEFURLS using chapter_sef. 
				'link_url'			=> ($row['chapter_parent'] == 0) ? 'page.php?bk='.$row['chapter_id'] : 'page.php?ch='.$row['chapter_id'], 
			//	'link_url'			=> vartrue($row['chapter_sef'],'#'),
				
				'link_description'	=> '',
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> $row['chapter_order'],
				'link_parent'		=> $row['chapter_parent'],
				'link_open'			=> '',
				'link_class'		=> 0, 
				'link_sub'			=> varset($sublinks[$row['chapter_id']]),
				'link_active'		=> false,
			);	
			$parent = vartrue($options['book']) ? intval($row['chapter_parent']) : 0;
			
		}
		
		$outArray 	= array();
		$ret =  e107::getNav()->compile($arr, $outArray, $parent);		
		
		if(!$title) return $ret;
		return array('title' => $title, 'body' => $ret);
	}
}
