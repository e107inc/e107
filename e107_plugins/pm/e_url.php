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

class pm_url // plugin-folder + '_url'
{
	function config() 
	{
		$config = array();
/*
		$config['inbox'] = array(
			'regex'			=> '^forum/rules/?',
			'sef'			=> 'forum/rules',
			'redirect'		=> '{e_PLUGIN}forum/forum.php?f=rules',
		);

		$config['outbox'] = array(
			'regex'			=> '^forum/stats/?',
			'sef'			=> 'forum/stats',
			'redirect'		=> '{e_PLUGIN}forum/forum_stats.php',
		);

		$config['compose'] = array(
			'regex'			=> '^forum/track/?',
			'sef'			=> 'forum/track',
			'redirect'		=> '{e_PLUGIN}forum/forum.php?f=track',
		);*/


		$config['index'] = array(
			'regex'			=> '^pm/?(.*)', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> 'pm', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_PLUGIN}pm/pm.php$1', 		// file-path of what to load when the regex returns true.

		);



		return $config;
	}
	

	
}