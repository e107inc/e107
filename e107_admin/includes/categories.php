<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

$mes = e107::getMessage();

$text = "<div style='text-align:center'>
	<table class='table'>";
	
$admin_cat = e107::getNav()->adminCats();

$newarray = e107::getNav()->adminLinks('core');
$plugin_array = e107::getNav()->adminLinks('plugin');

foreach ($admin_cat['id'] as $cat_key => $cat_id)
{
	$text_check = FALSE;
	$text_cat = "<tr><td class='fcaption' colspan='2'>".$admin_cat['title'][$cat_key]."</td></tr>
		<tr><td class='forumheader3' style='text-align: center; vertical-align: middle; width: 72px; height: 48px'>".$admin_cat['lrg_img'][$cat_key]."</td><td class='forumheader3'>
		<table style='width:100%'>";
	if ($cat_key != 5)
	{
		foreach ($newarray as $key => $funcinfo)
		{
			if ($funcinfo[4] == $cat_key)
			{
				$text_rend = e107::getNav()->renderAdminButton($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
				if ($text_rend)
				{
					$text_check = TRUE;
				}
				$text_cat .= $text_rend;
			}
		}
	}
	else
	{
		$text_rend = e107::getNav()->renderAdminButton(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", E_16_PLUGMANAGER, 'default');

		if ($text_rend)
		{
			$text_check = TRUE;
		}
		$text_cat .= $text_rend;
        
		foreach ($plugin_array as $plug_key => $plug_value)
		{
			$text_cat .= e107::getNav()->renderAdminButton($plug_value['link'], $plug_value['title'], $plug_value['caption'], $plug_value['perms'], $plug_value['icon'], 'default');
		}
	}
	$text_cat .= render_clean();
	$text_cat .= "</table>
		</td></tr>";
	if ($text_check)
	{
		$text .= $text_cat;
	}
}

$text .= "</table></div>";

$ns->tablerender(ADLAN_47." ".ADMINNAME, $mes->render().$text);

echo admin_info();

?>
