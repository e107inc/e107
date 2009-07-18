<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/admin_menu/admin_menu.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-07-18 10:17:56 $
 * $Author: marj_nl_fr $
*/

//@TODO make it 0.8 compatible

if (!defined('e107_INIT')) { exit; }
global $tp;
if (ADMIN == TRUE)
{
	// We're not in admin - load generic admin phrases
	include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');

	require_once(e_HANDLER."userclass_class.php");
	require_once(e_ADMIN."ad_links.php");
	require_once(e_HANDLER.'admin_handler.php');

	$array_functions[] = array(e_ADMIN."plugin.php", ADLAN_98, "Z");

	if ($sql->db_Select("plugin", "*", "plugin_installflag=1"))
	{
		while ($row = $sql->db_Fetch())
		{
			include(e_PLUGIN.$row['plugin_path']."/plugin.php");
			if ($eplug_conffile)
			{
				$array_functions[] = array(e_PLUGIN.$row['plugin_path']."/".$eplug_conffile, $tp->toHtml($eplug_name,"","defs,emotes_off, no_make_clickable"), "P".$row['plugin_id']);
			}
			unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
		}
	}

	$array_functions = asortbyindex($array_functions, 1);

	$amtext = "<div style='text-align:center'>
	<select name='activate' onchange='urljump(this.options[selectedIndex].value)' class='tbox'>
	<option>".LAN_SELECT."</option>\n";
	foreach ($array_functions as $link_value)
	{
		$amtext .= render_admin_links($link_value[0], $link_value[1], $link_value[2]);
	}

	$amtext .= "</select>
	</div>";
	$ns->tablerender(LAN_ADMIN, $amtext, 'admin_menu');
}

function render_admin_links($link, $title, $perms)
{
	if (getperms($perms))
	{
		return "<option value='".$link."'>".$title."</option>";
	}
}
