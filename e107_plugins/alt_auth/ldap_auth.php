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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/ldap_auth.php,v $
|     $Revision: 1.2 $
|     $Date: 2008-07-25 19:33:02 $
|     $Author: e107steved $

To do:
	1. Sort out a method of just checking the connection on login (needed for test)
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
	var $copyAttribs;			// Any attributes which are to be copied on successful login

	function auth_login()
	{
		$this->copyAttribs = array();
		$sql = new db;
		$sql -> db_Select("alt_auth", "*", "auth_type = 'ldap' ");
		while($row = $sql -> db_Fetch())
		{
		  $ldap[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		  if ((strpos($row['auth_parmname'],'ldap_xf_') === 0) && $ldap[$row['auth_parmname']])
		  {	// Attribute to copy on successful login
			$this->copyAttribs[$ldap[$row['auth_parmname']]] = substr($row['auth_parmname'],strlen('ldap_xf_'));	// Key = LDAP attribute. Value = e107 field name
			unset($row['auth_parmname']);
		  }
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



	function login($uname, $pass, &$newvals, $connect_only = FALSE)
	{
		/* Construct the full DN, eg:-
		** "uid=username, ou=People, dc=orgname,dc=com"
		*/
//		echo "Login to server type: {$this->serverType}<br />";
		$current_filter = "";
		if ($this->serverType == "ActiveDirectory")
		{
		  $checkDn = $uname.'@'.$this->dn;
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
		  if ($this->result === FALSE)
		  {
//		    echo "LDAP bind failed<br />";
			return AUTH_NOCONNECT;
		  }
			
//			In ldap_auth.php, should look like this instead for eDirectory 
//			$query = ldap_search($this -> connection, $this -> dn, "cn=".$uname);

		  if($this->serverType == "eDirectory")
		  {
			$current_filter = "(&(cn={$uname})".$this->filter.")";
		  }
		  else
		  {
			$current_filter = "uid=".$uname;
		  }
//		  echo "LDAP search: {$this->dn}, {$current_filter}<br />";
		  $query = ldap_search($this->connection, $this->dn, $current_filter);

		  if ($query === false)
		  {
//				Could not perform query to LDAP directory
			echo "LDAP - search for user failed<br />";
			return AUTH_NOCONNECT;
		  }
		  else
		  {
			$query_result = ldap_get_entries($this -> connection, $query);

			if ($query_result["count"] != 1)
			{
			  if ($connect_only) return AUTH_SUCCESS; else return AUTH_NOUSER;
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
		  // But bind can return success even if no password! Does reject an invalid password, however
		  if ($connect_only) return AUTH_SUCCESS;
		  if (trim($pass) == '') return AUTH_BADPASSWORD;				// Pick up a blank password
		  if (count($this->copyAttribs) == 0) return AUTH_SUCCESS;		// No attributes required - we're done
		  $ldap_attributes = array_keys($this->copyAttribs);
//		  echo "Validation search: {$checkDn}, {$current_filter},"; print_a($ldap_attributes); echo "<br />";
		  $this->result = ldap_search($this -> connection, $checkDn, $current_filter, $ldap_attributes);

		  if ($this->result)
		  {
			$entries = ldap_get_entries($this->connection, $this->result);
//			print_a($entries);
			if (count($entries) == 2)
			{ // All OK
			  for ($j = 0; $j < $entries[0]['count']; $j++)
			  {
				$k = $entries[0][$j];
				$tlv = $entries[0][$k];
				if (is_array($tlv) && isset($this->copyAttribs[$k]))
				{ // This bit executed if we've successfully got some data. Key is the attribute name, then array of data
				  $newvals[$this->copyAttribs[$k]] = $tlv[0];				// Just grab the first value
//				  echo $j.":Key: {$k} (Values: {$tlv['count']})";
//				  for ($i = 0; $i < $tlv['count']; $i++) { echo '  '.$tlv[$i]; }
//				  echo "<br />";
				}
				else
				{
//				  echo " Unexpected non-array value - Key: {$k}   Value: {$tlv}<br />";
				  return AUTH_NOCONNECT;  		// Not really a suitable return code for this - its an error
				}
			  }
			}
			else
			{
//			  echo "Got wrong number of entries<br />";
			  return AUTH_NOUSER;			// Bit debateable what to return if this happens
			}
		  }
		  else
		  {	// Probably a bit strange if we don't get any info back - but possible
//			echo "No results!<br />";
		  }

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

			switch ($this -> ldapErrorCode)
			{
			  case 32 :
				return AUTH_NOUSER;
			  case  49 :
				return AUTH_BADPASSWORD;
			}
			// return error code as if it never connected, maybe change that in the future
			return AUTH_NOCONNECT;  
		}
	}
}
?>
