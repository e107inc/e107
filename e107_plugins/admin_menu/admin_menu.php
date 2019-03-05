<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration menu
 *
*/


if (!defined('e107_INIT')) { exit; }


if (ADMIN == TRUE)
{
	
	e107::lan('core','admin', true); // We're not in admin - load generic admin phrases

	$tp 	= e107::getParser();
	$pref 	= e107::getPref();
	$ns 	= e107::getRender();
	$nav 	= e107::getNav();
	
	
    $array_functions = $nav->adminLinks();

	$amtext = "<div class='text-center' style='text-align:center'>
	<select name='activate' onchange='urljump(this.options[selectedIndex].value)' class='tbox form-control'>
	<option>".LAN_SELECT."...</option>\n";
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
