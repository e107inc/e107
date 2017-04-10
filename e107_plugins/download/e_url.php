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

class download_url // plugin-folder + '_url'
{

	public $alias = 'download';

	function config($profile=null)
	{
		$config = $this->profile1();

		return $config;
	}


	function profile1()
	{
		$config = array();

		$config['category'] = array(

			'sef'   => '{alias}/category/{download_category_id}/{download_category_sef}/',
		);

		$config['item']     = array(
			'sef'       => '{alias}/{download_id}/{download_sef}'
		);

		$config['get']     = array(
			'regex'		=> '^{alias}/get/([\d]*)/(.*)$',
			'sef'       => '{alias}/get/{download_id}/{download_sef}',
			'redirect'	=> '{e_PLUGIN}download/request.php?id=$1', 		// file-path of what to load when the regex returns true.
		);


		$config['index'] = array(
		//	'regex'			=> '^download/?$',
			'alias'     => 'download',
			'sef'		=> '{alias}',
			'redirect'	=> '{e_PLUGIN}download/download.php',
		);


		return $config;
	}



}