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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/import_user_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
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
		'user_id' 			=> 0,
		'user_name' 		=> '',
		'user_loginname'	=> '',
		'user_password'		=> '',
		'user_customtitle' 	=> '',
		'user_sess' 		=> '',			// Photo
		'user_email' 		=> '',
		'user_signature' 	=> '',
		'user_image' 		=> '',			// Avatar
		'user_hideemail' 	=> 1,
		'user_join'			=> 0,
		'user_realm'		=> 0,
		'user_pwchange'		=> 0,
		'user_lastvisit' 	=> 0,
		'user_currentvisit' => 0,
		'user_lastpost' 	=> 0,
		'user_chats' 		=> 0,
		'user_comments' 	=> 0,
		'user_ip' 			=> '',
		'user_ban' 			=> 0,
		'user_prefs' 		=> '',
	 //	'user_viewed' => '',
		'user_visits' 		=> 0,
		'user_admin' 		=> 0,
		'user_login'		=> '',			// User real name
		'user_class' 		=> '',
		'user_perms' 		=> '',
		'user_xup' 			=>  ''
	);


  // Fields which are defaulted at save-time if not previously set
	var $userSpecial = array('user_join', 'user_realm', 'user_pwchange');

  // Fields which must be set up by the caller.  
	var $userMandatory = array(
		'user_name', 'user_loginname', 'user_password'
	);
  	
	// Array is set up with the predefined extended fields which are actually in use
	var $actualExtended = array();


  // Constructor
	function user_import()
	{
		$this->userDB = new db;			// Have our own database object to write to the user table
		$this->actualExtended = e107::getUserExt()->getFieldNames(); // Create list of predefined extended user fields which are present
	}


  // Empty the user DB - by default leaving only the main admin.
	function emptyTargetDB($inc_admin = FALSE)
	{
	    
	    if ($inc_admin === TRUE)
		{
			$this->blockMainAdmin = FALSE;
			$delClause = '';
			$extClause = '';
		}
		else
		{
			$this->blockMainAdmin = TRUE;
			$delClause = 'user_id != 1 AND user_perms != "0" ';
			$extClause = 'user_extended_id != 1';
		}

		if($this->userDB->delete('user',$delClause))
		{
			e107::getMessage()->addDebug("Emptied User table");	
		}
		
		if($this->userDB->delete('user_extended',$extClause))
		{
			e107::getMessage()->addDebug("Emptied User-extended table");	
		}
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
	    if ($this->blockMainAdmin && isset($userRecord['user_id']) && ($userRecord['user_id'] == 1))
		{
			e107::getMessage()->addDebug("Skipping user record of main-admin");
			return true;
			// return 1;
		}
		
		$extendedFields = array();
		$userFields 	= array_keys($this->userDefaults);
		
		foreach ($userRecord as $k => $v)
		{
			if (!in_array($k, $userFields)) // Not present in e107_user table. 
			{
		    	if (in_array($k,$this->actualExtended)) // Present in e107_user_extended table. 
				{
					 $extendedFields[$k] = $v;		// Pull out any extended field values which are needed
				}
				else 
				{
					e107::getMessage()->addDebug("Removing user-field due to missing user-extended field {$k} ");	
				}			
				
				unset($userRecord[$k]);			// And always delete from the original data record
			}
		}
				
		foreach ($userRecord as $k => $v) // Check only valid fields being passed
		{	
		  	if (!array_key_exists($k,$this->userDefaults) && !in_array($k,$this->userSpecial) && !in_array($k,$this->userMandatory)   )         //
			{
				e107::getMessage()->addDebug("Failed on {$k} => {$v} ");
	  			return 2;
			}
		}
		// Check user names for invalid characters
		$userRecord['user_name'] = $this->vetUserName($userRecord['user_name'],FALSE);
		$userRecord['user_loginname'] = $this->vetUserName($userRecord['user_loginname'],FALSE);
		
		if (($userRecord['user_name'] === FALSE) || ($userRecord['user_name'] === FALSE))
		{
			e107::getMessage()->addDebug("user_name was empty");
			return 5;
		}
		
		if (trim($userRecord['user_name']) == '') $userRecord['user_name'] = trim($userRecord['user_loginname']);
		if (trim($userRecord['user_loginname']) == '') $userRecord['user_loginname'] = trim($userRecord['user_name']);
		
		foreach ($this->userMandatory as $k)
		{
			if (!isset($userRecord[$k]))
			{
				e107::getMessage()->addDebug("Failed userMandatory on {$k}");
				return 3;
			}
			
			if (strlen($userRecord[$k]) < 3)
			{
			//	e107::getMessage()->addDebug("Failed userMandatory length on {$k}");
				// return 3;
			}
		}
		
		if (!isset($userRecord['user_join'])) $userRecord['user_join'] = time();
		
		$userRecord['user_realm'] 		= '';		// Never carry across these fields
	    $userRecord['user_pwchange'] 	= 0;
	
		if(!$result = $this->userDB->replace('user',$userRecord))
		{
	     	return 4;
		}
	
		if (count($extendedFields))
		{
			$extendedFields['user_extended_id'] = varset($userRecord['user_id'],0) ? $userRecord['user_id'] : $result;
			
			if($this->userDB->replace('user_extended',$extendedFields) === false)
			{
				e107::getMessage()->addDebug("Failed to insert extended fields: ".print_a($extendedFields));
				return 6;
			}

		}
		return TRUE;
	}
 

 
	function getErrorText($errnum)    // these errors are presumptuous and misleading. especially '4' .
	{
		$errorTexts = array(
	    	0 => LAN_CONVERT_57, 
	    	1 => LAN_CONVERT_58, 
	    	2 => LAN_CONVERT_59,
			3 => LAN_CONVERT_60, 
			4 => LAN_CONVERT_61, 
			5 => LAN_CONVERT_62,
			6 => LAN_CONVERT_63
		);
		
		if (isset($errorTexts[$errnum])) return $errorTexts[$errnum];
		return 'Unknown: '.$errnum;
	}
}




