<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/link_category.php
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
if(!getperms("8")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
$aj = new textparse;

if(IsSet($_POST['add_category'])){
	if($_POST['category_name'] && $_POST['category_description']){
		$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
		$_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
		$sql -> db_Insert("link_category", "0, '".$_POST['category_name']."', '".$_POST['category_description']."' ");
		$message = LCLAN_1;
		unset($category_name, $category_description);
	}else{
		message_handler("ALERT", 5);
	}
}

if(IsSet($_POST['update_category'])){
	$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
	$_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
	$sql -> db_Update("link_category", "link_category_name='".$_POST['category_name']."', link_category_description='".$_POST['category_description']."' WHERE link_category_id='".$_POST['category_id']."' ");
	$message = LCLAN_2;
	unset($category_name, $category_description);
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("link_category", "link_category_id='".$_POST['existing']."' ");
	$message = LCLAN_3;
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("link_category", "*", "link_category_id='".$_POST['existing']."' ");
	list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();

	if($category_name == "Main"){
		$ns -> tablerender(LCLAN_4, "<div style='text-align:center'>".LCLAN_5."</div>");
		require_once("footer.php");
		exit;
	}

	$text = "<div style='text-align:center'>
	<b>".LCLAN_6." '".$category_name."' ".LCLAN_7."</b>
<br /><br />
<form method='post' action='$PHP_SELF'>
<input class='button' type='submit' name='cancel' value='".LCLAN_8."' /> 
<input class='button' type='submit' name='confirm' value='".LCLAN_9."' /> 
<input type='hidden' name='existing' value='".$_POST['existing']."'>
</form>
</div>";
$ns -> tablerender(LCLAN_10, $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = LCLAN_11;
}

if(IsSet($_POST['edit'])){
	
	$sql -> db_Select("link_category", "*", "link_category_id='".$_POST['existing']."' ");
	list($category_id, $category_name, $category_description) = $sql-> db_Fetch();
	if($category_name == "Main"){
		$ns -> tablerender(LCLAN_12, "<div style='text-align:center'>".LCLAN_13."</div>");
		require_once("footer.php");
		exit;
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$category_total = $sql -> db_Select("link_category");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

if(!$category_total){
	$text .= "<span class='defaulttext'>".LCLAN_14.".</span>";
}else{
	$text .= "<span class='defaulttext'>".LCLAN_15.": </span>
	<select name='existing' class='tbox'>";
	while(list($cat_id, $cat_name, $cat_description) = $sql-> db_Fetch()){
		$text .= "<option value='$cat_id'>".$cat_name."</option>";
	}
	$text .= "</select> 
	<input class='button' type='submit' name='edit' value='".LCLAN_16."' /> 
	<input class='button' type='submit' name='delete' value='".LCLAN_17."' />";
}


$text .= "
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_18.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='category_name' size='60' value='$category_name' maxlength='100' />
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".LCLAN_19.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='category_description' size='60' value='$category_description' maxlength='250' />
</td>
</tr>
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>";

if(IsSet($_POST['edit'])){

	$text .= "<input class='button' type='submit' name='update_category' value='".LCLAN_20."' />
<input type='hidden' name='category_id' value='$category_id'>";
}else{
	$text .= "<input class='button' type='submit' name='add_category' value='".LCLAN_21."' />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".LCLAN_22."</div>", $text);

require_once("footer.php");
?>	