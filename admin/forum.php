<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/forum.php														|
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
if(!getperms("5")){ header("location:../index.php"); }
require_once("auth.php");

If(IsSet($_POST['submit'])){
	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);

	$sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
	$row = $sql -> db_Fetch();
	$forum_parent = $row['forum_id'];  

	$sql -> db_Insert("forum", "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$forum_parent."', '".time()."', '".$_POST['forum_active']."', '".$mods."', 0, 0, 0");
	unset($forum_name, $forum_description, $forum_parent);
	$message = "Forum added to database.";
}

If(IsSet($_POST['update'])){
	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
	$row = $sql -> db_Fetch();
	$forum_parent = $row['forum_id'];
	$parent = addslashes($parent);
	$sql -> db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$forum_parent."', forum_active='".$_POST['forum_active']."', forum_moderators='".$mods."' WHERE forum_id='".$_POST['forum_id']."' ");
	unset($forum_name, $forum_description, $forum_parent, $forum_active);
	$message = "Forum parent updated in database.";
}

If(IsSet($_POST['psubmit'])){
	$sql -> db_Insert("forum", "0, '".$_POST['parent']."', '', '', '".time()."', '1', '0', '0', '0', '' ");
	unset($parent);
	$message = "Parent added to database.";
}

If(IsSet($_POST['pedit'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['existing']."' ");
	list($forum_id, $parent, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$parent = stripslashes($parent);
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['existing']."' ");
	list($forum_id, $forum_name, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$parent = stripslashes($parent);
}

If(IsSet($_POST['pupdate'])){
	$parent = addslashes($parent);
	$sql -> db_Update("forum", "forum_name='".$_POST['parent']."' WHERE forum_name='".$_POST['existing']."' ");
	unset($parent);
	$message = "Forum parent updated in database.";
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['existing']."' ");
	list($null, $null, $null, $forum_parent) = $sql-> db_Fetch();
	if($forum_parent == 0){	
		$tt = "parent";
	}else{
		$tt = "";
	}
	$sql -> db_Delete("forum", "forum_name='".$_POST['existing']."' ");
	$message = "Forum $tt deleted.";
}

If(IsSet($_POST['delete'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['existing']."' ");
	list($null, $null, $null, $forum_parent) = $sql-> db_Fetch();
	if($forum_parent == 0){
		$tt = "parent";
	}else{
		$tt = "";
	}

	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the '".$_POST['existing']."' forum $tt - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"$PHP_SELF\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Forum", $text);
	
	require_once("footer.php");
	exit;
}
If(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$ns -> tablerender("<div style=\"text-align:center\">Forums</div>", $text);

$forum_parent_total = $sql -> db_Select("forum", "*", "forum_parent='0' ");
if($forum_parent_total == 0){
	$text .= "<div style=\"text-align:center\">No parents yet</div><br />";
}else{
	$text .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
	Existing Parents: 
<select name=\"existing\" class=\"tbox\">";
	$c = 0;
	while(list($forum_id_, $forum_parent_) = $sql-> db_Fetch()){
		$parents[$c] = $forum_parent_;
		$parents_id[$c] = $forum_id_;
		$text .= "<option>".$parents[$c]."</option>";
		$c++;
	}
	$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"pedit\" value=\"Edit\" /> 
<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
</form>
<br />";
}
$text .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:20%\"><u>Parent</u>:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"parent\" size=\"60\" value=\"$parent\" maxlength=\"250\" />
<br /><br />
</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">";

if(IsSet($_POST['pedit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"pupdate\" value=\"Update Parent\" />
<input type=\"hidden\" name=\"existing\" value=\"".$existing."\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"psubmit\" value=\"Create Parent\" />";
}

$text .= "</td>
</tr>
</table>
</form>";

$ns -> tablerender("Parents", $text);

if($forum_parent_total == 0){
	$text = "<div style=\"text-align:center\">You need to define at least one forum parent before creating a forum.</div>";
	$ns -> tablerender("Forums", $text);
	require_once("footer.php");
	exit;
}


$forum_total = $sql -> db_Select("forum", "*", "forum_parent!='0' ");

if($forum_total == "0"){
	$text = "<div style=\"text-align:center\">No forums yet.</div><br />";
}else{
	$text = "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
	Existing Forums: 
	<select name=\"existing\" class=\"tbox\">";
	while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()){
		$text .= "<option>".$forum_name_."</option>";
	}
	$text .= "</select> 
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" /> 
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	</form>
	
	<br />";
}

$text .= "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">

<tr>
<td style=\"width:20%\"><u>Parent</u>:</td>
<td style=\"width:80%\">
<select name=\"parentforum\" class=\"tbox\">";
$c = 0;
	while($parents[$c]){
		if($parents_id[$c] == $forum_parent){
			$text .= "<option selected>".$parents[$c]."</option>";
		}else{
			$text .= "<option>".$parents[$c]."</option>";
		}
		$c++;
	}
$text .= "</select>
</td>
</tr>



<tr>
<td style=\"width:20%\"><u>Name</u>:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"forum_name\" size=\"60\" value=\"$forum_name\" maxlength=\"100\" />
</td>
</tr>
<tr>

<td style=\"width:20%\"><u>Description</u>: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"forum_description\" cols=\"50\" rows=\"5\">$forum_description</textarea>
</td>
</tr>

<tr> 
<td style=\"width:20%\">Activate?:</td>
<td style=\"width:80%\">";


if($forum_active == "1"){
	$text .= "<input type=\"checkbox\" name=\"forum_active\" value=\"1\"  checked> (Check to make forum active)";
}else{
	$text .= "<input type=\"checkbox\" name=\"forum_active\" value=\"1\"> (Check to make forum active)";
}

$text .= "</td><tr>

<td style=\"width:20%\">Moderators: </td>
<td style=\"width:80%\">";

$admin_no = $sql -> db_Select("admin", "*", "admin_permissions REGEXP('.A') OR admin_permissions='0' ");
$text .= "<select name=\"mod[]\" multiple size=\"".$admin_no."\" class=\"tbox\">";

while(list($admin_id_, $admin_name_, $null, $null, $null, $admin_perms_) = $sql-> db_Fetch()){
	$text .= "<option>".$admin_name_."</option>";
}
$text .= "</select>
</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">";


If(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update\" value=\"Update Forum\" />
	<input type=\"hidden\" name=\"forum_id\" value=\"".$forum_id."\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Create Forum\" />";
}

$text .= "</td>
</tr>
<tr>
<td colspan=\"2\"  class=\"smalltext\">
<br />
Tags allowed: all. <u>Underlined</u> fields are required.
</td>
</tr>
</table>
</form>";


$ns -> tablerender("Forums", $text);

require_once("footer.php");
?>	