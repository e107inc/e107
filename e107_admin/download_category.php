<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/download.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("Q")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
$aj = new textparse;

$handle=opendir(THEME."images/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$images[] = $file;
	}
}
closedir($handle);

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------



if(IsSet($_POST['createparent'])){
	if($_POST['category_name'] && $_POST['category_description']){
		$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
		$_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
		$sql -> db_Insert("download_category", "0, '".$_POST['category_name']."', '".$_POST['category_description']."', '".$_POST['category_icon']."', 0, '".$_POST['download_class']."' ");
		$message = DOWLAN_2;
		unset($_POST['category_name'], $_POST['category_description'], $_POST['category_icon']);
	}else{
		$message = DOWLAN_1;
	}
}

if(IsSet($_POST['createcategory'])){
	if($_POST['c_category_name'] && $_POST['c_category_description']){
		$_POST['c_category_name'] = $aj -> formtpa($_POST['c_category_name'], "admin");
		$_POST['c_category_description'] = $aj -> formtpa($_POST['c_category_description'], "admin");
		$sql -> db_Insert("download_category", "0, '".$_POST['c_category_name']."', '".$_POST['c_category_description']."', '".$_POST['c_category_icon']."', '".$_POST['c_category_parent']."', '".$_POST['c_download_class']."' ");
		$message = DOWLAN_3;
		unset($_POST['c_category_name'], $_POST['c_category_description'], $_POST['c_category_icon']);
	}else{
		$message = DOWLAN_1;
	}
}

