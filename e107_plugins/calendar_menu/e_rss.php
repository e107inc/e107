<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	RSS calendar feed
 */

// TODO LAN

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class calendar_menu_rss
{
	/**
	 * Admin RSS Configuration 
	 */		
	function config() 
	{
		$config = array();
	
		$config[] = array(
			'name'			=> 'Calendar',
			'url'			=> 'calendar',
			'topic_id'		=> '',
			'description'	=> 'This is the rss feed for the calendar entries', // that's 'description' not 'text' 
			'class'			=> '0',
			'limit'			=> '9'
		);
		
		return $config;
	}
	
	/**
	 * Compile RSS Data
	 * @param $parms array	url, limit, id 
	 * @return array
	 */
	function data($parms='')
	{
		$sql = e107::getDb();
		
		require_once('ecal_class.php');
		$ecal_class = new ecal_class;

		$current_day	= $ecal_class->cal_date['mday'];
		$current_month	= $ecal_class->cal_date['mon'];
		$current_year	= $ecal_class->cal_date['year'];
		$current		= mktime(0,0,0,$current_month, $current_day, $current_year);
		
		$rss = array();
		$i=0;

		$query = "
		SELECT e.*, c.event_cat_name
		FROM `#event` AS e
		LEFT JOIN `#event_cat` AS c ON c.event_cat_id = e.event_category
		WHERE e.event_start>='{$current}' AND c.event_cat_class REGEXP '".e_CLASS_REGEXP."'
		ORDER BY e.event_start ASC LIMIT 0,".$parms['limit'];

		if($items = $sql->gen($query))
		{

			while($row = $sql->fetch())
			{
				$tmp						= explode(".", $row['event_author']);
				$rss[$i]['author']			= $tmp[1];
				$rss[$i]['author_email']	= ''; 
				$rss[$i]['link']			= "calendar_menu/event.php?".$row['event_start'].".event.".$row['event_id'];
				$rss[$i]['linkid']			= $row['event_id'];
				$rss[$i]['title']			= $row['event_title'];
				$rss[$i]['description']		= '';
				$rss[$i]['category_name']	= $row['event_cat_name'];
				$rss[$i]['category_link']	= '';
				$rss[$i]['datestamp']		= $row['event_start'];
				$rss[$i]['enc_url']			= "";
				$rss[$i]['enc_leng']		= "";
				$rss[$i]['enc_type']		= "";
				$i++;
			}

		}				
					
		return $rss;
	}
		
	
}

?>