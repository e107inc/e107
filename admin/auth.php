<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/auth.php															|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
if(ADMIN == TRUE){
	require_once("header.php");
}else{
	if(IsSet($_POST['authsubmit'])){
		$obj = new auth;

		$row = $authresult = $obj -> authcheck($_POST['authname'], $_POST['authpass']);
		if($row[0] == "fop"){
			header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?e");
			exit;
		}else if($row[0] == "fon"){
			header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?f");
			exit;
		}else{

			$sql -> db_Select("user", "*", "user_name='".$_POST['authname']."'");
			list($user_id, $user_name, $user_pass) = $sql-> db_Fetch();
			$_SESSION['userkey'] = $user_id.".".$user_pass;
			setcookie('userkey', $user_id.".".$user_pass, time()+3600*24*30, '/', '', 0);
			$sql -> db_Update("user", "user_sess='".session_id()."' WHERE user_id='$user_id' ");
			header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
			exit;
		}
	}

	require_once("header.php");

	if($_SERVER['QUERY_STRING'] == "e"){
		$text = "<div style=\"text-align:center\">Incorrect password</div>";
		$ns -> tablerender("Unable to login", $text);
	}
	if($_SERVER['QUERY_STRING'] == "f"){
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
?>