<?php
function parse_profile($match,$referrer){
	$m = explode(".",$match[1]);
	$id = ($m[2]) ? $m[2] : USERID;
	$image = (file_exists(THEME."forum/profile.png")) ? "<img src='".THEME."forum/profile.png' alt='User Profile' title='User Profile' style='border:0' />" : "<img src='".e_IMAGE."forum/profile.png' alt='User Profile' title='User Profile' style='border:0' />";
	return "<a href='".e_BASE."user.php?id.{$id}'>{$image}</a>";
}
?>