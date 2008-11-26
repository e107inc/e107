<?php
// $Id: forum.php,v 1.2 2008-11-26 04:00:36 mcfly_e107 Exp $
function url_forum_forum($parms)
{
	switch($parms['func'])
	{
		case 'view':
			return e_PLUGIN_ABS."forum/viewforum.php?{$parms['id']}";
			break;
			
		case 'track':
			return e_PLUGIN_ABS.'forum/forum.php?track';
			break;
		
	}	
}
