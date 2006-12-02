<?php
require_once("class2.php");
$tmp = explode(".", e_QUERY);
$forum_id = intval($tmp[0]);
if($forum_id)
{
	header("Location:".SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewforum.php?{$forum_id}");
	exit;
}
header("Location:".SITEURL."index.php");
exit;
?>