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
|		$Revision: 1.52 $
|		$Date: 2005-06-29 16:38:19 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

if(!getperms("P")){header("location:".e_BASE."index.php"); exit; }
$e_sub_cat = 'content';
$e_wysiwyg = "content_text,cat_text";
$plugindir = e_PLUGIN."content/";

$lan_file = $plugindir.'languages/'.e_LANGUAGE.'/lan_content_admin.php';
include_once(file_exists($lan_file) ? $lan_file : $plugindir.'languages/English/lan_content_admin.php');

$lan_file = $plugindir.'languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : $plugindir.'languages/English/lan_content.php');

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."userclass_class.php");
require_once($plugindir."handlers/content_class.php");
$aa = new content;
require_once($plugindir."handlers/content_db_class.php");
$adb = new contentdb;
require_once($plugindir."handlers/content_form_class.php");
$aform = new contentform;
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();

global $tp;
$deltest = array_flip($_POST);

// check query
if(e_QUERY){
	$qs = explode(".", e_QUERY);
}

if(isset($_POST['delete']))
{
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
}

// ##### DB ---------------------------------------------------------------------------------------

if(isset($delete) && $delete == 'cat'){

	$sql -> db_Select($plugintable, "content_id,content_parent", "content_id = '$del_id' ");
	list($content_id, $content_parent) = $sql -> db_Fetch();

	$checkarray = $aa -> getCategoryTree("", $content_id, TRUE);
	unset($agc);	//unset the globalised getCategoryTree array
	$checkvalidparent = implode(",", array_keys($checkarray));
	$checkqry = " content_parent REGEXP '".$aa -> CONTENTREGEXP($checkvalidparent)."' ";

	//check if subcats present
	if(count($array) > 1){
		//subcategories found don't delete
		$checkermsg .= CONTENT_ADMIN_CAT_LAN_36."<br />";
		$checksubcat = TRUE;
	}else{
		$checkermsg .= CONTENT_ADMIN_CAT_LAN_39."<br />";
		$checksubcat = FALSE;
	}

	//check if items present
	if($sql -> db_Count($plugintable, "(*)", "WHERE ".$checkqry." ")){
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

if(isset($delete) && $delete == 'content'){
	if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
		$e107cache->clear($plugintable);
		$message = CONTENT_ADMIN_ITEM_LAN_3;
	}
}

if(isset($delete) && $delete == 'submitted'){
	if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
		$e107cache->clear($plugintable);
		$message = CONTENT_ADMIN_SUBMIT_LAN_8;
	}
}

if(isset($_POST['updateoptions'])){
	$content_pref	= $aa -> UpdateContentPref($_POST, $_POST['options_type']);
	$message		= CONTENT_ADMIN_CAT_LAN_22."<br /><br />";
	if($_POST['options_type'] != "0"){
		$message		.= $aa -> CreateParentMenu($_POST['options_type']);
	}
	$e107cache->clear($plugintable);
}


//pre-upload a new category icon in the create/edit category form
if(isset($_POST['uploadcaticon'])){

	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");
	$pathiconlarge = $_POST['iconpathlarge'];
	$pathiconsmall = $_POST['iconpathsmall'];	
	$uploaded = file_upload($pathiconlarge);
	
	$icon = "";
	if($uploaded) {
		$icon = $uploaded[0]['name'];
		require_once(e_HANDLER."resize_handler.php");
		resize_image($pathiconlarge.$icon, $pathiconlarge.$icon, '48', "nocopy");
		resize_image($pathiconlarge.$icon, $pathiconsmall.$icon, '16', "copy");
		rename($pathiconsmall."thumb_".$icon , $pathiconsmall.$icon);
	}
	$message	= ($icon ? CONTENT_ADMIN_CAT_LAN_58 : CONTENT_ADMIN_CAT_LAN_59);
	
}



