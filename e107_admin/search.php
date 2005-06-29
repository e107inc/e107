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
|     $Source: /cvs_backup/e107_0.7/e107_admin/search.php,v $
|     $Revision: 1.23 $
|     $Date: 2005-06-29 20:46:14 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("X")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'search';
require_once("auth.php");
require_once(e_HANDLER.'userclass_class.php');
$search_prefs = $sysprefs -> getArray('search_prefs');

$search_handlers['news'] = ADLAN_0;
$search_handlers['comments'] = SEALAN_6;
$search_handlers['users'] = SEALAN_7;
$search_handlers['downloads'] = ADLAN_24;
$search_handlers['pages'] = SEALAN_39;

preg_match("/^(.*?)($|-)/", mysql_get_server_info(), $mysql_version);
if (version_compare($mysql_version[1], '4.0.1', '<')) {
	$mysql_supported = false;
} else {
	$mysql_supported = true;
}

if (isset($_POST['updatesettings'])) {
	$search_prefs['relevance'] = $_POST['relevance'];
	$search_prefs['user_select'] = $_POST['user_select'];
	$search_prefs['multisearch'] = $_POST['multisearch'];
	$search_prefs['selector'] = $_POST['selector'];
	$search_prefs['time_restrict'] = $_POST['time_restrict'];
	$search_prefs['time_secs'] = $_POST['time_secs'] > 300 ? 300 : $tp -> toDB($_POST['time_secs']);
	if ($_POST['search_sort'] == 'mysql') {
		if ($mysql_supported) {
			$search_prefs['mysql_sort'] = TRUE;
		} else {
			$search_prefs['mysql_sort'] = FALSE;
			$message = SEALAN_33."<br />".SEALAN_34." ".$mysql_version[1];
		}
	} else {
		$search_prefs['mysql_sort'] = FALSE;
	}
	$search_prefs['php_limit'] = $tp -> toDB($_POST['php_limit']);
	$search_prefs['google'] = $_POST['google'];

	foreach($search_handlers as $s_key => $s_value) {
		$search_prefs['core_handlers'][$s_key]['class'] = $_POST['core_handlers'][$s_key]['class'];
		$search_prefs['core_handlers'][$s_key]['chars'] = $tp -> toDB($_POST['core_handlers'][$s_key]['chars']);
		$search_prefs['core_handlers'][$s_key]['results'] = $tp -> toDB($_POST['core_handlers'][$s_key]['results']);
		$search_prefs['core_handlers'][$s_key]['pre_title'] = $_POST['core_handlers'][$s_key]['pre_title'];
		$search_prefs['core_handlers'][$s_key]['pre_title_alt'] = $tp -> toDB($_POST['core_handlers'][$s_key]['pre_title_alt']);
		$search_prefs['core_handlers'][$s_key]['order'] = $_POST['core_handlers'][$s_key]['order'];
	}

	foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
		$search_prefs['plug_handlers'][$plug_dir]['class'] = $_POST['plug_handlers'][$plug_dir]['class'];
		$search_prefs['plug_handlers'][$plug_dir]['chars'] = $tp -> toDB($_POST['plug_handlers'][$plug_dir]['chars']);
		$search_prefs['plug_handlers'][$plug_dir]['results'] = $tp -> toDB($_POST['plug_handlers'][$plug_dir]['results']);
		$search_prefs['plug_handlers'][$plug_dir]['pre_title'] = $_POST['plug_handlers'][$plug_dir]['pre_title'];
		$search_prefs['plug_handlers'][$plug_dir]['pre_title_alt'] = $tp -> toDB($_POST['plug_handlers'][$plug_dir]['pre_title_alt']);
		$search_prefs['plug_handlers'][$plug_dir]['order'] = $_POST['plug_handlers'][$plug_dir]['order'];
	}

	foreach ($search_prefs['comments_handlers'] as $key => $value) {
		$search_prefs['comments_handlers'][$key]['class'] = $_POST['comments_handlers'][$key]['class'];
	}

	$tmp = addslashes(serialize($search_prefs));
	if ($sql -> db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs'")) {
		$message = $message ? $message : LAN_UPDATED;
	} else {
		$message = $message ? $message : 'failed';
	}
	
	$pref['search_restrict'] = $_POST['search_restrict'];
	$pref['search_highlight'] = $_POST['search_highlight'];
	save_prefs();
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$handlers_total = count($search_prefs['core_handlers']) + count($search_prefs['plug_handlers']);

