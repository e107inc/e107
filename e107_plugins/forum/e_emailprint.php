<?php
if (!defined('e107_INIT')) { exit; }

function print_item($thread_id)
{
	// moved to e_print.php
}

function email_item($thread_id)
{
	return e107::getAddon('forum','e_print')->render($thread_id); // Quick Fix
}