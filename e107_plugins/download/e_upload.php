<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Chatbox e_search addon
 */


if (!defined('e107_INIT')) { exit; }

// v2 e_search addon.
// Removes the need for search_parser.php, search_advanced.php and in most cases search language files.

class download_upload
{

	/*function config()
	{
		$cron = array();

		$cron[] = array(
			'name'			=> "Prune Download Log older than 12 months", // Prune downloads history
			'function'		=> "pruneLog",
			'category'		=> '',
			'description' 	=> "Non functional at the moment"
		);

		return $cron;
	}*/


	/**
	 * Compile Array Structure
	 */
	private function compile(&$inArray, &$outArray, $pid = 0)
	{
	    if(!is_array($inArray) || !is_array($outArray)){ return array(); }

	    foreach($inArray as $key => $val)
	    {
	        if($val['download_category_parent'] == $pid)
	        {
	            $val['download_category_sub'] = (!empty($val['download_category_sub'])) ? $val['download_category_sub'] : array();

	            if($val['download_category_id'] != $pid)
	            {
	                 $this->compile($inArray, $val['download_category_sub'], $val['download_category_id']);
	            }

		        $id = $val['download_category_id'];

	            $outArray[$id] = $val;
	        }
	    }
		return $outArray;
	}


	/**
	 * Sent to 'Category' dropdown menu in upload.php form.
	 * @return array
	 */
	function category()
	{
		$sql = e107::getDb();
		$qry = "SELECT download_category_id,download_category_name,download_category_parent FROM `#download_category`  WHERE download_category_class IN (".USERCLASS_LIST.")  ORDER BY download_category_order, download_category_parent";

  	  	if (!$data = $sql->retrieve($qry,true))
	  	{
	    	return array();
	  	}

	  	$outArray = array();

	  	$ret =  $this->compile($data,$outArray);

		$arr = array();
	  	foreach($ret as $k=>$v)
	    {
	        $id = $v['download_category_name'];
	        foreach($v['download_category_sub'] as $row)
	        {
	            $id2 = $row['download_category_name'];
		        $arr[$id] = array($row['download_category_id']=>$id2);
	            if(!empty($row['download_category_sub']))
	            {
					foreach($row['download_category_sub'] as $key=>$val)
					{
						$subid = $val['download_category_name'];
						$arr[$id][$subid] = array($val['download_category_id']=>$subid);
					}
	            }

	        }

	    }


		return $arr;





	}


	// TODO
	function copy($row)
	{



	}



}