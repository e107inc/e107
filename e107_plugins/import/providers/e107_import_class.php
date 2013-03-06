<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/e107_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// This must be an incredibly pointless file! But it does allow testing of the basic plugin structure.

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box
//$import_class_names['e107_import'] = 'E107';
//$import_class_comment['e107_import'] = 'Reads 0.7 and 0.8 version files';
//$import_class_support['e107_import'] = array('users');
//$import_default_prefix['e107_import'] = 'e107_';


require_once('import_classes.php');

class e107_import extends base_import_class
{
	
	
	public $title		= 'e107';
	public $description	= 'Reads 0.7 and 0.8 version files';
	public $supported	= array('users');
	public $mprefix		= 'e107_';
	
	
	
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
	    if ($this->ourDB == NULL) return FALSE;
	    switch ($task)
		{
		  case 'users' :
		  	$query =  "SELECT * FROM {$this->DBPrefix}user WHERE `user_id` != 1";
		    $result = $this->ourDB->db_Select_gen($query);
	
			if ($result === FALSE) return FALSE;
			break;
	
	
			
		  default :
		    return FALSE;
		}
		$this->copyUserInfo = !$blank_user;
		$this->currentTask = $task;
		
		return TRUE;
  }


  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  
  // Copy data read from the DB into the record to be returned.
	function copyUserData(&$target, &$source)
	{
		if ($this->copyUserInfo)
		{
			$target['user_id'] = $source['user_id'];
			$target['user_name'] = $source['user_name'];
			$target['user_loginname'] = $source['user_loginname'];
			$target['user_password'] = $source['user_password'];
			$target['user_email'] = $source['user_email'];
			$target['user_hideemail'] = $source['user_hideemail'];
			$target['user_join'] = $source['user_join'];
			$target['user_admin'] = $source['user_admin'];
			$target['user_lastvisit'] = $source['user_lastvisit'];
			$target['user_login'] = $source['user_login'];
			$target['user_ban'] = $source['user_ban'];
			$target['user_customtitle'] = $source['user_customtitle'];
			$target['user_sess'] = $source['user_sess'];			// Photo
			$target['user_signature'] = $source['user_signature'];
			$target['user_image'] = $source['user_image'];			// Avatar
			$target['user_currentvisit'] = $source['user_currentvisit'];
			$target['user_lastpost'] = $source['user_lastpost'];
			$target['user_chats'] = $source['user_chats'];
			$target['user_comments'] = $source['user_comments'];
		//	$target['user_forums'] = $source['user_forums'];
			$target['user_ip'] = $source['user_ip'];
			$target['user_prefs'] = $source['user_prefs'];
		 //	$target['user_viewed'] = $source['user_viewed'];
			$target['user_visits'] = $source['user_visits'];
			$target['user_class'] = $source['user_class'];
			$target['user_perms'] = $source['user_perms'];
			$target['user_xup'] = $source['user_xup'];
			$target['user_language'] = $source['user_language'];
			$target['user_country'] = $source['user_country'];
			$target['user_location'] = $source['user_location'];
			$target['user_aim'] = $source['user_aim'];
			$target['user_icq'] = $source['user_icq'];
			$target['user_yahoo'] = $source['user_yahoo'];
			$target['user_msn'] = $source['user_msn'];
			$target['user_homepage'] = $source['user_homepage'];
			$target['user_birthday'] = $source['user_birthday'];
			$target['user_timezone'] = $source['user_timezone'];
			
			return $target;
		}
	}
}


?>