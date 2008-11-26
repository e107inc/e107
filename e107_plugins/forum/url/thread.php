<?php
// $Id: thread.php,v 1.1 2008-11-26 04:00:36 mcfly_e107 Exp $
function url_forum_thread($parms)
{
	switch($parms['func'])
	{
		case 'track':
			return e_PLUGIN_ABS.'forum/forum.php?track';
			break;
		
	}	
}
