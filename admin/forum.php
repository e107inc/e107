<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/forum.php
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
if(!getperms("5")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

If(IsSet($_POST['submit'])){

	if($_POST['forum_all']){
		$forum_class_s = "";
	}else{
		$count = 0; unset($forum_class_s);
		while($_POST['forum_class'][$count]){
			$forum_class_s .= $_POST['forum_class'][$count]."|";
			$count++;
		}
		if(substr($forum_class_s, -1) == "|"){
			$forum_class_s = substr($forum_class_s, 0, -1);
		}
	}

	if($_POST['forum_active'] == "0" ? $forum_active = 0 : $forum_active = 1);

	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);

	$sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
	$row = $sql -> db_Fetch();
	$forum_parent = $row['forum_id']; 

	$sql -> db_Insert("forum", "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$forum_parent."', '".time()."', '$forum_active', '".$mods."', 0, 0, 0, '$forum_class_s' ");
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

	if($_POST['forum_all']){
		$forum_class_s = "";
	}else{
		$count = 0; unset($forum_class_s);
		while($_POST['forum_class'][$count]){
			$forum_class_s .= $_POST['forum_class'][$count]."|";
			$count++;
		}

//		echo $forum_class_s."<br />".substr($forum_class_s, -1)."<br />".substr($forum_class_s, 0, -1)."<br />";

		if(substr($forum_class_s, -1) == "|"){
			$forum_class_s = substr($forum_class_s, 0, -1);
		}
	}

//	echo "<b>".$forum_class_s."</b><br />";

	if($_POST['forum_active'] == "0" ? $forum_active = 0 : $forum_active = 1);

	$sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
	$row = $sql -> db_Fetch();
	$forum_parent = $row['forum_id'];
	$parent = addslashes($parent);
	$sql -> db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$forum_parent."', forum_active='$forum_active', forum_moderators='".$mods."', forum_class='$forum_class_s' WHERE forum_id='".$_POST['forum_id']."' ");
	unset($forum_name, $forum_description, $forum_parent, $forum_active);
	$message = "Forum parent updated in database.";
}

If(IsSet($_POST['psubmit'])){
	if($_POST['parent_all']){
		$parent_class_s = "";
	}else{
		$count = 0; unset($parent_class_s);
		while($_POST['parent_class'][$count]){
			$parent_class_s .= $_POST['parent_class'][$count]."|";
			$count++;
		}

		if(substr($parent_class_s, -1) == "|"){
			$parent_class_s = substr($parent_class_s, 0, -1);
		}
	}
	$sql -> db_Insert("forum", "0, '".$_POST['parent']."', '', '', '".time()."', '1', '0', '0', '0', '', '$parent_class_s' ");
	unset($parent);
	$message = "Parent added to database.";
}

If(IsSet($_POST['pedit'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['existing']."' ");
	list($forum_id, $parent, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost, $forum_class) = $sql-> db_Fetch();
	$parent = stripslashes($parent);
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['existing']."' ");
	list($forum_id, $forum_name, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost, $forum_class) = $sql-> db_Fetch();
	$parent = stripslashes($parent);
}

