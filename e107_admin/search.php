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
|     $Revision: 1.11 $
|     $Date: 2005-03-22 13:42:19 $
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

$search_handlers['news'] = SEALAN_5;
$search_handlers['comments'] = SEALAN_6;
$search_handlers['users'] = SEALAN_7;
$search_handlers['downloads'] = SEALAN_8;

if (isset($_POST['updatesettings'])) {
	$pref['search_restrict'] = $_POST['search_restrict'];
	save_prefs();
	$search_prefs['search_sort'] = $_POST['search_sort'];
	$search_prefs['relevance'] = $_POST['relevance'];
	$search_prefs['user_select'] = $_POST['user_select'];
	$search_prefs['multisearch'] = $_POST['multisearch'];
	$search_prefs['time_restrict'] = $_POST['time_restrict'];
	$search_prefs['time_secs'] = $_POST['time_secs'] > 300 ? 300 : $_POST['time_secs'];
	$search_prefs['google'] = $_POST['google'];
	
	foreach($search_handlers as $s_key => $s_value) {
		$search_prefs['core_handlers'][$s_key]['class'] = $_POST['core_handlers'][$s_key]['class'];
		$search_prefs['core_handlers'][$s_key]['chars'] = $_POST['core_handlers'][$s_key]['chars'];
		$search_prefs['core_handlers'][$s_key]['results'] = $tp -> toDB($_POST['core_handlers'][$s_key]['results']);
		$search_prefs['core_handlers'][$s_key]['pre_title'] = $_POST['core_handlers'][$s_key]['pre_title'];
		$search_prefs['core_handlers'][$s_key]['pre_title_alt'] = $tp -> toDB($_POST['core_handlers'][$s_key]['pre_title_alt']);
	}

	foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
		$search_prefs['plug_handlers'][$plug_dir]['class'] = $_POST['plug_handlers'][$plug_dir]['class'];
		$search_prefs['plug_handlers'][$plug_dir]['chars'] = $_POST['plug_handlers'][$plug_dir]['chars'];
		$search_prefs['plug_handlers'][$plug_dir]['results'] = $tp -> toDB($_POST['plug_handlers'][$plug_dir]['results']);
		$search_prefs['plug_handlers'][$plug_dir]['pre_title'] = $_POST['plug_handlers'][$plug_dir]['pre_title'];
		$search_prefs['plug_handlers'][$plug_dir]['pre_title_alt'] = $tp -> toDB($_POST['plug_handlers'][$plug_dir]['pre_title_alt']);
	}
	
	foreach ($search_prefs['comments_handlers'] as $key => $value) {
		$search_prefs['comments_handlers'][$key]['class'] = $_POST['comments_handlers'][$key]['class'];
	}

	$tmp = addslashes(serialize($search_prefs));
	$sql->db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs' ");
	$message = TRUE;
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

