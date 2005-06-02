<?php
require_once("class2.php");
$tmp = explode(".", e_QUERY);
$thread_id = intval($tmp[1]);
if($thread_id)
{
	header("Location:".SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?{$thread_id}");
	exit;
}
header("Location:".SITEURL."index.php");
exit;
?>