if (isset($message)) {
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<form method='post' action='".e_SELF."'><div style='text-align:center'>
<table style='".ADMIN_WIDTH."' class='fborder'>";

$text .= "<tr>
<td class='fcaption' colspan='2'>".SEALAN_20."</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_15.": </td>
<td style='width:50%' class='forumheader3'>
".r_userclass("search_restrict", $pref['search_restrict'], "off", "public,guest,nobody,member,admin,classes")."
</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_30."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='search_highlight' value='1'".($pref['search_highlight'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
<input type='radio' name='search_highlight' value='0'".(!$pref['search_highlight'] ? " checked='checked'" : "")." /> ".SEALAN_17."
</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_10."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='relevance' value='1'".($search_prefs['relevance'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
<input type='radio' name='relevance' value='0'".(!$search_prefs['relevance'] ? " checked='checked'" : "")." /> ".SEALAN_17."
</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_11."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='user_select' value='1'".($search_prefs['user_select'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
<input type='radio' name='user_select' value='0'".(!$search_prefs['user_select'] ? " checked='checked'" : "")." /> ".SEALAN_17."
</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_19."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='multisearch' value='1'".($search_prefs['multisearch'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
<input type='radio' name='multisearch' value='0'".(!$search_prefs['multisearch'] ? " checked='checked'" : "")." /> ".SEALAN_17."
</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_35."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='selector' value='2'".($search_prefs['selector'] == '2' ? " checked='checked'" : "")." /> ".SEALAN_36."&nbsp;&nbsp;
<input type='radio' name='selector' value='1'".($search_prefs['selector'] == '1' ? " checked='checked'" : "")." /> ".SEALAN_37."&nbsp;&nbsp;
<input type='radio' name='selector' value='0'".($search_prefs['selector'] == '0' ? " checked='checked'" : "")." /> ".SEALAN_38."
</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_12."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='time_restrict' value='0'".(!$search_prefs['time_restrict'] ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
<input type='radio' name='time_restrict' value='1'".($search_prefs['time_restrict'] ? " checked='checked'" : "")." />
".SEALAN_13." ".$rs -> form_text("time_secs", 3, $tp -> toForm($search_prefs['time_secs']), 3)." ".SEALAN_14."</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_3."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
".$rs -> form_radio('search_sort', 'mysql', ($search_prefs['mysql_sort'] == TRUE ? 1 : 0), 'MySql', ($mysql_supported ? "" : "disabled='true'"))."MySql
".$rs -> form_radio('search_sort', 'php', ($search_prefs['mysql_sort'] == TRUE ? 0 : 1)).SEALAN_31." 
".$rs -> form_text("php_limit", 5, $tp -> toForm($search_prefs['php_limit']), 5)." ".SEALAN_32." 
</td>
</tr>";

$text .= "<tr>
<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "updatesettings", LAN_UPDATE)."</td>
</tr>";

$text .= "</table>
</div><br />";

$text .= "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."' class='fborder'>";

$text .= "<tr>
<td class='fcaption' colspan='6'>".SEALAN_21."</td>
</tr>";

$text .= "<tr>
<td class='forumheader'>".SEALAN_24."</td>
<td class='forumheader'>".SEALAN_25."</td>
<td class='forumheader'>".SEALAN_27."</td>
<td class='forumheader'>".SEALAN_28."</td>
<td class='forumheader'>".SEALAN_26."</td>
<td class='forumheader'>".LAN_ORDER."</td>
</tr>";

foreach($search_handlers as $key => $value) {
	$text .= "<tr>
	<td style='width:40%; white-space:nowrap' class='forumheader3'>".$value."</td>
	<td style='width:10%' class='forumheader3'>";
	$text .= r_userclass("core_handlers[".$key."][class]", $search_prefs['core_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes");
	$text .= "</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("core_handlers[".$key."][chars]", 4, $tp -> toForm($search_prefs['core_handlers'][$key]['chars']), 4)."</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("core_handlers[".$key."][results]", 4, $tp -> toForm($search_prefs['core_handlers'][$key]['results']), 4)."</td>
	<td style='width:35%; text-align: center; white-space:nowrap' class='forumheader3'>
	<input type='radio' name='core_handlers[".$key."][pre_title]' value='1'".(($search_prefs['core_handlers'][$key]['pre_title'] == 1) ? " checked='checked'" : "")." /> ".SEALAN_22."&nbsp;&nbsp;
	<input type='radio' name='core_handlers[".$key."][pre_title]' value='0'".(($search_prefs['core_handlers'][$key]['pre_title'] == 0) ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
	<input type='radio' name='core_handlers[".$key."][pre_title]' value='2'".(($search_prefs['core_handlers'][$key]['pre_title'] == 2) ? " checked='checked'" : "")." /> ".SEALAN_23."&nbsp;&nbsp;
	".$rs -> form_text("core_handlers[".$key."][pre_title_alt]", 20, $tp -> toForm($search_prefs['core_handlers'][$key]['pre_title_alt']))."
	</td>";
	$text .= "<td style='width:5%; text-align:center' class='forumheader3'>";
	$text .= "<select name='core_handlers[".$key."][order]' class='tbox'>";
	for($a = 1; $a <= $handlers_total; $a++) {
		$text .= ($search_prefs['core_handlers'][$key]['order'] == $a) ? "<option value='".$a."' selected='selected'>".$a."</option>" : "<option value='".$a."'>".$a."</option>";
	}
	$text .= "</select>";
	$text .= "</td>";
	$text .= "</tr>";
}

foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
	require_once(e_PLUGIN.$plug_dir."/e_search.php");
	$text .= "<tr>
	<td style='width:40%; white-space:nowrap' class='forumheader3'>".$search_info[0]['qtype']."</td>
	<td style='width:10%' class='forumheader3'>";
	$text .= r_userclass("plug_handlers[".$plug_dir."][class]", $search_prefs['plug_handlers'][$plug_dir]['class'], "off", "public,guest,nobody,member,admin,classes");
	unset($search_info);
	$text .= "</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("plug_handlers[".$plug_dir."][chars]", 4, $tp -> toForm($search_prefs['plug_handlers'][$plug_dir]['chars']), 4)."</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("plug_handlers[".$plug_dir."][results]", 4, $tp -> toForm($search_prefs['plug_handlers'][$plug_dir]['results']), 4)."</td>
	<td style='width:35%; text-align: center' class='forumheader3'>
	<input type='radio' name='plug_handlers[".$plug_dir."][pre_title]' value='1'".(($search_prefs['plug_handlers'][$plug_dir]['pre_title'] == 1) ? " checked='checked'" : "")." /> ".SEALAN_22."&nbsp;&nbsp;
	<input type='radio' name='plug_handlers[".$plug_dir."][pre_title]' value='0'".(($search_prefs['plug_handlers'][$plug_dir]['pre_title'] == 0) ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
	<input type='radio' name='plug_handlers[".$plug_dir."][pre_title]' value='2'".(($search_prefs['plug_handlers'][$plug_dir]['pre_title'] == 2) ? " checked='checked'" : "")." /> ".SEALAN_23."&nbsp;&nbsp;
	".$rs -> form_text("plug_handlers[".$plug_dir."][pre_title_alt]", 20, $tp -> toForm($search_prefs['plug_handlers'][$plug_dir]['pre_title_alt']))."
	</td>";
	$text .= "<td style='width:5%; text-align:center' class='forumheader3'>";
	$text .= "<select name='plug_handlers[".$plug_dir."][order]' class='tbox'>";
	for($a = 1; $a <= $handlers_total; $a++) {
		$text .= ($search_prefs['plug_handlers'][$plug_dir]['order'] == $a) ? "<option value='".$a."' selected='selected'>".$a."</option>" : "<option value='".$a."'>".$a."</option>";
	}
	$text .= "</select>";
	$text .= "</td>";
	$text .= "</tr>";
}

$text .= "<tr>
<td style='width:40%; white-space:nowrap' class='forumheader3'>Google</td>
<td style='width:55%' colspan='5' class='forumheader3'>";
$sel = (isset($search_prefs['google']) && $search_prefs['google']) ? " checked='checked'" : "";
$text .= r_userclass("google", $search_prefs['google'], "off", "public,guest,nobody,member,admin,classes");
$text .= "</td>
</tr>";

$text .= "<tr>
<td colspan='6' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "updatesettings", LAN_UPDATE)."</td>
</tr>";

$text .= "</table>
</div><br />";

$text .= "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."' class='fborder'>";

$text .= "<tr>
<td class='fcaption' colspan='6'>".SEALAN_18."</td>
</tr>";

$text .= "<tr>
<td class='forumheader'>".SEALAN_24."</td>
<td class='forumheader' colspan='5'>".SEALAN_25."</td>
</tr>";

foreach ($search_prefs['comments_handlers'] as $key => $value) {
	$path = ($value['dir'] == 'core') ? e_HANDLER.'search/comments_'.$key.'.php' : e_PLUGIN.$value['dir'].'/search/search_comments.php';
	require_once($path);
	$text .= "<tr>
	<td style='width:40%; white-space:nowrap' class='forumheader3'>".$comments_title."</td>
	<td style='width:60%;' colspan='4' class='forumheader3'>";
	$text .= r_userclass("comments_handlers[".$key."][class]", $search_prefs['comments_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes");
	$text .= "</td>
	</tr>";
	unset($comments_title);
}

$text .= "<tr>
<td colspan='5' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "updatesettings", LAN_UPDATE)."</td>
</tr>";

$text .= "</table>
</div>
</form>";

$ns -> tablerender(SEALAN_1, $text);

require_once("footer.php");

?>