<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/updateadmin.php											|
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

if(IsSet($_POST['update_settings'])){
	if($_POST['a_name'] != "" && $_POST['a_email'] != "" && $_POST['a_password'] != "" && $_POST['a_password2'] != "" && ($_POST['a_password'] == $_POST['a_password2'])){
		$admin_ip = getip();
		session_destroy(); session_unregister();
		$sql -> db_Update("admin", "admin_name='".$_POST['a_name']."', admin_password='".md5($_POST['a_password'])."', admin_email='".$_POST['a_email']."', admin_pwchange='".time()."' WHERE admin_id='".ADMINID."' ");

		$sql -> db_Update("user", "user_password='".md5($_POST['a_password'])."' WHERE user_name='".ADMINNAME."' ");
		$se = TRUE;
	}else{
		$message = "Error - please re-submit";
	}
}

require_once("auth.php");
if($se == TRUE){
	$text = "<div style=\"text-align:center\">Settings updated.</div>";
	$ns -> tablerender("<div style=\"text-align:center\">Settings Updated for $a_name</div>", $text);
	require_once("footer.php");
	exit;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"$PHP_SELF\">\n

<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Name: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"a_name\" size=\"60\" value=\"".ADMINNAME."\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:30%\">Password: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"password\" name=\"a_password\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Re-type Password: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"password\" name=\"a_password2\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Email: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"a_email\" size=\"60\" value=\"".ADMINEMAIL."\" maxlength=\"100\" />
</td>
</tr>
</td>
</tr>
<tr valign=\"top\"> 
<td colspan=\"2\"  style =\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"update_settings\" value=\"Update settings\" />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">Update Settings for ".ADMINNAME."</div>", $text);

require_once("footer.php");
?>