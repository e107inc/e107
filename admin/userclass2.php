<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/admin.php
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

if(IsSet($_POST['createclass'])){
	if($_POST['class'] && $_POST['description']){
		$sql -> db_Insert("userclass_classes", "'0','".strip_tags(strtoupper($_POST['class']))."','".$_POST['description']."' ");
		$message = "Class added to database.";
	}
}

$qs = explode(".", e_QUERY);
$action = $qs[0]; $id = $qs[1];

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("userclass_classes", "userclass_id='".$id."' ");
	$message = "Class deleted.";
	unset($action);
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
	unset($action);
}

if($action == "delete"){
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete this userclass - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
</form>
</div>";
$ns -> tablerender("Confirm Delete Category", $text);
	
	require_once("footer.php");
	exit;
}

if(IsSet($_POST['updateclasses'])){	
	$sql -> db_Select("userclass_classes");
	$sql2 = new db;
	while($row = $sql -> db_Fetch()){
		extract($row);
		$name = "name_".$userclass_id;
		$description = "description_".$userclass_id;
		$sql2 -> db_Update("userclass_classes", "userclass_name='".$_POST[$name]."', userclass_description='".$_POST[$description]."' WHERE userclass_id='$userclass_id' ");
	}
	$message = "Classes updated.";
}


if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("userclass_classes", "userclass_id='".$id."' ");
	$message = "Class deleted.";
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$text = "<form method=\"post\" action=\"".e_SELF."\">
<table class=\"fborder\" style=\"width:95%\">";

if($sql -> db_Select("userclass_classes")){

	$text.="
	<tr><td class=\"fcaption\" >Class Name</td><td class=\"fcaption\" >Class Description</td></tr>\n";
	while(list($_userclass_id,$_userclass_name, $_userclass_description) = $sql-> db_Fetch()){
		$text.="<tr>
		<td class=\"forumheader2\">
		<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"25\" name=\"name_".$_userclass_id."\" value=\"".$_userclass_name."\">
		</td>
		<td class=\"forumheader2\">
		<input class=\"tbox\" type=\"text\" size=\"60\" maxlength=\"85\" name=\"description_".$_userclass_id."\" value=\"".$_userclass_description."\">
		
		<span class=\"defaulttext\">[ <a href=\"".e_SELF."?delete.".$_userclass_id."\">Delete</a> ]</span>
		</td>
		</tr>";
	}
	$text.="<tr><td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">
	<input class=\"button\" type=\"submit\" name=\"updateclasses\" value=\"Update Classes\"></td></tr>";
}

$text.="
<tr>
<td class=\"forumheader2\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"25\" name=\"class\" value=\"\">
</td>
<td class=\"forumheader2\">
<input class=\"tbox\" type=\"text\" size=\"60\" maxlength=\"85\" name=\"description\" value=\"\">
</td>
</tr>
<tr><td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">
<input class=\"button\" type=\"submit\" name=\"createclass\" value=\"Create New Class\"></td></tr>

</table>
</form>";





$ns -> tablerender("<div style=\"text-align:center\">User Class Settings</div>", $text);






require_once("footer.php");
?>