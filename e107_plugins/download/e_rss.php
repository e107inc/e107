<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class download_rss // plugin-folder + '_rss' 
{
	/**
	 * Admin RSS Configuration 
	 */		
	function config() 
	{
		$sql = e107::getDb();
		$config = array();

		// All download items
		$config[] = array(
			'name'			=> LAN_PLUGIN_DOWNLOAD_NAME,
			'url'			=> 'download',
			'topic_id'		=> '',
			'description'	=> RSS_PLUGIN_LAN_8, 
			'class'			=> '0',
			'limit'			=> '9'
		);

		// Specific categories 		
		if($items = $sql->select("download_category", "*","download_category_id != '' ORDER BY download_category_order"))
		{
			while($row = $sql->fetch())
			{
				$config[] = array(
					'name'		=> LAN_PLUGIN_DOWNLOAD_NAME.' > '.$row['download_category_name'],
					'url' 		=> 'download',
					'topic_id' 	=> $row['download_category_id'],
					'path'		=> 'download',
					'text'		=> RSS_PLUGIN_LAN_11.' '.$row['download_category_name'],
					'class'		=> '0',
					'limit'		=> '9',
				);
			}
		}
		
		return $config;
	}
	
	/**
	 * Compile RSS Data
	 * @param $parms array	url, limit, id 
	 * @return array
	 */
	function data($parms='')
	{
		$sql 		= e107::getDb();
		$limit 		= $parms['limit'];
		$topic_id 	= $parms['id'];
		
		$rss 		= array();
		$i 			= 0;

		// Individual download items for admin import
		if($topic_id && is_numeric($topic_id))
		{
			$topic = "d.download_category='" . intval($topic_id) . "' AND ";
		}
		else
		{
			$topic = "";
		}
		
	    $query = "SELECT d.*, dc.* FROM #download AS d LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id WHERE {$topic} d.download_active > 0 AND d.download_class IN (".USERCLASS_LIST.") ORDER BY d.download_datestamp DESC LIMIT 0,".$limit;

	    $sql->gen($query);
	 	$tmp = $sql->db_getList();
				
		foreach($tmp as $value)
		{
			if($value['download_author'])
			{
				$nick = preg_replace("/[0-9]+\./", "", $value['download_author']);
				$rss[$i]['author'] = $nick;
			}

			$rss[$i]['author_email'] 	= $value['download_author_email'];
			$rss[$i]['title'] 			= $value['download_name'];
			$rss[$i]['link'] 			= $e107->base_path."download/download.php?view.".$value['download_id']; // TODO SEF URL
			$rss[$i]['description'] 	= $value['download_description'];
	        $rss[$i]['category_name'] 	= $value['download_category_name'];
	        $rss[$i]['category_link'] 	= $e107->base_path."download/download.php?list.".$value['download_category_id']; // TODO SEF URL
			$rss[$i]['enc_url'] 		= $e107->base_path."download/request.php?".$value['download_id']; // TODO SEF URL
			$rss[$i]['enc_leng'] 		= $value['download_filesize'];
			//$rss[$i]['enc_type'] 		= $this->getmime($value['download_url']);
			$rss[$i]['enc_type'] 		= '';
			$rss[$i]['datestamp'] 		= $value['download_datestamp'];

			$i++;
		}	
					
		return $rss;
	}	
}

/* OLD V1 CODE LEFT FOR REFERENCE - TO BE DELETED

//##### create feed for admin, return array $eplug_rss_feed --------------------------------
	// Download
	$feed['name']		= LAN_PLUGIN_DOWNLOAD_NAME;
	$feed['url']		= 'download';
	$feed['topic_id']	= '';
	$feed['path']		= 'download';
	$feed['text']		= RSS_PLUGIN_LAN_8;
	$feed['class']		= '0';
	$feed['limit']		= '9';
	$eplug_rss_feed[]	= $feed;
		
// ------------------------------------------------------------------------------------

	// Download categories for admin import.
    $sqli = e107::getDb('download');

	if($sqli -> db_Select("download_category", "*","download_category_id!='' ORDER BY download_category_order "))
	{
		while($rowi = $sqli ->fetch())
		{
			$feed['name']		= LAN_PLUGIN_DOWNLOAD_NAME.' > '.$rowi['download_category_name'];
			$feed['url']		= 'download';
			$feed['topic_id']	= $rowi['download_category_id'];
			$feed['path']		= 'download';
			$feed['text']		= RSS_PLUGIN_LAN_11.' '.$rowi['download_category_name'];
			$feed['class']		= '0';
			$feed['limit']		= '9';
			$eplug_rss_feed[] = $feed;
		}
	}

//##### create rss data, return as array $eplug_rss_data -----------------------------------

	if($topic_id && is_numeric($topic_id))
	{
		$topic = "d.download_category='" . intval($topic_id) . "' AND ";
	}
	else
	{
		$topic = "";
	}
	$path='';
	//$class_list = "0,251,252,253";
    $query = "SELECT d.*, dc.* FROM #download AS d LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id WHERE {$topic} d.download_active > 0 AND d.download_class IN (".USERCLASS_LIST.") ORDER BY d.download_datestamp DESC LIMIT 0,".$this -> limit;
    $sql -> db_Select_gen($query);

	//	$sql->db_Select("download", "*", "{$topic} download_active > 0 AND download_class IN (".$class_list.") ORDER BY download_datestamp DESC LIMIT 0,".$this -> limit);
	$tmp = $sql->db_getList();
	$rss = array();
	$loop=0;
	foreach($tmp as $value)
	{
		if($value['download_author'])
		{
			$nick = preg_replace("/[0-9]+\./", "", $value['download_author']);
			$rss[$loop]['author'] = $nick;
		}
		$rss[$loop]['author_email'] = $value['download_author_email'];
		$rss[$loop]['title'] = $value['download_name'];
		$rss[$loop]['link'] = $e107->base_path."download/download.php?view.".$value['download_id'];
		$rss[$loop]['description'] = ($rss_type == 3 ? $value['download_description'] : $value['download_description']);
        $rss[$loop]['category_name'] = $value['download_category_name'];
        $rss[$loop]['category_link'] = $e107->base_path."download/download.php?list.".$value['download_category_id'];
		$rss[$loop]['enc_url'] = $e107->base_path."download/request.php?".$value['download_id'];
		$rss[$loop]['enc_leng'] = $value['download_filesize'];
		$rss[$loop]['enc_type'] = $this->getmime($value['download_url']);
		$rss[$loop]['datestamp'] = $value['download_datestamp'];
		$loop++;
	}

//##### ------------------------------------------------------------------------------------

$eplug_rss_data[] = $rss;