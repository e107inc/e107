global $sql, $override;
global $eMenuActive;

if(!in_array('edynamic_menu',$eMenuActive) && !in_array('tree_menu',$eMenuActive)) {
	$linktype = $parm;
	if(!defined('LINKDISPLAY')) {
		define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
	}
	if($override_function = $override -> override_check('sitelinks')){
		call_user_func($override_function);
	} else {	
		require_once(e_HANDLER."sitelinks_class.php");
		$sitelinks = new sitelinks;
		return $sitelinks->get();
	}
	return "";
}
