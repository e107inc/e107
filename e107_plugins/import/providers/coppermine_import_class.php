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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/coppermine_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box
// Info derived from version 1.4.16
//$import_class_names['coppermine_import'] = 'Coppermine';
//$import_class_comment['coppermine_import'] = 'Standalone gallery version';
//$import_class_support['coppermine_import'] = array('users');
//$import_default_prefix['coppermine_import'] = 'CPG_';


require_once('import_classes.php');

class coppermine_import extends base_import_class
{
	
	public $title		= 'Coppermine';
	public $description	= 'Standalone gallery version';
	public $supported	= array('users');
	public $mprefix		= 'CPG_';
	
	
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
  function setupQuery($task, $blank_user=FALSE)
  {
    if ($this->ourDB == NULL) return FALSE;
    switch ($task)
	{
	  case 'users' :
	    $result = $this->ourDB->db_Select_gen("SELECT * FROM {$this->DBPrefix}users WHERE `user_active`='YES' ");
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
	$target['user_name'] 		= $source['user_name'];
	$target['user_loginname'] 	= $source['user_name'];
	$target['user_login'] 		= $source['user_name'];
	$target['user_password'] 	= $source['user_password'];
	$target['user_email'] 		= $source['user_email'];
	$target['user_join'] 		= strtotime($source['user_regdate']);
	$target['user_lastvisit'] 	= strtotime($source['user_lastvisit']);
	
	switch ($source['user_group'])
	{
	  case 1 : 		// Admin
		$target['user_admin'] = 1;
		break;
	  case 2 :		// Ordinary member
	  case 3 :		// Anonymous
	    break;
	  case 4 :		// Banned
		$target['user_ban'] = 2;
		break;
	}
	
	return $target;

	/* Unused fields:
  user_group int(11) NOT NULL default '2',		2 = 'member'.
  user_group_list varchar(255) NOT NULL default '',
  */
  }
}



