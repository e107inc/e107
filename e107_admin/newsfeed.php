<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/headlines_conf.php
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
if(!getperms("E")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");

if(IsSet($_POST['add_headline'])){
	$datestamp = time();
	if($_POST['headline_url']){
		$sql -> db_Insert("headlines", "0, '".$_POST['headline_url']."', '', '0', '".$_POST['description']."', '".$_POST['webmaster']."', '".$_POST['copyright']."', '".$_POST['tagline']."', '".$_POST['headline_image']."', '".$_POST['activate']."' ");
		$message = NWFLAN_1;
		unset($headline_url, $headline_image);
	}else{
		$message = NWFLAN_20;
	}
}

if(IsSet($_POST['update_headline'])){
	$sql -> db_Update("headlines", "headline_url='".$_POST['headline_url']."', headline_timestamp='0', headline_description='".$_POST['description']."', headline_webmaster='".$_POST['webmaster']."', headline_copyright='".$_POST['copyright']."', headline_tagline='".$_POST['tagline']."', headline_image='".$_POST['headline_image']."', headline_active='".$_POST['activate']."' WHERE headline_id='".$_POST['headline_id']."' ");
	$message = NWFLAN_2;
	unset($headline_url, $headline_image);
}

if(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$sql -> db_Delete("headlines", "headline_url='".$_POST['existing']."' ");
		$message = NWFLAN_3;
	}else{
		$message = NWFLAN_4;
	}
}

if(IsSet($_POST['edit'])){
	$sql -> db_Select("headlines", "*", "headline_url='".$_POST['existing']."' ");
	list($headline_id, $headline_url, $headline_data, $headline_timestamp, $headline_description, $headline_webmaster, $headline_copyright, $headline_tagline, $headline_image, $headline_active) = $sql-> db_Fetch();
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$headline_total = $sql -> db_Select("headlines");


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n

<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' style='text-align:center' class='forumheader'>";


if($headline_total == "0"){
	$text .= "<span style='defaulttext'>".NWFLAN_5."</span>";
}else{
	$text .= "<span style='defaulttext'>".NWFLAN_6.": </span>
	<select name='existing' class='tbox'>";
	while(list($head_id, $head_url) = $sql-> db_Fetch()){
		$text .= "<option>".$head_url."</option>";
	}
	$text .= "</select> 
	<input class='button' type='submit' name='edit' value='".NWFLAN_7."' /> 
	<input class='button' type='submit' name='delete' value='".NWFLAN_8."' />
	<input type='checkbox' name='confirm' value='1'><span class='smalltext'> ".NWFLAN_9."</span>";
}


$text .= "
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".NWFLAN_10.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='headline_url' size='60' value='$headline_url' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".NWFLAN_11.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='headline_image' size='40' value='$headline_image' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".NWFLAN_12.":</td>
<td style='width:80%' class='forumheader3'>";
if($headline_tagline == 1){
	$text .= "<input type='checkbox' name='tagline' value='1' checked>";
}else{
	$text .= "<input type='checkbox' name='tagline' value='1'>";
}
$text .= "</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".NWFLAN_13.":</td>
<td style='width:80%' class='forumheader3'>";
if($headline_description == 1){
	$text .= "<input type='checkbox' name='description' value='1' checked>";
}else{
	$text .= "<input type='checkbox' name='description' value='1'>";
}
$text .= "</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".NWFLAN_14.":</td>
<td style='width:80%' class='forumheader3'>";
if($headline_webmaster == 1){
	$text .= "<input type='checkbox' name='webmaster' value='1' checked>";
}else{
	$text .= "<input type='checkbox' name='webmaster' value='1'>";
}
$text .= "</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".NWFLAN_15.":</td>
<td style='width:80%' class='forumheader3'>";
if($headline_copyright == 1){
	$text .= "<input type='checkbox' name='copyright' value='1' checked>";
}else{
	$text .= "<input type='checkbox' name='copyright' value='1'>";
}
$text .= "</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".NWFLAN_16.":</td>
<td style='width:80%' class='forumheader3'>";
if($headline_active == 1){
	$text .= "<input type='checkbox' name='activate' value='1' checked>";
}else{
	$text .= "<input type='checkbox' name='activate' value='1'>";
}
$text .= "</td>
</tr>

<tr style='vertical-align:top'> 
<td colspan='2' style='text-align:center' class='forumheader'>";

if(IsSet($_POST['edit'])){

	$text .= "<input class='button' type='submit' name='update_headline' value='".NWFLAN_17."' />
<input type='hidden' name='headline_id' value='$headline_id'>";
}else{
	$text .= "<input class='button' type='submit' name='add_headline' value='".NWFLAN_18."' />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".NWFLAN_19."</div>", $text);

require_once("footer.php");
?>	