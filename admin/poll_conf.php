<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/poll_conf.php													|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(ADMINPERMS != 0 && ADMINPERMS != 1 && ADMINPERMS != 2){
	header("location:../index.php");
}
require_once("auth.php");

$po = new poll;

if(IsSet($_POST['reset'])){
	unset($poll_id, $poll_title_, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10);
}

if(IsSet($_POST['deletecancel'])){
	$message = "Delete cancelled.<br />";
}

if(IsSet($_POST['deleteconfirm'])){
	echo $existing;
	$message = $po -> delete_poll($_POST['existing']);
	$poll_id = "";
}

if(IsSet($_POST['edit'])){
	$row = $po -> edit_poll($_POST['existing']);
	$poll_id = $row['poll_id'];
	$poll_title_ = $row['poll_title'];
	$poll_option_1 = $row['poll_option_1'];
	$poll_option_2 = $row['poll_option_2'];
	$poll_option_3 = $row['poll_option_3'];
	$poll_option_4 = $row['poll_option_4'];
	$poll_option_5 = $row['poll_option_5'];
	$poll_option_6 = $row['poll_option_6'];
	$poll_option_7 = $row['poll_option_7'];
	$poll_option_8 = $row['poll_option_8'];
	$poll_option_9 = $row['poll_option_9'];
	$poll_option_10 = $row['poll_option_10'];
}

if(IsSet($_POST['submit'])){
	$sql -> db_Update("prefs", "pref_value='".$_POST['poll_system_activate ']."' WHERE pref_name='poll_activate ' ");
	$message = $po -> submit_poll($_POST['poll_id'], $_POST['poll_title'], $_POST['poll_option_1'], $_POST['poll_option_2'], $_POST['poll_option_3'], $_POST['poll_option_4'], $_POST['poll_option_5'], $_POST['poll_option_6'], $_POST['poll_option_7'], $_POST['poll_option_8'], $_POST['poll_option_9'], $_POST['poll_option_10'], $_POST['activate'], $admin_id);
	unset($poll_id, $poll_title, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10);
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("poll", "*", "poll_id='".$_POST['existing']."' ");
	list($null, $null, $null, $null, $poll_title_) = $sql-> db_Fetch();
	
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the poll '$poll_title_' - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"$PHP_SELF\">
<input class=\"button\" type=\"submit\" name=\"deletecancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"deleteconfirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Catagory", $text);
	require_once("footer.php");
	exit;
}

