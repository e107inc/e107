<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/admin_content_config.php,v $
|		$Revision: 1.13 $
|		$Date: 2005-02-10 23:03:35 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

if(!getperms("J") && !getperms("K") && !getperms("L")){header("location:".e_BASE."index.php"); exit; }
$e_sub_cat = 'content';

$lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN.'content/languages/English/lan_content.php');

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."userclass_class.php");
require_once(e_PLUGIN."content/handlers/content_class.php");
$aa = new content;
require_once(e_PLUGIN."content/handlers/content_db_class.php");
$adb = new contentdb;
require_once(e_PLUGIN."content/handlers/content_form_class.php");
$aform = new contentform;
global $tp;

if(e_QUERY){
        $tmp		=	explode(".", e_QUERY);
		$type		=	$tmp[0];
		$type_id	=	$tmp[1];
        $action		=	$tmp[2];
        $sub_action	=	$tmp[3];
        $id			=	$tmp[4];
		$id2		=	$tmp[5];
        unset($tmp);
}
if(!isset($type)){ $type = "type"; }
if(!isset($type_id)){ $type_id = "0"; }

$deltest = array_flip($_POST);
if(preg_match("#(.*?)_delete_(\d+)#",$deltest[$tp->toJS("delete")],$matches)){
        $delete = $matches[1];
        $del_id = $matches[2];
}

// ##### DB ---------------------------------------------------------------------------------------
$content_pref = $aa -> getContentPref(($type_id != "0" ? $type_id : "0"));

if(isset($_POST['updateoptions'])){
		$content_pref = $aa -> UpdateContentPref($_POST, $_POST['options_type']);
		$message = CONTENT_ADMIN_CAT_LAN_22."<br /><br />";
		$message .= $aa -> CreateParentMenu($_POST['options_type']);
}

$content_cat_icon_path_large	=	$aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
$content_cat_icon_path_small	=	$aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
$content_icon_path				=	$aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
$content_image_path				=	$aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
$content_file_path				=	$aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

if(isset($_POST['create_category'])){
		 if($_POST['cat_heading']){
				$adb -> dbCategoryCreate("admin");
		}else{
				$message = CONTENT_ADMIN_ITEM_LAN_0;
				$content_parent = $_POST['parent'];
				$content_heading = $_POST['cat_heading'];
				$content_subheading = $_POST['cat_subheading'];				
				$content_text = $_POST['cat_text'];
				$content_icon = $_POST['cat_icon'];
				$content_comment = $_POST['cat_comment'];
				$content_rate = $_POST['cat_rate'];
				$content_pe = $_POST['cat_pe'];
				$content_class = $_POST['cat_class'];
				$ne_day = $_POST['ne_day'];
				$ne_month = $_POST['ne_month'];
				$ne_year = $_POST['ne_year'];
				$end_day = $_POST['end_day'];
				$end_month = $_POST['end_month'];
				$end_year = $_POST['end_year'];
		}
}

if(isset($_POST['update_category'])){
		if($_POST['cat_heading']){
				$adb -> dbCategoryUpdate("admin");
		}else{
				$message = CONTENT_ADMIN_ITEM_LAN_0;
				$content_parent = $_POST['parent'];
				$content_heading = $_POST['cat_heading'];
				$content_subheading = $_POST['cat_subheading'];				
				$content_text = $_POST['cat_text'];
				$content_icon = $_POST['cat_icon'];
				$content_comment = $_POST['cat_comment'];
				$content_rate = $_POST['cat_rate'];
				$content_pe = $_POST['cat_pe'];
				$content_class = $_POST['cat_class'];
				$ne_day = $_POST['ne_day'];
				$ne_month = $_POST['ne_month'];
				$ne_year = $_POST['ne_year'];
				$end_day = $_POST['end_day'];
				$end_month = $_POST['end_month'];
				$end_year = $_POST['end_year'];
		}
}
if(isset($_POST['assign_admins'])){
		$message = $adb -> dbAssignAdmins("admin");
}

