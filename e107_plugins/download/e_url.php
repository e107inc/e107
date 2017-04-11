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

	/**
	 * Support for different URL profiles (optional)
	 * @return array
	 */
	public $profiles = array(
		'default'       => array('label' => 'Friendly Default',         'examples' => array('{SITEURL}download/category/3/my-category-name')),
		'non-numeric'   => array('label' => 'Friendly (experimental)',  'examples' => array('{SITEURL}download/my-category/my-sub-category/my-file-name')),
	);


	function config($profile=null)
	{

		switch($profile)
		{
			case "non-numeric":
				$config = $this->profile2();
				break;

			case "default":
			default:
				$config = $this->profile1();

		}

		return $config;
	}




	private function profile2()
	{
		$config = $this->profile1();

		$config['subcategory'] = array(
			'regex'			=> '^{alias}/([^\/]*)/([^\/]*)/?$',
			'redirect'		=> '{e_PLUGIN}vstore/vstore.php?catsef=$2',
			'sef'			=> '{alias}/{cat_sef}/{subcat_sef}'
		);


		$config['category'] = array(
			'regex'			=> '^{alias}/category/([\d]*)/(.*)$',
			'redirect'		=> '{e_PLUGIN}download/download.php?action=list&id=$1',
			'sef'           => '{alias}/{download_category_sef}',
		);


		return $config;
	}


	private function profile1()
	{
		$config = array();

/*

		$config['subcategory'] = array(
			'regex'			=> '^{alias}/([^\/]*)/([^\/]*)/?$',
			'redirect'		=> '{e_PLUGIN}vstore/vstore.php?catsef=$2',
			'sef'			=> '{alias}/{cat_sef}/{subcat_sef}'
		);
*/

		$config['category'] = array(
			'regex'			=> '^{alias}/category/([\d]*)/(.*)$',
			'redirect'		=> '{e_PLUGIN}download/download.php?action=list&id=$1',
			'sef'           => '{alias}/category/{download_category_id}/{download_category_sef}/',
		);

		$config['item']     = array(
			'regex'		    => '^{alias}/([\d]*)/(.*)$',
			'redirect'	    => '{e_PLUGIN}download/download.php?action=view&id=$1',
			'sef'           => '{alias}/{download_id}/{download_sef}'
		);

		$config['get']     = array(
			'regex'		    => '^{alias}/get/([\d]*)/(.*)$',
			'sef'           => '{alias}/get/{download_id}/{download_sef}',
			'redirect'	    => '{e_PLUGIN}download/request.php?id=$1', 		// file-path of what to load when the regex returns true.
		);


		$config['index'] = array(
			'regex'		    => '{alias}/?$',
			'sef'		    => '{alias}',
			'redirect'	    => '{e_PLUGIN}download/download.php',
		);


		return $config;
	}



}