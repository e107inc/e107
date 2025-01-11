<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * XXX HIGHLY EXPERIMENTAL AND SUBJECT TO CHANGE WITHOUT NOTICE. 
*/

if (!defined('e107_INIT')) { exit; }


class forum_event
{

	function config()
	{

		$event = array();

		$event[] = array(
			'name'	=> "login", 
			'function'	=> "forum_eventlogin"
		);

		$event[] = array(
			'name'	=> "user_forum_post_created",
			'function'	=> "forum_eventnewpost"
		);
		return $event;

	}


	function forum_eventlogin($data) // Clear user_plugin_forum_viewed on user LOGIN
	{
		/*$myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
		$txt = "login (".USERID.")\n";
		fwrite($myfile, $txt);
		fwrite($myfile, print_r($data,true));
		fclose($myfile);
		echo('hola');
		print_a($data);*/
		e107::getDb()->update('user_extended', "user_plugin_forum_viewed = NULL WHERE user_extended_id = ".$data['user_id']);
		
	}

	function forum_eventnewpost($data) // Remove thread id from user_plugin_forum_viewed when a new reply is posted
	{
		//e107::getDb()->update('user_extended', "user_plugin_forum_viewed = REPLACE(user_plugin_forum_viewed, '{$data[post_thread]}', '0') WHERE user_extended_id != ".$data[post_user]); 
		e107::getDb()->update('user_extended', "user_plugin_forum_viewed = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_plugin_forum_viewed, ','), ',".$data['post_thread'].",', ',')) WHERE FIND_IN_SET('".$data['post_thread']."', user_plugin_forum_viewed) AND user_extended_id != ".$data['post_user']);

	}




} //end class

