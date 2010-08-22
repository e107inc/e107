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

if (!defined('e107_INIT')) { exit; }
$buts = "";
$text = "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>";

while (list($key, $funcinfo) = each($newarray)) {
	$buts .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
}
$text .= $buts;

$text_cat = '';
while ($td <= 5) {
	$text_cat .= "<td class='td' style='width:20%;' ></td>";
	$td++;
}
$td = 1;

$text .= "</tr></table></div>";

if($buts !=""){
	$ns->tablerender(ADLAN_47." ".ADMINNAME, $text);
}

$text = "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>";

$text .= render_links(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", E_16_PLUGMANAGER, 'default');

if ($sql->db_Select("plugin", "*", "plugin_installflag=1")) {
	while ($row = $sql->db_Fetch()) {
		extract($row);
		include(e_PLUGIN.$plugin_path."/plugin.php");
		if ($eplug_conffile) {
			$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs, emotes_off");
			$plugin_icon = $eplug_icon_small ? "<img src='".e_PLUGIN.$eplug_icon_small."' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />" : E_16_PLUGIN;
			$plugin_array[ucfirst($eplug_name)] = array('link' => e_PLUGIN.$plugin_path."/".$eplug_conffile, 'title' => $eplug_name, 'caption' => $eplug_caption, 'perms' => "P".$plugin_id, 'icon' => $plugin_icon);
		}
		unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
	}
}

ksort($plugin_array, SORT_STRING);
foreach ($plugin_array as $plug_key => $plug_value) {
	$text .= render_links($plug_value['link'], $plug_value['title'], $plug_value['caption'], $plug_value['perms'], $plug_value['icon'], 'default');
}

$text .= "</tr>
	</table></div>";

$ns->tablerender(ADLAN_CL_7, $text);

echo admin_info();

?>
