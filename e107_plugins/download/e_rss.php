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
		$items = $sql->createQueryBuilder()
			->select('*')->from('download_category')
			->where('download_category_id', '!=', '')
			->orderBy('download_category_order')
			->fetchAll();

		if($items)
		{
			foreach($items as $row)
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
		$qb = $sql->createQueryBuilder();
		$qb->select('d.*', 'dc.*')->from('download', 'd')
			->leftJoin('download_category', 'dc', $qb->expr()->compareColumns('d.download_category', 'dc.download_category_id'));

		if($topic_id && is_numeric($topic_id))
		{
			$qb->where('d.download_category', (int) $topic_id);
		}

		$tmp = $qb->where('d.download_active', '>', 0)
			->whereIn('d.download_class', explode(',', USERCLASS_LIST))
			->orderBy('d.download_datestamp', 'DESC')
			->setFirstResult(0)->setMaxResults((int) $limit)
			->fetchAll();
				
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
