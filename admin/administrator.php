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
if(ADMINPERMS != 0 && ADMINPERMS != 1){ header("location:../index.php"); }
require_once("auth.php");

if(IsSet($_POST['add_admin'])){

//	if(!$sql -> db_Select("admin", "*", "admin_password='".$_POST['ad_name']."' ")){


	if(!$sql -> db_Select("admin", "*", "admin_name='".$_POST['ad_name']."' ")){
		$sql -> db_Insert("admin", "0, '".$_POST['ad_name']."', '".md5($_POST ['a_password'])."', '".$_POST['ad_email']."', '', '".$_POST['a_perms']."', '".time()."' ");
		if(!$sql -> db_Select("user", "*", "user_name='".$_POST['ad_name']."' ")){
			$sql -> db_Insert("user", "0, '".$_POST['ad_name']."', '".md5($_POST['a_password'])."', '', '".$_POST['ad_email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".time()."', '0', '".time()."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '1' ");
		}else{
			$sql -> db_Update("user", "user_admin='1' WHERE user_name='".$_POST['ad_name']."' ");
		}
	}
	$message = "Administrator ".$_POST['ad_name']." added to database.<br />";
}

if(IsSet($_POST['update_admin'])){
	if($_POST['a_password'] != ""){
		$sql -> db_Update("admin", "admin_name='".$_POST['ad_name']."', admin_password='".md5($_POST['a_password'])."', admin_email='".$_POST['ad_email']."', admin_permissions='".$_POST['a_perms']."' WHERE admin_id='".$_POST['a_id']."' ");
		unset($ad_name, $a_password, $ad_email, $a_perms);
		$message = "Administrator ".$_POST['ad_name']." updated in database.<br />";
	}else{
		$message = "Password field left blank.<br />";
	}
}

if(IsSet($_POST['edit'])){
	$sql -> db_Select("admin", "*", "admin_id='".$_POST['existing']."' ");
	list($a_id, $ad_name, $null, $ad_email, $null, $a_perms) = $sql-> db_Fetch();
	if($a_perms == 0){
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
<td style=\"width:30%\">Permissions: </td>
<td style=\"width:70%\">
<select name=\"a_perms\" class=\"tbox\">";
if($a_perms == 1){ $text .= "<option selected>1 (super admin)</option>"; }else{ $text .= "<option>1 (super admin)</option>"; }
if($a_perms == 2 || $a_perms == ""){ $text .= "<option selected>2 (general admin)</option>"; }else{ $text .= "<option>2 (general admin)</option>"; }
if($a_perms == 3){ $text .= "<option selected>3 (restricted admin)</option>"; }else{ $text .= "<option>3 (restricted admin)</option>"; }
if($a_perms == 4){ $text .= "<option selected>4 (forum admin)</option>"; }else{ $text .= "<option>4 (forum admin)</option>"; }

$text .= "</select>
</td>
</tr>";

$text .= "<tr style=\"vertical-align:top\"> 
<td colspan=\"2\" style=\"text-align:center\">";

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