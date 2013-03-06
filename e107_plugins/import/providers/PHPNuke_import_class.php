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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/PHPNuke_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box

require_once('import_classes.php');

class PHPNuke_import extends base_import_class
{
	
	
	public $title			= 'PHP Nuke';
	public $description		= 'Supports users only - uses PHPBB2';
	public $supported		= array('users');
	public $mprefix			= 'nuke_';

	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		if ($this->ourDB == NULL) return FALSE;
		switch ($task)
		{
		  case 'users' :
		    $result = $this->ourDB->db_Select_gen("SELECT * FROM {$this->DBPrefix}users WHERE `user_active`=1");
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
  // Very similar to PHPBB fields (as far as the bits we can convert are concerned)
  function copyUserData(&$target, &$source)
  {
	if ($this->copyUserInfo) $target['user_id'] = $source['user_id'];
	$target['user_name'] = $source['username'];
	$target['user_loginname'] = $source['username'];
	$target['user_loginname'] = $source['name'];
	$target['user_password'] = $source['user_password'];
	$target['user_join'] = strtotime($source['user_regdate']);
	$target['user_email'] = $source['user_email'];
	$target['user_hideemail'] = $source['user_viewemail'];
    $target['user_image'] = $source['user_avatar'];
	$target['user_signature'] = $source['user_sig'];
	$target['user_forums'] = $source['user_posts'];
	$target['user_lastvisit'] = $source['user_lastvisit'];

	switch ($source['user_avatar_type'])
	{
	  default: 
	    $target['user_image'] = $source['user_avatar'];
	}
	$target['user_timezone'] = $source['user_timezone'];		// source is decimal(5,2)
	$target['user_language'] = $source['user_lang'];			// May need conversion
	$target['user_location'] = $source['user_from'];
	$target['user_icq'] = $source['user_icq'];
	$target['user_aim'] = $source['user_aim'];
	$target['user_yahoo'] = $source['user_yim'];
	$target['user_msn'] = $source['user_msnm'];
	$target['user_homepage'] = $source['user_website'];
	$target['user_ip'] = $source['last_ip'];
//	$target['user_'] = $source[''];
	
//	$source['user_rank'];
//	$target['user_admin'] = ($source['user_level'] == 1) ? 1 : 0;		// Guess
//	if ($target['user_admin'] != 0) $target['user_perms'] = '0.';
//	$target['user_ban'] = ($source['ublockon'] ? 2 : 0);					// Guess
	return $target;
  }

}


?>
