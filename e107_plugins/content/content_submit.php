<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
if (!isset($pref['plug_installed']['content']))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

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

//these have to be set for the tinymce wysiwyg
$e_wysiwyg	= "content_text";
global $tp;

include_lan($plugindir.'languages/'.e_LANGUAGE.'/lan_content.php');

if(e_QUERY){
	$qs = explode(".", e_QUERY);
}

// define e_pagetitle
$aa -> setPageTitle();

require_once(HEADERF);

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
			if(is_readable(e_THEME.$pref['sitetheme']."/content/content_submit_type_template.php")){
				require_once(e_THEME.$pref['sitetheme']."/content/content_submit_type_template.php");
			}else{
				require_once(e_PLUGIN."content/templates/content_submit_type_template.php");
			}
		}
		$sql2 = "";
		$content_submit_type_table_string = "";
		$count = "0";
		while($row = $sql -> db_Fetch()){
			if(!is_object($sql2)){ $sql2 = new db; }

			$content_pref					= $eArrayStorage->ReadArray($row['content_pref']);
			$content_pref["content_cat_icon_path_large"] = ($content_pref["content_cat_icon_path_large"] ? $content_pref["content_cat_icon_path_large"] : "{e_PLUGIN}content/images/cat/48/" );
			$content_pref["content_cat_icon_path_small"] = ($content_pref["content_cat_icon_path_small"] ? $content_pref["content_cat_icon_path_small"] : "{e_PLUGIN}content/images/cat/16/" );
			$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large"]);
			$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small"]);
			$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path"]);
			if($content_pref["content_submit"] && check_class($content_pref["content_submit_class"])){
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
	$array			= $aa -> getCategoryTree("", intval($qs[2]), TRUE);
	$validparent	= implode(",", array_keys($array));
	$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
	$sql2			= new db;
	//$contenttotal	= $sql2 -> db_Count($plugintable, "(*)", "WHERE ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " );
	$aform -> show_create_content("submit");
}

require_once(FOOTERF);

?>