if(IsSet($_POST['preview'])){
	$poll_title_ = $_POST['poll_title'];
	$poll_option_1 = $_POST['poll_option_1'];
	$poll_option_2 = $_POST['poll_option_2'];
	$poll_option_3 = $_POST['poll_option_3'];
	$poll_option_4 = $_POST['poll_option_4'];
	$poll_option_5 = $_POST['poll_option_5'];
	$poll_option_6 = $_POST['poll_option_6'];
	$poll_option_7 = $_POST['poll_option_7'];
	$poll_option_8 = $_POST['poll_option_8'];
	$poll_option_9 = $_POST['poll_option_9'];
	$poll_option_10 = $_POST['poll_option_10'];

	$po -> preview($poll_id, $_POST['poll_title'], $_POST['poll_option_1'], $_POST['poll_option_2'], $_POST['poll_option_3'], $_POST['poll_option_4'], $_POST['poll_option_5'], $_POST['poll_option_6'], $_POST['poll_option_7'], $_POST['poll_option_8'], $_POST['poll_option_9'], $_POST['poll_option_10']);
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

//########################################################################################################

$text = "<div style=\"text-align:center\">";
$sql -> db_Select("poll");
$poll_total = $sql -> db_Rows();
if($poll_total == "0"){
	$text .= "No polls set yet.
	</div>
	<br />";
}else{
	$text .= "<form method=\"post\" action=\"$PHP_SELF\">
	Existing Polls: 
	<select name=\"existing\" class=\"tbox\">";
	while($row = $sql-> db_Fetch()){
		$text .= "<option value=\"".$row['poll_id']."\">".$row['poll_title']."</option>";
	}
	$text .= "</select> 
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" /> 
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	</form>
	</div>
	<br />";
}

$text .= "
<form name='form1' method='post' action='$PHP_SELF'>
<table style=\"width:95%\">
<tr> 
<td style=\"width:20%\"><div class=\"normaltext\">Poll Question:</div></td>
<td>
<input class=\"tbox\" type=\"text\" name=\"poll_title\" size=\"70\" value=\"$poll_title_\" maxlength=\"200\" />";

$counter = 1;

for($count=1; $count<=10; $count++){
	$var = "poll_option_".$count;
	$option = stripslashes($$var);
	$text .= "<tr>
<td style=\"width:30%\">Option ".$count.":</td>
<td>
<input class=\"tbox\" type=\"text\" name=\"poll_option_$count\" size=\"60\" value=\"$option\" maxlength=\"200\" />
</td></tr>";
}

$text .= "<tr>
<td style=\"width:20%\">Make active poll?:</td>
<td>";

if($_POST['activate']){
	$text .= "<input type=\"checkbox\" name=\"activate\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"activate\" value=\"1\">";
}
$text .= "</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<br />";

if(IsSet($_POST['preview'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"preview\" value=\"Preview again\" /> ";
	if($_POST['poll_id'] != ""){
		$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Update poll in database\" /> ";

	}else{
		$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Post poll to database\" /> ";
	}
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"preview\" value=\"Preview\" /> ";
}
if(IsSet($poll_id)){
	$text .= "<input class=\"button\" type=\"submit\" name=\"reset\" value=\"New poll\" /> ";
}

$text .= "</td></tr></table>";
if($_POST['poll_id']){
	$text .= "<input type=\"hidden\" name=\"poll_id\" value=\"".$_POST['poll_id']."\">";
}else{
	$text .= "<input type=\"hidden\" name=\"poll_id\" value=\"".$poll_id."\">";
}
$text .= "</form>";

$ns -> tablerender("<div style=\"text-align:center\">Polls</div>", $text);
require_once("footer.php");

class poll{

	var $theme;

//#########################################################################################################
	function edit_poll($existing){
		$cls = new db;
		
		if($cls -> db_Select("poll", "*", "poll_id='$existing' ")){
			$row = $cls-> db_Fetch();
		}
		
		$row['poll_title'] = stripslashes($row['poll_title']);
		$row['poll_option_1'] = stripslashes($row['poll_option_1']);
		$row['poll_option_2'] = stripslashes($row['poll_option_2']);
		$row['poll_option_3'] = stripslashes($row['poll_option_3']);
		$row['poll_option_4'] = stripslashes($row['poll_option_4']);
		$row['poll_option_5'] = stripslashes($row['poll_option_5']);
		$row['poll_option_6'] = stripslashes($row['poll_option_6']);
		$row['poll_option_7'] = stripslashes($row['poll_option_7']);
		$row['poll_option_8'] = stripslashes($row['poll_option_8']);
		$row['poll_option_9'] = stripslashes($row['poll_option_9']);
		$row['poll_option_10'] = stripslashes($row['poll_option_10']);
		return $row;
	}

//#########################################################################################################
	function delete_poll($existing){
		$cls = new db;
		if($cls -> db_Delete("poll", " poll_id='".$existing."' ")){
			return  "Poll deleted.";
		}
	}

//#########################################################################################################
	function submit_poll($poll_id, $poll_name, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10, $activate, $admin_id){
		$datestamp = time();
		$cls = new db;
		if($activate == "1"){
			$cls -> db_Update("poll", "poll_active='0', poll_end_datestamp='$datestamp' WHERE poll_active='1' ");
			$message = "Poll entered into database and made active.";
		}else{
			$message = "Poll entered into database.";
		}
		if($poll_id != ""){
			$cls -> db_Update("poll", "poll_title='$poll_name', poll_option_1='$poll_option_1', poll_option_2='$poll_option_2', poll_option_3='$poll_option_3', poll_option_4='$poll_option_4', poll_option_5='$poll_option_5', poll_option_6='$poll_option_6', poll_option_7='$poll_option_7', poll_option_8='$poll_option_8', poll_option_9='$poll_option_9', poll_option_10='$poll_option_10', poll_active='$activate' WHERE poll_id='$poll_id' ");
			$message = "Poll updated in database.";
		}else{
			$cls -> db_Insert("poll", "'0', '$datestamp', '0', '$admin_id', '$poll_name', '$poll_option_1', '$poll_option_2', '$poll_option_3', '$poll_option_4', '$poll_option_5', '$poll_option_6', '$poll_option_7', '$poll_option_8', '$poll_option_9', '$poll_option_10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '$activate' ");
		}
		return $message;
	}

//#########################################################################################################
	function preview($poll_id, $poll_name, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10){

		$this -> render_poll($poll_name, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10, "preview");
	}	

//#########################################################################################################
	function render_poll($poll_name, $poll_option_1, $poll_option_2, $poll_option_3, $poll_option_4, $poll_option_5, $poll_option_6, $poll_option_7, $poll_option_8, $poll_option_9, $poll_option_10, $mode = "normal", $vote_1 = 0, $vote_2 = 0, $vote_3 = 0, $vote_4 = 0, $vote_5 = 0, $vote_6 = 0, $vote_7 = 0, $vote_8 = 0, $vote_9 = 0, $vote_10 = 0){

	$options = 0;
	for($count=1; $count<=10; $count++){
		$var = "poll_option_".$count;
		$var2 = $$var;
		$var3 = "vote_".$count;
		$poll[$count] = stripslashes($var2);
		$votes[$count] = $$var3;
		if($var2 != ""){
			$options++;
		}
	}

	$text = "<table style=\"width:35%\" class=\"border\" cellspacing=\"3\" align=\"center\">
	<tr>
	<td>
	<br />
	<div style=\"text-align:center\"><b>".$poll_name."</b></div>
	<hr />
	</div>
	<br />
	<form method=\"post\" action=\"$PHP_SELF\">
	<p>";
	for($counter=1; $counter<=$options; $counter++){
	
		$text .= "<input type=\"radio\" name=\"votea\" value=\"$counter\" />
		<span class=\"mediumtext\"><b>".stripslashes($poll[$counter])."</b></span>
		<br />
		<span class=\"smalltext\">
		<img src=\"".THEME."/images/bar.jpg\" height=\"12\" width=\"".($percen[$counter]*2)."\" style=\"border : 1px solid Black\" alt=\"\" />
		0% [No votes]<br />";

	}

	$text .= "<br />
	<div style=\"text-align:center\">
	Votes: 0
	<br />
	[ <a href=\"oldpolls.php\">Old Surveys</a> ]
	</td>
	</tr>
	</table>";

	$ps = new table;
	$ps -> tablerender("<div style=\"text-align:center\">Poll Preview</div>", $text);
	}
}



?>