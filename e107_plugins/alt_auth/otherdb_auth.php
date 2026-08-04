<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Alt_auth plugin - 'otherdb' authorisation handler
 *
 * $URL$
 * $Id$
 * 
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
	 *	@return void result code
	 */
	public function __construct()
	{
		$this->ErrorText = '';
		$this->conf = $this->altAuthGetParams('otherdb');
		$this->Available = TRUE;
	}



	/**
	 *	Retrieve and construct error strings
	 *
	 *	@todo - test whether reconnect to DB is required (shouldn't be)
	 */
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
	 *	@return int result (AUTH_xxxx)
	 *
	 *	On a successful login, &$newvals array is filled with the requested data from the server
	 */
	public function login($uname, $pword, &$newvals, $connect_only = FALSE)
	{
		$db = e107::getDb('alt_auth_otherdb');

		$server = $this->conf['otherdb_server'].':'.varset($this->conf['otherdb_port'], 3306);

		if(!$db->connect($server, $this->conf['otherdb_username'], $this->conf['otherdb_password'], true)
			|| !$db->database($this->conf['otherdb_database'], '', false))
		{
			$this->makeErrorText('Cannot connect to remote DB; '.$db->getLastErrorText());
			return AUTH_NOCONNECT;
		}

		if ($connect_only) return AUTH_SUCCESS;		// Test mode may just want to connect to the DB

		$sel_fields = array();
		// Make an array of the fields we want from the source DB
		foreach($this->conf as $k => $v)
		{
			if ($v && (strpos($k,'otherdb_xf_') === 0))
			{
				$sel_fields[] = $v;
			}
		}

		$sel_fields[] = $this->conf['otherdb_password_field'];
		$user_field = $this->conf['otherdb_user_field'];

		if(!empty($this->conf['otherdb_password_salt']))
		{
			$sel_fields[] = $this->conf['otherdb_password_salt'];
		}

		$columns = array();
		foreach($sel_fields as $field)
		{
			$column = $db->quoteIdentifier($field);
			if($column === false)
			{
				$this->makeErrorText('Invalid field name in alt_auth otherdb configuration');
				return AUTH_NOCONNECT;
			}
			$columns[] = $column;
		}

		$table = $db->quoteIdentifier($this->conf['otherdb_table']);
		$userColumn = $db->quoteIdentifier($user_field);

		if($table === false || $userColumn === false)
		{
			$this->makeErrorText('Invalid table or user field in alt_auth otherdb configuration');
			return AUTH_NOCONNECT;
		}

		//Get record containing supplied login name
		// Permanent raw-SQL boundary: kept as bound execute()/fetch() rather than the query builder - this is a foreign alt_auth DB, so the builder's from() prefix/language routing must NOT apply; the dynamic table/columns are quoteIdentifier()-validated fail-closed above, the only value (:uname) is bound, and the === false guard distinguishes a query error (AUTH_NOCONNECT) from an empty result (AUTH_NOUSER).
		$qry = "SELECT ".implode(',', $columns)." FROM ".$table." WHERE ".$userColumn." = :uname";

		if($db->execute($qry, array('uname' => $uname)) === false)
		{
			$this->makeErrorText('Lookup query failed');
			return AUTH_NOCONNECT;
		}

		if (!$row = $db->fetch('both'))
		{
			$this->makeErrorText('User not found');
			return AUTH_NOUSER;
		}
		
		/** Ancient code that breaks e107's ability to use the original MySQL resource
		if(!$r1 = mysql_query($qry))
		{
			mysql_close($res);
			$this->makeErrorText('Lookup query failed');
			return AUTH_NOCONNECT;
		}
		if(!$row = mysql_fetch_array($r1))
		{
			mysql_close($res);
			$this->makeErrorText('User not found');
			return AUTH_NOUSER;
		}

		mysql_close($res);*/				// Finished with 'foreign' DB now

		// Got something from the DB - see whether password valid
		require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');		// This auto-loads the 'standard' password handler as well
		$pass_check = new ExtendedPasswordHandler();

		$passMethod = $pass_check->passwordMapping($this->conf['otherdb_password_method']);
		if ($passMethod === FALSE) 
		{
			$this->makeErrorText('Password error - invalid method');
			return AUTH_BADPASSWORD;
		}

		$pwFromDB = $row[$this->conf['otherdb_password_field']];					// Password stored in DB
		$salt_field = $this->conf['otherdb_password_salt'];

		if(!empty($salt_field))
		{
			$pwFromDB .= ':'.$row[$salt_field];
		}

		if ($pass_check->checkPassword($pword, $uname, $pwFromDB, $passMethod) !== PASSWORD_VALID)
		{
			$this->makeErrorText('Password incorrect');
			return AUTH_BADPASSWORD;
		}
		// Now copy across any values we have selected
		foreach($this->conf as $k => $v)
		{
			if ($v && (strpos($k,'otherdb_xf_') === 0) && isset($row[$v]))
			{
				$newvals[substr($k,strlen('otherdb_xf_'))] = $row[$v];
			}
		}

		$this->makeErrorText('');		// Success - just reconnect to E107 DB if needed
		return AUTH_SUCCESS;
	}
}


