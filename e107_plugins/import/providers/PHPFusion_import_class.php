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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/PHPFusion_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box

require_once('import_classes.php');

class PHPFusion_import extends base_import_class
{
	
	public $title		= 'PHP Fusion';
	public $description	= 'Based on V5.1';
	public $supported	=  array('users');
	public $mprefix		= false;
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
	    if ($this->ourDB == NULL) return FALSE;
	    switch ($task)
		{
		  case 'users' :
		    $result = $this->ourDB->db_Select_gen("SELECT * FROM {$this->DBPrefix}users");
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
	if ($this->copyUserInfo) $target['user_id'] = $source['user_id'];
	$target['user_name'] = $source['user_name'];
	$target['user_loginname'] = $source['user_name'];
	$target['user_password'] = $source['user_password'];
	$target['user_email'] = $source['user_email'];
	$target['user_hideemail'] = $source['user_hide_email'];
    $target['user_image'] = $source['user_avatar'];
	$target['user_signature'] = $source['user_sig'];
	$target['user_forums'] = $source['user_posts'];
	$target['user_join'] = $source['user_joined'];
	$target['user_lastvisit'] = $source['user_lastvisit'];
	$target['user_location'] = $source['user_location'];
	$target['user_birthday'] = $source['user_birthdate'];
	$target['user_aim'] = $source['user_aim'];
	$target['user_icq'] = $source['user_icq'];
	$target['user_msn'] = $source['user_msn'];
	$target['user_yahoo'] = $source['user_yahoo'];
	$target['user_homepage'] = $source['user_web'];
	$target['user_timezone'] = $source['user_offset'];		// guess - may need conversion
	$target['user_ip'] = $source['user_ip'];
//	$target['user_'] = $source[''];
//	$target['user_'] = $source[''];
	
//	$target['user_ban'] = ($source['user_status'] ? 2 : 0);					// Guess
	return $target;
  }

}


?>
