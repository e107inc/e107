<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/poll_conf.php
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
if(!getperms("P")){ header("location:".e_HTTP."index.php"); exit;}
require_once("auth.php");
require_once("../classes/poll_class.php");

$poll = new poll;

if(IsSet($_POST['addoption']) && $_POST['option_count'] < 10){
	$_POST['option_count']++;
}

/*
if(IsSet($_POST['reset'])){
	unset($poll_id, $poll_title_, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10);
}
*/

if(IsSet($_POST['deletecancel'])){
	$message = "Delete cancelled.<br />";
}

if(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$message = $poll -> delete_poll($_POST['existing']);
		unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
	}
}

if(IsSet($_POST['edit'])){
	if($sql -> db_Select("poll", "*", "poll_id='".$_POST['existing']."' ")){
		$row = $sql-> db_Fetch(); extract($row);
		for($a=1; $a<=10; $a++){
			$var = "poll_option_".$a;
			if($$var){
				$_POST['poll_option'][($a-1)] = $$var;
			}
		}
		$_POST['activate'] = $poll_active;
		$_POST['option_count'] = count($_POST['poll_option']);
		$_POST['poll_title'] = $poll_title;
	}
}

if(IsSet($_POST['submit'])){
	$message = $poll -> submit_poll($_POST['poll_id'], $_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
	unset($_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
}

if(IsSet($_POST['preview'])){
	$poll -> render_poll($_POST['existing'], $_POST['poll_title'], $_POST['poll_option'], array($votes), "preview");
	$count=0;
	while($_POST['poll_option'][$count]){
		$_POST['poll_option'][$count] = stripslashes($_POST['poll_option'][$count]);
		$count++;
	}
	$_POST['poll_title'] = stripslashes($_POST['poll_title']);
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$poll_total = $sql -> db_Select("poll");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

if(!$poll_total){
	$text .= "<span class='defaulttext'>No polls set yet.</span>";
}else{
	$text .= "<span class='defaulttext'>Existing Polls:</span> 
	<select name='existing' class='tbox'>";
	while($row = $sql-> db_Fetch()){
		$text .= "<option value='".$row['poll_id']."'>".$row['poll_title']."</option>";
	}
	$text .= "</select> 
	<input class='button' type='submit' name='edit' value='Edit' /> 
	<input class='button' type='submit' name='delete' value='Delete' />
	<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> tick to confirm</span>
	";
}

$text .= "
</td>
</tr>
<tr> 
<td style='width:30%' class='forumheader3'><div class='normaltext'>Poll Question:</div></td>
<td style='width:70%'class='forumheader3'>
<input class='tbox' type='text' name='poll_title' size='70' value=`".$_POST['poll_title']."` maxlength='200' />";

$option_count = ($_POST['option_count'] ? $_POST['option_count'] : 1);
$text .= "<input type='hidden' name='option_count' value='$option_count'>";

for($count=1; $count<=$option_count; $count++){
	$text .= "<tr>
<td style='width:30%' class='forumheader3'>Option ".$count.":</td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='poll_option[]' size='40' value=`".$_POST['poll_option'][($count-1)]."` maxlength='200' />";
if($option_count == $count){
	$text .= " <input class='button' type='submit' name='addoption' value='Add another option' /> ";
}
$text .= "</td></tr>";
}

$text .= "<tr>
<td style='width:30%' class='forumheader3'>Poll status?:</td>
<td class='forumheader3'>";
$text .= (!$_POST['activate'] ? "<input name='activate' type='radio' value='0' checked>Inactive<br />" : "<input name='activate' type='radio' value='0'>Inactive<br />");
$text .= ($_POST['activate'] == 1 ? "<input name='activate' type='radio' value='1' checked>Active - allow votes from all<br />" : "<input name='activate' type='radio' value='1'>Active - allow votes from all<br />");
$text .= ($_POST['activate'] == 2 ? "<input name='activate' type='radio' value='2' checked>Active - allow votes from members only<br />" : "<input name='activate' type='radio' value='2'>Active - allow votes from members only<br />");

$text .= "</td>
</tr>

<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>";

if(IsSet($_POST['preview'])){
	$text .= "<input class='button' type='submit' name='preview' value='Preview again' /> ";
	if($_POST['poll_id']){
		$text .= "<input class='button' type='submit' name='submit' value='Update poll in database' /> ";

	}else{
		$text .= "<input class='button' type='submit' name='submit' value='Post poll to database' /> ";
	}
}else{
	$text .= "<input class='button' type='submit' name='preview' value='Preview' /> ";
}
if(IsSet($poll_id)){
	$text .= "<input class='button' type='submit' name='reset' value='New poll' /> ";
}

$text .= "</td></tr></table>";
if($_POST['poll_id']){
	$text .= "<input type='hidden' name='poll_id' value='".$_POST['poll_id']."'>";
}else{
	$text .= "<input type='hidden' name='poll_id' value='".$poll_id."'>";
}
$text .= "</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>Polls</div>", $text);
require_once("footer.php");
?>