<?php
function pm_menu_parse($match,$referrer){
	global $pref;
	@include_once(e_PLUGIN."pm_menu/languages/".e_LANGUAGE.".php");
	@include_once(e_PLUGIN."pm_menu/languages/English.php");
	
	if('SENDPM' == $match[1] AND (check_class($pref['pm_userclass']) || $referrer=='admin')){
		$img=(file_exists(THEME."forum/pm.png")) ? "<img src='".THEME."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />" : "<img src='".e_IMAGE."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />";
		return  "<a href='".e_PLUGIN."pm_menu/pm.php?send.{$match[2]}'>{$img}</a>";
	}
}
?>