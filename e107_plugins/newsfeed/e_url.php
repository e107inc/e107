<?php
/*
 * e107 Bootstrap CMS
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * IMPORTANT: Make sure the redirect script uses the following code to load class2.php: 
 * 
 * 	if (!defined('e107_INIT'))
 * 	{
 * 		require_once(__DIR__.'/../../class2.php');
 * 	}
 * 
 */
 
if (!defined('e107_INIT')) { exit; }

// v2.x Standard  - Simple mod-rewrite module. 

class newsfeed_url // plugin-folder + '_url'
{
	function config() 
	{
		$config = array();


		$config['source'] = array(
			'alias'         => 'newsfeed',
			'regex'			=> '^{alias}/(\d*)/(.*)', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> '{alias}/{newsfeed_id}/{newsfeed_sef}', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_PLUGIN}newsfeed/newsfeed.php?id=$1', 		// file-path of what to load when the regex returns true.

		);

		$config['index'] = array(
			'alias'         => 'newsfeed',
			'regex'			=> '^{alias}/?', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> '{alias}', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_PLUGIN}newsfeed/newsfeed.php', 		// file-path of what to load when the regex returns true.

		);

		return $config;
	}
	

	
}