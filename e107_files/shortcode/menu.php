<?php
/* $Id: menu.php,v 1.6 2009-08-16 16:30:56 secretr Exp $ */

function menu_shortcode($parm)
{
	return e107::getMenu()->render($parm);
}


?>