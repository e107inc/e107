<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/plugin_conf/chatbox_conf.php						|
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
if(!getperms("B")){ header("location:../index.php"); }
require_once("auth.php");

if(IsSet($_SERVER['QUERY_STRING'])){
	$temp = explode("-", $_SERVER['QUERY_STRING']);
	$action = $temp[0];
	$id = $temp[1];
	$url = $temp[2];
	if($action == "block"){
		$sql -> db_Update("comments", "comment_blocked='1' WHERE comment_id='$id' ");
		$message = "<b>Comment item blocked.</b><br /><br /><a href=\"$url\">Return</a>";
	}
	if($action == "unblock"){
		$sql -> db_Update("comments", "comment_blocked='0' WHERE comment_id='$id' ");
		$message = "<b>Comment item unblocked.</b><br /><br /><a href=\"$url\">Return</a>";
	}
	if($action == "delete"){
		$sql -> db_Delete("comments", "comment_id='$id' ");
		$message = "<b>Comment item deleted.</b><br /><br /><a href=\"$url\">Return</a>";
	}
	echo "<div style=\"text-align:center\">$message</div>";
	require_once("footer.php");
	exit;
}
?>