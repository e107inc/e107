<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/frontpage.php,v $
|     $Revision: 1.15 $
|     $Date: 2005-03-15 17:43:11 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("G")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'frontpage';

// update from old 6xx system
if (!$pref['frontpage']) {
	$pref['frontpage'] = "news.php";
	save_prefs();
} else if ($pref['frontpage'] == 'links') {
	$pref['frontpage'] = $PLUGINS_DIRECTORY."links_page/links.php";
	save_prefs();
} else if ($pref['frontpage'] == 'forum') {
	$pref['frontpage'] = $PLUGINS_DIRECTORY."forum/forum.php";
	save_prefs();
} else if (is_numeric($pref['frontpage'])) {
	$pref['frontpage'] = "content.php?content.".$pref['frontpage'];
	save_prefs();
} else if (strpos($pref['frontpage'], ".")===FALSE) {
	if (!preg_match("#/$#",$pref['frontpage'])) {
		$pref['frontpage'] = $pref['frontpage'].'.php';
		save_prefs();
	}
}
// end update

global $tp;
if (isset($_POST['updatesettings'])) {

	if ($_POST['frontpage'] == 'news') {
		$frontpage_value = 'news.php';

	} else if ($_POST['frontpage'] == 'forum') {
		$frontpage_value = $PLUGINS_DIRECTORY.'forum/forum.php';

	} else if ($_POST['frontpage'] == 'download') {
		$frontpage_value = 'download.php';

	} else if ($_POST['frontpage'] == 'links') {
		$frontpage_value = $PLUGINS_DIRECTORY.'links_page/links.php';

	} else if (is_numeric($_POST['frontpage'])) {
		$frontpage_value = 'content.php?content.'.$_POST['frontpage'];

	} else if ($_POST['frontpage'] == 'other') {
		$_POST['frontpage_url'] = $tp->toForm($_POST['frontpage_url']);
		$frontpage_value = $_POST['frontpage_url'] ? $_POST['frontpage_url'] : 'news.php';

	//##### CONTENT SECTION ---------------
	//content main parent
	} else if ($_POST['frontpage'] == 'content_main') {
		if($_POST['frontpage_content_main'] == ""){
			$errormsg = FRTLAN_16;
		}else{
			$tmp = explode(".", $_POST['frontpage_content_main']);
			$frontpage_value = $PLUGINS_DIRECTORY.'content/content.php?type.'.$tmp[1];
		}

	//content category
	} else if ($_POST['frontpage'] == 'content_sub') {
		$contentsubvalue = "";
		foreach ($_POST['frontpage_content_sub'] as $key => $value){
			if(!empty($value)){
				$contentsubvalue = $value;
				break;
			}
		}
		if($contentsubvalue == ""){
			$errormsg = FRTLAN_17;
		}else{
			$tmp = explode(".", $contentsubvalue);
			$frontpage_value = $PLUGINS_DIRECTORY.'content/content.php?type.'.$tmp[1].'.cat.'.$tmp[2];
		}

	//content item
	} else if ($_POST['frontpage'] == 'content_item') {
		$contentitemvalue = "";
		foreach ($_POST['frontpage_content_item'] as $key => $value){
			if(!empty($value)){
				$contentitemvalue = $value;
				break;
			}
		}
		if($contentitemvalue == ""){
			$errormsg = FRTLAN_18;
		}else{
			$tmp = explode(".", $contentitemvalue);
			$frontpage_value = $PLUGINS_DIRECTORY.'content/content.php?type.'.$tmp[1].'.content.'.$tmp[2];
		}
	}
	//##### -------------------------------

	if(!isset($errormsg)){
		$pref['frontpage'] = $frontpage_value;
		save_prefs();
		$message = TRUE;
		
		if ($pref['frontpage'] != "news") {
			if (!$sql->db_Select("links", "*", "link_url='news.php' ")) {
				$sql->db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0, 0");
			}
		} else {
			$sql->db_Delete("links", "link_url='news.php'");
		}
	}
}

require_once("auth.php");
$sql2 = new db; $sql3 = new db;
require_once(e_HANDLER."form_handler.php");
$rs = new form;

$frontpage_re = ($pref['frontpage'] ? $pref['frontpage'] : "news.php");

if(isset($errormsg)){
	$ns->tablerender("", "<div style='text-align:center'><b>".$errormsg."</b></div>");
}else{
	if (isset($message)) {
		$ns->tablerender("", "<div style='text-align:center'><b>".FRTLAN_1."</b></div>");
	}
}

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
		<td colspan='4' style='text-align:center'  class='fcaption'>".FRTLAN_2.": </td>
	</tr>
	<tr>
		<td style='width:2%;' class='forumheader3'>";
			if ($frontpage_re == "news.php") { $flag = TRUE; }
			$text .= $rs -> form_radio("frontpage", "news", ($frontpage_re == "news.php" ? "1" : "0"))."
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_3."</td>
	</tr>
	<tr>
		<td style='width:2%;' class='forumheader3'>";
			if ($frontpage_re == $PLUGINS_DIRECTORY."forum/forum.php") { $flag = TRUE; }
			$text .= $rs -> form_radio("frontpage", "forum", ($frontpage_re == $PLUGINS_DIRECTORY."forum/forum.php" ? "1" : "0"))."
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_4."</td>
	</tr>
	<tr>
		<td style='width:2%;' class='forumheader3'>";
			if ($frontpage_re == "download.php") { $flag = TRUE; }
			$text .= $rs -> form_radio("frontpage", "download", ($frontpage_re == "download.php" ? "1" : "0"))."
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_5."</td>
	</tr>
	<tr>
		<td style='width:2%;' class='forumheader3'>";
			if ($frontpage_re == $PLUGINS_DIRECTORY."links_page/links.php") { $flag = TRUE; }
			$text .= $rs -> form_radio("frontpage", "links", ($frontpage_re == $PLUGINS_DIRECTORY."links_page/links.php" ? "1" : "0"))."			
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_6."</td>
	</tr>";

	$array_mainparent = "";
	if ($sql -> db_Select("pcontent", "content_id, content_heading", "content_parent='0'")) {
		$text .= "
		<tr>
		<td style='width:2%;' class='forumheader3'>".$rs -> form_radio("frontpage", "content_main", ($flag != TRUE ? "1" : "0"))."</td>
		<td style='width:15%; white-space:nowrap;' class='forumheader3'>".FRTLAN_19."</td>
		<td style='width:83%;' colspan='2' class='forumheader3'>
		".$rs -> form_select_open("frontpage_content_main")."
		".$rs -> form_option("", "0", "");

		while ($row = $sql -> db_Fetch()) {

			if($frontpage_re == $PLUGINS_DIRECTORY."content/content.php?type.".$row['content_id']){
				$selected = "1";
				$flag = TRUE;
			}else{
				$selected = "0";
			}
			$text .= $rs -> form_option($row['content_heading'], $selected, "contentmain.".$row['content_id']);
			$array_mainparent[] = array($row['content_id'], $row['content_heading']);
		}

		$text .= $rs -> form_select_close()."
		</td>
		</tr>";
	}

	
