<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	e107 DB authorisation for alt_auth plugin
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
 *
 *	This connects to a 'foreign' e107 user database to validate the user
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
	 *	Read configuration, initialise connection to remote e107 database
	 *
	 *	@return AUTH_xxxx result code
	 */
	public function __construct()
	{
		$this->ErrorText = '';
		$this->conf = $this->altAuthGetParams('e107db');
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

	/*	if(!$res = mysql_connect($this->conf['e107db_server'], $this->conf['e107db_username'], $this->conf['e107db_password']))
		{
			$this->makeErrorText('Cannot connect to remote server');
			return AUTH_NOCONNECT;
		}


		if(!mysql_select_db($this->conf['e107db_database'], $res))
		{
			mysql_close($res);
			$this->makeErrorText('Cannot connect to remote DB');
			return AUTH_NOCONNECT;
		}
		if ($connect_only) return AUTH_SUCCESS;		// Test mode may just want to connect to the DB
	  */

	//	$dsn = 'mysql:dbname=' . $this->conf['e107db_database'] . ';host=' . $this->conf['e107db_server'];
		$dsn = "mysql:host=".$this->conf['e107db_server'].";port=".varset($this->conf['e107db_port'],3306).";dbname=".$this->conf['e107db_database'];

		try
		{
			$dbh = new PDO($dsn, $this->conf['e107db_username'], $this->conf['e107db_password']);
		}
		catch (PDOException $e)
		{
			$this->makeErrorText('Cannot connect to remote DB; PDOException message: ' . $e->getMessage());
			return AUTH_NOCONNECT;
		}



		$sel_fields = array();
		// Make an array of the fields we want from the source DB
		foreach($this->conf as $k => $v)
		{
			if ($v && (strpos($k,'e107db_xf_') === 0))
			{
				$sel_fields[] = substr($k,strlen('e107db_xf_'));
			}
		}

		$filterClass = intval(varset($this->conf['e107db_filter_class'], e_UC_PUBLIC));
		if (($filterClass != e_UC_PUBLIC) && (!in_array('user_class',$sel_fields)))
		{
			$sel_fields[] = 'user_class';
		}

		$sel_fields[] = 'user_password';
		$user_field = 'user_loginname';


		//Get record containing supplied login name
		$qry = 'SELECT '.implode(',',$sel_fields)." FROM ".$this->conf['e107db_prefix']."user WHERE {$user_field} = '{$uname}' AND `user_ban` = 0";
//	  echo "Query: {$qry}<br />";
		if(!$r1 = $dbh->query($qry))
		{
			$this->makeErrorText('Lookup query failed');
			e107::getMessage()->addDebug($qry);
			return AUTH_NOCONNECT;
		}

		if (!$row = $r1->fetch(PDO::FETCH_BOTH))
		{
			$this->makeErrorText('User not found');
			return AUTH_NOUSER;
		}

	//	mysql_close($res);				// Finished with 'foreign' DB now

		// Got something from the DB - see whether password valid
		require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');		// This auto-loads the 'standard' password handler as well
		$pass_check = new ExtendedPasswordHandler();

		$passMethod = $pass_check->passwordMapping($this->conf['e107db_password_method']);
		if ($passMethod === FALSE) 
		{
			$this->makeErrorText('Password error - invalid method');
			return AUTH_BADPASSWORD;
		}

		$pwFromDB = $row['user_password'];					// Password stored in DB

		if ($pass_check->checkPassword($pword, $uname, $pwFromDB, $passMethod) !== PASSWORD_VALID)
		{
			$this->makeErrorText('Password incorrect');
			return AUTH_BADPASSWORD;
		}

		// Valid user - check he's in an appropriate class
		if ($filterClass != e_UC_PUBLIC)
		{
			$tmp = explode(',', $row['user_class']);
			if (!in_array($filterClass, $tmp))
			{
				$this->makeErrorText('Userc not found');
				return AUTH_NOUSER;			// Treat as non-existent user
			}
			unset($tmp);
		}

		// Now copy across any values we have selected
		foreach($this->conf as $k => $v)
		{
			if ($v && (strpos($k,'e107db_xf_') === 0))
			{
				$f = substr($k,strlen('e107db_xf_'));
				if (isset($row[$f])) $newvals[$f] = $row[$f];
			}
		}
		$this->makeErrorText('');		// Success - just reconnect to E107 DB if needed
		return AUTH_SUCCESS;
	}
}

?>