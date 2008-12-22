<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: main.php,v 1.3 2008-12-22 03:15:04 mcfly_e107 Exp $
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
			return e_HTTP.'usersettings.php?id.'.$parms['id'];
			break;
		
	}
}

?>