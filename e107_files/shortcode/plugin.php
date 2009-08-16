<?php
/* $Id: plugin.php,v 1.1 2009-08-16 23:58:31 e107coders Exp $ */

function plugin_shortcode($parm)
{
	global $sql, $tp, $ns;

	$menu = $parm;

	$path = $tp -> toDB(dirname($menu));
	$name = $tp -> toDB(basename($menu));

	if($path == '.')
	{
	  $path = $menu;
	}
    return e107::getMenu()->renderMenu($path,$name);
}
