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
|     $Revision: 1.12 $
|     $Date: 2005-02-11 08:09:25 $
|     $Author: lisa_ $
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
		<td colspan='4' style='text-align:center'  class='forumheader'>".FRTLAN_2.": </td>
	</tr>
	<tr>
		<td style='width:2%;' class='forumheader3'>
			<input name='frontpage' type='radio' value='news'";
			if ($frontpage_re == "news.php") {
				$text .= "checked='checked'";
				$flag = TRUE;
			}
			$text .= " />
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_3."</td>
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3'>
			<input name='frontpage' type='radio' value='forum'";
			if ($frontpage_re == $PLUGINS_DIRECTORY."forum/forum.php") {
				$text .= "checked='checked'";
				$flag = TRUE;
			}
			$text .= " />
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_4."</td>
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3'>
			<input name='frontpage' type='radio' value='download'";
			if ($frontpage_re == "download.php") {
				$text .= "checked='checked'";
				$flag = TRUE;
			}
			$text .= " />
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_5."</td>
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3'>
			<input name='frontpage' type='radio' value='links'";
			if ($frontpage_re == $PLUGINS_DIRECTORY."links_page/links.php") {
				$text .= "checked='checked'";
				$flag = TRUE;
			}
			$text .= " />
		</td>
		<td style='width:95%' colspan='3' class='forumheader3'>".FRTLAN_6."</td>
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3'><input name='frontpage' type='radio' value='content_main' ".($flag != TRUE ? "checked='checked'" : "")." /></td>
		<td style='width:15%; white-space:nowrap;' class='forumheader3'>".FRTLAN_19."</td>
		<td style='width:83%;' colspan='2' class='forumheader3'>";
		
			$array_mainparent = "";
			if ($sql->db_Select("pcontent", "content_id, content_heading", "content_parent='0'")) {
				$text .= $content_heading;
				$text .= $rs -> form_select_open("frontpage_content_main");
				$text .= $rs -> form_option("", "0", "");
				while ($row = $sql->db_Fetch()) {
					extract($row);
					if($frontpage_re == $PLUGINS_DIRECTORY."content/content.php?type.".$content_id){
						$selected = "1";
						$flag = TRUE;
					}else{
						$selected = "0";
					}
					$text .= $rs -> form_option($row['content_heading'], $selected, "contentmain.".$row['content_id']);
					$array_mainparent[] = array($content_id, $content_heading);
				}
				$text .= $rs -> form_select_close();
			}

		$text .= "
		</td>
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3' rowspan='".count($array_mainparent)."'><input name='frontpage' type='radio' value='content_sub' ".($flag != TRUE ? "checked='checked'" : "")." /></td>
		<td style='width:15%; white-space:nowrap;' rowspan='".count($array_mainparent)."' class='forumheader3'>".FRTLAN_20."</td>";

			for($i=0;$i<count($array_mainparent);$i++){
				if ($sql2->db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,".(strlen($array_mainparent[$i][0])+2).") = '0.".$array_mainparent[$i][0]."' ")) {

					$text .= "
					".($i != 0 ? "<tr>" : "")."
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
					".($i == count($array_mainparent)-1 ? "</tr>" : "")."
					";
				}
			}

		$text .= "
		</td>
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3' rowspan='".count($array_mainparent)."'><input name='frontpage' type='radio' value='content_item' ".($flag != TRUE ? "checked='checked'" : "")." /></td>
		<td style='width:15%; white-space:nowrap;' rowspan='".count($array_mainparent)."' class='forumheader3'>".FRTLAN_21."</td>
		";

			for($i=0;$i<count($array_mainparent);$i++){
				if ($sql3->db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,".(strlen($array_mainparent[$i][0].".".$array_mainparent[$i][0])).") = '".$array_mainparent[$i][0].".".$array_mainparent[$i][0]."' ORDER BY content_heading")) {

					$text .= "
					".($i != 0 ? "<tr>" : "")."
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
					".($i == count($array_mainparent)-1 ? "</tr>" : "")."
					";
				}
			}

		$text .= "
		</tr>
		<tr>
		<td style='width:2%;' class='forumheader3'><input name='frontpage' type='radio' value='other' ".($flag != TRUE ? "checked='checked'" : "")." /></td>
		<td style='width:15%; white-space:nowrap;' class='forumheader3'>".FRTLAN_15."</td>
		<td style='width:83%;' colspan='2' class='forumheader3'>
			<input class='tbox' type='text' name='frontpage_url' size='50' value='".($flag != TRUE ? $pref['frontpage'] : "")."' maxlength='100' />
		</td>
		</tr>
		<tr style='vertical-align:top'>
		<td colspan='4' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='updatesettings' value='".FRTLAN_12."' />
		</td>
		</tr>
</table>
</form>
</div>";
	
$ns -> tablerender(FRTLAN_13, $text);

require_once("footer.php");

?>