//icon, file, image upload
if(isset($_POST['uploadfile'])){
	
	if($_POST['uploadtype']){
		$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");
		$mainparent		= $aa -> getMainParent($_POST['parent']);
		$content_pref	= $aa -> getContentPref($mainparent);

		if($_POST['content_id']){
			$newpid = $_POST['content_id'];
		}else{
			$sql -> db_select("pcontent", "MAX(content_id) as aid", "content_id!='0' ");
			list($aid) = $sql -> db_Fetch();
			$newpid = $aid+1;
		}
	}

	//icon
	if($_POST['uploadtype'] == "1"){
		$pref['upload_storagetype'] = "1";
		$pathtmp		= $_POST['tmppathicon'];
		$uploaded		= file_upload($pathtmp);
		$new = "";
		if($uploaded){
			$uporg		= $uploaded[0]['name'];
			$resize		= (isset($content_pref["content_upload_icon_size_{$mainparent}"]) && $content_pref["content_upload_icon_size_{$mainparent}"] ? $content_pref["content_upload_icon_size_{$mainparent}"] : "100");
			if($uporg){
				$new = $newpid."_".$uporg;
				rename($pathtmp.$uporg, $pathtmp.$new);
				require_once(e_HANDLER."resize_handler.php");
				resize_image($pathtmp.$new, $pathtmp.$new, $resize, "nocopy");
			}
		}
		$message = ($new ? CONTENT_ADMIN_ITEM_LAN_106 : CONTENT_ADMIN_ITEM_LAN_107);

	//file
	}elseif($_POST['uploadtype'] == "2"){
		$pref['upload_storagetype'] = "1";
		$pathtmp		= $_POST['tmppathfile'];
		$uploaded		= file_upload($pathtmp);
		$new = "";
		if($uploaded){
			$uporg		= $uploaded[0]['name'];
			if($uporg){
				$new = $newpid."_".$uporg;
				rename($pathtmp.$uporg, $pathtmp.$new);
			}
		}
		$message = ($new ? CONTENT_ADMIN_ITEM_LAN_108 : CONTENT_ADMIN_ITEM_LAN_109);

	//image
	}elseif($_POST['uploadtype'] == "3"){
		$pref['upload_storagetype'] = "1";
		$pathtmp		= $_POST['tmppathimage'];
		$uploaded		= file_upload($pathtmp);
		$new = "";
		if($uploaded){
			$uporg		= $uploaded[0]['name'];
			$resize		= (isset($content_pref["content_upload_image_size_{$mainparent}"]) && $content_pref["content_upload_image_size_{$mainparent}"] ? $content_pref["content_upload_image_size_{$mainparent}"] : "500");
			$resizethumb	= (isset($content_pref["content_upload_image_size_thumb_{$mainparent}"]) && $content_pref["content_upload_image_size_thumb_{$mainparent}"] ? $content_pref["content_upload_image_size_thumb_{$mainparent}"] : "100");
			if($uporg){
				$new = $newpid."_".$uporg;
				rename($pathtmp.$uporg, $pathtmp.$new);
				require_once(e_HANDLER."resize_handler.php");
				resize_image($pathtmp.$new, $pathtmp.$new, $resizethumb, "copy");
				resize_image($pathtmp.$new, $pathtmp.$new, $resize, "nocopy");
			}
		}
		$message = ($new ? CONTENT_ADMIN_ITEM_LAN_110 : CONTENT_ADMIN_ITEM_LAN_111);
	}

}

if(isset($_POST['create_category'])){
	if($_POST['cat_heading'] && $_POST['parent'] != "none"){
		$adb -> dbCategoryCreate("admin");
	}else{
		$message	= CONTENT_ADMIN_ITEM_LAN_0;
	}
}

if(isset($_POST['update_category'])){
	if($_POST['cat_heading'] && $_POST['parent'] != "none"){
		$adb -> dbCategoryUpdate("admin");
	}else{
		$message	= CONTENT_ADMIN_ITEM_LAN_0;
	}
}

if(isset($_POST['create_content'])){
	if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
		$adb -> dbContentCreate("admin");
	}else{
		$message	= CONTENT_ADMIN_ITEM_LAN_0;
	}
}

