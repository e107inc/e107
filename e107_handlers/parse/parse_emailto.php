<?php
function parse_emailto($match,$referrer){
	global $pref;
	$image = (file_exists(THEME."forum/email.png")) ? "<img src='".THEME."forum/email.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/email.png' alt='' style='border:0' />";
	$m = explode(".",$match[1],3);
	if(is_numeric($m[2])){
		if(!$pref['emailusers']){return "";}
		return "<a href='".e_BASE."emailmember.php?id.{$match[2]}'>{$image}</a>";
	} else {
		return "<a href='mailto:{$m[2]}'>{$image}</a>";
	}
}
?>