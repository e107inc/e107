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
if(!getperms("4")){ header("location:".e_HTTP."index.php"); exit;}
require_once("auth.php");

if(!e_QUERY){
	header("location:admin.php");
	exit;
}else{
	$id = e_QUERY;
}
if(IsSet($_POST['updateclass'])){
	extract($_POST);
	for($a=0; $a<=(count($_POST['userclass'])-1); $a++){
		$svar .= $userclass[$a].".";
	}
	$sql -> db_Update("user", "user_class='$svar' WHERE user_id='$id' ");
	echo "<div style=\"text-align:center\"><b>Classes updated.</b></div><br /><br />";
}


$sql -> db_Select("user", "*", "user_id='$id' ");
$row = $sql -> db_Fetch(); extract($row);

$sql -> db_Select("userclass_classes");
$c=0;
while($row = $sql -> db_Fetch()){
	$class[$c][0] = $row['userclass_id'];
	$class[$c][1] = $row['userclass_name'];
	$class[$c][2] = $row['userclass_description'];
	$c++;
}


$caption = "Set class for user <b>".$user_name."</b> (".$user_class.")";

$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\">
<table style=\"width:90%\" class=\"fborder\">";

for($a=0; $a<= (count($class)-1); $a++){
	$text .= "<tr><td style=\"width:30%\" class=\"fcaption\">";
	if(check_class($class[$a][0], $user_class)){
		$text .= "<input type=\"checkbox\" name=\"userclass[]\" value=\"".$class[$a][0]."\" checked>".$class[$a][1]." ";
	}else{
		$text .= "<input type=\"checkbox\" name=\"userclass[]\" value=\"".$class[$a][0]."\">".$class[$a][1]." ";
	}
	$text .= "</td><td style=\"width:70%\" class=\"forumheader\"> ".$class[$a][2]."</td></tr>";
}

$text .= "<tr><td colspan=\"2\" style=\"text-align:center\"><br /><input class=\"button\" type=\"submit\" name=\"updateclass\" value=\"Set Classes\" />
<br /><br />
</td>
</tr>
</table>
</form>";

$ns -> tablerender($caption, $text);


require_once("footer.php");
?>