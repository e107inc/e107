<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/updateadmin.php
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

if(IsSet($_POST['update_settings'])){
	if($_POST['a_name'] != "" && $_POST['a_password'] != "" && $_POST['a_password2'] != "" && ($_POST['a_password'] == $_POST['a_password2'])){
		$sql -> db_Update("user", "user_password='".md5($_POST['a_password'])."', user_pwchange='".time()."' WHERE user_name='".ADMINNAME."' ");
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
<form method=\"post\" action=\"".e_SELF."\">\n

<table style=\"width:95%\" class=\"fborder\">
<tr>
<td style=\"width:30%\" class=\"forumheader3\">Name: </td>
<td style=\"width:70%\" class=\"forumheader3\">
".ADMINNAME."
</td>
</tr>
<tr>
<td style=\"width:30%\" class=\"forumheader3\">Password: </td>
<td style=\"width:70%\" class=\"forumheader3\">
<input class=\"tbox\" type=\"password\" name=\"a_password\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:30%\" class=\"forumheader3\">Re-type Password: </td>
<td style=\"width:70%\" class=\"forumheader3\">
<input class=\"tbox\" type=\"password\" name=\"a_password2\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>

<tr> 
<td colspan=\"2\" style =\"text-align:center\"  class=\"forumheader\">
<input class=\"button\" type=\"submit\" name=\"update_settings\" value=\"Change Password\" />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">Password Update for ".ADMINNAME."</div>", $text);

require_once("footer.php");
?>