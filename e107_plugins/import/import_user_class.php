<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/import_user_class.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-17 13:48:44 $
 * $Author: marj_nl_fr $
 */

/*
Class intended to simplify importing of user information from outside.
It ensures that each user record has appropriate defaults

To use:
	1. Create one instance of the class
	2. Call emptyUserDB() to delete existing users
	3. If necessary, call overrideDefault() as necessary to modify the defaults
	4. For each record:
		a) Call getDefaults() to get a record with all the defaults filled in
		b) Update the record from the source database
		c) Call saveUser($userRecord) to write the record to the DB
*/

class user_import
{
  var $userDB = NULL;
  var $blockMainAdmin = TRUE;
  var $error;

// Every field must be in exactly one of the arrays $userDefaults, $userSpecial, $userMandatory  
  var $userDefaults = array(
	'user_id' => 0,
	'user_customtitle' => '',
	'user_sess' => '',			// Photo
	'user_email' => '',
	'user_signature' => '',
	'user_image' => '',			// Avatar
	'user_hideemail' => 1,
	'user_lastvisit' => 0,
	'user_currentvisit' => 0,
	'user_lastpost' => 0,
	'user_chats' => 0,
	'user_comments' => 0,
	'user_ip' => '',
	'user_ban' => 0,
	'user_prefs' => '',
 //	'user_viewed' => '',
	'user_visits' => 0,
	'user_admin' => 0,
	'user_login' => '',			// User real name
	'user_class' => '',
	'user_perms' => '',
	'user_xup' =>  ''
  );


  // Fields which are defaulted at save-time if not previously set
  var $userSpecial = array('user_join', 'user_realm', 'user_pwchange');

  // Fields which must be set up by the caller.  
  var $userMandatory = array(
	'user_name', 'user_loginname', 'user_password'
  );
  
  
  // Predefined fields which may appear in the extended user fields
  var $userExtended = array(
		'user_language',
		'user_country',
		'user_location',
		'user_aim',
		'user_icq',
		'user_yahoo',
		'user_msn',
		'user_homepage',
		'user_birthday',
		'user_timezone'
		);
		
  // Array is set up with those predefined extended fields which are actually in use
  var $actualExtended = array();


  // Constructor
  function user_import()
  {
  	global $sql;
    $this->userDB = new db;			// Have our own database object to write to the user table

	// Create list of predefined extended user fields which are present
	if($ret = getcachedvars("userdata_{$uid}"))
	{
	  foreach ($this->userExtended as $v)
	  {
	    if (isset($ret[$v])) $this->actualExtended[] = $v;
	  }
	}
  }


  // Empty the user DB - by default leaving only the main admin.
  function emptyTargetDB($inc_admin = FALSE)
  {
    $delClause = '';
    if ($inc_admin === TRUE)
	{
	  $this->blockMainAdmin = FALSE;
	}
	else
	{
	  $this->blockMainAdmin = TRUE;
	  $delClause = 'user_id != 1';
	}
	$this->userDB->db_Delete('user',$delClause);
	$this->userDB->db_Delete('user_extended',$delClause);
  }
  
  
  // Set a new default for a particular field
  function overrideDefault($key, $value)
  {
//    echo "Override: {$key} => {$value}<br />";
    if (!isset($this->userDefaults[$key])) return FALSE;
	$this->userDefaults[$key] = $value;
  }

  
  // Returns an array with all relevant fields set to the current default
  function getDefaults()
  {
	return $this->userDefaults;
  }


  // Vet a user or login name. If OK, always returns the name.
  // On error, if $just_strip true, returns 'processed' name; otherwise returns FALSE
  function vetUserName($name, $just_strip = FALSE)
  {
	$temp_name = trim(preg_replace('/&nbsp;|\#|\=|\$/', "", strip_tags($name)));
	if (($temp_name == $name) || $just_strip) return $temp_name;
	return FALSE;
  }


  // Add a user record to the DB - pass array as parameter. 
  // Returns an error code on failure. TRUE on success
  function saveData($userRecord)
  {
    if ($this->blockMainAdmin && isset($userRecord['user_id']) && ($userRecord['user_id'] == 1)) return 1;
	$extendedFields = array();
	foreach ($userRecord as $k => $v)
	{
	  if (in_array($k,$this->userExtended))
	  {
	    if (in_array($k,$this->actualExtended)) $extendedFields[$k] = $v;		// Pull out any extended field values which are needed
		unset($userRecord[$k]);			// And always delete from the original data record
	  }
	}
	foreach ($userRecord as $k => $v)
	{	// Check only valid fields being passed
	  	if (!array_key_exists($k,$this->userDefaults) && !in_array($k,$this->userSpecial) && !in_array($k,$this->userMandatory)   )         //
		{
			echo "Failed on {$k} => {$v} <br />";
  			return 2;
		}
	}
	// Check user names for invalid characters
	$userRecord['user_name'] = $this->vetUserName($userRecord['user_name'],FALSE);
	$userRecord['user_loginname'] = $this->vetUserName($userRecord['user_loginname'],FALSE);
	if (($userRecord['user_name'] === FALSE) || ($userRecord['user_name'] === FALSE)) return 5;
	
	if (trim($userRecord['user_name']) == '') $userRecord['user_name'] = trim($userRecord['user_loginname']);
	if (trim($userRecord['user_loginname']) == '') $userRecord['user_loginname'] = trim($userRecord['user_name']);
	foreach ($this->userMandatory as $k)
	{
	  if (!isset($userRecord[$k])) return 3;
	  if (strlen($userRecord[$k]) < 3) return 3;
	}
	if (!isset($userRecord['user_join'])) $userRecord['user_join'] = time();
	$userRecord['user_realm'] = '';		// Never carry across these fields
    $userRecord['user_pwchange'] = 0;

	if(!$result = $this->userDB->db_Insert('user',$userRecord))
	{
     	return 4;
	}

	if (count($extendedFields))
	{
	  $extendedFields['user_extended_id'] = varset($userRecord['user_id'],0) ? $userRecord['user_id'] : $result;
	  $result = $this->userDB->db_Insert('user_extended',$extendedFields);
	  if ($result === FALSE) return 6;
	}
	return TRUE;
  }
 

 
  function getErrorText($errnum)    // these errors are presumptuous and misleading. especially '4' .
  {
    $errorTexts = array(0 => 'No error', 1 => 'Can\'t change main admin data', 2 => 'invalid field passed',
			3 => 'Mandatory field not set', 4 => 'User already exists', 5 => 'Invalid characters in user or login name',
			6 => 'Error saving extended user fields');
	if (isset($errorTexts[$errnum])) return $errorTexts[$errnum];
	return 'Unknown: '.$errnum;
  }
}


?>