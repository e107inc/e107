<?php
function parse_picture($match,$referrer){
	$m = explode(".",$match[1]);
	if(is_numeric($m[2])){
		if($m[2] == USERID){
			$image = USERSESS;
		} else {
			$sql2 = new db;
			$sql2 -> db_Select("user","user_sess","user_id = '{$m[2]}'");
			$row = $sql2 -> db_Fetch();
			$image=$row['user_sess'];
		}
	} elseif($m[2]) {
		$image=$m[2];
	} else {
		$image = USERSESS;
	}
	if($image && file_exists(e_FILE."public/avatars/".$image)){
		return "<img src='".e_FILE."public/avatars/{$image}' alt='' />";
	} else {
		return "";
	}
}
?>