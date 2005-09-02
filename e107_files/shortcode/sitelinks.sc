global $eMenuActive,$linkstyle;
if(!in_array('edynamic_menu',$eMenuActive) && !in_array('tree_menu',$eMenuActive) && !in_array('q_tree_menu',$eMenuActive)) {
    $tmp = explode(":",$parm);
	$linktype = $tmp[0];
	$cat = ($tmp[1]) ? $tmp[1] : 1;
	if(!defined('LINKDISPLAY')) {
		define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
	}
	require_once(e_HANDLER."sitelinks_class.php");
	$sitelinks = new sitelinks;
	if(function_exists("linkstyle")){
    	$style = linkstyle($linkstyle);
	}else{
		$style="";
	}
	return $sitelinks->get($cat,$style);
}


