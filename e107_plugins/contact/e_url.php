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

class contact_url // plugin-folder + '_url'
{
	function config() 
	{
		$config = array();

		$config['index'] = array(
			'alias'         => 'contact',
			'regex'			=> '^{alias}\/?$',
			'sef'			=> '{alias}',
			'redirect'		=> '{e_BASE}contact.php',
		);

		return $config;
	}
	

	
}