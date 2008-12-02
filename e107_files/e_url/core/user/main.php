<?php

function url_user_main($parms)
{
	switch($parms['func'])
	{
		case 'profile':
			return e_HTTP.'user.php?id.'.$parms['id'];
			break;
	}
}

?>