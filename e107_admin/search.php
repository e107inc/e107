<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
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

require_once('../class2.php');
if (!getperms('X')) {
	header('location:'.e_BASE.'index.php');
	exit;
}
$e_sub_cat = 'search';

$query = explode('.', e_QUERY); // must be set before auth.php is loaded. 

require_once('auth.php');
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

foreach($pref['e_search_list'] as $file)
{

	if (is_readable(e_PLUGIN.$file."/e_search.php") && !isset($search_prefs['plug_handlers'][$file]))
	{
		$search_prefs['plug_handlers'][$file] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
		$save_search = TRUE;
	}
	if (is_readable(e_PLUGIN.$file.'/search/search_comments.php') && !isset($search_prefs['comments_handlers'][$file]))
	{
		include_once(e_PLUGIN.$file.'/search/search_comments.php');
		$search_prefs['comments_handlers'][$file] = array('id' => $comments_type_id, 'class' => '0', 'dir' => $file);
		unset($comments_type_id);
		$save_search = TRUE;
	}

}



if (!isset($search_prefs['boundary'])) {
	$search_prefs['boundary'] = 1;
	$save_search = TRUE;
}

if ($save_search) {
	$serialpref = addslashes(serialize($search_prefs));
	$sql -> db_Update("core", "e107_value='".$serialpref."' WHERE e107_name='search_prefs'");
}

if (isset($_POST['update_main'])) {
	foreach($search_handlers as $s_key => $s_value) {
		$search_prefs['core_handlers'][$s_key]['class'] = $_POST['core_handlers'][$s_key]['class'];
		$search_prefs['core_handlers'][$s_key]['order'] = $_POST['core_handlers'][$s_key]['order'];
	}

	foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
		$search_prefs['plug_handlers'][$plug_dir]['class'] = $_POST['plug_handlers'][$plug_dir]['class'];
		$search_prefs['plug_handlers'][$plug_dir]['order'] = $_POST['plug_handlers'][$plug_dir]['order'];
	}

	foreach ($search_prefs['comments_handlers'] as $key => $value) {
		$search_prefs['comments_handlers'][$key]['class'] = $_POST['comments_handlers'][$key]['class'];
	}

	$search_prefs['google'] = $_POST['google'];

	$tmp = addslashes(serialize($search_prefs));
	admin_update($sql -> db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs'"));
}

