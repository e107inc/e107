global $sql, $override;
if(!$sql -> db_Select("menus", "*", "(menu_name='edynamic_menu' OR menu_name REGEXP('tree_menu')) AND menu_location!=0"))
{
	$linktype = $parm;
	define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
	if($override_function = $override -> override_check('sitelinks')){
		call_user_func($override_function);
	} else {	
		require_once(e_HANDLER."sitelinks_class.php");
		sitelinks();
	}
	return "";
}
