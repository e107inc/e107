<?php
// $Id: thread.php,v 1.2 2008-11-26 19:59:06 mcfly_e107 Exp $
function url_forum_thread($parms)
{
	switch($parms['func'])
	{

		case 'track':
			return e_PLUGIN_ABS.'forum/forum.php?track';
			break;

		case 'nt':
			return e_PLUGIN_ABS."forum/forum_post.php?f=nt&id={$parms['id']}";
			break;

	}
}
