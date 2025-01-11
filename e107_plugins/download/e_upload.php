<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


if (!defined('e107_INIT')) { exit; }

// v2 e_upload addon.

class download_upload
{

	function config()
	{
		$config = array(
			'name'			    => LAN_PLUGIN_DOWNLOAD_NAME, // Prune downloads history
			'table'		        => "download",  // table to insert upload data into.
			'media'	            => array(
									'file'      => 'download_file',  // media-category for first imported file.
									'preview'   => '_common_image',  // media-category for screenshot/preview imported file.
									),
			'url'               => e_PLUGIN_ABS.'download/admin_download.php?mode=main&action=edit&id={ID}' // URL to edit new record.
		);

		return $config;
	}


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
	            $catID = 'download__'.$row['download_category_id'];
		        $arr[$id][$catID] = $id2;
	            if(!empty($row['download_category_sub']))
	            {
					foreach($row['download_category_sub'] as $key=>$val)
					{
						$subid = $val['download_category_name'];
						$catID2 = 'download__'.$val['download_category_id'];
						$arr[$id][$subid][$catID2] = $subid;
					}
	            }

	        }

	    }

		return $arr;





	}

	function insert($upload)
	{

		 $ret = array(
            'download_name'             => $upload['upload_name'],
            'download_url'              => $upload['upload_file'],
            'download_sef'              => eHelper::title2sef($upload['upload_name']),
            'download_author'           => $upload['upload_poster'],
            'download_author_email'     => $upload['upload_email'],
            'download_author_website'   => $upload['upload_website'],
            'download_description'      => $upload['upload_description'],
            'download_keywords'         => null,
            'download_filesize'         => $upload['upload_filesize'],
            'download_requested'        => 0,
            'download_category'         => $upload['upload_category'],
            'download_active'           => 1,
            'download_datestamp'        => $upload['upload_datestamp'],
            'download_thumb'            => null,
            'download_image'            => $upload['upload_ss'],
            'download_comment'          => 1,
            'download_class'            => e_UC_MEMBER,
            'download_visible'          => e_UC_MEMBER,
            'download_mirror'           => null,
            'download_mirror_type'      => 0,
        );

        return $ret;

	}



}