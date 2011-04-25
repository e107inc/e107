<?php
// $Id$
function url_forum_thread($parms)
{
	switch($parms['func'])
	{

		case 'nt':
			return e_PLUGIN_ABS."forum/forum_post.php?f=nt&amp;id={$parms['id']}";
			break;

		case 'rp':
			return e_PLUGIN_ABS."forum/forum_post.php?f=rp&amp;id={$parms['id']}";
			break;

		case 'view':
			$page = (varset($parms['page']) ? '&amp;p='.$parms['page'] : '');
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?id={$parms['id']}{$page}";
			break;

		case 'last':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?id={$parms['id']}&amp;last=1";
			break;

		case 'post':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=post&amp;id={$parms['id']}";
			break;

		case 'report':
			$page = (isset($parms['page']) ? (int)$parms['page'] : 0 );
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=report&amp;id={$parms['id']}&amp;post={$parms['post']}&amp;p={$page}";
			break;

		case 'edit':
			return e_PLUGIN_ABS."forum/forum_post.php?f=edit&amp;id={$parms['id']}";
			break;

		case 'move':
			return e_PLUGIN_ABS."forum/forum_conf.php?f=move&amp;id={$parms['id']}";
			break;

		case 'split':
			return e_PLUGIN_ABS."forum/forum_conf.php?f=split&amp;id={$parms['id']}";
			break;

		case 'quote':
			return e_PLUGIN_ABS."forum/forum_post.php?f=quote&amp;id={$parms['id']}";
			break;

		case 'next':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=next&amp;id={$parms['id']}";
			break;

		case 'prev':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=prev&amp;id={$parms['id']}";
			break;

		case 'track':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=track&amp;id={$parms['id']}";
			break;

		case 'untrack':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=untrack&amp;id={$parms['id']}";
			break;

		case 'track_toggle':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?f=track_toggle&amp;id={$parms['id']}";
			break;

	}
}
