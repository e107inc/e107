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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/categories.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:52:26 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
$text = "<div style='text-align:center'>
	<table class='fborder' style='".ADMIN_WIDTH."'>";
	
foreach ($admin_cat['id'] as $cat_key => $cat_id) {
	$text_check = FALSE;
	$text_cat = "<tr><td class='fcaption' colspan='2'>".$admin_cat['title'][$cat_key]."</td></tr>
		<tr><td class='forumheader3' style='text-align: center; vertical-align: middle; width: 72px; height: 48px'>".$admin_cat['lrg_img'][$cat_key]."</td><td class='forumheader3'>
		<table style='width:100%'>";
	if ($cat_key != 7) {
		foreach ($newarray as $key => $funcinfo) {
			if ($funcinfo[4] == $cat_key) {
				$text_rend = render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
				if ($text_rend) {
					$text_check = TRUE;
				}
				$text_cat .= $text_rend;
			}
		}
	} else {
		$text_rend = render_links(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", E_16_PLUGMANAGER, 'default');
		if ($text_rend) {
			$text_check = TRUE;
		}
		$text_cat .= $text_rend;
		if ($sql->db_Select("plugin", "*", "plugin_installflag=1")) {
			while ($row = $sql->db_Fetch()) {
				extract($row);
				include(e_PLUGIN.$plugin_path."/plugin.php");
				if ($eplug_conffile) {
					$plugin_icon = $eplug_icon_small ? "<img src='".e_PLUGIN.$eplug_icon_small."' alt='".$eplug_caption."' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />" :
					 E_16_PLUGIN;
					$text_rend = render_links(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $plugin_icon, 'default');
					if ($text_rend) {
						$text_check = TRUE;
					}
					$text_cat .= $text_rend;
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
			}
		}
	}
	$text_cat .= render_clean();
	$text_cat .= "</table>
		</td></tr>";
	if ($text_check) {
		$text .= $text_cat;
	}
}
	
$text .= "</table></div>";
	
$ns->tablerender(ADLAN_47." ".ADMINNAME, $text);
	
echo admin_info();
	
?>