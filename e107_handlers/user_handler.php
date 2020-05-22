<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Handler - user-related functions
 *
*/


/**
 *
 *	@package     e107
 *	@subpackage	e107_handlers
 *
 *	USER HANDLER CLASS - manages login and various user functions
 *
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
define('PASSWORD_E107_PHP', 3); // PHP Default - Using the bcrypt algorithm (default as of PHP 5.5.0).

define('PASSWORD_E107_ID','$E$');			// E107 salted


define('PASSWORD_INVALID', false);
define('PASSWORD_VALID',TRUE);
define ('PASSWORD_DEFAULT_TYPE',PASSWORD_E107_MD5);
//define ('PASSWORD_DEFAULT_TYPE',PASSWORD_E107_SALT);

// Required language file - if not loaded elsewhere, uncomment next line
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_user.php');

class UserHandler
{
	var $userVettingInfo = array();
	var $preferred = PASSWORD_DEFAULT_TYPE;			// Preferred password format
	var $passwordOpts = 0;							// Copy of pref
	var $passwordEmail = false;						// True if can use email address to log in
	private $otherFields = array();
	private $passwordAPI = false;

	// Constructor
	public function __construct()
	{
		$pref = e107::getPref();
		e107::lan('core','user');

		if(function_exists('password_verify'))
		{
			$this->passwordAPI = true;
		}

/**
	Table of vetting methods for user data - lists every field whose value could be set manually.
	Valid 'vetMethod' values (use comma separated list for multiple vetting):
		0 - Null method
		1 - Check for duplicates
		2 - Check against $pref['signup_disallow_text']
		3 - Check email address against remote server, only if option enabled

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
		'user_name' => array('niceName'=> LAN_USER_01, 'fieldType' => 'string', 'vetMethod' => '1,2', 'vetParam' => 'signup_disallow_text', 'srcName' => 'username', 'stripTags' => TRUE, 'stripChars' => '/&nbsp;|\#|\=|\$/', 'fixedBlock' => 'anonymous', 'minLength' => 2, 'maxLength' => varset($pref['displayname_maxlength'],15)),				// Display name
		//'user_loginname' => array('niceName'=> LAN_USER_02, 'fieldType' => 'string', 'vetMethod' => '1', 'vetParam' => '', 'srcName' => 'loginname', 'stripTags' => TRUE, 'stripChars' => '#[^a-z0-9_\.]#i', 'minLength' => 2, 'maxLength' => varset($pref['loginname_maxlength'],30)),			// User name
		'user_loginname' => array('niceName'=> LAN_USER_02, 'fieldType' => 'string', 'vetMethod' => '1', 'vetParam' => '', 'srcName' => 'loginname', 'stripTags' => TRUE, 'stripChars' => '#[^\p{L}\p{M}a-z0-9_\.]#ui', 'minLength' => 2, 'maxLength' => varset($pref['loginname_maxlength'],30)),			// User name
		'user_login' => array('niceName'=> LAN_USER_03, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'realname', 'dbClean' => 'toDB', 'stripTags' => TRUE, 'stripChars' => '#<|>#i'),				// Real name (no real vetting)
		'user_customtitle' => array('niceName'=> LAN_USER_04, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'customtitle', 'dbClean' => 'toDB', 'enablePref' => 'signup_option_customtitle', 'stripTags' => TRUE, 'stripChars' => '#<|>#i'),		// No real vetting
		'user_password' => array('niceName'=> LAN_PASSWORD, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'password1', 'dataType' => 2, 'minLength' => varset($pref['signup_pass_len'],1)),
		'user_sess' => array('niceName'=> LAN_USER_06, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'stripChars' => "#\"|'|(|)#", 'dbClean' => 'image', 'imagePath' => e_AVATAR_UPLOAD, 'maxHeight' => varset($pref['im_height'], 100), 'maxWidth' => varset($pref['im_width'], 120)),				// Photo
		'user_image' => array('niceName'=> LAN_USER_07, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'image', 'stripChars' => "#\"|'|(|)#", 'dbClean' => 'avatar'),	//, 'maxHeight' => varset($pref['im_height'], 100), 'maxWidth' => varset($pref['im_width'], 120) resized on-the-fly			// Avatar
		'user_email' => array('niceName'=> LAN_EMAIL, 'fieldType' => 'string', 'vetMethod' => '1,3', 'vetParam' => '', 'fieldOptional' => varset($pref['disable_emailcheck'],0), 'srcName' => 'email', 'dbClean' => 'toDB'),
		'user_signature' => array('niceName'=> LAN_USER_09, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'signature', 'dbClean' => 'toDB'),
		'user_hideemail' => array('niceName'=> LAN_USER_10, 'fieldType' => 'int', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'hideemail', 'dbClean' => 'intval'),
		'user_xup' => array('niceName'=> "XUP File", 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'user_xup', 'dbClean' => 'toDB'),
		'user_class' => array('niceName'=> LAN_USER_12, 'fieldType' => 'string', 'vetMethod' => '0', 'vetParam' => '', 'srcName' => 'class', 'dataType' => '1')
	);

	$this->otherFields = array(
			'user_join'			=> LAN_USER_14,
			'user_lastvisit'	=> LAN_USER_15,
			'user_currentvisit'	=> LAN_USER_16,
			'user_comments'		=> LAN_COMMENTS,
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

	  $this->passwordOpts = varset($pref['passwordEncoding'], 0);
	  $this->passwordEmail = varset($pref['allowEmailLogin'], false);

		switch ($this->passwordOpts)
		{
			case 3 :
				$this->preferred = PASSWORD_E107_PHP;
		        break;

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

		return false;
	}


	/**
	 * Return the code for the current default password hash-type
	 * @return int
	 */
	public function getDefaultHashType()
	{
		return $this->preferred;
	}


	/**
	 * Returns true if PHP5.5+ password API is found, otherwise return false.
	 * @return bool
	 */
	public function passwordAPIExists()
	{
		return $this->passwordAPI;
	}


	/**
	 * Check if a user posted field is readonly (should not be user-editable) - used in usersettings.php
	 * @param array $posted
	 * @return bool
	 */
	public function hasReadonlyField($posted)
	{
		$restricted = array_keys($this->otherFields);

		$pref = e107::getPref();

		if(empty($pref['signup_option_class']))
		{
			$restricted[] = 'user_class';
		}

		foreach($posted as $k=>$v)
		{
			if(in_array($k,$restricted))
			{
				return true;
			}
		}

		return false;

	}



	/**
	 * 	Given plaintext password and login name, generate password string to store in DB
	 *
	 *	@param string $password - plaintext password as entered by user
	 *	@param string $login_name - string used to log in (could actually be email address)
	 *	@param string $force empty| PASSWORD_E107_MD 5| PASSWORD_E107_SALT | PASSWORD_E107_PHP $force - if non-empty, forces a particular type of password
	 *
	 *	@return string|boolean - false if invalid emcoding method, else encoded password to store in DB
	 */
	public function HashPassword($password, $login_name='', $force=false)
	{
		if($force === false)
		{
			$force = $this->preferred;
		}

		if(($force == PASSWORD_E107_PHP) && $this->passwordAPI === false)
		{
			$force = PASSWORD_E107_SALT; // fallback.
		}

		switch ($force)
		{
			case PASSWORD_E107_MD5 :
				return md5($password);

			case PASSWORD_E107_SALT :
		        return PASSWORD_E107_ID.md5(md5($password).$login_name);
		        break;

			case PASSWORD_E107_PHP :
	            return password_hash($password, PASSWORD_DEFAULT);
		        break;
		}

		return false;

	}


	/**
	 *	Verify existing plaintext password against a stored hash value (which defines the encoding format and any 'salt')
	 *
	 *	@param string $password - plaintext password as entered by user
	 *	@param string $login_name - string used to log in (could actually be email address)
	 *	@param string $stored_hash - required value for password to match
	 *
	 *	@return string PASSWORD_INVALID|PASSWORD_VALID|string
	 *		PASSWORD_INVALID if no match
	 *		PASSWORD_VALID if valid password
	 *		Return a new hash to store if valid password but non-preferred encoding
	 */
	public function CheckPassword($password, $login_name, $stored_hash)
	{
		$password = trim($password);

		if(empty($password))
		{
			return PASSWORD_INVALID;
		}

		$type = $this->getHashType($stored_hash);

		switch($type)
		{
			case PASSWORD_E107_MD5://  &&
				if (md5($password) !== $stored_hash) return PASSWORD_INVALID;
				if ($this->preferred == PASSWORD_E107_MD5 && ($this->passwordOpts <= 1)) return PASSWORD_VALID;
				return $this->HashPassword($password);		// Valid password, but non-preferred encoding; return the new hash
				break;

			case PASSWORD_E107_SALT:
				$hash = $this->HashPassword($password, $login_name, PASSWORD_E107_SALT);
				if ($hash === false) return PASSWORD_INVALID;
				return ($hash == $stored_hash) ? PASSWORD_VALID : PASSWORD_INVALID;
				break;

			case PASSWORD_E107_PHP: // PHP 5.5+ Blowfish+
				if($this->passwordAPI === true && password_verify($password,$stored_hash))
				{
					return PASSWORD_VALID;
				}
				break;

		}

		return PASSWORD_INVALID;
	}


	/**
	 * If necessary, rehash the user password to the currently set algorythm and updated database. .
	 * @param array $user - user fields. required: user_id, user_loginname, user_password
	 * @param string $password - plain text password.
	 * @return bool|string returns new password hash on success or false.
	 */
	public function rehashPassword($user, $password)
	{
		$type = $this->getHashType($user['user_password']);

		if($type == $this->preferred || empty($user['user_id']) || empty($user['user_password']) || empty($user['user_loginname']))
		{
			return false;
		}

		$sql = e107::getDb();

		$newPasswordHash = $this->HashPassword($password, $user['user_loginname']);

		$update = array(

			'data' => array(
				'user_password' => $newPasswordHash,

			),
			'WHERE' => "user_id = ".intval($user['user_id'])." LIMIT 1",
			'_FIELD_TYPES' => array('user_password' => 'safestr'),

		);

		if($sql->update('user', $update)!==false)
		{
			return $newPasswordHash;
		}

		return false;

	}



	/**
	 * Detect Password Hash Algorythm type
	 * @param string $hash - Password hash to analyse
	 * @param string $mode - (optional) set to 'text' for a plain-text description.
	 * @return bool|int|array
	 */
	public function getHashType($hash, $mode='constant')
	{
		if(empty($hash))
		{
			return false;
		}

		$num = false;
		$name = '';

		if((strlen($hash) === 32))
		{
			$num = PASSWORD_E107_MD5;
			$name = 'md5';
		}
		elseif ((strlen($hash) === 35) && (substr($hash,0,3) == PASSWORD_E107_ID))
		{
			$num = PASSWORD_E107_SALT;
			$name = 'md5-salt';
		}
		elseif($this->passwordAPI)
		{
			$info = password_get_info($hash);
			if(!empty($info['algo']))
			{
				$num = PASSWORD_E107_PHP;
				$name = $info['algoName'];
			}
		}

		if($mode == 'array' && !empty($name))
		{
			return array($num,$name);
		}

		return $num;



	}



	/**
	 * Reset the user's password with an auto-generated string.
	 * @param $uid
	 * @param string $loginName (optional)
	 * @return bool|string rawPassword
	 */
	public function resetPassword($uid, $loginName='', $options=array())
	{
		if(empty($uid))
		{
			return false;
		}

		$rawPassword    = $this->generateRandomString(str_repeat('*', rand(8, 12)));
		$hash           = $this->HashPassword($rawPassword, $loginName);

		$updateQry = array(
			'data'          => array( 'user_password' => $hash ),
			'WHERE'         => 'user_id = '.intval($uid)." LIMIT 1",
			'_FIELD_TYPES'  => array( 'user_password' => 'safestr' 	)
		);

		if(e107::getDb()->update('user', $updateQry))
		{
			if(!empty($options['return']) && $options['return'] == 'array')
			{
				return array('password'=>$rawPassword, 'hash'=>$hash);
			}


			return $rawPassword;
		}


		return false;

	}



	/**
	 *	Verifies a standard response to a CHAP challenge
	 *
	 *	@param string $challenge - the string sent to the user
	 *	@param string $response - the response returned by the user
	 *	@param string $login_name - user's login name
	 *	@param string $stored_hash - password hash as stored in DB
	 *
	 *	@return PASSWORD_INVALID|PASSWORD_VALID
	 */
	public function CheckCHAP($challenge, $response, $login_name, $stored_hash )
	{
		if (strlen($challenge) != 40) return PASSWORD_INVALID;
		if (strlen($response) != 32) return PASSWORD_INVALID;
		$valid_ret = PASSWORD_VALID;
		if (strlen($stored_hash) == 32)
		{	// Its simple md5 password storage
			$stored_hash = PASSWORD_E107_ID.md5($stored_hash.$login_name);			// Convert to the salted format always used by CHAP
			if ($this->passwordOpts != PASSWORD_E107_MD5) $valid_ret = $stored_hash;
		}
		$testval = md5(substr($stored_hash,strlen(PASSWORD_E107_ID)).$challenge);
		if ($testval == $response) return $valid_ret;
		return PASSWORD_INVALID;
	}



	/**
	 *	Checks whether the user has to validate a change of user settings by entering password (basically, if that field affects the
	 *	stored password value)
	 *
	 *	@param string $fieldName - name of field being changed
	 *
	 *	@return bool TRUE if change required, false otherwise
	 */
	public function isPasswordRequired($fieldName)
	{
		if ($this->preferred == PASSWORD_E107_MD5) return false;
		switch ($fieldName)
		{
			case 'user_email' :
				return $this->passwordEmail;
			case 'user_loginname' :
				return TRUE;
		}
		return false;
	}



	/**
	 *	Determines whether its necessary to store a separate password for email address validation
	 *
	 *	@return bool TRUE if separate password
	 */
	public function needEmailPassword()
	{
		if ($this->preferred == PASSWORD_E107_MD5) return false;
		if ($this->passwordEmail) return TRUE;
		return false;
	}



	/**
	 *	Checks whether the password value can be converted to the current default
	 *
	 *	@param string $password - hashed password
	 *	@return bool TRUE if conversion possible, false if not possible, or not needed.
	 */
	public function canConvert($password)
	{
		if ($this->preferred == PASSWORD_E107_MD5) return false;
		if (strlen($password) == 32) return TRUE;		// Can convert from md5 to salted
		return false;
	}



	/**
	 *	Given md5-encoded password and login name, generate password string to store in DB
	 *
	 *	@param string $password - MD5-hashed password
	 *	@param string $login_name - user's login name
	 *
	 *	@return string hashed password to store in DB, converted as necessary
	 */
	public function ConvertPassword($password, $login_name)
	{
		if ($this->canConvert($password) === false) return $password;
		return PASSWORD_E107_ID.md5($password.$login_name);
	}



	/**
	 *	Generates a random user login name according to some pattern.
	 *	Checked for uniqueness.
	 *
	 *	@param string $pattern - defines the format of the username
	 *	@param int $seed - may be used with the random pattern generator
	 *
	 *	@return string a user login name, guaranteed unique in the database.
	 */
	public function generateUserLogin($pattern, $seed='')
	{
		$ul_sql = new db;
		if (strlen($pattern) < 6) $pattern = '##....';
		do
		{
			$newname = $this->generateRandomString($pattern, $seed);
		} while ($ul_sql->select('user','user_id',"`user_loginname`='{$newname}'"));
		return $newname;
	}



	/**
	 *	Generates a random string - for user login name, password etc, according to some pattern.
	 *	@param string $pattern - defines the output format:
	 *		# - an alpha character
	 *		. - a numeric character
	 *		* - an alphanumeric character
	 *      ! - symbol character
	 *      ? - alpha, numeric or symbol character.
	 *		^ - next character from seed
	 *		alphanumerics are included 'as is'
	 *	@param int $seed - may be used with the random pattern generator
	 *
	 *	@return string - the required random string
	 */
	public function generateRandomString($pattern='', $seed = '')
	{
		if (empty($pattern))
		{
			$pattern = '##....';
		}

		$newname = '';

		// Create alpha [A-Z][a-z]
		$alpha = 'AaBbCcDdEeFfGgHhIiJjKkLMmNnPpQqRrSsTtUuVvWwXxYyZz'; // O, o and l removed to avoid possible confusion with numbers.
		$alphaLength = strlen($alpha) - 1;

		// Create digit [0-9]
		$digit = '0123456789';
		$digitLength = strlen($digit) - 1;

		// Create alpha numeric [A-Z][a-z]
		$alphaNum = $alpha.$digit.chr(45).chr(95); // add support for - and _
		$alphaNumLength = strlen($alphaNum) - 1;

		$symbols = "~!@#$%^*-+?;:"; // avoid < > and quotes.
		$symbolsLength = strlen($symbols) - 1;

		$alphaNumSymbol = $alphaNum.$symbols;
		$alphaNumSymbolLength =  strlen($alphaNumSymbol) - 1;

		// Next character of seed (if used)
		$seed_ptr = 0;
		for ($i = 0, $patternLength = strlen($pattern); $i < $patternLength; $i++)
		{
			$c = $pattern[$i];
			switch ($c)
			{
				// Symbols only.
				case '!':
					$t = rand(0, $symbolsLength);
					$newname .= $symbols[$t];
					break;

				// Alphanumeric + Symbols (most secure)
				case '?':
					$t = rand(0, $alphaNumSymbolLength);
					$newname .= $alphaNumSymbol[$t];
					break;

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
					if (strrpos($alphaNum, $c) !== false)
					{
						$newname .= $c;
					}
			}
		}
		return $newname;
	}



	/**
	 *	Split up an email address to check for banned domains.
	 *	@param string $email - email address to process
	 *	@param string $fieldname - name of field being searched in DB
	 *
	 *	@return bool|string false if invalid address. Otherwise returns a set of values to check
	 *	Moved to IPHandler
	 */
	 /*
	public function make_email_query($email, $fieldname = 'banlist_ip')
	{
		return e107::getIPHandler()->makeEmailQuery($v, $fieldname);		// Valid 'stub' if required

		$tp = e107::getParser();
		$tmp = strtolower($tp -> toDB(trim(substr($email, strrpos($email, "@")+1))));	// Pull out the domain name
		if ($tmp == '') return false;
		if (strpos($tmp,'.') === false) return false;
		$em = array_reverse(explode('.',$tmp));
		$line = '';
		$out = array($fieldname."='*@{$tmp}'");		// First element looks for domain as email address
		foreach ($em as $e)
		{
			$line = '.'.$e.$line;
			$out[] = '`'.$fieldname."`='*{$line}'";
		}
		return implode(' OR ',$out);
	}
	*/



	/**
	 *	Create user cookie
	 *
	 *	@param array $lode - user information from DB - 'user_id' and 'user_password' required
	 *	@param bool $autologin - TRUE if the 'Remember Me' box ticked
	 *
	 *	@return void
	 */
	public function makeUserCookie($lode,$autologin = false)
	{
		$cookieval = $lode['user_id'].'.'.md5($lode['user_password']);		// (Use extra md5 on cookie value to obscure hashed value for password)
		if (e107::getPref('user_tracking') == 'session')
		{
			$_SESSION[e107::getPref('cookie_name')] = $cookieval;
		}
		else
		{
			if ($autologin == 1)
			{	// Cookie valid for up to 30 days
				cookie(e107::getPref('cookie_name'), $cookieval, (time() + 3600 * 24 * 30));
				$_COOKIE[e107::getPref('cookie_name')] = $cookieval; // make it available to the global scope before the page is reloaded
			}
			else
			{
				cookie(e107::getPref('cookie_name'), $cookieval);
				$_COOKIE[e107::getPref('cookie_name')] = $cookieval; // make it available to the global scope before the page is reloaded
			}
		}


	//	echo "Debug: making cookie: ".$cookieval ." from ".print_a($lode,true);
	//	exit;
	}


	/**
	 *	Generate an array of all the basic classes a user belongs to
	 *
	 *	Note that the passed data may relate to the currently logged in user, or if an admin is logged in, to a different user
	 *
	 *	@param array $userData - user's data record - must include the 'user_class' element
	 *	@param boolean $asArray if TRUE, returns results in an array; else as a comma-separated string
	 *	@param boolean $incInherited if TRUE, includes inherited classes
	 *	@param boolean $fromAdmin - if TRUE, adds e_UC_ADMIN and e_UC_MAINADMIN in if current user's entitlement permits
	 *
	 *	@return array|string of userclass information according to $asArray
	 */
	public function addCommonClasses($userData, $asArray = false, $incInherited = false, $fromAdmin = false)
	{
		if ($incInherited)
		{
			$classList = e107::getUserClass()->get_all_user_classes($userData['user_class']);
		}
		else
		{
			if (!empty($userData['user_class'])) $classList = explode(',',$userData['user_class']);
		}
		foreach (array(e_UC_MEMBER, e_UC_READONLY, e_UC_PUBLIC) as $c)
		{
			if (!in_array($c, vartrue($classList, array())))
			{
				$classList[] = $c;
			}
		}
		if (((varset($userData['user_admin'],0) == 1) && strlen($userData['user_perms'])) || ($fromAdmin && ADMIN))
		{
			$classList[] = e_UC_ADMIN;
			if ((strpos($userData['user_perms'],'0') === 0) || getperms('0'))
			{
				$classList[] = e_UC_MAINADMIN;
			}
		}
		if ($asArray) return $classList;
		return implode(',',$classList);
	}


	/**
	 * Return an array of descriptive names for each field in the user DB.
	 * @param bool $all if false, just returns modifiable fields. Else returns all
	 * @return array - key is field name, value is 'nice name' (descriptive name)
	 */
	public function getNiceNames($all = false)
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


	/**
	 *	Function does validation specific to user data. Updates the $targetData array as appropriate.
	 *
	 *	@param array $targetData - user data generated from earlier vetting stages - only the data in $targetData['data'] is checked
	 *
	 *	@return bool TRUE if nothing updated; false if errors found
	 */
	public function userValidation(&$targetData)
	{
		$u_sql = e107::getDb('u');
		$ret = TRUE;
		$errMsg = '';
		if (isset($targetData['data']['user_email']))
		{
			$v = trim($targetData['data']['user_email']);		// Always check email address if its entered
			if ($v == '')
			{
				if (!e107::getPref('disable_emailcheck'))
				{
					$errMsg = ERR_MISSING_VALUE;
				}
			}
			elseif (!check_email($v))
			{
				$errMsg = ERR_INVALID_EMAIL;
			}
			elseif ($u_sql->count('user', '(*)', "WHERE `user_email`='".filter_var($v,FILTER_SANITIZE_EMAIL)."' AND `user_ban`=1 "))
			{
				$errMsg = ERR_BANNED_USER;
			}
			else
			{	// See if email address banned
				$wc = e107::getIPHandler()->makeEmailQuery($v);		// Generate the query for the ban list
				if ($wc) { $wc = "`banlist_ip`='{$v}' OR ".$wc;  }
				if (($wc === false) || !e107::getIPHandler()->checkBan($wc, false, TRUE))
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
			if (!isset($targetData['errors']['user_email']) && !e107::getPref('disable_emailcheck'))
			{	// We may have already picked up an error on the email address - or it may be allowed to be empty
				$errMsg = ERR_MISSING_VALUE;
			}
		}
		if ($errMsg)
		{	// Update the error
			$targetData['errors']['user_email'] = $errMsg;
			$targetData['failed']['user_email'] = $v;
			$ret = false;
		}
		return $ret;
	}



	/**
	 *	Given an array of user data intended to be written to the DB, adds empty strings (or other default value) for any field which doesn't have a default in the SQL definition.
	 *	(Avoids problems with MySQL in STRICT mode.).
	 *
	 *	@param array $userInfo - user data destined for the database
	 *
	 *	@return bool TRUE if additions made, false if no change.
	 *
	 *	@todo - may be unnecessary with auto-generation of _NOTNULL array in db handler
	 */
	public function addNonDefaulted(&$userInfo)
	{
//		$nonDefaulted = array('user_signature' => '', 'user_prefs' => '', 'user_class' => '', 'user_perms' => '');
		$nonDefaulted = array('user_signature' => '', 'user_prefs' => '', 'user_class' => '', 'user_perms' => '', 'user_realm' => '');	// Delete when McFly finished
		$ret = false;
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


	/**
	 *	Delete time-expired partial registrations from the user DB, clean up user_extended table
	 *
	 *	@param bool $force - set TRUE to force check of user_extended table
	 *
	 *	@return int number of user records deleted
	 */
	public function deleteExpired($force = false)
	{
		$pref = e107::getPref();
		$sql = e107::getDb();

		$temp1 = 0;
		if (isset($pref['del_unv']) && $pref['del_unv'] && intval($pref['user_reg_veri']) != 2)
		{
			$threshold= intval(time() - ($pref['del_unv'] * 60));
			if(($temp1 = $sql->delete('user', 'user_ban = 2 AND user_join < '.$threshold)) > 0)
			{
				$force = true;
			}
		}

		if ($force) // Remove 'orphaned' extended user field records
		{
			$sql->gen("DELETE `#user_extended` FROM `#user_extended` LEFT JOIN `#user` ON `#user_extended`.`user_extended_id` = `#user`.`user_id`
					WHERE `#user`.`user_id` IS NULL");
		}

		return $temp1;
	}



	/**
	 *	Called to update initial user classes, probationary user class etc after various user events
	 *
	 *	@param array $user - user data. 'user_class' must be present
	 *	@param string $event = userveri|userall|userfull|userpartial - defines event
	 *
	 *	@return boolean - true if $user['user_class'] updated, false otherwise
	 */
	public function userClassUpdate($user, $event='userfull')
	{
		$pref = e107::getPref();
		$tp = e107::getParser();

		$initClassStage = isset($pref['init_class_stage']) ? intval($pref['init_class_stage']) : 0;
		$initClasses = array();
		$doClasses = false;
		$doProbation = false;
		$ret = false;

		switch($event)
		{
			case 'userall':
				$doClasses = true;
				$doProbation = true;
				break;
			case 'userfull':
				if(!$pref['user_reg_veri'] || ($initClassStage == 2))
				{
					$doClasses = true;
				}
				$doProbation = true;
				break;
			case 'userpartial' :
				if($initClassStage === 1)
				{
					// Set initial classes if to be done on partial signup, or if selected to add them now
					$doClasses = true;
				}
				$doProbation = true;
				break;
		}

		if($doClasses)
		{
			if(isset($pref['initial_user_classes']))   // Any initial user classes to be set at some stage
			{
				$initClasses = explode(',', $pref['initial_user_classes']);
			}

			if($doProbation && (varset($pref['user_new_period'], 0) > 0))
			{
				$initClasses[] = e_UC_NEWUSER;        // Probationary user class
			}

			if(count($initClasses))
			{    // Update the user classes
				if($user['user_class'])
				{
					$initClasses = array_unique(array_merge($initClasses, explode(',', $user['user_class'])));
				}
				$user['user_class'] = $tp->toDB(implode(',', $initClasses));
				//$ret = TRUE;
				$ret = $user['user_class'];
			}
		}

		return $ret;
	}



	/**
	 * Updates user status, primarily the user_ban field, to reflect outside events
	 *
	 * @param string $start - 'ban', 'bounce'
	 * @param integer $uid - internal user ID, zero if not known
	 * @param string $emailAddress - email address (optional)
	 *
	 * @return boolean | string - false if user found, error message if not
	 */
	public function userStatusUpdate($action, $uid, $emailAddress = '')
	{
		$db = e107::getDb('user');
		$qry = '';
		$error = false;				// Assume no error to start with
		$uid = intval($uid);		// Precautionary - should have already been done
		switch ($action)
		{
			case 'ban' :
				$newVal = USER_BANNED;
				$logEvent = USER_AUDIT_BANNED;
				break;
			case 'bounce' :
				$newVal = USER_EMAIL_BOUNCED;
				$logEvent = USER_AUDIT_MAIL_BOUNCE;
				break;
			case 'reset' :
				$newVal = USER_BOUNCED_RESET;
				$logEvent = USER_AUDIT_BOUNCE_RESET;
				break;
			case 'temp' :
				$newVal = USER_TEMPORARY_ACCOUNT;
				$logEvent = USER_AUDIT_TEMP_ACCOUNT;
				break;
			default :
				return 'Invalid action: '.$action;
		}
		if ($uid) { $qry = '`user_id`='.$uid; }
		if ($emailAddress) { if ($qry) $qry .= ' OR '; $qry .= "`user_email` = '{$emailAddress}'"; }
		if (false === $db->select('user', 'user_id, user_email, user_ban, user_loginname', $qry.' LIMIT 1'))
		{
			$error = 'User not found: '.$uid.'/'.$emailAddress;
		}
		else
		{
			$row = $db->db_Fetch();
			if ($uid && ($uid != $row['user_id']))
			{
				$error = 'UID mismatch: '.$uid.'/'.$row['user_id'];
			}
			elseif ($emailAddress && ($emailAddress != $row['user_email']))
			{
				$error = 'User email mismatch: '.$emailAddress.'/'.$row['user_email'];
			}
			else
			{	// Valid user!
				if ($row['user_ban'] != $newVal)		// We could implement a hierarchy here, so that an important status isn't overridden by a lesser one
				{	// Only update if needed
					$db->update('user', '`user_ban` = '.$newVal.', `user_email` = \'\' WHERE `user_id` = '.$row['user_id'].' LIMIT 1');
					// Add to user audit log		TODO: Should we log to admin log as well?
					$adminLog = e107::getAdminLog();
					$adminLog->user_audit($logEvent, array('user_ban' => $newVal, 'user_email' => $row['user_email']), $row['user_id'], $row['user_loginname']);
				}
			}
		}
		return $error;
	}
}

/**
 * Social login provider
 */
class e_user_provider
{
	/**
	 * @var string
	 */
	protected $_provider;

	/**
	 * Hybridauth adapter
	 * @var \Hybridauth\Adapter\AdapterInterface
	 */
	public $adapter;

	/**
	 * Hybridauth object
	 * @var Hybridauth\Hybridauth
	 */
	protected $hybridauth;
	protected $_config = array();
	/**
	 * @var social_login_config
	 */
	protected $social_login_config_manager;

	public function __construct($provider = null, $config = array())
	{
		require_once(e_PLUGIN . "social/includes/social_login_config.php");
		$this->social_login_config_manager = new social_login_config(e107::getConfig());

		if (!empty($config))
		{
			$this->_config = $config;
		}
		else
		{
			$this->_config = array(
				"callback" => $this->generateCallbackUrl($provider),
				"providers" => $this->social_login_config_manager->getValidConfiguredProviderConfigs(),
				"debug_mode" => 'error',
				"debug_file" => e_LOG . "hybridAuth.log"
			);

		}

		$this->respawnHybridauth();
		$this->setProvider($provider);

		$providerId = $this->getProvider();
		if ($providerId && $this->hybridauth->isConnectedWith($providerId))
		{
			$this->adapter = $this->hybridauth->getAdapter($providerId);
		}
	}

	private function respawnHybridauth()
	{
		$this->hybridauth = new Hybridauth\Hybridauth($this->_config);
	}

	public function setProvider($provider)
	{
		$this->_provider = $provider;
	}

	public function setBackUrl($url)
	{
		# system/xup/login by default
		$this->_config['callback'] = $this->generateCallbackUrl($url);
		$this->respawnHybridauth();
	}

	public function getProvider()
	{
		// $this->log(__CLASS__, __METHOD__, __LINE__);
		return $this->_provider;
	}

	public function getConfig()
	{
		return $this->_config;
	}

	public function getUserProfile()
	{
		if ($this->adapter)
		{
			try
			{
				return $this->adapter->getUserProfile();
			}
			catch (\Hybridauth\Exception\Exception $e)
			{
				return null;
			}
		}
		return null;
	}

	public function userId()
	{
		if ($profile = $this->getUserProfile())
		{
			return $this->getProvider() . '_' . $profile->identifier;
		}
		return null;
	}

	/**
	 * Get the social login providers for which we have adapters
	 *
	 * This function is slow! Please cache the output instead of calling it multiple times.
	 *
	 * @return array String list of supported providers. Empty if Hybridauth is broken.
	 */
	public static function getSupportedProviders()
	{
		$providers = [];

		try
		{
			$reflector = new ReflectionClass('Hybridauth\Hybridauth');
		}
		catch (ReflectionException $e)
		{
			return $providers;
		}
		$hybridauth_path = $reflector->getFileName();
		$hybridauth_providers_path = dirname($hybridauth_path) . "/Provider/";
		$fs_iterator = new FilesystemIterator($hybridauth_providers_path);
		foreach ($fs_iterator as $file)
		{
			if (!$file->isFile()) continue;

			$provider_source_code = file_get_contents($file);
			$provider_source_tokens = token_get_all($provider_source_code);
			for ($token_index = 0; isset($provider_source_tokens[$token_index]); $token_index++)
			{
				if (!isset($provider_source_tokens[$token_index][0])) continue;

				if (T_CLASS === $provider_source_tokens[$token_index][0])
				{
					$token_index += 2;
					$providers[] = $provider_source_tokens[$token_index][1];
				}
			}
		}

		sort($providers);
		return $providers;
	}

	/**
	 * Get the type of provider from a provider name
	 * @param $providerName string Name of the supported social login provider
	 * @return string|bool "OAuth1", "OAuth2", or "OpenID". If false, the provider name is invalid.
	 *                     Other values are technically possible but not supported.
	 */
	public static function getTypeOf($providerName)
	{
		$class_name = "Hybridauth\Provider\\{$providerName}";
		$parent_class = get_parent_class($class_name);
		if (!$parent_class) return false;

		$parent_class_split = explode("\\", get_parent_class($class_name));
		$type = end($parent_class_split);
		if ($type == "AbstractAdapter") return $providerName;
		if (!in_array($type, ['OAuth1', 'OAuth2', 'OpenID'])) return self::getTypeOf($type);
		return $type;
	}

	/**
	 * Get standard and supplementary fields of the specified provider
	 * @param $providerName string Name of the supported social login provider
	 * @return array Multidimensional associative array where the keys are the known field names and the values are a
	 *               description of what their key is for.  Keys can be nested in parent keys.  Parent keys will not
	 *               have a description of the key.  All fields take a string value.  Return will be empty if the
	 *               specified provider does not have any known fields.
	 */
	public static function getFieldsOf($providerName)
	{
		$standardFields = self::getStandardFieldsOf($providerName);
		$supplementaryFields = self::getSupplementalFieldsOf($providerName);
		return self::array_merge_recursive_distinct($standardFields, $supplementaryFields);
	}

	/**
	 * Get the standard/common/parent fields of the specified provider
	 * @param $providerName string Name of the supported social login provider
	 * @return array Multidimensional associative array where the keys are the standard field names and the values are
	 *               a description of what each key is for.  Keys can be nested in parent keys.  Parent keys will not
	 *               have a description of the key.  All fields take a string value.  Return will be empty if the
	 *               specified provider does not have any known standard fields.
	 */
	public static function getStandardFieldsOf($providerName)
	{
		$providerType = self::getTypeOf($providerName);
		switch ($providerType)
		{
			case 'OAuth2':
				$fieldPart = [
					'keys' => [
						'id' => "Client ID given to you by $providerName",
						'secret' => "Client secret given to you by $providerName",
					],
					'scope' => "Permissions to request from $providerName. See the $providerName OAuth2 documentation for details.",
				];
				break;
			case 'OAuth1':
				$fieldPart = [
					'keys' => [
						'key' => "Consumer key given to you by $providerName",
						'secret' => "Consumer secret given to you by $providerName",
					]
				];
				break;
			case 'OpenID':
				$fieldPart = [
					'openid_identifier' => "OpenID endpoint URL"
				];
				break;
			default:
				$fieldPart = [];
		}

		return $fieldPart;
	}

	/**
	 * Get the supplemental fields specific to the specified provider
	 * @param $providerName string Name of the supported social login provider
	 * @return array Multidimensional associative array where the keys are the supplemental field names and the values
	 *               are a description of what each key is for.  Keys can be nested in parent keys.  Parent keys will
	 *               not have a description of the key.  All fields take a string value.  Return will be empty if the
	 *               specified provider does not have any known supplemental fields.
	 */
	public static function getSupplementalFieldsOf($providerName)
	{
		$supplementalFields = [];
		$className = "Hybridauth\Provider\\${providerName}";
		try
		{
			$reflector = new ReflectionClass($className);
		}
		catch (ReflectionException $e)
		{
			return $supplementalFields;
		}

		$adapterPath = $reflector->getFileName();
		$adapterSourceCode = file_get_contents($adapterPath);
		$adapterTokens = token_get_all($adapterSourceCode);
		$rawDocumentation = null;
		for ($index = 0; isset($adapterTokens[$index]); $index ++)
		{
			if (!isset($adapterTokens[$index][1])) continue;

			if (T_DOC_COMMENT === $adapterTokens[$index][0] &&
				FALSE !== strpos($adapterTokens[$index][1], '$config'))
			{
				$rawDocumentation = $adapterTokens[$index][1];
			}

			if (T_VARIABLE == $adapterTokens[$index][0])
			{
				$supplementalFieldPathSplit = self::adapterTokenParseConfig($adapterTokens, $index, null);
				if (!is_null($supplementalFieldPathSplit))
				{
					$value = $rawDocumentation;
					$level = [];
					foreach (array_reverse($supplementalFieldPathSplit) as $supplementalFieldPathItem)
					{
						$level[$supplementalFieldPathItem] = $value;
						$value = $level;
						$level = [];
					}
					$supplementalFields = self::array_merge_recursive_distinct($supplementalFields, $value);
				}
			}
		}

		return $supplementalFields;
	}

	private static function adapterTokenParseConfig($adapterTokens, &$index, $carry)
	{
		if (!isset($adapterTokens[$index][1]))
		{
			if (in_array($adapterTokens[$index], [';', '.', ',', '?'])) return $carry;
			++$index;
			return self::adapterTokenParseConfig($adapterTokens, $index, $carry);
		}
		$token = $adapterTokens[$index];
		$tokenType = $token[0];
		$tokenValue = $token[1];

		switch ($tokenType)
		{
			case T_VARIABLE:
				if ($tokenValue == '$this') break;
				return $carry;
			case T_OBJECT_OPERATOR:
				break;
			case T_STRING:
				switch ($tokenValue)
				{
					case 'config':
						$carry = [];
						break;
					case 'filter':
					case 'get':
						break;
					default:
						return $carry;
				}
				break;
			case T_CONSTANT_ENCAPSED_STRING:
				$carry[] = trim($tokenValue, '\'"');
				break;
		}
		++$index;
		return self::adapterTokenParseConfig($adapterTokens, $index, $carry);
	}

	private static function array_merge_recursive_distinct(&$array1, &$array2)
	{
		$merged = $array1;

		foreach ($array2 as $key => &$value)
		{
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
			{
				$merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
			}
			else
			{
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Check if social logins are enabled site-wide
	 * @return bool TRUE if the site has social logins enabled; FALSE otherwise
	 */
	public function isSocialLoginEnabled()
	{
		return $this->social_login_config_manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL);
	}

	/**
	 * XUP Signup Method (falls-back to XUP login when existing user is detected).
	 * May be used as a simple XUP login link for existing and non-existing users.
	 */
	public function login($redirectUrl = true, $loginAfterSuccess = true, $emailAfterSuccess = true)
	{
		if (!$this->isSocialLoginEnabled())
		{
			throw new Exception("Login failed! This feature is disabled.", 100); // TODO lan
		}

		if (!$this->getProvider())
		{
			throw new Exception("Login failed! Wrong provider.", 2); // TODO lan
		}

		if ($redirectUrl)
		{
			if (true === $redirectUrl)
			{
				$redirectUrl = SITEURL;
			}
			elseif (strpos($redirectUrl, 'http://') !== 0 && strpos($redirectUrl, 'https://') !== 0)
			{
				$redirectUrl = e107::getUrl()->create($redirectUrl);
			}
		}


		if (e107::getUser()->isUser())
		{
			if ($redirectUrl)
			{
				$this->redirectAndForwardMessages($redirectUrl);
			}
			return false;
			//	throw new Exception( "Signup failed! User already signed in. ", 1); // TODO lan
		}

		$this->setBackUrl($redirectUrl);

		$this->adapter = $this->hybridauth->authenticate($this->getProvider());
		$profile = $this->adapter->getUserProfile();

		// returned back, if success...
		if ($profile->identifier)
		{

			$sql = e107::getDb();
			$userMethods = e107::getUserSession();

			$plainPwd = $userMethods->generateRandomString('************'); // auto plain passwords


			// TODO - auto login name, shouldn't be used if system set to user_email login...
			$userdata['user_loginname'] = $this->getProvider() . $userMethods->generateUserLogin(e107::getPref('predefinedLoginName', '_..#..#..#'));
			$userdata['user_email'] = $sql->escape($profile->emailVerified ? $profile->emailVerified : $profile->email) ?: '';
			$userdata['user_name'] = $sql->escape($profile->displayName);
			$userdata['user_login'] = $userdata['user_name'];
			$userdata['user_customtitle'] = ''; // not used
			$userdata['user_password'] = $userMethods->HashPassword($plainPwd, $userdata['user_loginname']); // pwd
			$userdata['user_sess'] = ''; //
			$userdata['user_image'] = $profile->photoURL; // avatar
			$userdata['user_signature'] = ''; // not used
			$userdata['user_hideemail'] = 1; // hide it by default
			$userdata['user_xup'] = $sql->escape($this->userId());

			$pref = e107::pref('core');

			if (!empty($pref['initial_user_classes']))
			{
				$userdata['user_class'] = $pref['initial_user_classes'];
			}
			elseif (!empty($pref['user_new_period']))
			{
				$userdata['user_class'] = e_UC_NEWUSER;
			}
			else
			{
				$userdata['user_class'] = '';
			}

			//		print_a($userdata);


			// user_name, user_xup, user_email and user_loginname shouldn't match
			$insert = (!empty($userdata['user_email'])) ? "OR user_email='" . $userdata['user_email'] . "' " : "";

			if ($uid = $sql->retrieve("user", "user_id", "user_xup='" . $sql->escape($this->userId()) . "' " . $insert . " OR user_loginname='{$userdata['user_loginname']}' OR user_name='{$userdata['user_name']}'"))
			{
				// $this->login($redirectUrl); // auto-login
				$result = e107::getUser()->loginProvider($this->userId());
                $this->updateXupProfile();

                if (!$result)
				{
					e107::getMessage()->addError("User already exists but is not connected through this social login provider");
				}

				if ($redirectUrl)
				{
					$this->redirectAndForwardMessages($redirectUrl);
				}

				return false;
				// throw new Exception( "Signup failed! User already exists. Please use 'login' instead.", 3);
			}

			if (empty($userdata['user_email']) && e107::getPref('disable_emailcheck', 0) == 0) // Allow it if set-up that way.
			{
				// Twitter will not provide email addresses.
				//	throw new Exception( "Signup failed! Can't access user email - registration without an email is impossible.".print_a($userdata,true), 4); // TODO lan
			}

			// other fields
			$now = time();
			$userdata['user_id'] = null;
			$userdata['user_image'] = '';
			$userdata['user_join'] = $now;
			$userdata['user_lastvisit'] = 0;
			$userdata['user_currentvisit'] = 0;
			$userdata['user_comments'] = 0;
			$userdata['user_ip'] = e107::getIPHandler()->getIP(false);
			$userdata['user_ban'] = USER_VALIDATED;
			$userdata['user_prefs'] = '';
			$userdata['user_visits'] = 0;
			$userdata['user_admin'] = 0;
			$userdata['user_perms'] = '';
			$userdata['user_realm'] = '';
			$userdata['user_pwchange'] = $now;

			/** @var e_system_user' $user */
			$user = e107::getSystemUser(0, false);
			$user->setData($userdata);
			$user->getExtendedModel(); // init
			//$user->setEditor(e107::getSystemUser(1, false));
			$user->save(true);

			// user model error
			if ($user->hasError())
			{
				e107::getLog()->add('XUP Signup Failure', $userdata, E_LOG_WARNING, "XUP_SIGNUP");
				throw new Exception($user->renderMessages(), 5);
			}


			### Successful signup!
			//$user->set('provider', $this->getProvider());
			$userdata = $user->getData();
			$userdata['provider'] = $this->getProvider();

			$userdata['callback_data'] = $profile;

			//	e107::getEvent()->trigger('userveri', $userdata);	 // Trigger New verified user.

			e107::getEvent()->trigger('user_xup_signup', $userdata);

			$ret = e107::getEvent()->trigger('usersupprov', $userdata);    // XXX - it's time to pass objects instead of array?

			if (true === $ret) return $this;

			// send email
			if ($emailAfterSuccess && !empty($userdata['user_email']))
			{
				$user->set('user_password', $plainPwd)->email('signup');
			}

			e107::getUser()->setProvider($this);

			// auto login
			if ($loginAfterSuccess)
			{
				e107::getUser()->loginProvider($this->userId()); // if not proper after-login, return true so user can see login screen
			}

			if ($redirectUrl)
			{
				$this->redirectAndForwardMessages($redirectUrl);
			}

			return true;
		}

		return false;
	}

	public function logout()
	{
		if (
			!$this->adapter ||
			!$this->hybridauth->isConnectedWith($this->getProvider())
		) return true;
		try
		{
			$this->adapter->disconnect();
			$this->adapter = null;
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
		return true;
	}

	/**
	 * @param string $backUrl
	 * @return string
	 */
	public function generateCallbackUrl($backUrl = null)
	{
		return e107::getUrl()->create(
			"system/xup/login",
			array(
				'provider' => $this->getProvider(),
				'back' => $backUrl,
				),
			array('full' => true, 'encode' => false)
		);
	}

	/**
	 * @param $redirectUrl
	 */
	private function redirectAndForwardMessages($redirectUrl)
	{
		$messages = e107::getMessage()->getAll('default', true, false);
		foreach ($messages as $type => $message_stack)
		{
			e107::getMessage()->addSessionStack($message_stack, 'default', $type);
		}
		e107::getRedirect()->redirect($redirectUrl);
	}

    /**
     * Synchronize user profile fields from social login provider
     */
    private function updateXupProfile()
    {
        try
        {
            // detect all currently connected providers
            $connected = $this->hybridauth->getConnectedProviders();
        }
        catch (Exception $e)
        {
            e107::getMessage()->addError('[' . $e->getCode() . ']' . $e->getMessage(), 'default', true);
            $session = e107::getSession();
            $session->set('HAuthError', true);
            $connected = false;
        }
        // no active session found
        if (!$connected) return;

        // query DB
        $sql = e107::getDb();
        $where = array();
        $userdata = array();

        foreach ($connected as $providerId)
        {
            try
            {
                $adapter = $this->hybridauth->getAdapter($providerId);
                $profile = $adapter->getUserProfile();
            }
            catch (\Hybridauth\Exception\Exception $e)
            {
                continue;
            }

            if (!$profile->identifier) continue;

            $userdata['user_name'] = $sql->escape($profile->displayName);
            $userdata['user_image'] = $profile->photoURL; // avatar
            $userdata['user_email'] = $profile->email;

            $id = $providerId . '_' . $profile->identifier;
            $where[] = "user_xup='" . $sql->escape($id) . "'";
        }
        // no active session found
        if (empty($where)) return;

        $where = implode(' OR ', $where);
        if ($sql->select('user', 'user_id, user_name, user_email, user_image, user_password, user_xup', $where))
        {

            $user = $sql->fetch();
            e107::getUserSession()->makeUserCookie($user);

            $spref = e107::pref('social');

            // Update display name or avatar image if they have changed.
            if (
                (empty($user['user_email']) && !empty($userdata['user_email'])) ||
                ($userdata['user_name'] != $user['user_name']) ||
                ($userdata['user_image'] != $user['user_image'])
            )
            {
                $updateQry = array();

                if (!empty($spref['xup_login_update_username']))
                {
                    $updateQry['user_name'] = $userdata['user_name'];
                }

                if (!empty($spref['xup_login_update_avatar']))
                {
                    $updateQry['user_image'] = $userdata['user_image'];
                }

                if (empty($user['user_email']))
                {
                    $updateQry['user_email'] = $userdata['user_email'];
                }

                $updateQry['WHERE'] = "user_id=" . $user['user_id'] . " LIMIT 1";

                if ($sql->update('user', $updateQry) !== false)
                {
                    $updatedProfile = array_replace($user, $userdata);
                    e107::getEvent()->trigger('user_xup_updated', $updatedProfile);
                    e107::getLog()->add('User Profile Updated', $userdata, E_LOG_INFORMATIVE, "XUP_LOGIN", LOG_TO_ADMIN, array('user_id' => $user['user_id'], 'user_name' => $user['user_name'], 'user_email' => $userdata['user_email']));
                }
                else
                {
                    e107::getLog()->add('User Profile Update Failed', $userdata, E_LOG_WARNING, "XUP_LOGIN", LOG_TO_ADMIN, $updateQry);
                }
            }

            unset($user['user_password']);
            e107::getLog()->user_audit(USER_AUDIT_LOGIN, '', $user['user_id'], $user['user_name']);
        }
    }
}


e107::coreLan('administrator', true);

class e_userperms
{

	protected $core_perms = array();

	protected $plugin_perms = array();

	protected $language_perms = array();

	protected $main_perms = array();

	protected $full_perms = array();

	protected $permSectionDiz = array(
		'core'		=> LAN_GENERAL,
		'plugin'	=> ADLAN_CL_7,
		'language'	=> ADLAN_132,
		'main'		=> ADMSLAN_58
	 );




	function __construct()
	{

		$this->core_perms = array(

		// In the same order as admin navigation! Plus same labels.

		// Settings
		"C"	=> array(ADLAN_74,E_16_CACHE, E_32_CACHE),		    // Clear the system cache
		"F"	=> array(ADLAN_58,E_16_EMOTE, E_32_EMOTE),	        // Emoticons
		"G"	=> array(ADLAN_60,E_16_FRONT, E_32_FRONT),		    // Front-Page Configuration
		"L"	=> array(ADLAN_132,E_16_LANGUAGE, E_32_LANGUAGE),	// Language Packs
		"T"	=> array(ADLAN_66,E_16_META, E_32_META),			// Meta tags

		"1"	=> array(LAN_PREFS,E_16_PREFS, E_32_PREFS),			// Alter Site Preferences
		"X"	=> array(LAN_SEARCH,E_16_SEARCH, E_32_SEARCH),		// Search
		"I"	=> array(ADLAN_138,E_16_LINKS, E_32_LINKS),			// Post SiteLinks
		"8"	=> array(ADMSLAN_27,E_16_LINKS, E_32_LINKS),		// Oversee SiteLink Categories
		"K"	=> array(ADLAN_159,E_16_EURL, E_32_EURL),			// Configure URLs

		// Users
		"3"	=> array(ADLAN_8,E_16_ADMIN, E_32_ADMIN),			// Modify Admin perms
		"4"	=> array(LAN_USER_MANAGEALL,E_16_USER, E_32_USER),	// Manage all user access and settings etc
		"U0" => array(ADLAN_34,E_16_USER, E_32_USER), 		    // moderate users/bans but not userclasses or extended fields,
		"U1" => array(LAN_USER_QUICKADD,E_16_USER, E_32_USER),	// "User: Quick Add User",
		"U2" => array(LAN_USER_OPTIONS,E_16_USER, E_32_USER),	// Manage only user-options
		"U3" => array(LAN_USER_RANKS,E_16_USER, E_32_USER),		// Manage only user-ranks
		"W"	=> array(ADLAN_136,E_16_MAIL, E_32_MAIL),			// Configure mail settings and mailout


		// Content
		"5"	=> array(ADLAN_42,E_16_CUST, E_32_CUST),			// create/edit custom PAGES
		"J"	=> array(ADLAN_42,E_16_CUST, E_32_CUST),			// create/edit custom MENUS

		"H"	=> array(ADLAN_0,E_16_NEWS, E_32_NEWS),								// Post News - All Areas except settings.
		"H0" => array(ADLAN_0." (".LAN_CREATE.")",E_16_NEWS, E_32_NEWS),					// Create News Items
		"H1" => array(ADLAN_0." (".LAN_EDIT.")",E_16_NEWS, E_32_NEWS),						// Edit News Items
		"H2" => array(ADLAN_0." (".LAN_DELETE.")",E_16_NEWS, E_32_NEWS),					// Delete News Items
		"H3" => array(ADLAN_0." (".LAN_CATEGORY." - ".LAN_CREATE.")",E_16_NEWS, E_32_NEWS),			// Create News Category
		"H4" => array(ADLAN_0." (".LAN_CATEGORY." - ".LAN_EDIT.")",E_16_NEWS, E_32_NEWS),			// Edit News Category
		"H5" => array(ADLAN_0." (".LAN_CATEGORY." - ".LAN_DELETE.")",E_16_NEWS, E_32_NEWS),			// Delete News Category

		"N"	=> array(ADLAN_0." (".LAN_SUBMITTED.")",E_16_NEWS, E_32_NEWS),			// Moderate submitted news
		"V"	=> array(ADLAN_31,E_16_UPLOADS, E_32_UPLOADS),							// Configure public file uploads
		"M"	=> array(ADLAN_28,E_16_WELCOME, E_32_WELCOME),							// Welcome Messages

		// Tools
		"Y"	=> array(ADLAN_147,E_16_INSPECT, E_32_INSPECT),	    // File inspector
		"9"	=> array(ADLAN_40, E_16_MAINTAIN, E_32_MAINTAIN),	// Take Down site for Maintenance
		"O"	=> array(ADLAN_149,E_16_NOTIFY, E_32_NOTIFY),		// Notify
		"U"	=> array(ADLAN_157,E_16_CRON, E_32_CRON),			// Schedule Tasks
		"S"	=> array(ADLAN_155,E_16_ADMINLOG, E_32_ADMINLOG),	// System Logging

		// Manage
		"B"	=> array(LAN_COMMENTMAN,E_16_COMMENT, E_32_COMMENT),	    								// Moderate Comments
		"6"	=> array(LAN_MEDIAMANAGER,E_16_FILE, E_32_FILE),											// File-Manager  - Upload /manage files -
		"A"	=> array(LAN_MEDIAMANAGER." (".LAN_ALL.")",E_16_IMAGES, E_32_IMAGES),						// Media-Manager All Areas.
		"A1"=> array(LAN_MEDIAMANAGER." (".LAN_UPLOAD."/".LAN_IMPORT.")",E_16_IMAGES, E_32_IMAGES),		// Media-Manager (Media Upload/Add/Import)
		"A2"=> array(LAN_MEDIAMANAGER." (".LAN_CATEGORIES.")",E_16_IMAGES, E_32_IMAGES),				// Media-Manager (Media-Categories)

		"TMP"=> array(ADLAN_140." (".LAN_PREFS.")",E_16_THEMEMANAGER, E_32_THEMEMANAGER),

		"2"	=> array(ADLAN_6,E_16_MENUS, E_32_MENUS),		// Alter Menus


		//	"D"=> ADMSLAN_29,	// Manage Banners 				(deprecated - now a plugin)
		//	"E"=> ADMSLAN_30,	// News feed headlines 			(deprecated - now a plugin)
		// "K"=>

		// "P" 				// Reserved for Plugins

		//	"Q"=> array(ADMSLAN_24),	// Manage download categories (deprecated - now a plugin)
		//	"R"=> ADMSLAN_44,	// Post Downloads (deprecated)


		//	"Z"=> ADMSLAN_62, // Plugin Manager.. included under Plugins category.
		);


	//	$sql = e107::getDb('sql2');
	//	$tp = e107::getParser();

		$pg = e107::getPlug();
		$installed = $pg->getInstalled();
		foreach($installed as $plug=>$version)
		{
			$pg->load($plug);

			$arr = array(
				0 => $pg->getName(),
				1 => $pg->getIcon(16),
				2 => $pg->getIcon(32)
			);

			$key = "P".$pg->getId();
			$this->plugin_perms[$key] = $arr;
		}

/*
		$plg = e107::getPlugin();
		$allPlugins = $plg->getall(1); // Needs all for 'reading' and 'installed' for writing.

		foreach($allPlugins as $k=>$row2)
		{
			if($plg->parse_plugin($row2['plugin_path']))
			{
				$plug_vars = $plg->plug_vars;
				$this->plugin_perms[("P".$row2['plugin_id'])] = array($tp->toHTML($row2['plugin_name'], false, 'RAWTEXT,defs'));
				$this->plugin_perms[("P".$row2['plugin_id'])][1] = $plg->getIcon($row2['plugin_path'],16);
				$this->plugin_perms[("P".$row2['plugin_id'])][2] = $plg->getIcon($row2['plugin_path'],32);
			}
		}
*/


		asort($this->plugin_perms);

		$this->plugin_perms = array("Z"=> array('0'=>ADLAN_98)) + $this->plugin_perms;

		if(e107::getConfig()->getPref('multilanguage'))
		{
			$lanlist = explode(",",e_LANLIST);
			sort($lanlist);
			foreach($lanlist as $langs)
			{
				$this->language_perms[$langs] = array("0"=>$langs);
			}
		}

		if(getperms('0'))
		{
			$this->main_perms = array('0' => array('0'=>ADMSLAN_58));
		}

		// Note: Using array_merge or array_merge_recursive will corrupt the array. 
		$this->full_perms = $this->core_perms + $this->plugin_perms + $this->language_perms + $this->main_perms;

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

		return $this->full_perms;
	}

	function checkb($arg, $perms, $info='')
	{
		$frm = e107::getForm();

		if(is_array($info))
		{
			$label		= $info[0];
			$icon_16	= $info[1];
			$icon_32	= $info[2];
		}
		elseif($info)
		{
			$label		= $info;
			$icon_16	= "";
			$icon_32	= "";
		}

		$class = getperms($arg, $perms) ? 'active' : '';

		$par = "<tr class='{$class}'>
			<td style='text-align:center'>".$icon_16."</td>
			<td style='text-align:center'>".$frm->checkbox('perms[]', $arg, getperms($arg, $perms))."</td>
			<td>".$frm->label($label,'perms[]', $arg)."</td>
			</tr>";

		return $par;
	}

	function renderPerms($perms,$uniqueID='')
	{
		$tmp = explode(".",$perms);
		$tmp = array_filter($tmp);

		$permdiz = $this->getPermList();

		$ptext = array();

		foreach($tmp as $p)
		{
			// if(trim($p) == ""){ continue; }
			$val = vartrue($permdiz[$p],'missing '.$p);
			$ptext[] = is_array($permdiz[$p]) ? $permdiz[$p][0] : $val;
		}

		$id = "id_".$uniqueID;


		$text = "<div href='#id_{$id}' class='e-pointer e-expandit' title='".ADMSLAN_71."'>{$perms}</div>\n";

		if(varset($ptext))
		{
			$text .= "<div id='id_{$id}' class='e-hideme'><ul><li>".implode("</li>\n<li>",$ptext)."</li></ul></div>\n";
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
	/*
		$text = "
			<form method='post' action='".e_SELF."' id='myform'>
				<fieldset id='core-administrator-edit'>
					<legend class='e-hideme'>".ADMSLAN_52."</legend>
					<table class='adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='control'>


		";
	*/

	$text = "<form method='post' action='".e_SELF."' id='myform'>
				<fieldset id='core-administrator-edit'>
					<legend class='e-hideme'>".ADMSLAN_52."</legend>";

	//XXX Bootstrap Tabs (as used below) should eventually be the default for all of the admin area.
	$text .= '
		 <ul class="nav nav-tabs">
		    <li class="active"><a href="#tab1" data-toggle="tab">'.$this->renderSectionDiz('core').'</a></li>
		    <li><a href="#tab2" data-toggle="tab">'.$this->renderSectionDiz('plugin').'</a></li>
		    <li><a href="#tab3" data-toggle="tab">'.$this->renderSectionDiz('language').'</a></li>
		     <li><a href="#tab4" data-toggle="tab">'.$this->renderSectionDiz('main').'</a></li>
		  </ul>
		  
		  <div class="tab-content">
		
			<div class="tab-pane active " id="tab1">
		      <div class="separator">
			'.$this->renderPermTable('core',$a_perms).'
		      </div>		
		    </div>
		
		    <div class="tab-pane" id="tab2">
		      <div class="separator">
		        '.$this->renderPermTable('plugin',$a_perms).'
		      </div>
			</div>
			
			<div class="tab-pane" id="tab3">
		      <div class="separator">
		        '.$this->renderPermTable('language',$a_perms).'
		      </div>
			</div>
			
			<div class="tab-pane" id="tab4">
		      <div class="separator">
		        '.$this->renderPermTable('main',$a_perms).'
		      </div>
			</div>
			
		  </div>';


	//	$text .= $this->renderPermTable('grouped',$a_perms);



		$text .= $this->renderCheckAllButtons();

	//	$text .= "</td></tr></tbody></table>";

					$text .= "
					".$this->renderSubmitButtons()."
					<input type='hidden' name='ad_name' value='{$ad_name}' />
					<input type='hidden' name='a_id' value='{$a_id}' />
				</fieldset>
			</form>
		";

	//	$text .= $this->renderPermTable('core',$a_perms);

		$ns->tablerender(ADMSLAN_52.SEP.$ad_name, $text);
	}

	function renderCheckAllButtons()
	{
		$frm = e107::getForm();
		return "
			<div class='field-section'>
				".$frm->admin_button('check_all', 'jstarget:perms', 'action', LAN_CHECKALL)."
				".$frm->admin_button('uncheck_all', 'jstarget:perms', 'action', LAN_UNCHECKALL)."
			</div>
		";
	}

	function renderSubmitButtons()
	{
		$frm = e107::getForm();
		return "
			<div class='buttons-bar center'>
				".$frm->admin_button('update_admin', LAN_UPDATE, 'update')."
				".$frm->admin_button('go_back', LAN_BACK, 'cancel')."
			</div>
		";
	}

	function renderPermTable($type,$a_perms='')
	{



		if($type == 'tabs')
		{
			$groupedList = $this->getPermList('grouped');
			$tab = array();
			foreach($groupedList as $section=>$list)
			{
				$text = '';
				//	$text .= "\t\t<div class='field-section'><h4>".$prm->renderSectionDiz($section)."</h4>"; //XXX Lan - General
				$text .= "\t\t<table class='table adminlist'>
				<colgroup>
					<col class='center' style='width:50px' />
					<col style='width:50px' />
					<col  />
				</colgroup>
				<tbody>";

				foreach($list as $key=>$diz)
				{
					$text .= $this->checkb($key, $a_perms, $diz);
				}

				$text .= "</tbody></table>";

				$tab[] = array('caption'=>$this->renderSectionDiz($section), 'text'=>$text);

			}

		//	return print_a($groupedList);
			return e107::getForm()->tabs($tab);
		}

		$groupedList = $this->getPermList($type);

		if($type != 'grouped')
		{
			$text = "\t\t<table class='table adminform'>
			<colgroup>
				<col class='center' style='width:50px' />
				<col style='width:50px' />
				<col  />
			</colgroup>
			<tbody>";
		//	$text .= "<tr><td class='field-section' colspan='3'><h4>".$this->renderSectionDiz($type)."</h4></td></tr>"; //XXX Lan - General
		//	$text .= "\t\t<div class='field-section'><h4>".$prm->renderSectionDiz($section)."</h4>"; //XXX Lan - General
			foreach($groupedList as $key=>$diz)
			{
				$text .= $this->checkb($key, $a_perms, $diz);
			}
			$text .= "</tbody>
			</table>";

			return $text;
		}

		$text = "";
		foreach($groupedList as $section=>$list)
		{
			$text .= "\t\t<table class='table adminlist'>
			<colgroup>
				<col class='center' style='width:50px' />
				<col style='width:50px' />
				<col  />
			</colgroup>
			<tbody><tr><td class='field-section' colspan='3'><h4>".$this->renderSectionDiz($section)."</h4></td></tr>"; //XXX Lan - General
		//	$text .= "\t\t<div class='field-section'><h4>".$prm->renderSectionDiz($section)."</h4>"; //XXX Lan - General
			foreach($list as $key=>$diz)
			{
				$text .= $this->checkb($key, $a_perms, $diz);
			}
			$text .= "</tbody>
			</table>";
		}

		return $text;
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
		$mes = e107::getMessage();
		if ($modID == 0)
		{
			$mes->addError("Malfunction at line ".__LINE__ ." of user_handler.php");
			return;
		}

		$sysuser = e107::getSystemUser($modID, false);
		$row = $sysuser->getData();
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

		//$sql->db_Update("user", "user_perms='{$perm}' WHERE user_id='{$modID}' ") 
		if(!$sysuser->isAdmin())
		{
			$sysuser->set('user_admin', 1)->save();
			$vars = array('x'=>$sysuser->getId(), 'y'=> $sysuser->getName(), 'z'=>$sysuser->getValue('email'));
			$lan = e107::getParser()->lanVars( USRLAN_164, $vars);
			e107::getLog()->add('USET_08', $lan, E_LOG_INFORMATIVE);
		}

		e107::getMessage()->addAuto($sysuser->set('user_perms', $perm)->save(), 'update', sprintf(LAN_UPDATED, $tp->toDB($_POST['ad_name'])), false, false);
		$logMsg = str_replace(array('[x]', '[y]'),array($modID, $a_name),ADMSLAN_72).$perm;
		e107::getLog()->add('ADMIN_01',$logMsg,E_LOG_INFORMATIVE,'');
	}

}
