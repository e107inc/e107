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
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/content_submit.php,v $
|		$Revision: 1.18 $
|		$Date: 2005-07-12 11:39:01 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

$plugindir = e_PLUGIN."content/";

require_once($plugindir."content_shortcodes.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
$rs = new form;
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

$lan_file = $plugindir.'languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : $plugindir.'languages/English/lan_content.php');

if(e_QUERY){
	$qs = explode(".", e_QUERY);
}

// define e_pagetitle
$aa -> setPageTitle();

require_once(HEADERF);

//include js
function headerjs(){
	global $tp, $plugindir, $qs, $pref;

	if( $qs[0] == "content" && $qs[1] == "submit" && is_numeric($qs[2]) ){
		$e_wysiwyg			= "content_text";
		if($pref['wysiwyg']){
			$pref['allow_html']	= "1";
			require_once(e_HANDLER."tiny_mce/wysiwyg.php");
			echo wysiwyg($e_wysiwyg);
		}
	}
}

if(isset($_POST['create_content'])){
	if($_POST['content_text'] && $_POST['content_heading'] && $_POST['parent'] != "none" && $_POST['content_author_name'] != "" && $_POST['content_author_email'] != ""){
		$adb -> dbContent("create", "submit");
	}else{
		$message = CONTENT_ADMIN_SUBMIT_LAN_4;
	}
}

if(isset($qs[0]) && $qs[0] == "s"){
	$message = CONTENT_ADMIN_SUBMIT_LAN_2."<br /><br />".CONTENT_ADMIN_SUBMIT_LAN_5;
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	require_once(FOOTERF);
	exit;
}
if(isset($qs[0]) && $qs[0] == "d"){
	$message = CONTENT_ADMIN_SUBMIT_LAN_3."<br /><br />".CONTENT_ADMIN_SUBMIT_LAN_5;
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	require_once(FOOTERF);
	exit;
}

if(isset($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!isset($qs[0])){
	if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_icon, content_pref", "content_parent = '0' AND content_class REGEXP '".e_CLASS_REGEXP."' ORDER BY content_heading")){
		$text .= "<div style='text-align:center;'>".CONTENT_ADMIN_SUBMIT_LAN_0."</div>";
	}else{
		
		if(!isset($CONTENT_SUBMIT_TYPE_TABLE)){
			require_once($plugindir."templates/content_submit_type_template.php");
		}
		$sql2 = "";
		$content_submit_type_table_string = "";
		$count = "0";
		while($row = $sql -> db_Fetch()){
			if(!is_object($sql2)){ $sql2 = new db; }

			$content_pref					= $eArrayStorage->ReadArray($row['content_pref']);
			$content_pref["content_cat_icon_path_large_{$row['content_id']}"] = ($content_pref["content_cat_icon_path_large_{$row['content_id']}"] ? $content_pref["content_cat_icon_path_large_{$row['content_id']}"] : "{e_PLUGIN}content/images/cat/48/" );
			$content_pref["content_cat_icon_path_small_{$row['content_id']}"] = ($content_pref["content_cat_icon_path_small_{$row['content_id']}"] ? $content_pref["content_cat_icon_path_small_{$row['content_id']}"] : "{e_PLUGIN}content/images/cat/16/" );
			$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$row['content_id']}"]);
			$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$row['content_id']}"]);
			$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$row['content_id']}"]);
			if($content_pref["content_submit_{$row['content_id']}"] && check_class($content_pref["content_submit_class_{$row['content_id']}"])){
				$content_submit_type_table_string .= $tp -> parseTemplate($CONTENT_SUBMIT_TYPE_TABLE, FALSE, $content_shortcodes);
				$count = $count + 1;
			}
		}
		if($count == "0"){
			$text .= "<div style='text-align:center;'>".CONTENT_ADMIN_SUBMIT_LAN_0."</div>";
		}else{
			$text = $CONTENT_SUBMIT_TYPE_TABLE_START.$content_submit_type_table_string.$CONTENT_SUBMIT_TYPE_TABLE_END;
		}
	}
	$caption = CONTENT_ADMIN_SUBMIT_LAN_1;
	$ns -> tablerender($caption, $text);
}

if(isset($qs[0]) && $qs[0]=="content" && $qs[1] == "submit" && is_numeric($qs[2]) && !isset($qs[3])){
	//check if valid categories exist for this main parent
	$array			= $aa -> getCategoryTree("", $qs[2], TRUE);
	$validparent	= implode(",", array_keys($array));
	$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
	$sql2			= new db;
	//$contenttotal	= $sql2 -> db_Count($plugintable, "(*)", "WHERE ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " );
	$aform -> show_create_content("submit");
}

require_once(FOOTERF);

?>
