<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/e_rss.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-11-18 02:03:35 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

//##### create feed for admin, return array $eplug_rss_feed --------------------------------
		// Download
		$feed['name']		= ADLAN_24;
		$feed['url']		= 'download';
		$feed['topic_id']	= '';
		$feed['path']		= 'download';
		$feed['text']		= RSS_PLUGIN_LAN_8;
		$feed['class']		= '0';
		$feed['limit']		= '9';
		$eplug_rss_feed[] = $feed;
		
// ------------------------------------------------------------------------------------


		// Download categories for admin import. 
		
		if($sqli -> db_Select("download_category", "*","download_category_id!='' ORDER BY download_category_order "))
		{
			while($rowi = $sqli -> db_Fetch())
			{
				$feed['name']		= ADLAN_24.' > '.$rowi['download_category_name'];
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
					$topic = "d.download_category='".intval($topic_id)."' AND ";
				}
				else
				{
					$topic = "";
				}
				$path='';
				$class_list = "0,251,252,253";
                $query = "SELECT d.*, dc.* FROM #download AS d LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id WHERE {$topic} d.download_active > 0 AND d.download_class IN (".$class_list.") ORDER BY d.download_datestamp DESC LIMIT 0,".$this -> limit;
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
					$rss[$loop]['link'] = $e107->base_path."download.php?view.".$value['download_id'];
					$rss['description'] = ($rss_type == 3 ? $value['download_description'] : $value['download_description']);
                    $rss['category_name'] = $value['download_category_name'];
                    $rss['category_link'] = $e107->base_path."download.php?list.".$value['download_category_id'];
					$rss['enc_url'] = $e107->base_path."request.php?".$value['download_id'];
					$rss['enc_leng'] = $value['download_filesize'];
					$rss['enc_type'] = $this->getmime($value['download_url']);
					$rss['pubdate'] = $value['download_datestamp'];
					$loop++;
				}


//##### ------------------------------------------------------------------------------------

$eplug_rss_data[] = $rss;