if (isset($_POST['update_handler'])) {
	if ($query[1] == 'c') {
		$handler_type = 'core_handlers';
	} else if ($query[1] == 'p') {
		$handler_type = 'plug_handlers';
	}
	$search_prefs[$handler_type][$query[2]]['class'] = $_POST['class'];
	$search_prefs[$handler_type][$query[2]]['chars'] = $tp -> toDB($_POST['chars']);
	$search_prefs[$handler_type][$query[2]]['results'] = $tp -> toDB($_POST['results']);
	$search_prefs[$handler_type][$query[2]]['pre_title'] = $_POST['pre_title'];
	$search_prefs[$handler_type][$query[2]]['pre_title_alt'] = $tp -> toDB($_POST['pre_title_alt']);

	$tmp = addslashes(serialize($search_prefs));
	admin_update($sql -> db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs'"));
}

if (isset($_POST['update_prefs'])) {
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
			$ns -> tablerender(LAN_ERROR, "<div style='text-align:center'><b>".SEALAN_33."<br />".SEALAN_34." ".$mysql_version[1]."</b></div>");
		}
	} else {
		$search_prefs['mysql_sort'] = FALSE;
	}
	$search_prefs['php_limit'] = $tp -> toDB($_POST['php_limit']);
	$search_prefs['boundary'] = $_POST['boundary'];

	$tmp = addslashes(serialize($search_prefs));
	admin_update($sql -> db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs'"));

	$pref['search_restrict'] = $_POST['search_restrict'];
	$pref['search_highlight'] = $_POST['search_highlight'];
	save_prefs();
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$handlers_total = count($search_prefs['core_handlers']) + count($search_prefs['plug_handlers']);

if ($query[0] == 'settings') {
	$text = "<form method='post' action='".e_SELF."?settings'><div style='text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	$text .= "<tr>
	<td class='fcaption' colspan='2'>".SEALAN_20."</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_15.": </td>
	<td style='width:50%' class='forumheader3'>
	".r_userclass("search_restrict", $pref['search_restrict'], "off", "public,guest,nobody,member,admin,main,classes")."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_30."</td>
	<td style='width:50%;' colspan='2' class='forumheader3'>
	<input type='radio' name='search_highlight' value='1'".($pref['search_highlight'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
	<input type='radio' name='search_highlight' value='0'".(!$pref['search_highlight'] ? " checked='checked'" : "")." /> ".SEALAN_17."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_10."</td>
	<td style='width:50%;' colspan='2' class='forumheader3'>
	<input type='radio' name='relevance' value='1'".($search_prefs['relevance'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
	<input type='radio' name='relevance' value='0'".(!$search_prefs['relevance'] ? " checked='checked'" : "")." /> ".SEALAN_17."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_11."</td>
	<td style='width:50%;' colspan='2' class='forumheader3'>
	<input type='radio' name='user_select' value='1'".($search_prefs['user_select'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
	<input type='radio' name='user_select' value='0'".(!$search_prefs['user_select'] ? " checked='checked'" : "")." /> ".SEALAN_17."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_19."</td>
	<td style='width:50%;' colspan='2' class='forumheader3'>
	<input type='radio' name='multisearch' value='1'".($search_prefs['multisearch'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
	<input type='radio' name='multisearch' value='0'".(!$search_prefs['multisearch'] ? " checked='checked'" : "")." /> ".SEALAN_17."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_35."</td>
	<td style='width:50%;' colspan='2' class='forumheader3'>
	<input type='radio' name='selector' value='2'".($search_prefs['selector'] == '2' ? " checked='checked'" : "")." /> ".SEALAN_36."&nbsp;&nbsp;
	<input type='radio' name='selector' value='1'".($search_prefs['selector'] == '1' ? " checked='checked'" : "")." /> ".SEALAN_37."&nbsp;&nbsp;
	<input type='radio' name='selector' value='0'".($search_prefs['selector'] == '0' ? " checked='checked'" : "")." /> ".SEALAN_38."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_12."</td>
	<td style='width:50%' colspan='2' class='forumheader3'>
	<input type='radio' name='time_restrict' value='0'".(!$search_prefs['time_restrict'] ? " checked='checked'" : "")." /> ".SEALAN_17."&nbsp;&nbsp;
	<input type='radio' name='time_restrict' value='1'".($search_prefs['time_restrict'] ? " checked='checked'" : "")." />
	".SEALAN_13." ".$rs -> form_text("time_secs", 3, $tp -> toForm($search_prefs['time_secs']), 3)." ".SEALAN_14."</td>
	</tr>";

	$text .= "<tr>
	<td class='forumheader3' style='width:50%'>".SEALAN_3."<br />".SEALAN_49."</td>
	<td colspan='2' class='forumheader3' style='width:50%'>
	".$rs -> form_radio('search_sort', 'mysql', ($search_prefs['mysql_sort'] == TRUE ? 1 : 0), 'MySql', ($mysql_supported ? "" : "disabled='true'"))."MySql<br />
	".$rs -> form_radio('search_sort', 'php', ($search_prefs['mysql_sort'] == TRUE ? 0 : 1)).SEALAN_31."
	".$rs -> form_text("php_limit", 5, $tp -> toForm($search_prefs['php_limit']), 5)." ".SEALAN_32."
	</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_47."<br />".SEALAN_48."</td>
	<td style='width:50%;' colspan='2' class='forumheader3'>
	<input type='radio' name='boundary' value='1'".($search_prefs['boundary'] ? " checked='checked'" : "")." /> ".SEALAN_16."&nbsp;&nbsp;
	<input type='radio' name='boundary' value='0'".(!$search_prefs['boundary'] ? " checked='checked'" : "")." /> ".SEALAN_17."
	</td>
	</tr>";

	$text .= "<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "update_prefs", LAN_UPDATE)."</td>
	</tr>";

	$text .= "</table>
	</div></form>";

} else if ($query[0] == 'edit') {
	if ($query[1] == 'c') {
		$handlers = $search_handlers;
		$handler_type = 'core_handlers';
	} else if ($query[1] == 'p') {
		$handlers = $search_prefs['plug_handlers'];
		$handler_type = 'plug_handlers';
	}

	$text = "<form method='post' action='".e_SELF."?main.".$query[1].".".$query[2]."'>
	<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	$text .= "<tr>
	<td class='fcaption' colspan='2'>".SEALAN_43.": ".$handlers[$query[2]]."</td>
	</tr>";

	$text .= "<tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_44.":</td>
	<td style='width:50%' class='forumheader3'>";
	$text .= r_userclass("class", $search_prefs[$handler_type][$query[2]]['class'], "off", "public,guest,nobody,member,admin,main,classes");
	$text .= "</td>
	</tr><tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_45.":</td>
	<td style='width:5%' class='forumheader3'>".$rs -> form_text("results", 4, $tp -> toForm($search_prefs[$handler_type][$query[2]]['results']), 4)."</td>
	</tr><tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_46.":</td>
	<td style='width:5' class='forumheader3'>".$rs -> form_text("chars", 4, $tp -> toForm($search_prefs[$handler_type][$query[2]]['chars']), 4)."</td>
	</tr><tr>
	<td style='width:50%' class='forumheader3'>".SEALAN_26.":</td>
	<td style='width:35%' class='forumheader3'>
	<input type='radio' name='pre_title' value='1'".(($search_prefs[$handler_type][$query[2]]['pre_title'] == 1) ? " checked='checked'" : "")." /> ".SEALAN_22."<br />
	<input type='radio' name='pre_title' value='0'".(($search_prefs[$handler_type][$query[2]]['pre_title'] == 0) ? " checked='checked'" : "")." /> ".SEALAN_17."<br />
	<input type='radio' name='pre_title' value='2'".(($search_prefs[$handler_type][$query[2]]['pre_title'] == 2) ? " checked='checked'" : "")." /> ".SEALAN_23."&nbsp;&nbsp;
	".$rs -> form_text("pre_title_alt", 20, $tp -> toForm($search_prefs[$handler_type][$query[2]]['pre_title_alt']))."
	</td>
	</tr>";

	$text .= "<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "update_handler", LAN_UPDATE)."</td>
	</tr>";

	$text .= "</table>
	</div>
	</form>";

} else {

	$text = "<form method='post' action='".e_SELF."'><div style='text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	$text .= "<tr>
	<td class='fcaption' colspan='4'>".SEALAN_21."</td>
	</tr>";

	$text .= "<tr>
	<td class='fcaption'>".SEALAN_24."</td>
	<td class='fcaption'>".SEALAN_25."</td>
	<td class='fcaption'>".LAN_ORDER."</td>
	<td class='fcaption'>".LAN_EDIT."</td>
	</tr>";

	foreach($search_handlers as $key => $value) {
		$text .= "<tr>
		<td style='width:55%; white-space:nowrap' class='forumheader3'>".$value."</td>
		<td style='width:25%' class='forumheader3'>";
		$text .= r_userclass("core_handlers[".$key."][class]", $search_prefs['core_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,classes");
		$text .= "</td>";
		$text .= "<td style='width:10%; text-align:center' class='forumheader3'>";
		$text .= "<select name='core_handlers[".$key."][order]' class='tbox'>";
		for($a = 1; $a <= $handlers_total; $a++) {
			$text .= ($search_prefs['core_handlers'][$key]['order'] == $a) ? "<option value='".$a."' selected='selected'>".$a."</option>" : "<option value='".$a."'>".$a."</option>";
		}
		$text .= "</select>
		</td>
		<td style='width:10%; text-align:center' class='forumheader3'>
		<a href='".e_SELF."?edit.c.".$key."'>".ADMIN_EDIT_ICON."</a>
		</td>
		</tr>";
	}

	foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
		if(is_readable(e_PLUGIN.$plug_dir."/e_search.php")){
			require_once(e_PLUGIN.$plug_dir."/e_search.php");
		}
		$text .= "<tr>
		<td style='width:55%; white-space:nowrap' class='forumheader3'>".$search_info[0]['qtype']."</td>
		<td style='width:25%' class='forumheader3'>";
		$text .= r_userclass("plug_handlers[".$plug_dir."][class]", $search_prefs['plug_handlers'][$plug_dir]['class'], "off", "public,guest,nobody,member,admin,main,classes");
		unset($search_info);
		$text .= "</td>";
		$text .= "<td style='width:10%; text-align:center' class='forumheader3'>";
		$text .= "<select name='plug_handlers[".$plug_dir."][order]' class='tbox'>";
		for($a = 1; $a <= $handlers_total; $a++) {
			$text .= ($search_prefs['plug_handlers'][$plug_dir]['order'] == $a) ? "<option value='".$a."' selected='selected'>".$a."</option>" : "<option value='".$a."'>".$a."</option>";
		}
		$text .= "</select>
		</td>
		<td style='width:10%; text-align:center' class='forumheader3'>
		<a href='".e_SELF."?edit.p.".$plug_dir."'>".ADMIN_EDIT_ICON."</a>
		</td>
		</tr>";
	}

	$text .= "<tr>
	<td style='white-space:nowrap' class='forumheader3'>Google</td>
	<td colspan='3' class='forumheader3'>";
	$sel = (isset($search_prefs['google']) && $search_prefs['google']) ? " checked='checked'" : "";
	$text .= r_userclass("google", $search_prefs['google'], "off", "public,guest,nobody,member,admin,main,classes");
	$text .= "</td>
	</tr>";

	$text .= "<tr>
	<td colspan='4' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "update_main", LAN_UPDATE)."</td>
	</tr>";

	$text .= "</table>
	</div><br />";

	$text .= "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	$text .= "<tr>
	<td class='fcaption' colspan='2'>".SEALAN_18."</td>
	</tr>";

	$text .= "<tr>
	<td class='fcaption'>".SEALAN_24."</td>
	<td class='fcaption'>".SEALAN_25."</td>
	</tr>";

	foreach ($search_prefs['comments_handlers'] as $key => $value) {
		$path = ($value['dir'] == 'core') ? e_HANDLER.'search/comments_'.$key.'.php' : e_PLUGIN.$value['dir'].'/search/search_comments.php';
		if(is_readable($path)){
			require_once($path);
		}
		$text .= "<tr>
		<td style='width:55%; white-space:nowrap' class='forumheader3'>".$comments_title."</td>
		<td style='width:45%' class='forumheader3'>";
		$text .= r_userclass("comments_handlers[".$key."][class]", $search_prefs['comments_handlers'][$key]['class'], "off", "public,guest,nobody,member,admin,main,classes");
		$text .= "</td>
		</tr>";
		unset($comments_title);
	}

	$text .= "<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "update_main", LAN_UPDATE)."</td>
	</tr>";

	$text .= "</table>
	</div>
	</form>";

}

$ns -> tablerender(SEALAN_1, $text);

require_once("footer.php");

function search_adminmenu() {
	global $query;
	if ($query[0] == '' || $query[0] == 'main') {
		$action = "main";
	} else if ($query[0] == 'settings') {
		$action = "settings";
	}

	$var['main']['text'] = SEALAN_41;
	$var['main']['link'] = e_SELF;

	$var['settings']['text'] = SEALAN_42;
	$var['settings']['link'] = e_SELF."?settings";

	show_admin_menu(SEALAN_40, $action, $var);
}

?>