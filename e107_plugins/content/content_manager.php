<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/content_manager.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

require_once("../../class2.php");

$plugindir = e_PLUGIN."content/";
require_once($plugindir."content_shortcodes.php");
global $tp;
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_HANDLER."file_class.php");
$fl = new e_file;

require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);

require_once($plugindir."handlers/content_class.php");
$aa = new content;
require_once($plugindir."handlers/content_db_class.php");
$adb = new contentdb;
require_once($plugindir."handlers/content_form_class.php");
$aform = new contentform;

include_lan($plugindir.'languages/'.e_LANGUAGE.'/lan_content_admin.php');
include_lan($plugindir.'languages/'.e_LANGUAGE.'/lan_content.php');

if(is_readable(e_THEME.$pref['sitetheme']."/content/content_admin_template.php")){
	require_once(e_THEME.$pref['sitetheme']."/content/content_admin_template.php");
}else{
	require_once(e_PLUGIN."content/templates/content_admin_template.php");
}

$deltest = array_flip($_POST);

if(e_QUERY){
	$qs = explode(".", e_QUERY);
}


if (!USER)
{	// non-user can never manage content
	header("location:".$plugindir."content.php"); 
	exit;
}


// define e_pagetitle
$aa -> setPageTitle();


// ##### DB ---------------------------------------------------------------------------------------

require_once(HEADERF);

//db : content create
if(isset($_POST['create_content'])){
	//content submit
	if(isset($qs[1]) && $qs[1] == "submit"){
		if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none" && $_POST['content_author_name'] != "" && $_POST['content_author_email'] != ""){
			$adb -> dbContent("create", "submit");
		}else{
			$message = CONTENT_ADMIN_SUBMIT_LAN_4;
		}
	//content create (manager)
	}elseif(isset($qs[1]) && $qs[1] == "create"){
		if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
			$adb -> dbContent("create", "contentmanager");
		}else{
			$message = CONTENT_ADMIN_ITEM_LAN_0;
		}
	}
}

//db : content update
if(isset($_POST['update_content'])){
	if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none"){
		$adb -> dbContent("update", "contentmanager");
	}else{
		$message = CONTENT_ADMIN_ITEM_LAN_0;
	}
}

//db : content delete
if(isset($_POST['delete'])){
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
}
if($delete == 'content' && is_numeric($del_id)){
	if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
		$message = CONTENT_ADMIN_ITEM_LAN_3;
		$e107cache->clear("content");
	}
}

