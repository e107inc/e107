<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

/* 
	return values
	AUTH_NOCONNECT 		= unable to connect to db
	AUTH_NOUSER				= user not found	
	AUTH_BADPASSWORD	= supplied password incorrect

	AUTH_SUCCESS 			= valid login
*/

class auth_login
{

	var $od;
	
	function auth_login()
	{
		global $otherdb_conf, $sql;
		$sql -> db_Select("alt_auth", "*", "auth_type = 'otherdb' ");
		while($row = $sql -> db_Fetch())
		{
			$otherdb_conf[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		}
		$class_name = "otherdb_".$otherdb_conf['otherdb_dbtype']."_class";

		if($otherdb_conf['otherdb_dbtype'] == 'e107')
		{
			$class_name = "otherdb_mysql_class";
		}
				
		if(class_exists($class_name))
		{
			$this->od = new $class_name;
		}
		else
		{
			return AUTH_NOCONNECT;
		}
	}

	function login($uname, $pword, &$newvals)
	{
		global $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $sql;
		$ret = $this->od->login($uname, $pword, $newvals);
		$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
		return $ret;
	}

}

class otherdb_mysql_class
{
	
	var $conf;
	
	function otherdb_mysql_class()
	{
		global $otherdb_conf;
		$this->conf = $otherdb_conf;
	}
	
	function login($uname, $pword, &$newvals)
	{

		//Attempt to open connection to sql database
		if(!$res = mysql_connect($this->conf['otherdb_server'], $this->conf['otherdb_username'], $this->conf['otherdb_password']))
		{
			return AUTH_NOCONNECT;
		}

		//Select correct db
		if(!mysql_select_db($this->conf['otherdb_database'], $res))
		{
			mysql_close($res);
			return AUTH_NOCONNECT;
		}

		if($this->conf['otherdb_dbtype'] == 'mysql')
		{
			$sel_fields = $this->conf['otherdb_password_field'];
			$user_field = $this->conf['otherdb_user_field'];
		}
		else
		{
			$sel_fields = 'user_password, user_email, user_join';
			$user_field = "user_loginname";
		}
		

		//Get record containing supplied login name
		$qry = "SELECT {$sel_fields} FROM {$this->conf['otherdb_table']} WHERE {$user_field} = '{$uname}'";
		if(!$r1 = mysql_query($qry))
		{
			mysql_close($res);
			return AUTH_NOCONNECT;
		}
		if(!$row = mysql_fetch_array($r1))
		{
			mysql_close($res);
			return AUTH_NOUSER;
		}
		
		//Compare password in db to supplied password
		if($this->conf['otherdb_password_method'] == 'md5' || $this->conf['otherdb_dbtype'] == 'e107')
		{
			$goodpw = md5($pword) == $row[0];
		}
		else
		{
			$goodpw = $pword == $row[0];
		}
		if($goodpw)
		{
			//Close newly opened mysql connection
			mysql_close($res);
			if($this->conf['otherdb_dbtype'] == 'e107')
			{
				$newvals['email'] = $row[1];
				$newvals['join'] = $row[2];
			}
			return AUTH_SUCCESS;
		}
		
		mysql_close($res);
		return AUTH_BADPASSWORD;
	}
}

?>