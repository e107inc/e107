<?php

if (!defined('e107_INIT')) { exit; }

// v2 e_search addon. 
// Removes the need for search_parser.php, search_advanced.php and in most cases search language files. 

class faqs_search extends e_search // include plugin-folder in the name.
{
		
	function config()
	{	
		$search = array(
			'name'			=> LAN_PLUGIN_FAQS_NAME,
			'table'			=> 'faqs as t LEFT JOIN #faqs_info as x on t.faq_parent=x.faq_info_id',

		//	'advanced' 		=> array(
		//						'date'	=> array('type'	=> 'date', 		'text' => LAN_DATE_POSTED),
		//						'author'=> array('type'	=> 'author',	'text' => LAN_SEARCH_61)
		//					),
							
			'return_fields'	=> array('t.faq_question','t.faq_answer','t.faq_datestamp','x.faq_info_title','t.faq_id','x.faq_info_id','x.faq_info_class'), 
			'search_fields'	=> array('t.faq_question'=>1.0, 't.faq_answer'=>1.2, "x.faq_info_title"=>0.6), // fields and weights. 
		//	$where = " find_in_set(faq_info_class,'".USERCLASS_LIST."') and ";
			'order'			=> array('t.faq_question' => DESC),
			'refpage'		=> 'chat.php'
		);


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();

		preg_match("/([0-9]+)\.(.*)/", $row['cb_nick'], $user);

		$res = array();
	
		$res['link'] 		= e_PLUGIN."chatbox_menu/chat.php?".$row['cb_id'].".fs";
		$res['pre_title'] 	= LAN_SEARCH_7;
		$res['title'] 		= $user[2];
		$res['summary'] 	= $row['cb_message'];
		$res['detail'] 		= $tp->toDate($row['cb_datestamp'], "long");

		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $_GET (ie. advanced fields included. in this case 'date' and 'author' )
	 */
	function where($parm='')
	{
		$tp = e107::getParser();

		$qry = "";
		
		if (vartrue($parm['time']) && is_numeric($parm['time'])) 
		{
			$qry .= " cb_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}

		if (vartrue($parm['author'])) 
		{
			$qry .= " cb_nick LIKE '%".$tp -> toDB($parm['author'])."%' AND";
		}
		
		return $qry;
	}
	

}
// $search_info[]=array( 'sfile' => e_PLUGIN.'faq/search.php', 'qtype' => 'FAQ', 'refpage' => 'faq.php');

?>