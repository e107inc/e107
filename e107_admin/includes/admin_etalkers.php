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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/admin_etalkers.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-05 16:57:40 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$td = 1;
function wad($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE){
	global $td;
	$permicon = $mode ? SML_IMG_PLUGIN : $icon;
	if (getperms($perms)){
		if ($td==1) { $text .= "<tr>"; }
		$text = "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap' 
		onmouseover='tdover(this)' onmouseout='tdnorm(this)' onclick=\"document.location.href='$link'\">$permicon $title</td>";
		if ($td==5) { $text .= '</tr>'; }
		if ($td<5) { $td++; } else { $td = 1; }
	} 
	return $text;
}

$text = "<div style='text-align:center'>
<table class='fborder' style='".ADMIN_WIDTH."'>";

foreach ($admin_cat['id'] as $cat_key => $cat_id) {
	$text_check = FALSE;
	$text_cat = "<tr><td class='forumheader3' rowspan='2' style='text-align: center; vertical-align: middle;'>".$admin_cat['lrg_img'][$cat_key]."</td><td class='forumheader'>".$admin_cat['title'][$cat_key]."</td></tr>
	<tr><td class='forumheader3'>
	<table style='width:100%'><tr>";
	if ($cat_key!=7) {
		$newarray = asortbyindex($array_functions, 1);
		foreach ($newarray as $key => $funcinfo) {
			if ($funcinfo[4]==$cat_key) {
				$text_rend = wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5]);
				if ($text_rend) { $text_check = TRUE; }
				$text_cat .= $text_rend;
			}
		}
	} else {
		$text_rend = wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", "", TRUE);
		if ($text_rend) { $text_check = TRUE; }
		$text_cat .= $text_rend;
		if ($sql -> db_Select("plugin", "*", "plugin_installflag=1")) {
			while ($row = $sql -> db_Fetch()) {
				extract($row);
				include(e_PLUGIN.$plugin_path."/plugin.php");
				if ($eplug_conffile) {
					$text_rend = wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, "", TRUE);
					if ($text_rend) { $text_check = TRUE; }
					$text_cat .= $text_rend;
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption);
			}
		}
	}
	while ($td<=5) {
		$text_cat .= "<td class='td' style='width:20%;' ></td>";
		$td++;
	}
	$td = 1;
	$text_cat .= "</tr></table>
	</td></tr>";
	if ($text_check) { $text .= $text_cat; }
}

$text .= "
</table>
</div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

$admin_info = TRUE;
$admin_info_style = TRUE;

?>
