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
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/content_manager.php,v $
|		$Revision: 1.4 $
|		$Date: 2005-02-12 09:52:10 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

$lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN.'content/languages/English/lan_content.php');

require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_PLUGIN."content/handlers/content_class.php");
$aa = new content;
require_once(e_PLUGIN."content/handlers/content_db_class.php");
$adb = new contentdb;
require_once(e_PLUGIN."content/handlers/content_form_class.php");
$aform = new contentform;
global $tp;


$deltest = array_flip($_POST);

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
		$type = $tmp[0];
		$type_id = $tmp[1];
        $action = $tmp[2];
        $sub_action = $tmp[3];
        $id = $tmp[4];
        unset($tmp);
}
if(!isset($type)){ $type = "type"; }
if(!isset($type_id)){ $type_id = "0"; }

if(preg_match("#(.*?)_delete_(\d+)#",$deltest[$tp->toJS("delete")],$matches)){
        $delete = $matches[1];
        $del_id = $matches[2];
}

// ##### DB ---------------------------------------------------------------------------------------
$content_pref = $aa -> getContentPref(($type_id != "0" ? $type_id : "0"));

require_once(HEADERF);

if(isset($_POST['create_content'])){
        if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
				$adb -> dbContentCreate("contentmanager");
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
        }
}

If(isset($_POST['update_content'])){
        if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
				$adb -> dbContentUpdate("contentmanager");
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
		}
}

if($delete == 'content'){
		if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
			$message = CONTENT_ADMIN_ITEM_LAN_3;
			$e107cache->clear("content");
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

		$ne_day = $_POST['ne_day'];
		$ne_month = $_POST['ne_month'];
		$ne_year = $_POST['ne_year'];	
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
		$custom["content_custom_score"] = $_POST['content_score'];
		$custom["content_custom_meta"] = $_POST['content_meta'];
		for($i=0;$i<$content_pref["content_admin_custom_number_{$type_id}"];$i++){
			$keystring = $_POST["content_custom_key_{$i}"];
			$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
		}

		$content_icon = $_FILES['file_userfile1'][name][0];								//won't work, cause file isn't upoaded
		for($i=0;$i<$content_pref["content_admin_files_number_{$type_id}"];$i++){
			$content_files{$i} = $_POST['content_files{$i}'];							//won't work, cause file isn't upoaded
		}
		for($i=0;$i<$content_pref["content_admin_images_number_{$type_id}"];$i++){
			$content_images{$i} = $_POST['content_images{$i}'];							//won't work, cause file isn't upoaded
		}

}

if(IsSet($message)){
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!e_QUERY){
			if(USERID){
				$aform -> show_contentmanager("edit", USERID, USERNAME);
				require_once(FOOTERF);
				exit;
			}else{
				header("location:".e_PLUGIN."content/content.php"); exit;
			}
}else{

	if($type == "c"){
			$message = CONTENT_ADMIN_ITEM_LAN_1."<br /><br />".CONTENT_ADMIN_ITEM_LAN_55;
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(FOOTERF);
			exit;

	}elseif($type == "u"){
			$message = CONTENT_ADMIN_ITEM_LAN_2."<br /><br />".CONTENT_ADMIN_ITEM_LAN_55;
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(FOOTERF);
			exit;

	}elseif($type=="type" && is_numeric($type_id)){
		if(!$action || ($action == "c" && $sub_action)){
			if($type_id == "0"){
					header("location:".e_SELF); exit;
			}else{
					$aform -> show_contentmanager("edit", USERID, USERNAME);
					$aform -> show_content_manage("contentmanager", USERID, USERNAME);
			}
		}
		if(is_numeric($action)){
			if(!$sub_action){
					$aform -> show_contentmanager("edit", USERID, USERNAME);
					$aform -> show_content_manage("contentmanager", USERID, USERNAME);
			}elseif($sub_action == "create"){
					$aform -> show_contentmanager("edit", USERID, USERNAME);
					$aform -> show_content_create("contentmanager", USERID, USERNAME);
			}else{
					header("location:".e_SELF); exit;
			}

		}elseif($action == "create"){
			if(!$sub_action){
					header("location:".e_SELF); exit;
			}else{
					$aform -> show_contentmanager("edit", USERID, USERNAME);
					$aform -> show_content_create("contentmanager", USERID, USERNAME);
			}
		}

	}else{
			header("location:".e_SELF); exit;
	}
}






require_once(FOOTERF);

function headerjs(){
	global $tp;
	$script = "<script type=\"text/javascript\">
	function addtext2(sc){
	        document.getElementById('dataform').cat_icon.value = sc;
	}
	</script>\n";

	$script .= "<script type=\"text/javascript\">
	function confirm_(mode, content_heading, content_id){
			if(mode == 'cat'){
					return confirm(\"".$tp->toJS(CONTENT_ADMIN_JS_LAN_0)." [".CONTENT_ADMIN_JS_LAN_6." \" + content_id + \": \" + content_heading + \"]\");
			}else{
					return confirm(\"".$tp->toJS(CONTENT_ADMIN_JS_LAN_1)." [".CONTENT_ADMIN_JS_LAN_6." \" + content_id + \": \" + content_heading + \"]\");
			}
	}

	function confirm2_(mode, number, name){
		if(mode == 'image'){
			var x=confirm(\"".CONTENT_ADMIN_JS_LAN_2." [".CONTENT_ADMIN_JS_LAN_4.": \" + name + \"] \");
		}else{
			var x=confirm(\"".CONTENT_ADMIN_JS_LAN_2." [".CONTENT_ADMIN_JS_LAN_5.": \" + name + \"] \");
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
			}else{
				for (i = 0; i < imagemax; i++){
					if(number == i){
						document.getElementById('content_files' + i).value = '';
					}
				}
			}
		}
	}

	</script>";

	return $script;
}

?>