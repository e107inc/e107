<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/news_category.php
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
if(!getperms("7")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

if(IsSet($_POST['add_category'])){
	$sql -> db_Insert("news_category", " '0', '".$_POST['category_name']."', '".$_POST['category_icon']."'");
	unset($category_name, $category_icon);
	$message = "Category added to database.";
}

if(IsSet($_POST['update_category'])){
	$sql -> db_Update("news_category", "category_name='".$_POST['category_name']."', category_icon='".$_POST['category_icon']."' WHERE category_id='".$_POST['category_id']."' ");
	unset($category_name, $category_icon);
	$message = "Category updated in database.";
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("news_category", "category_id='".$_POST['existing']."' ");
	$message = "Category deleted.";
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("news_category", "*", "category_id='".$_POST['existing']."' ");
	list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
	
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the '$category_name' news category - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Category", $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($_POST['edit'])){
	$sql -> db_Select("news_category", "*", "category_id='".$_POST['existing']."' ");
	list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$category_total = $sql -> db_Select("news_category");

if($category_total == "0"){
	$text = "No categories set yet.
	<br />
	<div style=\"text-align:center\">";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"".e_SELF."\">
	
	Existing Categories: 
	<select name=\"existing\" class=\"tbox\">";
	while(list($cat_id, $cat_name, $cat_icon) = $sql-> db_Fetch()){
		$text .= "<option value=\"$cat_id\">".$cat_name."</option>";
	}
	$text .= "</select> 
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" /> 
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	</form>
	</div>
	<br />";
}

$text .= "
<form method=\"post\" action=\"".e_SELF."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Category Name: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"category_name\" size=\"60\" value=\"$category_name\" maxlength=\"200\" />
</td>
</tr>
<tr>
<td style=\"width:30%\">Category Icon: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"category_icon\" size=\"60\" value=\"$category_icon\" maxlength=\"200\" />
</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">";

if(IsSet($_POST['edit'])){

	$text .= "<input class=\"button\" type=\"submit\" name=\"update_category\" value=\"Update category\" />
<input type=\"hidden\" name=\"category_id\" value=\"$category_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"add_category\" value=\"Add category\" />";
}
$text .= "</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">News Categories</div>", $text);

require_once("footer.php");
?>	