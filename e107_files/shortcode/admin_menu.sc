if(ADMIN){
	global $ns, $pref;
	//Show upper_right menu if the function exists
	$tmp = explode(".",e_PAGE);
	$adminmenu_func = $tmp[0]."_adminmenu";
	if(function_exists($adminmenu_func)){
	        call_user_func($adminmenu_func,$adminmenu_parms);
	}
	$plugindir = (str_replace("/","",str_replace("..","",e_PLUGIN))."/");
	$plugpath = e_PLUGIN.str_replace(basename(e_SELF),"",str_replace($plugindir,"",strstr(e_SELF,$plugindir)))."admin_menu.php";
	if(file_exists($plugpath)){
	        @require_once($plugpath);
	}
}
