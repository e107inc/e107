<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107_handlers/parser_functions.php
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
function e107core_parse($match,$referrer){
	global $pref;
	if($match[1] == "EMAILTO"){
		$image = (file_exists(THEME."forum/email.png")) ? "<img src='".THEME."forum/email.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/email.png' alt='' style='border:0' />";
		if(is_numeric($match[2])){
			if(!$pref['emailusers']){return $match[0];}
			return "<a href='".e_BASE."emailmember.php?id.{$match[2]}'>{$image}</a>";
		} else {
			return "<a href='mailto:{$match[2]}'>{$image}</a>";
		}
	}
	if($match[1] == "PROFILE"){
		$image = (file_exists(THEME."forum/profile.png")) ? "<img src='".THEME."forum/email.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/profile.png' alt='' style='border:0' />";
		return "<a href='".e_BASE."user.php?id.{$match[2]}'>{$image}</a>";
	}
}

?>
