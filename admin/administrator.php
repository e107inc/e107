<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/administrator.php											|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("3")){ header("location:../index.php"); }
require_once("auth.php");

if(IsSet($_POST['add_admin'])){
	for ($i=0; $i<=16; $i++){
		if($_POST['perms'][$i]){
			$perm .= $_POST['perms'][$i].".";
		}
	}
	if(!$sql -> db_Select("admin", "*", "admin_name='".$_POST['ad_name']."' ")){
		$sql -> db_Insert("admin", "0, '".$_POST['ad_name']."', '".md5($_POST ['a_password'])."', '".$_POST['ad_email']."', '', '".$perm."', '".time()."' ");
		if(!$sql -> db_Select("user", "*", "user_name='".$_POST['ad_name']."' ")){
			$sql -> db_Insert("user", "0, '".$_POST['ad_name']."', '".md5($_POST['a_password'])."', '', '".$_POST['ad_email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".time()."', '0', '".time()."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '1' ");
		}else{
			$sql -> db_Update("user", "user_admin='1' WHERE user_name='".$_POST['ad_name']."' ");
		}
	}
	$message = "Administrator ".$_POST['ad_name']." added to database.<br />";
}

if(IsSet($_POST['update_admin'])){
	$sql -> db_Select("admin", "*", "admin_id='".$_POST['a_id']."' ");
	$row = $sql -> db_Fetch();
	$a_name = $row['admin_name'];
	if($_POST['a_password'] == ""){
		$admin_password = $row['admin_password'];
	}else{
		$admin_password = md5($_POST['a_password']);
	}

	for ($i=0; $i<=16; $i++){
		if($_POST['perms'][$i]){
			$perm .= $_POST['perms'][$i].".";
		}
	}
	$sql -> db_Update("admin", "admin_name='".$_POST['ad_name']."', admin_password='$admin_password', admin_email='".$_POST['ad_email']."', admin_permissions='".$perm."' WHERE admin_id='".$_POST['a_id']."' ");
	$sql -> db_Update("user", "user_password='$admin_password' WHERE user_name='$a_name' ");
	unset($ad_name, $a_password, $ad_email, $a_perms);
	$message = "Administrator ".$_POST['ad_name']." updated in database.<br />";
}

if(IsSet($_POST['edit'])){
	$sql -> db_Select("admin", "*", "admin_id='".$_POST['existing']."' ");
	list($a_id, $ad_name, $null, $ad_email, $null, $a_perms) = $sql-> db_Fetch();
	if($a_perms == "0"){
		$text = "<div style=\"text-align:center\">$ad_name is the main site administrator and cannot be edited.
		<br /><br />
		<a href=\"administrator.php\">Continue</a></div>";
		$ns -> tablerender("<div style=\"text-align:center\">Error!</div>", $text);
		require_once("footer.php");
		exit;
	}
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("admin", "*", "admin_id='".$_POST['existing']."' ");
	list($admin_id, $admin_name, $admin_password, $admin_email, $admin_ip, $admin_permissions) = $sql-> db_Fetch();

	$text = "<div style=\"text-align:center\">";

	if($admin_permissions == 0){
		$text .= "$admin_name is the main site administrator and cannot be deleted.
		<br /><br />
		<a href=\"administrator.php\">Continue</a>";
		$ns -> tablerender("<div style=\"text-align:center\">Error!</div>", $text);
		require_once("footer.php");
		exit;
	}


	$text .= "<b>Please confirm you wish to delete '$admin_name' from the database - once deleted this administrator's record cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"existing\" value=\"$admin_name\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Administrator", $text);
	
			require_once("footer.php");
	exit;
}


if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($_POST['confirm'])){
	echo "AN: ".$_POST['existing'];
	$sql -> db_Delete("admin", "admin_name='".$_POST['existing']."' ");
	$message = "Administrator deleted.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$sql -> db_Select("admin");

$text = "<div style=\"text-align:center\">";

$text .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
Existing Administrators: 
<select name=\"existing\" class=\"tbox\">";
while(list($admin_id_, $admin_name_) = $sql-> db_Fetch()){
	$text .= "<option value=\"$admin_id_\">".$admin_name_."</option>";
}
$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" /> \n
<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" />\n";

$text .= "<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Admin Name: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"ad_name\" size=\"60\" value=\"$ad_name\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Admin Password: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"a_password\" size=\"60\" value=\"$a_password\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Admin Email: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"ad_email\" size=\"60\" value=\"$ad_email\" maxlength=\"100\" />
</td>
</tr>

<tr> 
<td style=\"width:30%; vertical-align:top\">Permissions: <br /></td>
<td style=\"width:70%\">";

function checkb($arg, $perms){
	if(getperms($arg, $perms)){
		$par = "<input type=\"checkbox\" name=\"perms[]\" value=\"$arg\" checked>";
	}else{
		$par = "<input type=\"checkbox\" name=\"perms[]\" value=\"$arg\">";
	}
	return $par;
}

$text .= checkb("1", $a_perms)."Alter site preferences<br />";
$text .= checkb("2", $a_perms)."Alter Menus<br />";
$text .= checkb("3", $a_perms)."Add site administrators<br />";
$text .= checkb("4", $a_perms)."Moderate users/bans etc<br />";
$text .= checkb("5", $a_perms)."Create/edit forums<br />";
$text .= checkb("6", $a_perms)."Upload files<br />";
$text .= checkb("7", $a_perms)."Oversee news categories<br />";
$text .= checkb("8", $a_perms)."Oversee link categories<br />";
$text .= checkb("9", $a_perms)."Take site down for maintenance<br /><br />";

$text .= checkb("A", $a_perms)."Moderate forums<br />";
$text .= checkb("B", $a_perms)."Moderate comments<br />";
$text .= checkb("C", $a_perms)."Moderate chatbox<br /><br />";

$text .= checkb("H", $a_perms)."Post news<br />";
$text .= checkb("I", $a_perms)."Post links<br />";
$text .= checkb("J", $a_perms)."Post articles<br />";
$text .= checkb("K", $a_perms)."Post reviews<br />";
$text .= checkb("L", $a_perms)."Post content pages<br />";
$text .= checkb("M", $a_perms)."Welcome message<br />";
$text .= checkb("N", $a_perms)."Moderate submitted news<br /><br />";

$text .= checkb("P", $a_perms)."Configure plugins<br />";

$text .= "
</td>
</tr>";

$text .= "<tr style=\"vertical-align:top\"> 
<td colspan=\"2\" style=\"text-align:center\"><br />";

if(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update_admin\" value=\"Update administrator\" />
	<input type=\"hidden\" name=\"a_id\" value=\"$a_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"add_admin\" value=\"Add administrator\" />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">Site Administrators</div>", $text);

require_once("footer.php");
?>