//render message
if(isset($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

//db : returned messages

	//content item submitted (with direct posting)
	if(isset($qs[0]) && $qs[0] == "s"){
		$message = CONTENT_ADMIN_SUBMIT_LAN_2."<br /><br />".CONTENT_ADMIN_SUBMIT_LAN_5;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		require_once(FOOTERF);
		exit;

	//content item submitted and reviewed in due course (without direct posting)
	}elseif(isset($qs[0]) && $qs[0] == "d"){
		$message = CONTENT_ADMIN_SUBMIT_LAN_3."<br /><br />".CONTENT_ADMIN_SUBMIT_LAN_5;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		require_once(FOOTERF);
		exit;

	//content item created (personal/category manager)
	}elseif(isset($qs[0]) && $qs[0] == "c"){
		$message = CONTENT_ADMIN_ITEM_LAN_1."<br /><br />".CONTENT_ADMIN_ITEM_LAN_55;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		require_once(FOOTERF);
		exit;

	//content item updated (personal/category manager)
	}elseif(isset($qs[0]) && $qs[0] == "u"){
		$message = CONTENT_ADMIN_ITEM_LAN_2."<br /><br />".CONTENT_ADMIN_ITEM_LAN_55;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
		require_once(FOOTERF);
		exit;
	}

if(!e_QUERY){
	//show content manager/submit options
	if(USER){
		$aform -> show_contentmanager("edit", USERID, USERNAME);
		require_once(FOOTERF);
		exit;
	}else{
		header("location:".$plugindir."content.php"); exit;
	}
}else{

	//validate permissions
	if(isset($qs[1]) && ($qs[1]=='edit' || $qs[1]=='sa') ){
		//on the edit page, the query id holds the content item's id number
		//we need to get the category (parent) of the content item first
		//this is both on the 'edit' page as well as on the 'post submitted' page.
		if(!$sql -> db_Select($plugintable, "content_id, content_parent", "content_id='".intval($qs[2])."' ")){
			//not a valid item, so redirect
			header("location: ".e_SELF); exit;
		}else{
			$row = $sql -> db_Fetch();
			//parent can be '0' (top level) or '0.X (subcategory)
			if(strpos($row['content_parent'], ".")){
				$id = substr($row['content_parent'],2);
			}else{
				$id = $row['content_parent'];
			}
		}

	}else{
		//on other pages in the manager either $qs[2] or $qs[1] holds the category id number
		if(isset($qs[2]) && is_numeric($qs[2]) ){
			$id = intval($qs[2]);
		}elseif(isset($qs[1]) && is_numeric($qs[1]) ){
			$id = intval($qs[1]);
		}
	}
	if(!isset($id)){
		header("location: ".e_SELF); exit;
	}

	//get preferences for this category
	$content_pref = $aa->getContentPref($id);

	//if inherit is used in the manager, we need to get the preferences from the core plugin table default preferences
	//and use those preferences in the permissions check.
	if( varsettrue($content_pref['content_manager_inherit']) ){
		$sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
		$row = $sql -> db_Fetch();
		$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
	}
	$content_pref = $aa->parseConstants($content_pref);

	//now we can check the permissions for this user
	$personalmanagercheck = FALSE;
	if( (isset($content_pref["content_manager_approve"]) && check_class($content_pref["content_manager_approve"])) || 
		(isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || 
		(isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) || 
		(isset($content_pref["content_manager_submit"]) && check_class($content_pref["content_manager_submit"]))
		){
		$personalmanagercheck = TRUE;
	//user is not allowed here, redirect to content frontpage
	}else{
		header("location:".$plugindir."content.php"); exit;
	}

	//show list of items in this category
	if(isset($qs[0]) && $qs[0] == "content" && is_numeric($qs[1])){
		$aform -> show_manage_content("contentmanager", USERID, USERNAME);

	//content create (manager)
	}elseif(isset($qs[0]) && $qs[0] == "content" && $qs[1] == "create" && is_numeric($qs[2])){
		$aform -> show_create_content("contentmanager", USERID, USERNAME);

	//content create (submit)
	}elseif(isset($qs[0]) && $qs[0]=="content" && $qs[1] == "submit" && is_numeric($qs[2]) && !isset($qs[3])){
		$aform -> show_create_content("submit", USERID, USERNAME);

	//content edit
	}elseif(isset($qs[0]) && $qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2])){
		$aform -> show_create_content("contentmanager", USERID, USERNAME);

	//display list of submitted content items
	}elseif(isset($qs[0]) && $qs[0] == "content" && $qs[1] == "approve" && is_numeric($qs[2])){
		//$aform -> show_submitted("contentmanager", USERID, USERNAME, $qs[2]);
		$aform -> show_submitted($qs[2]);

	//approve/post submitted content item
	}elseif(isset($qs[0]) && $qs[0] == "content" && $qs[1] == "sa" && is_numeric($qs[2]) ){
		$newqs = array_reverse($qs);
		//item; submit post / update redirect
		if($newqs[0] == "cu"){
			$mainparent = $aa -> getMainParent($qs[2]);
			$message = CONTENT_ADMIN_ITEM_LAN_117."<br /><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_88." <a href='".e_SELF."?content.create.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_89." <a href='".e_SELF."?content.".$mainparent."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_91." <a href='".e_SELF."?content.edit.".$qs[2]."'>".CONTENT_ADMIN_ITEM_LAN_90."</a><br />";
			$message .= CONTENT_ADMIN_ITEM_LAN_124." <a href='".e_PLUGIN."content/content.php?content.".$qs[2]."'>".CONTENT_ADMIN_ITEM_LAN_90."</a>";
			$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
			require_once(e_ADMIN."footer.php");
			exit;
		}
		$aform -> show_create_content("sa", USERID, USERNAME);

	}else{
		header("location:".e_SELF); exit;
	}
}

require_once(FOOTERF);

?>