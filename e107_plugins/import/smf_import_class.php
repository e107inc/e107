<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/import/smf_import_class.php,v $
|     $Revision: 1.1 $
|     $Date: 2009-07-20 15:24:34 $
|     $Author: e107coders $
|
+----------------------------------------------------------------------------+
*/

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box
$import_class_names['smf_import'] = 'SMF (Simple Machines Forum)';
$import_class_comment['smf_import'] = 'Supports users only';
$import_class_support['smf_import'] = array('users');
$import_default_prefix['smf_import'] = '';


require_once('import_classes.php');

class smf_import extends base_import_class
{
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
  function setupQuery($task, $blank_user=FALSE)
  {
    if ($this->ourDB == NULL) return FALSE;
    switch ($task)
	{
	  case 'users' :
	    $result = $this->ourDB->db_Select_gen("SELECT * FROM {$this->DBPrefix}members WHERE `is_activated`=1");
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
	if ($this->copyUserInfo) $target['user_id'] = $source['ID_MEMBER'];
	$target['user_name'] = $source['realName'];
	$target['user_login'] = $source['realName'];
	$target['user_loginname'] = $source['memberName'];
	$target['user_password'] = $source['passwd'];				// Check - could be plaintext
	$target['user_email'] = $source['emailAddress'];
	$target['user_hideemail'] = $source['hideEmail'];
    $target['user_image'] = $source['avatar'];
	$target['user_signature'] = $source['signature'];
	$target['user_forums'] = $source['posts'];
	$target['user_chats'] = $source['instantMessages'];
	$target['user_join'] = $source['dateRegistered'];
	$target['user_lastvisit'] = $source['lastLogin'];
	$target['user_homepage'] = $source['websiteUrl'];
	$target['user_location'] = $source['location'];
	$target['user_icq'] = $source['ICQ'];
	$target['user_aim'] = $source['AIM'];
	$target['user_yahoo'] = $source['YIM'];
	$target['user_msn'] = $source['MSN'];
	$target['user_timezone'] = $source['timeOffset'];			// Probably needs formatting
	$target['user_customtitle'] = $source['usertitle'];
	$target['user_ip'] = $source['memberIP'];
//	$target['user_'] = $source[''];
//	$target['user_'] = $source[''];
	
//    $target['user_language'] = $source['lngfile'];			// Guess to verify
	return $target;
  }

}


?>
