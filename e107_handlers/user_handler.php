<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Handler - user-related functions
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/user_handler.php,v $
 * $Revision: 1.18 $
 * $Date: 2009-11-18 01:04:43 $
 * $Author: e107coders $
 *
*/


/*
USER HANDLER CLASS - manages login and various user functions

Vetting routines TODO:
	user_sess processing
	user_image processing
	user_xup processing - nothing special?
*/


if (!defined('e107_INIT')) { exit; }

// Codes for `user_ban` field (not all used ATM)
define('USER_VALIDATED',0);
define('USER_BANNED',1);
define('USER_REGISTERED_NOT_VALIDATED',2);
define('USER_EMAIL_BOUNCED', 3);
define('USER_BOUNCED_RESET', 4);
define('USER_TEMPORARY_ACCOUNT', 5);


define('PASSWORD_E107_MD5',0);
define('PASSWORD_E107_SALT',1);

define('PASSWORD_E107_ID','$E$');			// E107 salted


define('PASSWORD_INVALID', FALSE);
define('PASSWORD_VALID',TRUE);
define ('PASSWORD_DEFAULT_TYPE',PASSWORD_E107_MD5);
//define ('PASSWORD_DEFAULT_TYPE',PASSWORD_E107_SALT);

// Required language file - if not loaded elsewhere, uncomment next line
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_user.php');

class UserHandler
{
	var $userVettingInfo = array();
	var $preferred = PASSWORD_DEFAULT_TYPE;			// Preferred password format
	var $passwordOpts = 0;							// Copy of pref
	var $passwordEmail = FALSE;						// True if can use email address to log in
	var $otherFields = array();