if(isset($_POST['create_content'])){
        if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
				$adb -> dbContentCreate("admin");
        }else{
                $message = CONTENT_ADMIN_ITEM_LAN_0;
				$content_heading = $_POST['content_heading'];
				$content_subheading = $_POST['content_subheading'];
				$content_summary = $_POST['content_summary'];
				$content_text = $_POST['content_text'];
				$content_icon = $_POST['content_icon'];
				$content_file = $_POST['content_file'];
				$content_comment = $_POST['content_comment'];
				$content_rate = $_POST['content_rate'];
				$content_pe = $_POST['content_pe'];
				$content_class = $_POST['content_class'];
				$ne_day = $_POST['ne_day'];
				$ne_month = $_POST['ne_month'];
				$ne_year = $_POST['ne_year'];
				$end_day = $_POST['end_day'];
				$end_month = $_POST['end_month'];
				$end_year = $_POST['end_year'];
				$custom["content_custom_score"] = $_POST['content_score'];
				$custom["content_custom_meta"] = $_POST['content_meta'];
				for($i=0;$i<$content_pref["content_admin_custom_number_{$type_id}"];$i++){
					$keystring = $_POST["content_custom_key_{$i}"];
					$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
				}
		}
}

If(isset($_POST['update_content'])){
        if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
				$adb -> dbContentUpdate("admin");
		}else{
				$message = CONTENT_ADMIN_ITEM_LAN_0;
				$content_heading = $_POST['content_heading'];
				$content_subheading = $_POST['content_subheading'];
				$content_summary = $_POST['content_summary'];
				$content_text = $_POST['content_text'];
				$content_icon = $_POST['content_icon'];
				$content_comment = $_POST['content_comment'];
				$content_rate = $_POST['content_rate'];
				$content_pe = $_POST['content_pe'];
				$content_class = $_POST['content_class'];
				$ne_day = $_POST['ne_day'];
				$ne_month = $_POST['ne_month'];
				$ne_year = $_POST['ne_year'];
				$end_day = $_POST['end_day'];
				$end_month = $_POST['end_month'];
				$end_year = $_POST['end_year'];
				$custom["content_custom_score"] = $_POST['content_score'];
				$custom["content_custom_meta"] = $_POST['content_meta'];
				for($i=0;$i<$content_pref["content_admin_custom_number_{$type_id}"];$i++){
					$keystring = $_POST["content_custom_key_{$i}"];
					$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
				}
		}
}

if($delete == 'cat'){

		$sql -> db_Select($plugintable, "content_parent", "content_id = '$del_id' ");
		list($content_parent) = $sql -> db_Fetch();

		if($content_parent == "0"){
			$check = $del_id.".".$del_id;
		}else{
			$tmp = explode(".", $content_parent);
			$check = $tmp[1].".".substr($content_parent,strlen($tmp[1])+1).".".$del_id;
		}

		//check if subcats present
		if($sql -> db_Select($plugintable, "content_parent", "content_id != '".$del_id."' AND LEFT(content_parent,".(strlen($content_parent)).") = '".$content_parent."' ")){
			//subcategories found don't delete
			$checkermsg .= CONTENT_ADMIN_CAT_LAN_36."<br />";
			$checksubcat = TRUE;
		}else{
			$checkermsg .= CONTENT_ADMIN_CAT_LAN_39."<br />";
			$checksubcat = FALSE;
		}

		//check if items present
		if($sql -> db_Select($plugintable, "content_parent", "LEFT(content_parent,".(strlen($content_parent)).") = '".$check."' OR content_parent = '".$check."' ")){
			//items found, don't delete
			$checkermsg .= CONTENT_ADMIN_CAT_LAN_37."<br />";
			$checkitems = TRUE;
		}else{
			$checkermsg .= CONTENT_ADMIN_CAT_LAN_38."<br />";
			$checkitems = FALSE;
		}
	
		if($checksubcat == FALSE && $checkitems == FALSE){
			if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
				$message = CONTENT_ADMIN_CAT_LAN_23."<br />";
			}
		}else{
			$message = $checkermsg;
		}
}

if($delete == 'content'){
		if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
			$e107cache->clear("content");
			$message = CONTENT_ADMIN_ITEM_LAN_3;
		}
}

