global $sql;
global $ns;
$menu = $parm;
global $eMenuList;
if (!array_key_exists($menu,$eMenuList)) {
	return;
}
foreach($eMenuList[$menu] as $row) {
	extract($row);
	$show_menu = TRUE;
	if($menu_pages) {
		list($listtype,$listpages) = explode("-",$menu_pages);
		$pagelist = explode("|",$listpages);
		$check_url = e_SELF."?".e_QUERY;
		if($listtype == '1') { //show menu
			$show_menu = FALSE;
			foreach($pagelist as $p) {
				if(strpos($check_url,$p) !== FALSE) {
					$show_menu = TRUE;
				}
			}
		}
		if($listtype == '2') { //hide menu
			$show_menu = TRUE;
			foreach($pagelist as $p) {
				if(strpos($check_url,$p) !== FALSE) {
					$show_menu = FALSE;
				}
			}
		}
	}
	if($show_menu) {
		$sql->db_Mark_Time($menu_name);
		if($menu_path != 'custom')
		{
			include(e_PLUGIN.$menu_name."/languages/".e_LANGUAGE.".php");
			if(e_LANGUAGE != 'English')
			{
				include(e_PLUGIN.$menu_name."/languages/English.php");
			}
		}
		include(e_PLUGIN.$menu_path."/".$menu_name.".php");
			
/*
		if(strstr($menu_name, "custom_")) {
			e107_require_once(e_PLUGIN."custom/".str_replace("custom_", "", $menu_name).".php");
		} else {
			include(e_PLUGIN.$menu_name."/languages/".e_LANGUAGE.".php");
			if(e_LANGUAGE != 'English') {
				include(e_PLUGIN.$menu_name."/languages/English.php");
			}
			include(e_PLUGIN.$menu_name."/".$menu_name.".php");
		}
*/
		$sql->db_Mark_Time("(After $menu_name)");
	}
}