	// Constructor
	function UserHandler()
	{
	  global $pref;

/*
	Table of vetting methods for user data - lists every field whose value could be set manually.
	Valid 'vetMethod' values (use comma separated list for multiple vetting):
		0 - Null method
		1 - Check for duplicates
		2 - Check against $pref['signup_disallow_text']

	Index is the destination field name. If the source index name is different, specify 'srcName' in the array.

	Possible processing options:
		'dbClean'		- 'sanitising' method for final value:
							- 'toDB' - passes final value through $tp->toDB()
							- 'intval' - converts to an integer
							- 'image'  - checks image for size
							- 'avatar' - checks an image in the avatars directory
		'stripTags'		- strips HTML tags from the value (not an error if there are some)
		'minLength'		- minimum length (in utf-8 characters) for the string
		'maxLength'		- minimum length (in utf-8 characters) for the string
		'longTrim'		- if set, and the string exceeds maxLength, its trimmed
		'enablePref'	- value is processed only if the named $pref evaluates to true; otherwise any input is discarded without error
*/
	$this->userVettingInfo = array(
		'user_name' => array('niceName'=> LAN_USER_01, 'fieldType' => 'string', 'vetMethod' => '1,2', 'vetParam' => 'signup_disallow_text', 'srcName' => 'username', 'stripTags' => TRUE, 'stripChars' => '/&nbsp;|\#|\=|\$/', fixedBlock => 'anonymous', 'minLength' => 2, 'maxLength' => varset($pref['displayname_maxlength'],15)),				// Display name
		'user_loginname' => array('niceName'=> LAN_USER_02, 'fieldType' => 'string', 'vetMethod' => '1', 'vetParam' => '', 'srcName' => 'loginname', 'stripTags' => TRUE, 'stripChars' => '/&nbsp;|\#|\=|\$/', 'minLength' => 2, 'maxLength' => varset($pref['loginname_maxlength'],30)),			// User name
		'user_login' => array('niceName'=> LAN_USER_03, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'realname', 'dbClean' => 'toDB'),				// Real name (no real vetting)
		'user_customtitle' => array('niceName'=> LAN_USER_04, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'customtitle', 'dbClean' => 'toDB', 'enablePref' => 'signup_option_customtitle'),		// No real vetting
		'user_password' => array('niceName'=> LAN_USER_05, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'password1', 'dataType' => 2, 'minLength' => varset($pref['signup_pass_len'],1)),
		'user_sess' => array('niceName'=> LAN_USER_06, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'stripChars' => "#\"|'|(|)#", 'dbClean' => 'image', 'imagePath' => e_UPLOAD.'avatars/', 'maxHeight' => varset($pref['im_height'], 100), 'maxWidth' => varset($pref['im_width'], 120)),				// Photo
		'user_image' => array('niceName'=> LAN_USER_07, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'image', 'stripChars' => "#\"|'|(|)#", 'dbClean' => 'avatar', 'maxHeight' => varset($pref['im_height'], 100), 'maxWidth' => varset($pref['im_width'], 120)),				// Avatar
		'user_email' => array('niceName'=> LAN_USER_08, 'fieldType' => 'string', 'vetMethod' => '1,3', 'vetParam' => '', 'fieldOptional' => varset($pref['disable_emailcheck'],0), 'srcName' => 'email', 'dbClean' => 'toDB'),
		'user_signature' => array('niceName'=> LAN_USER_09, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'signature', 'dbClean' => 'toDB'),
		'user_hideemail' => array('niceName'=> LAN_USER_10, 'fieldType' => 'int', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'hideemail', 'dbClean' => 'intval'),
		'user_xup' => array('niceName'=> LAN_USER_11, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'user_xup', 'dbClean' => 'toDB'),
		'user_class' => array('niceName'=> LAN_USER_12, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'class', 'dataType' => '1')
	);

		$this->otherFields = array(
			'user_join'			=> LAN_USER_14,
			'user_lastvisit'	=> LAN_USER_15,
			'user_currentvisit'	=> LAN_USER_16,
			'user_comments'		=> LAN_USER_17,
			'user_ip'			=> LAN_USER_18,
			'user_ban'			=> LAN_USER_19,
			'user_prefs'		=> LAN_USER_20,
			'user_visits'		=> LAN_USER_21,
			'user_admin'		=> LAN_USER_22,
			'user_perms'		=> LAN_USER_23,
			'user_pwchange'		=> LAN_USER_24
//			user_chats int(10) unsigned NOT NULL default '0',
			);
		$this->otherFieldTypes = array(
			'user_join'			=> 'int',
			'user_lastvisit'	=> 'int',
			'user_currentvisit'	=> 'int',
			'user_comments'		=> 'int',
			'user_ip'			=> 'string',
			'user_ban'			=> 'int',
			'user_prefs'		=> 'string',
			'user_visits'		=> 'int',
			'user_admin'		=> 'int',
			'user_perms'		=> 'string',
			'user_pwchange'		=> 'int'
			);

	  $this->passwordOpts = varset($pref['passwordEncoding'],0);
	  $this->passwordEmail = varset($pref['allowEmailLogin'],FALSE);
	  switch ($this->passwordOpts)
	  {
	    case 1 :
		case 2 :
		  $this->preferred = PASSWORD_E107_SALT;
		  break;
		case 0 :
		default :
		  $this->preferred = PASSWORD_E107_MD5;
		  $this->passwordOpts = 0;		// In case it got set to some stupid value
		  break;
	  }
	  return FALSE;
	}


	// Given plaintext password and login name, generate password string to store in DB
	function HashPassword($password, $login_name, $force='')
	{
	  if ($force == '') $force = $this->preferred;
	  switch ($force)
	  {
		case PASSWORD_E107_MD5 :
		  return md5($password);

		case PASSWORD_E107_SALT :
		  return PASSWORD_E107_ID.md5(md5($password).$login_name);
		  break;
	  }
	  return FALSE;
	}


