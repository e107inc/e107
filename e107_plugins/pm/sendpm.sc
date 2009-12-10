include_lan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');

global $sysprefs, $pm_prefs;
$pm_prefs = $sysprefs->getArray("pm_prefs");
if(check_class($pm_prefs['pm_class']))
{
	if(file_exists(THEME."forum/pm.png"))
	{
		$img = "<img src='".THEME_ABS."forum/pm.png' alt='".LAN_PM."' title='".LAN_PM."' style='border:0' />";
	}
	else
	{
		$img = "<img src='".e_PLUGIN_ABS."pm/images/pm.png' alt='".LAN_PM."' title='".LAN_PM."' style='border:0' />";
	}
	return  "<a href='".e_PLUGIN_ABS."pm/pm.php?send.{$parm}'>{$img}</a>";
}
else
{
	return "";
}
