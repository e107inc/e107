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

class news_url // plugin-folder + '_url'
{
	function config()
	{
		$config = array();

		$pref = e107::pref('core','url_aliases'); // [en][news]

		$alias = null;

		if(!empty($pref[e_LAN]))
		{
			foreach($pref[e_LAN] as $k=>$v)
			{
				if($v === 'news' )
				{
					$alias = $k;
					break;
				}
			}
		}

		$config['index'] = array(
		//	'regex'			=> '^gallery/?$', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> (!empty($alias)) ? $alias : 'news', 	// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_BASE}news.php', 		// file-path of what to load when the regex returns true.

		);


		return $config;
	}



}