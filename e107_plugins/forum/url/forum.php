<?php
// $Id: forum.php,v 1.4 2008-11-29 01:24:27 mcfly_e107 Exp $
function url_forum_forum($parms)
{
	switch($parms['func'])
	{
		case 'view':
			return e_PLUGIN_ABS."forum/viewforum.php?id={$parms['id']}";
			break;

		case 'track':
			return e_PLUGIN_ABS.'forum/forum.php?track';
			break;

		case 'main':
			return e_PLUGIN_ABS.'forum/forum.php';
			break;
	}

}
