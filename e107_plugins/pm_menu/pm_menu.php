<?php
if((USER && check_class($pref['pm_userclass'])) || ADMINPERMS=="0"){
	require_once(e_PLUGIN."pm_menu/pm_inc.php");
	$caption = ($pref['pm_title'] == "PMLAN_PM") ? PMLAN_PM : $pref['pm_title'];
	$ns->tablerender($caption,pm_show_stats(1));
}
?>