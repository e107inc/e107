global $sql;
if(!$sql -> db_Select("menus", "*", "(menu_name='edynamic_menu' OR menu_name REGEXP('tree_menu')) AND menu_location!=0"))
{
	$linktype = $parm;
	define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
	if(function_exists("theme_sitelinks"))
	{
		call_user_func("theme_sitelinks");
	}
	else
	{
		require_once(e_HANDLER."sitelinks_class.php");
		sitelinks();
	}
	return "";
}