if (isset($message)) {
		$ns->tablerender("", "<div style='text-align:center'><b>".LAN_UPDATED."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
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
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_3."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>".$rs -> form_radio('search_sort', 'php', ($search_prefs['search_sort'] == 'php' ? 1 : 0))."PHP".$rs -> form_radio('search_sort', 'mysql', ($search_prefs['search_sort'] == 'mysql' ? 1 : 0))."MySql</td>
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
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_12."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>
<input type='radio' name='time_restrict' value='0'".(!$search_prefs['time_restrict'] ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
<input type='radio' name='time_restrict' value='1'".($search_prefs['time_restrict'] ? " checked='checked'" : "")." /> 
".SEALAN_13." ".$rs -> form_text("time_secs", 3, $search_prefs['time_secs'], 3)." ".SEALAN_14."</td>
</tr>";

$text .= "<tr>
<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "updatesettings", LAN_UPDATE)."</td>
</tr>";

$text .= "</table>
</div>";

$text .= "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."' class='fborder'>";

$text .= "<tr>
<td class='fcaption' colspan='5'>".SEALAN_21."</td>
</tr>";

$text .= "<tr>
<td class='forumheader'>".SEALAN_24."</td>
<td class='forumheader'>".SEALAN_25."</td>
<td class='forumheader'>".SEALAN_27."</td>
<td class='forumheader'>".SEALAN_28."</td>
<td class='forumheader'>".SEALAN_26."</td>
</tr>";

foreach($search_handlers as $key => $value) {
	$text .= "<tr>
	<td style='width:45%; white-space:nowrap' class='forumheader3'>".$value."</td>
	<td style='width:10%' class='forumheader3'>";
	$text .= r_userclass("core_handlers[".$key."][class]", $search_prefs['core_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes");
	$text .= "</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("core_handlers[".$key."][chars]", 4, $search_prefs['core_handlers'][$key]['chars'], 4)."</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("core_handlers[".$key."][results]", 4, $search_prefs['core_handlers'][$key]['results'], 4)."</td>
	<td style='width:35%; text-align: center; white-space:nowrap' class='forumheader3'>
	<input type='radio' name='core_handlers[".$key."][pre_title]' value='1'".(($search_prefs['core_handlers'][$key]['pre_title'] == 1) ? " checked='checked'" : "")." /> ".SEALAN_22."&nbsp;&nbsp;
	<input type='radio' name='core_handlers[".$key."][pre_title]' value='0'".(($search_prefs['core_handlers'][$key]['pre_title'] == 0) ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
	<input type='radio' name='core_handlers[".$key."][pre_title]' value='2'".(($search_prefs['core_handlers'][$key]['pre_title'] == 2) ? " checked='checked'" : "")." /> ".SEALAN_23."&nbsp;&nbsp;
	".$rs -> form_text("core_handlers[".$key."][pre_title_alt]", 20, $tp -> toForm($search_prefs['core_handlers'][$key]['pre_title_alt']))."
	</td>
	</tr>";
}

foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
	require_once(e_PLUGIN.$plug_dir."/e_search.php");
	$text .= "<tr>
	<td style='width:45%; white-space:nowrap' class='forumheader3'>".$search_info[0]['qtype']."</td>
	<td style='width:10%' class='forumheader3'>";
	$text .= r_userclass("plug_handlers[".$plug_dir."][class]", $search_prefs['plug_handlers'][$plug_dir]['class'], "off", "public,guest,nobody,member,admin,classes");
	unset($search_info);
	$text .= "</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("plug_handlers[".$plug_dir."][chars]", 4, $search_prefs['plug_handlers'][$plug_dir]['chars'], 4)."</td>
	<td style='width:5%; text-align: center' class='forumheader3'>".$rs -> form_text("plug_handlers[".$plug_dir."][results]", 4, $search_prefs['plug_handlers'][$plug_dir]['results'], 4)."</td>
	<td style='width:35%; text-align: center' class='forumheader3'>
	<input type='radio' name='plug_handlers[".$plug_dir."][pre_title]' value='1'".(($search_prefs['plug_handlers'][$plug_dir]['pre_title'] == 1) ? " checked='checked'" : "")." /> ".SEALAN_22."&nbsp;&nbsp;
	<input type='radio' name='plug_handlers[".$plug_dir."][pre_title]' value='0'".(($search_prefs['plug_handlers'][$plug_dir]['pre_title'] == 0) ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
	<input type='radio' name='plug_handlers[".$plug_dir."][pre_title]' value='2'".(($search_prefs['plug_handlers'][$plug_dir]['pre_title'] == 2) ? " checked='checked'" : "")." /> ".SEALAN_23."&nbsp;&nbsp;
	".$rs -> form_text("plug_handlers[".$plug_dir."][pre_title_alt]", 20, $tp -> toForm($search_prefs['plug_handlers'][$plug_dir]['pre_title_alt']))."
	</td>
	</tr>";
}

$text .= "<tr>
<td style='width:45%; white-space:nowrap' class='forumheader3'>Google</td>
<td style='width:55%' colspan='4' class='forumheader3'>";
$sel = (isset($search_prefs['google']) && $search_prefs['google']) ? " checked='checked'" : "";
$text .= r_userclass("google", $search_prefs['google'], "off", "public,guest,nobody,member,admin,classes");
$text .= "</td>
</tr>";

$text .= "<tr>
<td class='fcaption' colspan='5'>".SEALAN_18."</td>
</tr>";

$text .= "<tr>
<td class='forumheader'>".SEALAN_24."</td>
<td class='forumheader' colspan='4'>".SEALAN_25."</td>
</tr>";

foreach ($search_prefs['comments_handlers'] as $key => $value) {
	$path = ($value['dir'] == 'core') ? e_HANDLER.'search/comments_'.$key.'.php' : e_PLUGIN.$value['dir'].'/comments_search.php';
	require_once($path);
	$text .= "<tr>
	<td style='width:45%; white-space:nowrap' class='forumheader3'>".$comments_title."</td>
	<td style='width:55%;' colspan='4' class='forumheader3'>";
	$text .= r_userclass("comments_handlers[".$key."][class]", $search_prefs['comments_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes");
	$text .= "</td>
	</tr>";
	unset($comments_title);
}

$text .= "<tr>
<td colspan='5' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "updatesettings", LAN_UPDATE)."</td>
</tr>";

$text .= "</table>
</form>
</div>";

$ns -> tablerender(SEALAN_1, $text);

require_once("footer.php");

?>