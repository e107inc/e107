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
|     $Revision: 1.9 $
|     $Date: 2005-02-01 03:01:04 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("G")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'frontpage';

// update from old 6xx system

if ($pref['frontpage'] == 'links') {
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
	}
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


require_once("auth.php");
	
$frontpage_re = ($pref['frontpage'] ? $pref['frontpage'] : "news.php");	
	
if ($message) {
	$ns->tablerender("", "<div style='text-align:center'><b>".FRTLAN_1."</b></div>");
}
	
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	 
	<td style='width:30%' class='forumheader3'>".FRTLAN_2.": </td>
	<td style='width:70%' class='forumheader3'>
	 
	 
	<input name='frontpage' type='radio' value='news'";
if ($frontpage_re == "news.php") {
	$text .= "checked='checked'";
	$flag = TRUE;
}
$text .= " />".FRTLAN_3."<br />
	<input name='frontpage' type='radio' value='forum'";
if ($frontpage_re == $PLUGINS_DIRECTORY."forum/forum.php") {
	$text .= "checked='checked'";
	$flag = TRUE;
}
$text .= " />".FRTLAN_4."<br />
	<input name='frontpage' type='radio' value='download'";
if ($frontpage_re == "download.php") {
	$text .= "checked='checked'";
	$flag = TRUE;
}
$text .= " />".FRTLAN_5."<br />
	<input name='frontpage' type='radio' value='links'";
if ($frontpage_re == $PLUGINS_DIRECTORY."links_page/links.php") {
	$text .= "checked='checked'";
	$flag = TRUE;
}
$text .= " />".FRTLAN_6."<br />";
	
if ($sql->db_Select("content", "*", "content_type='1'")) {
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$text .= "<input name='frontpage' type='radio' value='".$content_id."'";
		if ($frontpage_re == "content.php?content.".$content_id) {
			$text .= "checked='checked'";
			$flag = TRUE;
		}
		$text .= " />".FRTLAN_7.": ".$content_heading."/".$content_subheading."<br />";
	}
}
	
$text .= "
	<input name='frontpage' type='radio' value='other'";
if ($flag != TRUE) {
	$text .= "checked='checked'";
}
	
$text .= " />".FRTLAN_15."
	&nbsp;<input class='tbox' type='text' name='frontpage_url' size='50' value='";
if ($flag != TRUE) {
	$text .= $pref['frontpage'];
}
	
$text .= "' maxlength='100' />
	</td>
	</tr>
	 

	 
	<tr style='vertical-align:top'>
	<td colspan='2'  style='text-align:center'  class='forumheader'>
	<input class='button' type='submit' name='updatesettings' value='".FRTLAN_12."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";
	
$ns->tablerender(FRTLAN_13, $text);
require_once("footer.php");
	
?>