	// Verify existing plaintext password against a stored hash value (which defines the encoding format and any 'salt')
	// Return PASSWORD_INVALID if invalid password
	// Return PASSWORD_VALID if valid password
	// Return a new hash to store if valid password but non-preferred encoding
	function CheckPassword($password, $login_name, $stored_hash)
	{
	  if (strlen(trim($password)) == 0) return PASSWORD_INVALID;
	  if (($this->passwordOpts <= 1) && (strlen($stored_hash) == 32))
	  {	// Its simple md5 encoding
		if (md5($password) !== $stored_hash) return PASSWORD_INVALID;
		if ($this->preferred == PASSWORD_E107_MD5) return PASSWORD_VALID;
		return $this->HashPassword($password);		// Valid password, but non-preferred encoding; return the new hash
	  }

	  // Allow the salted password even if disabled - for those that do try to go back!
//  	  if (($this->passwordOpts >= 1) && (strlen($stored_hash) == 35) && (substr($stored_hash,0,3) == PASSWORD_E107_ID))
  	  if ((strlen($stored_hash) == 35) && (substr($stored_hash,0,3) == PASSWORD_E107_ID))
	  {	// Its the standard E107 salted hash
		$hash = $this->HashPassword($password, $login_name, PASSWORD_E107_SALT);
		if ($hash === FALSE) return PASSWORD_INVALID;
		return ($hash == $stored_hash) ? PASSWORD_VALID : PASSWORD_INVALID;
	  }

	  return PASSWORD_INVALID;
	}


	// Verifies a standard response to a CHAP challenge
	function CheckCHAP($challenge, $response, $login_name, $stored_hash )
	{
	  if (strlen($challenge) != 40) return PASSWORD_INVALID;
	  if (strlen($response) != 32) return PASSWORD_INVALID;
	  $valid_ret = PASSWORD_VALID;
	  if (strlen($stored_hash) == 32)
	  {	// Its simple md5 password storage
		$stored_hash = PASSWORD_E107_ID.md5($stored_hash.$login_name);			// Convert to the salted format always used by CHAP
		if ($this->passwordOpts != PASSWORD_E107_MD5) $valid_ret = $stored_response;
	  }
	  $testval = md5(substr($stored_hash,strlen(PASSWORD_E107_ID)).$challenge);
	  if ($testval == $response) return $valid_ret;
	  return PASSWORD_INVALID;
	}



	// Checks whether the user has to validate a user setting change by entering password (basically, if that field affects the
	// stored password value)
	// Returns TRUE if change required, FALSE otherwise
	function isPasswordRequired($fieldName)
	{
		if ($this->preferred == PASSWORD_E107_MD5) return FALSE;
		switch ($fieldName)
		{
			case 'user_email' :
				return $this->passwordEmail;
			case 'user_loginname' :
				return TRUE;
		}
		return FALSE;
	}


	// Determines whether its necessary to store a separate password for email address validation
	function needEmailPassword()
	{
		if ($this->preferred == PASSWORD_E107_MD5) return FALSE;
		if ($this->passwordEmail) return TRUE;
		return FALSE;
	}


	// Checks whether the password value can be converted to the current default
	// Returns TRUE if conversion possible.
	// Returns FALSE if conversion not possible, or not needed
	function canConvert($password)
	{
	  if ($this->preferred == PASSWORD_E107_MD5) return FALSE;
	  if (strlen($password) == 32) return TRUE;		// Can convert from md5 to salted
	  return FALSE;
	}


	// Given md5-encoded password and login name, generate password string to store in DB
	function ConvertPassword($password, $login_name)
	{
	  if ($this->canConvert($password) === FALSE) return $password;
	  return PASSWORD_E107_ID.md5($password.$login_name);
	}



	// Generates a random user login name according to some pattern.
	// Checked for uniqueness.
	function generateUserLogin($pattern, $seed='')
	{
	  $ul_sql = new db;
	  if (strlen($pattern) < 6) $pattern = '##....';
	  do
	  {
		$newname = $this->generateRandomString($pattern, $seed);
	  } while ($ul_sql->db_Select('user','user_id',"`user_loginname`='{$newname}'"));
	  return $newname;
	}



