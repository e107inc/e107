<?php
function parse_menu($match,$referrer){
	if($referrer != 'admin'){return "";}
	global $pref,$ns,$menu_pref,$sql,$aj;
	$m = explode(".",$match[1]);
	$fname = e_PLUGIN."{$m[2]}/{$m[2]}.php";
   @include_once(e_PLUGIN."{$m[2]}/languages/".e_LANGUAGE.".php");
	@include_once(e_PLUGIN."{$m[2]}/languages/English.php");
	if(file_exists($fname)){
		ob_end_flush();
		ob_start();
		require($fname);
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
}
?>