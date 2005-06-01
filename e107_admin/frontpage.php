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
|     $Revision: 1.17 $
|     $Date: 2005-06-01 22:59:26 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

require_once('../class2.php');
if (!getperms('G')) {
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'frontpage';
require_once('auth.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;

// update from old 6xx system
if (!$pref['frontpage']) {
	$pref['frontpage'] = 'news.php';
	save_prefs();
} else if ($pref['frontpage'] == 'links') {
	$pref['frontpage'] = $PLUGINS_DIRECTORY.'links_page/links.php';
	save_prefs();
} else if ($pref['frontpage'] == 'forum') {
	$pref['frontpage'] = $PLUGINS_DIRECTORY.'forum/forum.php';
	save_prefs();
} else if (is_numeric($pref['frontpage'])) {
	$pref['frontpage'] = $PLUGINS_DIRECTORY."content/content.php?type.".$pref['frontpage'];
	save_prefs();
} else if (strpos($pref['frontpage'], '.') === FALSE) {
	if (!preg_match("#/$#",$pref['frontpage'])) {
		$pref['frontpage'] = $pref['frontpage'].'.php';
		save_prefs();
	}
}
// end update from old 6xx system

$front_page['news'] = array('page' => 'news.php', 'title' => ADLAN_0);
$front_page['download'] = array('page' => 'download.php', 'title' => ADLAN_24);
//$front_page['your_plugin'] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php', 'title' => 'Your Plugin');
//$front_page['your_plugin']['title'] = 'Your Plugin';
//$front_page['your_plugin']['page'][] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php?1', 'title' => 'Page 1');
//$front_page['your_plugin']['page'][] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php?2', 'title' => 'Page 2');
//$front_page['your_plugin']['page'][] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php?3', 'title' => 'Page 3');

if ($sql -> db_Select("page", "*")) {
	$front_page['custom']['title'] = 'Custom Page';
	while ($row = $sql -> db_Fetch()) {
		$front_page['custom']['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']);
	}
}

if ($sql -> db_Select("plugin", "plugin_path", "plugin_installflag = '1'")) {
	while ($row = $sql -> db_Fetch()) {
		$frontpage_plugs[] = $row['plugin_path'];
	}
}

foreach ($frontpage_plugs as $plugin_id) {
	if (is_readable(e_PLUGIN.$plugin_id.'/e_frontpage.php')) {
		require_once(e_PLUGIN.$plugin_id.'/e_frontpage.php');
	}
}

if (isset($_POST['updatesettings'])) {
	if ($_POST['frontpage'] == 'other') {
		$_POST['other_page'] = $tp -> toForm($_POST['other_page']);
		$frontpage_value = $_POST['other_page'] ? $_POST['other_page'] : 'news.php';
	} else {
		if (is_array($front_page[$_POST['frontpage']]['page'])) {
			$frontpage_value = $front_page[$_POST['frontpage']]['page'][$_POST['multipage'][$_POST['frontpage']]]['page'];
		} else {
			$frontpage_value = $front_page[$_POST['frontpage']]['page'];
		}
	}
	
	$pref['frontpage'] = $frontpage_value;
	save_prefs();

	if ($pref['frontpage'] != 'news.php') {
		if (!$sql -> db_Select("links", "*", "link_url='news.php' ")) {
			$sql -> db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0, 0");
		}
	} else {
		$sql -> db_Delete("links", "link_url='news.php'");
	}
	$ns -> tablerender(LAN_UPDATED, "<div style='text-align:center'><b>".FRTLAN_1."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>
<tr>
<td colspan='3' class='fcaption'>".FRTLAN_2.": </td>
</tr>";

foreach ($front_page as $front_key => $front_value) {
	$type_selected = FALSE;
	if (is_array($front_value['page'])) {
		foreach ($front_value['page'] as $multipage) {
			if ($pref['frontpage'] == $multipage['page']) {
				$type_selected = TRUE;
				$not_other = TRUE;
			}
		}
	} else {
		if ($pref['frontpage'] == $front_value['page']) {
			$type_selected = TRUE;
			$not_other = TRUE;
		}
	}

	$text .= "<tr><td class='forumheader3'>";
	$text .= $rs -> form_radio('frontpage', $front_key, $type_selected);
	$text .= "</td>";
	
	if (is_array($front_value['page'])) {
		$text .= "<td style='width: 50%' class='forumheader3'>".$front_value['title']."</td>";
		$text .= "<td style='width: 50%' class='forumheader3'>";
		$text .= $rs -> form_select_open('multipage['.$front_key.']');
		foreach ($front_value['page'] as $multipage_key => $multipage_value) {
			$sub_selected = ($pref['frontpage'] == $multipage_value['page']) ? TRUE : FALSE;
			$text .= $rs -> form_option($multipage_value['title'], $sub_selected, $multipage_key);
		}
		$text .= $rs -> form_select_close();
		$text .= "</td>";
	} else {
		$text .= "<td style='width: 100%' colspan='2' class='forumheader3'>".$front_value['title']."</td>";
	}
	$text .= "</tr>";
}

$text .= "<tr>
<td class='forumheader3'>".$rs -> form_radio('frontpage', 'other', (!$not_other ? TRUE : FALSE))."</td>
<td style='width: 50%' class='forumheader3'>".FRTLAN_15."</td>
<td style='width: 50%' class='forumheader3'>
".$rs -> form_text('other_page', 50, (!$not_other ? $pref['frontpage'] : ''))."
</td>
</tr>";

$text .= "<tr style='vertical-align:top'>
<td colspan='3' style='text-align: center' class='forumheader'>
".$rs -> form_button('submit', 'updatesettings', FRTLAN_12)."
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(FRTLAN_13, $text);

require_once('footer.php');

?>