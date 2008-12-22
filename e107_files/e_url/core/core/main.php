<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: main.php,v 1.2 2008-12-22 03:15:04 mcfly_e107 Exp $
*
* eURL configuration script
*/

function url_core_main($parms)
{
	switch ($parms['action'])
	{
		case 'index':
		return e_HTTP.'index.php';
		break;
	}

}
?>