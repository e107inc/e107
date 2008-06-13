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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/user_handler.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-13 20:20:21 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/


/*
USER HANDLER CLASS - manages login and various user functions

*/


if (!defined('e107_INIT')) { exit; }


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


class UserHandler
{
	var $preferred = PASSWORD_DEFAULT_TYPE;			// Preferred password format
	var $passwordOpts = 0;							// Copy of pref
	var $passwordEmail = FALSE;						// True if can use email address to log in

	// Constructor
	function UserHandler()
	{
	  global $pref;
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
	function generateRandomString($pattern, $seed='')
	{
	  if (strlen($pattern) < 6) $pattern = '##....';
	  $newname = '';
	  $seed_ptr = 0;			// Next character of seed (if used)
	  for ($i = 0; $i < strlen($pattern); $i++)
	  {
		$c = $pattern[$i];
		switch ($c)
		{
		  case '#' :		// Alpha only (upper and lower case)
			do
			{
			  $t = chr(rand(65,122));
			} while (!ctype_alpha($t));
			$newname .= $t;
			break;
		  case '.' :		// Numeric only
			do
			{
			  $t = chr(rand(48,57));
			} while (!ctype_digit($t));
			$newname .= $t;
			break;
		  case '*' :		// Alphanumeric
			do
			{
			  $t = chr(rand(48,122));
			} while (!ctype_alnum($t));
			$newname .= $t;
			break;
		  case '^' :		// Next character from seed
			if ($seed_ptr < strlen($seed))
			{
			  $newname .= $seed[$seed_ptr];
			  $seed_ptr++;
			}
			break;
		  default :
			if (ctype_alnum($c)) $newname .= $c;
			// (else just ignore other characters in pattern)
		}
	  }
	  return $newname;
	}



	// Split up an email address to check for banned domains.
	// Return false if invalid address. Otherwise returns a set of values to check
	function make_email_query($email, $fieldname = 'banlist_ip')
	{
	  global $tp;
	  $tmp = strtolower($tp -> toDB(trim(substr($email, strrpos($email, "@")+1))));
	  if ($tmp == '') return FALSE;
	  if (strpos($tmp,'.') === FALSE) return FALSE;
	  $em = array_reverse(explode('.',$tmp));
	  $line = '';
	  $out = array();
	  foreach ($em as $e)
	  {
		$line = '.'.$e.$line;
		$out[] = $fieldname."='*{$line}'";
	  }
	  return implode(' OR ',$out);
	}



	// Validate a standard user field (for length, acceptable characters etc).
	// Returns TRUE if totally acceptable
	// If $justStrip is FALSE, returns FALSE for an unacceptable value
	// If $justStrip is TRUE, usually returns a new value (based on that passed) which does validate - usually characters stripped, length trimmed etc
	//		Note: will return FALSE for some input values regardless of the setting of $justStrip
	// Currently coded to always return TRUE if field name not recognised
	function validateField($fieldName,$fieldValue, $justStrip = FALSE)
	{
	  global $pref;
	  $newValue = $fieldValue;
	  switch ($fieldName)
	  {
	    case 'user_loginname' :
		  $newValue = trim(preg_replace('/&nbsp;|\#|\=|\$/', "", strip_tags($fieldValue)));
		  $newValue = substr($newValue,0,varset($pref['loginname_maxlength'],30));
		  if (strlen($newValue) < 2) return FALSE;			// Always an error if a short string 
		  break;
		case 'user_password' :
		  if (strlen($fieldValue) < $pref['signup_pass_len']) return FALSE;
		  break;
	  }
	  if ($justStrip)
	  {
		return $newValue;
	  }
	  else
	  {
	    return ($newValue == $fieldValue);
	  }
	}
	
	
	// Takes an array of $_POST fields whose first characters match $prefix, and passes them through the validateField routine
	// Returns three arrays - one of validated results, one of failed fields and one of errors corresponding to the failed fields
	function validatePostList($prefix = '', $doToDB = TRUE, $justStrip = FALSE)
	{
	  global $tp;
	  $ret = array('validate' => array(), 'failed' => array(), 'errors' => array());
	  foreach ($_POST as $k => $v)
	  {
		if (($prefix == '') || (strpos($k,$prefix) === 0))
		{  // Field to validate
		  $result = $this->validateField($k,$v,$justStrip);
		  if ($result === FALSE)
		  {  // error
			$ret['failed'][$k] = $v;
			$ret['errors'][$k] = TRUE;
		  }
		  else
		  {
			if ($doToTB) $result = $tp->toDB($result);
			$ret['validate'][$k] = $result;
		  }
		}
	  }
	  return $ret;
	}

	// Takes an array of $_POST field names specified in comma-separated form in $fieldlist (blank = 'all'), and passes them through the validateField routine
	// Returns three arrays - one of validated results, one of failed fields and one of errors corresponding to the failed fields
	function validatePostFields($fieldList = '', $doToDB = TRUE, $justStrip = FALSE)
	{
	  global $tp;
	  $ret = array('validate' => array(), 'failed' => array(), 'errors' => array());
	  if ($fieldList == '')
	  {
	    $fieldArray = array_keys($_POST);
	  }
	  else
	  {
		$fieldArray = explode(',',$fieldList);
	  }
	  foreach ($fieldArray as $k)
	  {
		$k = trim($k);
		$result = $this->validateField($k,$_POST[$k],$justStrip);
		if ($result === FALSE)
		{  // error
		  $ret['failed'][$k] = $_POST[$k];
		  $ret['errors'][$k] = TRUE;
		}
		else
		{
		  if ($doToTB) $result = $tp->toDB($result);
		  $ret['validate'][$k] = $result;
		}
	  }
	  return $ret;
	}


}

?>
