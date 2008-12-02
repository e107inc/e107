<?php
// $Id: forum.php,v 1.1 2008-12-02 00:33:29 secretr Exp $
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
