<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration menu
 *
*/

//@TODO make it 0.8 compatible

if (!defined('e107_INIT')) { exit; }

if (ADMIN == TRUE)
{
	// We're not in admin - load generic admin phrases
	include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
	
	$tp = e107::getParser();
	$pref = e107::getPref();
	$ns = e107::getRender();
	
	$nav = e107::getNav();
	
	$admin = $nav->adminLinks('assoc');
	$plugins = $nav->pluginLinks('assoc');

	$array_functions = array_merge($admin, $plugins);

	// print_a($array_functions);

	// asort($array_functions);
	ksort($array_functions, 'title'); //FIXME Improve ordering. 

	//$array_functions = asortbyindex($array_functions, 1);

	$amtext = "<div style='text-align:center'>
	<select name='activate' onchange='urljump(this.options[selectedIndex].value)' class='tbox'>
	<option>".LAN_SELECT."</option>\n";
	foreach ($array_functions as $link_value)
	{
		$amtext .= render_admin_links($link_value['link'], $link_value['title'], $link_value['perms']);
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
