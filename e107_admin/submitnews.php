<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/submitnews.php
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
if(!getperms("N")){ header("location:".e_BASE."index.php"); exit;}

if(IsSet($_POST['transfer'])){ 
	$sql -> db_Update("submitnews", "submitnews_auth='1' WHERE submitnews_id ='".$_POST['id']."' ");
	header("location:newspost.php?sn.".$_POST['id']);
	exit;
}

require_once("auth.php");

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("submitnews", "submitnews_id ='".$_POST['id']."' ");
	$message = SUBLAN_1;
}

if($_POST['delete']){
	$text = "<div style='text-align:center'>
	<b>".SUBLAN_2."</b>
<br /><br />
<form method='post' action='".e_SELF."'>
<input class='button' type='submit' name='cancel' value='".SUBLAN_3."' /> 
<input class='button' type='submit' name='confirm' value='".SUBLAN_4."' /> 
<input type='hidden' name='id' value='".$_POST['id']."'>
</form>
</div>";
$ns -> tablerender("".SUBLAN_5."", $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = SUBLAN_6;
}

if(IsSet($_POST['unauth_sn'])){
	$sql -> db_Update("submitnews", "submitnews_auth='1' WHERE submitnews_id ='".$_POST['id']."' ");
}
if(IsSet($_POST['reeval'])){
	$sql -> db_Update("submitnews", "submitnews_auth='0' WHERE submitnews_id ='".$_POST['id']."' ");
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ORDER BY submitnews_datestamp DESC")){
	$text = "<div style='text-align:center'><b>".SUBLAN_7."</b></div>
	<br />";
}else{
	$submit_total = $sql -> db_Rows();
	if($submit_total == 1){
		$text = "<div style='text-align:center'><b>".SUBLAN_8."</b></div>";
	}else{
		$text = "<div style='text-align:center'><b>".SUBLAN_9." ".$submit_total." ".SUBLAN_10.".</b></div>";
	}
	$text .= "<br />
	<br />";
	while(list($submitnews_id, $submitnews_name, $submitnews_email, $submitnews_title, $submitnews_item, $submitnews_datestamp, $submitnews_ip, $submitnews_auth) = $sql-> db_Fetch()){
		$obj = new convert;
		$datestamp = $obj->convert_date($submitnews_datestamp, "long");
		if($submitnews_ip == ""){ $submitnews_ip = SUBLAN_18; }
		$text .= SUBLAN_19." <b>".$submitnews_name. "</b>
		<br />
		[".SUBLAN_11.": ".$submitnews_email." (ip: ".$submitnews_ip.")]
		<br />
		[".SUBLAN_12." ".$datestamp."]
		<br />
		<br />
		<span class='mediumtext'>
		".SUBLAN_13.": <i>".$submitnews_title."</i><br />
		".SUBLAN_14.":
		<i>".$submitnews_item."</i>
		</span>
		<br />
		<br />";
		$text .= "<form method='post' action='".e_SELF."'>
		<input type='hidden' name='news_body' value='$submitnews_item'>
		<input type='hidden' name='news_source' value='Submitted by $submitnews_name [$submitnews_email]'>
		<input type='hidden' name='id' value='$submitnews_id'>
		<input class='button' type='submit' name='transfer' value='".SUBLAN_15."' />
		<input class='button' type='submit' name='unauth_sn' value='".SUBLAN_16."' />
		</form>
		<br />
		<br />";
	}
}	

$sql -> db_Select("submitnews", "*", "submitnews_auth ='1' ");
$sub_total = $sql -> db_Rows();

if($sub_total == "0"){
	$text .= "<div style='text-align:center'><b>".SUBLAN_17.".</b></div>
	<br />";
}else{
	$text .= "<div style='text-align:center'>
	<b>".SUBLAN_20." ...</b>
	</div>
	<br />
	<br />";
	while(list($submitnews_id, $submitnews_name, $submitnews_email, $submitnews_item, $submitnews_datestamp, $submitnews_ip, $submitnews_auth) = $sql-> db_Fetch()){
		$obj = new convert;
		$datestamp = $obj->convert_date($submitnews_datestamp, "short");
		$item = substr($submitnews_item, 0, 75)." ...";
		if($submitnews_ip == ""){ $submitnews_ip = "Unknown"; }
		$text .= "<form method='post' action='".e_SELF."?id=$submitnews_id'>
		".SUBLAN_19." <b>".$submitnews_name. "</b>
		<br />
		[".SUBLAN_11.": ".$submitnews_email." (ip: ".$submitnews_ip.")]
		<br />
		[".SUBLAN_12." ".$datestamp."]
		<br />
		<span class='mediumtext'>
		".SUBLAN_14.":
		<i>".$item."</i>
		</span>
		<br />
		<br />
		<form method='post' action='submitnews.php'>
		<input type='hidden' name='news_body' value='$submitnews_item'>
		<input type='hidden' name='news_source' value='Submitted by $submitnews_name [$submitnews_email]'>
		<input type='hidden' name='id' value='$submitnews_id'>
		<input class='button' type='submit' name='reeval' value='".SUBLAN_21."' />
		<input class='button' type='submit' name='delete' value='".SUBLAN_22."' />
		</form>
		<br />
		<br />";
	}
}

$ns -> tablerender("<div style='text-align:center'>".SUBLAN_23."</div>", $text);

require_once("footer.php");
?>