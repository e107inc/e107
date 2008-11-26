<?php
// $Id: forum.php,v 1.3 2008-11-26 19:59:06 mcfly_e107 Exp $
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

		case 'main':
			return e_PLUGIN_ABS.'forum/forum.php';
			break;
	}

}
