if (ADMIN) {
	global $ns, $pref;
	ob_start();
	//Show upper_right menu if the function exists
	$tmp = explode(".",e_PAGE);
	$adminmenu_func = $tmp[0]."_adminmenu";
	if(function_exists($adminmenu_func)){
		if (!$parm) {
			call_user_func($adminmenu_func,$adminmenu_parms);
		} else {
			return 'pre';
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
	$ret = ob_get_contents();
	ob_end_clean();
	return $ret;
}
