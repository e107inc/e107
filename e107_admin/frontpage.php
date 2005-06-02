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
|     $Revision: 1.18 $
|     $Date: 2005-06-02 04:30:04 $
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
require_once(e_HANDLER.'userclass_class.php');

$front_page['news'] = array('page' => 'news.php', 'title' => ADLAN_0);
$front_page['download'] = array('page' => 'download.php', 'title' => ADLAN_24);

if ($sql -> db_Select('page', '*')) {
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

if (isset($_POST['edit'])) {
	$_POST['type'] = 'user_class';
	$_POST['class'] = key($_POST['edit']);
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
		
	if ($_POST['type'] == 'all_users') {
		$pref['frontpage']['all'] = $frontpage_value;
		$pref['frontpage']['252'] = $frontpage_value;
		$pref['frontpage']['253'] = $frontpage_value;
		$pref['frontpage']['254'] = $frontpage_value;
		$class_list = get_userclass_list();
		foreach ($class_list as $fp_class) {
			$pref['frontpage'][$fp_class['userclass_id']] = $frontpage_value;
		}
	} else {
		$pref['frontpage']['all'] = '';
		$pref['frontpage'][$_POST['class']] = $frontpage_value;
	}

	$match = $pref['frontpage']['252'];
	foreach ($pref['frontpage'] as $check_key => $check_value) {
		if ($check_key != 'all') {
			if ($check_value != $match) {
				$diff = TRUE;
			}
		}
	}
	
	if (!$diff) {
		$pref['frontpage']['all'] = $match;
	}

	save_prefs();
	$ns -> tablerender(LAN_UPDATED, "<div style='text-align:center'><b>".FRTLAN_1."</b></div>");
}

$fp = new frontpage;

if (isset($_POST['select']) || isset($_POST['edit'])) {
	$fp -> select_page();
} else {
	$fp -> select_class();
}

class frontpage {
	function select_class() {
		global $rs, $pref, $ns, $front_page;
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>";

		$text .= "<tr>
		<td style='width: 50%' class='forumheader3'>Set frontpage for:</td>
		<td style='width: 50%' class='forumheader3'>
		".$rs -> form_radio('type', 'all_users', ($pref['frontpage']['all'] ? TRUE : FALSE))." All Users&nbsp;
		".$rs -> form_radio('type', 'user_class', ($pref['frontpage']['all'] ? FALSE : TRUE))." User Class: 
		".r_userclass('class', '', 'off', 'guest,member,admin,classes')."</td>
		</tr>";

		$text .= "<tr style='vertical-align:top'>
		<td colspan='2' style='text-align: center' class='forumheader'>
		".$rs -> form_button('submit', 'select', LAN_SELECT)."
		</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender(FRTLAN_13, $text);
		
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'><tr>
		<td style='width: 25%' class='fcaption'>User Class</td>
		<td style='width: 65%' class='fcaption'>Page</td>
		<td style='width: 10%' class='fcaption'>Edit</td>
		</tr>";
		
		foreach ($pref['frontpage'] as $current_key => $current_value) {
			if ($current_key != 'all') {
				if ($current_key == 252) {
					$title = FRTLAN_27;
				} else if ($current_key == 253) {
					$title = FRTLAN_28;
				} else if ($current_key == 254) {
					$title = FRTLAN_29;
				} else {
					$class_list = get_userclass_list();
					foreach ($class_list as $fp_class) {
						if ($current_key == $fp_class['userclass_id']) {
							$title = $fp_class['userclass_name'];
						}
					}
				}
				$text .= "<tr>
				<td class='forumheader3'>".$title."</td>
				<td class='forumheader3'>".$current_value."</td>
				<td class='forumheader3' style='text-align:center'>
				<input type='image' title='".LAN_EDIT."' name='edit[".$current_key."]' src='".ADMIN_EDIT_ICON_PATH."' />
				</td>
				</tr>";
			}
		}

		$text .= "</table>
		</form>
		</div>";

		$ns -> tablerender('Current Settings', $text);
		
	}
	
	function select_page() {
		global $rs, $pref, $ns, $front_page;
		
		if ($_POST['type'] == 'all_users') {
			$title = FRTLAN_26;
		} else {
			if ($_POST['class'] == 252) {
				$title = FRTLAN_27;
			} else if ($_POST['class'] == 253) {
				$title = FRTLAN_28;
			} else if ($_POST['class'] == 254) {
				$title = FRTLAN_29;
			} else {
				$class_list = get_userclass_list();
				foreach ($class_list as $fp_class) {
					if ($_POST['class'] == $fp_class['userclass_id']) {
						$title = $fp_class['userclass_name'];
					}
				}
			}
		}
		
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td colspan='3' class='fcaption'>".FRTLAN_2." ".$title.": </td>
		</tr>";
		
		foreach ($front_page as $front_key => $front_value) {
			$type_selected = FALSE;
			if (is_array($front_value['page'])) {
				foreach ($front_value['page'] as $multipage) {
					if ($pref['frontpage'][$_POST['class']] == $multipage['page']) {
						$type_selected = TRUE;
						$not_other = TRUE;
					}
				}
			} else {
				if ($pref['frontpage'][$_POST['class']] == $front_value['page']) {
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
					$sub_selected = ($pref['frontpage'][$_POST['class']] == $multipage_value['page']) ? TRUE : FALSE;
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
		".$rs -> form_text('other_page', 50, (!$not_other ? $pref['frontpage'][$_POST['class']] : ''))."
		</td>
		</tr>";
		
		$text .= "<tr style='vertical-align:top'>
		<td colspan='3' style='text-align: center' class='forumheader'>";
		$text .= $rs -> form_hidden('type', $_POST['type']);
		$text .= $rs -> form_hidden('class', $_POST['class']);
		$text .= $rs -> form_button('submit', 'updatesettings', FRTLAN_12);
		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";
		
		$ns -> tablerender(FRTLAN_13, $text);
	}
}

require_once('footer.php');

//$front_page['your_plugin'] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php', 'title' => 'Your Plugin');
//$front_page['your_plugin']['title'] = 'Your Plugin';
//$front_page['your_plugin']['page'][] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php?1', 'title' => 'Page 1');
//$front_page['your_plugin']['page'][] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php?2', 'title' => 'Page 2');
//$front_page['your_plugin']['page'][] = array('page' => $PLUGINS_DIRECTORY.'your_plugin/page.php?3', 'title' => 'Page 3');


?>