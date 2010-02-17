<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/alt_auth/ldap_auth.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

class auth_login
{

	var $server;
	var $dn;
	var $usr;
	var $pwd;
	var $serverType;
	var $ldapErrorCode;
	var $ldapErrorText;
	var $connection;
	var $result;
	var $ldapVersion;
	var $Available;
	var $filter;

	function auth_login()
	{
		$sql = new db;
		$sql -> db_Select("alt_auth", "*", "auth_type = 'ldap' ");
		while($row = $sql -> db_Fetch())
		{
			$ldap[$row['auth_parmname']]=$row['auth_parmval'];
		}

		$this->server = explode(",", $ldap['ldap_server']);
		$this->serverType = $ldap['ldap_servertype'];
		$this->dn = $ldap['ldap_basedn'];
		$this->usr = $ldap['ldap_user'];
		$this->pwd = $ldap['ldap_passwd'];
		$this->ldapVersion = $ldap['ldap_version'];
		$this->filter = (isset($ldap['ldap_edirfilter']) ? $ldap['ldap_edirfilter'] : "");

		if(!function_exists('ldap_connect'))
		{
			$this->Available = FALSE;
			return false;
		}

		if(!$this -> connect())
		{
			return AUTH_NOCONNECT;
		}
	}

	function connect()
	{
		foreach ($this->server as $key => $host)
		{
			$this->connection = ldap_connect($host);
			if ( $this->connection) {
				if($this -> ldapVersion == 3 || $this->serverType == "ActiveDirectory")
				{
					@ldap_set_option( $this -> connection, LDAP_OPT_PROTOCOL_VERSION, 3 );
				}
				return true;
			}
		}
		
		$this->ldapErrorCode = -1;
		$this->ldapErrorText = "Unable to connect to any server";
		return false;
	}

	function close()
	{
		if ( !@ldap_close( $this->connection))
		{
			$this->ldapErrorCode = ldap_errno( $this->connection);
			$this->ldapErrorText = ldap_error( $this->connection);
			return false;
		}
		else
		{
			return true;
		}
	}

	function login($uname, $pass)
	{
		/* Construct the full DN, eg:-
		** "uid=username, ou=People, dc=orgname,dc=com"
		*/
		if ($this->serverType == "ActiveDirectory")
		{
			$checkDn = "$uname@$this->dn";
		}
		else
		{
			if ($this -> usr != '' && $this -> pwd != '')
			{
				$this -> result = ldap_bind($this -> connection, $this -> usr, $this -> pwd);
			}
			else
			{
				$this -> result = ldap_bind($this -> connection);
			}
			
//			In ldap_auth.php, should look like this instead for eDirectory 
//			$query = ldap_search($this -> connection, $this -> dn, "cn=".$uname);

			if($this->serverType == "eDirectory")
			{
				$_filter = (isset($ldap['ldap_edirfilter']) ? $ldap['ldap_edirfilter'] : "");
				$current_filter = "(&(cn={$uname})".$this->filter.")";
				$query = ldap_search($this->connection, $this->dn, $current_filter);
			}
			else
			{
				$query = ldap_search($this->connection, $this->dn, "uid=".$uname);
			}

			if ($query == false)
			{
//				Could not perform query to LDAP directory
				return AUTH_NOCONNECT;
			}
			else
			{
				$query_result = ldap_get_entries($this -> connection, $query);

				if ($query_result["count"] != 1)
				{
					return AUTH_NOUSER;
				}
				else
				{
					$checkDn = $query_result[0]["dn"];
					$this -> close();
					$this -> connect();
				}
			}
		}
		// Try and connect...
		$this->result = ldap_bind($this -> connection, $checkDn, $pass);
		if ( $this->result)
		{
			// Connected OK - login credentials are fine!
			return AUTH_SUCCESS;
		}
		else
		{
			/* Login failed. Return false, together with the error code and text from
			** the LDAP server. The common error codes and reasons are listed below :
			** (for iPlanet, other servers may differ)
			** 19 - Account locked out (too many invalid login attempts)
			** 32 - User does not exist
			** 49 - Wrong password
			** 53 - Account inactive (manually locked out by administrator)
			*/
			$this->ldapErrorCode = ldap_errno( $this->connection);
			$this->ldapErrorText = ldap_error( $this->connection);

			if($this -> ldapErrorCode == 32)
			{
				return AUTH_NOUSER;
			}
			if($this -> ldapErrorCode == 49)
			{
				return AUTH_BADPASSWORD;
			}
			// return error code as if it never connected, maybe change that in the future
			return AUTH_NOCONNECT;  
		}
	}
}
?>