class userclass_import
{
	var $ucdb = null;
	var $blockMainAdmin = true;
	var $error;

	var $defaults = array(
		'userclass_id'              => 0,
		'userclass_name'            => 0,
		'userclass_description'     => 0,
		'userclass_editclass'       => 0,
		'userclass_parent'          => 0,
		'userclass_accum'           => 0,
		'userclass_visibility'      => 0,
		'userclass_type'            => 0,
		'userclass_icon'            => 0,
		'userclass_perms'           => 0,

	);

	// Fields which must be set up by the caller.
	var $mandatory = array(
		'userclass_name'
	);

	// Constructor
	function __construct()
	{
	    $this->ucdb = e107::getDb('pagechapter');	// Have our own database object to write to the table
	}


	// Empty the  DB
	function emptyTargetDB($inc_admin = FALSE)
	{
		$this->ucdb->truncate('userclass_classes');

	}


	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->defaults[$key])) return FALSE;
		$this->defaults[$key] = $value;
	}


  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{

		if(empty($row['userclass_name']))
		{
			return 3;
		}


		if(!$result = $this->ucdb->insert('userclass_classes',$row))
		{
	     	return 4;
		}

		//if ($result === FALSE) return 6;

		return true;
	}



	function getErrorText($errnum)    // these errors are presumptuous and misleading. especially '4' .
	{
		$errorTexts = array(
	    	0 => LAN_CONVERT_57, 
	    	1 => LAN_CONVERT_58, 
	    	2 => LAN_CONVERT_59,
			3 => LAN_CONVERT_60, 
			4 => LAN_CONVERT_61, 
			5 => LAN_CONVERT_62,
			6 => LAN_CONVERT_63
		);

		if (isset($errorTexts[$errnum])) return $errorTexts[$errnum];

		return $this->ucdb->getLastErrorText();

	}



}



?>
