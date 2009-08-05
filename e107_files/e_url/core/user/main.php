<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: main.php,v 1.4 2009-08-05 14:16:57 e107coders Exp $
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