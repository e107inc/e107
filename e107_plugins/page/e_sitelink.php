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

class page_sitelinks // include plugin-folder in the name.
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
		if(vartrue($parm))
		{
			parse_str($parm,$options);	
		}
			
		$sql 		= e107::getDb();
		$sublinks 	= array();
		$arr 		= array();		

		$query		= "SELECT * FROM #page WHERE ";
		$query 		.= vartrue($options['chapter']) ? "page_chapter = ".intval($options['chapter']) : 1;	 		
		$query 		.= " ORDER BY page_order"; 
	//	$query		.= vartrue($options['limit']) ? " LIMIT ".intval($options['limit']) : "";	
		
		$data 		= $sql->retrieve($query, true);

		foreach($data as $row)
		{
			$pid = $row['page_chapter'];
			$sublinks[$pid][] = array(
				'link_id'			=> $row['page_id'],
				'link_name'			=> $row['page_title'],
				'link_url'			=> vartrue($row['page_sef'],'page.php?'.$row['page_id']), 
				'link_description'	=> '',
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> $row['page_order'],
				'link_parent'		=> $row['page_chapter'],
				'link_open'			=> '',
				'link_class'		=> intval($row['page_class'])
			);
		}
		

	//	$filter = vartrue($options['book']) ? "chapter_id = ".intval($options['book']) : 1;	
		$filter = 1;
		
		if(vartrue($options['chapter']))
		{
			$filter = "chapter_id > ".intval($options['chapter']);
		}

		if(vartrue($options['book']))
		{
			$filter = "chapter_id > ".intval($options['book']);
		}


		$books = $sql->retrieve("SELECT * FROM #page_chapters WHERE ".$filter." ORDER BY chapter_order ASC" , true);
		
		foreach($books as $row)
		{
			
			$arr[] = array(
				'link_id'			=> $row['chapter_id'],
				'link_name'			=> $row['chapter_name'],
				'link_url'			=> vartrue($row['chapter_sef'],'#'),
				'link_description'	=> '',
				'link_button'		=> '',
				'link_category'		=> '',
				'link_order'		=> $row['chapter_order'],
				'link_parent'		=> $row['chapter_parent'],
				'link_open'			=> '',
				'link_class'		=> 0, 
				'link_sub'			=> varset($sublinks[$row['chapter_id']])
			);	
			

			$parent = vartrue($options['book']) ? intval($row['chapter_parent']) : 0;
			
		}
		
		
	//	print_a($arr);
		
	//	echo "<h3>Compiled</h3>";
		$outArray 	= array();
		$ret =  e107::getNav()->compile($arr, $outArray, $parent);		

	//		print_a($ret);
	
	
	//	$mes = e107::getMessage();
	//	$mes->addDebug( print_a($ret,true));
		
		return $ret;
	}
	
}



?>