if(IsSet($_POST['pedit'])){
	$sql -> db_Select("download_category", "*", "download_category_id='".$_POST['existing']."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$_POST['category_name'] = $download_category_name;
	$_POST['category_description'] = $download_category_description;
	$_POST['category_icon'] = $download_category_icon;
	$_POST['download_class'] = $download_category_class;
	$dlcat = $download_category_id;
}

if(IsSet($_POST['cedit'])){
	$sql -> db_Select("download_category", "*", "download_category_id='".$_POST['existing2']."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$_POST['c_category_name'] = $download_category_name;
	$_POST['c_category_description'] = $download_category_description;
	$_POST['c_category_icon'] = $download_category_icon;
	$_POST['c_download_class'] = $download_category_class;
	$dlcat = $download_category_id;
}

if(IsSet($_POST['updateparent'])){

	if($_POST['category_name'] && $_POST['category_description']){
		$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
		$_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
		$sql -> db_Update("download_category", "download_category_name='".$_POST['category_name']."', download_category_description='".$_POST['category_description']."', download_category_icon='".$_POST['category_icon']."', download_category_class='".$_POST['download_class']."' WHERE download_category_id='".$_POST['dlcat']."' ");
		$message = DOWLAN_4;
		unset($_POST['category_name'], $_POST['category_description'], $_POST['category_icon']);
	}else{
		$message = DOWLAN_1;
	}
}

if(IsSet($_POST['updatecategory'])){
	if($_POST['c_category_name'] && $_POST['c_category_description']){
		$_POST['c_category_name'] = $aj -> formtpa($_POST['c_category_name'], "admin");
		$_POST['c_category_description'] = $aj -> formtpa($_POST['c_category_description'], "admin");
		$sql -> db_Update("download_category", "download_category_name='".$_POST['c_category_name']."', download_category_description='".$_POST['c_category_description']."', download_category_icon='".$_POST['c_category_icon']."', download_category_parent='".$_POST['c_category_parent']."', download_category_class='".$_POST['c_download_class']."' WHERE download_category_id='".$_POST['dlcat']."' ");
		$message = DOWLAN_5;
		unset($_POST['c_category_name'], $_POST['c_category_description'], $_POST['c_category_icon']);
	}else{
		$message = DOWLAN_1;
	}
}

If(IsSet($_POST['pdelete'])){
	if($_POST['pconfirm']){
		$sql = new db;
		$sql -> db_Delete("download_category", "download_category_id='".$_POST['existing']."' ");
		$message = DOWLAN_6;
	}else{
		$message = DOWLAN_7;
	}
}

If(IsSet($_POST['c_delete'])){
	if($_POST['c_confirm']){
		$sql = new db;
		$sql -> db_Delete("download_category", "download_category_id='".$_POST['existing2']."' ");
		$message = DOWLAN_8;
	}else{
		$message = DOWLAN_9;
	}
}



// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$parent_total = $sql -> db_Select("download_category", "*", "download_category_parent='0'");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table class='fborder' style='width:85%'>
<tr><td colspan='2' style='text-align:center' class='forumheader'>".DOWLAN_10."</td></tr>
</tr>
<tr>
<td colspan='2' class='forumheader2' style='text-align:center'>";
if(!$parent_total){
	$text .= "No parents yet.";
}else{
	
	$text .= "
	<span class='defaulttext'>".DOWLAN_11.":</span> 
	<select name='existing' class='tbox'>";
	$c = 0;
	while($row = $sql-> db_Fetch()){
		extract($row);
		$parents[$c] = $download_category_name;
		$parents_id[$c] = $download_category_id;
		$text .= "<option value=".$download_category_id.">".$download_category_name."</option>";
		$c++;
	}
	$text .= "</select>
<input class='button' type='submit' name='pedit' value='".DOWLAN_12."' /> 
<input class='button' type='submit' name='pdelete' value='".DOWLAN_13."' />
<input type='checkbox' name='pconfirm' value='1'><span class='smalltext'> ".DOWLAN_14."</span>
";

}

$text .= "</td></tr>

<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_15.":</td>
<td class='forumheader3'>
<input class='tbox' type='text' size='30' maxlength='100' name='category_name' value='".$_POST['category_name']."'>
</td>
</tr>

<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_16.":</td>
<td class='forumheader3'>
<select name='category_icon' class='tbox'>";
if(!$_POST['category_icon']){
	$text .= "<option selected></option>\n";
}else{
	$text .= "<option></option>\n";
}

$counter = 0;
while($images[$counter]){
	if($images[$counter] == $_POST['category_icon']){
		$text .= "<option selected>".$images[$counter]."</option>\n";
	}else{
		$text .= "<option>".$images[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
</td>
</tr>


<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_17.":</td>
<td class='forumheader3'>
<input class='tbox' type='text' size='70' maxlength='250' name='category_description' value='".$_POST['category_description']."'>
</td>
</tr>

<tr>
<td class='forumheader3'>
".DOWLAN_25."
</td>
<td class='forumheader3'>".r_userclass("download_class",$_POST['download_class'])."
</td></tr>
<tr><td colspan='2' style='text-align:center' class='forumheader'>";

if(IsSet($_POST['pedit'])){
	$text .= "<input class='button' type='submit' name='updateparent' value='".DOWLAN_18."' /> 
	<input type='hidden' name='dlcat' value='$dlcat'>";
}else{
	$text .= "<input class='button' type='submit' name='createparent' value='".DOWLAN_19."' />";
}

$text .= "
</td>
</tr>
</table>
</form>
<br />";

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------



$category_total = $sql -> db_Select("download_category", "*", "download_category_parent!='0'");

$text .= "
<form method='post' action='".e_SELF."'>
<table class='fborder' style='width:85%'>
<tr><td colspan='2' style='text-align:center' class='forumheader'>".DOWLAN_20."</td></tr>
</tr>
<tr>
<td colspan='2' class='forumheader2' style='text-align:center'>";
if(!$category_total){
	$text .= "No categories yet.";
}else{
	
	$text .= "
	<span class='defaulttext'>".DOWLAN_21.":</span> 
	<select name='existing2' class='tbox'>";
	$c = 0;
	while($row = $sql-> db_Fetch()){
		extract($row);
		$text .= "<option value=".$download_category_id.">".$download_category_name."</option>";
		$c++;
	}
	$text .= "</select>
<input class='button' type='submit' name='cedit' value='".DOWLAN_12."' /> 
<input class='button' type='submit' name='c_delete' value='".DOWLAN_13."' />
<input type='checkbox' name='c_confirm' value='1'><span class='smalltext'> ".DOWLAN_14."</span>
";

}

$text .= "</td></tr>";

if(!$parent_total){
	$text .= "<tr><td colspan='2' style='text-align:center' class='forumheader3'>".DOWLAN_22."
	</td></tr></table></form></div>";
	$ns -> tablerender("<div style='text-align:center'>".DOWLAN_23."</div>", $text);
	require_once("footer.php");
	exit;
}

$text .= "</td></tr>

<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_24.":</td>
<td class='forumheader3'>
<select name=\"c_category_parent\" class=\"tbox\">";
$c = 0;
	while($parents[$c]){
		if($parents_id[$c] == $_POST['c_category_parent']){
			$text .= "<option value='".$parents_id[$c]."' selected>".$parents[$c]."</option>";
		}else{
			$text .= "<option value='".$parents_id[$c]."'>".$parents[$c]."</option>";
		}
		$c++;
	}
$text .= "</select>
</td>
</tr>

<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_15.":</td>
<td class='forumheader3'>
<input class='tbox' type='text' size='30' maxlength='100' name='c_category_name' value='".$_POST['c_category_name']."'>
</td>
</tr>

<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_16.":</td>
<td class='forumheader3'>
<select name='c_category_icon' class='tbox'>";
if(!$_POST['c_category_icon']){
	$text .= "<option selected></option>\n";
}else{
	$text .= "<option></option>\n";
}

$counter = 0;
while($images[$counter]){
	if($images[$counter] == $_POST['c_category_icon']){
		$text .= "<option selected>".$images[$counter]."</option>\n";
	}else{
		$text .= "<option>".$images[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
</td>
</tr>


<tr>
<td class='forumheader3' style='width:30%'>".DOWLAN_17.":</td>
<td class='forumheader3'>
<input class='tbox' type='text' size='70' maxlength='250' name='c_category_description' value='".$_POST['c_category_description']."'>
</td>
</tr>

<tr>
<td class='forumheader3'>
".DOWLAN_25."
</td>
<td class='forumheader3'>".r_userclass("c_download_class",$_POST['c_download_class'])."
</td></tr>
<tr><td colspan='2' style='text-align:center' class='forumheader'>";

if(IsSet($_POST['cedit'])){
	$text .= "<input class='button' type='submit' name='updatecategory' value='".DOWLAN_26."' />
	<input type='hidden' name='dlcat' value='$dlcat'>";
}else{
	$text .= "<input class='button' type='submit' name='createcategory' value='".DOWLAN_27."' />";
}

$text .= "
</td>
</tr>
</table>
</form>
</div>";


$ns -> tablerender("<div style='text-align:center'>".DOWLAN_28."</div>", $text);
require_once("footer.php");

function parse_class($array){
	if($_POST['download_all']){
		$download_class_s = "";
	}else{
		$count = 0; unset($download_class_s);
		while($array[$count]){
			$download_class_s .= $array[$count]."|";
			$count++;
		}
		if(substr($download_class_s, -1) == "|"){
			$download_class_s = substr($download_class_s, 0, -1);
		}
	}
	return $download_class_s;
}

?>	