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

class _blank_url // plugin-folder + '_url'
{
	function config() 
	{
		$config = array();

		$config['other'] = array(
			'alias'         => '_blank',                            // default alias '_blank'. {alias} is substituted with this value below. Allows for customization within the admin area.
			'regex'			=> '^{alias}/other/?$', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> '{alias}/other/', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_PLUGIN}_blank/_blank.php?other=1', 		// file-path of what to load when the regex returns true.

		);


		$config['index'] = array(
			'regex'			=> '^_blank/?$', 						// matched against url, and if true, redirected to 'redirect' below.
			'sef'			=> '_blank', 							// used by e107::url(); to create a url from the db table.
			'redirect'		=> '{e_PLUGIN}_blank/blank.php', 		// file-path of what to load when the regex returns true.

		);

		return $config;
	}
	

}