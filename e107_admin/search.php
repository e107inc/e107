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
|     $Revision: 1.3 $
|     $Date: 2005-03-13 10:43:42 $
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
$search_prefs = $sysprefs -> getArray('search_prefs');

$search_handlers['news'] = SEALAN_5;
$search_handlers['comments'] = SEALAN_6;
$search_handlers['users'] = SEALAN_7;
$search_handlers['downloads'] = SEALAN_8;

if (isset($_POST['updatesettings'])) {
	$search_prefs['search_chars'] = $_POST['search_chars'];
	$search_prefs['search_sort'] = $_POST['search_sort'];
	$search_prefs['search_res'] = $_POST['search_res'];
	foreach($search_handlers as $s_key => $s_value) {
		$search_prefs['core_handlers'][$s_key] = $_POST['core_handlers'][$s_key];
	}

	foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
		$search_prefs['plug_handlers'][$plug_dir] = $_POST['plug_handlers'][$plug_dir];
	}

	$search_prefs['google'] = $_POST['google'];

	$tmp = addslashes(serialize($search_prefs));
	$sql->db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs' ");
	$message = TRUE;
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

//load all plugin search routines
$handle = opendir(e_PLUGIN);
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
		$plugin_handle = opendir(e_PLUGIN.$file."/");
		while (false !== ($file2 = readdir($plugin_handle))) {
			if ($file2 == "e_search.php") {
				require_once(e_PLUGIN.$file."/".$file2);
			}
		}
	}
}

if (isset($message)) {
		$ns->tablerender("", "<div style='text-align:center'><b>".LAN_UPDATED."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>";


$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_2."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>".$rs -> form_text("search_chars", 3, $search_prefs['search_chars'], 4)."</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_9."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>".$rs -> form_text("search_res", 3, $search_prefs['search_res'], 4)."</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_3."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>".$rs -> form_radio('search_sort', 'php', ($search_prefs['search_sort'] == 'php' ? 1 : 0))."PHP".$rs -> form_radio('search_sort', 'mysql', ($search_prefs['search_sort'] == 'mysql' ? 1 : 0))."MySql</td>
</tr>";

$text .= "<tr>
<td style='width:50%; white-space:nowrap' class='forumheader3'>".SEALAN_4."</td>
<td style='width:50%;' colspan='2' class='forumheader3'>";

foreach($search_handlers as $key => $value) {
	$sel = (isset($search_prefs['core_handlers'][$key]) && $search_prefs['core_handlers'][$key]) ? " checked='checked'" : "";
	$text .= "<span style='white-space:nowrap'><input type='checkbox' name='core_handlers[".$key."]' ".$sel." />".$value."</span>\n";
}

$i = 0;
foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
	require_once(e_PLUGIN.$plug_dir."/e_search.php");
	$sel = $active ? " checked='checked'" : "";
	$text .= "<span style='white-space:nowrap'><input type='checkbox' name='plug_handlers[".$plug_dir."]' ".$sel." />".$search_info[$i]['qtype']."</span>\n";
	$i++;
}

$sel = (isset($search_prefs['google']) && $search_prefs['google']) ? " checked='checked'" : "";
$text .= "<input id='google' type='checkbox' name='google' ".$sel." />Google";

$text .= "</td>
</tr>";

$text .= "<tr>
<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button("submit", "updatesettings", LAN_UPDATE)."</td>
</tr>";

$text .= "</table>
</form>
</div>";
	
$ns -> tablerender(SEALAN_1, $text);

require_once("footer.php");

?>