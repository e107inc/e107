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

$qs = explode(".", e_QUERY);
$action = $qs[0]; $id = $qs[1];

$handle=opendir(THEME."images/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$images[] = $file;
	}
}
closedir($handle);
$handle=opendir(e_BASE."themes/shared/newsicons/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$images[] = $file;
	}
}
closedir($handle);


if(IsSet($_POST['createcategory'])){
	if($fp = @fopen(THEME."images/".$_POST['category_icon'],"r")){
		$icon = "images/".$_POST['category_icon'];
		fclose ($fp);
	}else{
		$icon = "themes/shared/newsicons/".$_POST['category_icon'];
	}

	$sql -> db_Insert("news_category", " '0', '".$_POST['category_name']."', '$icon' ");
	unset($category_name, $category_icon);
	$message = "Category added to database.";
}

if(IsSet($_POST['updatecategories'])){	
	$sql -> db_Select("news_category");
	$sql2 = new db;
	while($row = $sql -> db_Fetch()){
		extract($row);
		$name = "name_".$category_id;
		$icon = "icon_".$category_id;
		$sql2 -> db_Update("news_category", "category_name='".$_POST[$name]."', category_icon='".$_POST[$icon]."' WHERE category_id='$category_id' ");
	}
	$message = "Categories updated.";
}


if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("news_category", "category_id='".$_POST['id']."' ");
	$message = "Category deleted.";
}

if($action == "delete"){
	$sql -> db_Select("news_category", "*", "category_id='".$id."' ");
	list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
	
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the '$category_name' news category - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Category", $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$category_total = $sql -> db_Select("news_category");

$text = "<form method=\"post\" action=\"".e_SELF."\">
<table class=\"fborder\" style=\"width:98%\">

<tr><td colspan=\"2\" style=\"text-align:center\" class=\"fcaption\">Update Existing Categories</td></tr>
<tr><td class=\"forumheader\" >Category Name</td><td class=\"forumheader\" >Category Icon</td></tr>\n";
if(!$category_total){
	$text .= "<tr>
	<td colspan=\"2\" class=\"forumheader2\" style=\"text-align:center\">No news categories yet.</td>";
}else{
	while($row = $sql-> db_Fetch()){
		extract($row);
		$text.="<tr>
		<td class=\"forumheader2\">
		<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"25\" name=\"name_".$category_id."\" value=\"".$category_name."\">
		</td>
		<td class=\"forumheader2\">";
		if($fp = @fopen(THEME.$category_icon, "r")){
			$icon = THEME.$category_icon;
			fclose ($fp);
		}else{
			$icon = e_BASE.$category_icon;
		}
		$text .= "<img src=\"".$icon."\" alt=\"\" style=\"float:left\" />&nbsp;&nbsp;
		<input class=\"tbox\" type=\"text\" size=\"60\" maxlength=\"85\" name=\"icon_".$category_id."\" value=\"".$category_icon ."\">
			
		<span class=\"defaulttext\">[ <a href=\"".e_SELF."?delete.".$category_id ."\">Delete</a> ]</span>
		</td>
		</tr>";
	}
}
$text.="<tr><td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">";
if($category_total){
	$text .= "<input class=\"button\" type=\"submit\" name=\"updatecategories\" value=\"Update News Categories\"></td></tr>";
}

$text .= "<tr><td colspan=\"2\" style=\"text-align:center\" class=\"fcaption\">Create New Category</td></tr>
<tr><td class=\"forumheader\" >Category Name</td><td class=\"forumheader\" >Category Icon</td></tr>\n
<tr>
<td class=\"forumheader2\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"25\" name=\"category_name\" value=\"\">
</td>
<td class=\"forumheader2\">

<select name=\"category_icon\" class=\"tbox\">\n";
		$counter = 0;
		while($images[$counter]){
			if($images[$counter] == $pref['sitetheme'][1]){
				$text .= "<option selected>".$images[$counter]."</option>\n";
			}else{
				$text .= "<option>".$images[$counter]."</option>\n";
			}
		$counter++;
	}
	$text .= "</select>";




//<input class=\"tbox\" type=\"text\" size=\"60\" maxlength=\"85\" name=\"category_icon\" value=\"\">
$text .= "</td>
</tr>
<tr><td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">
<input class=\"button\" type=\"submit\" name=\"createcategory\" value=\"Create New News Category\"></td></tr>

</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">News Categories</div>", $text);
require_once("footer.php");
?>	