<?php
/* $Id$ */

function plugin_shortcode($parm = '')
{
	$tp = e107::getParser();

	@list($menu,$parms) = explode('|',$parm.'|', 2);

	$path = $tp->toDB(dirname($menu));
	$name = $tp->toDB(basename($menu));

	if($path == '.')
	{
	  $path = $menu;
	}
	/**
	 * @todo check if plugin is installed when installation required
	 */
	
	/**
	 *	fixed todo: $mode is provided by the menu itself, return is always true, added optional menu parameters
	 */
    return e107::getMenu()->renderMenu($path,$name, trim($parms, '|'),true);
}