	// Generates a random string - for user login name, password etc, according to some pattern.
	// Checked for uniqueness.
	// Pattern format:
	//		# - an alpha character
	//		. - a numeric character
	//		* - an alphanumeric character
	//		^ - next character from seed
	//		alphanumerics are included 'as is'
	function generateRandomString($pattern, $seed = '')
	{
		if (strlen($pattern) < 6)
			$pattern = '##....';

		$newname = '';

		// Create alpha [A-Z][a-z]
		$alpha = '';
		for($i = 65; $i < 91; $i++)
		{
			$alpha .= chr($i).chr($i+32);
		}
		$alphaLength = strlen($alpha) - 1;

		// Create digit [0-9]
		$digit = '';
		for($i = 48; $i < 57; $i++)
		{
			$digit .= chr($i);
		}
		$digitLength = strlen($digit) - 1;

		// Create alpha numeric [A-Z][a-z]
		$alphaNum = $alpha.$digit;
		$alphaNumLength = strlen($alphaNum) - 1;

		// Next character of seed (if used)
		$seed_ptr = 0;
		for ($i = 0, $patternLength = strlen($pattern); $i < $patternLength; $i++)
		{
			$c = $pattern[$i];
			switch ($c)
			{
				// Alpha only (upper and lower case)
				case '#' :
					$t = rand(0, $alphaLength);
					$newname .= $alpha[$t];
					break;

				// Numeric only - [0-9]
				case '.' :
					$t = rand(0, $digitLength);
					$newname .= $digit[$t];
					break;

				// Alphanumeric
				case '*' :
					$t = rand(0, $alphaNumLength);
					$newname .= $alphaNum[$t];
					break;

				// Next character from seed
				case '^' :
					if ($seed_ptr < strlen($seed))
					{
						$newname .= $seed[$seed_ptr];
						$seed_ptr++;
					}
					break;

				// (else just ignore other characters in pattern)
				default :
					if (strrpos($alphaNum, $c) !== FALSE)
					{
						$newname .= $c;
					}
/*
					else
					{
						$t = rand(0, $alphaNumLength);
						$newname .= $alphaNum[$t];
					}
*/
			}
		}
		return $newname;
	}



	// Split up an email address to check for banned domains.
	// Return false if invalid address. Otherwise returns a set of values to check
	function make_email_query($email, $fieldname = 'banlist_ip')
	{
	  global $tp;
	  $tmp = strtolower($tp -> toDB(trim(substr($email, strrpos($email, "@")+1))));	// Pull out the domain name
	  if ($tmp == '') return FALSE;
	  if (strpos($tmp,'.') === FALSE) return FALSE;
	  $em = array_reverse(explode('.',$tmp));
	  $line = '';
	  $out = array();
	  foreach ($em as $e)
	  {
		$line = '.'.$e.$line;
		$out[] = '`'.$fieldname."`='*{$line}'";
	  }
	  return implode(' OR ',$out);
	}



	function makeUserCookie($lode,$autologin = FALSE)
	{
		global $pref;
		$cookieval = $lode['user_id'].".".md5($lode['user_password']);		// (Use extra md5 on cookie value to obscure hashed value for password)
		if ($pref['user_tracking'] == "session")
		{
			$_SESSION[$pref['cookie_name']] = $cookieval;
		}
		else
		{
			if ($autologin == 1)
			{	// Cookie valid for up to 30 days
				cookie($pref['cookie_name'], $cookieval, (time() + 3600 * 24 * 30));
			}
			else
			{
				cookie($pref['cookie_name'], $cookieval);
			}
		}
	}


	// Generate an array of all the basic classes a user belongs to
	// if $asArray TRUE, returns results in an array; else as a comma-separated string
	// If $incInherited is TRUE, includes inherited classes
	function addCommonClasses($userData, $asArray = FALSE, $incInherited = FALSE)
	{
		if ($incInherited)
		{
			$classList = array();
			global $e_userclass;
			if (!isset($e_userclass) && !is_object($e_userclass))
			{
				require_once(e_HANDLER."userclass_class.php");
				$e_userclass = new user_class;
			}
			$classList = $e_userclass->get_all_user_classes($var['user_class']);
		}
		else
		{
			if ($userData['user_class'] != '') $classList = explode(',',$userData['user_class']);
		}
		foreach (array(e_UC_MEMBER, e_UC_READONLY, e_UC_PUBLIC) as $c)
		{
			if (!in_array($c,$classList))
			{
				$classList[] = $c;
			}
		}
		if ((varset($userData['user_admin'],0) == 1) && strlen($userData['user_perms']))
		{
		  $classList[] = e_UC_ADMIN;
		  if (strpos($userData['user_perms'],'0') === 0)
		  {
			$classList[] = e_UC_MAINADMIN;
		  }
		}
		if ($asArray) return $classList;
		return implode(',',$classList);
	}


