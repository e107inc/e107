<?php
/* $Id: menu.php,v 1.7 2009-08-16 23:58:31 e107coders Exp $ */

function menu_shortcode($parm)
{
	return e107::getMenu()->renderArea($parm);
}


?>