<?php
function print_item($thread_id)
{
	include_once(e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum;
	$thread_info = $forum->thread_get($thread_id,0,999);
	return "<pre>".print_r($thread_info,TRUE)."</pre>";
}

function email_item($thread_id)
{
	include_once(e_PLUGIN.'forum/forum_class.php');
	$forum = new e107forum;
	$thread_info = $forum->thread_get($thread_id,0,999);
	return "<pre>".print_r($thread_info,TRUE)."</pre>";
}

?>