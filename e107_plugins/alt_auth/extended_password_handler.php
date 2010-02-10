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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/extended_password_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/*
EXTENDED PASSWORD HANDLER CLASS 
	- supports many password formats used on other systems
	- implements checking of existing passwords only

To use:
	Instantiate ExtendedPasswordHandler
	call CheckPassword(plaintext_password,login_name, stored_value)
or, optionally:
	call CheckPassword(plaintext_password,login_name, stored_value, password_type)


To do:

*/

if (!defined('e107_INIT')) { exit; }


require_once(e_HANDLER.'user_handler.php');



  define('PASSWORD_PHPBB_SALT',2);
  define('PASSWORD_MAMBO_SALT',3);
  define('PASSWORD_JOOMLA_SALT',4);
  define('PASSWORD_GENERAL_MD5',5);
  define('PASSWORD_PLAINTEXT',6);
  define('PASSWORD_GENERAL_SHA1',7);
  define('PASSWORD_WORDPRESS_SALT', 8);

  // Supported formats:
  define('PASSWORD_PHPBB_ID','$H$');			// PHPBB salted
  define('PASSWORD_ORIG_ID','$P$');				// 'Original' code
  define('PASSWORD_WORDPRESS_ID', '$P$');		// WordPress 2.8


class ExtendedPasswordHandler extends UserHandler
{
  var $itoa64;								// Holds a string of 64 characters for base64 conversion
//  var $iteration_count_log2;					// Used to compute number of iterations in calculating hash
  var $random_state = '';					// A (hopefully) random number




  // Constructor
  function ExtendedPasswordHandler()
  {
	// Lookup string ready for base64 conversions
	  $this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	  $this->UserHandler();				// Ancestor constructor
  }


  // Return a number of random bytes as specified by $count
  function get_random_bytes($count)
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


  // Encode to base64 (each block of three 8-bit chars becomes 4 printable chars)
  // Use first $count characters of $input string
  function encode64($input, $count)
  {
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
  }



  // Method for PHPBB3-style salted passwords, which begin '$H$', and WordPress-style salted passwords, which begin '$P$'
  // Given a plaintext password and the complete password/hash function (which includes any salt), calculate hash
  // Returns FALSE on error
	function crypt_private($password, $stored_password, $password_type = PASSWORD_PHPBB_SALT)
	{
		$output = '*0';
		if (substr($stored_password, 0, 2) == $output)
		{
			$output = '*1';
		}

		$prefix = '';
		switch ($password_type)
		{
			case PASSWORD_PHPBB_SALT :
				$prefix = PASSWORD_PHPBB_ID;
				break;
			case PASSWORD_WORDPRESS_SALT :
				$prefix = PASSWORD_WORDPRESS_ID;
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


	// Return array of supported password types - key is used internally, text is displayed
	function getPasswordTypes($include_core = FALSE)
	{
		$vals = array();
		if ($include_core)
		{
		  $vals = array('md5' => IMPORTDB_LAN_7,'e107_salt' => IMPORTDB_LAN_8);		// Methods supported in core
		}
		if (is_bool($include_core))
		{
		$vals = array_merge($vals,array( 
			'plaintext' 	=> IMPORTDB_LAN_2, 
			'joomla_salt'	=> IMPORTDB_LAN_3, 
			'mambo_salt'	=> IMPORTDB_LAN_4,
			'smf_sha1'		=> IMPORTDB_LAN_5,
			'sha1'			=> IMPORTDB_LAN_6,
			'phpbb3_salt'	=> IMPORTDB_LAN_12,
			'wordpress_salt'	=> IMPORTDB_LAN_13
			));
		}
		return $vals;
	}


	// Return password type which relates to a specific foreign system
	function passwordMapping($ptype)
	{
		$maps = array( 
				'plaintext' 	=> PASSWORD_PLAINTEXT, 
				'joomla_salt' 	=> PASSWORD_JOOMLA_SALT, 
				'mambo_salt' 	=> PASSWORD_MAMBO_SALT,
				'smf_sha1' 		=> PASSWORD_GENERAL_SHA1,
				'sha1' 			=> PASSWORD_GENERAL_SHA1,
				'mambo' 		=> PASSWORD_GENERAL_MD5,
				'phpbb2'		=> PASSWORD_GENERAL_MD5,
				'e107'			=> PASSWORD_GENERAL_MD5,
				'md5'			=> PASSWORD_GENERAL_MD5,
				'e107_salt'		=> PASSWORD_E107_SALT,
				'phpbb2_salt'	=> PASSWORD_PHPBB_SALT,
				'phpbb3_salt'	=> PASSWORD_PHPBB_SALT,
				'wordpress_salt'	=> PASSWORD_WORDPRESS_SALT
				);
		if (isset($maps[$ptype])) return $maps[$ptype];
		return FALSE;
	}


	// Extension of password validation - 
	function CheckPassword($pword, $login_name, $stored_hash, $password_type = PASSWORD_DEFAULT_TYPE)
	{
		switch ($password_type)
		{
			case PASSWORD_GENERAL_MD5 :
			case PASSWORD_E107_MD5 :
				$pwHash = md5($pword);
				break;

			case PASSWORD_GENERAL_SHA1 :
				if (strlen($stored_hash) != 40) return PASSWORD_INVALID;
				$pwHash = sha1($pword);
				break;

			case PASSWORD_JOOMLA_SALT :
			case PASSWORD_MAMBO_SALT :
				if ((strpos($row['user_password'], ':') === false) || (strlen($row[0]) < 40))
				{
					return PASSWORD_INVALID;
				}
				// Mambo/Joomla salted hash - should be 32-character md5 hash, ':', 16-character salt (but could be 8-char salt, maybe)
				list($hash, $salt) = explode(':', $stored_hash);
				$pwHash = md5($pword.$salt);
				$stored_hash = $hash;
				break;

			case PASSWORD_E107_SALT :
				return UserHandler::CheckPassword($password, $login_name, $stored_hash);
				break;

			case PASSWORD_PHPBB_SALT :
			case PASSWORD_WORDPRESS_SALT :
				if (strlen($stored_hash) != 34) return PASSWORD_INVALID;
				$pwHash = $this->crypt_private($pword, $stored_hash, $password_type);
				if ($pwHash[0] == '*')
				{
					return PASSWORD_INVALID;
				}
				$stored_hash = substr($stored_hash,12);
				break;

			case PASSWORD_PLAINTEXT :
				$pwHash = $pword;
				break;

			default :
				return PASSWORD_INVALID;
		}
		if ($stored_hash != $pwHash) return PASSWORD_INVALID;
		return PASSWORD_VALID;
	}

}


?>