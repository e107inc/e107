<?php
global $plugindir;
require_once($plugindir."handlers/content_defines.php");
$lan_file = $plugindir.'languages/'.e_LANGUAGE.'/lan_content_help.php';
include_once(file_exists($lan_file) ? $lan_file : $plugindir.'languages/English/lan_content_help.php');


//$arrhide = array("creation", "submission", "paththeme", "general", "contentmanager", "menu", "recentpages", "catpages", "contentpages", "authorpage", "archivepage", "toppage", "scorepage");


if(!e_QUERY){
	$text = CONTENT_ADMIN_HELP_ITEM_1;
}else{
	$qs = explode(".", e_QUERY);

	//##### CONTENT --------------------------------------------------
		//manage content items
		if($qs[0] == "content" && is_numeric($qs[1]) ){
			$text = CONTENT_ADMIN_HELP_ITEM_2;

		//edit content item
		}elseif($qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]) ){
			$text = CONTENT_ADMIN_HELP_ITEMEDIT_1;

		//post submitted content item
		}elseif($qs[0] == "content" && $qs[1] == "sa" && is_numeric($qs[2]) ){
			$text = CONTENT_ADMIN_HELP_ITEMCREATE_2;

		//create content item
		}elseif($qs[0] == "content" && $qs[1] == "create" ){
			//Create New Content (no category selected)
			if(!isset($qs[2])){
				$text = CONTENT_ADMIN_HELP_ITEMCREATE_1;

			//Create New Content (category selected)
			}elseif(is_numeric($qs[2])){
				$text = CONTENT_ADMIN_HELP_ITEMCREATE_2;
			}

	//##### ORDER --------------------------------------------------
		//order : view categories
		}elseif($qs[0] == "order" && (!isset($qs[1]) || $qs[1] == "inc" || $qs[1] == "dec")){
			$text = CONTENT_ADMIN_HELP_ORDER_1;

		//order global items of parent='2'
		}elseif($qs[0] == "order" && is_numeric($qs[1]) && (!isset($qs[2]) || $qs[2] == "inc" || $qs[2] == "dec") ){
			$text = CONTENT_ADMIN_HELP_ORDER_3;

		//order items with parent=2 or category='5'
		}elseif($qs[0] == "order" && is_numeric($qs[1]) && is_numeric($qs[2]) && (!isset($qs[3]) || $qs[3] == "inc" || $qs[3] == "dec") ){
			$text = CONTENT_ADMIN_HELP_ORDER_2;

	//##### SUBMITTED --------------------------------------------------
		}elseif($qs[0] == "submitted" && !isset($qs[1]) ){
			$text = CONTENT_ADMIN_HELP_SUBMIT_1;

	//##### OPTION --------------------------------------------------
		//option: mainpage
		}elseif($qs[0] == "option" && !isset($qs[1]) ){
			$text = CONTENT_ADMIN_HELP_OPTION_1;

		//option: with main parent selected, show all options
		}elseif($qs[0] == "option" && isset($qs[1]) && (is_numeric($qs[1]) || $qs[1] == "default") ){
			//$text = CONTENT_ADMIN_HELP_OPTION_2;
			$text .= "
			<div id='creationhelp'>".CONTENT_ADMIN_HELP_OPTION_DIV_1."</div>
			<div id='submissionhelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_2."</div>
			<div id='paththemehelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_3."</div>
			<div id='generalhelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_4."</div>
			<div id='contentmanagerhelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_5."</div>
			<div id='menuhelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_6."</div>
			<div id='recentpageshelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_7."</div>
			<div id='catpageshelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_8."</div>
			<div id='contentpageshelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_9."</div>
			<div id='authorpagehelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_10."</div>
			<div id='archivepagehelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_11."</div>
			<div id='toppagehelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_12."</div>
			<div id='scorepagehelp' style='display:none;'>".CONTENT_ADMIN_HELP_OPTION_DIV_13."</div>
			";

	//##### CATEGORY --------------------------------------------------
		//category content manager : choose category
		}elseif($qs[0] == "manager" && !isset($qs[1]) ){
			$text = CONTENT_ADMIN_HELP_MANAGER_1;

		//category content manager : view contentmanager
		}elseif($qs[0] == "manager" && isset($qs[1]) && is_numeric($qs[1]) ){
			$text = CONTENT_ADMIN_HELP_MANAGER_2;

		//overview all categories
		}elseif($qs[0] == "cat" && !isset($qs[1]) ){
			$text = CONTENT_ADMIN_HELP_CAT_1;

		//create category
		}elseif($qs[0] == "cat" && $qs[1] == "create" ){
			$text = CONTENT_ADMIN_HELP_CAT_2;

		//edit category
		}elseif($qs[0] == "cat" && $qs[1] == "edit" && is_numeric($qs[2]) ){
			$text = CONTENT_ADMIN_HELP_CAT_3;

		}
}
$ns -> tablerender(CONTENT_ADMIN_HELP_1, $text);

?>