<?php

$lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content_help.php';
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN.'content/languages/English/lan_content_help.php');

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $type = $tmp[0];
		$type_id = $tmp[1];
		$action = $tmp[2];
        $sub_action = $tmp[3];
        $id = $tmp[4];
        unset($tmp);
}

//main page
if(!$type){
	$text = CONTENT_ADMIN_HELP_LAN_0;
}

//create page
if($type == "type" && is_numeric($type_id)){
	if(!$action || ($action == "c" && $sub_action)){
		$text = CONTENT_ADMIN_HELP_LAN_1;
		if($action == "c" && $sub_action){
			$text .= CONTENT_ADMIN_HELP_LAN_2;
		}
		$text .= CONTENT_ADMIN_HELP_LAN_3;
	}

	//create page
	if($action == "create"){
		if(!$sub_action){
			if($type_id == "0"){
				$text = CONTENT_ADMIN_HELP_LAN_4;
			}else{
				$text = CONTENT_ADMIN_HELP_LAN_5;
			}
		}else{
			$text = CONTENT_ADMIN_HELP_LAN_6;
		}
	}

	//category page
	if($action == "cat"){
		if($type_id == "0" && $sub_action == "create"){
			$text = CONTENT_ADMIN_HELP_LAN_7;

		}elseif($type_id != "0" && $sub_action == "create"){
			$text = CONTENT_ADMIN_HELP_LAN_8;

		}elseif($type_id == "0" && ($sub_action == "manage" || !$sub_action)){
			$text = CONTENT_ADMIN_HELP_LAN_9;

		}elseif($type_id != "0" && ($sub_action == "manage" || !$sub_action)){
			$text = CONTENT_ADMIN_HELP_LAN_10;

			if(getperms("0")){
				$text .= CONTENT_ADMIN_HELP_LAN_15;
			}

		}elseif($type_id == "0" && $sub_action == "edit"){

		}elseif($type_id != "0" && $sub_action == "edit"){
			$text = CONTENT_ADMIN_HELP_LAN_11;

		}elseif($type_id != "0" && $sub_action == "options"){
			$text = CONTENT_ADMIN_HELP_LAN_12;

		}elseif($type_id != "0" && $sub_action == "contentmanager"){
			$text = CONTENT_ADMIN_HELP_LAN_16;

		}elseif(!$sub_action){
		}
	}

	if($action == "sa"){
			$text = CONTENT_ADMIN_HELP_LAN_13;
	}

	if($action == "order"){
			if($type_id == "0" || !$sub_action){
				$text = CONTENT_ADMIN_HELP_LAN_0;
			}elseif($type_id != "0" && $sub_action == "cat"){
				$text = CONTENT_ADMIN_HELP_LAN_18;
			}elseif($type_id != "0" && $sub_action && $sub_action != "cat"){
				$text = CONTENT_ADMIN_HELP_LAN_19;
			}
	}

}

$ns -> tablerender(CONTENT_ADMIN_HELP_LAN_14, $text);

?>