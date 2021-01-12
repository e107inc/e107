global $eMenuActive,$linkstyle;
// PHP warnings FIX - Deprecated menus, $eMenuActive not set
//if(!empty($parm) && is_string($parm) && !in_array('edynamic_menu',$eMenuActive) && !in_array('tree_menu',$eMenuActive) && !in_array('q_tree_menu',$eMenuActive))


if(is_string($parm))
{
    $tmp = explode(":", $parm);
	$linktype = $tmp[0];
	$cat = (isset($tmp[1])) ? $tmp[1] : 1;
	$css_class = (isset($tmp[2])) ? $tmp[2] : false;
	if(!defined('LINKDISPLAY')) {
		define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
	}
		
	$sitelinks = e107::getSitelinks();
	
	if(function_exists("linkstyle")){
    	$style = linkstyle($linkstyle);
	}else{
		$style="";
	}
	return $sitelinks->get($cat, $style, $css_class);
}


