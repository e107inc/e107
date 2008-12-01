<?php
// $Id: thread.php,v 1.4 2008-12-01 21:11:01 mcfly_e107 Exp $
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

		case 'post':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?post={$parms['id']}";
			break;

		case 'report':
			$page = (isset($parms['page']) ? (int)$parms['page'] : 0 );
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?id={$parms['id']}&report={$parms['report']}&page={$page}";
			break;

		case 'edit':
			return e_PLUGIN_ABS."forum/forum_post.php?edit={$parms['id']}";
			break;

		case 'quote':
			return e_PLUGIN_ABS."forum/forum_post.php?quote={$parms['id']}";
			break;

		case 'next':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?next={$parms['id']}";
			break;

		case 'prev':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?prev={$parms['id']}";
			break;

		case 'track':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?track={$parms['id']}";
			break;

		case 'untrack':
			return e_PLUGIN_ABS."forum/forum_viewtopic.php?untrack={$parms['id']}";
			break;


	}
}
