<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	LDAP authorisation for alt_auth plugin
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

class auth_login extends alt_auth_base
{
	private	$server;		// The LDAP server (array of possible servers)
	private $dn;			// LDAP domain
	private $ou;			// LDAP OU
	private	$usr;			// User name to log on to server
	private $pwd;			// Password to log on to server
	private $serverType;	// Server type = LDAP/AD/eDirectory
	public	$ldapErrorCode;	// LDAP error code on exit
	public	$ldapErrorText;	// LDAP error string on exit
	public	$ErrorText;		// e107 error string on exit
	private	$connection;	// LDAP resource for connection
	private	$ldapVersion;	// Version of LDAP to use
	public	$Available = FALSE;		// Flag indicates whether DB connection available
	private	$filter;		// Filter for eDirectory search
	private	$copyAttribs; 	// Any attributes which are to be copied on successful login
	public	$copyMethods;	// Methods which are to be used to copy attributes



	/**
	 *	Read configuration, initialise connection to LDAP database
	 *
	 *	@return AUTH_xxxx result code
	 */
	public function auth_login()
	{
		$this->copyAttribs = array();
		$this->copyMethods = array();
		$ldap = $this->altAuthGetParams('ldap');

		foreach ($ldap as $row)
		{
			if ((strpos($row['auth_parmname'], 'ldap_xf_') === 0) && $ldap[$row['auth_parmname']]) // Attribute to copy on successful login
			{
				$this->copyAttribs[substr($row['auth_parmname'], strlen('ldap_xf_'))] = $ldap[$row['auth_parmname']]; // Key = LDAP attribute. Value = e107 field name
			}
			elseif ((strpos($row['auth_parmname'], 'ldap_pm_') === 0) && $ldap[$row['auth_parmname']] && ($ldap[$row['auth_parmname']] != 'none')) // Method to use to copy parameter
			{	// Any fields with non-null 'copy' methods
				$this->copyMethods[substr($row['auth_parmname'], strlen('ldap_pm_'))] = $ldap[$row['auth_parmname']]; // Key = e107 field name. Value = copy method
			}
		}
		$this->server = explode(',', $ldap['ldap_server']);
		$this->serverType = $ldap['ldap_servertype'];
		$this->dn = $ldap['ldap_basedn'];
		$this->ou = $ldap['ldap_ou'];
		$this->usr = $ldap['ldap_user'];
		$this->pwd = $ldap['ldap_passwd'];
		$this->ldapVersion = $ldap['ldap_version'];
		$this->filter = (isset($ldap['ldap_edirfilter']) ? $ldap['ldap_edirfilter'] : "");

		if (!function_exists('ldap_connect'))
		{
			return AUTH_NORESOURCE;
		}

		if (!$this->connect())
		{
			return AUTH_NOCONNECT;
		}
		$this->Available = TRUE;
		return AUTH_SUCCESS;
	}


	/**
	 *	Retrieve and construct error strings
	 */
	private function makeErrorText($extra = '')
	{
		$this->ldapErrorCode = ldap_errno($this->connection);
		$this->ldapErrorText = ldap_error($this->connection);
		$this->ErrorText = $extra . ' ' . $this->ldapErrorCode . ': ' . $this->ldapErrorText;
	}


	/**
	 *	Connect to the LDAP server
	 *
	 *	@return boolean TRUE for success, FALSE for failure
	 */
	public function connect()
	{
		foreach ($this->server as $key => $host)
		{
			$this->connection = ldap_connect($host);
			if ($this->connection)
			{
				if ($this->ldapVersion == 3 || $this->serverType == "ActiveDirectory")
				{
					@ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
				}
				return true;
			}
		}

		$this->ldapErrorCode = -1;
		$this->ldapErrorText = "Unable to connect to any server";
		$this->ErrorText = $this->ldapErrorCode . ': ' . $this->ldapErrorText;
		return false;
	}


	/**
	 *	Close the connection to the LDAP server
	 */
	public function close()
	{
		if (!@ldap_close($this->connection))
		{
			$this->makeErrorText(); // Read the error code and explanatory string
			return false;
		}
		else
		{
			return true;
		}
	}


