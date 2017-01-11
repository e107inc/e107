<?php
/*
 * e107 Bootstrap CMS
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)

 */
 
if (!defined('e107_INIT')) { exit; }

// v2.x Standard  - Simple mod-rewrite module. 

class rss_menu_url // plugin-folder + '_url'
{
	function config() 
	{
		$config = array();

		$config['rss'] = array(
			'alias'			=> 'feed',
			'regex'			=> '^{alias}/(.*)/rss/?([\d]*)?$',
			'sef'			=> '{alias}/{rss_url}/rss/{rss_topicid}',
			'redirect'		=> '{e_PLUGIN}rss_menu/rss.php?cat=$1&type=2&topic=$2'
		);

		$config['atom'] = array(
			'alias'			=> 'feed',
			'regex'			=> '^{alias}/(.*)/atom/?([\d]*)?$',
			'sef'			=> '{alias}/{rss_url}/atom/{rss_topicid}',
			'redirect'		=> '{e_PLUGIN}rss_menu/rss.php?cat=$1&type=4&topic=$2'
		);
	
		$config['index'] = array(
			'alias'			=> 'feed',
			'regex'			=> '^{alias}/?$',
			'sef'			=> '{alias}',
			'redirect'		=> '{e_PLUGIN}rss_menu/rss.php',
		);

		return $config;
	}
	
}