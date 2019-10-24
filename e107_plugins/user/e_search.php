<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * user/member e_search addon 
 * replaces e107_handlers/search/: search_user.php, advanced_user.php, 
 */

if (!defined('e107_INIT')) { exit; }

// v2.x e_search addon.
class user_search extends e_search // include plugin-folder in the name.
{
		
	function config()
	{	
		$search = array(
			'name'			=> LAN_140,
			'table'			=> 'user',

			'advanced' 		=> array(
								'time' => array('type' => 'date', 'text' => LAN_SEARCH_62),
							),

			'return_fields'	=> array('user_id', 'user_name', 'user_email', 'user_signature', 'user_join'),
			'search_fields'	=> array('user_name' => '1.2', 'user_signature' => '0.6'), // fields and weights.
			
			'order'			=> array('user_join' => 'DESC'),
			'refpage'		=> 'user.php'
		);

		return $search;
	}

	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();
		$res = array();
	
		$res['link'] 		= e107::getUrl()->create('user/profile/view', array(
								'id' => $row['user_id'], 
								'name' => $row['user_name'])
							   ); //"user.php?id.".$row['user_id'];

		$res['pre_title'] 	= $row['user_id']." | ";
		$res['title'] 		= $row['user_name'];
		$res['summary'] 	= $row['user_signature'] ?  LAN_SEARCH_72.": ".$row['user_signature'] : LAN_SEARCH_73;
		$res['detail'] 		= LAN_SEARCH_74.": ".$tp->toDate($row['user_join'], "long");

		return $res;
		
	}


	/**
	 * Advanced Where
	 * @param $parm - data returned from $_GET 
	 */
	function where($parm=null)
	{
		$tp = e107::getParser();

		$qry = "";
		
		if (vartrue($parm['time']) && is_numeric($parm['time'])) 
		{
			$qry .= " user_join ".($_GET['on'] == 'new' ? '>=' : '<=')." '".(time() - $_GET['time'])."' AND";
		}
		
		return $qry;
	}
	
}