if(isset($_POST['update_content'])){
	if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
		$adb -> dbContentUpdate("admin");
	}else{
		$message	= CONTENT_ADMIN_ITEM_LAN_0;
	}
}

if(isset($_POST['update_order'])){
	if(isset($qs[1])){
		if(isset($qs[2])){
			$message = $adb -> dbSetOrder("all", "ci", $_POST['order']);
		}else{
			$message = $adb -> dbSetOrder("all", "ai", $_POST['order']);
		}
	}else{
		$message = $adb -> dbSetOrder("all", "cc", $_POST['order']);
	}
}

if(isset($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// ##### End --------------------------------------------------------------------------------------

if(!e_QUERY){																//show main categories
	$aform -> show_manage_content("", "", "");
	require_once(e_ADMIN."footer.php");
	exit;
}else{
	$qs = explode(".", e_QUERY);

	//manage content items
	if($qs[0] == "content" && is_numeric($qs[1]) ){
		$aform -> show_manage_content("", "", "");

	//edit content item
	}elseif($qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]) ){
		$newqs = array_reverse($qs);
		if($newqs[0] == "cu"){										//item; update redirect
			$mainparent = $aa -> getMainParent($qs[2]);
			$message = CONTENT_ADMIN_ITEM_LAN_2."<br /><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_88." <a href='".e_SELF."?content.create.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_89." <a href='".e_SELF."?content.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_91." <a href='".e_SELF."?content.edit.".$qs[2]."'>".CONTENT_ADMIN_ITEM_LAN_90."</a>";
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(e_ADMIN."footer.php");
			exit;
		}
		$aform -> show_create_content("admin", $userid="", $username="");

	//post submitted content item
	}elseif($qs[0] == "content" && $qs[1] == "sa" && is_numeric($qs[2]) ){
		$newqs = array_reverse($qs);
		if($newqs[0] == "cu"){										//item; submit post / update redirect
			$mainparent = $aa -> getMainParent($qs[2]);
			$message = CONTENT_ADMIN_ITEM_LAN_117."<br /><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_88." <a href='".e_SELF."?content.create.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_89." <a href='".e_SELF."?content.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_91." <a href='".e_SELF."?content.edit.".$qs[2]."'>".CONTENT_ADMIN_ITEM_LAN_90."</a>";
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(e_ADMIN."footer.php");
			exit;
		}
		$aform -> show_create_content("sa", $userid="", $username="");

	//create content item
	}elseif($qs[0] == "content" && $qs[1] == "create" ){
		$newqs = array_reverse($qs);
		if($newqs[0] == "cc"){										//item; create redirect
			$mainparent = $aa -> getMainParent($qs[2]);
			$message = CONTENT_ADMIN_ITEM_LAN_1."<br /><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_88." <a href='".e_SELF."?content.create.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_89." <a href='".e_SELF."?content.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(e_ADMIN."footer.php");
			exit;
		}
		$aform -> show_create_content("admin", $userid="", $username="");



	//order : view categories
	}elseif($qs[0] == "order" && !isset($qs[1])){
		$aform -> show_order();

	//order global items of parent='2'
	}elseif($qs[0] == "order" && is_numeric($qs[1]) && !isset($qs[2]) ){
		$aform -> show_content_order("ai");

	//increase order of global items
	}elseif($qs[0] == "order" && is_numeric($qs[1]) && $qs[2] == "inc" && isset($qs[3]) ){
		$message = $adb -> dbSetOrder("inc", "ai", $qs[3]);
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		$aform -> show_content_order("ai");

	//decrease order of global items
	}elseif($qs[0] == "order" && is_numeric($qs[1]) && $qs[2] == "dec" && isset($qs[3]) ){
		$message = $adb -> dbSetOrder("dec", "ai", $qs[3]);
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		$aform -> show_content_order("ai");

	//order items with parent=2 or category='5'
	}elseif($qs[0] == "order" && is_numeric($qs[1]) && is_numeric($qs[2]) && !isset($qs[3]) ){
		$aform -> show_content_order("ci");
	
	//increase order of items in category
	}elseif($qs[0] == "order" && is_numeric($qs[1]) && is_numeric($qs[2]) && $qs[3] == "inc" && isset($qs[4]) ){
		$message = $adb -> dbSetOrder("inc", "ci", $qs[4]);
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		$aform -> show_content_order("ci");

	//decrease order of items in category
	}elseif($qs[0] == "order" && is_numeric($qs[1]) && is_numeric($qs[2]) && $qs[3] == "dec" && isset($qs[4]) ){
		$message = $adb -> dbSetOrder("dec", "ci", $qs[4]);
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		$aform -> show_content_order("ci");

	//increase category order
	}elseif($qs[0] == "order" && $qs[1] == "inc" && isset($qs[2]) ){
		$message = $adb -> dbSetOrder("inc", "cc", $qs[2]);
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		$aform -> show_order("admin");

	//decrease category order
	}elseif($qs[0] == "order" && $qs[1] == "dec" && isset($qs[2]) ){
		$message = $adb -> dbSetOrder("dec", "cc", $qs[2]);
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		$aform -> show_order("admin");




	}elseif($qs[0] == "submitted" && !isset($qs[1]) ){
		$aform -> show_submitted();




	}elseif($qs[0] == "option" && !isset($qs[1]) ){
		$aform -> show_options();

	}elseif($qs[0] == "option" && isset($qs[1]) && (is_numeric($qs[1]) || $qs[1] == "default") ){
		$aform -> show_options_cat();




	//category content manager : choose category
	}elseif($qs[0] == "manager" && !isset($qs[1]) ){
		if(!getperms("0")){ header("location:".e_SELF); exit; }
		//$aform -> show_admin_contentmanager();
		$aform -> show_manage("manager");
	
	//category content manager : view contentmanager
	}elseif($qs[0] == "manager" && isset($qs[1]) && is_numeric($qs[1]) ){
		if(!getperms("0")){ header("location:".e_SELF); exit; }
		if(isset($qs[2])){
			$message = $adb -> dbAssignAdmins("admin", $qs[1], $qs[2]);
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		}
		$aform -> show_admin_contentmanager_category();




	//overview all categories
	}elseif($qs[0] == "cat" && !isset($qs[1]) ){
		$aform -> show_manage("category");

	//create category
	}elseif($qs[0] == "cat" && $qs[1] == "create" ){
		$newqs = array_reverse($qs);
		if($newqs[0] == "pc"){									//category; create redirect
				$message = CONTENT_ADMIN_CAT_LAN_11."<br /><br />";
				$message .= "<br /><br />".CONTENT_ADMIN_CAT_LAN_50."<br /><br />";
				$message .= "
				".CONTENT_ADMIN_CAT_LAN_44." <a href='".e_SELF."?cat.create'>".CONTENT_ADMIN_CAT_LAN_43."</a><br />
				".CONTENT_ADMIN_CAT_LAN_42." <a href='".e_SELF."?cat.edit.".$qs[2]."'>".CONTENT_ADMIN_CAT_LAN_43."</a><br />
				";
				$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
				require_once(e_ADMIN."footer.php");
				exit;
		}
		$aform -> show_create_category();

	//edit category
	}elseif($qs[0] == "cat" && $qs[1] == "edit" && is_numeric($qs[2]) ){
		$newqs = array_reverse($qs);
		if($newqs[0] == "pu"){										//category; update redirect
				$message = CONTENT_ADMIN_CAT_LAN_12."<br /><br />
				".CONTENT_ADMIN_CAT_LAN_42." <a href='".e_SELF."?cat.edit.".$qs[2]."'>".CONTENT_ADMIN_CAT_LAN_43."</a><br />
				".CONTENT_ADMIN_CAT_LAN_53." <a href='".e_SELF."?cat'>".CONTENT_ADMIN_CAT_LAN_43."</a><br />";
				$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
				require_once(e_ADMIN."footer.php");
				exit;
		}
		$aform -> show_create_category();


	}
}

