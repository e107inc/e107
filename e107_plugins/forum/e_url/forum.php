<?php
// $Id$
function url_forum_forum($parms)
{
	switch($parms['func'])
	{
		case 'view':
			$page = (varset($parms['page']) ? '&p='.$parms['page'] : '');
			return e_PLUGIN_ABS."forum/forum_viewforum.php?id={$parms['id']}{$page}";
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

		case 'rules':
			return e_PLUGIN_ABS.'forum/forum.php?f=rules';
			break;

		case 'mfar':
			return e_PLUGIN_ABS.'forum/forum.php?f=mfar&id='.$parms['id'];
			break;

	}
}
