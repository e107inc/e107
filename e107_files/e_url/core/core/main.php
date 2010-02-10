<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
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