	// Return an array of descriptive names for each field in the user DB. If $all is false, just returns the modifiable ones. Else returns all
	function getNiceNames($all = FALSE)
	{
//		$ret = array('user_id' => LAN_USER_13);
		foreach ($this->userVettingInfo as $k => $v)
		{
			$ret[$k] = $v['niceName'];
		}
		if ($all)
		{
			$ret = array_merge($ret, $this->otherFields);
		}
		return $ret;
	}
//===================================================
//			User Field validation
//===================================================

/*	$_POST field names:

	DB				signup			usersettings	quick add		function
  ------------------------------------------------------------------------------
  user_id 			-				user_id			-				Unique user ID
  user_name 		name$			username		username		Display name
  user_loginname	loginname		loginname 		loginname		User name (login name)
  user_customtitle 	-				customtitle		-				Custom title
  user_password 	password1		password1		password1		Password (prior to encoding)
					password2		password2		password1		(Check password field)
  user_sess 						*				-				Photo (file on server)
  user_email		email			email			email			Email address
					email_confirm
  user_signature 	signature		signature		-				User signature
  user_image		image			image*			-				Avatar (may be external URL or file on server)
  user_hideemail	hideemail		hideemail		-				Flag to hide user's email address
  user_login		realname		realname		realname		User Real name
  user_xup			xupexist$		user_xup		-				XUP file link
  user_class		class			class			userclass		User class (array on form)

user_loginname may be auto-generated
* avatar (user_image) and photo (user_sess) may be uploaded files
$changed to match the majority vote

Following fields auto-filled in code as required:
  user_join
  user_lastvisit
  user_currentvisit
  user_chats
  user_comments
  user_forums
  user_ip
  user_ban
  user_prefs
  user_viewed
  user_visits
  user_admin
  user_perms
  user_pwchange

*/
	// Function does validation specific to user data. Updates the $targetData array as appropriate.
	// Returns TRUE if nothing updated; FALSE if errors found (only checks data previously passed as good)
	function userValidation(&$targetData)
	{
		global $e107, $pref;
		$u_sql = new db;
		$ret = TRUE;
		$errMsg = '';
		if (isset($targetData['data']['user_email']))
		{
			$v = trim($targetData['data']['user_email']);		// Always check email address if its entered
			if ($v == '')
			{
				if (!varsettrue($pref['disable_emailcheck']))
				{
					$errMsg = ERR_MISSING_VALUE;
				}
			}
			elseif (!check_email($v))
			{
				$errMsg = ERR_INVALID_EMAIL;
			}
			elseif ($u_sql->db_Count('user', '(*)', "WHERE `user_email`='".$v."' AND `user_ban`=1 "))
			{
				$errMsg = ERR_BANNED_USER;
			}
			else
			{	// See if email address banned
				$wc = $this->make_email_query($v);		// Generate the query for the ban list
				if ($wc) { $wc = "`banlist_ip`='{$v}' OR ".$wc;  }
				if (($wc === FALSE) || !$e107->check_ban($wc, FALSE, TRUE))
				{
//					echo "Email banned<br />";
					$errMsg = ERR_BANNED_EMAIL;
				}
			}
			if ($errMsg)
			{
				unset($targetData['data']['user_email']);			// Remove the valid entry
			}
		}
		else
		{
			if (!isset($targetData['errors']['user_email']) && !varset($pref['disable_emailcheck'],FALSE))
			{	// We may have already picked up an error on the email address - or it may be allowed to be empty
				$errMsg = ERR_MISSING_VALUE;
			}
		}
		if ($errMsg)
		{	// Update the error
			$targetData['errors']['user_email'] = $errMsg;
			$targetData['failed']['user_email'] = $v;
			$ret = FALSE;
		}
		return $ret;
	}

