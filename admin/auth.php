<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/auth.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
if(ADMIN == TRUE){
	$language =  $pref['sitelanguage'][1]; if(!$language){ $language = "English"; }
	if(file_exists("languages/admin_lan_".$language.".php")){
		require_once("languages/admin_lan_".$language.".php");
	}else if(file_exists("languages/admin_lan_English.php")){
		require_once("languages/admin_lan_English.php");
	}else{
		require_once("../languages/admin_lan_English.php");
	}
	if(file_exists("header.php")){
		require_once("header.php");
	}else{
		require_once("../header.php");
	}
}else{
	if($_POST['authsubmit']){
		$obj = new auth;

		$row = $authresult = $obj -> authcheck($_POST['authname'], $_POST['authpass']);
		if($row[0] == "fop"){
			echo "<script type='text/javascript'>document.location.href='admin.php?e'</script>\n";
		}else if($row[0] == "fon"){
			echo "<script type='text/javascript'>document.location.href='admin.php?f'</script>\n";
		}else{

			$sql -> db_Select("user", "*", "user_name='".$_POST['authname']."'");
			list($user_id, $user_name, $userpass) = $sql-> db_Fetch();
			if($pref['tracktype'][1] == "session"){
				$_SESSION['userkey'] = $user_id.".".$userpass;
			}else{
				setcookie('userkey', $user_id.".".$userpass, time()+3600*24*30, '/', '', 0);
//				echo $user_id.".".$userpass;
			}
			echo "<script type='text/javascript'>document.location.href='admin.php'</script>\n";
		}
	}

	require_once("header.php");

	if(e_QUERY == "e"){
		$text = "<div style=\"text-align:center\">Incorrect password</div>";
		$ns -> tablerender("Unable to login", $text);
	}
	if(e_QUERY == "f"){
		$text = "<div style=\"text-align:center\">Administrator name not found in database</div>";
		$ns -> tablerender("Unable to login", $text);
	}

	if(ADMIN == FALSE){
		$obj = new auth;
		$obj -> authform();
		require_once("footer.php");
		exit;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class auth{
	
	function authform(){
		/*
		# Admin auth login
		#
		# - parameters		none
		# - return				null
		# - scope					public
		*/
		echo "<div style=\"align:center\">";
		$text =  "<form method=\"post\" action=\"".e_SELF."\">\n
<table style=\"width:40%\" align=\"center\">
<tr>
<td style=\"width:15%\" class=\"defaulttext\">".LAN_16."</td>
<td><input class=\"tbox\" type=\"text\" name=\"authname\" size=\"30\" value=\"$authname\" maxlength=\"20\" />\n</td>
</tr>
<tr>
<td style=\"width:15%\" class=\"defaulttext\">".LAN_17."</td>
<td><input class=\"tbox\" type=\"password\" name=\"authpass\" size=\"30\" value=\"\" maxlength=\"20\" />\n</td>
</tr>
<tr>
<td style=\"width:15%\"></td>
<td>
<input class=\"button\" type=\"submit\" name=\"authsubmit\" value=\"Log In\" /> 
</td>
</tr>
</table>";

$au = new table;
$au -> tablerender(LAN_18, $text);
echo "</div>";
	}

	function authcheck($authname, $authpass){
		/*
		# Admin auth check
		# - parameter #1:		string $authname, entered name
		# - parameter #2:		string $authpass, entered pass
		# - return				boolean if fail, else result array
		# - scope					public
		*/
		$sql_auth = new db;
		if($sql_auth -> db_Select("user", "*", "user_name='$authname' AND user_admin='1' ")){
			if($sql_auth -> db_Select("user", "*", "user_name='$authname' AND user_password='".md5($authpass)."' AND user_admin='1' ")){
				$row = $sql_auth -> db_Fetch();
				return $row;
			}else{
				$row = array("fop");
				return $row;
			}
		}else{
			$row = array("fon");
			return $row;
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//


?>