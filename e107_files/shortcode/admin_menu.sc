if (ADMIN) {
	global $ns, $pref;
	//Show upper_right menu if the function exists
	$tmp = explode(".",e_PAGE);
	$adminmenu_func = $tmp[0]."_adminmenu";
	if(function_exists($adminmenu_func)){
		if (!$parm) {
			call_user_func($adminmenu_func,$adminmenu_parms);
		} else {
			$ret = 'pre';
			return $ret;
		}
	}
	$plugindir = (str_replace("/","",str_replace("..","",e_PLUGIN))."/");
	$plugpath = e_PLUGIN.str_replace(basename(e_SELF),"",str_replace($plugindir,"",strstr(e_SELF,$plugindir)))."admin_menu.php";
	if(file_exists($plugpath)){
		if (!$parm) {
			@require_once($plugpath);
		} else {
			return 'pre';
		}
	}
}
