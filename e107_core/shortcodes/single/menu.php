<?php
/* $Id$ */

function menu_shortcode($parm)
{
	return e107::getMenu()->renderArea($parm);
}


?>