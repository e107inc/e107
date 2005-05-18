<?php

function getTypeidFromNid($nid){
	global $sql;
	$sql = new db;

	$sql -> db_Select("pcontent", "*", "content_id='".$nid."' " );
	$row = $sql -> db_Fetch();
	if($row['content_parent'] == 0){
		$type_id = $row['content_id'];
	}else{
		$tmp = explode(".", $row['content_parent']);
		$type_id = $tmp[0];
	}
	return $type_id;
}

$type_id = getTypeidFromNid($nid);

$e_plug_table = "pcontent"; //This is set to the table name you have decided to use.
$reply_location= e_PLUGIN."content/content.php?type.".$type_id.".content.$nid"; //This is set to the location you'd like the user to return to after replying to a comment.
$db_table = "pcontent"; //This is the name of your plugins database table.
$link_name = "content_heading"; //This is the name of the field in your plugin's db table that corresponds to it's name or title.
$db_id = "content_id"; // This is the name of the field in your plugin's db table that correspond to it's unique id number.
$plugin_name = "Content"; // A name for your plugin. It will be used in links to comments, in list_new/new.php.

?>