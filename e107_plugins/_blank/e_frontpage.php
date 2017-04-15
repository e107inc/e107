<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

if (!defined('e107_INIT')) { exit; }

//v2.x spec.
class _blank_frontpage // include plugin-folder in the name.
{
	// simple
	function config()
	{

		$frontPage = array('page' => '{e_PLUGIN}_blank/_blank.php', 'title' => LAN_PLUGIN__BLANK_NAME);

		return $frontPage;
	}


	
	// multiple
	/*function config()
	{
		$config = array();

		$config['title']    = LAN_PLUGIN__BLANK_NAME;
		$config['page']     = array(
						0   => array('page' => '{e_PLUGIN}_blank/_blank.php', 'title'=>'Main Page'),
		);

		return $config;
	}
	*/




}



?>