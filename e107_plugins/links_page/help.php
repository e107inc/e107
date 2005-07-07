<?php
$lan_file = $plugindir."languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : $plugindir."languages/English.php");

if(!e_QUERY){
	$text = LAN_ADMIN_HELP_1;
}else{
	if(e_QUERY){
		$qs = explode(".", e_QUERY);

		if(is_numeric($qs[0])){
			$from = array_shift($qs);
		}else{
			$from = "0";
		}
	}

	//##### LINK --------------------------------------------------
		//manage Link items
		if($qs[0] == "link" && !isset($qs[1]) ){
			$text = LAN_ADMIN_HELP_3;
		//edit
		}elseif($qs[0] == "link" && $qs[1] == "edit" && is_numeric($qs[2]) ){
			$text = LAN_ADMIN_HELP_9;
		//view links in cat
		}elseif($qs[0] == "link" && $qs[1] == "view" && (is_numeric($qs[2]) || $qs[2] == "all") ){
			$text = LAN_ADMIN_HELP_8;
		//create
		}elseif($qs[0] == "link" && $qs[1] == "create" && !isset($qs[2])){
			$text = LAN_ADMIN_HELP_4;
		//create/post submitted
		}elseif($qs[0] == "link" && $qs[1] == "sn" && is_numeric($qs[2])){
			$text = LAN_ADMIN_HELP_10;

	//##### SUBMITTED --------------------------------------------------
		}elseif($qs[0] == "sn" && !isset($qs[1]) ){
			$text = LAN_ADMIN_HELP_5;

	//##### OPTION --------------------------------------------------
		}elseif($qs[0] == "opt" && !isset($qs[1]) ){
			$text = LAN_ADMIN_HELP_6;

	//##### CATEGORY --------------------------------------------------
		}elseif($qs[0] == "cat" && $qs[1] == "create" ){
			$text = LAN_ADMIN_HELP_2;
		}elseif($qs[0] == "cat" && $qs[1] == "edit" && is_numeric($qs[2]) ){
			$text = LAN_ADMIN_HELP_7;
		}
}
$ns -> tablerender(LAN_ADMIN_HELP_0, $text);

?>