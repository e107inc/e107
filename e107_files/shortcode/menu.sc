global $sql;
global $ns;
$menu = $parm;
global $eMenuList;
foreach($eMenuList[$menu] as $row)
{
	extract($row);
	$show_menu = TRUE;
	if($menu_pages)
	{
		list($listtype,$listpages) = explode("-",$menu_pages);
		$pagelist = explode("|",$listpages);
		$check_url = e_SELF."?".e_QUERY;
		if($listtype == '1') //show menu
		{
			$show_menu = FALSE;
			foreach($pagelist as $p)
			{
				if(strpos($check_url,$p) !== FALSE)
				{
					$show_menu = TRUE;
				}
			}
		}
		if($listtype == '2') //hide menu
		{
			$show_menu = TRUE;
			foreach($pagelist as $p)
			{
				if(strpos($check_url,$p) !== FALSE)
				{
					$show_menu = FALSE;
				}
			}
		}
	}
	if(check_class($menu_class) && $show_menu)
	{
		if(strstr($menu_name, "custom_"))
		{
			require_once(e_PLUGIN."custom/".str_replace("custom_", "", $menu_name).".php");
		}
		else
		{
			@include(e_PLUGIN.$menu_name."/languages/".e_LANGUAGE.".php");
			@include(e_PLUGIN.$menu_name."/languages/English.php");
			require_once(e_PLUGIN.$menu_name."/".$menu_name.".php");
		}
	}
}
