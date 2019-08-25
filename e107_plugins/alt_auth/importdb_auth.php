<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	imported DB authorisation for alt_auth plugin
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

/* 
	return values
	AUTH_NOCONNECT 		= unable to connect to db
	AUTH_NOUSER			= user not found	
	AUTH_BADPASSWORD	= supplied password incorrect

	AUTH_SUCCESS 		= valid login
*/


class auth_login extends alt_auth_base
{

	public	$Available = FALSE;		// Flag indicates whether DB connection available
	public	$ErrorText;				// e107 error string on exit
	private $conf;					// Configuration parameters

	
	/**
	 *	Read configuration
	 *
	 *	@return AUTH_xxxx result code
	 */
	public function __construct()
	{
		$this->ErrorText = '';
		$this->conf = $this->altAuthGetParams('importdb');
		$this->Available = TRUE;
	}
	
	
	private function makeErrorText($extra = '')
	{
		$this->ErrorText = $extra;
	}


	/**
	 *	Validate login credentials
	 *
	 *	@param string $uname - The user name requesting access
	 *	@param string $pass - Password to use (usually plain text)
	 *	@param pointer &$newvals - pointer to array to accept other data read from database
	 *	@param boolean $connect_only - TRUE to simply connect to the database
	 *
	 *	@return integer result (AUTH_xxxx)
	 *
	 *	On a successful login, &$newvals array is filled with the requested data from the server
	 */
	public function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
		if ($connect_only) return AUTH_SUCCESS;			// Big problem if can't connect to our own DB!

		// See if the user's in the E107 database - otherwise they can go away
		global $sql, $tp;
		if (!$sql->db_Select('user', 'user_loginname, user_password', "user_loginname = '".$tp -> toDB($uname)."'")) 
		{	// Invalid user
			$this->makeErrorText('User not found');
			return AUTH_NOUSER;
		}

		// Now look at their password - we always need to verify it, even if its a core E107 format.
		// Higher levels will always convert an authorised password to E107 format and save it for us.
		if (!$row = $sql->db_Fetch())
		{
			$this->makeErrorText('Error reading DB');
			return AUTH_NOCONNECT;			// Debateable return code - really a DB error. But consistent with other handler
		}

		require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');		// This auto-loads the 'standard' password handler as well
		$pass_check = new ExtendedPasswordHandler();

		if(empty($this->conf['importdb_password_method']))
		{
			$this->makeErrorText('importdb_password_method not set');
		}



		$passMethod = $pass_check->passwordMapping($this->conf['importdb_password_method']);

		e107::getMessage()->addInfo("Testing with Password Method: ".$this->conf['importdb_password_method']);

		if ($passMethod === FALSE) 
		{
			$this->makeErrorText('Password error - invalid method');
			return AUTH_BADPASSWORD;
		}

		$pwFromDB = $row['user_password'];					// Password stored in DB

		e107::getMessage()->addDebug("Stored Password: ".$pwFromDB);

		if ($pass_check->checkPassword($pword, $uname, $pwFromDB, $passMethod) !== PASSWORD_VALID)
		{
			$this->makeErrorText('Password incorrect');
			return LOGIN_CONTINUE;		// Could have already changed password to E107 format
		}
		$this->makeErrorText('');
		return AUTH_SUCCESS;
	}
}

?>