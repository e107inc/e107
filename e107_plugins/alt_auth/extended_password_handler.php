<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Extended password handler for alt_auth plugin
 *
 * $URL$
 * $Id$
 */

/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */

/**
EXTENDED PASSWORD HANDLER CLASS 
	- supports many password formats used on other systems
	- implements checking of existing passwords only

To use:
	Instantiate ExtendedPasswordHandler
	call CheckPassword(plaintext_password,login_name, stored_value)
or, optionally:
	call CheckPassword(plaintext_password,login_name, stored_value, password_type)

@todo:
	1. Check that public/private declarations of functions are correct
*/


if (!defined('e107_INIT')) { exit; }


require_once(e_HANDLER.'user_handler.php');


// @todo make these class constants
/*define('PASSWORD_PHPBB_SALT',2);
define('PASSWORD_MAMBO_SALT',3);
define('PASSWORD_JOOMLA_SALT',4);
define('PASSWORD_GENERAL_MD5',5);
define('PASSWORD_PLAINTEXT',6);
define('PASSWORD_GENERAL_SHA1',7);
define('PASSWORD_WORDPRESS_SALT', 8);
define('PASSWORD_MAGENTO_SALT', 9);
define('PASSWORD_PHPFUSION_SHA256', 10);

// Supported formats:
define('PASSWORD_PHPBB_ID', '$H$');				// PHPBB salted
define('PASSWORD_ORIG_ID', '$P$');				// 'Original' code
define('PASSWORD_WORDPRESS_ID', '$P$');			// WordPress 2.8
*/



class ExtendedPasswordHandler extends UserHandler
{
	private $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';	// Holds a string of 64 characters for base64 conversion
	var $random_state = '';						// A (hopefully) random number

	const   PASSWORD_E107_MD5       = 0;
	const   PASSWORD_E107_SALT      = 1;
	const   PASSWORD_PHPBB_SALT     = 2;
	const   PASSWORD_MAMBO_SALT     = 3;
	const   PASSWORD_JOOMLA_SALT    = 4;
	const   PASSWORD_GENERAL_MD5    = 5;
	const   PASSWORD_PLAINTEXT      = 6;
	const   PASSWORD_GENERAL_SHA1   = 7;
	const   PASSWORD_WORDPRESS_SALT = 8;
	const   PASSWORD_MAGENTO_SALT   = 9;
	const   PASSWORD_PHPFUSION_SALT = 10;

	const   PASSWORD_PHPBB_ID           = '$H$';			// PHPBB salted
	const   PASSWORD_ORIG_ID            = '$P$';				// 'Original' code
	const   PASSWORD_WORDPRESS_ID       = '$P$';			// WordPress 2.8

	/**
	 * Constructor - just call parent
	 */
	function __construct()
	{
		// Ancestor constructor
		  parent::__construct();
	}


	/**
	 *	Return a number of random bytes as specified by $count
	 */
	private function get_random_bytes($count)
	{
		$this->random_state = md5($this->random_state.microtime().mt_rand(0,10000));  // This will 'auto seed'

		$output = '';
		for ($i = 0; $i < $count; $i += 16) 
		{	// Only do this loop once unless we need more than 16 bytes
		  $this->random_state = md5(microtime() . $this->random_state);
		  $output .= pack('H*', md5($this->random_state));		// Becomes an array of 16 bytes
		}
		$output = substr($output, 0, $count);

		return $output;
	}


	/**
	 * 	Encode to base64 (each block of three 8-bit chars becomes 4 printable chars)
	 *	Use first $count characters of $input string
	 */
	private function encode64($input, $count)
	{
		return base64_encode(substr($input, 0, $count));	// @todo - check this works OK
		/*
		$output = '';
		$i = 0;
		do 
		{
		  $value = ord($input[$i++]);	
		  $output .= $this->itoa64[$value & 0x3f];
		  if ($i < $count) $value |= ord($input[$i]) << 8;
		  $output .= $this->itoa64[($value >> 6) & 0x3f];
		  if ($i++ >= $count) break;
		  if ($i < $count) $value |= ord($input[$i]) << 16;
		  $output .= $this->itoa64[($value >> 12) & 0x3f];
		  if ($i++ >= $count) break;
		  $output .= $this->itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
		*/
	}