	// Given an array of user data intended to be written to the DB, adds empty strings (or other default value) for any field which doesn't have a default in the SQL definition.
	// (Avoids problems with MySQL in STRICT mode.).
	// Returns TRUE if additions made, FALSE if no change.
	function addNonDefaulted(&$userInfo)
	{
//		$nonDefaulted = array('user_signature' => '', 'user_prefs' => '', 'user_class' => '', 'user_perms' => '');
		$nonDefaulted = array('user_signature' => '', 'user_prefs' => '', 'user_class' => '', 'user_perms' => '', 'user_realm' => '');	// Delete when McFly finished
		$ret = FALSE;
		foreach ($nonDefaulted as $k => $v)
		{
			if (!isset($userInfo[$k]))
			{
				$userInfo[$k] = $v;
				$ret = TRUE;
			}
		}
		return $ret;
	}


	// Delete time-expired partial registrations from the user DB, clean up user_extended table
	function deleteExpired($force = FALSE)
	{
		global $pref, $sql;
		$temp1 = 0;
		if (isset($pref['del_unv']) && $pref['del_unv'] && $pref['user_reg_veri'] != 2)
		{
			$threshold= intval(time() - ($pref['del_unv'] * 60));
			if (($temp1 = $sql->db_Delete('user', 'user_ban = 2 AND user_join < '.$threshold)) > 0) { $force = TRUE; }
		}
		if ($force)
		{	// Remove 'orphaned' extended user field records
			$sql->db_Select_gen("DELETE `#user_extended` FROM `#user_extended` LEFT JOIN `#user` ON `#user_extended`.`user_extended_id` = `#user`.`user_id`
					WHERE `#user`.`user_id` IS NULL");
		}
		return $temp1;
	}


	// Called to update initial user classes, probationary user class etc
	function userClassUpdate(&$user, $event='userveri')
	{
		global $pref, $tp;

		$initClasses = array();
		$doClasses = FALSE;
		$doProbation = FALSE;
		$ret = FALSE;
		switch ($event)
		{
			case 'userall' :
				$doClasses = TRUE;
				$doProbation = TRUE;
				break;
			case 'userfull' :		// A 'fully fledged' user
				if (!$pref['user_reg_veri'] || ($pref['init_class_stage'] == '2'))
				{
					$doClasses = TRUE;
				}
				$doProbation = TRUE;
				break;
			case 'userpartial' :
				if ($pref['init_class_stage'] == '1')
				{	// Set initial classes if to be done on partial signup, or if selected to add them now
					$doClasses = TRUE;
				}
				$doProbation = TRUE;
				break;
		}
		if ($doClasses)
		{
			if (isset($pref['initial_user_classes'])) { $initClasses = explode(',',$pref['initial_user_classes']); }	 // Any initial user classes to be set at some stage
			if ($doProbation && (varset($pref['user_new_period'], 0) > 0))
			{
				$initClasses[] = e_UC_NEWUSER;		// Probationary user class
			}
			if (count($initClasses))
			{	// Update the user classes
				if ($user['user_class'])
				{
					$initClasses = array_unique(array_merge($initClasses, explode(',',$user['user_class'])));
				}
				$user['user_class'] = $tp->toDB(implode(',',$initClasses));
				$ret = TRUE;
			}
		}

	}
}

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_administrator.php");

class e_userperms
{
	protected $core_perms = array(
	
		"1"=> ADMSLAN_19,
		"2"=> ADMSLAN_20,
		"3"=> ADMSLAN_21,
		"4"=> ADMSLAN_22,	// Moderate users/bans etc
		"5"=> ADMSLAN_23,	// create/edit custom pages
        "J"=> ADMSLAN_41,	// create/edit custom menus
		"Q"=> ADMSLAN_24,	// Manage download categories
		"6"=> ADMSLAN_25,	// Upload /manage files
		"Y"=> ADMSLAN_67,	// file inspector
		"O"=> ADMSLAN_68,	// notify
		"7"=> ADMSLAN_26,
		"8"=> ADMSLAN_27,
		"C"=> ADMSLAN_64,
		"9"=> ADMSLAN_28,
		"W"=> ADMSLAN_65,
    	"D"=> ADMSLAN_29,
		"E"=> ADMSLAN_30,
		"F"=> ADMSLAN_31,
		"G"=> ADMSLAN_32,
		"S"=> ADMSLAN_33,
		"T"=> ADMSLAN_34,
		"V"=> ADMSLAN_35,
		"X"=> ADMSLAN_66,
		"A"=> ADMSLAN_36,	// Configure Image Settings
		"B"=> ADMSLAN_37,
		"H"=> ADMSLAN_39,
		"I"=> ADMSLAN_40,
		"L"=> ADMSLAN_43,
		"R"=> ADMSLAN_44,
		"U"=> ADMSLAN_45,
		"M"=> ADMSLAN_46,
		"N"=> ADMSLAN_47,
	//	"Z"=> ADMSLAN_62,
	);
	
