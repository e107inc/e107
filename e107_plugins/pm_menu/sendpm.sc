global $pref;
@include_once(e_PLUGIN."pm_menu/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."pm_menu/languages/English.php");
if(check_class($pref['pm_userclass']))
{
	if(file_exists(THEME."forum/pm.png"))
	{
		$img = "<img src='".THEME."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />";
	}
	else
	{
		$img = "<img src='".e_IMAGE."forum/pm.png' alt='".PMLAN_PM."' title='".PMLAN_PM."' style='border:0' />";
	}
	return  "<a href='".e_PLUGIN."pm_menu/pm.php?send.{$parm}'>{$img}</a>";
}
else
{
	return "";
}
