<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * Chatbox e_search addon 
 */
 

if (!defined('e107_INIT')) { exit; }

// v2 e_search addon. 
// Removes the need for search_parser.php, search_advanced.php and in most cases search language files. 

class news_search extends e_search // include plugin-folder in the name.
{
		
	function config()
	{
		$sql = e107::getDb();
		
		$catList = array();
		
		$catList[] = array('id' => 'all', 'title' => LAN_SEARCH_51);
		
		if ($sql ->select("news_category", "category_id, category_name")) 
		{
			while($row = $sql->fetch()) 
			{
				$catList[] = array('id' => $row['category_id'], 'title' => $row['category_name']);
			//	$advanced_caption['title'][$row['category_id']] = 'News -> '.$row['category_name'];
			}
		}
		
		
		$matchList = array(
					array('id' => 0, 'title' => LAN_SEARCH_53),
					array('id' => 1, 'title' => LAN_SEARCH_54)
		);

			
		$search = array(
			'name'			=> LAN_SEARCH_98,
			'table'			=> 'news AS n LEFT JOIN #news_category AS c ON n.news_category = c.category_id',

			'advanced' 		=> array(
								'cat'	=> array('type'	=> 'dropdown', 		'text' => LAN_SEARCH_55, 'list'=>$catList),
								'date'=> array('type'	=> 'date',			'text' => LAN_DATE_POSTED),
								'match'=> array('type'	=> 'dropdown',		'text' =>  LAN_SEARCH_52, 'list'=>$matchList)
							),
							
			'return_fields'	=> array('n.news_id', 'n.news_title', 'n.news_sef', 'n.news_body', 'n.news_extended', 'n.news_allow_comments', 'n.news_datestamp', 'n.news_category', 'c.category_name'), 
			'search_fields'	=> array('n.news_title' => '1.2', 'n.news_body' => '0.6', 'n.news_extended' => '0.6', 'n.news_summary' => '1.2', 'n.news_meta_keywords'=>'1.1', 'n.news_meta_description'=>'1.1'), // fields and their weights.
	
			'order'			=> array('news_datestamp' => 'DESC'),
			'refpage'		=> 'news.php'
		);


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();
		
		$res = array();
				
		$res['link'] 		= e107::getUrl()->create('news/view/item', $row);//$row['news_allow_comments'] ? "news.php?item.".$row['news_id'] : "comment.php?comment.news.".$row['news_id'];
		$res['pre_title'] 	= $tp->toHTML($row['category_name'],false,'TITLE')." | ";
		$res['title'] 		= $row['news_title'];
		$res['summary'] 	= $row['news_body'].' '.$row['news_extended'];
		$res['detail'] 		= LAN_SEARCH_3.$tp->toDate($row['news_datestamp'], "long");
		$res['image']		= $row['news_thumbnail'];
		
		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $parm (ie. advanced fields included. in this case 'date' and 'author' )
	 */
	function where($parm=null)
	{
		$tp = e107::getParser();
	
		$time = time();
		
		$qry = "(news_start < ".$time.") AND (news_end=0 OR news_end > ".$time.") AND news_class IN (".USERCLASS_LIST.") AND";
		
		if (isset($parm['cat']) && $parm['cat'] != 'all') {
			$qry .= " c.category_id='".intval($parm['cat'])."' AND";
		}
		
		if (isset($parm['time']) && is_numeric($parm['time'])) {
			$qry .= " n.news_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}
				
		return $qry;
	}
	

}

//Old v1.
// $search_info[] = array('sfile' => e_PLUGIN.'chatbox_menu/search/search_parser.php', 'qtype' => CB_SCH_LAN_1, 'refpage' => 'chat.php', 'advanced' => e_PLUGIN.'chatbox_menu/search/search_advanced.php');