	protected $plugin_perms = array();
	
	protected $language_perms = array();
	
	protected $main_perms = array();
	
	protected $permSectionDiz = array(
		'core'		=> ADMSLAN_74,
		'plugin'	=> ADLAN_CL_7,
		'language'	=> ADLAN_132,
		'main'		=> ADMSLAN_58
	 );
	
	
	function __construct()
	{
		
		
		$sql = e107::getDb('sql2');
		$tp = e107::getParser();
		
		
		$sql->db_Select("plugin", "*", "plugin_installflag='1'");
		while ($row2 = $sql->db_Fetch())
		{
			$this->plugin_perms[("P".$row2['plugin_id'])] = LAN_PLUGIN." - ".$tp->toHTML($row2['plugin_name'], FALSE, 'RAWTEXT,defs');
		}	
		
		asort($this->plugin_perms);
		
		$this->plugin_perms = array("Z"=>ADMSLAN_62) + $this->plugin_perms;
		
		if(e107::getConfig()->getPref('multilanguage'))
		{
			$lanlist = explode(",",e_LANLIST);
			sort($lanlist);
			foreach($lanlist as $langs)
			{
				$this->language_perms[$langs] = $langs;
			}
		}
		
		if(getperms('0'))
		{
			$this->main_perms = array('0' => ADMSLAN_58);
		}
		
	}
	
	function renderSectionDiz($key)
	{
		return $this->permSectionDiz[$key];	
	}
	
	
	function getPermList($type='all')
	{
		if($type == 'core')
		{
			return $this->core_perms;
		}
		if($type == 'plugin')
		{
			return $this->plugin_perms;
		}
		if($type == 'language')
		{
			return $this->language_perms;
		}
		if($type == 'main')
		{
			return $this->main_perms;
		}
		
		if($type == 'grouped')
		{
			$ret = array();
			$ret['core'] 		= $this->core_perms;
			$ret['plugin']		= $this->plugin_perms;
			
			if(vartrue($this->language_perms))
			{
				$ret['language'] = $this->language_perms;
			}
			
			if(vartrue($this->main_perms))
			{
				$ret['main'] = $this->main_perms;
			}
		
			return $ret;
				
		}
			
		return array_merge($this->core_perms,$this->plugin_perms,$this->language_perms,$this->main_perms);
	}
	
	function checkb($arg, $perms, $label='')
	{
		$frm = e107::getForm();
		
		$par = "<div class='field-spacer'>";
		$par .= $frm->checkbox('perms[]', $arg, getperms($arg, $perms));
		if ($label)
		{
			$par .= $frm->label($label,'perms[]', $arg);
		}
		$par .= "</div>\n";
	
		return $par;
	}
	
	function renderPerms($perms,$uniqueID='')
	{
		$tmp = explode(".",$perms);
		$permdiz = $this->getPermList();
		$ptext = array();
		
		foreach($tmp as $p)
		{
			$ptext[] = $permdiz[$p];
		}		
		
		$id = "id_".$uniqueID;
		
		
		$text = "<div onclick=\"e107Helper.toggle('id_{$id}')\" class='e-pointer' title='".ADMSLAN_71."'>{$perms}</div>";
		
		if(varset($ptext))
		{
			$text .= "<div id='id_{$id}' class='e-hideme'><ul><li>".implode("</li><li>",$ptext)."</li></ul></div>";
		}
		
		/*
		$text = "<a href='#".$id."' class='e-expandit' title='".ADMSLAN_71."'>{$perms}</a>";
		
		if(varset($ptext))
		{
			$text .= "<div class='e-hideme' id='".$id."' ><ul><li>".implode("</li><li>",$ptext)."</li></ul></div>";
		}
			*/	
	    return $text;
	}
	
