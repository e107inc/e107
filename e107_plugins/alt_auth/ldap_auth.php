<?php

class auth_login {

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

	function auth_login()
	{
		$sql = new db;
		$sql -> db_Select("alt_auth","*","auth_type = 'ldap' ");
		while($row = $sql -> db_Fetch())
		{
			extract($row);
			$ldap[$auth_parmname]=$auth_parmval;
		}


		$this -> server = explode(",",$ldap['ldap_server']);
		$this -> serverType = $ldap['ldap_servertype'];
		$this -> dn = $ldap['ldap_basedn'];
		$this -> usr = $ldap['ldap_user'];
		$this -> pwd = $ldap['ldap_passwd'];
		$this -> ldapVersion = $ldap['ldap_version'];

		if(!$this -> connect())
		{
			echo "could not connect";
			exit;
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
			//            echo "here";
		}
		else
		{
//			echo $this -> dn."<br />";
//			echo "uid=".$uname."<br />";
			if ($this -> usr != '' && $this -> pwd != '')
			{
				$this -> result = ldap_bind($this -> connection, $this -> usr, $this -> pwd);
			}
			else
			{
				$this -> result = ldap_bind($this -> connection);
			}
			$query = ldap_search($this -> connection, $this -> dn, "uid=".$uname);
			if ($query == false)
			{
				echo "Could not perform query to LDAP directory.";
				exit;
			}
			else
			{
//				echo "getting entries";
				$query_result = ldap_get_entries($this -> connection, $query);
//				echo $query_result["count"]."<br />";

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

//			echo $this->ldapErrorCode."<br />";
//			echo $this->ldapErrorText;
//			exit;


			if($this -> ldapErrorCode == 32)
			{
				return AUTH_NOUSER;
			}
			if($this -> ldapErrorCode == 49)
			{
				return AUTH_BADPASSWORD;
			}
			return false;
		}
	}
}
?>
