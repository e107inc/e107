global $eMenuActive;
if(!in_array('edynamic_menu',$eMenuActive) && !in_array('tree_menu',$eMenuActive)) {
	$linktype = $parm;
	if(!defined('LINKDISPLAY')) {
		define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
	}
	require_once(e_HANDLER."sitelinks_class.php");
	$sitelinks = new sitelinks;
	return $sitelinks->get();
}
