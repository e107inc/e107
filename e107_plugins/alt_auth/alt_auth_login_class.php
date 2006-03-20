<?php
class alt_login
{
	function alt_login($method, &$username, &$userpass)
	{
		global $pref;
		$newvals=array();
		define("AUTH_SUCCESS", -1);
		define("AUTH_NOUSER", 1);
		define("AUTH_BADPASSWORD", 2);
		define("AUTH_NOCONNECT", 3);
		require_once(e_PLUGIN."alt_auth/".$method."_auth.php");
		$_login = new auth_login;

		if($_login->Available === FALSE)
		{
			return false;
		}

		$login_result = $_login -> login($username, $userpass, $newvals);
		
		if($login_result === AUTH_SUCCESS )
		{
			$sql = new db;
			if(!$sql -> db_Select("user","*","user_loginname='{$username}' "))
			{
				// User not found in e107 database - add it now.
				$qry = "INSERT INTO #user (user_id, user_loginname, user_name, user_join) VALUES ('0','{$username}','{$username}',".time().")";
				$sql -> db_Select_gen($qry);
			}
			// Set password and any other applicable fields
			$qry="user_password='".md5($userpass)."'";
			foreach($newvals as $key => $val)
			{
				$qry .= " ,user_{$key}='{$val}' ";
			}
			$qry.=" WHERE user_loginname='{$username}' ";
			$sql -> db_Update("user", $qry);
		}
		else
		{
			switch($login_result)
			{
				case AUTH_NOUSER:
					if(!isset($pref['auth_nouser']) || !$pref['auth_nouser'])
					{
						$username=md5("xx_nouser_xx");
					}
					break;
				case AUTH_NOCONNECT:
					if(!isset($pref['auth_noconn']) || !$pref['auth_noconn'])
					{
						$username=md5("xx_noconn_xx");
					}
					break;
				case AUTH_BADPASSWORD:
					$userpass=md5("xx_badpassword_xx");
					break;
			}
		} 
	}
}
?>