<?php

function url_user_main($parms)
{
	switch(varsettrue($parms['func'], 'profile'))
	{
		case 'profile':
			return e_HTTP.'user.php?id.'.$parms['id'];
			break;
	}
}

?>