	/**
	 *	Validate login credentials
	 *
	 *	@param string $uname - The user name requesting access
	 *	@param string $pass - Password to use (usually plain text)
	 *	@param pointer &$newvals - pointer to array to accept other data read from database
	 *	@param boolean $connect_only - TRUE to simply connect to the server
	 *
	 *	@return integer result (AUTH_xxxx)
	 *
	 *	On a successful login, &$newvals array is filled with the requested data from the server
	 */
	function login($uname, $pass, &$newvals, $connect_only = false)
	{
		/* Construct the full DN, eg:-
		** "uid=username, ou=People, dc=orgname,dc=com"
		*/
		// echo "Login to server type: {$this->serverType}<br />";
		$current_filter = "";
		if ($this->serverType == "ActiveDirectory")
		{
			$checkDn = $uname . '@' . $this->dn;
			// added by Father Barry Keal
			// $current_filter = "(sAMAccountName={$uname})"; for pre windows 2000
			$current_filter = "(userprincipalname={$uname}@{$this->dn})"; // for 2000 +
			// end add by Father Barry Keal
		}
		else
		{
			if ($this->usr != '' && $this->pwd != '')
			{
				$this->result = ldap_bind($this->connection, $this->usr, $this->pwd);
			}
			else
			{
				$this->result = ldap_bind($this->connection);
			}
			if ($this->result === false)
			{
				// echo "LDAP bind failed<br />";
				$this->makeErrorText(); // Read the error code and explanatory string
				return AUTH_NOCONNECT;
			}
			// In ldap_auth.php, should look like this instead for eDirectory
			// $query = ldap_search($this -> connection, $this -> dn, "cn=".$uname);
			if ($this->serverType == "eDirectory")
			{
				$current_filter = "(&(cn={$uname})" . $this->filter . ")";
			}
			else
			{
				$current_filter = "uid=" . $uname;
			}
			// echo "LDAP search: {$this->dn}, {$current_filter}<br />";
			$query = ldap_search($this->connection, $this->dn, $current_filter);

			if ($query === false)
			{
				// Could not perform query to LDAP directory
				echo "LDAP - search for user failed<br />";
				$this->makeErrorText(); // Read the error code and explanatory string
				return AUTH_NOCONNECT;
			}
			else
			{
				$query_result = ldap_get_entries($this->connection, $query);

				if ($query_result["count"] != 1)
				{
					if ($connect_only) return AUTH_SUCCESS;
					else return AUTH_NOUSER;
				}
				else
				{
					$checkDn = $query_result[0]["dn"];
					$this->close();
					$this->connect();
				}
			}
		}
		// Try and connect...
		$this->result = ldap_bind($this->connection, $checkDn, $pass);
		if ($this->result)
		{
			// Connected OK - login credentials are fine!
			// But bind can return success even if no password! Does reject an invalid password, however
			if ($connect_only) return AUTH_SUCCESS;
			if (trim($pass) == '') return AUTH_BADPASSWORD; // Pick up a blank password
			if (count($this->copyAttribs) == 0) return AUTH_SUCCESS; // No attributes required - we're done
			$ldap_attributes = array_values(array_unique($this->copyAttribs));
			if ($this->serverType == "ActiveDirectory")
			{	// If we are using AD then build up the full string from the fqdn
				$altauth_tmp = explode('.', $this->dn);
				$checkDn='';
				foreach($altauth_tmp as $$altauth_dc)
				{
					$checkDn .= ",DC={$altauth_dc}";
				}
				// prefix with the OU
				$checkDn = $this->ou . $checkDn;
			}
			$this->result = ldap_search($this->connection, $checkDn, $current_filter, $ldap_attributes);
			if ($this->result)
			{
				$entries = ldap_get_entries($this->connection, $this->result);
				if (count($entries) == 2) // All OK
				{
					for ($j = 0; $j < $entries[0]['count']; $j++)
					{
						$k = $entries[0][$j];			// LDAP attribute name
						$tlv = $entries[0][$k];			// Array of LDAP data
						if (is_array($tlv) && count($tempKeys = array_keys($this->copyAttribs,$k))) // This bit executed if we've successfully got some data. Key is the attribute name, then array of data
						{
							foreach ($tempKeys as $tk)		// Single LDAP attribute may be mapped to several fields
							{
//								$newvals[$tk] = $this->translate($tlv[0]); // Just grab the first value
								$newvals[$tk] = $tlv[0]; // Just grab the first value
							}
						}
						else
						{
							// echo " Unexpected non-array value - Key: {$k}   Value: {$tlv}<br />";
							$this->makeErrorText(); // Read the error code and explanatory string
							return AUTH_NOCONNECT; // Not really a suitable return code for this - its an error
						}
					}
				}
				else
				{
					// echo "Got wrong number of entries<br />";
					$this->makeErrorText(); // Read the error code and explanatory string
					return AUTH_NOUSER; // Bit debateable what to return if this happens
				}
			}
			else // Probably a bit strange if we don't get any info back - but possible
				{
					// echo "No results!<br />";
				}

				return AUTH_SUCCESS;
		}
		else
		{
			/* Login failed. Return error code.
			** The common error codes and reasons are listed below :
			** (for iPlanet, other servers may differ)
			** 19 - Account locked out (too many invalid login attempts)
			** 32 - User does not exist
			** 49 - Wrong password
			** 53 - Account inactive (manually locked out by administrator)
			*/
			$this->makeErrorText(); // Read the error code and explanatory string

			switch ($this->ldapErrorCode)
			{
				case 32 :
					return AUTH_NOUSER;
				case 49 :
					return AUTH_BADPASSWORD;
			}
			// return error code as if it never connected, maybe change that in the future
			return AUTH_NOCONNECT;
		}
	}
}