If(IsSet($_POST['pupdate'])){
	if($_POST['parent_all']){
		$parent_class_s = "";
	}else{
		$count = 0; unset($parent_class_s);
		while($_POST['parent_class'][$count]){
			$parent_class_s .= $_POST['parent_class'][$count]."|";
			$count++;
		}

		if(substr($parent_class_s, -1) == "|"){
			$parent_class_s = substr($parent_class_s, 0, -1);
		}
	}
	$parent = addslashes($parent);
	$sql -> db_Update("forum", "forum_name='".$_POST['parent']."', forum_class='$parent_class_s' WHERE forum_name='".$_POST['existing']."' ");
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
<form method=\"post\" action=\"e_SELF\">
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
$text .= "<form method=\"post\" action=\"".e_SELF."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:20%\"><u>Parent</u>:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"parent\" size=\"60\" value=\"$parent\" maxlength=\"250\" />
<br /><br />
</td>
</tr>

<tr> 
<td style=\"width:20%\">Accessable to?:<br /><span class=\"smalltext\">(tick to make accessable to users in the ticked class)</span></td>
<td style=\"width:80%\">";

if($forum_active == 0 && $_POST['pedit']){
	$text .= "<input type=\"checkbox\" name=\"parent_active\" value=\"0\" checked>No-one (inactive)<br />";
}else{
	$text .= "<input type=\"checkbox\" name=\"parent_active\" value=\"0\">No-one (inactive)<br />";
}

if(!$forum_class && $_POST['pedit']){
	$text .= "<input type=\"checkbox\" name=\"parent_all\" value=\"1\" checked>Everyone (public)<br /><span class=\"smalltext\">(ticking this box will override the classes below)</span><br />";
}else{
	$text .= "<input type=\"checkbox\" name=\"parent_all\" value=\"1\">Everyone (public) <span class=\"smalltext\">(ticking this box will override the classes below)</span><br />";
}
if($sql -> db_Select("userclass_classes")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if($forum_class && eregi($forum_class, $userclass_id)){
			$text .= "<input type=\"checkbox\" name=\"parent_class[]\" value=\"$userclass_id\" checked>".$userclass_name ."<br />";
		}else{
			$text .= "<input type=\"checkbox\" name=\"parent_class[]\" value=\"$userclass_id\">".$userclass_name ."<br />";
		}
	}
}

$text .= "<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">";

if(IsSet($_POST['pedit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"pupdate\" value=\"Update Parent\" />
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">";
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
	$text = "<form method=\"post\" action=\"".e_SELF."\">
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
<form method=\"post\" action=\"".e_SELF."\">
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
<td style=\"width:20%\">Accessable to?:<br /><span class=\"smalltext\">(tick to make accessable to users in the ticked class)</span></td>
<td style=\"width:80%\">";

if($forum_active == 0 && $_POST['edit']){
	$text .= "<input type=\"checkbox\" name=\"forum_active\" value=\"0\" checked>No-one (inactive)<br />";
}else{
	$text .= "<input type=\"checkbox\" name=\"forum_active\" value=\"0\">No-one (inactive)<br />";
}

if(!$forum_class && $_POST['edit']){
	$text .= "<input type=\"checkbox\" name=\"forum_all\" value=\"1\" checked>Everyone (public)<br /><span class=\"smalltext\">(ticking this box will override the classes below)</span><br />";
}else{
	$text .= "<input type=\"checkbox\" name=\"forum_all\" value=\"1\">Everyone (public) <span class=\"smalltext\">(ticking this box will override the classes below)</span><br />";
}

if($sql -> db_Select("userclass_classes")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if($forum_class && eregi($forum_class, $userclass_id)){
			$text .= "<input type=\"checkbox\" name=\"forum_class[]\" value=\"$userclass_id\" checked>".$userclass_name ."<br />";
		}else{
			$text .= "<input type=\"checkbox\" name=\"forum_class[]\" value=\"$userclass_id\">".$userclass_name ."<br />";
		}
	}
}
	

$text .= "</td><tr><td colspan=\"2\"><br />";


/*
<select name=\"forum_active\" class=\"tbox\">
";

if($forum_active == "1"){
	$text .= "<option value=\"0\" selected>De-activated</option>
	<option value=\"1\" selected>Active</option>
	<option value=\"2\">Private</option>
	";
}else if($forum_active == "2"){
	$text .= "<option value=\"0\" selected>De-activated</option>
	<option value=\"1\">Active</option>
	<option value=\"2\" selected>Private</option>
	";
}else{
	$text .= "<option value=\"0\" selected>De-activated</option>
	<option value=\"1\">Active</option>
	<option value=\"2\">Private</option>
	";
}
*/
$text .= "</td></tr><tr>

<td style=\"width:20%\">Moderators:<br /><span class=\"smalltext\">(tick to make active on this forum)</span></td>
<td style=\"width:80%\">";

$admin_no = $sql -> db_Select("user", "*", "user_admin='1' AND user_perms REGEXP('A.') OR user_perms='0' "); 
while($row = $sql-> db_Fetch()){	
	extract($row);
	$text .= "<input type=\"checkbox\" name=\"mod[]\" value=\"".$user_name ."\"";
		if(eregi($user_name, $forum_moderators)){
			$text .= " checked";
		}
		$text .= "> ".$user_name ."<br />";
}

$text .= "</td>
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