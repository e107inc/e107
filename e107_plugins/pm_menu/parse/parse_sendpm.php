<?php
function parse_sendpm($match,$referrer){
	global $pref;
	$m = explode(".",$match[1]);
	@include_once(e_PLUGIN."pm_menu/languages/".e_LANGUAGE.".php");
	@include_once(e_PLUGIN."pm_menu/languages/English.php");
	if(check_class($pref['pm_userclass'])){
		$img=(file_exists(THEME."forum/pm.png")) ? "<img src='".THEME."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />" : "<img src='".e_IMAGE."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />";
		return  "<a href='".e_PLUGIN."pm_menu/pm.php?send.{$m[2]}'>{$img}</a>";
	} else {
		return "";
	}
}
?>