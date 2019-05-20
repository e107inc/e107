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
							
			'return_fields'	=> array('t.faq_question','t.faq_answer','t.faq_datestamp','x.faq_info_title','t.faq_id','x.faq_info_id','x.faq_info_title', 'x.faq_info_class','x.faq_info_sef'), 
			'search_fields'	=> array('t.faq_question'=>1.0, 't.faq_answer'=>1.2, "x.faq_info_title"=>0.6, 't.faq_tags'=> 1.4), // fields and weights. 
			'order'			=> array('t.faq_question' => DESC),
			'refpage'		=> 'chat.php'
		);


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$res = array();

	    $res['link'] 		= $url = e107::url('faqs','category', $row); // e_PLUGIN . "faq/faq.php?cat." . $cat_id . "." . $link_id . "";
	    $res['pre_title'] 	= $row['faq_info_title'] ? $row['faq_info_title'] .' | ' : "";
	    $res['title'] 		= $row['faq_question'];
	    $res['summary'] 	= substr($row['faq_answer'], 0, 100)."....  ";
	    $res['detail'] 		= e107::getParser()->toDate($row['faq_datestamp'],'long');

		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $_GET (ie. advanced fields included. in this case 'date' and 'author' )
	 */
	function where($parm=null)
	{
		$tp = e107::getParser();

		$qry = " find_in_set(x.faq_info_class,'".USERCLASS_LIST."') AND ";
		
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