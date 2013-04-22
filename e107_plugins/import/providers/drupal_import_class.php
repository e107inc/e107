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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/drupal_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */


// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box
// Module based on Drupal 5.7 and 6.1 schemas; may well work with other versions
//$import_class_names['drupal_import'] = 'Drupal 5.7/6.1';
//$import_class_comment['drupal_import'] = 'Basic import';
//$import_class_support['drupal_import'] = array('users');
//$import_default_prefix['drupal_import'] = '';

require_once('import_classes.php');

class drupal_import extends base_import_class
{
	
	public $title		= 'Drupal 5 - 8';
	public $description	= 'Basic import';
	public $supported	= array('users');
	public $mprefix		= false;
	
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
  // If $blank_user is true, certain cross-referencing user info is to be zeroed
	function setupQuery($task, $blank_user=FALSE)
	{
	    if ($this->ourDB == NULL) return FALSE;
		
	    switch ($task)
		{
		  case 'users' :
		    $result = $this->ourDB->db_Select_gen("SELECT * FROM {$this->DBPrefix}users WHERE `status`=1");
			if ($result === FALSE) return FALSE;
			break;
		
		  case 'polls' :
		    return FALSE;
		  case 'news' :
		    return FALSE;
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
	function copyUserData(&$target, &$source) // http://drupal.org/files/er_db_schema_drupal_7.png
	{
		if ($this->copyUserInfo)
		{
			$target['user_id'] 			= $source['uid'];
			$target['user_name'] 		= $source['name'];
			$target['user_loginname'] 	= $source['name'];
			$target['user_password'] 	= $source['pass'];
			$target['user_email'] 		= $source['mail'];
			$target['user_signature'] 	= $source['signature'];
			$target['user_join'] 		= $source['created'];			
			$target['user_lastvisit'] 	= $source['login'];		// Could use $source['access']
		    $target['user_image'] 		= $source['picture'];
			// $source['init'] is email address used to sign up from
		    $target['user_timezone'] 	= $source['timezone'];		// May need conversion varchar(8)
		    $target['user_language'] 	= $source['language'];		// May need conversion varchar(12)
		    
			return $target;
		}
	}
}


?>