	/**
	 *	Method for PHPBB3-style salted passwords, which begin '$H$', and WordPress-style salted passwords, which begin '$P$'
	 *	Given a plaintext password and the complete password/hash function (which includes any salt), calculate hash
	 *	Returns FALSE on error
	 */
	private function crypt_private($password, $stored_password, $password_type = self::PASSWORD_PHPBB_SALT)
	{
		$output = '*0';
		if (substr($stored_password, 0, 2) == $output)
		{
			$output = '*1';
		}

		$prefix = '';
		switch ($password_type)
		{
			case self::PASSWORD_PHPBB_SALT :
				$prefix = self::PASSWORD_PHPBB_ID;
				break;
			case self::PASSWORD_WORDPRESS_SALT :
				$prefix = self::PASSWORD_WORDPRESS_ID;
				break;
			default :
				$prefix = '';
		}

		if ($prefix != substr($stored_password, 0, 3))
		{
			return $output;
		}

		$count_log2 = strpos($this->itoa64, $stored_password[3]);			// 4th character indicates hash depth count
		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;

		$salt = substr($stored_password, 4, 8);						// Salt is characters 5..12
		if (strlen($salt) != 8)
		{
			return $output;
		}

		# We're kind of forced to use MD5 here since it's the only
		# cryptographic primitive available in all versions of PHP
		# currently in use.  To implement our own low-level crypto
		# in PHP would result in much worse performance and
		# consequently in lower iteration counts and hashes that are
		# quicker to crack (by non-PHP code).
		// Get raw binary output (always 16 bytes) - we assume PHP5 here
		$hash = md5($salt.$password, TRUE);
		do 
		{
			$hash = md5($hash.$password, TRUE);
		} while (--$count);

		$output = substr($setting, 0, 12);		// Identifier, shift count and salt - total 12 chars
		$output .= $this->encode64($hash, 16);	// Returns 22-character string

		return $output;
	}


	/**
	 *	Return array of supported password types - key is used internally, text is displayed
	 */
	public function getPasswordTypes($includeExtended = TRUE)
	{
		$vals = array();
		$vals = array(
		'md5' => IMPORTDB_LAN_7,
		'e107_salt' => IMPORTDB_LAN_8);		// Methods supported in core

		if ($includeExtended)
		{
			$vals = array_merge($vals,array( 
				'plaintext' 		=> IMPORTDB_LAN_2, 
				'joomla_salt'		=> IMPORTDB_LAN_3, 
				'mambo_salt'		=> IMPORTDB_LAN_4,
				'smf_sha1'			=> IMPORTDB_LAN_5,
				'sha1'				=> IMPORTDB_LAN_6,
				'phpbb3_salt'		=> IMPORTDB_LAN_12,
				'wordpress_salt'	=> IMPORTDB_LAN_13,
				'magento_salt'		=> IMPORTDB_LAN_14,
				'phpfusion_salt'    => "PHPFusion",
				));
		}
		return $vals;
	}


	/**
	 *	Return password type which relates to a specific foreign system
	 */
	public function passwordMapping($ptype)
	{
		$maps = array( 
				'plaintext' 		=> self::PASSWORD_PLAINTEXT,
				'joomla_salt' 		=> self::PASSWORD_JOOMLA_SALT,
				'mambo_salt' 		=> self::PASSWORD_MAMBO_SALT,
				'smf_sha1' 			=> self::PASSWORD_GENERAL_SHA1,
				'sha1' 				=> self::PASSWORD_GENERAL_SHA1,
				'mambo' 			=> self::PASSWORD_GENERAL_MD5,
				'phpbb2'			=> self::PASSWORD_GENERAL_MD5,
				'e107'				=> self::PASSWORD_GENERAL_MD5,
				'md5'				=> self::PASSWORD_GENERAL_MD5,
				'e107_salt'			=> self::PASSWORD_E107_SALT,
				'phpbb2_salt'		=> self::PASSWORD_PHPBB_SALT,
				'phpbb3_salt'		=> self::PASSWORD_PHPBB_SALT,
				'wordpress_salt'	=> self::PASSWORD_WORDPRESS_SALT,
				'magento_salt'		=> self::PASSWORD_MAGENTO_SALT,
				'phpfusion_salt'    => self::PASSWORD_PHPFUSION_SALT,
				);
		if (isset($maps[$ptype])) return $maps[$ptype];
		return FALSE;
	}


