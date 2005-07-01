<?php
/*
+ ----------------------------------------------------------------------------+
|    e107 website system
|
|    ©Steve Dunstan 2001-2002
|    http://e107.org
|    jalist@e107.org
|
|    Released   under the   terms and   conditions of the
|    GNU    General Public  License (http://gnu.org).
|
|    $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/admin_linkspage_config.php,v $
|    $Revision: 1.1 $
|    $Date: 2005-07-01 08:03:58 $
|    $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
}

$imagedir = e_IMAGE."/admin_images/";
if (!defined('LINK_ICON_EDIT')) { define("LINK_ICON_EDIT", "<img src='".$imagedir."maintain_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_DELETE')) { define("LINK_ICON_DELETE", "<img src='".$imagedir."delete_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_DELETE_BASE')) { define("LINK_ICON_DELETE_BASE", $imagedir."delete_16.png"); }
if (!defined('LINK_ICON_LINK')) { define("LINK_ICON_LINK", "<img src='".$imagedir."leave_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_ORDER_UP_BASE')) { define("LINK_ICON_ORDER_UP_BASE", $imagedir."up.png"); }
if (!defined('LINK_ICON_ORDER_DOWN_BASE')) { define("LINK_ICON_ORDER_DOWN_BASE", $imagedir."down.png"); }
if (!defined('LINK_ICON_ORDER_UP')) { define("LINK_ICON_ORDER_UP", "<img src='".$imagedir."up.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_ORDER_DOWN')) { define("LINK_ICON_ORDER_DOWN", "<img src='".$imagedir."down.png' alt='' style='border:0; cursor:pointer;' />"); }

$lan_file = $plugindir."languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : $plugindir."languages/English.php");

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_PLUGIN.'links_page/link_class.php');
$lc = new linkclass();

$linkspage_pref = $lc -> getLinksPagePref();

$deltest = array_flip($_POST);

if (e_QUERY) {
	$qs = explode(".", e_QUERY);
}

if(isset($_POST['delete'])){
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
}
if (isset($_POST['create_category'])) {
	$lc -> dbCategoryCreate($_POST);
}
if (isset($_POST['update_category'])) {
	$lc -> dbCategoryUpdate($_POST);
}
if (isset($_POST['updateoptions'])) {
	$linkspage_pref = $lc -> UpdateLinksPagePref($_POST);
	$lc -> show_message(LCLAN_ADMIN_6);
}
if (isset($_POST['add_link'])) {
	$lc -> dbLinkCreate();
}
//upload link icon
if(isset($_POST['uploadlinkicon'])){
	$lc -> uploadLinkIcon($_POST);
}
//upload category icon
if(isset($_POST['uploadcatlinkicon'])){
	$lc -> uploadCatLinkIcon($_POST);
}
//update link order
if (isset($_POST['update_order'])) {
	$lc -> dbOrderUpdate($_POST['link_order']);
}
//update link category order
if (isset($_POST['update_category_order'])) {
	$lc -> dbOrderCatUpdate($_POST['link_category_order']);
}
if (isset($_POST['inc'])) {
	$lc -> dbOrderUpdateInc($_POST['inc']);
}
if (isset($_POST['dec'])) {
	$lc -> dbOrderUpdateDec($_POST['dec']);
}
//delete link
if (isset($delete) && $delete == 'main') {
	$sql->db_Select("links_page", "link_order", "link_id='".$del_id."'");
	$row = $sql->db_Fetch();
	$sql2 = new db;
	$sql->db_Select("links_page", "link_id", "link_order>'".$row['link_order']."' && link_category='".$id."'");
	while ($row = $sql->db_Fetch()) {
		$sql2->db_Update("links_page", "link_order=link_order-1 WHERE link_id='".$row['link_id']."'");
	}
	if ($sql->db_Delete("links_page", "link_id='".$del_id."'")) {
		$lc->show_message(LCLAN_ADMIN_10." #".$del_id." ".LCLAN_ADMIN_11);
	}
}
//delete category
if (isset($delete) && $delete == 'category') {
	if ($sql->db_Delete("links_page_cat", "link_category_id='$del_id' ")) {
		$lc->show_message(LCLAN_ADMIN_12." #".$del_id." ".LCLAN_ADMIN_11);
		unset($id);
	}
}
//delete submitted link
if (isset($delete) && $delete == 'sn') {
	if ($sql->db_Delete("tmp", "tmp_time='$del_id' ")) {
		$lc->show_message(LCLAN_ADMIN_13);
	}
}



//show link categories (cat edit)
if (!e_QUERY) {
	$lc->show_categories("cat");
}

//show cat edit form
if (isset($qs[0]) && $qs[0] == 'cat' && isset($qs[1]) && $qs[1] == 'edit' && isset($qs[2]) && is_numeric($qs[2])) {
	$lc->show_cat_create();
}

//show cat create form
if (isset($qs[0]) && $qs[0] == 'cat' && isset($qs[1]) && $qs[1] == 'create' && !isset($qs[2]) ) {
	$lc->show_cat_create();
}

if (isset($qs[0]) && $qs[0] == 'link') {
	//view categories (link select cat)
	if (!isset($qs[1])){
		$lc->show_categories("link");

	//view links in cat
	}elseif (isset($qs[1]) && $qs[1] == 'view' && isset($qs[2]) && is_numeric($qs[2])) {
		$lc->show_links();

	//edit link
	}elseif (isset($qs[1]) && $qs[1] == 'edit' && isset($qs[2]) && is_numeric($qs[2])) {
		$lc->show_link_create();

	//create link
	}elseif (isset($qs[1]) && $qs[1] == 'create' && !isset($qs[2]) ) {
		$lc->show_link_create();

	//post submitted
	}elseif (isset($qs[1]) && $qs[1] == 'sn' && isset($qs[2]) && is_numeric($qs[2]) ) {
		$lc->show_link_create();
	}
}

//view submitted links
if (isset($qs[0]) && $qs[0] == 'sn') {
	$lc->show_submitted();
}

//options
if (isset($qs[0]) && $qs[0] == 'opt') {
	$lc->show_pref_options();
}

// ##### Display options --------------------------------------------------------------------------
function admin_linkspage_config_adminmenu(){
	global $qs, $sql;
	if ($qs[0] == "") {
		$act = "cat";
	}else{
		$act = $qs[0];
		if(isset($qs[1])){
			if($qs[1] == "create"){
				$act .= ".create";
			}
			if($qs[1] == "edit"){
				$act .= "";
			}
			if($qs[1] == "view"){
				$act .= "";
			}
		}
	}

	$var['cat']['text'] = LCLAN_ADMINMENU_2;
	$var['cat']['link'] = e_SELF;

	$var['cat.create']['text'] = LCLAN_ADMINMENU_3;
	$var['cat.create']['link'] = e_SELF."?cat.create";

	$var['link']['text'] = LCLAN_ADMINMENU_4;
	$var['link']['link'] = e_SELF."?link";

	$var['link.create']['text'] = LCLAN_ADMINMENU_5;
	$var['link.create']['link'] = e_SELF."?link.create";
		
	if ($tot = $sql->db_Select("tmp", "*", "tmp_ip='submitted_link' ")) {
		$var['sn']['text'] = LCLAN_ADMINMENU_7." (".$tot.")";
		$var['sn']['link'] = e_SELF."?sn";
	}
		
	$var['opt']['text'] = LCLAN_ADMINMENU_6;
	$var['opt']['link'] = e_SELF."?opt";
		
	show_admin_menu(LCLAN_ADMINMENU_1, $act, $var);
		
	if ($sql->db_Select("links_page_cat", "*")) {
		while ($row = $sql->db_Fetch()) {
			$cat_var[$row['link_category_id']]['text'] = $row['link_category_name'];
			$cat_var[$row['link_category_id']]['link'] = e_SELF."?link.view.".$row['link_category_id'];
		}
		 
		$active = ($qs[0] == 'link') ? $id : FALSE;
		show_admin_menu(LCLAN_ADMINMENU_8, $active, $cat_var);
	}
}

require_once(e_ADMIN.'footer.php');
exit;

// End ---------------------------------------------------------------------------------------------------------

?>