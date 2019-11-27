<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * blank e_search addon 
 */
 

if (!defined('e107_INIT')) { exit; }

// v2.x e_search addon.


class _blank_search extends e_search // include plugin-folder in the name.
{
		
	function config()
	{	
		$search = array(
			'name'			=> "Blank Plugin",
			'table'			=> 'blank',

			'advanced' 		=> array(
								'date'	=> array('type'	=> 'date', 		'text' => LAN_DATE_POSTED),
								'author'=> array('type'	=> 'author',	'text' => LAN_SEARCH_61)
							),
							
			'return_fields'	=> array('blank_id', 'blank_nick', 'blank_message', 'blank_datestamp'),
			'search_fields'	=> array('blank_nick' => '1', 'blank_message' => '1'), // fields and weights.
			
			'order'			=> array('blank_datestamp' => 'DESC'),
			'refpage'		=> 'chat.php'
		);


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();

		preg_match("/([0-9]+)\.(.*)/", $row['blank_nick'], $user);

		$res = array();
	
		$res['link'] 		= e_PLUGIN."blank_menu/_blank.php?".$row['blank_id'].".fs";
		$res['pre_title'] 	= LAN_SEARCH_7;
		$res['title'] 		= $user[2];
		$res['summary'] 	= $row['blank_message'];
		$res['detail'] 		= $tp->toDate($row['blank_datestamp'], "long");

		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $_GET (ie. advanced fields included. in this case 'date' and 'author' )
	 */
	function where($parm=null)
	{
		$tp = e107::getParser();

		$qry = "";
		
		if (vartrue($parm['time']) && is_numeric($parm['time'])) 
		{
			$qry .= " blank_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}

		if (vartrue($parm['author'])) 
		{
			$qry .= " blank_nick LIKE '%".$tp -> toDB($parm['author'])."%' AND";
		}
		
		return $qry;
	}
	

}