	/**
	 *	Extension of password validation to handle more types
	 *
	 *	@param string $pword - plaintext password as entered by user
	 *	@param string $login_name - string used to log in (could actually be email address)
	 *	@param string $stored_hash - required value for password to match
	 *	@param integer $password_type - constant specifying the type of password to check against
	 *
	 *	@return PASSWORD_INVALID|PASSWORD_VALID|string
	 *		PASSWORD_INVALID if no match
	 *		PASSWORD_VALID if valid password
	 *		Return a new hash to store if valid password but non-preferred encoding
	 */
	public function CheckPassword($pword, $login_name, $stored_hash, $password_type = PASSWORD_DEFAULT_TYPE)
	{
		switch ($password_type)
		{
			case self::PASSWORD_GENERAL_MD5 :
			case self::PASSWORD_E107_MD5 :
				$pwHash = md5($pword);

				break;

			case self::PASSWORD_GENERAL_SHA1 :
				if (strlen($stored_hash) != 40) return PASSWORD_INVALID;
				$pwHash = sha1($pword);
				break;

			case self::PASSWORD_JOOMLA_SALT :
			case self::PASSWORD_MAMBO_SALT :
				if ((strpos($stored_hash, ':') === false) || (strlen($stored_hash) < 40))
				{
					return PASSWORD_INVALID;
				}
				// Mambo/Joomla salted hash - should be 32-character md5 hash, ':', 16-character salt (but could be 8-char salt, maybe)
				list($hash, $salt) = explode(':', $stored_hash); 
				$pwHash = md5($pword.$salt);
				$stored_hash = $hash;
				break;
				

			case self::PASSWORD_MAGENTO_SALT :
				$hash = $salt = '';
				if ((strpos($stored_hash, ':') !== false))
				{
					list($hash, $salt) = explode(':', $stored_hash); 
				}
				// Magento salted hash - should be 32-character md5 hash, ':', 2-character salt, but could be also only md5 hash
				else 
				{
					$hash = $stored_hash;
				} 
				if(strlen($hash) !== 32) 
				{
					//return PASSWORD_INVALID;
				}
				
				$pwHash = $salt ? md5($salt.$pword) : md5($pword);
				$stored_hash = $hash;
				break;

			case self::PASSWORD_E107_SALT :
				//return e107::getUserSession()->CheckPassword($password, $login_name, $stored_hash);
				return parent::CheckPassword($pword, $login_name, $stored_hash);
				break;

			case self::PASSWORD_PHPBB_SALT :
			case self::PASSWORD_WORDPRESS_SALT :
				if (strlen($stored_hash) != 34) return PASSWORD_INVALID;
				$pwHash = $this->crypt_private($pword, $stored_hash, $password_type);
				if ($pwHash[0] == '*')
				{
					return PASSWORD_INVALID;
				}
				$stored_hash = substr($stored_hash,12);
				break;

			case self::PASSWORD_PHPFUSION_SALT:

				list($hash, $salt) = explode(':', $stored_hash);

				if (strlen($hash) !== 32)
				{
					$pwHash = hash_hmac('sha256',$pword, $salt);
				}
				else
				{
					e107::getMessage()->addDebug("PHPFusion Md5 Hash Detected ");
					$pwHash = md5(md5($pword));
				}

				$stored_hash = $hash;
				break;

			case self::PASSWORD_PLAINTEXT :
				$pwHash = $pword;
				break;

			default :
				return PASSWORD_INVALID;
		}

		if(deftrue('e_DEBUG'))
		{
			e107::getMessage()->addDebug("Stored Hash: ".$stored_hash);

			if(!empty($salt))
			{
				e107::getMessage()->addDebug("Stored Salt: ".$salt);
			}

			e107::getMessage()->addDebug("Generated Hash: ".$pwHash);
		}

		if ($stored_hash != $pwHash) return PASSWORD_INVALID;

		return PASSWORD_VALID;
	}

}


?>