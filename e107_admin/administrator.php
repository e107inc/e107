<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/administrator.php
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
if(!getperms("3")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

if(IsSet($_POST['add_admin'])){
	if(!$_POST['ad_name'] || !$_POST['a_password']){
		$message = ADMSLAN_55;
	}else{
		for ($i=0; $i<=29; $i++){
			if($_POST['perms'][$i]){
				$perm .= $_POST['perms'][$i].".";
			}
		}
		
		if(!$sql -> db_Select("user", "*", "user_name='".$_POST['ad_name']."' ")){
			$sql -> db_Insert("user", "0, '".$_POST['ad_name']."', '".md5($_POST['a_password'])."', '', '".$_POST['ad_email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".time()."', '0', '".time()."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '1', '', '', '$perm', '', '' ");
			$message = ADMSLAN_0." ".$_POST['ad_name']."<br />";
		}else{
			$sql -> db_Update("user", "user_admin='1', user_perms='$perm' WHERE user_name='".$_POST['ad_name']."' ");
		}
		$message = $_POST['ad_name']." ".ADMSLAN_1."<br />";
	}
}

if(IsSet($_POST['update_admin'])){
	$sql -> db_Select("user", "*", "user_id='".$_POST['a_id']."' ");
	$row = $sql -> db_Fetch();
	$a_name = $row['user_name'];
	if($_POST['a_password'] == ""){
		$admin_password = $row['user_password'];
	}else{
		$admin_password = md5($_POST['a_password']);
	}

	for ($i=0; $i<=29; $i++){
		if($_POST['perms'][$i]){
			$perm .= $_POST['perms'][$i].".";
		}
	}
	$sql -> db_Update("user", "user_password='$admin_password', user_perms='$perm' WHERE user_name='$a_name' ");
	unset($ad_name, $a_password, $a_perms);
	$message = "Administrator ".$_POST['ad_name']." ".ADMSLAN_2."<br />";
}

if(IsSet($_POST['edit'])){
	$sql -> db_Select("user", "*", "user_id='".$_POST['existing']."' ");
	$row = $sql-> db_Fetch();
	extract($row);
	$a_id = $user_id; $ad_name = $user_name; $a_perms = $user_perms;
	if($a_perms == "0"){
		$text = "<div style='text-align:center'>$ad_name ".ADMSLAN_3."
		<br /><br />
		<a href='administrator.php'>".ADMSLAN_4."</a></div>";
		$ns -> tablerender("<div style='text-align:center'>".ADMSLAN_5."</div>", $text);
		require_once("footer.php");
		exit;
	}
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("user", "*", "user_id='".$_POST['existing']."' ");
	$row = $sql-> db_Fetch();
	extract($row);

	$text = "<div style='text-align:center'>";

	if($user_perms == "0"){
		$text .= "$user_name ".ADMSLAN_6."
		<br /><br />
		<a href='administrator.php'>".ADMSLAN_4."</a>";
		$ns -> tablerender("<div style='text-align:center'>".ADMSLAN_5."</div>", $text);
		require_once("footer.php");
		exit;
	}


	$text .= "<b>".ADMSLAN_7." '$user_name' ".ADMSLAN_8."</b>
<br /><br />
<form method='post' action='".e_SELF."'>
<input class='button' type='submit' name='cancel' value='".ADMSLAN_9."' /> 
<input class='button' type='submit' name='confirm' value='".ADMSLAN_10."' /> 
<input type='hidden' name='existing' value='$user_name'>
</form>
</div>";
$ns -> tablerender(ADMSLAN_11, $text);
	
			require_once("footer.php");
	exit;
}


if(IsSet($_POST['cancel'])){
	$message = ADMSLAN_12;
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Update("user", "user_admin=0, user_perms='' WHERE user_name='".$_POST['existing']."' ");
	$message = "Administrator deleted.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$sql -> db_Select("user", "*", "user_admin='1'");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='myform'>
<table style='width:95%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>
<span class='defaulttext'>".ADMSLAN_13.":</span> 
<select name='existing' class='tbox'>";
while(list($admin_id_, $admin_name_) = $sql-> db_Fetch()){
	$text .= "<option value='$admin_id_'>".$admin_name_."</option>";
}
$text .= "</select>
<input class='button' type='submit' name='delete' value='".ADMSLAN_14."' /> \n
<input class='button' type='submit' name='edit' value='".ADMSLAN_15."' />\n
</td></tr>

<tr>
<td style='width:30%' class='forumheader3'>".ADMSLAN_16.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='ad_name' size='60' value='$ad_name' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".ADMSLAN_17.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='a_password' size='60' value='$a_password' maxlength='100' />
</td>
</tr>

<tr> 
<td style='width:30%' class='forumheader3'>".ADMSLAN_18.": <br /></td>
<td style='width:70%' class='forumheader3'>";

function checkb($arg, $perms){
	if(getperms($arg, $perms)){
		$par = "<input type='checkbox' name='perms[]' value='$arg' checked>\n";
	}else{
		$par = "<input type='checkbox' name='perms[]' value='$arg'>\n";
	}
	return $par;
}

$text .= checkb("1", $a_perms).ADMSLAN_19."<br />";
$text .= checkb("2", $a_perms).ADMSLAN_20."<br />";
$text .= checkb("3", $a_perms).ADMSLAN_21."<br />";
$text .= checkb("4", $a_perms).ADMSLAN_22."<br />";
$text .= checkb("5", $a_perms).ADMSLAN_23."<br />";
$text .= checkb("Q", $a_perms).ADMSLAN_24."<br />";
$text .= checkb("6", $a_perms).ADMSLAN_25."<br />";
$text .= checkb("7", $a_perms).ADMSLAN_26."<br />";
$text .= checkb("8", $a_perms).ADMSLAN_27."<br />";
$text .= checkb("9", $a_perms).ADMSLAN_28."<br /><br />";

$text .= checkb("D", $a_perms).ADMSLAN_29."<br />";
$text .= checkb("E", $a_perms).ADMSLAN_30."<br />";
$text .= checkb("F", $a_perms).ADMSLAN_31."<br />";
$text .= checkb("G", $a_perms).ADMSLAN_32."<br />";
$text .= checkb("S", $a_perms).ADMSLAN_33."<br />";
$text .= checkb("T", $a_perms).ADMSLAN_34."<br />";
$text .= checkb("V", $a_perms).ADMSLAN_35."<br />";

$text .= checkb("A", $a_perms).ADMSLAN_36."<br />";
$text .= checkb("B", $a_perms).ADMSLAN_37."<br />";
$text .= checkb("C", $a_perms).ADMSLAN_38."<br /><br />";

$text .= checkb("H", $a_perms).ADMSLAN_39."<br />";
$text .= checkb("I", $a_perms).ADMSLAN_40."<br />";
$text .= checkb("J", $a_perms).ADMSLAN_41."<br />";
$text .= checkb("K", $a_perms).ADMSLAN_42."<br />";
$text .= checkb("L", $a_perms).ADMSLAN_43."<br />";
$text .= checkb("R", $a_perms).ADMSLAN_44."<br />";
$text .= checkb("U", $a_perms).ADMSLAN_45."<br />";
$text .= checkb("M", $a_perms).ADMSLAN_46."<br />";
$text .= checkb("N", $a_perms).ADMSLAN_47."<br /><br />";

$text .= checkb("P", $a_perms).ADMSLAN_48."<br />";

$text .= "
<br />
<a href='".e_SELF."?checkall=1' onclick=\"setCheckboxes('myform', true); return false;\">".ADMSLAN_49."</a> - 
<a href='".e_SELF."' onclick=\"setCheckboxes('myform', false); return false;\">".ADMSLAN_51."</a>

</td>
</tr>";

$text .= "<tr style='vertical-align:top'> 
<td colspan='2' style='text-align:center' class='forumheader'>";

if(IsSet($_POST['edit'])){
	$text .= "<input class='button' type='submit' name='update_admin' value='".ADMSLAN_52."' />
	<input type='hidden' name='a_id' value='$a_id'>";
}else{
	$text .= "<input class='button' type='submit' name='add_admin' value='".ADMSLAN_53."' />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".ADMSLAN_54."</div>", $text);

require_once("footer.php");
?>