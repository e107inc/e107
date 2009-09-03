<?php
/* $Id: plugin.php,v 1.2 2009-09-03 01:27:27 e107coders Exp $ */

function plugin_shortcode($parm)
{
	global $sql, $tp, $ns;

	list($menu,$return) = explode("|",$parm);

	$path = $tp -> toDB(dirname($menu));
	$name = $tp -> toDB(basename($menu));

	if($path == '.')
	{
	  $path = $menu;
	}
    return e107::getMenu()->renderMenu($path,$name,$mode,$return);
}
