<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/users.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(ADMINPERMS != 0 && ADMINPERMS != 1){
	header("location:../index.php");
}
require_once("auth.php");

$qs = explode(".", $_SERVER['QUERY_STRING']);
$action = $qs[0];
$id = $qs[1];

if($action == "uta"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("user", "user_admin='1' WHERE user_id='$id' ");
	$sql -> db_Insert("admin", "0, '$user_name', '$user_password', '$user_email', '', '3', '".time()."' ");
	$message = $user_name." now listed a Level 3 Administrator - to edit please go to the <a href=\"administrator.php\">Administrator page</a>.";
}

if($action == "utr"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("user", "user_admin='0' WHERE user_id='$id' ");
	$sql -> db_Delete("admin", "admin_name='$user_name'");
	$message = $user_name." has had Administrator status removed.";
}


if($action == "ban"){
	$sql -> db_Update("user", "user_ban='1' WHERE user_id='$id' ");
	$message = "User banned.";
}

if($action == "unban"){
	$sql -> db_Update("user", "user_ban='0' WHERE user_id='$id' ");
	$message = "User unbanned.";
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Select("user", "*", "user_id='".$_POST['id']."' ");
	$row = $sql -> db_Fetch();
	extract($row);
	if($user_admin == 1){
		$sql -> db_Delete("admin", "admin_name='$user_name' ");
		$sql -> db_Delete("user", "user_id='".$_POST['id']."' ");
		$message = "User deleted (User was also an administrator - admin entry also deleted.)";
	}else{
		$sql -> db_Delete("user", "user_id='".$_POST['id']."' ");
		$message = "User deleted.";
	}
}

If(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if($action == "del"){
	$sql -> db_Select("user", "*", "user_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$text = "<div style=\"text-align:center\">";
	if($user_admin == 1){
		$sql2 = new db;
		if($sql2 -> db_Select("admin", "*", "admin_name='$user_name' ")){
			$row = $sql2 -> db_Fetch();
			extract($row);
			if($admin_permissions == 0){
				$text .= "<b>You cannot delete the main site administrator.</b></div>";
				$ns -> tablerender("Unable to delete", $text);
				require_once("footer.php");
				exit;
			}
		}
	}
	$text .= "<b>Please confirm you wish to delete this member ($user_name) - once deleted the record cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"users.php\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" />
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" />
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Article", $text);
require_once("footer.php");
exit;
}

$sql -> db_Select("user", "*", "ORDER BY user_name", $mode="no_where");

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$text = "<div style=\"text-align:center\">Members sorted alphabetically</div><br />
<table style=\"width:95%\">";
while($row = $sql -> db_Fetch()){
	extract($row);
	
	$text .= "<tr>
	<td style=\"width:2%\">$user_id</td>
	<td style=\"width:20%\">";
	if($user_admin == 1){
		$text .= "<b>[Admin]</b> ";
	}
	$text .= "$user_name</td>
	<td style=\"width:20%\"><a href=\"mailto:".$user_email."\">".$user_email."</a></td>
	<td style=\"width:50%\">[<a href=\"userinfo.php?".$user_ip."\">Info</a>] [<a href=\"../usersettings.php?$user_id\">Edit</a>] [<a href=\"users.php?del.$user_id\">Delete</a>] [";
	
	if($user_admin == 1){
		$text .= "<a href=\"users.php?utr.$user_id\">Remove admin status</a>] [";
	}else{
		$text .= "<a href=\"users.php?uta.$user_id\">Make admin</a>] [";
	}
	if($user_ban == 0){
		$text .= "<a href=\"users.php?ban.$user_id\">Ban</a>]";
	}else{
		$text .= "<a href=\"users.php?unban.$user_id\">Unban</a>]";
	}
	
	$text .= "</td>
	</tr>";

}

$text .= "</table>";

$ns -> tablerender("<div style=\"text-align:center\">Member Moderation</div>", $text);
require_once("footer.php");
?>