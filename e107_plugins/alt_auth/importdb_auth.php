<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/importdb_auth.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

/* 
	return values
	AUTH_NOCONNECT 		= unable to connect to db
	AUTH_NOUSER			= user not found	
	AUTH_BADPASSWORD	= supplied password incorrect

	AUTH_SUCCESS 		= valid login
*/

class auth_login
{

	var $conf;
	var $ErrorText;
	
	function auth_login()
	{
		global $sql;
		$this->ErrorText = '';
		$this->conf = array();
		if (!$sql -> db_Select("alt_auth", "*", "auth_type = 'importdb' ")) return AUTH_NOCONNECT;	// We should get at least one value
		while ($row = $sql -> db_Fetch())
		{
			$this->conf[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		}
		$this->Available = TRUE;
	}
	
	
	function makeErrorText($extra = '')
	{
		$this->ErrorText = $extra;
	}


	function login($uname, $pword, &$newvals, $connect_only = FALSE)
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

		$passMethod = $pass_check->passwordMapping($this->conf['importdb_password_method']);
		if ($passMethod === FALSE) 
		{
			$this->makeErrorText('Password error - invalid method');
			return AUTH_BADPASSWORD;
		}

		$pwFromDB = $row['user_password'];					// Password stored in DB
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