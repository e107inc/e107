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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/admin_default.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-09 18:13:14 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$text = "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
}

while ($td<=5) {
	$text_cat .= "<td class='td' style='width:20%;' ></td>";
	$td++;
}
$td = 1;

$text .= "</tr><tr>
<td colspan='5'><br />
</td>
</tr>";

$text .= render_links(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", E_16_PLUGMANAGER, 'default');

if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		include(e_PLUGIN.$plugin_path."/plugin.php");
		if($eplug_conffile){
			$text .= render_links(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, E_16_PLUGIN, 'default');
		}
		unset($eplug_conffile, $eplug_name, $eplug_caption);
	}
}

$text .= "</tr>
</table></div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

admin_info();

?>
