<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/includes/categories.php,v $
|     $Revision: 1.8 $
|     $Date: 2009-11-05 09:15:12 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER."message_handler.php");
$emessage = e107::getMessage();

$text = "<div style='text-align:center'>
	<table class='fborder' style='".ADMIN_WIDTH."'>";

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
				$text_rend = render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
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
		$text_rend = render_links(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", E_16_PLUGMANAGER, 'default');

		$xml = e107::getXml();
		$xml->filter = array('@attributes' => FALSE,'administration' => FALSE);	// .. and they're all going to need the same filter

		if ($text_rend)
		{
			$text_check = TRUE;
		}
		$text_cat .= $text_rend;
		if ($sql->db_Select("plugin", "*", "plugin_installflag=1"))
		{
			while ($row = $sql->db_Fetch())
			{
				extract($row);		//  plugin_id int(10) unsigned NOT NULL auto_increment,
									//	plugin_name varchar(100) NOT NULL default '',
									//	plugin_version varchar(10) NOT NULL default '',
									//	plugin_path varchar(100) NOT NULL default '',
									//	plugin_installflag tinyint(1) unsigned NOT NULL default '0',
									//	plugin_addons text NOT NULL,

				if (is_readable(e_PLUGIN.$plugin_path."/plugin.xml"))
				{
					$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
					$eplug_name = $tp->toHTML($readFile['name'],FALSE,"defs, emotes_off");
					$eplug_conffile = $readFile['administration']['configFile'];
					$eplug_icon_small 	= $plugin_path.'/'.$readFile['administration']['iconSmall'];
					$eplug_icon 		= $plugin_path.'/'.$readFile['administration']['icon'];
					$eplug_caption = $readFile['administration']['caption'];
				}
				elseif (is_readable(e_PLUGIN.$plugin_path."/plugin.php"))
				{
					include(e_PLUGIN.$plugin_path."/plugin.php");
				}
				if ($eplug_conffile)
				{
					$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs, emotes_off");
					$plugin_icon = $eplug_icon_small ? "<img src='".e_PLUGIN.$eplug_icon_small."' alt='".$eplug_caption."' class='icon S16' />" : E_16_PLUGIN;
					$plugin_array[ucfirst($eplug_name)] = array('link' => e_PLUGIN.$plugin_path."/".$eplug_conffile, 'title' => $eplug_name, 'caption' => $eplug_caption, 'perms' => "P".$plugin_id, 'icon' => $plugin_icon);
					$text_check = TRUE;
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
			}
		}
		ksort($plugin_array, SORT_STRING);
		foreach ($plugin_array as $plug_key => $plug_value)
		{
			$text_cat .= render_links($plug_value['link'], $plug_value['title'], $plug_value['caption'], $plug_value['perms'], $plug_value['icon'], 'default');
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

$ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);

echo admin_info();

?>
