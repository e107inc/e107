<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/links.php
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
if(!getperms("I")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

if(e_QUERY != ""){
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$linkid = $qs[1];
}

if($_POST['add_link'] != ""){
	$sql -> db_Select("link_category", "*", "link_category_name='".$_POST['cat_name']."' ");
	$row = $sql -> db_Fetch();
	$link_cat_id = $row['link_category_id'];
	$sql -> db_Insert("links", "0, '".$_POST['link_name']."', '".$_POST['link_url']."', '".$_POST['link_description']."', '".$_POST['link_button']."', '$link_cat_id', '0', '0', '".$_POST['linkopentype']."' ");
	$message = "Link added to database.";
	unset ($link_id, $link_name, $link_url, $link_description, $link_button, $link_main);
}

if(IsSet($_POST['update_link'])){
	$sql -> db_Select("link_category", "*", "link_category_name='".$_POST['cat_name']."' ");
	$row = $sql -> db_Fetch();
	$link_cat_id = $row['link_category_id'];
	$sql -> db_Update("links", "link_name='".$_POST['link_name']."', link_url='".$_POST['link_url']."', link_description='".$_POST['link_description']."', link_button= '".$_POST['link_button']."', link_category='$link_cat_id', link_open='".$_POST['linkopentype']."' WHERE link_id='".$_POST['link_id']."' ");
	$message = "Link updated in database.";
	unset ($link_id, $link_name, $link_url, $link_description, $link_button, $link_main);
}

if(IsSet($_POST['edit']) || $action == "edit"){
	if($action == "edit"){
		$sql -> db_Select("links", "*", "link_id='".$linkid."' ");
	}else{
		$sql -> db_Select("links", "*", "link_id='".$_POST['existing']."' ");
	}
	list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_refer) = $sql-> db_Fetch();
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("links", "link_id='".$_POST['existing']."' ");
	$message = "Link deleted.";
}

if(IsSet($_POST['delete']) || $action == "delete"){
	if($action == "delete"){
		$sql -> db_Select("links", "*", "link_id='".$linkid."' ");
	}else{
		$sql -> db_Select("links", "*", "link_id='".$_POST['existing']."' ");
	}
	list($link_id, $link_name) = $sql-> db_Fetch();
	
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the '$link_name' link - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" />"; 
if($action == "delete"){
$text .= "<input type=\"hidden\" name=\"existing\" value=\"".$linkid."\">";
 }else{
$text .= "<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">";

}
$text .= "</form>
</div>";
$ns -> tablerender("Confirm Delete Link", $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($_POST['update_order'])){
	extract($_POST);
	$sql -> db_Select("links");
	$sql2 = new db;
	while(list($link_id) = $sql-> db_Fetch()){
		$sql2 -> db_Update("links", "link_order='".$link_order[$link_id]."' WHERE link_id='$link_id' ");
	}

	$message = "Order updated.";

}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$sql -> db_Select("links");
$link_total = $sql -> db_Rows();
if($link_total == "0"){
	$text = "<div style=\"text-align:center\">
	No links set yet.
	</div>
	<br />
	";
}else{
$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"".e_SELF."\">
	Existing Links: 
	<select name=\"existing\" class=\"tbox\">";
	while(list($link_id_, $link_name_) = $sql-> db_Fetch()){
		$text .= "<option value=\"$link_id_\">".$link_name_."</option>";
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
<td style=\"width:30%\">Link Category: </td>
<td style=\"width:70%\">";

if(!$sql -> db_Select("link_category")){
	$text .= "<div class=\"twelvept\">No categories set yet.</div><br />";
}else{

	$text .= "
	<select name=\"cat_name\" class=\"tbox\">";
	
	while(list($cat_id, $cat_name, $cat_description) = $sql-> db_Fetch()){
		if($link_category == $cat_id || ($cat_id == $linkid && $action == "add")){
			$text .= "<option selected>".$cat_name."</option>";
		}else{
			$text .= "<option>".$cat_name."</option>";
		}
	}
	$text .= "</select>";
}
$text .= "<span class=\"twelvept\"> [ <a href=\"link_category.php\">Add/Edit Categories</a> ]</span>

<tr>
<td style=\"width:30%\">Link Name: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"link_name\" size=\"60\" value=\"$link_name\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Link URL: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"link_url\" size=\"60\" value=\"$link_url\" maxlength=\"200\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Link Description: </td>
<td style=\"width:70%\">
<textarea class=\"tbox\" name=\"link_description\" cols=\"59\" rows=\"3\">$link_description</textarea>
</td>
</tr>

<tr>
<td style=\"width:30%\">Link Button: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"link_button\" size=\"60\" value=\"$link_button\" maxlength=\"100\" />

</td>
</tr>
<tr>
<td style=\"width:30%\">Link Open Type: </td>
<td style=\"width:70%\">
<select name=\"linkopentype\" class=\"tbox\">
<option value=\"0\" selected>opens in same window</option>
<option value=\"1\">_target=blank</option>
<option value=\"2\">_target=parent</option>
<option value=\"3\">_target=top</option>
<option value=\"4\">open in 600x400 miniwindow</option>
</select>

</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\"><br />";
if(IsSet($_POST['edit']) || $action == "edit"){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update_link\" value=\"Update Link\" />
<input type=\"hidden\" name=\"link_id\" value=\"$link_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"add_link\" value=\"Add link\" />";
}
$text .= "</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Links</div>", $text);

$text = "<form method=\"post\" action=\"".e_SELF."\">
<table style=\"width:95%\">";

$sql -> db_Select("link_category");
$sql2 = new db;
while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
	if($sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ")){
		$text .= "<tr><td colspan=\"2\"><b>$link_category_name</b></td></tr>";
		while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer) = $sql2-> db_Fetch()){
			
			$text .= "<tr>
<td style=\"width:30%\">".$link_id." - ".$link_name."</td>
<td style=\"width:70%\"><input class=\"tbox\" type=\"text\" name=\"link_order[$link_id]\" size=\"6\" value=\"$link_order\" maxlength=\"3\" /></td>
</tr>";
		}
		$text .= "<tr><td colspan=\"2\"><br /></td></tr>";
	}
}
$text .= "
<tr>
<td colspan=\"2\" style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"update_order\" value=\"Update Order\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Link Order</div>", $text);

require_once("footer.php");
?>	