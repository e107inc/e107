<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/users.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");



if(e_QUERY != ""){
	$qs = explode(".", e_QUERY);
	$from = $qs[0];
	$order = $qs[1];
	$ordert = $qs[2];
	$view = $qs[3];
	$action = $qs[4];
	$id = $qs[5];
}else{
	if(!$_POST['order'] ? $order="user_id" : $order = $_POST['order']);
	if(!$_POST['ordert'] ? $ordert="DESC" : $ordert = $_POST['ordert']);
	if(!$_POST['view'] ? $view="20" : $view = $_POST['view']);
	if(!$from){ $from = 0; }
}

if($action == "uta"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("user", "user_admin='1' WHERE user_id='$id' ");
	$message = $user_name." now listed an Administrator - to set permissions please go to the <a href=\"administrator.php\">Administrator page</a>.";
}

if($action == "utr"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	if($user_perms == "0"){
		$message = "You cannot remove admin status of main site admin";
	}else{
		$sql -> db_Update("user", "user_admin='0' WHERE user_id='$id' ");
		$message = $user_name." has had Administrator status removed.";
	}
}


if($action == "ban"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	if($user_perms == "0"){
		$message = "You cannot ban the main site administrator";
	}else{
		$sql -> db_Update("user", "user_ban='1' WHERE user_id='$id' ");
		$message = "User banned.";
	}
}

if($action == "unban"){
	$sql -> db_Update("user", "user_ban='0' WHERE user_id='$id' ");
	$message = "User unbanned.";
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Select("user", "*", "user_id='".$_POST['id']."' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$sql -> db_Delete("user", "user_id='".$_POST['id']."' ");
	$message = "User deleted.";
}

If(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if($action == "del"){
	$sql -> db_Select("user", "*", "user_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$text = "<div style=\"text-align:center\">";
	if($user_admin == 1 && $user_perms == "0"){
		$message = "You cannot delete the main site administrator.</b></div>";
	}else{
		$text .= "<b>Please confirm you wish to delete this member ($user_name) - once deleted the record cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" />
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" />
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
	$ns -> tablerender("Confirm Delete Article", $text);
	require_once("footer.php");
	exit;
	}
}


$searchquery = $_POST['searchquery'];

$text .= "<div style='text-align:center'>
<table style='width:95%' class=\"fborder\">
<tr>
<td style=\"text-align:center\" colspan=\"2\">";
if($message){ $text .= $message."<br /><br />"; }
$text .= "<form method=\"post\" action=\"".e_SELF."\">
Search <input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"$searchquery\" maxlength=\"50\" />
<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"".LAN_180."\" />
&nbsp;&nbsp;
Order by <select name=\"order\" class=\"tbox\">";
$text .= ($order == "user_id" ? "<option value=\"user_id\" selected>User ID</option>" : "<option value=\"user_id\">User ID</option>");
$text .= ($order == "user_name" ? "<option value=\"user_name\" selected>User name</option>" : "<option value=\"user_name\">User name</option>");
$text .= ($order == "user_visits" ? "<option value=\"user_visits\" selected>Visits to site</option>" : "<option value=\"user_visits\">Visits to site</option>");
$text .= ($order == "user_admin" ? "<option value=\"user_admin\" selected>Admin</option>" : "<option value=\"user_admin\">Admin</option>");
$text .= ($order == "user_ban" ? "<option value=\"user_ban\" selected>Status</option>" : "<option value=\"user_ban\">Status</option>");
$text .= "</select>
<select name=\"ordert\" class=\"tbox\">";
$text .= ($ordert == "ASC" ? "<option value=\"DESC\" selected>Descending</option>" : "<option value=\"DESC\">Descending</option>");
$text .= ($ordert == "ASC" ? "<option value=\"ASC\" selected>Ascending</option>" : "<option value=\"ASC\">Ascending</option>");
$text .= "</select>
View
<select name=\"view\" class=\"tbox\">";
$text .= ($view == "10" ? "<option value=\"10\" selected>10</option>" : "<option value=\"10\">10</option>");
$text .= ($view == "25" ? "<option value=\"25\" selected>25</option>" : "<option value=\"25\">25</option>");
$text .= ($view == "50" ? "<option value=\"50\" selected>50</option>" : "<option value=\"50\">50</option>");
$text .= ($view == "75" ? "<option value=\"75\" selected>75</option>" : "<option value=\"75\">75</option>");
$text .= ($view == "100" ? "<option value=\"100\" selected>100</option>" : "<option value=\"100\">100</option>");
$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"sortsubmit\" value=\"Sort\" />
</form>
</td>";

$total = $sql -> db_Count("user");
if(IsSet($_POST['searchsubmit'])){
	$results = $sql -> db_Select("user", "*", "user_name REGEXP('".$searchquery."')");
}else{
	$sql -> db_Select("user", "*", "ORDER BY $order $ordert LIMIT $from, $view", "nowhere");
}
while($row = $sql -> db_Fetch()){
	extract($row);
	$text .= "<tr class=\"border\"><td class=\"fcaption\" style=\"width:40%\">";
	if($user_admin){ $text .= "[Admin] "; }
	$text .= $user_id.".".$user_name."&nbsp;&nbsp;
	</td>
	<td class=\"forumtable2\" style=\"width:60%; text-align:center\">
	[<a href=\"userinfo.php?".$user_ip."\">Info</a>] [<a href=\"../usersettings.php?$user_id\">Edit</a>] [<a href=\"users.php?$from.$order.$ordert.$view.del.$user_id\">Delete</a>]";
	if($user_ban == 0){
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.ban.$user_id\">Ban</a>]";
	}else if($user_ban == 2){
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.ban.$user_id\">Ban -unactivated-</a>]";
	}else{
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.unban.$user_id\">Unban</a>]";
	}
	if($user_admin == 1 ? $text .= " [<a href=\"users.php?$from.$order.$ordert.$view.utr.$user_id\">Remove admin status</a>]" : $text .= " [<a href=\"users.php?$from.$order.$ordert.$view.uta.".$user_id.".".e_QUERY."\">Make admin</a>]");
	$text .= "[<a href=\"userclass.php?".$user_id."\">Set Class</a>]</td>
	</tr>";
}
$text .= "</table>
</div>";
$ns -> tablerender("Members", $text);

if(IsSet($_POST['searchsubmit'])){
	echo "<div style='text-align:center'>Search returned ".$results." result(s).</div>";
}else{
	require_once(e_BASE."classes/np_class.php");
	$ix = new nextprev("users.php", $from, $view, $total, LAN_315, $order.".".$ordert.".".$view);
}




























require_once("footer.php");

/*
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
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" />
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" />
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Article", $text);
require_once("footer.php");
exit;
}

$sql -> db_Select("user", "*", "user_admin=0 ORDER BY user_name");

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
	}else if($user_ban == 2){
		$text .= "<a href=\"users.php?ban.$user_id\">Ban -unactivated-</a>]";
	}else{
		$text .= "<a href=\"users.php?unban.$user_id\">Unban</a>]";
	}
	
	$text .= "</td>
	</tr>";

}

$text .= "</table>";

$ns -> tablerender("<div style=\"text-align:center\">Member Moderation</div>", $text);
require_once("footer.php");
*/
?>