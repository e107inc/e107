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
 * 		require_once("../../class2.php");
 * 	}
 * 
 */
 
if (!defined('e107_INIT')) { exit; }

// v2.x Standard  - Simple mod-rewrite module. 

class forum_url // plugin-folder + '_url'
{
	function config() 
	{
		$config = array();

		$config['rules'] = array(
			'regex'			=> '^forum/rules/?',
			'sef'			=> 'forum/rules',
			'redirect'		=> '{e_PLUGIN}forum/forum.php?f=rules',
		);

		$config['stats'] = array(
			'regex'			=> '^forum/stats/?',
			'sef'			=> 'forum/stats',
			'redirect'		=> '{e_PLUGIN}forum/forum_stats.php',
		);

		$config['post'] = array(
			'regex'			=> '^forum/post/?',
			'sef'			=> 'forum/post/',
			'redirect'		=> '{e_PLUGIN}forum/forum_post.php',
		);

		// only create url  - parsed above.
		$config['move'] = array(
			'sef'           => 'forum/post/?f=move&amp;id={thread_id}',
		);

		$config['topic'] = array(
			'regex'			=> '^forum/(.*)/(\d*)-([\w-]*)/?\??(.*)',
			'sef'			=> 'forum/{forum_sef}/{thread_id}-{thread_sef}/',
			'redirect'		=> '{e_PLUGIN}forum/forum_viewtopic.php?id=$2&$4'
		);
/*
		$config['subforum'] = array(
			'regex'			=> '^forum/(.*)/(.*)$',
			'sef'			=> 'forum/{parent_sef}/{forum_sef}',
			'redirect'		=> '{e_PLUGIN}forum/forum_viewforum.php?sef=$2',
			'legacy'        => '{e_PLUGIN}forum/forum_viewforum.php?id={forum_id}'
		);
*/

		$config['forum'] = array(
			'regex'			=> '^forum/(.*)$',
			'sef'			=> 'forum/{forum_sef}',
			'redirect'		=> '{e_PLUGIN}forum/forum_viewforum.php?sef=$1',
			'legacy'        => '{e_PLUGIN}forum/forum_viewforum.php?id={forum_id}'
		);

		$config['index'] = array(
			'regex'			=> '^forum/?$', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> 'forum', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_PLUGIN}forum/forum.php', 		// file-path of what to load when the regex returns true.

		);



		return $config;
	}
	

	
}