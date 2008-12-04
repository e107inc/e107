<?php
// $Id: thread.php,v 1.3 2008-12-04 21:36:09 mcfly_e107 Exp $
function url_forum_thread($parms)
{
	switch($parms['func'])
	{

		case 'nt':
			return e_PLUGIN_ABS."forum/forum_post.php?f=nt&id={$parms['id']}";
			break;

		case 'rp':
			return e_PLUGIN_ABS."forum/forum_post.php?f=rp&id={$parms['id']}";
			break;

		case 'view':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?id={$parms['id']}";
			break;

		case 'last':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?id={$parms['id']}&last=1";
			break;

		case 'post':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=post&id={$parms['id']}";
			break;

		case 'report':
			$page = (isset($parms['page']) ? (int)$parms['page'] : 0 );
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=report&id={$parms['report']}&page={$page}";
			break;

		case 'edit':
			return e_PLUGIN_ABS."forum/forum_post.php?f=edit&id={$parms['id']}";
			break;

		case 'quote':
			return e_PLUGIN_ABS."forum/forum_post.php?f=quote&id={$parms['id']}";
			break;

		case 'next':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=next&id={$parms['id']}";
			break;

		case 'prev':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=prev&id={$parms['id']}";
			break;

		case 'track':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=track&id={$parms['id']}";
			break;

		case 'untrack':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=untrack&id={$parms['id']}";
			break;


	}
}