	/**
	 * Render edit admin perms form. 
	 *
	 * @param array $row [optional] containing $row['user_id'], $row['user_name'], $row['user_perms'];
	 * @return void
	 */
	function edit_administrator($row = '')
	{
		$pref = e107::getPref();
		$lanlist = explode(",", e_LANLIST);
		require_once(e_HANDLER."user_handler.php");
		$prm = $this;
		$ns = e107::getRender();
		$sql = e107::getDb();
		$frm = e107::getForm();
		
	
		$a_id = $row['user_id'];
		$ad_name = $row['user_name'];
		$a_perms = $row['user_perms'];
	
		$text = "
			<form method='post' action='".e_SELF."' id='myform'>
				<fieldset id='core-administrator-edit'>
					<legend class='e-hideme'>".ADMSLAN_52."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".ADMSLAN_16.": </td>
								<td class='control'>
									".$ad_name."
									<input type='hidden' name='ad_name' size='60' value='{$ad_name}' />
								</td>
							</tr>
							<tr>
								<td class='label'>".ADMSLAN_18."</td>
								<td class='control'>
	
		";
				
		$groupedList = $prm->getPermList('grouped');
			
		foreach($groupedList as $section=>$list)
		{
			$text .= "\t\t<div class='field-section'><h4>".$prm->renderSectionDiz($section)."</h4>"; //XXX Lan - General	
			foreach($list as $key=>$diz)
			{
				$text .= $prm->checkb($key, $a_perms, $diz);			
			}
			$text .= "</div>";
		}
	
		$text .= "<div class='field-section'>
			".$frm->admin_button('check_all', 'jstarget:perms', 'action', LAN_CHECKALL)."
			".$frm->admin_button('uncheck_all', 'jstarget:perms', 'action', LAN_UNCHECKALL)."
			</div>
		</td>
		</tr>
				</tbody>
					</table>
					<div class='buttons-bar center'>
						<input type='hidden' name='a_id' value='{$a_id}' />
						".$frm->admin_button('update_admin', ADMSLAN_52, 'update')."
						".$frm->admin_button('go_back', ADMSLAN_70)."
					</div>
				</fieldset>
			</form>
		";
	
		$ns->tablerender(ADMSLAN_52, $text);
	}
	
	/**
	 * Update user (admin) permissions.
	 * NOTE: exit if $uid is not an integer or is 0.
	 *
	 * @param integer $uid
	 * @param array $permArray eg. array('A', 'K', '1');
	 * @return void 
	 */
	function updatePerms($uid, $permArray)
	{
		global $admin_log;
		
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		$modID = intval($uid);
		if ($modID == 0)
		{
			exit();
		}
		
		$sql->db_Select("user", "*", "user_id=".$modID);
		$row = $sql->db_Fetch();
		$a_name = $row['user_name'];
	
		$perm = "";
	
		foreach($permArray as $value)
		{
			$value = $tp->toDB($value);
			if ($value == "0")
			{
				if (!getperms('0')) { $value = ""; break; }
				$perm = "0"; break;
			}
	
			if ($value)
			{
				$perm .= $value.".";
			}
	  }
	
		admin_update($sql->db_Update("user", "user_perms='{$perm}' WHERE user_id='{$modID}' "), 'update', sprintf(ADMSLAN_2, $tp->toDB($_POST['ad_name'])), false, false);
		$logMsg = str_replace(array('--ID--', '--NAME--'),array($modID, $a_name),ADMSLAN_72).$perm;
		$admin_log->log_event('ADMIN_01',$logMsg,E_LOG_INFORMATIVE,'');
	}

}
