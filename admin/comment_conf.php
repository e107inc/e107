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

if(IsSet($_SERVER['QUERY_STRING'])){
	$temp = explode("-", $_SERVER['QUERY_STRING']);
	$action = $temp[0];
	$id = $temp[1];
	$url = $temp[2];
	if($action == "block"){
		$sql -> db_Update("comments", "comment_blocked='1' WHERE comment_id='$id' ");
	}
	if($action == "unblock"){
		$sql -> db_Update("comments", "comment_blocked='0' WHERE comment_id='$id' ");
	}
	if($action == "delete"){
		$sql -> db_Delete("comments", "comment_id='$id' ");
	}
	header("location:".$url);
}
?>