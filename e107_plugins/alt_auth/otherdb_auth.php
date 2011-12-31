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

class auth_login
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
		$this->conf = altAuthGetParams('otherdb');
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
		//global $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $sql;
		//$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
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
		//Attempt to open connection to sql database
		if(!$res = mysql_connect($this->conf['otherdb_server'], $this->conf['otherdb_username'], $this->conf['otherdb_password']))
		{
			$this->makeErrorText('Cannot connect to remote server');
			return AUTH_NOCONNECT;
		}
		//Select correct db
		if(!mysql_select_db($this->conf['otherdb_database'], $res))
		{
			mysql_close($res);
			$this->makeErrorText('Cannot connect to remote DB');
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
		if (isset($this->conf['otherdb_salt_field']))
		{
			$sel_fields[] = $this->conf['otherdb_salt_field'];
		}


		//Get record containing supplied login name
		$qry = "SELECT ".implode(',',$sel_fields)." FROM {$this->conf['otherdb_table']} WHERE {$user_field} = '{$uname}'";
//	  echo "Query: {$qry}<br />";
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

		mysql_close($res);				// Finished with 'foreign' DB now

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
		if ($salt_field) $pwFromDB .= ':'.$row[$salt_field];

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

?>