if(isset($_POST['preview'])){
		$content_heading = $tp -> post_toHTML($_POST['content_heading']);
		$content_subheading = $tp -> post_toHTML($_POST['content_subheading']);
		$content_summary = $tp -> post_toHTML($_POST['content_summary']);
		$content_text = $tp -> post_toHTML($_POST['content_text']);

		$text = "
		<div style='text-align:center'>
		<table class='fborder' style='".ADMIN_WIDTH."' border='0'>
		<tr><td>".$content_heading."</td></tr>
		<tr><td>".$content_subheading."</td></tr>
		<tr><td>".$content_summary."</td></tr>
		<tr><td>".$content_text."</td></tr>
		</table>
		</div>";
			  
		$ns -> tablerender($content_heading, $text);

		$content_authorname = $_POST['content_authorname'];
		$content_authoremail = $_POST['content_authoremail'];
		$content_parent = $_POST['parent'];
		$content_heading = $tp -> post_toForm($_POST['content_heading']);
		$content_subheading = $tp -> post_toForm($_POST['content_subheading']);
		$content_summary = $tp -> post_toForm($_POST['content_summary']);
		$content_text = $tp -> post_toForm($_POST['content_text']);
		$content_comment = $_POST['content_comment'];
		$content_rate = $_POST['content_rate'];
		$content_pe = $_POST['content_pe'];
		$content_class = $_POST['content_class'];
		$ne_day = $_POST['ne_day'];
		$ne_month = $_POST['ne_month'];
		$ne_year = $_POST['ne_year'];
		$end_day = $_POST['end_day'];
		$end_month = $_POST['end_month'];
		$end_year = $_POST['end_year'];
		$custom["content_custom_score"] = $_POST['content_score'];
		$custom["content_custom_meta"] = $_POST['content_meta'];
		for($i=0;$i<$content_pref["content_admin_custom_number_{$type_id}"];$i++){
			$keystring = $_POST["content_custom_key_{$i}"];
			$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
		}

		$content_icon = $_FILES['file_userfile1']['name'][0];							//won't work, cause file isn't upoaded
		for($i=0;$i<$content_pref["content_admin_files_number_{$type_id}"];$i++){
			$content_files{$i} = $_POST['content_files{$i}'];							//won't work, cause file isn't upoaded
		}
		for($i=0;$i<$content_pref["content_admin_images_number_{$type_id}"];$i++){
			$content_images{$i} = $_POST['content_images{$i}'];							//won't work, cause file isn't upoaded
		}
}

