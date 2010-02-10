<?php
/* $Id$ */

function plugin_shortcode($parm = '')
{
	$tp = e107::getParser();

	list($menu,$return) = explode('|',$parm.'|');

	$path = $tp -> toDB(dirname($menu));
	$name = $tp -> toDB(basename($menu));

	if($path == '.')
	{
	  $path = $menu;
	}
	/**
	 *	@todo: $mode not defined
	 */
    return e107::getMenu()->renderMenu($path,$name,$mode,$return);
}
