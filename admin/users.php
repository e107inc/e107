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

if($action == "ban"){
	$sql -> db_Update("user", "user_ban='1' WHERE user_id='$id' ");
	$message = "User banned.";
}

if($action == "unban"){
	$sql -> db_Update("user", "user_ban='0' WHERE user_id='$id' ");
	$message = "User unbanned.";
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Delete("user", "user_id='".$_POST['id']."' ");
	$message = "User deleted.";
}

If(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

If($action == "del"){
	$sql -> db_Select("user", "*", "user_id='$id' ");
	list($user_id, $user_name)  = $sql -> db_Fetch();
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete this member ($user_name) - once deleted the record cannot be retrieved</b>
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
while(list($user_id, $user_name, $null, $user_sess, $user_email, $user_homepage, $user_icq, $user_aim, $user_msn, $user_location, $user_birthday, $user_signature, $user_image, $user_timezone, $user_hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_new, $user_viewed, $user_visits)  = $sql -> db_Fetch()){

	
	$text .= "<tr>
	<td style=\"width:10%\">$user_id</td>
	<td style=\"width:20%\">$user_name</td>
	<td style=\"width:20%\"><a href=\"mailto:".$user_email."\">".$user_email."</a></td>
	<td style=\"width:50%\">[<a href=\"userinfo.php?".$user_ip."\">Info</a>] [<a href=\"../usersettings.php?$user_id\">Edit</a>] [<a href=\"users.php?del.$user_id\">Delete</a>] [";
	if($user_ban == 0){
		$text .= "<a href=\"users.php?ban.$user_id\">Ban</a>] [Status: Active]";
	}else{
		$text .= "<a href=\"users.php?unban.$user_id\">Unban</a>] [Status: Banned]";
	}
	
	$text .= "</td>
	</tr>";

}

$text .= "</table>";

$ns -> tablerender("<div style=\"text-align:center\">Member Moderation</div>", $text);
require_once("footer.php");
?>