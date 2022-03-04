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

class gsitemap_url // plugin-folder + '_url'
{
	function config()
	{
		$config = array();


		$config['index'] = array(
			'alias'         => 'sitemap',
			'regex'			=> '^{alias}/?$', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> '{alias}', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_BASE}gsitemap.php?show=1', 		// file-path of what to load when the regex returns true.

		);

		$config['xml'] = array(
			'alias'         => 'sitemap',
			'regex'			=> '^{alias}\.xml$', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> '{alias}.xml', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_BASE}gsitemap.php', 		// file-path of what to load when the regex returns true.

		);

		$addons = e107::getAddonConfig('e_gsitemap', 'gsitemap');

		foreach($addons as $plug => $item)
		{
			foreach($item as $data )
			{
				$key = $plug.'-'.$data['sef'];  // eg. news-posts
				$config[$key] = array(
					'alias'         => $key.'-sitemap',
					'regex'			=> '^{alias}\.xml$', 						// matched against url, and if true, redirected to 'redirect' below.
					'sef'			=> '{alias}.xml', 							// used by e107::url(); to create a url from the db table.
					'redirect'		=> '{e_BASE}gsitemap.php?plug='.$plug.'&func='.$data['function'], 		// file-path of what to load when the regex returns true.

				);

			//	var_dump($config);
			}

		}


		return $config;
	}



}