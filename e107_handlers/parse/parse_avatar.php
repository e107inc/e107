<?php
function parse_avatar($match,$referrer){
	$m = explode(".",$match[1]);
	if(is_numeric($m[2])){
		if($m[2] == USERID){
			$image = USERIMAGE;
		} else {
			$sql2 = new db;
			$sql2 -> db_Select("user","user_image","user_id = '{$m[2]}'");
			$row = $sql2 -> db_Fetch();
			$image=$row['user_image'];
		}
	} elseif($m[2]) {
		$image=$m[2];
	} else {
		$image = USERIMAGE;
	}
	if($image){
		require_once(e_HANDLER."avatar_handler.php");
		return "<img src='".avatar($image)."' alt='' />";
	} else {
		return "";
	}
}
?>