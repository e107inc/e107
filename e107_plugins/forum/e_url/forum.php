<?php
// $Id: forum.php,v 1.3 2008-12-13 21:52:19 mcfly_e107 Exp $
function url_forum_forum($parms)
{
	switch($parms['func'])
	{
		case 'view':
			return e_PLUGIN_ABS."forum/forum_viewforum.php?id={$parms['id']}";
			break;

		case 'track':
			return e_PLUGIN_ABS.'forum/forum.php?track';
			break;

		case 'main':
			return e_PLUGIN_ABS.'forum/forum.php';
			break;

		case 'post':
			return e_PLUGIN_ABS."forum/forum_post.php?f={$parms['type']}}id={$parms['id']}";
			break;

	}
}