// ##### End --------------------------------------------------------------------------------------


// ##### Display options --------------------------------------------------------------------------
function admin_content_config_adminmenu(){

                global $sql, $plugintable, $aa;

				if(e_QUERY){
					$qs		=	explode(".", e_QUERY);
				}

				if(isset($qs[0]) && $qs[0] == "cat" && isset($qs[1]) && $qs[1] == "create"){
					$act	= $qs[0].".".$qs[1];

				}elseif(isset($qs[0]) && $qs[0] == "content" && isset($qs[1]) && $qs[1] == "create"){
					$act	= $qs[0].".".$qs[1];

				}else{
					$act	= (isset($qs[0]) ? $qs[0] : "");
				}

                if($act==""){$act="content";}

                $var['content']['text']			= CONTENT_ADMIN_MENU_LAN_0;
                $var['content']['link']			= e_SELF;

                $var['content.create']['text']	= CONTENT_ADMIN_MENU_LAN_1;
                $var['content.create']['link']	= e_SELF."?content.create";

                $var['cat']['text']				= CONTENT_ADMIN_MENU_LAN_2;
                $var['cat']['link']				= e_SELF."?cat";

				$var['cat.create']['text']		= CONTENT_ADMIN_MENU_LAN_3;
                $var['cat.create']['link']		= e_SELF."?cat.create";

				$var['order']['text']			= CONTENT_ADMIN_MENU_LAN_15;
                $var['order']['link']			= e_SELF."?order";

				$var['option']['text']			= CONTENT_ADMIN_MENU_LAN_6;
                $var['option']['link']			= e_SELF."?option";

				$var['manager']['text']			= CONTENT_ADMIN_MENU_LAN_17;
                $var['manager']['link']			= e_SELF."?manager";

                if($submittedcontents = $sql -> db_Count($plugintable, "(*)", "WHERE content_refer ='sa' ")){
                        $var['submitted']['text']	= CONTENT_ADMIN_MENU_LAN_4." (".$submittedcontents.")";
                        $var['submitted']['link']	= e_SELF."?submitted";
                }

				show_admin_menu(CONTENT_ADMIN_MENU_LAN_6, $act,$var);

				if(isset($qs[0]) && $qs[0] == "option" && isset($qs[1])){
					unset($var);
					$var=array();
					$var['creation']['text']		= CONTENT_ADMIN_MENU_LAN_7;
					$var['submission']['text']		= CONTENT_ADMIN_MENU_LAN_8;
					$var['paththeme']['text']		= CONTENT_ADMIN_MENU_LAN_9;
					$var['general']['text']			= CONTENT_ADMIN_MENU_LAN_10;
					//$var['recentpages']['text']		= CONTENT_ADMIN_MENU_LAN_11;
					//$var['catpages']['text']		= CONTENT_ADMIN_MENU_LAN_12;
					//$var['contentpages']['text']	= CONTENT_ADMIN_MENU_LAN_13;
					//$var['authorpage']['text']		= CONTENT_ADMIN_MENU_LAN_18;
					//$var['archivepage']['text']		= CONTENT_ADMIN_MENU_LAN_16;
					//$var['toppage']['text']			= CONTENT_ADMIN_MENU_LAN_20;
					$var['contentmanager']['text']	= CONTENT_ADMIN_MENU_LAN_19;
					$var['menu']['text']			= CONTENT_ADMIN_MENU_LAN_14;

					$sql = new db;
					$category_total			= $sql -> db_Select($plugintable, "content_heading", "content_id='".$qs[1]."' ");
					list($content_heading)	= $sql -> db_Fetch();

					show_admin_menu(CONTENT_ADMIN_MENU_LAN_6.": ".$content_heading."", $act, $var, TRUE);

					unset($var);
					$var=array();
					//$var['creation']['text']		= CONTENT_ADMIN_MENU_LAN_7;
					//$var['submission']['text']		= CONTENT_ADMIN_MENU_LAN_8;
					//$var['paththeme']['text']		= CONTENT_ADMIN_MENU_LAN_9;
					//$var['general']['text']			= CONTENT_ADMIN_MENU_LAN_10;
					$var['recentpages']['text']		= CONTENT_ADMIN_MENU_LAN_11;
					$var['catpages']['text']		= CONTENT_ADMIN_MENU_LAN_12;
					$var['contentpages']['text']	= CONTENT_ADMIN_MENU_LAN_13;
					$var['authorpage']['text']		= CONTENT_ADMIN_MENU_LAN_18;
					$var['archivepage']['text']		= CONTENT_ADMIN_MENU_LAN_16;
					$var['toppage']['text']			= CONTENT_ADMIN_MENU_LAN_20;
					$var['scorepage']['text']		= CONTENT_ADMIN_MENU_LAN_22;
					//$var['contentmanager']['text']	= CONTENT_ADMIN_MENU_LAN_19;
					//$var['menu']['text']			= CONTENT_ADMIN_MENU_LAN_14;
					show_admin_menu(CONTENT_ADMIN_MENU_LAN_21.": ".$content_heading."", $act, $var, TRUE);
				
				}else{
						$sql2 = new db;
						if($category_total = $sql2 -> db_Select($plugintable, "content_id, content_heading", "content_parent='0' ")){
							while($row = $sql2 -> db_Fetch()){

								unset($var);
								$var=array();

								$array		= $aa -> getCategoryTree("", $row['content_id'], FALSE);	//get all categories from each main parent
								$newarray	= array_merge_recursive($array);

								$newparent=array();
								for($a=0;$a<count($newarray);$a++){
									for($b=0;$b<count($newarray[$a]);$b++){
										$newparent[$newarray[$a][$b]] = $newarray[$a][$b+1];
										$b++;
									}
								}

								foreach($newparent as $key => $value){
									$var['c'.$key]['text']	= $value;
									$var['c'.$key]['link']	= e_SELF."?content.".$key;
								}
								if( isset($qs[0]) && $qs[0] == "content" && isset($qs[1]) && $qs[1] == "create"){
									$act = "";
								}elseif( isset($qs[0]) && $qs[0] == "cat" && isset($qs[1]) && ($qs[1] == "create" || $qs[1] == "edit") ){
									$act = "";
								}elseif( isset($qs[0]) && $qs[0] == "order" ){
									$act = "";
								}elseif( isset($qs[0]) && $qs[0] == "manager" ){
									$act = "";
								}else{
									if(isset($qs[0]) && isset($qs[1]) ){
										$act = "c".$qs[1];
									}else{
										$act = "c";
									}
								}

								show_admin_menu(CONTENT_ADMIN_MENU_LAN_5." : ".$row['content_heading']."", $act, $var);
							}
						}
				}

}
// ##### End --------------------------------------------------------------------------------------


require_once(e_ADMIN."footer.php");

function headerjs(){
	global $tp, $plugindir;

	$script = "
	<script type='text/javascript' src='".$plugindir."content.js'></script>\n
	<script type=\"text/javascript\">

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
	//var newvalues;
	var isNew = true;
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
	}
	document.getElementById('assignclass1').selectedIndex=-1;
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
	}

	function clearMe(clid) {
	location.href = document.location + \".clear\";
	}

	function saveMe(clid) {
	var strValues = \"\";
	var boxLength = document.getElementById('assignclass2').length;
	var count = 0;
	if (boxLength != 0) {
	for (i = 0; i < boxLength; i++) {
	if (count == 0) {
	strValues = document.getElementById('assignclass2').options[i].value;
	} else {
	strValues = strValues + \",\" + document.getElementById('assignclass2').options[i].value;
	}
	count++;
	}
	}
	if (strValues.length == 0) {
	//alert(\"You have not made any selections\");
	}
	else {
	location.href = document.location + \".\" + strValues;
	}
	}
	//]]>
	</script>";

	return $script;
}

?>