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
if(!getperms("7")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
$aj = new textparse;

$qs = explode(".", e_QUERY);
$action = $qs[0]; $id = $qs[1];

$handle=opendir(THEME."images/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$images[] = $file;
	}
}
closedir($handle);
$handle=opendir(e_IMAGE."newsicons/");
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
		$icon = "".e_IMAGE."newsicons/".$_POST['category_icon'];
	}
	$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
	$sql -> db_Insert("news_category", " '0', '".$_POST['category_name']."', '$icon' ");
	unset($category_name, $category_icon);
	$message = NCLAN_1;
}

if(IsSet($_POST['updatecategories'])){	
	$sql -> db_Select("news_category");
	$sql2 = new db;
	while($row = $sql -> db_Fetch()){
		extract($row);
		$name = "name_".$category_id;
		$icon = "icon_".$category_id;
		$_POST['name'] = $aj -> formtpa($_POST['name'], "admin");
		$sql2 -> db_Update("news_category", "category_name='".$_POST[$name]."', category_icon='".$_POST[$icon]."' WHERE category_id='$category_id' ");
	}
	$message = NCLAN_2;
}


if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("news_category", "category_id='".$_POST['id']."' ");
	$message = NCLAN_3;
}

if($action == "delete"){
	$sql -> db_Select("news_category", "*", "category_id='".$id."' ");
	list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
	
	$text = "<div style='text-align:center'>
	<b>".NCLAN_4." '$category_name' ".NCLAN_5."</b>
<br /><br />
<form method='post' action='".e_SELF."'>
<input class='button' type='submit' name='cancel' value='".NCLAN_6."' /> 
<input class='button' type='submit' name='confirm' value='".NCLAN_7."' /> 
<input type='hidden' name='id' value='".$id."'>
</form>
</div>";
$ns -> tablerender(NCLAN_8, $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = NCLAN_9;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$category_total = $sql -> db_Select("news_category");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table class='fborder' style='width:95%'>

<tr><td colspan='3' style='text-align:center' class='forumheader'>".NCLAN_10."</td></tr>
<tr><td class='forumheader'><span class='defaulttext'>".NCLAN_11."</span></td><td colspan='2' class='forumheader'><span class='defaulttext'>".NCLAN_12."</span></td></tr>\n";
if(!$category_total){
	$text .= "<tr>
	<td colspan='3' class='forumheader2' style='text-align:center'>".NCLAN_13."</td>";
}else{
	while($row = $sql-> db_Fetch()){
		extract($row);
		$text.="<tr>
		<td class='forumheader3'>
		<input class='tbox' type='text' size='30' maxlength='25' name='name_".$category_id."' value='".$category_name."'>
		</td>
		<td class='forumheader3' style='width:5%'>";
		if($fp = @fopen(THEME.$category_icon, "r")){
			$icon = THEME.$category_icon;
			fclose ($fp);
		}else{
			$icon = e_BASE.$category_icon;
		}
		$text .= "
		<img src='".$icon."' alt='' style='float:left' />
		</td>
		<td class='forumheader3'>
		<input class='tbox' type='text' size='60' maxlength='85' name='icon_".$category_id."' value='".$category_icon ."'>
			
		<span class='defaulttext'>[ <a href='".e_SELF."?delete.".$category_id ."'>".NCLAN_17."</a> ]</span>
		</td>
		</tr>";
	}
}

$text .= "
<tr><td colspan='3' style='text-align:center' class='forumheader'>";
if($category_total){
	$text .= "<input class='button' type='submit' name='updatecategories' value='".NCLAN_15."'></td></tr>";
}

$text .= "
</table>
<br />
<table class='fborder' style='width:80%'>
<tr><td colspan='3' style='text-align:center' class='forumheader'>".NCLAN_16."</td></tr>
<tr><td class='forumheader'><span class='defaulttext'>".NCLAN_11."</span></td><td class='forumheader'><span class='defaulttext'>".NCLAN_12."</span></td></tr>
<tr>
<td class='forumheader3'>
<input class='tbox' type='text' size='30' maxlength='25' name='category_name' value=''>
</td>
<td class='forumheader3'>

<select name='category_icon' class='tbox'>\n";
$counter = 0;
while($images[$counter]){
	if($images[$counter] == $pref['sitetheme']){
		$text .= "<option selected>".$images[$counter]."</option>\n";
	}else{
		$text .= "<option>".$images[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
</td>
</tr>
<tr><td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='createcategory' value='".NCLAN_13."'></td></tr>

</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".NCLAN_14."</div>", $text);
require_once("footer.php");
?>	