if(isset($_POST['update_order'])){
		$message = $adb -> dbSetOrder("all", $_POST['order']);
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
// ##### End --------------------------------------------------------------------------------------

if(!e_QUERY){																//show main categories
		$aform -> show_main_parent("edit");
		require_once(e_ADMIN."footer.php");
		exit;
}else{
	if($type=="type" && is_numeric($type_id)){
		if(!$action || ($action == "c" && $sub_action)){
			if($type_id == "0"){
					header("location:".e_SELF); exit;
			}else{															//item; overview by category
					$aform -> show_main_parent("edit");
					$aform -> show_content_manage("admin");
			}
		}

		if($action == "create"){											//item
			if($sub_action == "cc"){										//item; create redirect
						$message = CONTENT_ADMIN_ITEM_LAN_1;
						$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
						require_once(e_ADMIN."footer.php");
						exit;
			}elseif(!$sub_action){
				if($type_id == "0"){										//item; create; show main categories
						$aform -> show_main_parent("create");
						require_once(e_ADMIN."footer.php");
						exit;
				}else{														//item; create form; use selected main category
						$aform -> show_main_parent("create");
						$aform -> show_content_create("admin");
				}
			}elseif(($sub_action == "edit" || $sub_action == "sa")){
				if(!is_numeric($id)){
						header("location:".e_SELF."?type.".$type_id); exit;
				}else{														//item; edit form
					if($id2 == "cu"){										//item; update redirect
						$message = CONTENT_ADMIN_ITEM_LAN_2;
						$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
						require_once(e_ADMIN."footer.php");
						exit;
					}
					$aform -> show_main_parent("edit");
					$aform -> show_content_create("admin","","");
				}
			}else{
					header("location:".e_SELF."?type.".$type_id); exit;
			}
		}

		if($action == "sa"){												//submit; overview content items
			$aform -> show_content_submitted("admin");
		}

		if($action == "order"){
			if(!$sub_action || $type_id == "0"){
						$aform -> show_main_parent("order");							//show main parents for order selection
						require_once(e_ADMIN."footer.php");
						exit;

			//category (category order)
			}elseif($sub_action == "cat" && $type_id != "0" && (!$id || substr($id,0,3) == "inc" || substr($id,0,3) == "dec") ){
						if($sub_action == "cat" && substr($id,0,3) == "inc"){			//increase order
							$adb -> dbSetOrder("inc", "cc-".substr($id,4));
						}elseif($sub_action == "cat" && substr($id,0,3) == "dec"){		//decrease order
							$adb -> dbSetOrder("dec", "cc-".substr($id,4));
						}
						$aform -> show_main_parent("order");							//show main parents for order selection
						$aform -> show_cat_order("admin");								//show categories from selected main parent

			//all items (global order)
			}elseif($sub_action == "all" && $type_id != "0" && (!$id || substr($id,0,3) == "inc" || substr($id,0,3) == "dec") ){
						if(substr($id,0,3) == "inc"){									//increase order
							$adb -> dbSetOrder("inc", "ai-".substr($id,4));
						}elseif(substr($id,0,3) == "dec"){								//decrease order
							$adb -> dbSetOrder("dec", "ai-".substr($id,4));
						}
						$aform -> show_main_parent("order");							//show main parents for order selection
						$aform -> show_content_order("admin", "allitem");					//show global content items order

			//items in category (category items order)
			}elseif($sub_action && $sub_action != "cat" && $sub_action != "all" && $type_id != "0" && (!$id || substr($id,0,3) == "inc" || substr($id,0,3) == "dec") ){
						if(substr($id,0,3) == "inc"){									//increase order
							$adb -> dbSetOrder("inc", "ci-".substr($id,4));
						}elseif(substr($id,0,3) == "dec"){								//decrease order
							$adb -> dbSetOrder("dec", "ci-".substr($id,4));
						}
						$aform -> show_main_parent("order");							//show main parents for order selection
						$aform -> show_content_order("admin", "catitem");							//show order of content items from selected category

			}else{
						header("location:".e_SELF."?type.".$type_id.".order"); exit;
			}
		}

		if($action == "cat"){												//category
			if(!$sub_action || $sub_action == "manage"){					//category; main parents
					$aform -> show_main_parent("editcat");
					if($type_id != "0"){
							$aform -> show_cat_manage("editcat");			//category; overview subcategories
					}
			}elseif($sub_action == "edit"){									//category; edit
				if(is_numeric($id)){										//category; edit form
					if($id2 != "" && $id2 == "pu"){										//category; update redirect
							$message = CONTENT_ADMIN_CAT_LAN_12;
							$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
							require_once(e_ADMIN."footer.php");
							exit;
					}
					$aform -> show_main_parent("editcat");
					$aform -> show_cat_create("admin");
					//$aform -> show_cat_manage("editcat");
				}else{
					header("location:".e_SELF."?type.".$type_id.".cat"); exit;
				}
			}elseif($sub_action == "create"){
				if($id && $id != "pc"){
						header("location:".e_SELF."?type.".$type_id.".cat.create"); exit;
				}else{														//category; create form
						if($id == "pc"){									//category; create redirect
								$message = CONTENT_ADMIN_CAT_LAN_11;
								if($type_id == "0"){ $message .= "<br /><br />".CONTENT_ADMIN_OPT_LAN_82; }
								$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
								require_once(e_ADMIN."footer.php");
								exit;
						}
						$aform -> show_main_parent("createcat");
						$aform -> show_cat_create("admin");
				}
			}elseif($sub_action == "options"){								//category; options
					$aform -> show_cat_options();

			}elseif($sub_action == "contentmanager"){						//category; contentmanager users for category
					if(!getperms("0")){
							header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
					}
					if(is_numeric($id)){
							$aform -> show_admin_contentmanager();
					}
			}else{
					header("location:".e_SELF."?type.0.cat"); exit;
			}

		}
	}
}
// ##### End --------------------------------------------------------------------------------------

// ##### Display options --------------------------------------------------------------------------
function admin_content_config_adminmenu(){

                global $sql, $plugintable;
				global $action, $sub_action, $type, $type_id;

				require_once(e_PLUGIN."content/handlers/content_class.php");
				$aa = new content;

				if(e_QUERY){
						$tmp		=	explode(".", e_QUERY);
						$type		=	$tmp[0];
						$type_id	=	$tmp[1];
						$action		=	$tmp[2];
						$sub_action	=	$tmp[3];
						$id			=	$tmp[4];
						$id2		=	$tmp[5];
						unset($tmp);
				}

				if($action == "cat"){
					if($sub_action == "create"){
						$act = $action.".".$sub_action;
					}elseif($sub_action == "edit" || $sub_action == "manage" || $sub_action == "options"){
						$act = $action.".manage";
					}
				}elseif($action == "create"){
					if(!$sub_action){
						$act = $action;
					}elseif($sub_action == "edit"){
						$act = "main";
					}
				}elseif($action == "c"){
					if(!$sub_action){
						$act = $action;
					}else{
						$act = $action.".".$sub_action;
					}
				}else{
					$act = $action;
				}

                if($act==""){$act="main";}

                $var['main']['text']=CONTENT_ADMIN_MENU_LAN_0;
                $var['main']['link']=e_SELF;

                $var['create']['text']=CONTENT_ADMIN_MENU_LAN_1;
                $var['create']['link']=e_SELF."?type.0.create";

                $var['cat.manage']['text']=CONTENT_ADMIN_MENU_LAN_2;
                $var['cat.manage']['link']=e_SELF."?type.0.cat.manage";

				$var['cat.create']['text']=CONTENT_ADMIN_MENU_LAN_3;
                $var['cat.create']['link']=e_SELF."?type.0.cat.create";

				$var['order']['text']=CONTENT_ADMIN_MENU_LAN_15;
                $var['order']['link']=e_SELF."?type.0.order";

                if($submittedcontents = $sql -> db_Count($plugintable, "(*)", "WHERE content_refer ='sa' ")){
                        $var['sa']['text']=CONTENT_ADMIN_MENU_LAN_4." (".$submittedcontents.")";
                        $var['sa']['link']=e_SELF."?type.0.sa";
                }

				show_admin_menu(CONTENT_ADMIN_MENU_LAN_6, $act,$var);

				if($sub_action == "options"){
					unset($var);
					$var=array();
					$var['creation']['text'] = CONTENT_ADMIN_MENU_LAN_7;
					$var['submission']['text'] = CONTENT_ADMIN_MENU_LAN_8;
					$var['paththeme']['text'] = CONTENT_ADMIN_MENU_LAN_9;
					$var['general']['text'] = CONTENT_ADMIN_MENU_LAN_10;
					$var['listpages']['text'] = CONTENT_ADMIN_MENU_LAN_11;
					$var['catpages']['text'] = CONTENT_ADMIN_MENU_LAN_12;
					$var['contentpages']['text'] = CONTENT_ADMIN_MENU_LAN_13;
					$var['menu']['text'] = CONTENT_ADMIN_MENU_LAN_14;
					

					$sql = new db;
					$category_total = $sql -> db_Select($plugintable, "content_heading", "content_id='".$type_id."' ");
					list($content_heading) = $sql -> db_Fetch();

					show_admin_menu(CONTENT_ADMIN_MENU_LAN_6.": ".$content_heading."", $act, $var, TRUE);
				
				}else{
						$sql2 = new db;
						if($category_total = $sql2 -> db_Select($plugintable, "content_id, content_heading", "content_parent='0' ")){
							while($row = $sql2 -> db_Fetch()){
								extract($row);
								unset($var);
								$var=array();
								$parentdetails2 = $aa -> getParent("", "", $content_id);
								$parentarray = $aa -> printParent($parentdetails2, "0", $content_id, "optionadminmenu");

								for($i=0;$i<count($parentarray);$i++){
									$var['c'.$parentarray[$i][3]]['text']=$parentarray[$i][1];
									$var['c'.$parentarray[$i][3]]['link']=e_SELF."?type.".$parentarray[$i][4].".c.{$parentarray[$i][3]}";
								}

								//$parentarray = $aa -> prefetchBreadCrumb($content_id, "", "admin");
								//print_r($parentarray);
								//for($i=0;$i<count($parentarray);$i++){
								//	//$parentarray[$i][3] = ($parentarray[$i][3] == "." ? $parentarray[$i][0].".".$parentarray[$i][0] : $parentarray[$i][0].".".$parentarray[$i][3]);
								//	$var['c'.$parentarray[$i][3]]['text']=$parentarray[$i][1];
								//	$var['c'.$parentarray[$i][3]]['link']=e_SELF."?type.".$parentarray[0][0].".c.{$parentarray[$i][3]}";
								//}


								show_admin_menu(CONTENT_ADMIN_MENU_LAN_5." : ".$content_heading."", 'c'.$sub_action, $var);
							}
						}
				}

}
// ##### End --------------------------------------------------------------------------------------


require_once(e_ADMIN."footer.php");

function headerjs(){
	global $tp;
	$script = "<script type=\"text/javascript\">

	function confirm2_(mode, number, name){
		if(mode == 'image'){
			var x=confirm(\"".CONTENT_ADMIN_JS_LAN_2." [".CONTENT_ADMIN_JS_LAN_4.": \" + name + \"] \");
		}
		if(mode == 'icon'){
			var x=confirm(\"".CONTENT_ADMIN_JS_LAN_7." [".CONTENT_ADMIN_JS_LAN_8.": \" + name + \"] \");
		}
		if(mode == 'file'){
			var x=confirm(\"".CONTENT_ADMIN_JS_LAN_3." [".CONTENT_ADMIN_JS_LAN_5.": \" + name + \"] \");
		}
		var i;
		var imagemax = 10;
		if(x){
			if(mode == 'image'){
				for (i = 0; i < imagemax; i++){
					if(number == i){
						document.getElementById('content_images' + i).value = '';
					}
				}
			}
			if(mode == 'icon'){
				document.getElementById('content_icon').value = '';
			}
			if(mode == 'file'){
				for (i = 0; i < imagemax; i++){
					if(number == i){
						document.getElementById('content_files' + i).value = '';
					}
				}
			}
		}
	}

	//<![CDATA[
	// Adapted from original:  Kathi O'Shea (Kathi.O'Shea@internet.com)
	function moveOver() {
		var boxLength = document.getElementById('assignclass2').length;
		var selectedItem = document.getElementById('assignclass1').selectedIndex;
		var selectedText = document.getElementById('assignclass1').options[selectedItem].text;
		var selectedValue = document.getElementById('assignclass1').options[selectedItem].value;
		var i;
		var isNew = true;
		var newvalues;
		if (boxLength != 0) {
			for (i = 0; i < boxLength; i++) {
				thisitem = document.getElementById('assignclass2').options[i].text;
				if (thisitem == selectedText) {
					isNew = false;
					break;
				}
			}
		}
		if (isNew) {
			newoption = new Option(selectedText, selectedValue, false, false);
			document.getElementById('assignclass2').options[boxLength] = newoption;
			document.getElementById('assignclass1').options[selectedItem].text = '';
			newvalues = selectedValue + ','
		}
		document.getElementById('assignclass1').selectedIndex=-1;
		document.getElementById('class_id').value = document.getElementById('class_id').value + newvalues
	}
		 
		 
	function removeMe() {
		var boxLength = document.getElementById('assignclass2').length;
		var boxLength2 = document.getElementById('assignclass1').length;
		arrSelected = new Array();
		var count = 0;
		for (i = 0; i < boxLength; i++) {
			if (document.getElementById('assignclass2').options[i].selected) {
				arrSelected[count] = document.getElementById('assignclass2').options[i].value;
				var valname = document.getElementById('assignclass2').options[i].text;
				for (j = 0; j < boxLength2; j++) {
					if (document.getElementById('assignclass1').options[j].value == arrSelected[count]){
						document.getElementById('assignclass1').options[j].text = valname;
					}
				}
				// document.getElementById('assignclass1').options[i].text = valname;
			}
			count++;
		}
		var x;
		for (i = 0; i < boxLength; i++) {
			for (x = 0; x < arrSelected.length; x++) {
				if (document.getElementById('assignclass2').options[i].value == arrSelected[x]) {
					document.getElementById('assignclass2').options[i] = null;
				}
			}
			boxLength = document.getElementById('assignclass2').length;
		}
		boxLengthvalues = document.getElementById('assignclass2').length;
		var newvalues = '';
		for (i = 0; i < boxLengthvalues; i++) {
			selectedValuevalues = document.getElementById('assignclass2').options[i].value;
			newvalues = newvalues + selectedValuevalues + ','
		}
		document.getElementById('class_id').value = newvalues
	}	
	
	</script>";

	return $script;
}

?>