/*

	$count = 0;
	for($i=0;$i<count($array_mainparent);$i++){
		if ($sql2->db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,".(strlen($array_mainparent[$i][0])+2).") = '0.".$array_mainparent[$i][0]."' ")) {

			if($count == 0){
				$text .= "
				<tr>
				<td style='width:2%;' class='forumheader3'>".$rs -> form_radio("frontpage", "content_sub", ($flag != TRUE ? "1" : "0"))."</td>
				<td style='width:15%; white-space:nowrap;' class='forumheader3'>".FRTLAN_20."</td>";
			}else{
				$text .= "<tr><td class='forumheader3' colspan='2'>&nbsp;</td>";
			}

			$text .= "
			<td style='width:10%; white-space:nowrap;' class='forumheader3'>".$array_mainparent[$i][1]."</td>
			<td style='width:73%; white-space:nowrap;' class='forumheader3'>
			".$rs -> form_select_open("frontpage_content_sub[{$array_mainparent[$i][0]}]")."
			".$rs -> form_option("", "0", "");

			while($row2 = $sql2->db_Fetch()){
			extract($row2);
					if($frontpage_re == $PLUGINS_DIRECTORY."content/content.php?type.".$array_mainparent[$i][0].".cat.".$content_id){
						$selected = "1";
						$flag = TRUE;
					}else{
						$selected = "0";
					}
					$text .= $rs -> form_option($row2['content_heading'], $selected, "contentsub.".$array_mainparent[$i][0].".".$row2['content_id']);
				
			}
			$text .= $rs -> form_select_close()."
			</td>
			".($count == 0 ? "</tr>" : "");

			$count = $count + 1;
		}
	}

	$count = 0;
	for($i=0;$i<count($array_mainparent);$i++){
		if($sql3->db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,".(strlen($array_mainparent[$i][0].".".$array_mainparent[$i][0])).") = '".$array_mainparent[$i][0].".".$array_mainparent[$i][0]."' ORDER BY content_heading")){

			if($count == 0){
				$text .= "
				<tr>
				<td style='width:2%;' class='forumheader3'>".$rs -> form_radio("frontpage", "content_item", ($flag != TRUE ? "1" : "0"))."</td>
				<td style='width:15%; white-space:nowrap;' class='forumheader3'>".FRTLAN_21."</td>";
			}else{
				$text .= "<tr><td class='forumheader3' colspan='2'>&nbsp;</td>";
			}

			$text .= "
			<td style='width:10%; white-space:nowrap;' class='forumheader3'>".$array_mainparent[$i][1]."</td>
			<td style='width:73%; white-space:nowrap;' class='forumheader3'>
			".$rs -> form_select_open("frontpage_content_item[{$array_mainparent[$i][0]}]")."
			".$rs -> form_option("", "0", "");

			while($row3 = $sql3->db_Fetch()){
			extract($row3);
					if($frontpage_re == $PLUGINS_DIRECTORY."content/content.php?type.".$array_mainparent[$i][0].".content.".$row3['content_id']){
						$selected = "1";
						$flag = TRUE;
					}else{
						$selected = "0";
					}
					$text .= $rs -> form_option($content_heading, $selected, "contentitem.".$array_mainparent[$i][0].".".$row3['content_id']);
			}
			$text .= $rs -> form_select_close()."
			</td>
			".($count == 0 ? "</tr>" : "");

			$count = $count + 1;
		}
	}
*/
	$text .= "
	<tr>
		<td style='width:2%;' class='forumheader3'>".$rs -> form_radio("frontpage", "other", ($flag != TRUE ? "1" : "0"))."</td>
		<td style='width:15%; white-space:nowrap;' class='forumheader3'>".FRTLAN_15."</td>
		<td style='width:83%;' colspan='2' class='forumheader3'>
			".$rs -> form_text("frontpage_url", 50, ($flag != TRUE ? $pref['frontpage'] : ""), 100, "tbox")."
		</td>
	</tr>
	<tr style='vertical-align:top'>
		<td colspan='4' style='text-align:center' class='forumheader'>
			".$rs -> form_button("submit", "updatesettings", FRTLAN_12)."
		</td>
	</tr>
</table>
</form>
</div>";
	
$ns -> tablerender(FRTLAN_13, $text);

require_once("footer.php");

?>