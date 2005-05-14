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

//Manage Existing Content (no category selected)
if(!$type){
	$text = CONTENT_ADMIN_HELP_ITEM_1;
}

//Manage Existing Content (category selected)
if($type == "type" && is_numeric($type_id)){
	if(!$action || ($action == "c" && $sub_action) ){
		$text = CONTENT_ADMIN_HELP_ITEM_2;
	}
	
	if($action == "create"){
		//Create New Content (no category selected)
		if(!$sub_action){
			$text = CONTENT_ADMIN_HELP_ITEMCREATE_1;

		//Create New Content (category selected)
		}elseif(is_numeric($sub_action)){
			$text = CONTENT_ADMIN_HELP_ITEMCREATE_2;

		//Manage Existing Content (edit page)
		}elseif($sub_action == "edit"){
			$text = CONTENT_ADMIN_HELP_ITEMEDIT_1;
		}
	}

	if($action == "cat"){
		//Manage Existing Categories
		if(!$sub_action){
			$text = CONTENT_ADMIN_HELP_CAT_1;
			//Manage Existing Categories (show contentmanager link)
			if(getperms("0")){
				$text .= CONTENT_ADMIN_HELP_CAT_2;
			}

		//Create New Category
		}elseif($sub_action == "create"){
			//Create New Category (no category selected)
			if(!$id){
				$text = CONTENT_ADMIN_HELP_CAT_3;

			//Create New Category (category selected)
			}elseif(is_numeric($id)){
				$text = CONTENT_ADMIN_HELP_CAT_4;
			}

		//Manage Existing Categories (edit page)
		}elseif($sub_action == "edit"){
			$text = CONTENT_ADMIN_HELP_CAT_5;

		//Manage Existing Categories (options page)
		}elseif($sub_action == "options"){
			$text = CONTENT_ADMIN_HELP_CAT_6;

		//Manage Existing Categories (contentmanager page)
		}elseif($sub_action == "contentmanager"){
			$text = CONTENT_ADMIN_HELP_CAT_7;
		}

	}

	//Submitted Content Items
	if($action == "sa"){
		$text = CONTENT_ADMIN_HELP_SUBMIT_1;
	}

	//Manage Order
	if($action == "order"){

		//Manage Order (category order)
		if($sub_action == "cat"){
			$text = CONTENT_ADMIN_HELP_ORDER_1;

		//Manage Order (items order in category)
		}elseif($sub_action == "item"){
			$text = CONTENT_ADMIN_HELP_ORDER_2;

		//Manage Order (global items order)
		}elseif($sub_action == "all"){
			$text = CONTENT_ADMIN_HELP_ORDER_3;
		}
	}

}

$ns -> tablerender(CONTENT_ADMIN_HELP_1, $text);

?>