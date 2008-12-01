<?php
// $Id: thread.php,v 1.3 2008-12-01 01:10:50 mcfly_e107 Exp $
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

		case 'view':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?id={$parms['id']}";
			break;

	}
}
