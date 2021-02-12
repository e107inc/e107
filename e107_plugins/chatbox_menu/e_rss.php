<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	RSS chatbox feed addon
 */
 
if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class chatbox_menu_rss // plugin-folder + '_rss' 
{
	/**
	 * Admin RSS Configuration 
	 */		
	function config() 
	{
		$config = array();
	
		$config[] = array(
			'name'			=> 'Chatbox Posts',
			'url'			=> 'chatbox',
			'topic_id'		=> '',
			'description'	=> 'this is the rss feed for the chatbox entries', // that's 'description' not 'text' 
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
		
		$rss = array();
		$i=0;
					
		if($items = $sql->select('chatbox', "*", "cb_blocked=0 ORDER BY cb_datestamp DESC LIMIT 0,".$parms['limit']))
		{

			while($row = $sql->fetch())
			{
				$tmp						= explode(".", $row['cb_nick']);
				$rss[$i]['author']			= $tmp[1];
				$rss[$i]['author_email']	= ''; 
				$rss[$i]['link']			= "chatbox_menu/chat.php?".$row['cb_id'];
				$rss[$i]['linkid']			= $row['cb_id'];
				$rss[$i]['title']			= '';
				$rss[$i]['description']		= $row['cb_message'];
				$rss[$i]['category_name']	= '';
				$rss[$i]['category_link']	= '';
				$rss[$i]['datestamp']		= $row['cb_datestamp'];
				$rss[$i]['enc_url']			= "";
				$rss[$i]['enc_leng']		= "";
				$rss[$i]['enc_type']		= "";
				$i++;
			}

		}				
					
		return $rss;
	}
			
		
	
}



/*
 * XXX Left here as an example of how to convert from v1.x to v2.x
 *  
//##### create feed for admin, return array $eplug_rss_feed --------------------------------

$feed['name']		= 'Chatbox';
$feed['url']		= 'chatbox';			//the identifier for the rss feed url
$feed['topic_id']	= '';					//the topic_id, empty on default (to select a certain category)
$feed['path']		= 'chatbox_menu';		//this is the plugin path location
$feed['text']		= 'this is the rss feed for the chatbox entries';
$feed['class']		= '0';
$feed['limit']		= '9';

// ------------------------------------------------------------------------------------


//##### create rss data, return as array $eplug_rss_data -----------------------------------
$rss = array();
if($items = $sql->select('chatbox', "*", "cb_blocked=0 ORDER BY cb_datestamp DESC LIMIT 0,".$this -> limit)){
	$i=0;
	while($rowrss = $sql ->fetch()){
		$tmp						= explode(".", $rowrss['cb_nick']);
		$rss[$i]['author']			= $tmp[1];
		$rss[$i]['author_email']	= '';
		$rss[$i]['link']			= $e107->base_path.$PLUGINS_DIRECTORY."chatbox_menu/chat.php?".$rowrss['cb_id'];
		$rss[$i]['linkid']			= $rowrss['cb_id'];
		$rss[$i]['title']			= '';
		$rss[$i]['description']		= $rowrss['cb_message'];
		$rss[$i]['category_name']	= '';
		$rss[$i]['category_link']	= '';
		$rss[$i]['datestamp']		= $rowrss['cb_datestamp'];
		$rss[$i]['enc_url']			= "";
		$rss[$i]['enc_leng']		= "";
		$rss[$i]['enc_type']		= "";
		$i++;
	}
}


//##### ------------------------------------------------------------------------------------

$eplug_rss_data[] = $rss;
$eplug_rss_feed[] = $feed;
*/

