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
	require_once("header.php");
}else{
	if($_POST['authsubmit']){

		echo "<b>DEBUG</b><br />";

		$obj = new auth;

		$row = $authresult = $obj -> authcheck($_POST['authname'], $_POST['authpass']);
		if($row[0] == "fop"){
			header("Location: ".e_SELF."?e");
			exit;
		}else if($row[0] == "fon"){
			header("Location: ".e_SELF."?f");
			exit;
		}else{

			$sql -> db_Select("user", "*", "user_name='".$_POST['authname']."'");
			list($user_id, $user_name, $user_pass) = $sql-> db_Fetch();

			setcookie('userkey', $user_id.".".$user_pass, time()+3600*24*30, '/', '', 0);
			header("Location: ".e_SELF);
			exit;
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
?>