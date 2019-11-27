<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class user_user // plugin-folder + '_user'
{		
		
/*	function profile($udata)  // display on user profile page.
	{

		$var = array(
			0 => array('label' => "Label", 'text' => "Some text to display", 'url'=> e_PLUGIN_ABS."_blank/blank.php")
		);
		
		return $var;
	}*/


	/**
	 * Experimental and subject to change without notice.
	 * @return mixed
	 */
	function delete($uid)
	{
		$us = e107::getUserSession();

		$config = array();

		$config['user'] =  array(
		//	'user_id'           => '[primary]',
			'user_name'         => 'Deleted-User-'.$uid,
			'user_loginname'    => 'Deleted-Login-'.$uid,
			'user_email'        => 'noreply-'.$uid.'@nowhere.com',
			'user_ip'           => '',
			'user_lastvisit'    => time(),
			'user_password'     => $us->HashPassword($us->generateRandomString("#??????????#"), 'Deleted-Login-'.$uid),
			'user_ban'          => 5, // 'deleted' status'
			// etc.
			'WHERE'             => 'user_id = '.$uid,
			'MODE'              => 'update'
		);

		$config['user_extended'] = array(
			'WHERE'             => 'user_extended_id = '.$uid,
			'MODE'              => 'delete'
		);

		return $config;

	}


	
}