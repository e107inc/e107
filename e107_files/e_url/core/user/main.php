<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* eURL configuration script
*/

function url_user_main($parms)
{
	switch(varsettrue($parms['func'], 'profile'))
	{
		case 'profile':
			return e_HTTP.'user.php?id.'.$parms['id'];
			break;
			
		case 'settings':
			return e_HTTP.'usersettings.php?'.$parms['id'];
			break;
		
	}
}

?>